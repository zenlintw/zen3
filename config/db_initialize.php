<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                         *
     *      Creation  : 2003/03/21                                                            *
     *      work for  : provide all program to connect DB and initialize global variables     *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *
     *      @version:$Id: db_initialize.php,v 1.1 2010/02/24 02:38:56 saly Exp $                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/sys_config.php');
    if (PHP_VERSION >= '5') {
        include_once(sysDocumentRoot . '/lib/domxml-php4-to-php5.php');
    }
    if (PHP_VERSION >= '7') {
        include_once(sysDocumentRoot . '/lib/ereg-wrapper.php');
    include_once(sysDocumentRoot . '/lib/mysql-wrapper.php');
    if (file_get_contents("php://input")) {
        $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents("php://input");
    }
    }
    require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');
    require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');
    require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    setlocale(LC_ALL,'zh_TW.UTF-8');

    // 停機公告
    $system_pause_file = sysDocumentRoot . '/base/10001/system_pause.txt';
    if (file_exists($system_pause_file)){
        $fp = @fopen($system_pause_file, "r");
        // 讀出整個檔案內容
        $dec_content = @fread($fp,filesize($system_pause_file));
        // 解開編碼
        $system_pause_data = unserialize(other_dec($dec_content));
        if (is_array($system_pause_data)){
            if ((time()> strtotime($system_pause_data['start_time'])) && (time()<strtotime($system_pause_data['end_time']))){
                $systemPauseAllowIps = explode(";", $system_pause_data['allow_ip']);
                if (!in_array(wmGetUserIp(), $systemPauseAllowIps)) {
                    header('Location: /construction.php');
                    exit;
                }
            }
        }
        unset($fp, $dec_content, $system_pause_data);
    }
    unset($system_pause_file);

    // =======================  系統變數宣告段 begin  =======================
    // 設定 ADOdb 傳回之陣列同時傳回 array 與 hash
    $ADODB_FETCH_MODE = ADODB_FETCH_BOTH;

    // 資料庫連結
    $sysConn = &ADONewConnection(sysDBtype);
    if (!$sysConn->Connect(sysDBhost, sysDBaccoount, sysDBpassword))
        die('Database Connecting failure !');
    if ($_SERVER['HTTPS']){
        $http_secure = true;
    }else{
        $http_secure = false;
    }
    if (!isset($_SERVER['SCRIPT_URI'])) {
        $_SERVER['SCRIPT_URI'] = sprintf("%s://%s%s", $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
    }

    // Wmpro5.1於apache2.4上跑php fpm的架構，apache_request_headers函式將不再定義
    if( !function_exists('apache_request_headers') ) {
        function apache_request_headers() {
            $arh = array();
            $rx_http = '/\AHTTP_/';
            foreach($_SERVER as $key => $val) {
                if( preg_match($rx_http, $key) ) {
                    $arh_key = preg_replace($rx_http, '', $key);
                    $rx_matches = array();
                    $rx_matches = explode('_', $arh_key);

                    if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                        foreach($rx_matches as $ak_key => $ak_val){
                            $rx_matches[$ak_key] = ucfirst($ak_val);
                        }
                        $arh_key = implode('-', $rx_matches);
                    }
                    $arh[$arh_key] = $val;
                }
            }
            return( $arh );
        }
    }

    // crontab執行的程式不需檢查host header Attack
    if (!isset($isConsole)){
        // 排除弱掃中風險 host header attack
        $headers = apache_request_headers();
        $hosts_res = dbGetCol('WM_school', 'school_host', 'school_host != "localhost" and school_host not like "[delete]%"');

        if(isset($headers['Host']) && !in_array($headers['Host'], $hosts_res)){
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
        if(is_array($_SERVER['SCRIPT_URI'])){
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        if (!in_array($_SERVER['HTTP_HOST'], $hosts_res) || !in_array($_SERVER['SERVER_NAME'], $hosts_res)) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }

    // 去掉 $_SERVER['HTTP_HOST'] 的 port
    if (strpos($_SERVER['HTTP_HOST'], ':') !== FALSE) list($_SERVER['HTTP_HOST']) = explode(':', $_SERVER['HTTP_HOST']);

    // 內定角色定義
    $sysRoles = array('guest'          =>     1, // 參觀者
                      'senior'         =>     2, // 學長
                      'paterfamilias'  =>     4, // 家長
                      'superintendent' =>     8, // 長官/督學
                      'auditor'        =>    16, // 旁聽生
                      'student'        =>    32, // 正式生
                      'assistant'      =>    64, // 課程助教
                      'instructor'     =>   128, // 講師
                      'class_instructor' => 256, // 班級助理
                      'teacher'        =>   512, // 教師 (通常比講師多具有教材管理編修權)
                      'director'       =>  1024, // 導師 (學生人員管理)
                      // 'course_opener'  =>  1024, // 開課者 -- Wmpro5取消此身份
                      'manager'        =>  2048, // 一般管理者
                      'administrator'  =>  4096, // 超級管理者
                      'root'           =>  8192, // 最高管理者 (一機只有一人)
                      'all'            => 16127  // 所有之 mask
                     );

    /* -- BEGIN 讀取常數定義檔 -- */
    function getConstatnt($school_id){
        if (10000 == $school_id) {
            $fname = sysDocumentRoot . '/base/config.txt';
        } else {
            $fname = sysDocumentRoot . '/base/' . $school_id . '/config.txt';
        }
        $Da = array();
        $fp = @fopen($fname, "r");
        if (!$fp) return array();
        // 讀出整個檔案內容
        $dec_content = @fread($fp,filesize($fname));
        // 解開編碼
        $org_content = other_dec($dec_content);
        $temp_array = explode("\r\n",$org_content);
        if (is_array($temp_array)){
            $temp_count = count($temp_array);
            for ($i=0;$i < $temp_count;$i++){
                $item = trim($temp_array[$i]);
                $item1 = explode('@',$item);

                // $item1[0] 欄位名稱
                // $item1[1] 欄位值
                $Da[$item1[0]]= $item1[1];
            }
        }
        @fclose($fp);

        return $Da;
    }
    /* -- END 讀取常數定義檔 -- */

    $sch_sql = 'select school_id from WM_school where school_host="' . $_SERVER['HTTP_HOST'] . '"';
    $sysConn->Execute('use ' . sysDBname);
    $school_id = $sysConn->GetOne($sch_sql);
	// wm3update (避免排程執行取不到school_id(B)
	if($school_id==false){
		$school_id = 10001;
	}
	// wm3update (避免排程執行取不到school_id(E)
    $Da = getConstatnt($school_id);
    $masterDa = getConstatnt(10000);

    # 開課限量 : 全站最高開課量：0 表無限，其他正值表有限
    define('sysCourseLimit',(strlen($Da['sysCourseLimit']) > 0)?intval($Da['sysCourseLimit']):DEFAULT_sysCourseLimit);

    # 試題限量 : 單一課程，最多可建立多少試題：數量=0表無限，其他正值表有限
    define('CourseQuestionsLimit',(strlen($Da['CourseQuestionsLimit']) > 0)?intval($Da['CourseQuestionsLimit']):DEFAULT_CourseQuestionsLimit);

    # 試卷限量 : 單一課程每份試卷最多可以出多少題：預設200 ，不可以設為零
    define('CourseExamQuestionsLimit',(strlen($Da['CourseExamQuestionsLimit']) > 0)?intval($Da['CourseExamQuestionsLimit']):DEFAULT_CourseExamQuestionsLimit);

    # 系統TimeOut : 系統預設 TimeOut 時間機制：預設為2 小時，60為一小時。(換算單位為分)
    define('systemTimeOutLimit',(strlen($Da['systemTimeOutLimit']) > 0)?intval($Da['systemTimeOutLimit']):DEFAULT_systemTimeOutLimit);

    # 帳號規則 : 帳號第一個字是否為數字(代號為1)、字母 (代號為2)或數字跟字母皆可(代號為0)
    define('Account_firstchr',(strlen($Da['Account_firstchr']) > 0)?intval($Da['Account_firstchr']):DEFAULT_Account_firstchr);

    # 學員帳號限量 : 全站最高學員帳號量：0 表無限，其他正值表有限
    define('sysMaxUser',(strlen($Da['sysMaxUser']) > 0)?intval($Da['sysMaxUser']):DEFAULT_sysMaxUser);

    # 學員登入帳號限量 : 全站最高學員登入帳號量：0 表無限，其他正值表有限
    define('sysMaxConcurrentUser',(strlen($Da['sysMaxConcurrentUser']) > 0)?intval($Da['sysMaxConcurrentUser']):DEFAULT_sysMaxConcurrentUser);

    # 帳號最短限字 : 帳號最少限輸入幾個字元(範圍區間為2~20)，預設為2
    define('sysAccountMinLen',(strlen($Da['sysAccountMinLen']) > 0)?intval($Da['sysAccountMinLen']):DEFAULT_sysAccountMinLen);

    # 帳號最長限字 : 帳號最少限輸入幾個字元(範圍區間為2~20)，預設為20
    define('sysAccountMaxLen',(strlen($Da['sysAccountMaxLen']) > 0)?intval($Da['sysAccountMaxLen']):DEFAULT_sysAccountMaxLen);

    # 帳號檢查規則
    $account_rule = '';
    switch (Account_firstchr){
        case 1:     // 數字
            $account_rule = '/^[0-9][0-9A-Z]*([._-][0-9A-Z]+)?$/i';
            break;
        case 2:     // 字母
            $account_rule = '/^[A-Z][0-9A-Z]*([._-][0-9A-Z]+)?$/i';
            break;
        case 0:        // 數字跟字母
        default:    // 數字跟字母
            $account_rule = '/^[0-9A-Z]+([._-][0-9A-Z]+)*([0-9A-Z]+)?$/i ';
            break;
    }
    define('Account_format', $account_rule);

    # 閱讀時數 教材目錄的單一閱讀節點的時數計算：
    # 預設為3 秒~ 6 小時內的閱讀時數才會計算進去，否則不予計算。
    # (換算單位為秒)
    define('pathNodeTimeShortlimit',(strlen($Da['pathNodeTimeShortlimit']) > 0)?intval($Da['pathNodeTimeShortlimit']):DEFAULT_pathNodeTimeShortlimit);
    define('pathNodeTimeLonglimit',(strlen($Da['pathNodeTimeLonglimit']) > 0)?intval($Da['pathNodeTimeLonglimit']):DEFAULT_pathNodeTimeLonglimit);

    # 可包裝的課程內容大小 : 設定單一課程可包裝硬碟空間大小(預設為512000KB)
    define('CoursePackLimit',(strlen($Da['CoursePackLimit']) > 0)?$Da['CoursePackLimit']:DEFAULT_CoursePackLimit);

    # 匯出試題題數 : 設定單次匯出的題數數目(預設為200題)
    define('ExamPackLimit',(strlen($Da['ExamPackLimit']) > 0)?intval($Da['ExamPackLimit']):DEFAULT_ExamPackLimit);

    # 啟用 join net : 預設為不啟用Join net
    define('joinet',(strlen($Da['joinet']) > 0)?$Da['joinet']:DEFAULT_joinet);

    # 定義使用 join net 的 MMC_Server
    define('MMC_Server',(strlen($Da['MMC_Server']) > 0)?$Da['MMC_Server']:DEFAULT_MMC_Server);

    # 定義使用 join net 的 MMC_Server_port
    define('MMC_Server_port',(strlen($Da['MMC_Server_port']) > 0)?$Da['MMC_Server_port']:DEFAULT_MMC_Server_port);

    # 定義課程分組可否使用joinnet
    define('joinnet_group_enable',(strlen($Da['joinet']) > 0)?$Da['joinnet_group']:'N');

    # 啟用 Anicam Live  : 預設為不啟用Anicam Live
    define('anicam',(strlen($Da['anicam']) > 0)?$Da['anicam']:DEFAULT_anicam);

    # 定義使用 Anicam 的 Media Server
    define('MMS_Server',(strlen($Da['MMS_Server']) > 0)?$Da['MMS_Server']:DEFAULT_MMS_Server);

    # 定義使用 Anicam 的 Media Server port
    define('MMS_Server_port',(strlen($Da['MMS_Server_port']) > 0)?$Da['MMS_Server_port']:DEFAULT_MMS_Server_port);

    # 白板系統設定 預設為不啟用
    define('White_Board',(strlen($Da['White_Board']) > 0)?$Da['White_Board']:DEFAULT_White_Board);

    # 語音討論版 預設為不啟用
    define('Voice_Board',(strlen($Da['Voice_Board']) > 0)?$Da['Voice_Board']:DEFAULT_Voice_Board);

    # 系統可以使用的語系
    define('sysAvailableChars', strlen($Da['sysAvailableChars']) > 0 ? $Da['sysAvailableChars'] : DEFAULT_SYS_AVAILABLE_CHARS);

    # 使用者帳號 : 定義有哪些使用者可以使用常數定義檔，如須定義超過一個使用者，請用半形逗號隔開。
    // define('Access_constant',(strlen($Da['Access_constant']) > 0)?$Da['Access_constant']:DEFAULT_Access_constant);

    # breez live constant
    define('breeze',$Da['breeze']);
    define("BREEZE_LOGIN",str_replace('(at)','@',$Da['BREEZE_LOGIN']));
    define("BREEZE_PASSWORD",$Da['BREEZE_PASSWORD']);
    define("BREEZE_ACCESSKEY",$Da['BREEZE_ACCESSKEY']);
    define("BREEZE_SERVER_ADDR",$Da['BREEZE_SERVER_ADDR']);
    define("BREEZE_USER_GROUP",$Da['BREEZE_USER_GROUP']);
    define("BREEZE_SCHOOL_ID",$Da['BREEZE_SCHOOL_ID']);
    define("BREEZE_WM_MEETING_FOLDER_ID",$Da['BREEZE_WM_MEETING_FOLDER_ID']);   //永久會議
    define("BREEZE_WM_MEETING_FOLDER_ID1",$Da['BREEZE_WM_MEETING_FOLDER_ID1']);     //臨時性會議

    # 是否以學分數為加權數 預設 是
    define('Grade_Calculate',(strlen($Da['Grade_Calculate']) > 0)?$Da['Grade_Calculate']:DEFAULT_grade_calculate);

    // 是否使用 Captcha 圖形檢核
    define('sysEnableCaptcha', $Da['sysEnableCaptcha'] ? true : false);

    // 是否使用 SCORM 3S 編輯
    define('sysEnable3S', $Da['sysEnable3S'] ? true : false);

    // 是否使用行動 APP
    define('sysEnableApp', $Da['sysEnableApp'] ? true : false);

    // 是否使用 APP Server Push
    define('sysEnableAppServerPush', $Da['sysEnableAppServerPush'] ? true : false);

    // 是否使用 課程圖片設定(教師可用)
    define('sysEnableAppCoursePicture', $Da['sysEnableAppCoursePicture'] ? true : false);

    // 是否使用 行動測驗模組
    define('sysEnableAppCourseExam', $Da['sysEnableAppCourseExam'] ? true : false);

    // 是否使用 APP Logo 自行置換
    define('sysEnableAppBackgroundLogo', $Da['sysEnableAppBackgroundLogo'] ? true : false);

    // 是否使用 行動問卷模組
    define('sysEnableAppQuestionnaire', $Da['sysEnableAppQuestionnaire'] ? true : false);

    // 是否使用 愛上互動模組
    define('sysEnableAppISunFuDon', $Da['sysEnableAppISunFuDon'] ? true : false);

    // Websocket 的網址
    define('sysWebsocketHost', $Da['sysWebsocketHost']);

    // APP iOS ID
    define('sysAppIosId', $Da['sysAppIosId']);

    // APP Android ID
    define('sysAppAndroidId', $Da['sysAppAndroidId']);

    // APP iOS store url
    define('sysAppIosUrl', $Da['sysAppIosUrl']);

    // APP Android store url
    define('sysAppAndroidUrl', $Da['sysAppAndroidUrl']);

    // 是否自動產生教師個人教材分享庫
    define('sysAutoGenContentDB', $Da['sysAutoGenContentDB'] ? true : false);

    $sch_sql_lang = 'select language from WM_school where school_host="' . $_SERVER['HTTP_HOST'] . '"';
    $sysConn->Execute('use ' . sysDBname);
    $school_lang = $sysConn->GetOne($sch_sql_lang);
    define('sysDefaultLang' , $school_lang);

    // 是否啟用 LCMS
    define('sysLcmsEnable', $Da['sysLcmsEnable'] ? true : false);

    // LCMS 的網址
    define('sysLcmsHost', $Da['sysLcmsHost']);

    // 是否啟用Mooc
    define('sysEnableMooc', (strlen($Da['sysEnableMooc']) > 0) ? intval($Da['sysEnableMooc']) : DEFAULT_ENABLE_MOOC);

    // 是否常駐側邊欄
    define('always_show_sidebar', $Da['show_sidebar'] ? true : false);

    // 是否為獨立校
    define('is_independent_school', $Da['is_independent'] ? true : false);

    // 是否為入口網校
    define('is_portal_school', $Da['is_portal'] ? true : false);

    // 是否啟用學習快通車
    define('enableQuickReview', $Da['enableQuickReview'] ? true : false);

    // 開課者是否直接預設為老師
    define('openerDefaultTea', $Da['openerDefaultTea'] ? true : false);

    // 是否開啟直播服務
    define('enableLiveService', $Da['enableLiveService'] ? true : false);

    // MASTER 常數
    // Portal 學校ID
    define('portal_school_id', $masterDa['portal_school'] ? intval($masterDa['portal_school']) : 10000);

    // 是否啟用付費選項
    define('enablePaid', $masterDa['enablePaid'] ? true : false);

	// 是否為多台主機 (wm3update)
    define('EnableMulitServer', $Da['EnableMulitServer'] ? true : false);
	define('MulitServer', $Da['MulitServer_content']);
    // 是否為多台主機 (wm3update)
	
    unset($sch_sql, $school_id, $Da, $account_rule, $masterDa);
    // 常數定義檔 END

    // 系統可使用語系
    $sysAvailableChars = explode(',', sysAvailableChars);


    // 常數定義檔 BEGIN

    function removeUnAvailableChars(&$chars)
    {
        if (is_array($chars) && count($chars))
        foreach(array_diff(array_keys($chars),$GLOBALS['sysAvailableChars']) as $k) {
                    // 「請選擇」選項，由於學校設定下拉選單需要出現，因此不能刪除，因為有可能常數定義簡體中文，但學校設定正體中文，若沒出現「請選擇」選項，畫面上會顯示簡體中文，但實際資料庫記錄正體中文，造成誤會
                    if (empty($k) === false) {unset($chars[$k]);}
                }
    }

    // =======================  系統變數宣告段 end  =======================

    // =======================  函數物件宣告段 begin  =======================

    // 檢查 school_id ticket
    function chkSchoolId($table){
        global $sysConn, $_COOKIE, $_SERVER;
        // 反單引號會造成判斷錯誤，先移除
        $table = str_replace("`", "", $table);

        if (!preg_match('/^\w+$/', $table))
        {
            if (preg_match('/\b(WM_\w+)/', $table, $regs))
                $table = $regs[1];
            else
                return;
        }

        if (in_array($table, array( 'WM_all_account', 'WM_auth_ftp','WM_manager', 'WM_school',
                                    'WM_sch4user', 'WM_prelogin', 'CO_mooc_account', 'CO_all_course',
                                    'CO_all_group', 'CO_all_major', ' CO_course_install', 'CO_school','WM_persist_login')))
        {
            ($sysConn->GetOne('select DATABASE()') != sysDBname) AND $sysConn->Execute('use ' . sysDBname);
        }
        elseif (isset($_COOKIE['school_hash']))
        {
            $sid = substr($_COOKIE['school_hash'],17,5);
            $skey = md5($_SERVER['HTTP_HOST'] . $sid);
            if ((substr($skey,0,17) . $sid . substr($skey,-10)) != $_COOKIE['school_hash'])
                die('Illegal school_hash value !');
            if (!defined('sysDBschool'))
                define('sysDBschool', sysDBprefix . $sid);
            if ($_SERVER['REMOTE_ADDR'] == '127.0.0.2')
            {
                ($sysConn->GetOne('select DATABASE()') != $GLOBALS['db']) AND $sysConn->Execute('use ' . $GLOBALS['db']);
            }else {
                ($sysConn->GetOne('select DATABASE()') != sysDBschool) AND $sysConn->Execute('use ' . sysDBschool);
            }
        }
    }

    // 取單一 table 之單一 record
    // 傳回陣列
    function dbGetStSr($table, $fields, $where, $fetchMode=false)
    {
        return _dbGetSome('GetRow',$table, $fields, $where, $fetchMode);
    }

    // 取單一 table 之多 record
    // 傳回 RecordSet
    function dbGetStMr($table, $fields, $where, $fetchMode=false){
        global $sysConn, $ADODB_FETCH_MODE;

        chkSchoolId($table);
        $keepMode = $ADODB_FETCH_MODE;
        if ($fetchMode) $ADODB_FETCH_MODE = $fetchMode;
        if (preg_match('/\slimit\s+([0-9]+)\s*(,\s*([0-9]+)\s*)?$/i', $where, $regs))
        {
            if ($regs[3])
                $ret = $sysConn->SelectLimit(sprintf('SELECT /*! SQL_SMALL_RESULT */ %s FROM %s WHERE %s',
                                                     $fields, $table, str_replace($regs[0], '', $where)
                                                    ), $regs[3], $regs[1]
                                            );
            else
                $ret = $sysConn->SelectLimit(sprintf('SELECT /*! SQL_SMALL_RESULT */ %s FROM %s WHERE %s',
                                                     $fields, $table, str_replace($regs[0], '', $where)
                                                    ), $regs[1]
                                            );
        }
        else
            $ret = $sysConn->Execute("SELECT $fields FROM $table WHERE $where");
        $ADODB_FETCH_MODE = $keepMode;
        return $ret;
    }

    function _dbGetSome($method, $table, $fields, $where, $fetchMode=false)
    {
        global $sysConn, $ADODB_FETCH_MODE;

        chkSchoolId($table);
        $keepMode = $ADODB_FETCH_MODE;
        if ($fetchMode) $ADODB_FETCH_MODE = $fetchMode;
        $result = $sysConn->$method("select $fields from $table where $where");
        $ADODB_FETCH_MODE = $keepMode;
        return $result;
    }

    function dbGetOne($table, $fields, $where, $fetchMode=ADODB_FETCH_NUM)
    {
        return _dbGetSome('GetOne',$table, $fields, $where, $fetchMode);
    }

    function dbGetCol($table, $fields, $where)
    {
        return _dbGetSome('GetCol',$table, $fields, $where, ADODB_FETCH_NUM);
    }

    function dbGetRow($table, $fields, $where, $fetchMode=ADODB_FETCH_ASSOC)
    {
        return _dbGetSome('GetRow',$table, $fields, $where, $fetchMode);;
    }

    function dbGetAssoc($table, $fields, $where, $fetchMode=ADODB_FETCH_ASSOC)
    {
        return _dbGetSome('GetAssoc',$table, $fields, $where, $fetchMode);
    }

    function dbGetAll($table, $fields, $where, $fetchMode=ADODB_FETCH_ASSOC)
    {
        return _dbGetSome('GetAll',$table, $fields, $where, $fetchMode);
    }

    function dbSet($table, $fields, $where){
        global $sysConn;

        chkSchoolId($table);
        return $sysConn->Execute("UPDATE $table SET $fields WHERE $where");
    }

    function dbNew($table, $fields, $values){
        global $sysConn;

        chkSchoolId($table);
        return $sysConn->Execute("INSERT INTO $table ($fields) values ($values)");
    }

    function dbDel($table, $where){
        global $sysConn;

        chkSchoolId($table);
        return $sysConn->Execute("DELETE FROM $table WHERE $where");
    }

    /**
     * 自動取得 syslog 的 Table
     * @return string $table : 表格
     **/
    function getSysLogTable() {
       global $sysSession, $_SERVER;

        $table = 'WM_log_others';

        if (!empty($sysSession->env)) {
            switch ($sysSession->env) {
                case 'academic' : $table = 'WM_log_manager';   break;
                case 'direct'   : $table = 'WM_log_director';  break;
                case 'teach'    : $table = 'WM_log_teacher';   break;
                case 'learn'    : $table = 'WM_log_classroom'; break;
                default:
                    $table = 'WM_log_others';
            }
        } else {
            if (strpos($_SERVER['PHP_SELF'], '/learn/') === 0) {
                $table = 'WM_log_classroom';
            } else if (strpos($_SERVER['PHP_SELF'], '/teach/')    === 0) {
                $table = 'WM_log_teacher';
            } else if (strpos($_SERVER['PHP_SELF'], '/direct/')   === 0) {
                $table = 'WM_log_director';
            } else if (strpos($_SERVER['PHP_SELF'], '/academic/') === 0) {
                $table = 'WM_log_manager';
            } else {
                $table = 'WM_log_others';
            }
        }
        return $table;
    }

    /**
     * 取得最底層 IP
     */
    function wmGetUserIp()
    {
        static $user_ip;

        if (!isset($user_ip))
        {
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $r = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (empty($r) || preg_match('/[^\s\d.,]/', $r)) return $user_ip;

            $temparyip = preg_split('/\s*,\s*/', $r, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($temparyip as $ip)
                if (preg_match('/^(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])(\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])){3}$/', $ip) &&
                    strpos($ip, '10.')      !== 0 &&
                    strpos($ip, '192.168.') !== 0 &&
                    strpos($ip, '172.16.')  !== 0)
                {
                    $user_ip = $ip;
                    break;
                }
        }
        return $user_ip;
    }

    /**
     * 設定並取得 User Agent 的編號
     * @param string $str : usre agent 的資料
     * @return integer 編號
     *     false : 失敗
     **/
    function getUserAgent($str) {
        global $sysConn;

        $val = trim($str);
        $RS = dbGetStSr('WM_log_userAgent', 'agent_id', "agent_detail='{$val}'");
        if (count($RS) > 0) return $RS['agent_id'];
        dbNew('WM_log_userAgent', 'agent_detail', "'{$val}'");
        if ($sysConn->Affected_Rows() > 0) return $sysConn->Insert_ID();
        else return false;
    }

    /**
     * 紀錄訊息
     * @param integer $function_id : 功能編號
     * @param integer $department_id : course_id 或 class_id 或 school_id ...etc
     * @param integer $instance : board_id 或 exam_id ... etc
     * @param integer $result_id   : 錯誤編號
     * @param string  $environment : 環境
     *     auto      : 由函數自行判斷
     *     classroom : 教室
     *     teacher   : 老師
     *     director  : 導師
     *     manager   : 管理者
     * @param string  $script_name : 程式名稱
     * @param string  $note        : 備註
     * @param string  $user        : 帳號
     * @return string : 紀錄在哪個表格
     *     boolean false : 失敗
     **/
    function wmSysLog($function_id,$department_id,$instance ,$result_id, $environment='auto', $script_name='', $note='', $user='') {
        global $sysSession, $sysConn, $_SERVER;

        $fid     = $function_id;        // 功能編號
        if (empty($fid)) $fid = $sysSession->cur_func;    // 假如沒有指定功能編號，就由 SysSession 取得
        if (empty($user))$user= $sysSession->username;    // 假如沒有指定帳號，就由SysSession 取得
        $rid     = $result_id;                  // 錯誤編號
        $note    = trim($note);                 // 附記
        $address = wmGetUserIp();               // user IP
        $headers = apache_request_headers();
        $agent   = $headers['User-Agent'];      // User Agent
        $agentID = getUserAgent($agent);        // User Agent ID
        if ($agentID === false) return false;
        $scname  = trim($script_name);          // 記錄程式名
        if (empty($scname)) $scname = $_SERVER['PHP_SELF'];

        switch ($environment) {
            // 由函數自行判斷
            case 'auto'     : $table = getSysLogTable();   break;
            // 教室
            case 'classroom': $table = 'WM_log_classroom'; break;
            // 老師
            case 'teacher'  : $table = 'WM_log_teacher';   break;
            // 導師
            case 'director' : $table = 'WM_log_director';  break;
            // 管理者
            case 'manager'  : $table = 'WM_log_manager';   break;
            default:
                $table   = 'WM_log_others';
        }

        if (! isset($department_id)) $department_id = 0;
        if (! isset($instance)) $instance = '0';

        $field   = 'function_id, username, log_time, department_id,instance,result_id, note, remote_address, user_agent, script_name';
        dbNew($table, $field, "'{$fid}', '{$user}', NOW(),{$department_id},'{$instance}','{$rid}', '{$note}', '{$address}', {$agentID}, '{$scname}'");
        if ($sysConn->Affected_Rows() > 0) return str_replace('WM_log_', '', $table);
        else return false;
    }

    /**
     * make_seed()
     *     建立亂數種子
     * @return float 亂數種子
     **/
    function make_seed() {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
    }

    /**
     * setTicket()
     *     設定車票
     **/
    function setTicket() {
        global $_COOKIE, $sysSession;
        // 檢查 php 的版本是不是 4.2.0 以上(含)，若不是則需要設定亂數種子
        if (version_compare(phpversion(), "4.2.0", "<")) {
            srand(make_seed());
        }

        $sysSession->ticket = rand(100000, 999999);
        dbSet('WM_session', "ticket='{$sysSession->ticket}'", "idx='{$_COOKIE['idx']}'");
    }

    /**
     * 對 guest 的限制
     *
     * @param   array   $SCH    學校設定資料
     */
    function guestLimitation($SCH)
    {
        global $http_secure;
        // 自己的 cron 不必限
        if (PHP_SAPI == 'cli' ||
            $_SERVER['REMOTE_ADDR'] == $_SERVER["SERVER_ADDR"] ||
            $_SERVER['REMOTE_ADDR'] == '127.0.0.1')
            return;

        // 不允許 Guest 存取部分路徑的資料
        if (eregi('^/(academic|teach|direct)/.*$', $_SERVER['REQUEST_URI']) ||
            (dirname($_SERVER['SCRIPT_NAME']) == '/online' &&
             !in_array(basename($_SERVER['SCRIPT_NAME']), array('session.php', 'online.php'))) // 不允許 guest 使用線上傳訊
           ) {
            // header('Status: 403 Forbidden');
            header('Location: /');
            die('<h1>Forbidden</h1>');
        }

        // 檢查是否已超過系統上線的人數限制
        if ((defined('sysMaxConcurrentUser'))&&(sysMaxConcurrentUser != 0))  // 0表示無限制
        {
            //避免不斷呼叫max_concurrent.php
            if (($_SERVER['PHP_SELF'] != '/sys/max_concurrent.php')&&($_SERVER['PHP_SELF'] != '/index.php'))
            {
                list($now_courrent_num) = dbGetStSr('WM_session','count(*)','`chance`=0', ADODB_FETCH_NUM);
                if ($now_courrent_num > sysMaxConcurrentUser)
                {
                    header("Location: /sys/max_concurrent.php");
                    exit;
                }
            }
        }

        // 檢查允不允許 Guest 登入與 Guest 登入人數
        $path_parts = pathinfo($_SERVER['REQUEST_URI']);
        $ary = array('/', '/mooc', '/mooc/controllers', '/lib', '/lib/login', '/sys', '/sys/about', '/sys/door', '/sys/reg', '/learn/news', '/learn/mycourse', '/forum', '/info', '/xmlapi', '/lib/phpqrcode');
        if (!in_array($path_parts['dirname'], $ary) && !defined('forGuestQuestionnaire')) {
            // 檢查允不允許 Guest 登入
            if ($SCH['guest'] != 'Y') {
                // 移除舊的 sysSession
                dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
                // 清除 Cookie
                $_COOKIE['idx'] = '';
                setcookie('idx', '', 0, '/', '', $http_secure);
                echo <<< BOF
<html>
<head>
<script language="JavaScript" type="text/javascript">
    window.onload = function () {
        parent.location.replace("/sys/guest_deny.php");
    };
</script>
</head>
<body>
    <div>Not Allow Guest, return home...</div>
</body>
</html>
BOF;
                die();
            }
            // Guest 登入人數
            list($cnt) = dbGetStSr('WM_session', 'count(*)', "username='guest'", ADODB_FETCH_NUM);
            if (!empty($SCH['guestLimit']) && $cnt > $SCH['guestLimit']) {
                // 移除舊的 sysSession
                dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
                // 清除 Cookie
                $_COOKIE['idx'] = '';
                setcookie('idx', '', 0, '/', '', $http_secure);
                echo <<< BOF
<html>
<head>
<script language="JavaScript" type="text/javascript">
    window.onload = function () {
        parent.location.replace("/sys/guest_limit.php");
    };
</script>
</head>
<body>
    <div>Over Guest Limit, return home...</div>
</body>
</html>
BOF;
                die();
            }
        }
    }

    // SESSION 物件宣告
    class SessionInfo{
        // 獨立欄位
        var $username;
        var $realname;
        var $email;
        var $homepage;
        var $school_id;
        var $school_name;
        var $course_id;
        var $course_name;
        var $class_id;
        var $class_name;
        var $role;
        var $room_id;
        var $ip;
        var $cur_func;
        var $ticket;
        var $board_name;
        var $q_path;
        var $news_nodes;    /* 最新消息公開節點 */
        var $board_ownerid; /* 討論板 owner */
        var $board_ownername;   /* 討論板 owner */

        // 複合欄位
        var $lang;
        var $theme;
        var $env;
        var $msg_serial;   /* 訊息中心：目前讀取哪封訊息 */
        var $board_id;
        var $sortby;
        var $page_no;
        var $post_no;
        var $b_right;   /* 討論板一般區權限 */
        var $q_sortby;
        var $q_page_no;
        var $q_post_no;
        var $q_right;   /* 討論板精華區權限 */
        var $news_board;    /* 討論板是否為最新消息類型(不能刊登)   */
        var $board_readonly;    /* 討論板是否為唯讀類型(不能刊登)   */
        var $board_qonly;   /* 討論板是否為只有精華區類型   */
        var $goto_label;    /* 切換選單到哪一個項目，使用選單編號 */

        // 建構子
        function __construct(){
            global $sysConn,$http_secure;

            $this->b_right = false;
            $this->q_right = false;
            $this->news_nodes = '';

            $SCH = dbGetStSr('WM_school', 'school_id, school_name, language, theme, guest, guestLimit, school_mail', "school_host='{$_SERVER['HTTP_HOST']}'");
            if (!is_array($SCH)) die('No school bind ' . $_SERVER['HTTP_HOST']);
            // Bug#1493
            // 抓取學校常數定義中的語系 -- Begin by Small 2006/10/31
            $sch_sql = 'select school_id from WM_school where school_host="' . $_SERVER['HTTP_HOST'] . '"';
            $sysConn->Execute('use ' . sysDBname);
            $school_id = $sysConn->GetOne($sch_sql);
            $conf = getConstatnt($school_id);
            if (array_key_exists('sysAvailableChars', $conf)) {
                $avln = explode(',', $conf['sysAvailableChars']);
            } else {
                // 使用預設值
                $avln = array('Big5', 'GB2312', 'en', 'EUC-JP', 'user_define');
            }

            @ini_set('sendmail_from', $SCH['school_mail'] ? $SCH['school_mail'] : sysWebMaster);
            // 抓取學校常數定義中的語系 -- End by Small 2006/10/31

            // 參考 BugNo.1124
                        // 顯示的語系
                        // 沒有點選首頁語系
                        if ($_GET['lang'] == '') {
                            // cookie沒有值，取學校設定
                            if ($_COOKIE['wm_lang'] == '') {
                                $cookie_lang = $SCH['language'];
                                setcookie('wm_lang', $cookie_lang, time() + 86400, '/', '', $http_secure);
                            } else {
                                // 有值取cookie值
                                if (!in_array($_COOKIE['wm_lang'], $avln)) {
                                    $cookie_lang = $SCH['language'];
                                } else {
                                    $cookie_lang = $_COOKIE['wm_lang'];
                                }
                            }
                        } else {
                            // 依照首頁點選的語系顯示
                            if (strpos($_SERVER['PHP_SELF'], '/academic/dbcs/') !== 0 ) {
                                $cookie_lang = trim($_GET['lang']);
                                // Bug#1493
                                // 如果從網址列輸入語系，而語系不在常數定義中，重新導回index.php by Small 2006/10/31
                                if (!in_array($cookie_lang, $avln)) {
                                    $cookie_lang = $SCH['language'];
                                    header('Location: /index.php');
                                    exit;
                                }
                                setcookie('wm_lang', $cookie_lang, time() + 86400, '/', '', $http_secure);
                            } else {
                                $cookie_lang = $SCH['language'];
                            }
                        }


            /* [MOOC](B) #57892 另外儲存 idx 便於 SSO 到其他學校及內容商  2014/12/19 By Spring */
            // 判斷有無 sIdx，如果有則判斷從哪間學校來及學校類型
            if (empty($_COOKIE['idx']) || strlen($_COOKIE['idx']) != 32) {
                if(!empty($_COOKIE['sIdx']) && strlen($_COOKIE['sIdx']) == 37) {
                    // 解析 ssoIdx
                    $fromSch = substr($_COOKIE['sIdx'], 0, 5);
                    $fromIdx = substr($_COOKIE['sIdx'], 5, 32);
                    // 取得學校型態
                    // $fromDa = getConstatnt($fromSch);
                    $currDa = getConstatnt($SCH['school_id']);
                    if ($currDa["is_portal"] === "1") {
                        // 入口網校 (or MOOCs)
                        $currSch = 0;
                    } else {
                        if ($currDa["is_independent"] === "0") {
                            // 內容商
                            $currSch = 2;
                        } else {
                            // 獨立校 (or本校)
                            $currSch = 1;
                        }
                    }
                    // 當前學校為入口網校或是內容商時，將登入校的 session 複製過來
                    if ($currSch == 0 || $currSch == 2) {
                        $fromDB = sysDBprefix . $fromSch;
                        // 如果確定此session有存在
                        if (dbGetOne($fromDB . ".WM_session", "count(idx)", "idx='{$fromIdx}'") > 0) {
                            $rsUser = dbGetOne($fromDB . ".WM_session", "username", "idx='{$fromIdx}'");
                            // 驗證並同步使用者 (因 sysSession 無法使用，改寫 login.inc line: 269)
                            /*
                            if (!(dbGetOne('WM_user_account', 'count(*) as ct ', "username='" . $rsUser . "'") > 0) && ($rsUser != 'root')) {
                                // 如果是入口網校或者內容商，則依據開放註冊與否同步使用者
                                if ($currSch == 0 || $currSch == 2) {
                                    switch(dbGetOne('WM_school', 'canReg', "school_id='" . $SCH['school_id'] . "'", ADODB_FETCH_NUM))
                                    {
                                        case "Y":
                                                dbNew('WM_sch4user', "school_id, username, reg_time, login_times", "'{$SCH['school_id']}', '{$rsUser}', NOW(), 1");
                                                $sysConn->Execute("INSERT INTO " . sysDBprefix . "{$SCH['school_id']}.WM_user_account SELECT * FROM " .
                                                                    sysDBname . ".WM_all_account where " . sysDBname . ".WM_all_account.username='{$rsUser}'");
                                                dbSet("WM_user_account", "enable='Y'", "username='{$rsUser}'");
                                                break;
                                        case "N":
                                                break;
                                        case "C":
                                                dbNew('WM_sch4user', "school_id, username, reg_time, login_times", "'{$SCH['school_id']}', '{$rsUser}', NOW(), 1");
                                                $sysConn->Execute("INSERT INTO " . sysDBprefix . "{$SCH['school_id']}.WM_user_account SELECT * FROM " .
                                                                    sysDBname . ".WM_all_account where " . sysDBname . ".WM_all_account.username='{$rsUser}'");
                                                dbSet("WM_user_account", "enable='N'", "username='{$rsUser}'");
                                                break;
                                    }
                                }

                            }
                             */
                            // 判斷帳號為可使用，取入口網校
                            // if (dbGetOne(sysDBprefix .portal_school_id.".WM_user_account", "enable", "username='{$rsUser}'") == 'Y') {
                            // 判斷帳號為可使用
                            if (dbGetOne("WM_user_account", "enable", "username='{$rsUser}'") == 'Y') {
                                // 複製原 session 到新登入學校
                                $sysConn->Execute("INSERT INTO " . sysDBprefix . "{$SCH['school_id']}.WM_session SELECT * FROM " .
                                                    sysDBprefix . $fromSch . ".WM_session where " . sysDBprefix . $fromSch . ".WM_session.idx='{$fromIdx}'");
                                dbSet("WM_session", "school_id='{$SCH['school_id']}'", "username='{$rsUser}'");
                                // 儲存 cookie(idx)
                                $_COOKIE['idx'] = $fromIdx;
                setcookie('idx', $fromIdx, 0, '/', '', $http_secure);
                            }
                        }
                    }
                }
            }
            /* [MOOC](E) #57892 */
            // 如果沒有 session key (idx)
            if (empty($_COOKIE['idx']) || strlen($_COOKIE['idx']) != 32) {
                guestLimitation($SCH);
                // 產生 4 個 session 值
                if (!defined('sysDBschool'))
                    define('sysDBschool', sysDBprefix . $SCH['school_id']);

                $skey = md5($_SERVER['HTTP_HOST'].$SCH['school_id']);
                $_COOKIE['school_hash'] = substr($skey, 0, 17) . $SCH['school_id'] . substr($skey, -10);
                setcookie('school_hash', $_COOKIE['school_hash'], time()+86400, '/', '', $http_secure);

                if (
                    (
                        $_SERVER['REQUEST_URI'] == '/mooc/index.php' ||
                        $_SERVER['SCRIPT_URL'] == '/login.php' ||
                        $_SERVER['SCRIPT_URL'] == '/mooc/irs/check.php' ||
                        eregi('^/learn/.*$', $_SERVER['REQUEST_URI']) ||
                        basename($_SERVER['SCRIPT_FILENAME']) == 'rd.php'
                    ) &&
                    !eregi('^/learn/newcalendar/.*$', $_SERVER['REQUEST_URI'])
                ){
                    //查看所有學校行事曆不算進入學習環境 不用紀錄idx
                    $User = array();
                    $User['username']   = 'guest';
                    $User['first_name'] = 'Guest';
                    $User['last_name']  = '';
                    $User['email']      = '';
                    $User['homepage']   = '';
                    $User['language']   = $cookie_lang;
                    // Redmine#3368 By Small 2012/07/23
                    // $_COOKIE['idx'] = $this->init($User);
                    $idx = $this->init($User);
                                        setcookie('idx', $idx, 0, '/', '', $http_secure);
                    $_COOKIE['idx'] = $idx;
                } else {
                    $this->username    = 'guest';
                    $this->realname    = 'Guest';
                    $this->theme       = $SCH['theme'];

                    // 參考 BugNo.1124
                    $this->lang        = $cookie_lang;

                    $this->school_id   = $SCH['school_id'];
                    $this->school_name = $SCH['school_name'];
                    return;
                }
                // header('Location: ' . $_SERVER['REQUEST_URI']);
                // die();
            }


            // 有 session key
            chkSchoolId('WM_session');
            $sessinfo = dbGetStSr('WM_session', '/*!40001 SQL_NO_CACHE */ *', "idx='{$_COOKIE['idx']}'");

            if ($sessinfo){
                if (empty($sessinfo['username']) || ($sessinfo['username'] == 'guest')) {
                    guestLimitation($SCH);
                }

                $this->username     = $sessinfo['username'];
                $this->realname     = $sessinfo['realname'];
                $this->email        = $sessinfo['email'];
                $this->homepage     = $sessinfo['homepage'];
                $this->school_id    = $sessinfo['school_id'];
                $this->school_name  = $sessinfo['school_name'];
                $this->course_id    = $sessinfo['course_id'];
                $this->course_name  = $sessinfo['course_name'];
                $this->class_id     = $sessinfo['class_id'];
                $this->class_name   = $sessinfo['class_name'];
                $this->role         = $sessinfo['role'];
                $this->room_id      = $sessinfo['room_id'];
                $this->ip           = $sessinfo['ip'];
                $this->cur_func     = $sessinfo['cur_func'];
                $this->ticket       = $sessinfo['ticket'];
                $this->board_name   = $sessinfo['board_name'];
                $this->q_path       = $sessinfo['q_path'];
                $this->news_nodes   = $sessinfo['news_nodes'];
                $this->board_ownerid    = $sessinfo['board_ownerid'];
                $this->board_ownername  = $sessinfo['board_ownername'];
                $this->goto_label   = $sessinfo['goto_label'];
                // 用 eval 去產生一個欄位裡所包含的 session 值

                eval('$this->' . str_replace("\t", ';$this->', $sessinfo['session']));

                                // mars建議，由於專案仍須在首頁使用URL設定語系，故還原此段
                if (!empty($cookie_lang) && ($this->lang != $cookie_lang)) {
                    $this->lang = $cookie_lang;
                }

//                                // 尚未登入，依cookie值，至於cookie值，在SessionInfo()有給予了
//                                if ($this->username === 'guest') {
//                                    $this->lang = $cookie_lang;
//                                }

                //if (preg_match('/^(\$this->\w+\s*=\s*[^\s]+;)+$/sU', $temp_ss)) eval($temp_ss);
                if (!defined('sysDBschool'))
                    define('sysDBschool', sysDBprefix . $this->school_id);
            } else {
                // Cookie 遺失或是重複登入被踢除
                // 刪除 Session
                if ($_SERVER['REMOTE_ADDR'] == '127.0.0.2')
                {
                    $sysConn->Execute('use ' . $GLOBALS['db']);
                }else{
                    $sysConn->Execute('use ' . sysDBschool);
                }
                dbDel('WM_auth_ftp', "userid='{$sysSession->username}'");
                // dbDel('WM_auth_samba', "username='{$sysSession->username}'");
                dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
                // 清除 Cookie
                                setcookie('idx', '', 0, '/', '', $http_secure);
                // 導到連線逾時的畫面
                if($_SERVER['PHP_SELF'] != '/connect_lost.php')
                {
                    echo <<< BOF
<html>
<head>
<script language="JavaScript" type="text/javascript">
    window.onload = function () {
        parent.location.replace("/connect_lost.php");
    };
</script>
</head>
<body>
    <div>connect lost....</div>
</body>
</html>
BOF;

                    die();
                }
            }
        }

        // 回存 Session 資料

        function restore(){
            dbSet('WM_session',
                  sprintf('course_id=%d, cur_func="%s",q_path="%s",news_nodes="%s",board_ownerid="%s",board_ownername="%s",room_id="%s",session="%s"',
                      intval($this->course_id),$this->cur_func,$this->q_path,$this->news_nodes,$this->board_ownerid,$this->board_ownername,$this->room_id,
                          vsprintf("lang='%s'\ttheme='%s'\tenv='%s'\tmsg_serial=%d\tboard_id=%d\t" .
                                   "sortby='%s'\tpage_no=%d\tpost_no=%d\tb_right='%s'\t" .
                                   "q_sortby='%s'\tq_page_no=%d\tq_post_no=%d\tq_right='%s'\t".
                                   "news_board=%d\tboard_readonly=%d\tboard_qonly=%d\tgoto_label='%s';",
                                   array_pad(array_slice(get_object_vars($this), 20), 16, '')
                                  )
                         ),
                  "idx='{$_COOKIE['idx']}'");
        }

        // 清除 Session 資料
        function clean(){
            dbSet('WM_session', "session='lang=\"$this->lang\"\ttheme=\"$this->theme\";'", "idx='{$_COOKIE['idx']}'");
        }

        // 初始化 Session
        function init($User){
            global $sysConn,$http_secure;

            $sysConn->Execute('use ' . sysDBname);
            $RS = dbGetStSr('WM_school', 'school_id, school_name, language, theme', "school_host='{$_SERVER['HTTP_HOST']}'");
            if (empty($RS['school_id']) || empty($RS['school_name'])) die('No any school bind the server_name: '.$_SERVER['HTTP_HOST']);

            mt_srand(intval(substr(microtime(),3,6)));
            $ip = wmGetUserIp();
            /*
            $map='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            do {
                $idx = '';
                for ($i=0; $i<32; $i++) $idx .= substr($map,mt_rand(0,61),1);
                list($c)=dbGetStSr('WM_session', 'count(*)', "idx='$idx'", ADODB_FETCH_NUM);
            } while ($c);
            */

            $map='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            do {
                $idx0 = idx_prefix . $User['username'] . time();
                $idx_length = strlen($idx0);
                $diff_length = 32 - $idx_length;

                for($i = 0; $i < $diff_length ; $i++)
                {
                    $idx1 .= substr($map, mt_rand(0, 61), 1);
                }

                $idx = md5($idx0 . $idx1);
                $c = dbGetOne('WM_session', 'COUNT(*)', "idx = '{$idx}'");
            } while ($c);

            $school_name = addslashes($RS['school_name']);
            $this->lang  = empty($User['language'])?$RS['language']:$User['language'];
            $this->theme = empty($User['theme'])   ?$RS['theme']   :$User['theme'];

            // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
            $realname = checkRealname($User['first_name'],$User['last_name']);
            // 避免被 ' "  造成 SQL 指令錯誤 (Add by lst) (Begin)
            $realname = addslashes($realname);
            $school_name = addslashes($school_name);
            $User['username'] = addslashes($User['username']);
            $User['email'] = addslashes($User['email']);
            $User['homepage'] = addslashes($User['homepage']);
            // 避免被 ' "  造成 SQL 指令錯誤 (Add by lst) (End)

            dbNew('WM_session', 'idx,username,realname,email,homepage,school_id,school_name,ip,session',
                               "'$idx', '{$User['username']}', '$realname ', '{$User['email']}', ".
                               "'{$User['homepage']} ', {$RS['school_id']}, '$school_name ', '$ip', ".
                               "'lang=\"$this->lang\"\ttheme=\"$this->theme\";'");
                        setcookie('idx', $idx, 0, '/', '', $http_secure);
            if ($_SERVER['REMOTE_ADDR'] == '127.0.0.2')
            {
                $sysConn->Execute('use ' . $GLOBALS['db']);
            }else{
                $sysConn->Execute('use ' . sysDBschool);
            }
            return $idx;
        }

        // 重載 Session
        function refresh(){
            $this->SessionInfo();
        }
    }

    // 把準備要當作 SQL like 比對的資料，做 % 跟 _ 的 escape
    function escape_LIKE_query_str($str){
        return preg_replace('/(%|_)/', '\\\\\1', $str);
    }

    // 轉碼函式
    function locale_conv($word){
        global $sysSession;
        return (($w = iconv($sysSession->lang, 'UTF-8', $word)) === FALSE) ? $word : $w;
    }

    /*Custom 2017-11-30 *049131 (B)*/
    function co_lang_default($datas){
        if(is_array($datas)){
            foreach ($datas as $key => $val)
            {
                if($val == "" || $val== "undefined" || $val== "--=[unnamed]=--"){
                    $datas[$key] = $datas[sysDefaultLang];
                }
            }
        }
        return $datas;
    }
    /*Custom 2017-11-30 *049131 (E)*/

    /**
     * 複合欄位搜尋函式
     * @param string $search_words : 尋找的關鍵字
     * @param string $table : 要尋找的 table 名稱
     * @param string $fields : 要尋找的欄位名稱，第一欄必為 primary key
     * @return array 找到的 records
     */
    function serialized_search($search_words, $table, $fields)
    {
        global $sysSession, $sysConn, $ADODB_FETCH_MODE;

        $matches = array();

        if (empty($search_words) || empty($table) || empty($fields)) return $matches;

        $keep = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
        $RS = dbGetStMr($table, $fields, 1);
        $ADODB_FETCH_MODE = $keep;
        if ($sysConn->ErrorNo() || $RS->RecordCount() < 1) return $matches;

        while($fields = $RS->FetchRow())
        {
            $k = array_shift($fields);
            foreach($fields as $field)
            {
                if (($contents = unserialize($field)) === FALSE)    // 不是 serialized 複合欄位
                {
                    if (strpos($field, $search_words) !== FALSE) $matches[$k] = $field;
                }
                else    // 是 serialized 複合欄位
                {
                    if (strpos($contents[$sysSession->lang], $search_words) !== FALSE) $matches[$k] = $contents[$sysSession->lang];
                }
            }
        }

        return $matches;
    }

    /**
     * 將 UTF-8 內容，轉換為 Excel 可辨識的 Unicode-16LE
     *
     * @param  string   $string   UTF-8 字串
     * @return string   Excel 可辨識的 Unicode-16LE 字串
     */
    function utf8_to_excel_unicode($string)
    {
        return chr(255) . chr(254) . mb_convert_encoding(str_replace(',', chr(9), $string), 'UTF-16LE', 'UTF-8');
    }

    /**
     * 檢查目前課程(班級)編號、環境是否正確
     */
    function checkCurrentId($data)
    {
        global $sysSession;

        /**
        MIS#23533 管理者環境→學校管理→學生環境布置 會導到 connect_lost.php
        所以補上sch_layout.php    by Small 2011/12/29
        **/
        // MIS#24410 雲科-從教師端,點討論室名稱，會出現使用者帳號與系統失去聯繫！ by Small 2012/03/14
        // 補上/learn/chat/index.php
        $except_files = array('/index.php',
                              '/connect_lost.php',
                              '/academic/index.php',
                              '/direct/index.php',
                              '/teach/index.php',
                              '/learn/index.php',
                              '/academic/sch/sch_layout.php',
                              '/learn/chat/index.php'
                             );
        if (!in_array($_SERVER['PHP_SELF'], $except_files) &&
            !preg_match('!/sysbar\.php$|^/sys/|^/online/!', $_SERVER['PHP_SELF'])
            )
        {
            switch($sysSession->env)
            {
                case 'teach' : $sysbar_id = 'c_sysbar';
                               $sel_value = intval($sysSession->course_id);
                               break;
                case 'direct': $sysbar_id = 'sysbar';
                               $sel_value = intval($sysSession->class_id);
                               break;
                default      : $sysbar_id = 's_sysbar';
                               $sel_value = intval($sysSession->course_id);
            }

            $check .= <<< BOF
<script type="text/javascript" language="javascript">
    if ((typeof(parent.{$sysbar_id}) == "object") &&
        (typeof(parent.{$sysbar_id}.document.getElementById('selcourse')) == "object") &&
        (parent.{$sysbar_id}.document.getElementById('selcourse')!= null) &&
        (parent.{$sysbar_id}.document.getElementById('selcourse').value != '{$sel_value}') &&
        (parent.{$sysbar_id}.document.getElementById('selcourse').value != 0) &&
        (parent.{$sysbar_id}.document.getElementById('selcourse').value != 10000000)
       )
    {
        parent.location.href = "/connect_lost.php";
    }

    href_ary = parent.location.pathname.split("/");
    /*APP檢視LCMS教材不用判斷路徑與SESSION關係*/
    if (((href_ary[1] == "learn") || (href_ary[1] == "teach") || (href_ary[1] == "direct") || (href_ary[1] == "academic")) &&
        (href_ary[1] != "{$sysSession->env}") &&
        ("app" != "{$sysSession->env}")
       )
    {
        parent.location.href = "/connect_lost.php";
    }
</script>
BOF;

            $data = preg_replace('!</body>!i', $check. "\n</body>", $data, 1);
        }
    }


    /**
     * 控制本頁是否鎖右鍵 (此為 ob_start 自動機制，勿自行呼叫)
     *
     * @param   string      $data       輸出網頁
     * @return  string                  加了是否防右鍵的網頁
     */
    function pageLockControl($data)
    {
        // 檢查目前課程(班級)編號、環境是否正確（PHP5不適用所以移除）
        // checkCurrentId(&$data);

        $js_file = preg_match('!/sysbar\.php$|^/learn/path/|^/learn/scorm/|_folder\.php$|_tree\.php$|_tools\.php$|_toolbar\.php$!',
                              $_SERVER['PHP_SELF']) ?
                   'disable' : 'enable';

        if (preg_match('!</body>!i', $data))
            return preg_replace('!</body>!i', '<script src="/lib/' . $js_file . '.js" type="text/javascript"></script></body>', $data, 1);
        else
            return $data . (preg_match('/<body\b/i', $data) ? ('<script src="/lib/' . $js_file . '.js" type="text/javascript"></script>') : '');
    }

    /**
     * 取得複合欄位的 locale 值，當未命名時，以第一個有取的字串輸出
     *
     * @param   string      $v          以 serialize() 處理過的多語系字串
     * @param   string      $unnamed    未命名時，輸出什麼字串
     * @return  string                  本地詞
     */
    function fetchTitle($v, $unnamed='--=[unnamed]=--')
    {
        if (is_array($v) && count($v))
            $x = $v;
        else if (($x = @unserialize($v)) === false)
            return $unnamed;

        /*Custom 2017-11-22 *049131 */
        $locale_title = trim($x[$GLOBALS['sysSession']->lang]);

        if ($locale_title == '' || $locale_title == 'undefined' || $locale_title == '--=[unnamed]=--')    // 取本語系的課名
        {
            $x = explode(chr(9), trim(implode(chr(9), $x)));    // 如果本語系課名是空的，就取第一個有名字的
            $locale_title = $x[0];
        }
        return ($locale_title == '' ? $unnamed : $locale_title);
    }

    /*
     * 以下是 PHP 5.1 版以下，四個在檔案超過2GB會出錯的函式之替代方案 (_wmErrorHandler() 是 private 函式)
     */

    /**
     * 大檔案之下，取代 fulesize() 之函式
     *
     * @param   string  $f  檔名
     * @return  int         檔案大小
     */
    function wm_filesize($f)
    {
        if (!@file_exists($f)) return false;
        if (($s = @filesize($f)) === false || $s < 0)
            return (exec('ls -dl ' . escapeshellarg($f) . " | awk '{print $5}'"));
        else
            return $s;
    }

    /**
     * 內部使用之錯誤捕捉函式 (供 wm_is_file() 及 wm_is_dir() 用)
     */
    function _wmErrorHandler($errno, $errstr, $errfile, $errline)
    {
        global $file_system_overflow;
        $file_system_overflow = true;
    }

    /**
     * 大檔案之下，取代 is_file() 之函式
     *
     * @param   string  $f  檔名
     * @return  bool        是否是檔案
     */
    function wm_is_file($f)
    {
        global $file_system_overflow;

        set_error_handler('_wmErrorHandler');
        if (($r = @is_file($f)) === false)
        {
            if ($file_system_overflow)
            {
                restore_error_handler();
                $f = escapeshellarg($f);
                return (exec("test -f $f && echo Yes") == 'Yes');
            }
        }
        restore_error_handler();
        return $r;
    }

    /**
     * 大檔案之下，取代 is_dir() 之函式
     *
     * @param   string  $f  目錄名
     * @return  bool        是否是目錄
     */
    function wm_is_dir($f)
    {
        global $file_system_overflow;

        set_error_handler('_wmErrorHandler');
        if (($r = @is_dir($f)) === false)
        {
            if ($file_system_overflow)
            {
                restore_error_handler();
                $f = escapeshellarg($f);
                return (exec("test -d $f && echo Yes") == 'Yes');
            }
        }
        restore_error_handler();
        return $r;
    }

    /**
     * 大檔案之下，取代 is_filemtime() 之函式
     *
     * @param   string  $f  檔案名
     * @return  int         最後異動時間 timestamp
     */
    function wm_filemtime($f)
    {
        global $file_system_overflow;

        set_error_handler('_wmErrorHandler');
        if (($r = @filemtime($f)) === false)
        {
            if ($file_system_overflow)
            {
                restore_error_handler();
                $f = escapeshellarg($f);
                return exec("stat -c '%Y' $f");
            }
        }
        restore_error_handler();
        return $r;
    }

    // 取資料表欄位
    function wm_gettbcols($schema, $table)
    {
        $RS = dbGetStMr('`INFORMATION_SCHEMA`.`COLUMNS`',
            '`COLUMN_NAME`',
            sprintf('`TABLE_SCHEMA` = "%s" and `TABLE_NAME` = "%s"', $schema, $table),
            ADODB_FETCH_ASSOC);

        $cols = array();
        if ($RS && $RS->RecordCount() >= 1) {
            while(!$RS->EOF){
                $cols[] = $RS->fields['COLUMN_NAME'];

                $RS->MoveNext();
            }
        }
        return $cols;
    }

    /**
     * 代換學習路徑節點的 <title>
     */
    class SyncImsmanifestTitle
    {
        var $course_id;
        var $serial;
        var $dom;
        var $xpath;

        /**
         * 建構子
         */
        function __construct($course_id=false)
        {
            global $sysSession, $ADODB_FETCH_MODE, $sysConn;

            $this->course_id = $course_id ? $course_id : (strpos($_SERVER['PHP_SELF'], '/academic/') === 0 ? $sysSession->school_id : $sysSession->course_id);

            $keep = $ADODB_FETCH_MODE;
            $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
            list($this->serial, $xmlstr) = $sysConn->GetRow('select serial,content from WM_term_path where course_id=' . $this->course_id . ' order by serial desc limit 1');
            $ADODB_FETCH_MODE = $keep;
            if (!$this->dom = @domxml_open_mem($xmlstr)) return false;
            $this->xpath = $this->dom->xpath_new_context();
        }

        /**
         *  將 serialize 的字串或已經 unserialize 的陣列，轉為以 Tab 分隔的字串
         *
         * @param   string|array    $title  serialize 的字串或已經 unserialize 的陣列
         * @return  string                  以 Tab 分隔的字串
         */
        function convToNodeTitle($title)
        {
            $new_titles = is_array($title) ? $title : unserialize($title);

            $node_title = array($new_titles['Big5']        ? $new_titles['Big5']        : '--= unnamed(zh-tw) =--',
                                $new_titles['GB2312']      ? $new_titles['GB2312']      : '--= unnamed(zh-cn) =--',
                                $new_titles['en']          ? $new_titles['en']          : '--= unnamed(en) =--',
                                $new_titles['EUC-JP']      ? $new_titles['EUC-JP']      : '--= unnamed(jp) =--',
                                $new_titles['user_define'] ? $new_titles['user_define'] : '--= unnamed(user-def) =--');
            return implode(chr(9), $node_title);
        }

        /**
         * 取代一個 instance 的 title
         *
         * @param   int     $type       節點類型 (2~7)
         * @param   int     $instance   exam_id, board_id ...
         * @param   string  $title      新 Title
         */
        function replaceTitleForImsmanifest($type, $instance, $title)
        {
            if (!method_exists($this->xpath, 'xpath_eval') ||
                !preg_match('/^[2-9]$/', $type) ||
                !preg_match('/^\w+$/',   $instance)) return false;

            if ($type == 7)
                $ret = $this->xpath->xpath_eval('/manifest/organizations//item[@identifierref=/manifest/resources/resource[@href="javascript:fetchWMinstance(' . $type . ",'" . $instance . '\')"]/@identifier]');
            else
                $ret = $this->xpath->xpath_eval('/manifest/organizations//item[@identifierref=/manifest/resources/resource[@href="javascript:fetchWMinstance(' . $type . ',' . $instance . ')"]/@identifier]');

            if (is_array($ret->nodeset) && count($ret->nodeset))
            {
                foreach ($ret->nodeset as $node)
                {
                    $child = $this->xpath->xpath_eval('./title', $node);
                    if (is_array($child->nodeset) && count($child->nodeset))
                    {
                        if ($child->nodeset[0]->has_child_nodes())
                            foreach ($child->nodeset[0]->child_nodes() as $willdrop)
                                $child->nodeset[0]->remove_child($willdrop);

                        $child->nodeset[0]->append_child($this->dom->create_text_node($title));
                    }
                }
            }
        }

        /**
         *  存回新的教材路徑
         *
         * @return  bool    true=完成：false=失敗
         */
        function restoreImsmanifest()
        {
            global $sysConn;

            if (empty($this->course_id) || empty($this->serial) || !is_object($this->dom)) return false;
            if (dbSet('WM_term_path', 'content=' . $sysConn->qstr($this->dom->dump_mem(true)), "course_id={$this->course_id} and serial={$this->serial}"))
            {
                wmSysLog(1900100200, $this->course_id , 0 , 0, 'teacher', $_SERVER['PHP_SELF'], 'update term path for update title');
                return true;
            }
            else
                return false;
        }
    }

    // =======================  函數物件宣告段 end  =======================

    // =======================  一般 SQL 宣告段 begin  =======================

    /**
     * 取得教授或選修中的課程 (會依身份而檢查開放日期與狀態)
     *
     * @param   string      $fields     欲取得的欄位
     * @param   string      $username   特定帳號。忽略則以 $sysSession->username 代替
     * @param   int         $roles      判斷的身份(旁聽生、正式生、助教、講師、教師)，預設為正式生
     * @param   string      $order      排序欄位，可多個
     * @param   string      $limit      取得位置與個數
     * @return                          若欄位是 count(*) 則傳回int；否則傳回 recordset
     *
     */
    function &dbGetCourses($fields='count(*)', $username='', $roles=0, $order=false, $limit=false)
    {
        global $sysConn, $sysSession, $sysRoles, $ADODB_FETCH_MODE;

        $username = preg_match(Account_format, $username) ? $username : $sysSession->username;
        $roles    = $roles ? $roles : $sysRoles['student'];
        $sqls = 'select ' . $fields . ' from WM_term_major AS M ' .
                'inner join WM_term_course AS C on C.course_id=M.course_id ' .
                'where M.username="' . $username . '" AND C.kind="course" AND (' .
                ($roles & $sysRoles['auditor'] ? (
                '(M.role&' . $sysRoles['auditor'] .
                ' and (C.status=1 or (C.status=2 and (isnull(C.st_begin) or C.st_begin<=CURDATE()) and (isnull(C.st_end) or C.st_end>=CURDATE()))))') : '') .
                ($roles & $sysRoles['student'] ? ( ($roles & $sysRoles['auditor'] ? ' or ' : '') .
                '(M.role&' . $sysRoles['student'] .
                ' and (C.status=1 or C.status=3 or ((C.status=2 or C.status=4) and (isnull(C.st_begin) or C.st_begin<=CURDATE()) and (isnull(C.st_end) or C.st_end>=CURDATE()))))') : '') .
                ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']) ? ( ($roles & $sysRoles['student'] ? ' or ' : '') .
                '(M.role&' . ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'])) .
                ' and (C.status between 1 and 5))') : '') .
                ')' .
                ($order && preg_match('/^(\w+|`\w+`)(\.(\w+|`\w+`))*(\s+(asc|desc))?(\s*,\s*(\w+|`\w+`)(\.(\w+|`\w+`))*(\s+(asc|desc))?)*$/i', $order) ? (' order by ' . $order) : '') .
                ($limit && preg_match('/^\d+(\s*,\s*\d+)?$/', $limit) ? (' limit ' . $limit) : '');

        chkSchoolId('WM_term_major');
        if (strtolower($fields) == 'count(*)')
        {
            return $sysConn->GetOne($sqls);
        }
        else
        {
            $curr_mode = $ADODB_FETCH_MODE;
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
            $rs = $sysConn->Execute($sqls);
            $ADODB_FETCH_MODE = $curr_mode;
            return $rs;
        }
    }

    /**
     * private function 提供給 function getAllCourseInGroup 使用
     * @param int $group_id 群組id
     * @return array array($course_id => $caption)
     */
    function _getCourseInArray($group_id)
    {
        global $CourseAndGroupRelation;

        $rtn = array();
        if ($CourseAndGroupRelation[$group_id] && count($CourseAndGroupRelation[$group_id]))
        {
            foreach($CourseAndGroupRelation[$group_id] as $cid => $child)
            {
                if ($child['kind'] == 'group' && $cid != '10000000')
                    $rtn = $rtn + _getCourseInArray($cid);
                else if ($child['kind'] == 'course')
                    $rtn[$cid] = $child['caption'];
            }
        }
        return $rtn;
    }

    $CourseAndGroupRelation = array();
    /**
     * 取得某群組底下所有的課程
     * @param int $group_id 群組id
     * @param array 群組下所有課程id, caption
     */
    function getAllCourseInGroup($group_id)
    {
        global $CourseAndGroupRelation;

        if ($group_id < 10000000 || $group_id > 99999999) return;

        if (count($CourseAndGroupRelation) == 0)
        {
            $rs = dbGetStMr('WM_term_course as C left join WM_term_group as G on G.child = C.course_id',
                            'if(isnull(G.parent),10000000, G.parent) as parent, C.course_id, C.caption, C.kind',
                            'G.parent and C.status < 9', ADODB_FETCH_ASSOC);
            if ($rs) while($row = $rs->FetchRow())
            {
                $CourseAndGroupRelation[$row['parent']][$row['course_id']] = array('caption'=>$row['caption'], 'kind'=>$row['kind']);
            }
        }

        return _getCourseInArray($group_id);
    }

    // 取得課程群組中教授的課程
    // 利用字串取代，帳號：%USERNAME%，群組編號：%GROUP_ID%
    $Sqls['get_group_teacher_term'] = 'SELECT CS.`course_id`, CS.`caption`, CS.`status`, CS.`st_begin`, CS.`st_end`' .
                                      ',TH.`role` & ' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .' as level '.
                                      'FROM `WM_term_group` AS GP, `WM_term_major` AS TH ' .
                                      'LEFT JOIN `WM_term_course` AS CS ON CS.`course_id`=TH.`course_id` AND CS.`kind`="course" AND CS.`status`<9 ' .
                                      'WHERE GP.`parent`=%GROUP_ID% AND TH.`username`=\'%USERNAME%\' ' .
                                      'AND TH.`course_id`=GP.`child` AND TH.role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .
                                      ' ORDER BY GP.`permute` ASC';

    // 取得全校課程中的群組課程
    // 利用字串取代，群組編號：%GROUP_ID%，群組清單：群組編號：%GROUP_LIST%
    $Sqls['get_group_school_term'] = 'select CS.* ' .
        'from `WM_term_group` AS GP ' .
        'left join `WM_term_course` AS CS on CS.`course_id`=GP.`child` AND CS.status<9 ' .
        'where GP.`parent`=%GROUP_ID% AND GP.`child` not IN (%GROUP_LIST%)';

    // 取得課程所屬的課程群組
    // 利用字串取代，課程編號：%COURSE_ID%
    $Sqls['get_course_in_group'] = 'select CS.`course_id`, CS.`caption` ' .
        'from `WM_term_group` AS GP left join `WM_term_course` AS CS ' .
        'on CS.`course_id`=GP.`parent` AND CS.status<9 where GP.child=%COURSE_ID%';

    // 取得 使用者 敲打的字 是否 存在 WM_user_account 的 email
    // 利用字串取代，email：%email%
    $Sqls['get_email'] = 'select username '.
                         ' from `WM_user_account`  '.
                         ' where email like "%%email%%"';

    // 查詢 課程名稱的 代碼
    // 利用字串取代，課程名稱：%cname%
    $Sqls['get_course_id'] = 'select course_id ' .
                             ' from `WM_term_course` '.
                             ' where caption like "%%cname%%" AND status<9';


   // 抓取 教授 老師的 姓名
   // 利用字串取代，teacher：%teacher%
    $Sqls['get_teacher_name'] = 'select a.username,a.first_name,a.last_name '.
                                ' from `WM_user_account` as a,`WM_term_major` as b ' .
                                ' where a.username = b.username and b.role&' .
                                ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .
                                ' %teacher% group by a.username ';


    // 抓取 學生的正在修的課程清單
    // 利用字串取代，帳號: %USERNAME%
    $Sqls['get_student_courselist'] = 'select CS.course_id, CS.caption,'.
                                      'MJ.last_login,MJ.login_times,MJ.post_times,MJ.dsc_times,'.
                                      'sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page '.
                                      'from WM_term_major as MJ left outer join WM_record_reading as P '.
                                      '  on MJ.course_id=P.course_id and P.username="%USERNAME%" ' .
                                      '  left join WM_term_course as CS on MJ.course_id=CS.course_id ' .
                                      '  where MJ.username="%USERNAME%" and (' .
                                      '(MJ.role&' . $sysRoles['auditor'] . ' and (CS.status=1 or (CS.status=2 and (isnull(CS.st_begin) or CS.st_begin<=CURDATE()) and (isnull(CS.st_end) or CS.st_end>=CURDATE())))) or ' .
                                      '(MJ.role&' . $sysRoles['student'] . ' and (CS.status=1 or CS.status=3 or ((CS.status=2 or CS.status=4) and (isnull(CS.st_begin) or CS.st_begin<=CURDATE()) and (isnull(CS.st_end) or CS.st_end>=CURDATE())))) ' .
                                      ') group by MJ.course_id ';


    // 抓取 學生的 已經 修的課程 或者 課程刪除 的 清單
    // 利用字串取代，帳號: %USERNAME%
    $Sqls['get_student_end_courselist'] = 'select CS.course_id, CS.caption,'.
                                          'MJ.last_login,MJ.login_times,MJ.post_times,MJ.dsc_times,'.
                                          'sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page '.
                                          'from WM_term_major as MJ left outer join WM_record_reading as P '.
                                          '  on MJ.course_id=P.course_id and P.username="%USERNAME%" ' .
                                          '  left join WM_term_course as CS on MJ.course_id=CS.course_id ' .
                                          ' where MJ.username="%USERNAME%" ' .
                                          ' and ( ( CS.st_end <= now() )  or CS.status = 9 )  ' .
                                          '  group by MJ.course_id ';

    // 抓取 學生 已上過 或 曾經上過 的  課程清單
    // 利用字串取代，帳號: %USERNAME%
    $Sqls['get_student_all_courselist'] = 'select CS.course_id, CS.caption,CS.st_begin, CS.st_end,CS.status,CS.teacher,CS.credit,CS.fair_grade ' .
                                          ' from WM_term_major AS MJ left join WM_term_course AS CS ' .
                                          ' on CS.course_id=MJ.course_id ' .
                                          ' where MJ.username="%USERNAME%" ';

    // 抓取 學生 某一課程 的 總成績
    // 利用字串取代，帳號: %USERNAME%  課程代碼: %COURSE_ID%
    $Sqls['get_student_course_total_grade'] = 'select total ' .
                                              'from WM_grade_stat ' .
                                              ' where course_id = %COURSE_ID% ' .
                                              'and username="%USERNAME%"';

    // 抓取 某一班級 的老師 助教 (WM_class_director) 或 學員 (WM_class_member) 的資料
    // 利用字串取代，資料表: %TABLE%  班級代碼: %CLASS_ID%
    $Sqls['get_class'] = ' select B.username,A.first_name,A.last_name,A.gender,B.role,A.email ' .
                         ' from %TABLE% as B inner join  WM_user_account as A ' .
                         '   on B.username = A.username ' .
                         ' where B.class_id = ' . "%CLASS_ID%";

    // 抓取 多個班級 的老師 助教 (WM_class_director) 或 學員 (WM_class_member) 的 email 資料
    // 利用字串取代，資料表: %TABLE%  班級代碼: %CLASS_ID%
    $Sqls['get_many_class'] = 'select A.username,A.first_name,A.last_name,A.email ' .
                              'from WM_user_account as A inner join %TABLE% as B ' .
                              'on A.username = B.username '.
                              'where B.class_id in (%CLASS_ID%) ';

    // ---------  教師環境-設定助教  ----------
    // 取得目前課程中的老師/講師/助教 身份的username
    // 利用字串取代，班級代碼: %COURSE_ID%
    $Sqls['get_course_teacher_level'] =
        ' select b.username,b.role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .
        ' as level,c.course_id,c.caption,a.first_name,a.last_name ' .
        ' from WM_term_major as b, WM_term_course as c ,WM_user_account as a'.
        ' where b.course_id = %COURSE_ID% and b.course_id = c.course_id and a.username = b.username and b.role&' .
        ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);

    // ---------  聊天室  ----------
    // 取得目前聊天室中，有哪些人已經離線了
    // 利用字串取代，聊天室編號：%ROOM_ID%
    $Sqls['get_chat_offline_user'] = 'select `WM_session`.`idx`, `WM_chat_session`.`username`, `WM_chat_session`.`realname`, IFNULL(`WM_session`.`chance`, -1) as chance ' .
        ' from `WM_chat_session` left join `WM_session` on `WM_chat_session`.`username`=`WM_session`.`username` ' .
        " where `WM_chat_session`.`rid`='%ROOM_ID%' ";

    // 抓取 全校成員的資料
    $Sqls['get_all_student'] = 'select username,first_name,last_name,gender,email ' .
                               ' from WM_user_account';


    // 抓取 某一課程 底下 成員的資料
    // 利用字串取代，聊天室編號：%COURSE_ID%
    $Sqls['get_course_all_student'] = 'select A.role,B.* ' .
                                      'from WM_term_major as A inner join WM_user_account as B ' .
                                      'on A.username= B.username' .
                                      ' where A.course_id = %COURSE_ID%';

    // 抓取 某一課程 底下 成員的總數
    // 利用字串取代，：%COURSE_ID%
    $Sqls['get_course_all_student2'] = 'select count(*) as num ' .
                                       'from WM_term_major as A inner join WM_user_account as B ' .
                                       'on A.username= B.username' .
                                       ' where A.course_id = %COURSE_ID%';

    // 抓取 學生 已上過 或 曾經上過 的  課程 及 各科的總分 清單
    // 利用字串取代，帳號: %USERNAME%
    $Sqls['get_student_all_course_grade_list'] = 'select CS.course_id, CS.caption,CS.st_begin, CS.st_end,CS.status,CS.teacher,CS.credit,CS.fair_grade,WG.total ' .
                                          ', if(WG.total >= CS.fair_grade,CS.credit,0) as real_credit ' .
                                          ' from WM_term_course AS CS ' .
                                          ' inner join WM_term_major AS MJ  ' .
                                          ' on CS.course_id=MJ.course_id ' .
                                          ' left join WM_grade_stat as WG ' .
                                          ' on MJ.course_id = WG.course_id ' .
                                          ' and MJ.username = WG.username ' .
                                          ' where CS.`status`!=9 AND MJ.username="%USERNAME%"';

    // 抓取 老師的帳號 姓名 及 身份
    $Sqls['get_all_teacher_level'] = ' select b.username,a.first_name,a.last_name,b.role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ' as level ' .
                                     ' from WM_term_major as b, WM_term_course as c ,WM_user_account as a'.
                                     ' where b.course_id = c.course_id ' .
                                     ' and a.username = b.username and b.role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);

    // 抓取 學生的 正在修的 、 已經 修的課程 或者 課程刪除 的 清單
    // 利用字串取代，帳號: %USERNAME%
    $Sqls['get_student_total_courselist'] = 'select CS.course_id, CS.caption,'.
                                          'MJ.last_login,MJ.login_times,MJ.post_times,MJ.dsc_times,'.
                                          'sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page '.
                                          'from WM_term_major as MJ left outer join WM_record_reading as P '.
                                          '  on MJ.course_id=P.course_id and P.username="%USERNAME%" ' .
                                          '  left join WM_term_course as CS on MJ.course_id=CS.course_id ' .
                                          ' where MJ.username="%USERNAME%" ' .
                                          '  group by MJ.course_id ';

    // 抓取 某門課 所有學員的 修課清單
    // 利用字串取代，課號: %COURSEID% 身份 : %ROLE% 其他查詢條件 : %OTHER_COND%
    $Sqls['get_all_courselist'] = 'select T.*,U.first_name,U.last_name,U.email ,sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page ' .
                                  ' from WM_term_major as T ' .
                                  ' left join WM_user_account as U ' .
                                  ' on T.username=U.username ' .
                                  ' left join WM_record_reading as P ' .
                                  ' on T.course_id=P.course_id  and T.username = P.username ' .
                                  ' where T.course_id= %COURSEID% %OTHER_COND% and (T.role & %ROLE%) ' .
                                  ' group by T.username ';

    // 教室→資訊區→修課排行 的 SQL
    // 利用字串取代，課號: %COURSEID% 其它查詢條件 : %CONDITION%
    $Sqls['learn_ranking'] = 'select M.username,A.first_name,A.last_name,hid&3=3 as hidden,M.last_login,M.login_times,M.post_times,M.dsc_times,sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page,M.role ' .
                             'from WM_term_major as M left join WM_user_account as A ' .
                             ' on M.username=A.username ' .
                             ' left outer join WM_record_reading as P ' .
                             ' on P.course_id= %COURSEID% and M.username=P.username ' .
                             ' where M.course_id=%COURSEID% %CONDITION% group by M.username ';

    // 抓取 學生 已上過 或 曾經上過 的  課程 及 各科的總分 清單 for 管理者 - 班級管理 - 檢視成績
    // 利用字串取代，主TABLE的 Alis : %TABLE_ALIS% , TABLE : %TABLE_LEFT% , 其它條件 : %OTHER_CONDITION% , 計算總平均 : %TOTAL_AVG%
    $Sqls['get_student_grade_list'] = 'select %TABLE_ALIS%.username,count(TM.course_id) as total_course,' .
                                     'sum(if(TC.fair_grade <= GS.total,1,0)) as greater,' .
                                     '(count(TM.course_id) - sum(if(TC.fair_grade <= GS.total,1,0))) as smaller,' .
                                     ' %TOTAL_AVG% as total_avg ' .
                                     'from %TABLE_LEFT%' .
                                     ' left join WM_term_course as TC on TM.course_id=TC.course_id and TC.status != 9 ' .
                                     ' left join WM_grade_stat as GS on %TABLE_ALIS%.username=GS.username and TM.course_id=GS.course_id ' .
                                     ' where ' .
                                     ' %OTHER_CONDITION%' .
                                     '  group by %TABLE_ALIS%.username ';

    // 抓取 某班級 底下 某門課 有無此班級 的學員修課清單 for 導師環境 - 成員管理 - 到課統計
    // 利用字串取代，主TABLE的 Alis : %TABLE_ALIS% , TABLE : %TABLE_LEFT%
    // 課號: %COURSEID% 身份 : %ROLE% 其他查詢條件 : %OTHER_COND%
    $Sqls['get_direct_all_courselist'] = 'select T.*,U.first_name,U.last_name,U.email ,sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page ' .
                                         ' from %TABLE_LEFT%' .
                                         ' left join WM_term_major as T ' .
                                         ' on %TABLE_ALIS%.username = T.username ' .
                                         ' left join WM_record_reading as P ' .
                                         ' on T.course_id=P.course_id ' .
                                         ' and %TABLE_ALIS%.username = P.username ' .
                                         ' left join WM_user_account as U ' .
                                         ' on %TABLE_ALIS%.username=U.username ' .
                                         ' where T.course_id= %COURSEID% %OTHER_COND%' .
                                         ' and (T.role & %ROLE%) ' .
                                         ' group by T.username ';

    // 個人區→我的課程→課程辦公室 [未改作業 或 未改考卷]  的 SQL
    // 利用字串取代，課號: %COURSEID% , 三合一的類別 : %TYPE% , 身份: %ROLE%
    $Sqls['cour_hwork_exam_times'] = 'select count(*) as num ' .
                                     ' from WM_qti_%TYPE%_test as H ' .
                                     ' left join WM_qti_%TYPE%_result as R ' .
                                     ' on R.exam_id = H.exam_id   ' .
                                     ' where H.course_id = %COURSEID%' .
                                     ' and R.status in ("submit","break") ';

    // 校園廣場 - 學習榮譽榜 的 SQL
    // 利用字串取代，其他查詢條件: %OTHER_COND%
    $Sqls['school_ranking'] = 'select UA.username,UA.first_name,UA.last_name,' .
                               'count(TM.course_id) as total_course,' .
                               'round((sum(TC.credit * GS.total)+(sum(if(isnull(TC.credit),GS.total,0)))),1) as total_grade, ' .
                               'sum(TM.login_times) as login_times,' .
                               'sum(TM.post_times) as post_times,' .
                               'sum(TM.dsc_times) as dsc_times' .
                               ' from WM_user_account as UA ' .
                               ' left join WM_term_major as TM ' .
                               ' on UA.username = TM.username ' .
                               ' left join WM_grade_stat as GS ' .
                               ' on UA.username=GS.username ' .
                               ' and TM.course_id=GS.course_id left join WM_term_course as TC ' .
                               ' on TM.course_id=TC.course_id ' .
                               ' where  ((TC.status <> 9) or (TC.status is null)) ' .
                               ' and UA.username not in ("'.sysRootAccount.'") %OTHER_COND%'.
                               ' group by UA.username ';

    // 校園廣場 - 學習榮譽榜 - 抓取 閱讀時數的資料 的 SQL
    // 利用字串取代，其他查詢條件: %OTHER_COND%
    $Sqls['school_read_times'] = 'select UA.username,UA.first_name,UA.last_name,' .
                               'sum(unix_timestamp(DP.over_time) - unix_timestamp(DP.begin_time)+1) as rss,' .
                               'count(DP.username) as page' .
                               ' from WM_user_account as UA ' .
                               ' left outer join WM_record_reading as DP' .
                               ' on UA.username=DP.username' .
                               ' left join WM_term_course as TC ' .
                               ' on DP.course_id=TC.course_id ' .
                               ' where  ((TC.status <> 9) or (TC.status is null)) ' .
                               ' and UA.username not in ("'.sysRootAccount.'") %OTHER_COND%'.
                               ' group by UA.username ';

    // 抓取 老師的正在修的課程清單
    // 利用字串取代，帳號: %USERNAME%
    $Sqls['get_teacher_courselist'] =   'select CS.course_id, CS.caption,'.
                                    'MJ.last_login,MJ.login_times,MJ.post_times,MJ.dsc_times,'.
                                    'sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page '.
                                    'from WM_term_major as MJ left outer join WM_record_reading as P '.
                                    ' on MJ.course_id=P.course_id and P.username="%USERNAME%" ' .
                                    ' left join WM_term_course as CS on MJ.course_id=CS.course_id ' .
                                    ' where MJ.username="%USERNAME%" ' .
                                    ' and MJ.role & %ROLE%' .
                                    ' and CS.status not in (0,9) ' .
                                    // ' and (CS.st_begin <= now() || isnull(CS.st_begin)) '.
                                    ' and (CS.st_end >= CURDATE() or isnull(CS.st_end)) '.
                                    ' group by MJ.course_id ';

    // 抓取 老師的 已經 修的課程 或者 課程刪除 的 清單
    // 利用字串取代，帳號: %USERNAME%
    $Sqls['get_teacher_end_courselist'] = 'select CS.course_id, CS.caption,'.
                                      'MJ.last_login,MJ.login_times,MJ.post_times,MJ.dsc_times,'.
                                      'sum(unix_timestamp(P.over_time) - unix_timestamp(P.begin_time)+1) as rss,count(P.username) as page '.
                                      'from WM_term_major as MJ left outer join WM_record_reading as P '.
                                      '  on MJ.course_id=P.course_id and P.username="%USERNAME%" ' .
                                      '  left join WM_term_course as CS on MJ.course_id=CS.course_id ' .
                                      ' where MJ.username="%USERNAME%" ' .
                                      '  and MJ.role & %ROLE%' .
                                      ' and ( CS.st_end < CURDATE() or CS.status in (0,9) )  ' .
                                      '  group by MJ.course_id ';

    // 抓取 某帳號隸屬在那些班級
    // 利用字串取代，帳號: %USERNAME%
    $Sqls['user_belong_class'] = 'select M.class_id,C.caption ' .
                                 'from WM_class_member as M ' .
                                 'left join WM_class_main as C ' .
                                 'on M.class_id = C.class_id ' .
                                 'where M.username="%USERNAME%" ' .
                                 ' order by class_id desc ';

    // =======================  一般 SQL 宣告段 end  =======================


    // =======================  主程式開始  =========================
    // APP 使用時，由 /xmlapi/action.class.php 執行主程式功能
    if (defined('XMLAPI') && XMLAPI === true) {
        return;
    }

    // 抑制 proxy / IE 保留住舊網頁
    header('Pragma: no-cache');
    header('Cache-Control: no-cache, no-store, private, must-revalidate, post-check=0, pre-check=0');
    header('Expires: -1');
    header('Content-type: text/html;');

    // 產生 Session 實體
    $_SERVER['REMOTE_ADDR'] = wmGetUserIp();
    $sysSession = new SessionInfo;
    $sysSiteNo = $sysSession->school_id + sysSiteUID;
    // 建立通用的CSRF的Token
    $csrfToken = md5($sysSession->idx);
    if (empty($sysSession->env)) $sysSession->env = 'learn';    // add by lst : 預設使用學生環境

    // 目錄越權處理
    list($foo, $TopDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    if (strpos('learn|teach|direct|academic', $TopDir) !== FALSE)
    {
        if ($sysSession->username == 'guest' && $sysSession->env != 'learn')  die('Access Denied.');
                $except_files = array(
                    '/index.php',
                    '/login.php',
                    '/academic/index.php',
                    '/direct/index.php',
                    '/teach/sysbar.php',
                    '/teach/index.php',
                    '/teach/select.php',
                    '/teach/course/lcms.php',
                    '/teach/goto_course.php'
                );
        if ( !defined('IS_CLI_RUN') && !in_array($_SERVER['PHP_SELF'], $except_files) &&
            (($sysSession->env == 'learn'  && $TopDir != 'learn') ||
             ($sysSession->env == 'teach'  && $TopDir != 'learn' && $TopDir != 'teach') ||
             ($sysSession->env == 'direct' && $TopDir != 'learn' && $TopDir != 'direct')
            )
           ){
            switch($TopDir)
            {
                case 'academic':
                    // 檢查是否具有一般管理者, 進階管理者, root的權限
                    if (!aclCheckRole($sysSession->username, ($sysRoles['manager']|$sysRoles['administrator']|$sysRoles['root']), $sysSession->school_id, false)){
                        die('You are not the system manager.');
                    }
                    $sysSession->env = 'academic';
                    break;
                case 'teach':
                    // 檢查是否具有教師、助教、講師的權限(需先進入某一課程後，才能透過網址列切換)
                    if (!aclCheckRole($sysSession->username, ($sysRoles['teacher']|$sysRoles['assistant']|$sysRoles['instructor']), $sysSession->course_id, false)){
                        die('You are not the office manager, or never go into each course after login.');
                    }
                    $sysSession->env = 'teach';
                    break;
                case 'direct':
                    // 檢查是否具有導師的權限(需先事先進入導師辦公室過，才能透過網址列直接切換)
                    if (!aclCheckRole($sysSession->username, ($sysRoles['director']|$sysRoles['class_instructor']), $sysSession->class_id, false)){
                        die('You are not the director.');
                    }
                    $sysSession->env = 'direct';
                    break;
                case 'learn':
                    break;
            }
        }

    }
    unset($foo, $TopDir, $except_files);

    // 針對上傳檔案做一過濾處理
    if (isset($_FILES) && is_array($_FILES)) {
        foreach ($_FILES as $uploadfieldName => $uploadfieldVal) {
            // 多重檔案上傳
            if (is_array($uploadfieldVal['name'])) {
                // 檔名有sql injection的可能，利用detect_malicious_data去驗證
                detect_malicious_data($uploadfieldVal['name'], true);
                for ($i = 0, $size=count($uploadfieldVal); $i < $size; $i++) {
                    //去掉不合法字元，及開頭為.的隱藏檔
                    $rawUploadFileName = preg_replace('/[\\/:\*\?"<>\|%#+]+/i', '_', $uploadfieldVal['name'][$i]);
                    while (strpos($rawUploadFileName, '.') === 0) {
                        $rawUploadFileName = substr($rawUploadFileName,1);
                    }
                    $checkUploadFileName = str_replace(array('.php','..'), array('.phps',''), basename($rawUploadFileName));
                    //有攻擊性的不合法檔名發現，更換檔名並log
                    if (strcmp($rawUploadFileName, $checkUploadFileName) != 0) {
                        wmSysLog(900100199, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'uploadFileName:'.mysql_escape_string($_FILES[$uploadfieldName]['name'][$i]));
                        $_FILES[$uploadfieldName]['name'][$i] = $checkUploadFileName;
                    }else{
                        $_FILES[$uploadfieldName]['name'][$i] = $rawUploadFileName;
                    }
                }
            }else{
                // 檔名有sql injection的可能，利用detect_malicious_data去驗證
                detect_malicious_data($_FILES[$uploadfieldName], true);
                //去掉不合法字元，及開頭為.的隱藏檔
                $rawUploadFileName = preg_replace('/[\\/:\*\?"<>\|%#+]+/i', '_', $uploadfieldVal['name']);
                while (strpos($rawUploadFileName, '.') === 0) {
                    $rawUploadFileName = substr($rawUploadFileName,1);
                }
                $checkUploadFileName = str_replace(array('.php','..'), array('.phps',''), basename($rawUploadFileName));
                //有攻擊性的不合法檔名發現，更換檔名並log
                if (strcmp($rawUploadFileName, $checkUploadFileName) != 0) {
                    wmSysLog(900100199, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'uploadFileName:'.mysql_escape_string($_FILES[$uploadfieldName]['name']));
                    $_FILES[$uploadfieldName]['name'] = $checkUploadFileName;
                }else{
                    $_FILES[$uploadfieldName]['name'] = $rawUploadFileName;
                }
            }
        }
    }

    /**
     * 新增"保存登入"的資料
     * @param  string $sessionIdx session idx
     * @param  string $user       帳號
     * @return string             persist idx
     */
    function createPersistData($sessionIdx, $user){
        // user agent
        $headers = apache_request_headers();
        $LoginAgentStr   = trim($headers['User-Agent']);
        if (strlen($LoginAgentStr)>255) $LoginAgentStr = substr($LoginAgentStr,0,255);

        // 建立時間
        $pidxCreateTime = date('Y-m-d H:i:s');

        $newPidx = sprintf("%s%s%s%s",md5($sessionIdx),md5($user),md5($pidxCreateTime),md5($LoginAgentStr));
        dbNew(
            'WM_persist_login',
            '`persist_idx`,`session_idx`,`username`,`create_time`,`expire_time`,`create_ipaddress`,`user_agent`',
            sprintf("'%s','%s','%s','%s',DATE_ADD('%s', INTERVAL 1 MONTH),'%s','%s'",
                $newPidx,$sessionIdx,$user,$pidxCreateTime,$pidxCreateTime,$_SERVER['REMOTE_ADDR'],$LoginAgentStr)
        );
        return $newPidx;
    }

    /**
     * 驗證傳入的$pidx(保持登入的idx)是否有效且正確
     * @param  string $pidx persist_idx
     * @return boolean 是否正確
     */
    function validPersistIdx($pidx){
        if (strlen($pidx) != 128) {
            wmSysLog(900128001, $sysSession->school_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'persist idx length error:'.mysql_escape_string($pidx));
            return false;
        }

        $persistRow = dbGetRow('WM_persist_login','*',sprintf("persist_idx='%s' and expire_time>NOW()",mysql_escape_string($pidx)));
        if (!isset($persistRow['persist_idx']) || ($persistRow['persist_idx'] != $pidx)){
            wmSysLog(900128002, $sysSession->school_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'persist idx not found:'.mysql_escape_string($pidx));
            return false;
        }

        $headers = apache_request_headers();
        $agent   = trim($headers['User-Agent']);
        if (strlen($agent)>255) $agent = substr($agent,0,255);
        if ($agent != $persistRow['user_agent']){
            wmSysLog(900128003, $sysSession->school_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'user Agent Error:'.mysql_escape_string($pidx));
            return false;
        }

        if (empty($persistRow['username'])) {
            wmSysLog(900128004, $sysSession->school_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'username is empty:'.mysql_escape_string($pidx));
            return false;
        }

        if ($persistRow['username'] == 'root') {
            wmSysLog(900128005, $sysSession->school_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'root deny to persist login:'.mysql_escape_string($pidx));
            return false;
        }

        $chkUsernameVal = checkUsername($persistRow['username']);
        if (!in_array($chkUsernameVal, array(2,4))){
            wmSysLog(900128005, $sysSession->school_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'username:'.mysql_escape_string($persistRow['username']).' not enable:'.mysql_escape_string($pidx));
            return false;
        }

        return true;
    }

    // 保持登入的處理
    if (($sysSession->username == 'guest') &&
        (!empty($_COOKIE["persist_idx"])) &&
        (!in_array($_SERVER['PHP_SELF'],array('/login.php','/connect_lost.php')))
    ){
        // 仍是有效的cookie, 導向登入頁
        if (validPersistIdx($_COOKIE["persist_idx"])){
            header('LOCATION: /login.php');
            exit;
        }else{
            // 清除 persist_idx
            setcookie('persist_idx', '', time()-3600, '/', '', $http_secure);
            // 清除 WM_persist_login
            dbDel('WM_persist_login', sprintf("persist_idx='%s'",mysql_escape_string($_COOKIE["persist_idx"])));
        }
    }
    ob_start('pageLockControl');
?>

<?php
    /**
     * 設定並回傳是iOS或是Android
     * @param string $str : user agent 的資料
     * @return string iOS|Android
     **/
    function getAppUserAgent($str)
    {
        /*
            TODO 不只把OS分開，也將不同裝置區分出來 ex. iOS Phone, iOS Pad, Android Phone, Android Pad
            可參考 sencha touch sdk microloader的development.js的用法
         */
        if (strstr($str, 'iPhone') !== '' ||
            strstr($str, 'iPad') !== ''
        ) {
            return 'iOS';
        } else {
            return 'Android';
        }
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
     * @param string  $useragent   : 記錄來源
     * @return string : 紀錄在哪個表格
     *     boolean false : 失敗
     **/
    function appSysLog($function_id,$department_id,$instance ,$result_id, $environment='auto', $script_name='', $note='', $user='', $useragent='')
    {
        global $sysSession, $sysConn, $_SERVER;

        if (empty($function_id)) {
            $fid = $sysSession->cur_func;    // 假如沒有指定功能編號，就由 SysSession 取得
        } else {
            $fid = $function_id;
        }
        if (empty($user))$user= $sysSession->username;    // 假如沒有指定帳號，就由SysSession 取得
        $rid     = $result_id;                  // 錯誤編號
        $note    = trim($note);                 // 附記
        $address = wmGetUserIp();               // user IP
        $headers = apache_request_headers();
        $agent   = $headers['User-Agent'];      // User Agent
        $agentType = ($useragent !== '' ) ? $useragent : getAppUserAgent($agent);        // User Agent ID
        $agentID = 0;
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

        if (! isset($department_id)) {
            $department_id = 0;
        }
        if (! isset($instance)) {
            $instance = 0;
        }
        
        if (in_array($function_id, array(999999001, 999999002))) {
            $table = 'APP_log_others';
        }

        $note = '(' . $agentType . ')' . $note;
        $field   = 'function_id, username, log_time, department_id,instance,result_id, note, remote_address, user_agent, script_name';
        dbNew($table, $field, "'{$fid}', '{$user}', NOW(),{$department_id},{$instance} ,'{$rid}', '{$note}', '{$address}', {$agentID}, '{$scname}'");
        if ($sysConn->Affected_Rows() > 0) {
            return str_replace('WM_log_', '', $table);
        } else {
            return false;
        }
    }

    // SESSION 物件宣告
    class APPSessionInfo
    {
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
        var $ticket = '';
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
        function APPSessionInfo()
        {
            global $sysSession, $sysConn, $http_secure;

            $this->b_right = false;
            $this->q_right = false;
            $this->news_nodes = '';
            $this->env = 'app';
            
            $getTable = 'WM_school';
            $getFields = 'school_id, school_name, language, theme, guest, guestLimit, school_mail';
            $getWhere = "school_host='{$_SERVER['HTTP_HOST']}'";
            $SCH = dbGetStSr($getTable, $getFields, $getWhere);
            if (!is_array($SCH)) die('No school bind ' . $_SERVER['HTTP_HOST']);
            // Bug#1493
            // 抓取學校常數定義中的語系 -- Begin by Small 2006/10/31
            $sch_sql = 'select school_id from WM_school where school_host="' . $_SERVER['HTTP_HOST'] . '"';
            $sysConn->Execute('use ' . sysDBname);
            $school_id = $sysConn->GetOne($sch_sql);
            $conf = getConstatnt($school_id);
            $avln = explode(',', $conf['sysAvailableChars']);

            @ini_set('sendmail_from', $SCH['school_mail'] ? $SCH['school_mail'] : sysWebMaster);
            // 抓取學校常數定義中的語系 -- End by Small 2006/10/31

            $cookie_lang = $SCH['language'];

            $skey = md5($_SERVER['HTTP_HOST'].$SCH['school_id']);
            $_COOKIE['school_hash'] = substr($skey, 0, 17) . $SCH['school_id'] . substr($skey, -10);
            setcookie('school_hash', $_COOKIE['school_hash'], time()+86400, '/', '', $http_secure);

            if (isset($_REQUEST['ticket'])) {
                $this->ticket = trim($_REQUEST['ticket']);
                $_COOKIE['idx'] = $this->ticket;
            } else if (isset($_COOKIE['idx'])) {
                $this->ticket = trim($_COOKIE['idx']);
            } else {
                $this->ticket = '';
            }

            chkSchoolId('WM_session');
            if ($this->ticket !== '') {
                $ticket = mysql_real_escape_string($this->ticket);
                $sessinfo = dbGetStSr('WM_session', '/*!40001 SQL_NO_CACHE */ *', "idx='{$ticket}'");
                if (!$sessinfo) {
                    if (!defined('sysDBschool'))
                        define('sysDBschool', sysDBprefix . $SCH['school_id']);

                    $User = array();
                    $User['username']   = 'guest';
                    $User['first_name'] = 'Guest';
                    $User['last_name']  = '';
                    $User['email']      = '';
                    $User['homepage']   = '';
                    $User['language']   = $cookie_lang;
                    $this->ticket = $this->init($User);
                    $_COOKIE['idx'] = $this->ticket;
                    setcookie('idx', $this->ticket, 0, '/', '', $http_secure);
                }
            } else {
                $User = array();
                $User['username']   = 'guest';
                $User['first_name'] = 'Guest';
                $User['last_name']  = '';
                $User['email']      = '';
                $User['homepage']   = '';
                $User['language']   = $cookie_lang;
                $this->ticket = $this->init($User);
                $_COOKIE['idx'] = $this->ticket;
                setcookie('idx', $this->ticket, 0, '/', '', $http_secure);
            }

            // 有 session key
            $ticket = mysql_real_escape_string($this->ticket);
            $sessinfo = dbGetStSr('WM_session', '/*!40001 SQL_NO_CACHE */ *', "idx='{$ticket}'");

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
        }

        // 回存 Session 資料
        function restore()
        {
            dbSet('WM_session',
                  sprintf('course_id=%d, cur_func="%s",q_path="%s",news_nodes="%s",board_ownerid="%s",board_ownername="%s",session="%s"',
                      intval($this->course_id),$this->cur_func,$this->q_path,$this->news_nodes,$this->board_ownerid,$this->board_ownername,
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
        function clean()
        {
            dbSet('WM_session', "session='lang=\"$this->lang\"\ttheme=\"$this->theme\";'", "idx='{$_COOKIE['idx']}'");
        }

        // 初始化 Session
        function init($User)
        {
            global $sysConn;

            $sysConn->Execute('use ' . sysDBname);
            $RS = dbGetStSr('WM_school', 'school_id, school_name, language, theme', "school_host='{$_SERVER['HTTP_HOST']}'");
            if (empty($RS['school_id']) || empty($RS['school_name'])) {
                die('No any school bind the server_name: '.$_SERVER['HTTP_HOST']);
            }

            mt_srand(intval(substr(microtime(),3,6)));
            $ip = wmGetUserIp();

            $sysConn->Execute('use ' . sysDBprefix . $RS['school_id']);

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
                               "'lang=\"$this->lang\"\ttheme=\"$this->theme\"\tenv=\"$this->env\";'");
            if ($_SERVER['REMOTE_ADDR'] == '127.0.0.2') {
                $sysConn->Execute('use ' . $GLOBALS['db']);
            } else {
                $sysConn->Execute('use ' . sysDBschool);
            }
            return $idx;
        }

        // 重載 Session
        function refresh()
        {
            $this->APPSessionInfo();
        }
    }
    
    // =======================  一般 SQL 宣告段 begin  =======================
    /**
     * 取得教授或選修中的課程 (會依身份而檢查開放日期與狀態) 會連帶回傳課程圖片
     *
     * @param   string      $fields     欲取得的欄位
     * @param   string      $username   特定帳號。忽略則以 $sysSession->username 代替
     * @param   int         $roles      判斷的身份(旁聽生、正式生、助教、講師、教師)，預設為正式生
     * @param   string      $order      排序欄位，可多個
     * @param   string      $limit      取得位置與個數
     * @return  array                   傳回筆數與recordset
     *
     */
    function &dbGetCoursesWithPicture($fields='count(*)', $username='', $roles=0, $order=false, $limit=false, $keyword, $gid = 10000000)
    {
        global $sysConn, $sysSession, $sysRoles, $ADODB_FETCH_MODE;

        $username = preg_match(Account_format, $username) ? $username : $sysSession->username;
        $username = mysql_real_escape_string($username);
        $keyword = mysql_real_escape_string($keyword);
        $roles    = $roles ? $roles : $sysRoles['student'];

        /* #81924 補上依據課程群組的撈取動作 */
        $groupWhere  = '';
        if($gid != 10000000){
          $array_group = dbGetCol("WM_term_group","child","child <> 0 and parent = {$gid}");
          $array_group = implode(",",$array_group);
          $groupWhere  = " AND C.course_id IN({$array_group})";
        }

        $sqls = 'select SQL_CALC_FOUND_ROWS ' . $fields . ' from WM_term_major AS M ' .
            'inner join WM_term_course AS C on C.course_id=M.course_id ' .
            'left join CO_course_picture AS P on M.course_id=P.course_id '.
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
            ($keyword !== '' ? ' AND (C.caption like ' . $sysConn->qstr('a:2:{s:4:"Big5"%s:%:"%' . $keyword . '%"%s:6:"GB2312"%s:%:%}', get_magic_quotes_gpc()) .' OR C.caption like ' . $sysConn->qstr('a:2:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:"%' . $keyword . '%"%}', get_magic_quotes_gpc()) . ') ' : '') .
            $groupWhere . 
            ($order && preg_match('/^(\w+|`\w+`)(\.(\w+|`\w+`))*(\s+(asc|desc))?(\s*,\s*(\w+|`\w+`)(\.(\w+|`\w+`))*(\s+(asc|desc))?)*$/i', $order) ? (' order by ' . $order) : '') .
            ($limit && preg_match('/^\d+(\s*,\s*\d+)?$/', $limit) ? (' limit ' . $limit) : '');


        chkSchoolId('WM_term_major');
        $curr_mode = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $rs = $sysConn->Execute($sqls);

        $ADODB_FETCH_MODE = $curr_mode;

        $totalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        $result = array(
            'totalSize' => $totalSize,
            'result' => $rs
        );

        return $result;
    }

    /**
     * 取得教授或選修中的課程 (會依身份而檢查開放日期與狀態)
     *
     * @param   string      $fields         欲取得的欄位
     * @param   string      $username       特定帳號。忽略則以 $sysSession->username 代替
     * @param   int         $roles          判斷的身份(旁聽生、正式生、助教、講師、教師)，預設為正式生
     * @param   boolean      $order          排序欄位，可多個
     * @param   boolean      $limit          取得位置與個數
     * @param   string      $qtiTestTable   exam | questionnaire | homework
     * @param   string      $keyword        課程名稱關鍵字
     * @return  array                       傳回筆數與recordset
     *
     */
    function &dbGetCoursesWithQTI($fields='count(*)', $username='', $roles=0, $order=false, $limit=false, $qtiTestTable, $keyword = '')
    {
        global $sysConn, $sysSession, $sysRoles, $ADODB_FETCH_MODE;

        $username = preg_match(Account_format, $username) ? $username : $sysSession->username;
        $roles    = $roles ? $roles : $sysRoles['student'];
        $keyword = mysql_real_escape_string($keyword);

        $keywordSql = ($keyword !== '') ? ' AND (C.caption like \'%' . $keyword .'%\' OR C.caption like ' . $sysConn->qstr('a:2:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:"%' . $keyword . '%"%}', get_magic_quotes_gpc()) . ') ' : '';

        $sqls = 'SELECT SQL_CALC_FOUND_ROWS ' . $fields . ' FROM WM_term_major AS M ' .
            'INNER JOIN WM_term_course AS C on C.`course_id` = M.`course_id` ' .
            'INNER JOIN '. $qtiTestTable . ' as QT on QT.`course_id` = M.`course_id` ' .
            'WHERE M.`username` ="' . $username . '" AND C.kind = "course" AND (' .
            ($roles & $sysRoles['auditor'] ? (
                '(M.`role`&' . $sysRoles['auditor'] .
                ' and (C.`status` = 1 or (C.`status` = 2 AND (isnull(C.`st_begin`) OR C.st_begin <= CURDATE()) and (isnull(C.`st_end`) OR C.`st_end` >= CURDATE()))))') : '') .
            ($roles & $sysRoles['student'] ? ( ($roles & $sysRoles['auditor'] ? ' OR ' : '') .
                '(M.`role`&' . $sysRoles['student'] .
                ' AND (C.`status` = 1 or C.`status` = 3 OR ((C.`status`=2 or C.`status`=4) AND (isnull(C.`st_begin`) or C.`st_begin` <= CURDATE()) AND (isnull(C.`st_end`) OR C.`st_end` >= CURDATE()))))') : '') .
            ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']) ? ( ($roles & $sysRoles['student'] ? ' OR ' : '') .
                '(M.`role`&' . ($roles & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'])) .
                ' AND (C.`status` BETWEEN 1 AND 5))') : '') .
            ')' . $keywordSql .
            ' GROUP BY course_id '.
            ($order && preg_match('/^(\w+|`\w+`)(\.(\w+|`\w+`))*(\s+(ASC|DESC))?(\s*,\s*(\w+|`\w+`)(\.(\w+|`\w+`))*(\s+(ASC|DESC))?)*$/i', $order) ? (' ORDER BY ' . $order) : '') .
            ($limit && preg_match('/^\d+(\s*,\s*\d+)?$/', $limit) ? (' LIMIT ' . $limit) : '');


        chkSchoolId('WM_term_major');
        $curr_mode = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $rs = $sysConn->Execute($sqls);

        $ADODB_FETCH_MODE = $curr_mode;

        $totalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        $result = array(
            'totalSize' => $totalSize,
            'result' => $rs
        );

        return $result;
    }

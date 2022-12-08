<?php
    // 系統參數

    // Server OS
    define('sysOS'          , strpos($_SERVER['SERVER_SOFTWARE'], 'Win')?'win':'unix');

    // WM3 安裝實體路徑
    define('sysDocumentRoot', dirname(dirname(__FILE__)));

    // PEAR 安裝實體路徑 (Add By Shenting)
    define('sysPearRoot'    , sysOS=='unix'?PEAR_INSTALL_DIR . DIRECTORY_SEPARATOR:'');

    // 暫存路徑
    define('sysTempPath'    , sysOS=='unix'?'/tmp':$_ENV['TMP']);

    // 主機 Unique ID
    define('sysSiteUID'     , 1000100000);

    // HMAC ticket seed
    define('sysTicketSeed'  , md5($_COOKIE['idx']));

    // 目前 QTI 是哪一個子系統 (測驗、問卷、作業)
    if (!defined('QTI_which')) define('QTI_which', basename(dirname($_SERVER['PHP_SELF'])) );

    // 最大學校 ID
    define('sysMaxSchool'   , 99999);

    // 最大課程 ID
    define('sysMaxCourse'   , 99999999);

    // 資料庫種類
    if (PHP_VERSION >= '7') {
		define('sysDBtype'      , 'mysqli');
	}else{
		define('sysDBtype'      , 'mysql');
	}

    // 資料庫主機
    define('sysDBhost'      , '192.168.10.82');

    // 資料庫名稱
    define('sysDBname'      , 'WM_MASTER');

    // 資料庫名稱前綴 (WM_)
    define('sysDBprefix'    , substr(sysDBname, 0, strrpos(sysDBname, '_')+1));

    // 資料庫帳號
    define('sysDBaccoount'  , 'wm3');

    // 資料庫密碼
    define('sysDBpassword'  , 'WmIiI');

    // 內定語系
    //define('sysDefaultLang' , 'Big5');

    // 內定布景
    define('sysDefaultTheme', 'default');

    // 討論板內定每頁幾篇
    define('sysPostPerPage' , 10);

    // 討論板內定排序
    define('sysSortBy'      , 'pt'); // pt, username, ranking, hit, subject

    // 精華區內定排序
    define('sysQSortBy'     , 'subject'); // pt, username, ranking          , hit, subject

    if (strpos($_SERVER['HTTP_HOST'], ':') !== FALSE)
        list($_SERVER['HTTP_HOST'], $_SERVER['SERVER_PORT']) = explode(':', $_SERVER['HTTP_HOST'], 2);

    // 系統送信寄件者
    define('sysWebMaster'   , '"Wisdom Master System"<webmaster@' . $_SERVER['HTTP_HOST'] . '>');

    define('TTF_DIR'        , sysDocumentRoot . '/lib/truetype/');
    
    // 最高管理員的帳號
    define('sysRootAccount' , 'root');

    // Mail 規則
    define('sysMailRule'    , '/^\w+(-\w+)*(\.\w+(-\w+)*)*@\w+(-\w+)*(\.\w+(-\w+)*)+$/');
    // Mail 規則 (多個)
    define('sysMailsRule'   , '/^\w+(-\w+)*(\.\w+(-\w+)*)*@\w+(-\w+)*(\.\w+(-\w+)*)+([ ,;]+\w+(-\w+)*(\.\w+(-\w+)*)*@\w+(-\w+)*(\.\w+(-\w+)*)+)*$/');

    define('DEFAULT_sysCourseLimit'          , 0);          // 開課限量
    define('DEFAULT_CourseQuestionsLimit'    , 0);          // 試題限量
    define('DEFAULT_CourseExamQuestionsLimit', 200);      // 試卷限量
    define('DEFAULT_systemTimeOutLimit'      , 120);      // 系統TimeOut
    define('DEFAULT_Account_firstchr'        , 0);        // 帳號規則
    define('DEFAULT_sysMaxUser'              , 0);        // 學員帳號限量
    define('DEFAULT_sysMaxConcurrentUser'    , 0);        // 學員登入帳號限量
    define('DEFAULT_sysAccountMinLen'        , 2);          // 帳號最短限字
    define('DEFAULT_sysAccountMaxLen'        , 20);          // 帳號最長限字
    define('DEFAULT_pathNodeTimeShortlimit'  , 3);        // 閱讀時數 (最少 ; 以秒計算)
    define('DEFAULT_pathNodeTimeLonglimit'   , 21600);    // 閱讀時數 (最長 ; 以秒計算)
    define('DEFAULT_CoursePackLimit'         , 512000);   // 可包裝的課程內容大小
    define('DEFAULT_ExamPackLimit'           , 200);      // 匯出試題題數
    define('DEFAULT_joinet'                  , 'N');      // 啟用 join net
    define('DEFAULT_MMC_Server'              , '');       // 定義使用 join net 的 MMC_Server
    define('DEFAULT_MMC_Server_port'         , '');       // 定義使用 join net 的 MMC_Server_port
    define('DEFAULT_anicam'                  , 'N');      // 啟用 Anicam Live
    define('DEFAULT_MMS_Server'              , '');       // 定義使用 Anicam 的 Media Server
    define('DEFAULT_MMS_Server_port'         , '');       // 定義使用 Anicam 的 Media Server port
    define('DEFAULT_White_Board'             , 'N');      // 白板系統設定 預設為不啟用
    define('DEFAULT_Voice_Board'             , 'N');      // 語音討論版 預設為不啟用
    define('cron_minutely_function_id'       , 199999900);
    define('cron_hourly_function_id'         , 199999901);
    define('cron_daily_function_id'          , 199999902);
    define('cron_monthly_function_id'        , 199999903);

    //Breeze php files root
    define('BREEZE_PHP_DIR'              , $_SERVER['DOCUMENT_ROOT'] . '/breeze');
    define('BREEZE_SESSION_DIR'          , BREEZE_PHP_DIR . '/sessionFiles');

    define('DEFAULT_Grade_Calculate'     , 'Y');

    define('DEFAULT_SYS_AVAILABLE_CHARS' , 'Big5,GB2312');
    
    //Mooc模組的預設常數
    define('DEFAULT_ENABLE_MOOC'         , 1);      // WMPRO5.1之後，Mooc模組預設為啟用

    // define('WM_SSL', true); // 若要使用 SSL 登入則把此行的 remark 拿掉，不用則 remark 起來
?>

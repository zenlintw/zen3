<?php
    // Define
    define('PATH_LIB', dirname(__FILE__)."/lib/");
    define('PATH_MODEL', dirname(__FILE__)."/model/");

    // WM Setting
    define('DEBUG_MODE', false);    // 開啟與關閉 Debug 訊息，Debug 訊息會直接輸出，上線時一定要關閉！
    define('WM_SERVER_HTTP', ($_SERVER['HTTPS']) ? 'https' : 'http');
    define('WM_SERVER_HOST', WM_SERVER_HTTP . '://' . $_SERVER['SERVER_NAME']);    // 設定 WM Pro 的外網連線網址，可設定 LB 位置
    define('WM_HTDOC_PATH', '/volume/htdocs/wm3');    // 設定 WM Pro 程式碼的磁碟安裝位置
    define('WM_OS_FILE_ENCODING', 'UTF-8');     // 設定值為UTF-8、Big5

    // APP Hongu Server Setting
    define('HONGU_SERVER', 'localhost');        // 設定 App Server 的連線位置
    define('HONGU_PORT', '80');                 // 設定 App Server 的連線 Port
    define('HONGU_USER', 'admin');              // 設定 App Server 提供訊息推播的登入帳號
    define('HONGU_PWD', 'sunnet.sun');          // 設定 App Server 提供訊息推播的登入密碼
    define('HONGU_DOMAIN', 'HONGU');            // 設定 App Server 提供訊息推播的登入網域
    define('HONGU_WM_DOMAIN', 'WM_MASTER');     // 設定 App Server 註冊的 WM Pro 網域

    // 加解密專用
    define('AES_APP_STRING', 'wisdommasterprofessionalapp');
    define('AES_APP_IV', 'SunAppEnNePpaNus');

    // Google client id、secret
    define('APP_GOOGLE_CLIENT_ID', '319183942965-f6reo8334giqbimdsbe10pt0e8rb2ue3.apps.googleusercontent.com');
    define('APP_GOOGLE_CLIENT_SECRET', 'hxlHVNiZk-tPkaez8XNO66zR');

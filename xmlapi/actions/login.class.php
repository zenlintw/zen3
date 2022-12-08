<?php
/**
 * 登入驗證
 *
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @category    xmlapi
 * @package     WM25
 * @subpackage  WebServices
 * @author      Jeff Wang <jeff@sun.net.tw>
 * @copyright   2011 SunNet Tech. INC.
 * @version     1.0
 * @since       2012-12-28
 */

include_once(dirname(__FILE__).'/action.class.php');
include_once(PATH_LIB . 'login.php');

class loginAction extends baseAction
{
    var $LOGIN_TYPES = array('wm', 'google', 'qrcode', 'session');

    var $username;
    var $password;
    var $userAgent;

    var $loginType;
    var $googleIdToken;
    var $moveLight;
    var $existIdx;

    var $userIP;
    var $userHost;
    var $sessionId;
    var $language;

    var $responseCode = 0;
    var $responseMsg = 'success';
    var $responseData = null;

    // 是否要踢到前者
    var $kickOff = false;
    var $sessionResult;
    var $wmproLoginData = array();
    var $returnNewFormat = false;

    function loginAction()
    {
        // 修正 php4 的 bug，當query string value 有 %26 會被自動切割的白痴問題
        if (strpos($_SERVER['REQUEST_URI'], '?')) {
            parse_str(substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?')+1), $_GET);
        }

        $aesCode = 0;
        $method = 'GET';

        if (isset($_REQUEST['username'])) {
            // 舊登入機制：GET(無加密才會用GET)
            $this->username = trim($_REQUEST['username']);
            $this->password = trim($_REQUEST['password']);
            $this->userAgent = isset($_REQUEST['useragent']) ? trim($_REQUEST['useragent']) : '';
            $this->loginType = (isset($_REQUEST['type']) && in_array($_REQUEST['type'], $this->LOGIN_TYPES)) ? trim($_REQUEST['type']) : $this->LOGIN_TYPES[0];
            $this->existIdx = isset($_REQUEST['idx']) ? trim($_REQUEST['idx']) : '';
            $this->kickOff = (empty($_REQUEST['kickOff']) || !isset($_REQUEST['kickOff'])) ? false : $_REQUEST['kickOff'];
        } else {
            $method = 'POST';
            // 新登入機制：POST & json format(有加密才會用POST)
            $jsonUtility = new JsonUtility();

            $inputData = file_get_contents('php://input');
            $postData = $jsonUtility->decode($inputData);
            $type = trim($postData['type']);
            $this->loginType = (isset($type) && in_array($type, $this->LOGIN_TYPES)) ? $type : $this->LOGIN_TYPES[0];

            // 多元登入類型判斷
            switch ($this->loginType) {
            case 'google':
                $this->googleIdToken = isset($postData['gpit']) ? trim($postData['gpit']) : '';
                break;
            case 'qrcode':
                $this->moveLight = isset($postData['moveLight']) ? trim($postData['moveLight']) : '';
                break;
            case 'session':
                $this->existIdx = trim($postData['idx']);
                break;
            default:
                // 一般登入資訊
                $aesCode = intval($postData['aesCode']);
                $this->username = trim($postData['username']);
                $this->password = trim($postData['password']);
                $this->userAgent = isset($postData['useragent']) ? trim($postData['useragent']) : '';
                $this->loginType = (isset($_GET['type']) && in_array($_GET['type'], $this->LOGIN_TYPES)) ? trim($_GET['type']) : $this->LOGIN_TYPES[0];
                $this->existIdx = isset($_GET['idx']) ? trim($_GET['idx']) : '';
                $this->kickOff = (empty($postData['kickOff']) || !isset($postData['kickOff'])) ? false : $postData['kickOff'];
            }
        }

        //if ($method === 'POST') {
        if ($method === 'POST' && $this->loginType === 'wm') {
            // 有加密，需將帳密解密
            if ($aesCode > 0) {
                // 舊加密：有aesCode
                $APPEncrypt = new APPEncrypt();
                $this->username = $APPEncrypt->decrypt($this->username, $aesCode);
                $this->password = $APPEncrypt->decrypt(rawurldecode($this->password), $aesCode);
            } else {
                // 新加密：無aesCode
                $this->username = decryptImmediately($this->username);
                $this->password = decryptImmediately($this->password);
                $this->returnNewFormat = true;
            }
        }

        $this->setUserIP();

        parent::baseAction();
    }

    function setUserIP()
    {
        global $_SERVER;
        //設定使用者的ip
        $this->userIP = explode(
            ',',
            ($_SERVER['HTTP_X_FORWARDED_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'])
        );
        $this->userHost = $this->userIP[0];
    }

    function getUserData($username)
    {
        return dbGetStSr(
            'WM_all_account',
            '*',
            sprintf("`username` = '%s'", mysql_real_escape_string($username))
        );
    }
    /**
     * PRO 標準登入流程 (WM + LDAP)
     * @param $username
     * @param $password
     * @return bool
     */
    function loginByWM($username, $password) {
        // 帳號或密碼為空，則直接回傳驗證失敗
        if ($username === '' || $password === '') {
            $this->responseCode = 503;
            $this->responseMsg = "LOGIN FAIL (Parameter Error)";
            return false;
        }

        // 使用者帳密驗證
        $customAuthAdapter = dirname(__FILE__) . '/../../lib/login/AccountCenter_App.class.php';
        if (!file_exists($customAuthAdapter)) {
            $this->responseCode = 503;
            $this->responseMsg = "LOGIN FAIL (No Implement)";
            return false;
        }

        include($customAuthAdapter);
        $auth = new AccountCenter_APP();
        if (!$auth->auth($username, $password)) {
            $this->responseCode = 1;
            $this->responseMsg = "LOGIN FAIL (Auth Fail)";
            return false;
        }

        $this->wmproLoginData = array (
            'username' => encryptImmediately($this->username),
            'password' => encryptImmediately($this->password)
        );
        return true;
    }
    /**
     * Google+ 登入流程
     * @param $idToken
     * @return bool
     */
    function loginByGooglePlus($idToken) {
        $customAuthAdapter = sysDocumentRoot . '/lib/login/AccountCenter_Google.class.php';
        if (!file_exists($customAuthAdapter)) {
            $this->responseCode = 503;
            $this->responseMsg = "google login (No Implement)";
            return false;
        }

        require_once($customAuthAdapter);

        if (defined('APP_GOOGLE_CLIENT_ID') && APP_GOOGLE_CLIENT_ID !== '') {
            // APP 的 client id 有另外定義的話
            $GoogleLogin = new AccountCenter_Google(false);
            $GoogleLogin->createGoogleClient(array(
                'oauth2_client_id' => APP_GOOGLE_CLIENT_ID,
                'oauth2_client_secret' => APP_GOOGLE_CLIENT_SECRET
            ));
        } else {
            // 自動使用平台設定建立連線
            $GoogleLogin = new AccountCenter_Google();
        }

        // 利用 Access_Token 取得 Google userInfo
        $payload = $GoogleLogin->getPayloadByIdToken($idToken);
        // NOTE: 這邊會有個問題在 app 端用的是 auth，網頁端用的是 login，verifyIdToken 取回的格式可能不太一樣
        $userId = $payload['sub'];

        // 確認平台上有無資料
        if (!$GoogleLogin->isRegisterByGoogleId($userId)) {
            // TODO: 這邊先不在 APP 作註冊流程
            // 沒有資料就新增帳號
            // $GoogleLogin->addGoogleAccount($payload);
            $this->responseCode = 4;
            $this->responseMsg = "Google login Fail";
            return false;
        }

        // 設定帳號供後續程式使用
        $this->username = $GoogleLogin->getUsernameByGoogleId($userId);

        unset($GoogleLogin);

        return true;
    }
    /**
     * QR Code 登入流程
     * @param $moveLight
     * @return bool
     */
    function loginByQRCode($moveLight) {
        global $sysSession;

        $QRCodeLogin = new QRCode_Login();

        // 解析 QRCode
        $qrCodeParams = $QRCodeLogin->parseQRCodeTicket($moveLight);
        if ($qrCodeParams !== false) {
            // 利用 QRCode 資訊登入
            $auth = $QRCodeLogin->chgMySessionByQRCode($qrCodeParams);
            // 設定帳號供後續程式使用
            $this->username = $sysSession->username;
        } else {
            $auth = false;
        }

        // QRCode 處理上有錯誤
        if ($auth === false) {
            $this->responseCode = 1;
            $this->responseMsg = $QRCodeLogin->errorMsg;
        }

        unset($QRCodeLogin);

        return $auth;
    }
    /**
     * Session 登入流程 (for WebView )
     * @param $idx
     * @return bool
     */
    function loginBySession ($idx) {
        // 確認 session 是否存在
        $username = dbGetOne(
            'WM_session',
            '`username`',
            sprintf("`idx` = '%s'", mysql_real_escape_string($idx))
        );

        if (!$username) {
            $this->responseCode = 4;
            return false;
        } else {
            // 設定帳號供後續程式使用
            $this->username = $username;
            return true;
        }
    }

    /**
     * 砍掉 initialize.php 產生的 Guest
     *
     * @return void
     */
    function removeGuestSession()
    {
        if (!empty($_COOKIE['idx']) && strlen($_COOKIE['idx']) === 32) {
            dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
        }
    }

    function main()
    {
        global $sysSession;

        // 驗證登入資訊
        switch ($this->loginType) {
        case 'google':
            $auth = $this->loginByGooglePlus($this->googleIdToken);
            break;
        case 'qrcode':
            $auth = $this->loginByQRCode($this->moveLight);
            break;
        case 'session':
            $auth = $this->loginBySession($this->existIdx);
            // 將舊 idx 移除
            dbDel('`WM_session`', sprintf("`idx` = '%s'", mysql_real_escape_string($this->existIdx)));
            break;
        default:
            $auth = $this->loginByWM($this->username, $this->password);
        }

        if ($this->returnNewFormat) {
            if ($auth !== true) {
                // 砍掉 initialize.php 產生的 Guest
                $this->removeGuestSession();
                $this->returnHandler($this->responseCode, "Auth fail" . '::loginType:' . $this->loginType, array (
                    'session_idx' => '',
                    'session_username' => '',
                    'session_realname' => ''
                ));
            }

            // 透過session handler處理跟session有關的動作
            $sessionHandler = new APPSessionHandler();
            $this->sessionResult = $sessionHandler->mainHandler('', $this->username, $this->kickOff);

            if ($this->sessionResult['code'] === 0) {
                $_COOKIE['idx'] = $this->sessionResult['data']['session_idx'];

                //取得使用者的資料, 以陣列回傳
                $userData = $this->getUserData($this->username);

                // 設定school hash
                $skey = md5($_SERVER['HTTP_HOST'].$sysSession->school_id);
                $schoolHash = substr($skey, 0, 17) . $sysSession->school_id . substr($skey, -10);
                setcookie('school_hash', $schoolHash, 0, '/');

                // 登入寫 Log
                $msg = 'App User Login';
                appSysLog(999999001, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], $msg, $userData['username'], $this->userAgent);

                dbSet(
                    'WM_sch4user',
                    "`login_times`=`login_times` + 1, `last_login` = NOW(), `last_ip` = '{$this->userHost}'",
                    sprintf("school_id=%d AND username='%s'", $sysSession->school_id, $this->username)
                );

                // 設定cookie
                setcookie('idx', $_COOKIE['idx'], 0, '/');

                $_COOKIE['url'] = WM_SERVER_HOST;
                /**
                 * 舊的session_data資料項目
                 * 'session_data' => array (
                 *     'ticket'=> $this->sessionId,
                 *     'username' => $this->>username
                 * )
                 **/
                $this->returnHandler($this->responseCode, $this->responseMsg, array (
                    'cookie_data' => $_COOKIE,
                    'session_data' => array(
                        'ticket' => $this->sessionResult['data']['session_idx'],
                        'username' => $this->username
                    ),
                    'idx_data' => $this->sessionResult['data'],
                    'login_data' => $this->wmproLoginData,
                    'language' => $this->sessionResult['data']['session_language'],
                    'socket_domain'=> 'http://' . $_SERVER['SERVER_NAME']   // 根據 irs 指定 server 修改
                ));
            } else {
                $this->returnHandler($this->sessionResult['code'], $this->sessionResult['message'], array (
                    'idx_data' => $this->sessionResult['data']
                ));
            }
        } else {
            if ($auth !== true) {
                // 砍掉 initialize.php 產生的 Guest
                $this->removeGuestSession();
                $this->returnHandler($this->responseCode, $this->responseMsg);
            }

            //取得使用者的資料, 以陣列回傳
            $userData = $this->getUserData($this->username);
            if (!isset($userData['username'])) {
                // 砍掉 initialize.php 產生的 Guest
                $this->removeGuestSession();
                $this->returnHandler(2, "Login fail (User data not found.)");
            }

            // 確認是否允許重複登入
            $multiLoginSetting = dbGetOne('WM_school', 'multi_login', "school_id={$sysSession->school_id} AND school_host='{$_SERVER['HTTP_HOST']}'");
            if ($multiLoginSetting == 'N') {
                $times = dbGetOne('WM_session', 'count(*) AS times', "username='{$this->username}'");
                $skipUsers = array('root', 'sunnet');

                if ($times > 0  && !in_array($this->username, $skipUsers)) {
                    // 若有重複登入，則將前一個登入者登出
                    dbDel('WM_session',    "username='{$this->username}'");
                    dbDel('WM_auth_ftp',   "userid='{$this->username}'");
                }
            }

            // 砍掉 initialize.php 產生的 Guest
            $this->removeGuestSession();

            // 建立個人ini
            $this->sessionId = $sysSession->init($userData);
            $_COOKIE['idx'] = $this->sessionId;
            $this->language = ($userData['language'] === NULL) ? 'Big5' : $userData['language'];

            // 設定cookie
            setcookie('idx', $this->sessionId, 0, '/');

            // 登入次數累加
            dbSet(
                'WM_sch4user',
                "`login_times`=`login_times`+1, `last_login`=NOW(), `last_ip`='" . wmGetUserIp() . "'",
                sprintf("school_id=%d AND username='%s'", $sysSession->school_id, $userData['username'])
            );

            // 登入寫 Log
            $msg = 'App User Login';
            appSysLog(999999001, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], $msg, $userData['username'], $this->userAgent);

            $this->returnHandler($this->responseCode, $this->responseMsg, array(
                'cookie_data' => $_COOKIE,              // 給app底層set cookie 用
                'session_data' => array(
                    'ticket'=> $this->sessionId,        // 給app前端呼叫web service用
                    'username' => $this->username
                ),
                'language' => $this->language,
                'socket_domain'=> 'http://' . $_SERVER['SERVER_NAME']   // 根據 irs 指定 server 修改
            ));
        }
    }
}

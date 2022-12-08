<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/lib/jsonwrapper/jsonwrapper.php');
    require_once(sysDocumentRoot . '/lib/login/login.inc');
    require_once(sysDocumentRoot . '/mooc/models/school.php');
     
    // 取 FB 參數
    $rsSchool = new school();
    $FBPara = $rsSchool->getSchoolFBParameter($sysSession->school_id);
    define('FB_APP_ID', $FBPara['canReg_fb_id']);
    define('FB_APP_SECRET', $FBPara['canReg_fb_secret']);
    /**
     * 驗證帳號密碼是否正確, 並取得使用者的資訊
     * @param string $user       帳號
     * @param bool   $skipPasswd 是否略過密碼檢查
     * @return mixed
     */
    function &getUserInfo($user, $skipPasswd = true, $return = false)
    {
        global $UsersValidedByWM3;

        //檢查使用者是否不經帳號中心驗證
        if (is_array($UsersValidedByWM3) && in_array($user, $UsersValidedByWM3)) {
            return getUserInfoFromWM3($user, $skipPasswd, $return);
        }

        //依據設定的帳號中心取得使用者資料
        switch(AccountCenter)
        {
            case "WM3":
                return getUserInfoFromWM3($user, $skipPasswd, $return);
                break;
            case "LDAP":
                include_once(sysDocumentRoot . '/lib/login/AccountCenter_LDAP.class');
                $obj = new AccountCenter_LDAP();
                return $obj->getUserInfo($user);
                break;
            case "OTHDB":
                include_once(sysDocumentRoot . '/lib/login/AccountCenter_OTHDB.class');
                $obj = new AccountCenter_OTHDB();
                return $obj->getUserInfo($user);
                break;
            case "WEBSERVICES":
                include_once(sysDocumentRoot . '/lib/login/AccountCenter_WS.class');
                $obj = new AccountCenter_WS();
                return $obj->getUserInfo($user);
            default:
                return getUserInfoFromWM3($user, $skipPasswd, $return);
                break;
        }
    }

    /**
     * 設定 Session 資料
     * @param $username
     */
    function setLoginInfo($username)
    {
        global $sysSession;

        //3.1 建立個人ini
        setUserIni($username);
        //3.2 移除原cookie中idx舊的session資料
        removeExpiredSessionIdx($_COOKIE['idx']);
        //3.3 移除之前同一位使用者的ftp認證設定資料
        removeExpiredFtpAuth();
        //3.4 重設新的idx資料
        $userinfo = getUserInfo($username);
        $idx = $sysSession->init($userinfo);
        $_COOKIE['idx'] = $idx;
        $sysSession->restore();
        setcookie('school_hash', $_COOKIE['school_hash']);
    }

    /**
     * 轉換 facebook 的 user id 為 WMPro 的 username
     * @param int $id facebook 的 user id
     * @return string WMPro 的 username，若沒有則回傳 false
     */
    function fbid2Username($id)
    {
        global $sysConn;

        $sysConn->Execute('use ' . sysDBname);
        $fetchMode = $sysConn->fetchMode;
        $sysConn->fetchMode = ADODB_FETCH_ASSOC;
        $rs = $sysConn->GetRow('select * from CO_fb_account where `id`=?', array($id));
        $sysConn->fetchMode = $fetchMode;

        if (count($rs) > 0) {
            $username = $rs['username'];
            $res = checkUsername($username);
            if ($res === 2 || $res === 4) {
                return $username;
            }
        }
        return false;
    }

    if (!function_exists('http_build_query')) {
        function http_build_query($data, $prefix = '', $sep = '', $key = '')
        {
            $ret = array();
            foreach ((array)$data as $k => $v) {
                if (is_int($k) && $prefix != null) {
                    $k = urlencode($prefix . $k);
                }
                if ((!empty($key)) || ($key === 0)) {
                    $k = $key . '[' . urlencode($k) . ']';
                }
                if (is_array($v) || is_object($v)) {
                    array_push($ret, http_build_query($v, '', $sep, $k));
                } else {
                    array_push($ret, $k . '=' . urlencode($v));
                }
            }
            if (empty($sep)) {
                $sep = ini_get('arg_separator.output');
            }
            return implode($sep, $ret);
        }
    }

    if (!function_exists('curl_setopt_array')) {
        function curl_setopt_array(&$ch, $curl_options)
        {
            foreach ($curl_options as $option => $value) {
                if (!curl_setopt($ch, $option, $value)) {
                    return false;
                }
            }
            return true;
        }
    }

    function getRemoteData($uri, $data = null, $method = 'GET', &$status)
    {
        $uri = trim($uri);
        $ua = $_SERVER['HTTP_USER_AGENT'];

        $options = array(
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => $ua,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 60
        );

        $method = strtoupper($method);
        // 啟動 POST
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
        }

        if (!is_null($data) && is_array($data)) {
            if ($method === 'POST') {
                $options[CURLOPT_POSTFIELDS] = http_build_query($data);
            } else if ($method === 'GET') {
                if (strpos($uri, '?') === false) {
                    $uri .= '?' . http_build_query($data);
                } else {
                    $uri .= '&' . http_build_query($data);
                }
            }
        }
        $options[CURLOPT_URL] = $uri;

        $ch  = curl_init();
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);

        return $output;
    }
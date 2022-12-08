<?php
    if (!function_exists('http_build_query')) {
        function http_build_query($data, $prefix = '', $sep = '', $key = '') {
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
        function curl_setopt_array(&$ch, $curl_options) {
            foreach ($curl_options as $option => $value) {
                if (!curl_setopt($ch, $option, $value)) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * @param string $uri   遠端網址
     * @param array  $data  POST 的資料
     * @return mixed|string
     */
    function getReomteData($uri, $data = array())
    {

//        // 判斷 DNS 設定
//        $url = parse_url($uri);
//        exec("ping -c 1 " . $url['host'], $output, $return_var);
//        if ($return_var === 1) {
//            preg_match_all("(([0-9]+).([0-9]+).([0-9]+).([0-9]+))", $output[0], $matches);
//            die('IP of the LCMS domain is not exist: ' . $matches[0][0] . ' (' . $url['host'] . '), please contact IT Manager.');// lcms網域對應的ip不存在
//        } elseif ($return_var === 2) {
//            die('Unknown LCMS domain: ' . $url['host'] . ', please contact IT Manager.');// wm站台不認識lcms網域
//        }

        $uri = trim($uri);
        $ua = $_SERVER['HTTP_USER_AGENT'];

        preg_match("/^([a-z]{4,5}):\/\//", $uri, $match);
        if (empty($_COOKIE['showmeinfo']) === false) {
            echo '<pre>';
            var_dump('uri', $uri);
            var_dump('http協定', $match[1]);
            echo '</pre>';
        }

        if ($match[1] === 'http') {
            $options = array(
                CURLOPT_URL            => $uri,
                CURLOPT_HEADER         => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT      => $ua,
                CURLOPT_FOLLOWLOCATION => true,
            );
        } else {
            $options = array(
                CURLOPT_URL            => $uri,
                CURLOPT_HEADER         => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT      => $ua,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => 0,// lcms有使用ssl憑證時才開啟
                CURLOPT_SSL_VERIFYPEER => false// lcms有使用ssl憑證時才開啟
            );
        }
        if (empty($_COOKIE['showmeinfo']) === false) {
            echo '<pre>';
            var_dump('CURL參數', $options);
            echo '</pre>';
        }

        // 啟動 POST
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($data);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 404) {
            return '';
        }

        // 判斷 DNS 設定
        if ($output === false) {
            $url = parse_url($uri);
            die('Unknown LCMS domain: ' . $url['host'] . ', please contact IT Manager.');// wm站台不認識lcms網域
        }

        return $output;
    }

    /**
     * 取得要給 LCMS 驗證的資料
     * @param int $csid 課程編號
     * @return mixed|bool
     */
    function getLcmsVerifyData($csid, $otherData=null )
    {
        global $sysRoles, $sysSession;

        // 取個人基本資料，使透過教材資源庫透通時可以更新
        $rs = dbGetStMr(
            'WM_user_account',
            'email, department, office_tel, cell_phone', sprintf("username='%s'", $sysSession->username),
            ADODB_FETCH_ASSOC
        );
        if ($rs) {
            while (!$rs->EOF) {
                $user['email'] = $rs->fields['email'];
                $user['department'] = $rs->fields['department'];
                $user['office_tel'] = $rs->fields['office_tel'];
                $user['cell_phone'] = $rs->fields['cell_phone'];

                $rs->MoveNext();
            }
        }
        unset($rs);

        // 取wm http協定
        $schoolProtocol = 'http';
//        echo '<pre>';
//        var_dump($_SERVER['SCRIPT_URI']);
//        var_dump($_SERVER['HTTP_REFERER']);
//        echo '</pre>';
        if ($_SERVER['HTTP_REFERER']) {
            preg_match('/^(http[s]*):\/\//', $_SERVER['HTTP_REFERER'], $matches);
//            echo '<pre>';
//            var_dump('$matches[1]', $matches[1]);
//            echo '</pre>';
            $schoolProtocol = $matches[1];
        } else if (preg_match('/.cycu.edu.tw/', $_SERVER['SCRIPT_URI'])) {
//            echo '<pre>';
//            var_dump('url match');
//            echo '</pre>';
            $schoolProtocol = 'https';
        }

        $uid = md5(uniqid(rand(),1));
        $ticket = md5(sysSiteUID . sysTicketSeed . $uid);
        $otherData = is_array($otherData) ? $otherData : array();
        $data = array_merge($otherData, array(
            'idx'      => $_COOKIE['idx'],
            'username' => $sysSession->username,
            'realname' => $sysSession->realname,
            'course_id' => $sysSession->course_id,
            'course_name' => $sysSession->course_name,
            'school_id' => $sysSession->school_id,
            'school_name' => $sysSession->school_name,
            'school_ip' => $_SERVER['SERVER_ADDR'],
            'school_port' => $_SERVER['SERVER_PORT'],
            'school_domain' => $_SERVER['SERVER_NAME'],
            'school_protocol' => $schoolProtocol,
            'time' => time(),
            'teachers' => array(),
            'ticket'   => $ticket,
            'email' => $user['email'],
            'department' => $user['department'],
            'tel' => $user['office_tel'],
            'cell_phone' => $user['cell_phone'],
            'choose_teaching_default' => 'resources',// 辦公室-學習路徑維護，挑選「LCMS教材」時，可決定預設開啟哪個頁籤（教學資源庫 contents、我的資源 resources）
            'enter_warehouse_default' => 'resources',// 辦公室-教材上傳，點選「教材資源庫」時，可決定預設開啟哪個頁籤（教學資源庫 contents、我的資源 resources）
        ));

        // echo '<pre>';
        // var_dump('getLcmsVerifyData $data', $data);
        // echo '</pre>';

        $teachers = array();

        $roles = $sysRoles;

        // 取老師群(扣掉學生以下，考慮學校可能有客製其他角色)
        unset($roles['guest']);
        unset($roles['senior']);
        unset($roles['paterfamilias']);
        unset($roles['superintendent']);
        unset($roles['auditor']);
        unset($roles['student']);
        unset($roles['manager']);
        unset($roles['administrator']);
        unset($roles['root']);
        unset($roles['all']);

        if (empty($_COOKIE['showmeinfo']) === false) {
            echo '<pre>';
            var_dump('傳送到lcms的角色', $roles);
            echo '</pre>';
        }

        $role = 0;
        foreach ($roles as $v) {
            $role = $role + (int)$v;
        }

        if (!empty($csid)) {
            $rs = dbGetStMr(
               'WM_term_major',
               'username, role', 'course_id=' . $csid . ' AND role & ' . $role,
                ADODB_FETCH_ASSOC
            );
        } else {
            $rs = dbGetStMr(
               'WM_term_major',
               'distinct username, role', 'username="' . $sysSession->username . '" AND role & ' . $role,
                ADODB_FETCH_ASSOC
            );

        }

        if ($rs !== false) {
            while($fields = $rs->FetchRow()){
//                $data['teachers'][] = $fields['username'];
                // 取老師
//                if ((int)$fields['role'] === ($fields['role'] | $roles['teacher'])) {
//                    $teachers[] = $fields['username'];
//                }
                if (((int)$fields['role'] === ($fields['role'] | $roles['teacher'])) || ((int)$fields['role'] === ($fields['role'] | $roles['assistant'])) || ((int)$fields['role'] === ($fields['role'] | $roles['instructor']))) {
                    $teachers[] = $fields['username'];
                }
            }
        }
        if (empty($_COOKIE['showmeinfo']) === false) {
            echo '<pre>';
//            var_dump('老師、講師、助教');
//            var_dump($data['teachers']);
//            var_dump('登入者');
//            var_dump(array($sysSession->username));
//            var_dump('交集');
//            var_dump(array_intersect($data['teachers'], array($sysSession->username)));
            var_dump('本課程老師群');
            var_dump($teachers);
            echo '</pre>';
        }

        // wm教師群太多導致url太長，apache回傳「Request-URI Too Large」，故改成傳交集過去即可
        // 因應助教無法上課去
//        $intersect = array_intersect($data['teachers'], array($sysSession->username));
//        if (count($intersect) >= 1){
//            $data['teachers'] = $intersect;
//        } else {
//            $data['teachers'] = $teachers;
//        }
//        if (empty($_COOKIE['showmeinfo']) === false) {
//            echo '<pre>';
//            var_dump('傳送到lcms的帳號', $data['teachers']);
//            echo '</pre>';
//        }
//        echo '<pre>';
//        var_dump($data);
//        echo '</pre>';
//        die();

        // 決定要傳哪些老師過去 lcms
        // 因應 教材資源庫和上課去做調整，前者不用問教材的擁有者群，所以取登入者（通常是老師、助教、講師）與課程老師群做交集，後者傳教材擁有者群與課程老師群做交集
        if (empty($_COOKIE['showmeinfo']) === false) {
            echo '<pre>';
            var_dump('學習節點編號', getResourceHref($_GET['rid']));
            echo '</pre>';
        }
        $rid = htmlspecialchars($_GET['rid']);
        if (mb_strlen($rid, 'utf-8') >= 1) {
            // 取 lcms 參數
            preg_match('/\/(courses|unit|asset)*\/play\/([0-9]*)/', getResourceHref($rid), $matches);
            if (empty($_COOKIE['showmeinfo']) === false) {
                echo '<pre>';
        //        var_dump($matches[1]);
        //        var_dump(ucfirst($matches[1]));
        //        var_dump($matches[2]);
                var_dump(sysLcmsHost . '/lms/get' . ucfirst($matches[1]) . 'InfoById/' . $matches[2]);
                echo '</pre>';
            }

            // 取 lcms教材 擁有者群（擁有者、編輯者）資訊
            $info = getReomteData(
                sysLcmsHost . '/lms/get' . ucfirst($matches[1]) . 'InfoById/' . $matches[2]
            );

            // 增加相容性，有些學校可能lcms還是舊版
            // 偵測回應值，如果是空值，則傳老師群；如果有數值，則傳
            if (empty($_COOKIE['showmeinfo']) === false) {
                echo '<pre>';
                var_dump('教材路徑', sysLcmsHost . '/lms/get' . ucfirst($matches[1]) . 'InfoById/' . $matches[2]);
                var_dump('回傳的資料', $info);
                echo '</pre>';
            }

            // 使用lcms傳來的教材擁有者，與目前wm的教師群進行交集
            if (mb_strlen($info, 'utf-8') >= 1){
                $users = json_decode($info, true);
                if (empty($_COOKIE['showmeinfo']) === false) {
                    echo '<pre>';
                    var_dump($users);
                    var_dump('lcms教材 擁有者群（擁有者、編輯者）', $users['data']);
                    var_dump('wm課程老師群（老師、助教、講師）', $teachers);
                    var_dump('交集', array_intersect($users['data'], $teachers));
                    echo '</pre>';
                }

                // 取lcms教材擁有者群與本門課老師群做交集
                if (is_array($users['data']) && is_array($teachers)) {
                    $data['teachers'] = array_intersect($users['data'], $teachers);
                } else {
                    $data['teachers'] = array();
                }
            // 傳老師群過去，可能造成url過長問題
            } else {
                $data['teachers'] = $teachers;
            }

            $isTableExist = dbGetOne('`INFORMATION_SCHEMA`.`TABLES`', 'TABLE_NAME', sprintf("`table_schema` = '%s' AND `table_name` = '%s'", sysDBprefix . $sysSession->school_id, 'LM_read_video_log'));
            if ($isTableExist) {
                // 取是否閱讀完畢
                $finish = dbGetOne('`LM_read_video_log`', 'count(`course_id`) cnt', sprintf("`course_id` = '%s' AND `username` = '%s' AND `activity_id` = '%s' AND action_id = 'ended'", $sysSession->course_id, $sysSession->username, $rid));
                if ((int)$finish >= 1) {
                    $data['video_read_finish'] = '1';
                } else {
                    $data['video_read_finish'] = '0';
                }
            } else {
                $data['video_read_finish'] = '0';
            }
        } else {
            if (is_array($teachers)) {
                $data['teachers'] = $teachers;
            } else {
                $data['teachers'] = array();
            }
        }

        if (empty($_COOKIE['showmeinfo']) === false) {
            echo '<pre>';
            var_dump('傳送過去的老師群', $data['teachers']);
            echo '</pre>';
        }
//        die();

//        error_reporting(E_ALL);
//        ini_set("display_errors", 1);

        $key = 'wmpro_lcms_pqal' . $ticket;

//        // 確認LCMS PHP版本
//        $lcmsPhpVersion = getReomteData(
//            sysLcmsHost . '/lms/getPhpVersion'
//        );
//        echo '<pre>';
//        var_dump('lcmsPhpVersion', $lcmsPhpVersion);
//        var_dump('wmPhpVersion', PHP_VERSION);
//        echo '</pre>';

//        if (version_compare(PHP_VERSION, '7.1.0') >= 0 && version_compare($lcmsPhpVersion, '7.1.0') >= 0) {
//            echo '<pre>';
//            var_dump('use opensslEncrypt Func');
//            echo '</pre>';
//            $enc = opensslEncrypt(serialize($data), $key);
//        } else {
//            echo '<pre>';
//            var_dump('use sysNewEncode Func');
//            echo '</pre>';
            $enc = sysNewEncode(serialize($data), $key, true, MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
//        }

//        echo '<pre>';
//        var_dump('lib_lcms.php', sysLcmsHost . '/lms/token', serialize($data), $key, $ticket);
//        var_dump($enc);
//        var_dump('PHP_VERSION', PHP_VERSION, $lcmsPhpVersion, min(PHP_VERSION, $lcmsPhpVersion));
//        echo '</pre>';
//        die();

        /* Begin of 測試解密 */
//        $dec  = opensslDecrypt($enc, $key);
//        echo '<pre>';
//        var_dump('測試解密', $dec);
//        echo '</pre>';
        /* End of 測試解密 */

        $token = getReomteData(
            sysLcmsHost . '/lms/token',
            array(
                'data' => $enc,
                'ticket' => $ticket//,
//                'phpversion' => min(PHP_VERSION, $lcmsPhpVersion)
            )
        );

    //    echo '<pre>';
    //    var_dump('getLcmsVerifyData $token', $token);
    //    echo '</pre>';

        if (empty($_COOKIE['show_me_stop']) === false) {
            die();
        }

        if ($token === '' && empty($_COOKIE['showmeinfo']) === false) {
            echo '<div>';
            echo '無權限 或 lcms主機服務已終止，請檢查lcms主機服務狀況';
            echo '</div>';
            return false;
        }

        $data['ticket'] = $token;

        // echo '<pre>';
        // var_dump($data);
        // echo '</pre>';

        return $data;
    }

    /*
     * 取上課中cookie到lcms，若無老師群則回報清空cookie
     *
     * $mode String forced 不論是否過期，強制給予lcms cookie指令/ auto判斷建立時間，決定是否要給予lcms cookie指令
     */
    function setWmLearningHashCookie($mode = 'forced') {

        global $sysSession, $sysRoles;
        $pathWmCookieHash2Lcms = '';

        if (empty($_COOKIE['showmeinfo']) === FALSE) {
            echo '<pre>';
            var_dump(sysLcmsEnable);
            var_dump($sysSession->course_id);
            var_dump(preg_match('/^\d{8}$/', $sysSession->course_id));
            echo '</pre>';
        }
        if (sysLcmsEnable && preg_match('/^\d{8}$/', $sysSession->course_id)) {
            require_once(sysDocumentRoot . '/lib/acl_api.php');

            // 取課程老師群
            $rs = dbGetStMr(
                'WM_term_major',
                'username, role',
                'course_id = ' . $sysSession->course_id . ' AND role&' . ($sysRoles['teacher']|$sysRoles['assistant']),
                ADODB_FETCH_ASSOC
            );
            while($fields = $rs->FetchRow()){
                $teachers[] = $fields['username'];
            }
            if (empty($_COOKIE['showmeinfo']) === FALSE) {
                echo '<pre>';
                var_dump($teachers);
                echo '</pre>';
            }

            // 有老師群才設定 cookie
            if ($teachers) {
                // 到 lcms 設定 wm cookie_hash
                $protocol = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
                $wmCookieHash = _3desEncode(json_encode(array('time' => time(), 'teachers' => $teachers, 'school_domain' => $protocol . '://' . $_SERVER['SERVER_NAME'], 'remote_addr' => $_SERVER['REMOTE_ADDR'], 'username' => $sysSession->username)));
                if (empty($_COOKIE['showmeinfo']) === FALSE) {
                    echo '<pre>';
                    var_dump($wmCookieHash);
                    echo '</pre>';
                }

                $isSetCookie = TRUE;
                // 自動判斷模式，會判斷 wm_learning_hash_start 是否即將逾時，如果1小時候逾時，則提供 lcms set cookie url
                if ($mode === 'auto' && $_COOKIE['wm_learning_hash_start']) {
                    $isSetCookie = FALSE;

                    $wmLearningHashStart = sysNewDecode($_COOKIE['wm_learning_hash_start'], 'wm_learning_hash');
                    $wmLearningHashStart = explode('|', $wmLearningHashStart);
                    if (empty($_COOKIE['showmeinfo']) === FALSE) {
                        echo '<pre>';
                        var_dump('wmLearningHashStartCid', $wmLearningHashStart[0]);
                        var_dump('cid', $sysSession->course_id);
                        var_dump('wmLearningHashStartTime', $wmLearningHashStart[1]);
                        var_dump('now', time());
                        var_dump('time diff', (time() - $wmLearningHashStart[1]));
                        var_dump('over time after 1 hour ?', ((time() - $wmLearningHashStart[1]) >= (86400 - (60 * 60))));
                        echo '</pre>';
                    }

                    // 同一門課且即將逾時
                    if ($wmLearningHashStart[0] === $sysSession->course_id && ((time() - $wmLearningHashStart[1]) >= (86400 - (60 * 60)))) {
                        $isSetCookie = TRUE;
                    }
                }

                if ($isSetCookie === TRUE) {
                    $pathWmCookieHash2Lcms = sysLcmsHost . '/lms/wmcookie/set/' . rawurlencode(base64_url_encode($wmCookieHash));
    //                getReomteData($pathWmCookieHash2Lcms);

                    // 寫入 wm_learning_hash_start
                    if ($_SERVER['HTTPS']){
                        $http_secure = TRUE;
                    } else {
                        $http_secure = FALSE;
                    }
                    setcookie('wm_learning_hash_start', sysNewEncode($sysSession->course_id . '|' .time(), 'wm_learning_hash'), time() + 86400, '/', '', $http_secure);
                }
            } else {
                $pathWmCookieHash2Lcms = sysLcmsHost . '/lms/wmcookie/clear';
                setcookie('wm_learning_hash_start', '', time() - 3600, '/');
            }

            return $pathWmCookieHash2Lcms;
        }
    }
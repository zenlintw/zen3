<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/mooc/lib/fb_login_lib.php');
    require_once(sysDocumentRoot . '/lang/fb_login.php');

    function jsonOutput($data)
    {
        header('Content-type: application/json');
        echo json_encode($data);
        exit;
    }

    session_start();
    $user = $_SESSION['facebook_user'];

    if ($sysSession->username !== 'guest') {
        // 已經登入，重導回 MOOC 首頁
        header('Location: /mooc/index.php');
        exit();
    }

    // 產生 login key
    $uid      = md5(uniqid(rand(), 1));
    $loginKey = md5(sysSiteUID . sysTicketSeed . $uid);
    dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');

    $username = trim($_POST['username']);
    $userinfo = getUserInfo($username, false, true);
    if ($userinfo !== false) {
        // 帳號驗證通過
        // 檢查帳號是否已經綁定其它 facebook 帳號
        $sysConn->Execute('use ' . sysDBname);
        $row = $sysConn->GetRow('SELECT `id` FROM CO_fb_account WHERE `username`=?', array($username));
        if ((count($row) > 0) && ($row['id'] !== $user->id)) {
            // 已經有綁定 facebook 帳號，但是是不同的 id
            dbNew('WM_prelogin', 'login_seed, uid, log_time', "'$loginKey', '$uid', NOW()");
            $result = array(
                'code'    => 1,
                'message' => $MSG['username_combine'][$sysSession->lang],
                'ticket'  => $loginKey
            );
            jsonOutput($result);
        }

        unset($_SESSION['facebook_user']);
        // 新增 facebook 跟 account 的關聯
        $sysConn->Execute(
            'insert into CO_fb_account (`id`, `username`) values (?, ?)',
            array($user->id, $userinfo['username'])
        );
        setLoginInfo($username);
        $result = array(
            'code'    => 0,
            'message' => ''
        );
        jsonOutput($result);
    }

    dbNew('WM_prelogin', 'login_seed, uid, log_time', "'$loginKey', '$uid', NOW()");
    // 帳號驗證失敗，重新驗證
    $result = array(
        'code'    => 1,
        'message' => $MSG['username_password_error'][$sysSession->lang],
        'ticket'  => $loginKey
    );
    jsonOutput($result);
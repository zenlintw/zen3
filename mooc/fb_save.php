<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/mooc/lib/fb_login_lib.php');

    session_start();
    $user = $_SESSION['facebook_user'];
    unset($_SESSION['facebook_user']);

    if ($sysSession->username !== 'guest') {
        // 已經登入，重導回 MOOC 首頁
        header('Location: /mooc/index.php');
        exit();
    }

    $username = trim($_POST['username']);
    if ($username === '') {
        header('Location: /mooc/fb_login.php');
        exit();
    }

    $email = trim($_POST['email']);

    $data = array(
        'first_name' => $_POST['first_name'],
        'email'      => $_POST['email']
    );

    $res = checkUsername($username);
    if ($res <= 0) {
        addUser($username, $data, 'Y');
        // 新增 facebook 跟 account 的關聯
        $sysConn->Execute('use ' . sysDBname);
        $sysConn->Execute(
            'insert into CO_fb_account (`id`, `username`) values (?, ?)',
            array($user->id, $username)
        );
        setLoginInfo($username);
        header('Location: /mooc/index.php');
    } else {
        header('Location: /mooc/fb_login.php');
    }
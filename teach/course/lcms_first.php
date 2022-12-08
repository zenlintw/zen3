<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/lib_lcms.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    if (!sysLcmsEnable) {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

	list($ps) = dbGetStSr('WM_user_account', '`password`', "`username` = '{$sysSession->username}'", ADODB_FETCH_NUM);

    $lcms = sysLcmsHost . '/lms/first';
    $token = urlencode(_3desEncode(json_encode(array('username' => $sysSession->username, 'password' => $ps))));
    $get = sprintf('?token=%s', $token);

    header('Location: ' . $lcms . $get);
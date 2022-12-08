<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot .'/lib/lib_forum.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '900200700';
    $sysSession->restore();
    if (!aclVerifyPermission(
        $sysSession->cur_func,
        aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

    $std = "/^([0-9]+)$/";
    if (strlen($_POST['bid']) === 10 && preg_match($std, $_POST['bid']) === 1) {
        $bid = $_POST['bid'];
        $sysSession->board_id = $bid;
        $sysSession->restore();
    } else {
        $bid = $sysSession->board_id;
    }
    if (ereg('^520,([0-9]{10}),([0-9]{6,}),([0-9]{10})\.php$', basename($_SERVER['PHP_SELF']), $reg) &&
        $reg[1] == $bid
       ) {
        delete_post($reg[1], $reg[2], $reg[3]);
    }

    header('Location: read.php');
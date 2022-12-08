<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/common.php');

    if (!$profile['isPhoneDevice']) {
        header('Location: /mooc/index.php');
        exit;
    }

    define('SHOW_PHONE_UI', 1);

    $smarty->display('common/tiny_header.tpl');
    $smarty->display('common/course_header.tpl');
    // 載入wmpro5原來的程式
    include(sysDocumentRoot . '/teach/chat/chat_save.php');
    $smarty->display('common/tiny_footer.tpl');
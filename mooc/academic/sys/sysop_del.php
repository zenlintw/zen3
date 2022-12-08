<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/mooc/controllers/JSON.php');
    require_once(sysDocumentRoot . '/academic/sys/lib.php');

    if (!$profile['isPhoneDevice']) {
        header('Location: /mooc/index.php');
        exit;
    }

    // 取出操作此功能的管理者的等級
    $level = intval(getAllSchoolTopAdminLevel($sysSession->username));
    if ($level < $sysRoles['administrator']) $level = intval(getAdminLevel($sysSession->username));
    if ($level <= 0) {
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '不具備管理者的權限!');
        die(json_encode($MSG['not_sysop'][$sysSession->lang]));
    }

    $sqls  = '';
    $sysop = array();
    $user  = array();

    // 建立刪除人員列表 (Begin)
    if (checkUsername($_POST['username']) !== 2) {
        die(json_encode('Error Params:1.'));
    }

    $username = trim($_POST['username']);
    if ($username == sysRootAccount) {
        die(json_encode('Error Params.'));
    }

    $delData = dbGetRow('WM_manager','*',sprintf("username='%s' and school_id=%d",$username,$sysSession->school_id), ADODB_FETCH_ASSOC);
    if ($delData['permit'] >= $level) {
        die(json_encode($MSG['msg_del_fail'][$sysSession->lang]));
    }

    // 刪除管理者
    dbDel('WM_manager',sprintf("username='%s' and school_id=%d",$username,$sysSession->school_id));
    wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager', $_SERVER['PHP_SELF'], "刪除管理者name={$username}, school={$sysSession->school_id} success!");
    die(json_encode('OK'));
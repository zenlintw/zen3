<?php
/**
 * 刪除帳號
 * $Id: stud_remove.php,v 1.1 2010/02/24 02:38:45 saly Exp $
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/stud_account.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

$sysSession->cur_func = '400300200';
$sysSession->restore();
if (!aclVerifyPermission(400300200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$msgtp = $_POST['msgtp'] ? $_POST['msgtp'] : ($_GET['msgtp'] ? $_GET['msgtp'] : 1);
$msgtp = min(3, max(1, $msgtp));

$searchkey = $_POST['searchkey'] ? $_POST['searchkey'] : ($_GET['searchkey'] ? $_GET['searchkey'] : 1);

if ($_POST['keyword'] != '') {
    $keyword  = addslashes(trim($_POST['keyword']));
    $keyword1 = stripslashes(trim($_POST['keyword']));
} else {
    $keyword  = addslashes(addslashes(trim($_GET['keyword'])));
    $keyword1 = trim($_GET['keyword']);
}

if (($_POST['order_by'] == '') && ($_GET['order_by'] == '')) {
    $order_by = 'asc';
} else if (($_POST['order_by'] == 'asc') || ($_GET['order_by'] == 'asc')) {
    $order_by = 'asc';
} else if (($_POST['order_by'] == 'desc') || ($_GET['order_by'] == 'desc')) {
    $order_by = 'desc';
}

// 每頁顯示幾筆
$page_num = $_POST['page_num'] ? intval($_POST['page_num']) : ($_GET['page_num'] ? intval($_GET['page_num']) : sysPostPerPage);

// 設定車票
setTicket();

$js = <<< BOF
/*  刪除連續帳號 &　刪除不規則帳號　&　刪除匯入帳號 (delete) */
function chgHistory(val) {
    var obj = document.getElementById("actFm");
    if ((typeof(obj) != "object") || (obj == null)) return false;
    obj.msgtp.value = val;
    obj.submit();
}

BOF;

showXHTML_head_B($MSG['delete_account'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('inline', $js);
showXHTML_head_E();

showXHTML_body_B();

    $arry[] = array(
        $MSG['del_serial_account'][$sysSession->lang],
        'delTable1',
        'chgHistory(1);'
    );
    $arry[] = array(
        $MSG['del_discrete_account'][$sysSession->lang],
        'delTable2',
        'chgHistory(2);'
    );
    $arry[] = array(
        $MSG['import_del_account'][$sysSession->lang],
        'delTable3',
        'chgHistory(3);'
    );

    switch ($msgtp) {
        case 1:
            $form_id    = 'delFm1';
            $form_extra = 'action="stud_remove1.php" method="post" onsubmit="return chkData();" style="display:inline"';
            break;
        case 2:
            $form_id    = 'delFm2';
            $form_extra = 'method="post" action="stud_remove.php" enctype="multipart/form-data" style="display:inline"';
            break;
        case 3:
            $form_id    = 'delFm3';
            $form_extra = 'action="stud_remove3.php" method="post" enctype="multipart/form-data" onsubmit="return checkfile();" style="display:inline"';
            break;
    }

    showXHTML_tabFrame_B($arry, $msgtp, $form_id, '', $form_extra, '');

    // 個人 (begin)
    switch ($msgtp) {
        case 1: // 刪除連續帳號
            include_once(sysDocumentRoot . '/academic/stud/stud_remove_serial.php');
            break;
        case 2: // 刪除不規則帳號
            include_once(sysDocumentRoot . '/academic/stud/stud_remove_abnormal.php');
            break;
        case 3: // 刪除匯入帳號
            include_once(sysDocumentRoot . '/academic/stud/stud_remove_import.php');
            break;
    }
    // 個人 (end)
    showXHTML_tabFrame_E();

    showXHTML_form_B('action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
        showXHTML_input('hidden', 'msgtp', $msgtp, '', '');
        showXHTML_input('hidden', 'user', $_POST['user'], '', '');
        showXHTML_input('hidden', 'sort_by', '', '', '');
        showXHTML_input('hidden', 'order_by', '', '', '');
        showXHTML_input('hidden', 'p', '', '', '');
        showXHTML_input('hidden', 'searchkey', $searchkey, '', '');
        showXHTML_input('hidden', 'keyword', htmlspecialchars($keyword1), '', '');
        showXHTML_input('hidden', 'page_num', $page_num, '', '');
    showXHTML_form_E();

    //  刪除不規則帳號 (delete)
    $ticket = md5('AddManual' . $sysSession->ticket . $sysSession->school_id . $sysSession->username);
    showXHTML_form_B('action="stud_remove2.php" method="post" enctype="multipart/form-data" style="display:none"', 'DelManualFm');
        showXHTML_input('hidden', 'ticket', $ticket, '', '');
        showXHTML_input('hidden', 'del_user', '', '', '');
    showXHTML_form_E();

    //  學員資訊
    showXHTML_form_B('action="stud_info.php" method="post" enctype="multipart/form-data" style="display:none"', 'stud_info');
        showXHTML_input('hidden', 'msgtp', '', '', '');
        showXHTML_input('hidden', 'user', '', '', '');
        showXHTML_input('hidden', 'del_user', '1', '', '');
    showXHTML_form_E();

showXHTML_body_E();

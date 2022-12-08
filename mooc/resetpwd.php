<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/user.php');
require_once(sysDocumentRoot . '/lang/register.php');

$idx = $_GET['idx'];

// 進入本頁面先第一次檢查密碼是否更換密碼過了、檢查驗證碼是否在3天內
$rsUser = new user();
$valid = $rsUser->isForgetCodeExists($idx);

switch ($valid) {
    case '0': // 驗證碼空值，無效
    noCacheRedirect($baseUrl . '/mooc/message.php?type=10');
        break;

    case '3': // 超過3天內驗證，無效
    noCacheRedirect($baseUrl . '/mooc/message.php?type=9');
        break;

    case '4': // 已經更新密碼過了
    noCacheRedirect($baseUrl . '/mooc/message.php?type=15');
        break;
}

// 帳號長度限制
$user_limit2 = str_replace(array('%MIN%', '%MAX%'),
    array(sysAccountMinLen, sysAccountMaxLen),
    $MSG['msg_js_02'][$sysSession->lang]);

// 帳號字母限制
$user_limit3 = str_replace('%FIRSTCHR%',
    $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang],
    $MSG['msg_js_03'][$sysSession->lang]);

// 取語系
$arrMsg = array($MSG['empty_account'][$sysSession->lang],
    $user_limit2,
    $user_limit3,
    $MSG['msg_js_04'][$sysSession->lang],
    $MSG['msg_js_05'][$sysSession->lang],
    $MSG['msg_js_06'][$sysSession->lang],
    $MSG['msg_js_07'][$sysSession->lang],
    $MSG['msg_js_26'][$sysSession->lang],
    $MSG['msg_js_09'][$sysSession->lang],
    $MSG['msg_js_10'][$sysSession->lang],
    $MSG['msg_js_11'][$sysSession->lang],
    $MSG['msg_js_12'][$sysSession->lang],
    $MSG['msg_first_name_error'][$sysSession->lang],
    $MSG['msg_last_name_error'][$sysSession->lang],
    $MSG['msg_account_reduplicate'][$sysSession->lang],
    $MSG['system_reserved'][$sysSession->lang]
    );

// 組語系HTML
$msg = exportLang($arrMsg);

$smarty->assign('msg', $msg);
$smarty->assign('sysAccountMinLen', sysAccountMinLen);
$smarty->assign('sysAccountMaxLen', sysAccountMaxLen);
$smarty->assign('Account_format', Account_format);
$smarty->assign('idx', $idx);

// 如果有Post資料(register_p.php來的)才回傳到頁面
if (count($_POST) >= 1) {
    $smarty->assign('post', $_POST);
    $smarty->assign('pwdfocus', 'true');
} else if (isset($_GET['usr'])&& $_GET['usr'] !== '') {
    $userInfo['username'] = base64_decode(urldecode($_GET['usr']));
    $smarty->assign('post', $userInfo);
    $smarty->assign('pwdfocus', 'true');
}

$smarty->display('resetpwd.tpl');
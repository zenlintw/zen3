<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lang/register.php'); //語系

// 已登入者，不能使用
if ($sysSession->username != 'guest') {
    header('HTTP/1.1 403 Forbidden');
    exit;
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
    $MSG['system_reserved'][$sysSession->lang],
    $MSG['email_used'][$sysSession->lang]
    );

// 組語系HTML
$msg = exportLang($arrMsg);

$smarty->assign('msg', $msg);
$smarty->assign('sysAccountMinLen', sysAccountMinLen);
$smarty->assign('sysAccountMaxLen', sysAccountMaxLen);
$smarty->assign('Account_format', Account_format);
$smarty->assign('mail_rule', sysMailRule);

// 如果有SERVER資料(message.php來的)才回傳到頁面
if (count($_SERVER['argv']) > 0) {
    $username = base64_decode(urldecode($_SERVER['argv'][0]));
    $email = base64_decode(urldecode($_SERVER['argv'][1]));
    $encemail = urlencode(md5('xliame'.$email));
    if ($encemail == $_SERVER['argv'][2]) {
        $smarty->assign('email', $email);
        $smarty->assign('username', $username);
        $smarty->assign('encemail', $encemail);
    } else {
        // 非法驗證碼要給什麼訊息?
    }
}


// 如果有Post資料(forget_p.php來的)才回傳到頁面
if (count($_POST) >= 1) {
    $smarty->assign('post', $_POST);
}

// 取 FB id
$FBPara = $rsSchool->getSchoolFBParameter($sysSession->school_id);
$smarty->assign('FB_APP_ID', $FBPara['canReg_fb_id']);

$smarty->display('resend.tpl');
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/user.php');
require_once(sysDocumentRoot . '/lang/register.php'); //語系

// 檢查驗證碼
if (!(isset($_POST['idx'])) || $_POST['idx'] === '') {
    $tmpMessage[] = $MSG['msg_15'][$sysSession->lang];
} elseif (strlen($_POST['idx']) !== 32) {
    $tmpMessage[] = $MSG['msg_16'][$sysSession->lang];
}

// 檢查帳號
if (strlen($_POST['username']) <= 1 || strlen($_POST['username']) >= 21) {
    $user_limit2 = str_replace(array('%MIN%', '%MAX%'),
                               array(sysAccountMinLen, sysAccountMaxLen),
                               $MSG['msg_js_02'][$sysSession->lang]);
    $tmpMessage[] = $user_limit2;
} else {
    $error_no = checkUsername($_POST['username']);
    if ($error_no > 0) {
        if ($error_no == 1) {
            $tmpMessage[] = $MSG['system_reserved'][$sysSession->lang];
        } else if ($error_no == 3) {
            $user_limit3 = str_replace('%FIRSTCHR%', $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang], $MSG['msg_js_03'][$sysSession->lang]);
            $tmpMessage[] = $user_limit3;
        } else if ($error_no == 4) {
            $tmpMessage[] = $MSG['system_reserved'][$sysSession->lang];
        } else if ($error_no == 5) {
            $tmpMessage[] = $MSG['msg_fill_username'][$sysSession->lang];
        }
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $_POST['username']);
    }
}

// 檢查密碼
if (!(isset($_POST['password'])) || $_POST['password'] === '') {
    $tmpMessage[] = $MSG['msg_js_04'][$sysSession->lang];
} elseif (strlen($_POST['password']) <= 5) {
    $tmpMessage[] = $MSG['msg_js_05'][$sysSession->lang];
}

// 確認密碼
if (!(isset($_POST['repassword'])) || $_POST['repassword'] === '') {
    $tmpMessage[] = $MSG['msg_js_06'][$sysSession->lang];
}

if ($_POST['password'] !== $_POST['repassword']) {
    $tmpMessage[] = $MSG['msg_js_07'][$sysSession->lang];
}

if (count($tmpMessage) >= 1) {
    // 組錯誤訊息
    foreach ($tmpMessage as $v) {
        $message .= '<div>' . $v . '</div>';
    }
} else {
    // 確認帳號與驗證碼有效性且配對相符
    $rsUser = new user();
    $valid = $rsUser->isForgetCodeExists($_POST['idx']);

    // 設定訊息
    switch ($valid) {
        case '1':
                $set = $rsUser->setForgetCodeExists($_POST['idx'], $_POST['username']);
                if ($set === '1') {
                    // 更新密碼
                    $r = $rsUser->setUserPassword($_POST['username'], md5($_POST['password']));
                    if ($r === '1') {
                        $type = 8;
                    } else {
                        $type = '10&idx='.$_POST['idx'];
                    }
                } else {
                    $type = '10&idx='.$_POST['idx'];
                }
            break;

        case '3':
            $type = 9;
            break;

        case '0':
        default:
            $type = '10&idx='.$_POST['idx'];
            break;
    }
    noCacheRedirect($baseUrl . '/mooc/message.php?type=' . $type);
}

$smarty->assign('idx', $_POST['idx']);
unset($_POST['idx']);// $_POST['idx']要先指定後才可以unset
unset($_POST['password']);
unset($_POST['repassword']);
$smarty->assign('message', $message);
$smarty->assign('post', $_POST);

$smarty->display('resetpwd_p.tpl');
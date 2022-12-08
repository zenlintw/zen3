<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lang/register.php'); //語系
require_once(sysDocumentRoot . '/lang/mooc.php');

global $sysSession, $MSG;

// XSS 防護
if (!empty($_GET['email'])) $_GET['email'] = htmlspecialchars($_GET['email'], ENT_QUOTES);
if (!empty($_POST['email'])) $_POST['email'] = htmlspecialchars($_POST['email'], ENT_QUOTES);
if (!empty($_POST['encemail'])) $_POST['encemail'] = htmlspecialchars($_POST['encemail'], ENT_QUOTES);
if (!empty($_POST['username'])) $_POST['username'] = htmlspecialchars($_POST['username'], ENT_QUOTES);
if (!preg_match('/^[0-9]+$/', $_GET['type'])){
    header('HTTP/1.1 403 Forbidden');
    exit;
}
$msgType = intval($_GET['type']);

// 設定訊息、按鈕參數
switch ($msgType) {
    case 1:
        // 註冊需要管理者審核
        if (in_array('C', $canRegister)) {
            $message = $MSG['need_confirm'][$sysSession->lang];
            $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
            break;
        }else{
            $message = $MSG['verificationsended'][$sysSession->lang] . '<h4>' . $_GET['email'] . '</h4>' . $MSG['plzclicklink'][$sysSession->lang];
            $buttons = exportButton(array(
                'btnSignIn' => array($MSG['login'][$sysSession->lang], 'login.php'),
                'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')
            ));
        }
        break;

    case 2:
        $message = $MSG['startenjoy'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 3:
        $message = $MSG['waitforaudit'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 4:
        $message = $MSG['emailverificationexpired'][$sysSession->lang];
        $buttons = exportButton(array('btnResend' => array($MSG['resendverification'][$sysSession->lang], 'resend.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 5:
        // 重發驗證信
        if ($_POST['action'] !== null && $_POST['action'] !== '' && $_POST['action'] == 'resend'){
            dbDel('WM_prelogin', "login_seed='{$_POST['login_key']}'"); //刪除login_key
            $message = $MSG['emailverificationincorrect2'][$sysSession->lang];
            $message = strtr($message,array('%EMAIL%' => base64_decode(urldecode($_POST['email']))));
            $buttons = exportButton(array('btnResend' => array($MSG['resendverification'][$sysSession->lang], 'resend.php?'.$_POST['username'].'+'.$_POST['email'].'+'.htmlspecialchars($_POST['encemail'], ENT_QUOTES)),
                                        'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        } else {
            $message = $MSG['emailverificationincorrect'][$sysSession->lang];
            $buttons = exportButton(array('btnResend' => array($MSG['resendverification'][$sysSession->lang], 'resend.php'),
                                        'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        }
        break;

    case 6:
        $message = $MSG['verifyfinished'][$sysSession->lang] . '<h4>' . $_GET['email'] . '</h4>' . $MSG['activeaccount'][$sysSession->lang];
        $buttons = exportButton(array('btnSignIn' => array($MSG['login'][$sysSession->lang], 'login.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 7:
        $message = $MSG['sended'][$sysSession->lang] . '<h4>' . $_GET['email'] . '</h4>' . $MSG['clicklink'][$sysSession->lang];
        $buttons = exportButton(array('btnSignIn' => array($MSG['login'][$sysSession->lang], 'login.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 8:
        $message = $MSG['newpasswordlogin1'][$sysSession->lang];
        $buttons = exportButton(array('btnSignIn' => array($MSG['login'][$sysSession->lang], 'login.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 9:
        $message = $MSG['verificationexpired'][$sysSession->lang];
        $buttons = exportButton(array('btnResend' => array($MSG['return_forgot'][$sysSession->lang], 'forget.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 10:
        $message = $MSG['verificationincorrect'][$sysSession->lang];
        if ($_GET['idx'] !== null && $_GET['idx'] !=='') {
            $buttons = exportButton(array('btnReturn' => array($MSG['return_previous_page'][$sysSession->lang], 'resetpwd.php?idx='.$_GET['idx']),
                                        'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        } else {
            $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        }

        break;

    case 11:
        $message = $MSG['passwordincorrect'][$sysSession->lang];
        $buttons = exportButton(array('btnSignIn' => array($MSG['return_login'][$sysSession->lang], 'login.php'),
                                    'btnResend' => array($MSG['btn_query_password'][$sysSession->lang], 'forget.php')));
        break;

    case 12:
        $message = $MSG['logindirect'][$sysSession->lang];
        $buttons = exportButton(array('btnSignIn' => array($MSG['login'][$sysSession->lang], 'login.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 13:
        $message = $MSG['notmember'][$sysSession->lang];
        $buttons = exportButton(array('btnForget' => array($MSG['btn_query_password'][$sysSession->lang], 'forget.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 14:
        $message = $MSG['notmember'][$sysSession->lang];
        $buttons = exportButton(array('btnResend' => array($MSG['resendverification'][$sysSession->lang], 'resend.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 15:
        $message = $MSG['newpasswordlogin2'][$sysSession->lang];
        $buttons = exportButton(array('btnSignIn' => array($MSG['login'][$sysSession->lang], 'login.php'),
                                    'btnResend' => array($MSG['btn_query_password'][$sysSession->lang], 'forget.php')));
        break;
    case 16:
        switch ($_GET['msg']) {
        // error參考 /lib/lib_stud_rm.php  function DelStudentAll()
        case 0:
            $msg = $MSG['withdrawalsuccess'][$sysSession->lang];
            break;
        case 1:
            $msg = $MSG['withdrawalfailure'][$sysSession->lang] . "(error:1)";
            break;
        case 2:
            $msg = $MSG['withdrawalfailure'][$sysSession->lang] . "(error:2)";
            break;
        case 4:
            $msg = $MSG['withdrawalfailure'][$sysSession->lang] . "(error:4)";
            break;
        case 3:
        case 5:
            $msg = $MSG['withdrawalfailure'][$sysSession->lang] . "(error:3.5)";
            break;
        }
        $message = '<h4>' . $msg . '</h4>'."<br/>".$MSG['auto_return_course'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 17:
        $message = $MSG['logout_first'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 18:
        $message = $MSG['parameter_error'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 201: // QrcodeError: 掃描登入的手機session是未登入
        $message = $MSG['qrcode_phone_no_session'][$sysSession->lang];
        $buttons = exportButton(array('btnSignIn' => array($MSG['login'][$sysSession->lang], 'login.php'),
                                    'btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 202: // QrcodeError: 找不到被掃描的session
        $message = $MSG['qrcode_session_not_found'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 203: // QrcodeError: 目標的Qrcode已是登入身份
        $message = $MSG['qrcode_session_not_guest'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 204: // QrcodeError: 提供被掃描登入的Qrcode其目前身份是guest
        $message = $MSG['qrcode_session_is_guest'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 205: // QrcodeError: 掃描手機的session是已登入身份
        $message = $MSG['scan_session_not_guest'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 206: // QrcodeError: Qrcode已過期
        $message = $MSG['qrcode_expire'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 221: // Qrcode OK: 掃描手機目前已是相同身份
        $message = $MSG['scan_session_same_username'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 222: // Qrcode OK: 掃描手機已正確登入
        $message = $MSG['scan_session_login_success'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    case 223: // Qrcode OK: 手機掃描對方Qrcode使其登入成功
        $message = $MSG['qrcode_session_logined_success'][$sysSession->lang];
        $buttons = exportButton(array('btnHome' => array($MSG['return_home'][$sysSession->lang], 'index.php')));
        break;

    default:
        $message = $MSG['nonemessage'][$sysSession->lang];
        break;
}

$smarty->assign('message', $message);
$smarty->assign('buttons', $buttons);
$smarty->display('message.tpl');

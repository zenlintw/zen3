<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lang/register.php'); //語系

    // 未開放註冊時，導向 mooc 首頁
    if (!in_array('Y', $canRegister) && !in_array('C', $canRegister) && !in_array('FB', $canRegister)) {
        header('Location: /mooc/index.php');
        exit;
    }

    // 已登入者，不能使用
    if ($sysSession->username != 'guest') {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    session_start();
    unset($_SESSION['facebook_user']);
    global $sysSession;

    // 設定ticket
    $ticket = md5($sysSession->ticket . 'WriteUserData' . $sysSession->username . $sysSession->school_id .
        $sysSession->school_host);

    // 帳號長度限制
    $user_limit2 = str_replace(array('%MIN%', '%MAX%'),
        array(sysAccountMinLen, sysAccountMaxLen),
        $MSG['msg_js_02'][$sysSession->lang]);

    // 帳號字母限制
    $user_limit3 = str_replace('%FIRSTCHR%',
        $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang],
        $MSG['msg_js_03'][$sysSession->lang]);

    // 取語系
    $arrMsg = array(
        $MSG['empty_account'][$sysSession->lang],
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
        $MSG['msg_js_25'][$sysSession->lang],
        $MSG['email_used'][$sysSession->lang],
        $MSG['captcha_error'][$sysSession->lang], //19
        $MSG['msg_fill_captcha'][$sysSession->lang]
    );

    // 組語系HTML
    $msg = exportLang($arrMsg);

    // 隱私權檔案
    $path = "/base/{$sysSession->school_id}/door/policy/";
    if (file_exists(sysDocumentRoot . $path . $sysSession->lang . '.html')) {
        $policy = $path . $sysSession->lang . '.html';
    } else {
        $policy = '/mooc/agree.php';
    }

    $smarty->assign('ticket', $ticket);
    $smarty->assign('msg', $msg);
    $smarty->assign('sysAccountMinLen', sysAccountMinLen);
    $smarty->assign('sysAccountMaxLen', sysAccountMaxLen);
    $smarty->assign('Account_format', Account_format);
    $smarty->assign('mail_rule', sysMailRule);
    $smarty->assign('policy', $policy);

    // 如果有Post資料(save.php來的)才回傳到頁面
    if (count($_POST) >= 1) {
        // XSS 防護
        if (is_array($_POST)) {
            foreach ($_POST as $k=>$v) {
                if (is_array($v)) {
                    foreach ($v as $k1=> $v1) {
                        if (!empty($_POST[$k][$k1])) $_POST[$k][$k1]=htmlspecialchars($v1, ENT_QUOTES);
                    }
                }else{
                    if (!empty($_POST[$k])) $_POST[$k] = htmlspecialchars($v, ENT_QUOTES);
                }
            }
        }

        $smarty->assign('post', $_POST);
    } else {
        // 如沒 POST 資料，則使用 session 預設語系 (借用 post 傳值)
        $smarty->assign('post', array('lang'=> $sysSession->lang));
    }

    // 取 FB id
    $FBPara = $rsSchool->getSchoolFBParameter($sysSession->school_id);
    $smarty->assign('FB_APP_ID', $FBPara['canReg_fb_id']);

    // 圖形驗證碼
    $sysEnableCaptcha = '0';
    if (defined('sysEnableCaptcha') && sysEnableCaptcha) {
        $sysEnableCaptcha = '1';
    }
    $smarty->assign('sysEnableCaptcha', $sysEnableCaptcha);

    $smarty->display('register.tpl');

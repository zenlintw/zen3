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
    
    session_start();
    unset($_SESSION['facebook_user']);
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
        $MSG['msg_js_22'][$sysSession->lang],
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
    $smarty->assign('mail_rule', sysMailRule);

    // 如果有Post資料(forget_p.php來的)才回傳到頁面
    if (count($_POST) >= 1) {
        if (isset($_POST['username'])) {
            $rtn = checkUsername($_POST['username']);
            if (($rtn == 0) || ($rtn == 2)) {
                $smarty->assign('prev_fill_username', $_POST['username']);
            }
        }

        if (isset($_POST['email'])) {
            if (preg_match(sysMailRule, $_POST['email'])) {
                $smarty->assign('prev_fill_email', $_POST['email']);
            }
        }
    }

        // 取 FB id
    $FBPara = $rsSchool->getSchoolFBParameter($sysSession->school_id);
    $smarty->assign('FB_APP_ID', $FBPara['canReg_fb_id']);

    $smarty->display('forget.tpl');

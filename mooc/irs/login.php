<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lang/register.php');
    require_once(sysDocumentRoot . '/lang/pw_query.php');
    require_once(sysDocumentRoot . '/lang/irs.php');

if ($_SERVER['HTTPS']) {
    ini_set("session.cookie_secure","1");
} else {
    ini_set("session.cookie_secure","0");
}

    if (empty($_GET['goto'])){
        header('Location: /mooc/index.php');
        die();
    }else{
        $goto = sysNewDecode($_GET['goto'],'wm5IRS');
        if ($goto === false){
            header('LOCATION: /mooc/irs/message.php?goto='.sysNewEncode(serialize(array('code'=>'1')), 'wm5IRS'));
            exit;
        }else{
            $gotoData = unserialize($goto);
            if ((strlen($gotoData['course_id']) != 8) ||
                (strlen($gotoData['exam_id']) != 9) ||
                !in_array($gotoData['type'], array('exam','questionnaire'))
            ){
                header('LOCATION: /mooc/irs/message.php?goto='.sysNewEncode(serialize(array('code'=>'1')), 'wm5IRS'));
                exit;
            }
        }
    }

    if ($sysSession->username != 'guest'){
        header('LOCATION: /mooc/irs/check.php?action=start&goto='.$_GET['goto']);
        exit;
    }

    $smarty->assign('irsGoto', htmlspecialchars($_GET['goto']));

    session_start();
    unset($_SESSION['facebook_user']);

    $uid      = md5(uniqid(rand(), 1));
    $loginKey = md5(sysSiteUID . sysTicketSeed . $uid);

    dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
    dbNew('WM_prelogin', 'login_seed, uid, log_time', "'$loginKey', '$uid', NOW()");

    $smarty->assign('loginKey', $loginKey);

    if (isset($_GET['idx']) && $_GET['idx'] !== '') {
        $username = base64_decode(urldecode($_GET['idx']));
        $smarty->assign('username', $username);
        $smarty->assign('message', $MSG['login_fail'][$sysSession->lang]);
    }
    // ????????????????????????????????????ID?????? reurl ?????? GET ?????????
    if(isset($_GET['reurl']) && $_GET['reurl'] !== ''){
        $reurl = $_GET['reurl'];
    }else{
         $reurl = "";
    }

    if ($sysSession->username !== 'guest') {
        // ?????????????????????USERS
        header('Location: /mooc/message.php?type=17');
        exit();
    }

    $smarty->assign('reurl', $reurl);

    // ??? FB id
    $FBPara = $rsSchool->getSchoolFBParameter($sysSession->school_id);
    $smarty->assign('FB_APP_ID', $FBPara['canReg_fb_id']);

    // ???????????????
    $sysEnableCaptcha = '0';
    if (defined('sysEnableCaptcha') && sysEnableCaptcha) {
        $sysEnableCaptcha = '1';
    }
    $smarty->assign('sysEnableCaptcha', $sysEnableCaptcha);

    $smarty->display('irs/login.tpl');

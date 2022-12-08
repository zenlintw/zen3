<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lang/irs.php');
    
    /*if ($sysSession->username == 'guest') {
        header('Location: /mooc/index.php');
        exit;
    }*/
    
    if ($sysSession->username != 'guest')
    list($name) = dbGetStSr('WM_user_account', 'CONCAT(IFNULL(`last_name`,""),IFNULL(`first_name`,"")) as name', "username = '{$sysSession->username}'", ADODB_FETCH_NUM);
    
    $ticket = md5(sysTicketSeed . $sysSession->username . $_COOKIE['idx']);
    
    if($_SERVER['argv'][0] != '') {
        
        if ($_SERVER['argv'][0]==1) {
            $smarty->assign('message', $MSG['msg_code1'][$sysSession->lang]);
        }
        
        if ($_SERVER['argv'][0]==2) {
            $smarty->assign('message', $MSG['msg_code2'][$sysSession->lang]);
        }
    }

    $code = strtoupper(dechex($sysSession->course_id));

    $smarty->assign('name', $name);
    $smarty->assign('code', $code);
    //$smarty->assign('company', $company);
    $smarty->assign('ticket', $ticket);
    $smarty->display('user/code.tpl');

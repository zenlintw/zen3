<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    
    $content = str_replace('%appTitle%',
        $sysSession->school_name,
        $MSG['security_policy_content'][$sysSession->lang]);
    
	$smarty->assign('content', $content);
	$smarty->display('security.tpl');

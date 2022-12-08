<?php

/**
 * 進行線上更新回覆步驟二
 * $Id: rollback1.php,v 1.1 2010/02/24 02:38:48 saly Exp $
 **/

	set_time_limit(3000);
	ignore_user_abort(true);
		
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/wm3update/lib.php');
	
	//此線上更新只提供給root這帳號使用
	if ($sysSession->username != sysRootAccount)
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
	
#========functions =================

#========main=======================
    // 寫入rollback指令
    //驗證update_id
    $oUpdSess = new WM3UpdateSession();
    if (!$oUpdSess->createInstructionFile("rollback", $_POST['rollback_id'])) {
        die("Fail to Create Instruction.");
    }
    die('<script>alert("wait to rollback.");document.location.href="/academic/wm3update/list.php";</script>');



	// $o_rollback = new WM3Rollback($_POST['rollback_id']);
	// $o_rollback->doRollback();
	// // 將此更新patch設為Rollback
	// $o_log = new WM3UpdateLog();
	// $o_log->setRollBackStatus($_POST['rollback_id']);
	// header('Location: /academic/wm3update/list.php');
?>
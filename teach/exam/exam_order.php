<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/03/21                                                            *
	 *		work for  : save exam(s) order sequencing                                         *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600100100';
		$sysSession->restore();
		if (!aclVerifyPermission(1600100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
			
		}
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700100100';
		$sysSession->restore();
		if (!aclVerifyPermission(1700100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
			
		}
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800100100';
		$sysSession->restore();
		if (!aclVerifyPermission(1800100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
			
		}
	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');			
	}
	$ticket = md5(sysTicketSeed . $course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');			
	}
	if (!ereg('^[0-9]+(,[0-9]+)*$', $_POST['lists'])) {	// 檢查 lists
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
	   die('Fake lists.');	
	}

	$ords = explode(',', $_POST['lists']);
	foreach($ords as $k => $v){
		dbSet('WM_qti_' . QTI_which . '_test', "sort=$k", "exam_id=$v");
	}

	header('Location: exam_maintain.php' . ($_POST['referer']?"?{$_POST['referer']}":''));
?>

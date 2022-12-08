<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func='1800300300';
	$sysSession->restore();
	if (!aclVerifyPermission(1800300300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if (!isset($_POST['ticket'])) $_POST['ticket'] = md5(sysTicketSeed . $course_id . $_SERVER['argv'][1]);
	if (!isset($_POST['referer'])) $_POST['referer'] = $_SERVER['argv'][1];
	if (!isset($_POST['lists'])) $_POST['lists'] = $_SERVER['argv'][0];

	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket = md5(sysTicketSeed . $course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');
	}
	if (!eregi('^[0-9A-Z_]+$', $_POST['lists'])) {	// 檢查 lists
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
	   die('Fake lists.');
	}

	require_once(sysDocumentRoot . '/teach/exam/exam_statistics_result.php');
?>

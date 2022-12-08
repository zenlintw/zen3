<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='1800300400';
	$sysSession->restore();
	if (!aclVerifyPermission(1800300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if ($_SERVER['argv'][2] != md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']))
	{
	   	wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Fake ticket');
		die('Fake ticket !');
	}

	$_POST['ticket'] = md5(sysTicketSeed . $course_id . $_SERVER['argv'][1]);
	$_POST['referer'] = $_SERVER['argv'][1];
	$_POST['lists'] = $_SERVER['argv'][0];

	require_once(sysDocumentRoot . '/teach/exam/exam_statistics_result.php');
?>

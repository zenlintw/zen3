<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/07/01                                                            *
	 *		work for  :                                                                       *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1000200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$idx = 0;
	if (is_array($_POST['teams']))
	{
		foreach($_POST['teams'] as $team_id){
			$team_id = intval($team_id);
			dbSet('WM_student_separate', 'permute=' . $idx++, "course_id={$sysSession->course_id} and team_id=$team_id");
			if ($sysConn->ErrorNo() > 0) {
			$errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
			wmSysLog($sysSession->cur_func, $sysSession->course_id , $team_id , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
			die($errMsg);
			} 
		}
	}

	header('Location: stud_groups.php?order=3');

?>

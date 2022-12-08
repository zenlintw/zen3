<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/08/01                                                            *
	 *		work for  : grade manage                                                          *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
	
	$sysSession->cur_func = '1400100200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (ereg('^[0-9]+$', $_POST['grade_id']) &&
		is_array($_POST['fields']) && count($_POST['fields']) &&
		(list($isThisCourseGrade) = dbGetStSr('WM_grade_list', 'count(*)', "course_id={$sysSession->course_id} and grade_id={$_POST['grade_id']}", ADODB_FETCH_NUM)))
	{
		$students = $sysConn->GetCol('select username from WM_term_major where course_id=' . $sysSession->course_id . ' and role & ' . $sysRoles['student']);
		foreach($_POST['fields'] as $username => $grade){
			if (!preg_match('/^-?\d{1,5}(\.\d+)?$/', $grade[0])) $grade[0] = '';
			if (in_array($username, $students))
			{
				dbSet('WM_grade_item', "score='{$grade[0]}',comment='{$grade[1]}'", "grade_id={$_POST['grade_id']} and username='$username'");
				if ($sysConn->ErrorNo()) {
					$errMsg = $sysConn->ErrorMsg();
					wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['grade_id'] , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
					die($errMsg);
				}
				// MIS#20398 by Small 2011/3/7
				// elseif ($sysConn->Affected_Rows() == 0 && $grade[0] != '')
				elseif ($sysConn->Affected_Rows() == 0 && ($grade[0] != '' || $grade[1]!=''))
				{
					$grade[0] = ($grade[0]=='')? 0 : $grade[0];
					dbNew('WM_grade_item', 'grade_id,username,score,comment', "{$_POST['grade_id']}, '$username', {$grade[0]}, '{$grade[1]}'");
				}
			}
		}
		unset($students);
		reCalculateGrades($sysSession->course_id);
		header('Location: grade_modify.php?gid=' . $_POST['grade_id'] . '&status=1');
	}
	else {
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['grade_id'] , 2, 'auto', $_SERVER['PHP_SELF'], 'Incorrect grade_id');
		die('Incorrect grade_id.');
	}

?>

<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/07/30                                                            *
	 *		work for  : grade manage                                                          *
	 *		work on   : Apache 1.3.28, MySQL 4.0 up, PHP 4.3.2                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
	
	$sysSession->cur_func = '1400200300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// �B�z�ק諸���Z
	if ($_POST['modified']){
		$grades = explode(';', $_POST['modified']);
		foreach($grades as $grade){
			list($grade_id, $userlist) = explode(':', $grade, 2);
			if (!preg_match('/^[\d.]+$/', $grade_id)) continue;
			$users = explode('&', $userlist);
			foreach($users as $user){
                //#47357 Chrome[�줽��/���Z�޲z/���Z�`��] ��ʭק令�Z��A���U�u�x�s�w��諸���ơv�A�|�^�_���ק�e�����Z�C�G�]���P�_���S����� - �P _�A�ҥH���o��Ӧr�����b���ҷ|�x�s����
				if (!preg_match('/^[\w\-\_]+=[\d.]+$/', $user)) continue;
				list($username, $score) = explode('=', $user, 2);
				dbSet('WM_grade_item', "score=$score", "grade_id=$grade_id and username='$username'");
				if ($sysConn->Affected_Rows() == 0)
					dbNew('WM_grade_item', 'grade_id,username,score', "$grade_id, '$username', $score");
			}
		}
	}

	// �x�s�έp��� (�`���B�����B�ƦW)
	reCalculateGrades($sysSession->course_id);

	wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'grade save modified in(' . $_POST['modified'] . ')');

	header('Location: grade_sheet.php?saved');
?>
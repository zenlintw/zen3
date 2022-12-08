<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/18                                                            *
	 *		work for  : 新增/刪除 本課學員 (連續 及 不連續刪除)                                                      *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *      $Id: stud_addrm1.php,v 1.1 2010/02/24 02:40:30 saly Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if ($_SERVER['argv'][0] == '1') {
		$_POST['op'] == '5' ? ($sysSession->cur_func= '1100400400') : ($sysSession->cur_func='1100400300');
	}
	elseif ($_SERVER['argv'][0] == '2' || $_SERVER['argv'][0] == '4') {
		$_POST['op'] == '5' ? ($sysSession->cur_func= '1100400200') : ($sysSession->cur_func='1100400100');
	}
	elseif ($_SERVER['argv'][0] == '3') {
		$_POST['op'] == '5' ? ($sysSession->cur_func= '1100400600') : ($sysSession->cur_func='1100400500');
	}
	else
		die('argument requied.');

	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (empty($_SERVER['argv'][0]) || !ereg('^[1-5]$', $_POST['op'])) {
		header('Location: stud_addrm.php'); exit;
	}

	switch($_SERVER['argv'][0]){
		case '1':
			$users = array();
			$last = intval($_POST['last']);
			$pattern = str_replace('%', '%%', trim($_POST['header'])) .
				       '%0' . intval($_POST['len']) . 'd' .
				       str_replace('%', '%%', trim($_POST['tail']));
			for($i=intval($_POST['first']); $i<=$last; $i++){
				$users[] = sprintf($pattern, $i);
			}
			break;
		case '2':
		case '3':
		case '4':
			$users = preg_split('/[^\w.-]+/', $_POST['userlist'], -1, PREG_SPLIT_NO_EMPTY);
			break;
		/*
		case '3':
			if (is_uploaded_file($_FILES['cvsfile']['tmp_name'])){
				$users = preg_split('/[^\w.-]+/', file_get_contents($_FILES['cvsfile']['tmp_name']), -1, PREG_SPLIT_NO_EMPTY);
				unlink($_FILES['cvsfile']['tmp_name']);
				break;
			}
		*/
		default :
			header('Location: stud_addrm.php'); exit;
			break;

	}

	$length = count($users);
	if ($length < 1) { header('Location: stud_addrm.php'); exit; }

	require_once(sysDocumentRoot . '/teach/student/stud_addrm_lib.php');
?>

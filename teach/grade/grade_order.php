<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/06                                                            *
	 *		work for  : grade manage                                                          *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '1400300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if ($_GET['gid'] && ereg('^[0-9]+(,[0-9]+)*$', $_GET['gid'])){
		$orders = explode(',', $_GET['gid']);
		for($i=0; $i<count($orders); $i++){
			dbSet('WM_grade_list', "permute=$i", 'grade_id=' . $orders[$i]);
		}
	}
	header('Location: grade_maintain.php');
?>
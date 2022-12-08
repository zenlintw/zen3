<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/16                                                            *
	 *		work for  : 權限控管之 ACL 新增                                                   *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '100200100';
	$sysSession->restore();
	if (!aclVerifyPermission(100200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!ereg('^[0-9]+$', $_POST['function_id'])) {
	   wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'manager', $_SERVER['PHP_SELF'], 'Incorrect function_id format!');
	   die('Incorrect function_id format.');
	}
	if (empty($_POST['caption'])) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,2, 'manager', $_SERVER['PHP_SELF'], 'Function description required!');
		die('Function description required.');
	}
	else
		$_POST['caption'] = addslashes(iconv('UTF-8', 'Big5', stripslashes($_POST['caption'])));
	$scope = is_array($_POST['scope']) ? array_sum($_POST['scope']) : 0;
	$default = is_array($_POST['default']) ? array_sum($_POST['default']) : 0;
	dbNew('WM_acl_function',
	      'function_id,caption,scope,default_permission',
	      "{$_POST['function_id']},'{$_POST['caption']}',$scope,$default"
	     );
	if ($sysConn->ErrorNo() == 0) {
		header('Location: access_maintain.php');
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], '新增 ACL function success '.$_POST['function_id']);
	}
	else {
		echo 'Error : ', $sysConn->ErrorNo(), ' - ', $sysConn->ErrorMsg();
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,3, 'manager', $_SERVER['PHP_SELF'], '新增 ACL function fail '.$_POST['function_id'] . $sysConn->ErrorMsg());
	}
?>

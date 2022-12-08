<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/16                                                            *
	 *		work for  : 權限控管之 ACL 新增                                                   *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *      $Id: access_process1.php,v 1.1 2010/02/24 02:38:13 saly Exp $:                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/academic_access.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '100200100';
	$sysSession->restore();
	if (!aclVerifyPermission(100200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!ereg('^[0-9]+(,[0-9]+)*$', $_POST['function_id'])) die('Incorrect function_id format.');
	$scope = is_array($_POST['scope']) ? array_sum($_POST['scope']) : 0;
	$default = is_array($_POST['default']) ? array_sum($_POST['default']) : 0;
	switch($_POST['op']){
		case 'modifyFunction':
			if (empty($_POST['caption'])) {
				wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 1, 'manager',$_SERVER['PHP_SELF'], 'modifyFunction:Function description required');
				die('Function description required.');
			}
			else
				$_POST['caption'] = addslashes(iconv('UTF-8', 'Big5', stripslashes($_POST['caption'])));
			dbSet('WM_acl_function',
			      "caption='{$_POST['caption']}',scope=$scope,default_permission=$default",
			      "function_id={$_POST['function_id']}"
			     );
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], 'modifyFunction function_id = ' . $_POST['function_id']);
			break;
		case 'modifyScope':
			dbSet('WM_acl_function',
			      "scope=$scope",
			      "function_id in ({$_POST['function_id']})"
			     );
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], 'modifyScope function_id = ' . $_POST['function_id']);
			break;
		case 'modifyPermission':
			dbSet('WM_acl_function',
			      "default_permission=$default",
			      "function_id in ({$_POST['function_id']})"
			     );
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], 'modifyPermission function_id = ' . $_POST['function_id']);
			break;
	}
	header('Location:access_maintain.php?' . $_POST['attributes']);
?>

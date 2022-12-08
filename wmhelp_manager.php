<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C								  *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang																  *
	 *		Creation  : 2003/02/19																	  *
	 *		work for  : Files Manage																  *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1										  *
	 *		identifier: $Id: wmhelp_manager.php,v 1.1 2010/02/24 02:38:55 saly Exp $				*
	 *                                                                                                *
	 **************************************************************************************************/

	define('wmhelp', true);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	define('basePath', sprintf('%s/base/%05d/door/wmhelp/%s', $_SERVER['DOCUMENT_ROOT'], $sysSession->school_id, $sysSession->lang));
	// define('basePath', sprintf('%s/base/%05d/door/wmhelp/', $_SERVER['DOCUMENT_ROOT'], $sysSession->school_id));
	if (!is_dir(basePath)) die('Directory not found. Please contact your provider.');

	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func = '1200100100';
	$sysSession->restore();
	if (!aclVerifyPermission(1200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$cnt = aclCheckRole($sysSession->username, $sysRoles['manager'] | $sysRoles['administrator'] | $sysRoles['root']);
	if (!$cnt) {
		echo 'Access Deny!' ;
		die();
	}

	require_once(sysDocumentRoot . '/teach/files/manager.php');
?>

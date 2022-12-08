<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C								  *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang																  *
	 *		Creation  : 2003/02/19																	  *
	 *		work for  : Files Manage																  *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1										  *
	 *		identifier: $Id: manager.php,v 1.1 2010/02/24 02:38:39 saly Exp $				*
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='1200100100';
	$sysSession->restore();
	if (!aclVerifyPermission(1200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	
	define('basePath', sprintf('%s/base/%05d/door/', $_SERVER['DOCUMENT_ROOT'], $sysSession->school_id));
        
        dbSet('WM_auth_ftp', "home='" . sprintf('%s/base/%05d/door/', $_SERVER['DOCUMENT_ROOT'], $sysSession->school_id) . "'", "userid='{$sysSession->username}'");
        
	if (!is_dir(basePath)) die('Directory not found. Please contact your provider.');
	require_once(sysDocumentRoot . '/teach/files/manager.php');
?>

<?php
	/**
	 * sysbar 管理工具
	 * @version $Id: sysbar_tools.php,v 1.1 2010/02/24 02:38:20 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func = '700400300';
	$sysSession->restore();
	if (!aclVerifyPermission(700400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	define('SYSBAR_MENU' , 'teach');
	define('SYSBAR_LEVEL', 'manager_course');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_tools.php');
?>

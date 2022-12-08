<?php
	/**
	 * sysbar 管理工具
	 * @version $Id: sysbar_tools.php,v 1.1 2010/02/24 02:40:24 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	define('SYSBAR_MENU' , 'learn');
	define('SYSBAR_LEVEL', 'teacher');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_tools.php');
?>

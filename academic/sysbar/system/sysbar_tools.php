<?php
	/**
	 * sysbar ?޲z?u??
	 *
	 * @since   2004/04/06
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_tools.php,v 1.1 2010/02/24 02:38:47 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1300300400';
	$sysSession->restore();
	if (!aclVerifyPermission(1300300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	define('SYSBAR_MENU' , 'academic');
	define('SYSBAR_LEVEL', 'root');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_tools.php');
?>

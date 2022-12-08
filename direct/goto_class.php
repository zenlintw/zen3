<?php
	/**
	 * 導師環境的切換課程
	 * $Id: goto_class.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1300400200';
	$sysSession->restore();
	if (!aclVerifyPermission(1300400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$getSysbar = true;
	$envWork = $sysSession->env;
	$envRead = 'direct';
	require_once(sysDocumentRoot . '/academic/goto.php');
?>

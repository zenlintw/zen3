<?php
	/**
	 * 管理者切換學校
	 *
	 * @since   2003/07/04
	 * @author  ShenTing Lin
	 * @version $Id: goto_school.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '100300500';
	$sysSession->restore();
	if (!aclVerifyPermission(100300500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$getSysbar = true;
	$envWork   = $sysSession->env;
	$envRead   = 'academic';
	require_once(sysDocumentRoot . '/academic/goto.php');
?>

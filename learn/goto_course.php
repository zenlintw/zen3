<?php
	/**
	 * 學生環境的切換課程
	 * $Id: goto_course.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='700700100';
	$sysSession->restore();
	if (!aclVerifyPermission(700700100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}
	
	$getSysbar = true;
	$envWork = $sysSession->env;
	$envRead = 'learn';
	require_once(sysDocumentRoot . '/academic/goto.php');

?>

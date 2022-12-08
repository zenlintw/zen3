<?php
	/**
	 * 儲存討論室設定
	 *
	 * @since   2003/12/30
	 * @author  ShenTing Lin
	 * @version $Id: chat_save.php,v 1.1 2010/02/24 02:40:22 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$owner_id = $sysSession->course_id;
	$env      = 'teach';
	require_once(sysDocumentRoot . '/academic/chat/chat_main_save.php');
?>
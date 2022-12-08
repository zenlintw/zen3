<?php
	/**
	 * ¼f®Ö¾Ç­û
	 *
	 * @since   2004/03/15
	 * @author  ShenTing Lin
	 * @version $Id: review_action.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400300700';
	$sysSession->restore();
	if (!aclVerifyPermission(400300700, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	require_once(sysDocumentRoot . '/academic/review/review_action.php');
?>

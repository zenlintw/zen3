<?php
	/**
	 * °e¥X¼f®Öµ²ªG
	 *
	 * @since   2004/03/15
	 * @author  ShenTing Lin
	 * @version $Id: review_action1.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '500100100';
	$sysSession->restore();
	if (!aclVerifyPermission(500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	require_once(sysDocumentRoot . '/academic/review/review_actmail1.php');
?>

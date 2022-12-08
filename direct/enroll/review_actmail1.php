<?php
	/**
	 * 儲存審核結果，並且寄送信件
	 *
	 * @since   2004/03/17
	 * @author  ShenTing Lin
	 * @version $Id: review_actmail1.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '500300300';
	$sysSession->restore();
	if (!aclVerifyPermission(500300300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	require_once(sysDocumentRoot . '/academic/review/review_actmail1.php');
?>

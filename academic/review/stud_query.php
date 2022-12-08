<?php
	/**
	 * 查詢人員資料
	 *     直接使用 /academic/stud/stud_query.php
	 *
	 * @since   2004/02/25
	 * @author  ShenTing Lin
	 * @version $Id: stud_query.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission(400400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$rvInclude = true;
	require_once(sysDocumentRoot . '/academic/stud/stud_query.php');
?>

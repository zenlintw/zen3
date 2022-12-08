<?php
	/**
	 * ¦¨ÁZ¸Ô²Ó¸ê®Æ
	 *
	 * @since   2004/07/09
	 * @author  ShenTing Lin
	 * @version $Id: member_grade_detail.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1400300100';
	$sysSession->restore();
	if (!aclVerifyPermission(1400300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$DIRECT_VIEW_GRADE = true;
	require_once(sysDocumentRoot . '/academic/class/detail_grade.php');
?>

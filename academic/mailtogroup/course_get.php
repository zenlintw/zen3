<?php
	/**
	 * 課程管理
	 *
	 * 建立日期：2002/12/25
	 * @author  ShenTing Lin
	 * @version $Id: course_get.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '500100100';
	$sysSession->restore();
	if (!aclVerifyPermission(500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	define('Mail2Group', true);
	require_once(sysDocumentRoot . '/academic/course/course_get.php');
?>

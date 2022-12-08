<?php
	/**
	 * 學校設定
	 *
	 * 建立日期：2003//
	 * @author  ShenTing Lin
	 * @version $Id: sch_single.php,v 1.1 2010/02/24 02:38:42 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func='400400500';
	$sysSession->restore();
	if (!aclVerifyPermission(400400500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	$isSingle = 'Single';
	require_once(sysDocumentRoot . '/academic/sch/sch_list.php');
?>
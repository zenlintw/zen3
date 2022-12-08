<?php
	/**
	 * 管理我的課程
	 *
	 * @since   2004/09/23
	 * @author  ShenTing Lin
	 * @version $Id: mycourse_manage.php,v 1.1 2010/02/24 02:39:10 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2500100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	
	require_once(sysDocumentRoot . '/learn/mycourse/mycourse_manage.php');
?>

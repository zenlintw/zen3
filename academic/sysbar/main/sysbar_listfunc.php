<?php
	/**
	 * 取得功能目錄
	 *
	 * @since   2004/04/02
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_listfunc.php,v 1.1 2010/02/24 02:38:46 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '1200100100';
	// $sysSession->restore();
	if (!aclVerifyPermission(1200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$baseUri = '/';
	require_once(sysDocumentRoot . '/teach/course/listfiles.php');
?>

<?php
	/**
	 * 取得教材目錄
	 *
	 * @since   2004/04/02
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_listcour.php,v 1.1 2010/02/24 02:40:24 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '1200100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	
	define('SYSBAR_MENU' , 'learn');
	define('SYSBAR_LEVEL', 'teacher');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_listcour.php');
?>

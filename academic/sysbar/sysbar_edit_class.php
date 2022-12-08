<?php
	/**
	 * 編輯班級環境的選單
	 * 超級管理者專用
	 *
	 * @since   2004/04/06
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_edit_class.php,v 1.1 2010/02/24 02:38:46 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1300500400';
	$sysSession->restore();
	if (!aclVerifyPermission(1300500400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 設定功能編號
	define('SYSBAR_MENU' , 'class');
	define('SYSBAR_LEVEL', 'administrator');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_edit.php');
?>

<?php
	/**
	 * 編輯個人的選單
	 * 一般管理者專用
	 *
	 * @since   2004/04/06
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_edit_personal.php,v 1.1 2010/02/24 02:38:42 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func = '1300300400';
	$sysSession->restore();
	if (!aclVerifyPermission(1300300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 設定功能編號
	define('SYSBAR_MENU' , 'personal');
	define('SYSBAR_LEVEL', 'manager');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_edit.php');
?>

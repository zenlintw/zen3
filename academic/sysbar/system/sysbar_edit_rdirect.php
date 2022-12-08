<?php
	/**
	 * 編輯導師環境的選單
	 * 超級管理者專用
	 *
	 * @since   2004/04/06
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_edit_rdirect.php,v 1.1 2010/02/24 02:38:47 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1300400400';
	$sysSession->restore();
	if (!aclVerifyPermission(1300400400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 設定功能編號
	define('SYSBAR_MENU' , 'direct');
	define('SYSBAR_LEVEL', 'root');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_edit.php');
?>

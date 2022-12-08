<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 顯示 個人的小圖片                                                               *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400300';
	$sysSession->restore();
	if (!aclVerifyPermission(400400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 判斷是否為老師
	$isTA = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id);

	// 判斷是否要隱藏此照片
	list($u_hid) = dbGetStSr('WM_user_account', 'hid'," username='" . $_GET['a'] . "'", ADODB_FETCH_NUM);

	if (($u_hid & 1) && !$isTA && ($_GET['a'] != $sysSession->username))
	{

		$filename = sysDocumentRoot. "/theme/{$sysSession->theme}/learn/communication/hide.gif";
		$len = filesize($filename);

		header('Cache-control: no-cache, no-store, private, must-revalidate, post-check=0, pre-check=0');
		header('pragma:no-cache');
		header('expires:0');
		header('Content-type: image/jpeg');
		header('Content-transfer-encoding: binary');
		header('Content-Disposition: filename=picture.jpg');
		header('Accept-Ranges: bytes');
		header("Content-Length: {$len}");

		readfile($filename);
	}
	else
	{
		getUserPic($_GET['a'], true);
	}
?>

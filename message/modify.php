<?php
	/**
	 * 編輯訊息
	 *
	 * 建立日期：2003/10/21
	 * @author  ShenTing Lin
	 * @version $Id: modify.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200200200';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($sysSession->cur_func == $msgFuncID['notebook']) {
		$head   = $MSG['tabs_notebook_title'][$sysSession->lang];
		$target = 'notebook.php';
		$isNB   = true;
	} else {
		$head   = $MSG['title'][$sysSession->lang];
		$target = 'index.php';
		$isNB   = false;
	}

	$serial = intval($_POST['serial']);

	$RS = dbGetStSr('WM_msg_message', '*', "`msg_serial`={$serial} AND `receiver`='{$sysSession->username}'", ADODB_FETCH_ASSOC);
	if ($RS === false)
	{
		header('Location: ' . $target);
		exit;
	}
	$to      = '';
	$subject = $RS['subject'];
	$content = $RS['content'];
	$isHTML  = ($RS['content_type'] == 'text') ? false : true;
	$msg_id  = $serial;

	$attachment = $RS['attachment'];
	$title      = 'modify';
	$refw       = trim($RS['status']) . '';
	require_once(sysDocumentRoot . '/message/write.php');
?>

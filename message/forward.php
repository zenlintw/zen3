<?php
	/**
	 * 轉寄訊息
	 *
	 * 建立日期：2003/05/15
	 * @author  ShenTing Lin
	 * @version $Id: forward.php,v 1.1 2010/02/24 02:40:17 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200200300';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
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
	$subject = 'Fw: ' . $RS['subject'];
	$content = $RS['content'];
	if (!$isNB) {
		if ($RS['content_type'] == 'text') {
			$content = str_replace("\n", "\n: ", ': ' . $content . "\n\n");
			$content .= "\n\n";
			$isHTML = false;
		} else {
			$content = '<pre class="box01">' . $content . "</pre>\n\n";
			$isHTML = true;
		}
	}

	$attachment = $RS['attachment'];
	$title      = 'forward';
	$refw       = trim($RS['status']) . ',forward';
	require_once(sysDocumentRoot . '/message/write.php');
?>

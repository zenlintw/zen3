<?php
	/**
	 * 回覆別人的訊息
	 *
	 * 建立日期：2003/05/15
	 * @author  ShenTing Lin
	 * @version $Id: reply.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

	// $sysSession->cur_func = '2200200200';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($sysSession->cur_func == $msgFuncID['notebook']) {
		$head      = $MSG['tabs_notebook_title'][$sysSession->lang];
		$target    = 'notebook.php';
		$isNB      = true;
		$st_serial = 11;
	} else {
		$head      = $MSG['title'][$sysSession->lang];
		$target    = 'index.php';
		$isNB      = false;
		$st_serial = 12;
	}

	$serial = intval($_POST['serial']);

	$RS = dbGetStSr('WM_msg_message', '*', "`msg_serial`={$serial} AND `receiver`='{$sysSession->username}'", ADODB_FETCH_ASSOC);
	if ($RS === false)
	{
		header('Location: ' . $target);
		exit;
	}
	$to = $RS['sender'];
	$subject = 'Re: ' . $RS['subject'];
	$content = $RS['content'];
	if ($RS['content_type'] == 'text') {
		$content  = str_replace("\n", "\n: ", ': ' . $content . "\n") . "\n\n";
		$isHTML   = false;
	} else {
		$content = '<pre class="box01">' . $content . "</pre>\n";
		$isHTML = true;
	}

	$title = 'reply';
	$refw  = trim($RS['status']) . ',reply';
	$smarty->assign('to', $to);
	require_once(sysDocumentRoot . '/message/write.php');
?>

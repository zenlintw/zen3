<?php
	/**
	 * 收錄到筆記本
	 *
	 * 建立日期：2003/10/23
	 * @author  ShenTing Lin
	 * @version $Id: collect_note.php,v 1.1 2010/02/24 02:40:17 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200200300';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$serial = intval($_POST['serial']);
	$res    = msg2note($serial);
	$msg    = $res ? $MSG['msg_collect_success'][$sysSession->lang] : $MSG['msg_collect_fail'][$sysSession->lang];

	$js = <<< BOF
	window.onload = function () {
		var obj = document.getElementById("readFm");
		alert("$msg");
		obj.submit();
	};
BOF;
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/learn/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		echo $msg;
		showXHTML_form_B('action="read.php" method="post" enctype="multipart/form-data" style="display:none"', 'readFm');
			showXHTML_input('hidden', 'serial', $serial, '', '');
		showXHTML_form_E('');
	showXHTML_body_E();
?>
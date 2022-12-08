<?php
	/**
	 * 傳送訊息
	 *
	 * @since   2003/12/05
	 * @author  ShenTing Lin
	 * @version $Id: msg_talk.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/msg_online.php');
	require_once(sysDocumentRoot . '/online/msg_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2100100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2100100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$username = trim($_POST['reciver']);
	$realname = trim($_POST['reciver_name']);
	// 設定已經讀取的旗標
	$js  = $sendJS;
	$js .= <<< BOF

	user["{$username}"] = "{$realname}";
	function msgView() {
		window.location.replace("/online/msg_view.php");
	}


	window.onload = function () {
		if (typeof(window.dialogWidth) == "undefined") {
			parent.window.resizeTo(410, 400);
		} else {
			window.dialogWidth  = "410px";
			window.dialogHeight = "400px";
		}
		// picReSize();
		msgWrite('{$username}');
	};
BOF;

	showXHTML_head_B($MSG['title_message'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', 'hotkey.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('leftmargin="15" topmargin="10"');
		$btns = array();
		msgSendWin($sender, $btns, true);
	showXHTML_body_E('');
?>

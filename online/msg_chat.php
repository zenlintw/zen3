<?php
	/**
	 * 進入聊天室
	 *
	 * @since   2004/02/06
	 * @author  ShenTing Lin
	 * @version $Id: msg_chat.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2000200200';
	$sysSession->restore();
	if (!aclVerifyPermission(2000200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!empty($sysSession->room_id)) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , $sysSession->room_id , 1, 'auto', $_SERVER['PHP_SELF'], '已在討論室中!');
		$js = <<< BOF
	window.onload = function () {
		var obj = document.getElementById("tabs1");
		var xW = 300, xH = 150;
		// xW = parseInt(obj.offsetWidth)  + 40;
		// xH = parseInt(obj.offsetHeight) + 100;
		// xH = 400;
		if (typeof(window.dialogWidth) == "undefined") {
			parent.window.resizeTo(xW, xH);
		} else {
			window.dialogWidth  = xW + "px";
			window.dialogHeight = xH + "px";
		}
	};
BOF;
		showXHTML_head_B($MSG['msg_in_chat_title'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('inline', $js);
		showXHTML_head_E();
		showXHTML_body_B();
			$ary = array();
			$ary[] = array($MSG['msg_in_chat_title'][$sysSession->lang], 'tabs1');
			// $colspan = 'colspan="2"';
			echo '<div align="center">';
			showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
				showXHTML_table_B('width="250" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td('', $MSG['msg_in_chat'][$sysSession->lang]);
					showXHTML_tr_E();
				showXHTML_table_E();
			showXHTML_tabFrame_E();
			echo '</div>';
		showXHTML_body_E();
		die();
	} else {
		// $rid = trim($_GET['rid']);
		$rid = trim($_POST['rid']);
		$pwd = md5(sysTicketSeed . $_COOKIE['idx']);
		$enc = base64_decode(trim($rid));
		$rid = trim(@mcrypt_decrypt(MCRYPT_DES, $pwd, $enc, 'ecb'));
		if (strpos($rid, 'online') === FALSE) die('access deny');
		dbSet('WM_session', "`room_id`='{$rid}'", "idx='{$_COOKIE['idx']}'");
		$sysSession->room_id = $rid;
		$rnm = trim($_POST['rnm']);
		wmSysLog($sysSession->cur_func, $sysSession->school_id , $sysSession->room_id , 0, 'auto', $_SERVER['PHP_SELF'], 'go to chatroom');
		include_once(sysDocumentRoot . '/learn/chat/chat_room.php');
	}
?>

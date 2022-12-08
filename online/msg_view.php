<?php
	/**
	 * 閱讀訊息
	 *
	 * @since   2003/11/10
	 * @author  ShenTing Lin
	 * @version $Id: msg_view.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/msg_online.php');
	require_once(sysDocumentRoot . '/online/msg_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '2100300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 取得新訊息的總數，與第一筆新訊息的序號
	// $sysConn->debug = true;
	list($total, $serial) = dbGetStSr('WM_im_message', 'count(*) as total, `serial`',
									  "`username`='{$sysSession->username}' AND `sorder`=0 AND `reciver`='{$sysSession->username}' AND `saw`='N' group by `username` order by `send_time`", ADODB_FETCH_NUM);
	if (empty($serial)) {
		showXHTML_head_B($MSG['title_message'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_head_E();
		showXHTML_body_B('leftmargin="15" topmargin="10"');
			$ary = array();
			$ary[] = array($MSG['tabs_read_msg'][$sysSession->lang], 'tabs1');
			showXHTML_tabFrame_B($ary, 1, '', 'tabsMsgView');
				showXHTML_table_B('width="350" border="0" cellspacing="1" cellpadding="3" id="tabs1" class="cssTable"');
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td('colspan="2" nowrap="nowrap"', $MSG['msg_no_msg'][$sysSession->lang]);
					showXHTML_tr_E('');
					showXHTML_tr_B('class="cssTrOdd"');
						showXHTML_td_B('align="center" colspan="2" nowrap="nowrap"');
							showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="parent.close()"');
						showXHTML_td_E();
					showXHTML_tr_E('');
				showXHTML_table_E();
			showXHTML_tabFrame_E();
		showXHTML_body_E('');
		die();
	}

	// 取得訊息
	$RS = dbGetStMr('WM_im_message', '`sender`, `sender_name`, `send_time`, `talk`, `chat_id`, `message`, `ctype`', "`username`='{$sysSession->username}' AND `serial`='{$serial}' order by `sorder`", ADODB_FETCH_ASSOC);
	if (!$RS->EOF) {
		$sender      = $RS->fields['sender'];
		$sender_name = htmlspecialchars($RS->fields['sender_name'], ENT_QUOTES);
		$send_time   = $RS->fields['send_time'];
		$talk        = $RS->fields['talk'];
		$rid         = $RS->fields['chat_id'];
		$message     = $RS->fields['message'];
		$ctype       = $RS->fields['ctype'];
		$RS->MoveNext();
	}
	while (!$RS->EOF) {
		$message     .= $RS->fields['message'];
		$RS->MoveNext();
	}

	if ($ctype == 'text') {
		$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
		$replace = array("<a href=\"\\1\" target=\"_blank\" class=\"cssAnchor\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
		//$content = '<pre>' . preg_replace($patterns, $replace, htmlspecialchars($content, ENT_QUOTES)) . '</pre>';
		$message = nl2br(preg_replace($patterns, $replace, htmlspecialchars($message, ENT_QUOTES)));
	}

	// 設定已經讀取的旗標
	dbSet('WM_im_message', '`saw`="Y"', "`serial`='{$serial}'");
	list($hid) = dbGetStSr('WM_user_account', '`hid`', "`username`='$sender'", ADODB_FETCH_NUM);
	$fhid      = 32;
	$hidPic    = ($hid & $fhid);
	$winWidth  = ($hidPic) ? 410 : 520;

	$pwd = md5(sysTicketSeed . $_COOKIE['idx']);
	$enc = @mcrypt_encrypt(MCRYPT_DES, $pwd, $rid, 'ecb');
	$rid = base64_encode($enc);

	$js  = $sendJS;
	$js .= <<< BOF

	var rid = "{$rid}";

	user["{$sender}"] = "{$sender_name}";
	function msgView() {
		window.location.replace("/online/msg_view.php");
	}

	function picReSize() {
		var orgW = 0, orgH = 0;
		var demagnify = 0;
		var node = document.getElementById("MyPic");

		if ((typeof(node) != "object") || (node == null)) return false;
		orgW = parseInt(node.width);
		orgH = parseInt(node.height);
		if ((orgW > 110) || (orgH > 120)) {
			demagnify = (((orgW / 110) > (orgH / 120)) ? parseInt(orgW / 110) : parseInt(orgH / 120)) + 1;
			node.width  = parseInt(orgW / demagnify);
			node.height = parseInt(orgH / demagnify);
		}
		node.parentNode.style.height = node.height + 3;
		// node.onload = function() {};
	}

	window.onload = function () {
		if (typeof(window.dialogWidth) == "undefined") {
			parent.window.resizeTo({$winWidth}, 400);
		} else {
			window.dialogWidth  = {$winWidth} + "px";
			window.dialogHeight = 400 + "px";
		}
		// picReSize();
	};
BOF;

	showXHTML_head_B($MSG['title_message'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', 'hotkey.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('leftmargin="15" topmargin="10"');
		echo '<div id="tabsMsgView">';
		showXHTML_table_B();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top"');
					// 閱讀傳訊 (Begin)
					$ary = array();
					switch ($talk) {
						case 'Alert':
							break;
						case 'Talk':
							$ary[] = array($MSG['tabs_read_call'][$sysSession->lang], 'tabs1');
							$help  = $MSG['msg_from1'][$sysSession->lang] . $sender . ' (' . $sender_name . ') ' . $MSG['msg_from3'][$sysSession->lang];
							$rname =  $sender . '(' . $sender_name . ') ' . $MSG['msg_room_name'][$sysSession->lang];
							break;
						case 'Accept':
							$ary[] = array($MSG['tabs_read_call'][$sysSession->lang], 'tabs1');
							$help  = $sender . ' (' . $sender_name . ') ' . $MSG['msg_from_accept'][$sysSession->lang];
							$rname =  $sysSession->username . '(' . $sysSession->realname . ') ' . $MSG['msg_room_name'][$sysSession->lang];
							break;
						case 'Refuse':
							$ary[] = array($MSG['tabs_read_call'][$sysSession->lang], 'tabs1');
							$help  = $sender . ' (' . $sender_name . ') ' . $MSG['msg_from_reject'][$sysSession->lang];
							break;
						default:
							$ary[] = array($MSG['tabs_read_msg'][$sysSession->lang], 'tabs1');
							$help  = $MSG['msg_from1'][$sysSession->lang] . $sender . ' (' . $sender_name . ') ' . $MSG['msg_from2'][$sysSession->lang];
					}
					showXHTML_tabFrame_B($ary, 1);
						showXHTML_table_B('width="350" border="0" cellspacing="1" cellpadding="3" id="tabs1" class="cssTable"');
							showXHTML_tr_B('class="cssTrHead"');
								showXHTML_td('colspan="2" nowrap="nowrap"', $help);
							showXHTML_tr_E('');
							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td('width="35" nowrap="nowrap"' , $MSG['th_send_time'][$sysSession->lang]);
								showXHTML_td('width="315"', $send_time);
							showXHTML_tr_E('');
							showXHTML_tr_B('class="cssTrOdd"');
								showXHTML_td('width="35"' , $MSG['th_im_content'][$sysSession->lang]);
								showXHTML_td_B('width="315"');
									if (strlen($message) > 254) {
										echo '<div style="width:300px; height:210px; overflow:auto">' . $message . '</div>';
									} else {
										echo $message;
									}
								showXHTML_td_E();
							showXHTML_tr_E('');
							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td_B('align="center" colspan="2" nowrap="nowrap"');
									// 若是系統的提示，則不顯示回覆
									switch ($talk) {
										case 'Alert':
											break;
										case 'Talk':
											showXHTML_input('button', '', $MSG['btn_accept'][$sysSession->lang], '', 'class="cssBtn" onclick="acceptWrite(\'' . $sender . '\');"');
											showXHTML_input('button', '', $MSG['btn_reject'][$sysSession->lang], '', 'class="cssBtn" onclick="rejectWrite(\'' . $sender . '\');"');
											break;
										case 'Accept':
											showXHTML_input('button', '', $MSG['btn_chat'][$sysSession->lang], '', 'class="cssBtn" onclick="goChatroom();"');
											break;
										case 'Refuse':
											showXHTML_input('button', '', $MSG['btn_recall'][$sysSession->lang], '', 'class="cssBtn" onclick="callWrite(\'' . $sender . '\');"');
											break;
										default:
											showXHTML_input('button', '', $MSG['btn_reply'][$sysSession->lang], '', 'class="cssBtn" onclick="msgWrite(\'' . $sender . '\');"');
									}
									if ($total > 1) {
										showXHTML_input('button', '', $MSG['btn_im_next'][$sysSession->lang],   '', 'class="cssBtn" onclick="msgView()"');
									}
									showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="closeWin()"');
								showXHTML_td_E();
							showXHTML_tr_E('');
						showXHTML_table_E();
					showXHTML_tabFrame_E();
					// 閱讀傳訊 (End)
				showXHTML_td_E();
				if (!$hidPic) {
					showXHTML_td_B('valign="top"');
						// 顯示圖片 (Begin)
						$ary = array();
						$ary[] = array($MSG['tabs_user_pic'][$sysSession->lang], 'tabs4');
						showXHTML_tabFrame_B($ary, 1);
							showXHTML_table_B('width="110" border="0" cellspacing="1" cellpadding="3" id="tabs4" class="cssTable"');
								showXHTML_tr_B('class="cssTrEvn"');
									showXHTML_td_B('align="center" valign="middle" nowrap="nowrap" width="110"');
										echo '<div align="center" valign="middle" style="width:124px; height:120px; overflow:hidden" id="divPic">';
										$enc = @mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $serial, 'ecb');
										$ids = base64_encode($enc);
										echo '<img src="showpic.php?a=' . $ids . '" type="image/jpeg" id="MyPic" name="MyPic" borer="0" align="absmiddle" onload="picReSize()" loop="0">';
										echo '</div>';
									showXHTML_td_E();
								showXHTML_tr_E('');
							showXHTML_table_E();
						showXHTML_tabFrame_E();
						// 顯示圖片 (End)
					showXHTML_td_E();
				}
			showXHTML_tr_E();
		showXHTML_table_E();
		echo '</div>';
	// 若是系統的提示，底下的程式不需要顯示
	if ($talk != 'Alert') {
		$btns = array();
		if ($total > 1) {
			$btns[] = array($MSG['btn_im_next'][$sysSession->lang], 'msgView()');
		}
		$btns[] = array($MSG['btn_goto_view'][$sysSession->lang], 'msgLayer(false)');

		msgSendWin($sender, $btns, false, $rname);
	}
	showXHTML_body_E('');
?>

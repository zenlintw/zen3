<?php
	/**
	 * 線上傳訊-訊息回顧
	 *
	 * @since   2003/11/12
	 * @author  ShenTing Lin
	 * @version $Id: msg_history.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/msg_online.php');
	require_once(sysDocumentRoot . '/online/msg_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2100300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// $RS = dbGetStMr('WM_im_message', '*', "`username`='{$sysSession->username}' order by `send_time`, `serial`, `sorder`, `sender`");
	$idx = isset($_POST['msgtp']) ? intval($_POST['msgtp']) : 1;
	switch ($idx) {
		case 1 :
			$sqls = "`username`='{$sysSession->username}' AND `reciver`='{$sysSession->username}'";
			break;
		case 2 :
			$sqls = "`username`='{$sysSession->username}' AND `sender`='{$sysSession->username}'";
			break;
		case 3 :
		    $sqls = '`username` = "' . $sysSession->username . '"';
		default:
	}

	$order = ' order by `send_time` desc, `serial`, `sorder`, `sender`';
	$RS = dbGetStMr('WM_im_message',
					'`sorder`, `sender`, `sender_name`, `reciver`, `send_time`, `message`, `ctype`',
					$sqls . $order, ADODB_FETCH_ASSOC);

	$js  = $sendJS;
	$js .= <<< BOF

	function goList() {
		window.location.replace("/online/userlist.php");
	}

	function chgHistory(val) {
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.msgtp.value = val;
		obj.submit();
	}

	window.onload = function () {
		var obj1 = null, obj2 = null;
		if (typeof(window.dialogWidth) == "undefined") {
			parent.window.resizeTo(620, 500);
		} else {
			window.dialogWidth  = 620 + "px";
			window.dialogHeight = 500 + "px";
		}
		obj1 = document.getElementById("tb1");
		obj2 = document.getElementById("tb2");
		if ((obj1 != null) && (obj2 != null)) {
			obj2.innerHTML = obj1.innerHTML;
		}
		// picReSize();
	};
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', 'hotkey.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_reciver_msg'][$sysSession->lang], 'tabs1', 'chgHistory(1)');
		$ary[] = array($MSG['tabs_outgoing_msg'][$sysSession->lang], 'tabs2', 'chgHistory(2)');
		$ary[] = array($MSG['tabs_in_out_msg'][$sysSession->lang], 'tab3', 'chgHistory(3)');
		showXHTML_tabFrame_B($ary, $idx, '', 'tabsMsgView');
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="tabsMsgList" class="cssTable"');
				$col = 'class="cssTrEvn"';
				switch ($idx) {
				    case 1 : $cols = ' colspan="4"'; break;
				    case 2 : $cols = ' colspan="3"'; break;
				    case 3 : $cols = ' colspan="5"'; break;
				}
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" id="tb1"' . $cols);
						showXHTML_input('button', '', $MSG['btn_goto_list'][$sysSession->lang], '', 'class="cssBtn" onclick="goList()"');
					showXHTML_td_E();
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					if ($idx == 1 || $idx == 3) showXHTML_td('align="center"', $MSG['th_sender'][$sysSession->lang]);
					if ($idx == 2 || $idx == 3) showXHTML_td('align="center"', $MSG['th_reciver'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_send_time'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_message'][$sysSession->lang]);
					if ($idx == 1 || $idx == 3) showXHTML_td('align="center"', $MSG['th_reply'][$sysSession->lang]);
				showXHTML_tr_E('');

				$message = '';
				$cnt = 0;
				$user = array();
				if ($RS && ($RS->RecordCount() > 0)) {
					while (!$RS->EOF) {
						if ($RS->fields['sorder'] == 0) {
							if ($cnt != 0) {
									if ($ctype == 'text') {
										$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
										$replace = array("<a href=\"\\1\" target=\"_blank\" class=\"cssAnchor\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
										$message = nl2br(preg_replace($patterns, $replace, htmlspecialchars($message, ENT_QUOTES)));
									}
									showXHTML_td_B('');
										if (strlen($message) > 254) {
											if ($idx == 1) echo '<div style="width:360px; height:110px; overflow:auto">' . $message . '</div>';
											if ($idx == 2 || $idx == 3) echo '<div style="width:400px; height:110px; overflow:auto">' . $message . '</div>';
										} else {
											echo $message;
										}
									showXHTML_td_E();
									if ($idx == 1 || $idx == 3) {
										showXHTML_td_B();
										    if ($reciver == $sysSession->username)
											    showXHTML_input('button', '', $MSG['btn_reply'][$sysSession->lang], '', 'class="cssBtn" onclick="msgReply(\'' . $sender . '\', ' . $cnt. ')"');
											else
											    echo '&nbsp;';
										showXHTML_td_E();
									}
								showXHTML_tr_E('');
							}

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							$message = '';
							$cnt++;
							$ctype   = $RS->fields['ctype'];
							$sender  = $RS->fields['sender'];
							$reciver = $RS->fields['reciver'];
							showXHTML_tr_B($col);
								if ($idx == 1 || $idx == 3) showXHTML_td('', $sender . '<br />(' . $RS->fields['sender_name'] . ')');
								if ($idx == 2 || $idx == 3) {
									if (!isset($user[$reciver])) {
										list($firstN, $lastN) = dbGetStSr('WM_user_account', '`first_name`, `last_name`', "`username`='{$reciver}'", ADODB_FETCH_NUM);
										$user[$reciver] = checkRealname($firstN, $lastN);
									}
									showXHTML_td('', $reciver . '<br />(' . $user[$reciver] . ')');
								}
								showXHTML_td('nowrap="nowrap"', $RS->fields['send_time']);
						}
						$message .= $RS->fields['message'];
						$RS->MoveNext();
					}

					if ($ctype == 'text') {
						$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
						$replace = array("<a href=\"\\1\" target=\"_blank\" class=\"cssAnchor\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
						$message = nl2br(preg_replace($patterns, $replace, htmlspecialchars($message, ENT_QUOTES)));
					}
					showXHTML_td_B();
						if (strlen($message) > 254) {
							if ($idx == 1 || $idx == 3) echo '<div style="width:360px; height:110px; overflow:auto">' . $message . '</div>';
							if ($idx == 2) echo '<div style="width:400px; height:110px; overflow:auto">' . $message . '</div>';
						} else {
							echo $message;
						}
					showXHTML_td_E();
					if ($idx == 1 || $idx == 3) {
						showXHTML_td_B();
						    if ($reciver == $sysSession->username)
							    showXHTML_input('button', '', $MSG['btn_reply'][$sysSession->lang], '', 'class="cssBtn" onclick="msgReply(\'' . $sender . '\', ' . $cnt . ')"');
							else
							    echo '&nbsp;';
						showXHTML_td_E();
					}
					showXHTML_tr_E('');
				} else {
					// 沒有任何訊息
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('align="center"' . $cols, $MSG['msg_no_message'][$sysSession->lang]);
					showXHTML_tr_E('');
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" id="tb2"' . $cols);
						showXHTML_input('button', '', $MSG['btn_goto_list'][$sysSession->lang], '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();

		showXHTML_form_B('action="' . $_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
			showXHTML_input('hidden', 'msgtp', '', '', '');
		showXHTML_form_E();

		if ($idx == 1 || $idx == 3) {
			$btns = array();
			$btns[] = array($MSG['btn_return_history'][$sysSession->lang], 'msgLayer(false)');
			msgSendWin('', $btns);
		}
	showXHTML_body_E();
?>

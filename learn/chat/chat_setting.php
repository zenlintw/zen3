<?php
	/**
	 * 聊天室設定
	 *
	 * @since   2003//
	 * @author  ShenTing Lin
	 * @version $Id: chat_setting.php,v 1.1 2010/02/24 02:39:06 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

/*
	1. 沒有主持權的只顯示個人喜好設定
	2. 有主持權的才顯示主持人設定
	3. 預設的主持人可以調整預設的主持人
	4. 要不要奪回主持權
*/

	// 檢查主持人 (Begin)
	$isHost  = false;
	$isAdmin = false;
	// 檢查是不是現在聊天室的主持人
	$host = getChatHost();
	if ($sysSession->username == $host) $isHost = true;
	// 檢查是不是聊天室的管理員
	$host = getChatAdmin();
	if ($sysSession->username == $host) {
		$isHost  = true;
		$isAdmin = true;
	}
	// 檢查主持人 (End)

	$js = <<< BOF

	function initUser() {
		var txt = "", chk = "";
		var obj = document.getElementById("setFm");
		if ((typeof(opener) == "object") && (opener != null)) {
			obj.user_exit.selectedIndex = (opener.userPref["exit"] == "notebook") ? 1 : 0;
			obj.user_in_out.checked = opener.userPref["inout"];
		}

		obj = document.getElementById("user_userlst");
		if ((typeof(opener) == "object") && (opener != null)) {
			var j = 0;
			for (var i in opener.userLst) {
				if (i == opener.mySelf) continue;
				j++;
				chk  = opener.denyLst[i] ? ' checked="checked"' : '';
				txt += '<input type="checkbox" value="' + i + '"' + chk + '> ' + opener.userLst[i][0] + " (" + i + ")";
				// if ((j % 2) == 0) txt += "<br />";
				txt += "<br />";
			}
			if (txt == "") txt = "{$MSG['chat_msg_empty'][$sysSession->lang]}";
			obj.innerHTML = txt;
		}
	}

	function saveSetting() {
		var val = "";
		var nodes = null;
		var obj = document.getElementById("user_userlst");
		if ((typeof(opener) == "object") && (opener != null)) {
			nodes = obj.getElementsByTagName("input");
			for (var i = 0; i < nodes.length; i++) {
				if (nodes[i].type != "checkbox") continue;
				val = nodes[i].value;
				opener.denyLst[val] = nodes[i].checked;
			}
		}

		obj = document.getElementById("setFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		if ((typeof(opener) == "object") && (opener != null)) {
			opener.userPref["inout"] = obj.user_in_out.checked;
			opener.userPref["exit"] = obj.user_exit.value;
		}

		opener.chat_style();
		obj.submit();
	}

	window.onload = function () {
		if ((typeof(opener) != "object") || (opener == null)) return false;
		initUser();
	};
BOF;

	showXHTML_head_B($MSG['chat_set_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('topmargin="10"');
		$ary = array();
		$ary[] = array($MSG['chat_user_set'][$sysSession->lang], 'tabs_user');
		if ($isHost) $ary[] = array($MSG['chat_host_set'][$sysSession->lang], 'tabs_host');
		showXHTML_tabFrame_B($ary, 1, 'setFm', '', 'action="chat_setting1.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			// 個人喜好設定 (Begin)
			showXHTML_table_B('width="400" border="0" cellspacing="1" cellpadding="3" id="tabs_user" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('', $MSG['user_set_msg'][$sysSession->lang]);
				showXHTML_tr_E();
				// 離開處理聊天內容
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B();
						echo $MSG['user_msg_exit'][$sysSession->lang];
						showXHTML_input('select', 'user_exit', $exitUser, 'notebook', 'class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 接受線上傳訊
				list($rec) = dbGetStSr('WM_im_setting', '`recive`', "`username`='{$sysSession->username}'", ADODB_FETCH_NUM);
				$chk = ($rec == 'N') ? '' : 'checked="checked"';
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B();
						showXHTML_input('checkbox', 'user_online_msg', '', '', $chk);
						echo $MSG['user_msg_message'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 顯示人員進出訊息
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B();
						showXHTML_input('checkbox', 'user_in_out', '', '', 'checked="checked"', 'separator');
						echo $MSG['user_msg_in_out'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 不想看到哪些人的訊息
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B();
						echo $MSG['user_msg_deny'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 不想看到哪些人的訊息，人員列表
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('id="user_userlst" style="padding-left: 30px;"', '&nbsp;a');
				showXHTML_tr_E();
				// 離開按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center"');
						showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="saveSetting()"');
						echo '&nbsp;';
						showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.close();"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// 個人喜好設定 (End)
			if ($isHost) {
				$RS = dbGetStSr('WM_chat_setting', '*', "`rid`='{$sysSession->room_id}'", ADODB_FETCH_ASSOC);
				// 主持人設定 (Begin)
				$col = 'class="cssTrOdd"';
				showXHTML_table_B('width="400" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable" style="display: none;"');
					showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td('colspan="2"', $MSG['host_set_msg'][$sysSession->lang]);
					showXHTML_tr_E();
					// 聊天室名稱
					$lang = old_getCaption($RS['title']);
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					$arr_names = array('Big5'		=>	'host_room_name_big5',
								       'GB2312'		=>	'host_room_name_gb',
								       'en'			=>	'host_room_name_en',
								       'EUC-JP'		=>	'host_room_name_jp',
								       'user_define'=>	'host_room_name_user'
								        );
				    showXHTML_tr_B($col);
					    showXHTML_td('align="right" valign="center"', $MSG['host_msg_room_name'][$sysSession->lang]);
					    showXHTML_td_B('');
						    $multi_lang = new Multi_lang(false, $lang, $col); // 多語系輸入框
						    $multi_lang->show(true, $arr_names);
					    showXHTML_td_E();
				    showXHTML_tr_E();
				
					// 人數限制
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="2"');
							echo $MSG['host_msg_user_limit1'][$sysSession->lang];
							showXHTML_input('text', 'host_user_limit', intval($RS['maximum']), '', 'maxlength="5" class="cssInput" style="width: 30px;"');
							echo $MSG['host_msg_user_limit2'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					// 結束聊天後處理聊天內容
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="2"');
							echo $MSG['host_msg_exit'][$sysSession->lang];
							showXHTML_input('select', 'host_exit', $exitHost, trim($RS['exit_action']), 'class="cssInput"');
						showXHTML_td_E();
					showXHTML_tr_E();
					// 允許切換聊天室
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="2"');
							$chk = (trim($RS['jump']) == 'allow') ? ' checked="checked"' : '';
							showXHTML_input('checkbox', 'host_change', '', '', $chk);
							echo $MSG['host_msg_allow_chg'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					/*
					// 影音互動設定
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="3"');
							echo $MSG['host_msg_media_set'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					// 啟動影音互動
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="3" style="padding-left: 30px;"');
							$chk = (trim($RS['media']) == 'enable') ? ' checked="checked"' : '';
							showXHTML_input('checkbox', 'enable_media', '', '', $chk);
							echo $MSG['host_msg_enable_media'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					// 語音設定 IP 與 Port
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="3" style="padding-left: 30px;"');
							echo $MSG['host_msg_media_ip'][$sysSession->lang];
							showXHTML_input('text', 'host_media_ip', trim($RS['ip']), '', 'class="cssInput"');
							echo $MSG['host_msg_media_port'][$sysSession->lang];
							showXHTML_input('text', 'host_media_port', intval($RS['port']), '', 'maxlength="5" class="cssInput" style="width: 60px;"');
						showXHTML_td_E();
					showXHTML_tr_E();
					// 語音設定通訊協定
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="3" style="padding-left: 30px;"');
							echo $MSG['host_msg_media_protocol'][$sysSession->lang];
							$ary = array(
								'tcp' => $MSG['tcp'][$sysSession->lang],
								'udp' => $MSG['udp'][$sysSession->lang],
							);
							$chk = (trim($RS['protocol']) == 'TCP') ? 'tcp' : 'udp';
							showXHTML_input('radio', 'host_media_protocol', $ary, $chk, '');
						showXHTML_td_E();
					showXHTML_tr_E();
					*/
					// 主持人設定
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="2"');
							echo $MSG['host_msg_host_set'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					// 一般更換主持人
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
							echo $MSG['host_msg_chg_host'][$sysSession->lang];
							$RSS   = dbGetStMr('WM_chat_session', '`username`, `realname`, `host`', "`rid`='{$sysSession->room_id}' order by `host` DESC, `login` ASC", ADODB_FETCH_ASSOC);
							$lst   = array();
							$host  = '';
							$sels  = '<select name="newHost" id="newHost" class="cssInput">';
							while (!$RSS->EOF) {
								if ($RSS->fields['host'] == 'Y') {
									$sels .= '<option value="' . $RSS->fields['username'] . '" style="color: #FF0000;">* ' . $RSS->fields['realname'] . ' (' . $RSS->fields['username'] . ') </option>';
								} else {
									$sels .= '<option value="' . $RSS->fields['username'] . '">' . $RSS->fields['realname'] . ' (' . $RSS->fields['username'] . ') </option>';
								}
								$RSS->MoveNext();
							}
							$sels .= '</select>';
							echo $sels;
						showXHTML_td_E();
					showXHTML_tr_E();
					if ($isAdmin) {
						// 聊天室管理員
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
								echo $MSG['host_msg_root'][$sysSession->lang];
								showXHTML_input('text', 'host_root', trim($RS['host']), '', 'maxlength="32" class="cssInput"');
							showXHTML_td_E();
						showXHTML_tr_E();
						// 登入時是否取回主持權
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
								echo $MSG['host_msg_login'][$sysSession->lang];
								$ary = array(
									'yes' => $MSG['yes'][$sysSession->lang],
									'no'  => $MSG['no'][$sysSession->lang],
								);
								$chk = (trim($RS['get_host']) == 'Y') ? 'yes' : 'no';
								showXHTML_input('radio', 'host_login', $ary, $chk, '');
							showXHTML_td_E();
						showXHTML_tr_E();
					}
					// 離開按鈕
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="2" align="center"');
							showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="saveSetting()"');
							echo '&nbsp;';
							showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.close();"');
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
				// 主持人設定 (End)
			}
		showXHTML_tabFrame_E();
	showXHTML_body_E();

?>

<?php
	/**
	 * 訊息中心模組
	 *
	 * @since   2004/09/09
	 * @author  ShenTing Lin
	 * @version $Id: mod_message.php,v 1.1 2010/02/24 02:39:09 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	if (!defined('MYCOURSE_MODULE') || MYCOURSE_MODULE === false) {
		include_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
		include_once(sysDocumentRoot . '/lib/acl_api.php');
	}
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	
	$sysSession->cur_func = '2100100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!function_exists('showDatetime')) {
		function showDatetime($val) {
			global $sysSession, $sysConn;
			$time = $sysConn->UnixTimeStamp($val);
			return divMsg(120, date('Y/m/d H:i:s', $time));
		}
	}

	$lang = strtolower($sysSession->lang);
	$user_ary = array();
	if (!function_exists('showSender')) {
		function showSender($sender) {
			global $user_ary, $lang;
			if (!array_key_exists($sender, $user_ary)) {
				$RSS = dbGetStSr('WM_user_account', '`first_name`, `last_name`, `email`, `homepage`', "`username`='{$sender}'", ADODB_FETCH_ASSOC);
				// Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
                $username = checkRealname($RSS['first_name'],$RSS['last_name']);
                // $username = (($lang == 'big5') || ($lang == 'gb2312')) ? $RSS['last_name'] . $RSS['first_name'] : $RSS['first_name'] . ' ' . $RSS['last_name'];
				$user_ary[$sender] = array($username, $RSS['email'], $RSS['homepage']);
			} else {
				$username = $user_ary[$sender][0];
			}
			$email = $user_ary[$sender][1];
			$homepage = $user_ary[$sender][2];
			$from  = (!empty($email)) ? ("<a href=\"mailto:{$email}\" class=\"cssAnchor\" onclick=\"event.cancelBubble=true;\">{$sender}</a>"): $sender;
			$from .= '&nbsp;';
			$from .= (!empty($homepage)) ? ("(<a href=\"{$homepage}\" class=\"cssAnchor\" target=\"_blank\" onclick=\"event.cancelBubble=true;\">{$username}</a>)"):"({$username})";
			return divMsg(100, $from, "{$sender}&nbsp;({$username})");
		}
	}

	$isEdit = ($sysSession->username != 'guest');
	$lines = 5;
	$id = 'MyMessageCenter';
	// 主要視窗大小的設定 (Begin)
	$wd = $defSize - 10;
	$dd = ($defSize < $defRSize) ? $wd : intval($defRSize) - 245;
	$Ld = $defLSize - 15;
	$Rd = intval($defRSize) - 245;
	if (ereg('MSIE [567]', $_SERVER["HTTP_USER_AGENT"])) {
		// IE 要比 Firefox 多減 10 px
		$dd -= 10;
		$Rd -= 10;
	}
	$display = ($defSize < $defRSize) ? ' style="display: none;"' : '';
	// 主要視窗大小的設定 (End)
	$id = showXHTML_mytitle_B($id, $MSG['tabs_message'][$sysSession->lang], $wd, $isEdit);
		showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tab_' . $id . '"');
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td_B('colspan="3"');
					// 取得幾封新訊息
					list($cnt) = dbGetStSr('WM_msg_message', 'count(*)', "`folder_id`='sys_inbox' AND `receiver`='{$sysSession->username}' AND `status`=''", ADODB_FETCH_NUM);
					// 取得收件匣的名稱
					$name = getNameFromID('sys_inbox', $username);
					$line = (intval($cnt) > intval($lines)) ? intval($lines) : intval($cnt);
					echo sprintf($MSG['msg_new_message'][$sysSession->lang], $name, $cnt, $line);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('align="center" nowrap="nowrap"', $MSG['th_subject'][$sysSession->lang]);
				showXHTML_td('align="center" nowrap="nowrap"' . $display, $MSG['th_sender'][$sysSession->lang]);
				showXHTML_td('align="center" nowrap="nowrap"' . $display, $MSG['th_send_time'][$sysSession->lang]);
			showXHTML_tr_E();
			// 取得幾封新訊息
			$table = 'WM_msg_message';
			$field = '`msg_serial`, `sender`, `submit_time`, `subject`';
			$where = "`folder_id`='sys_inbox' AND `receiver`='{$sysSession->username}' AND `status`='' order by `submit_time` DESC limit 0,{$lines}";
			$RS = dbGetStMr($table, $field, $where, ADODB_FETCH_ASSOC);
			if ($RS) {
				while (!$RS->EOF) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('nowrap="nowrap" onclick="mod_' . $id . '_read(\'' . $RS->fields['msg_serial'] . '\');"');
							$title = htmlspecialchars($RS->fields['subject']);
							if (empty($title)) $title = '&nbsp;';
							$href = '<a href="javascript:;" onclick="return false;" class="cssAnchor">' . $title . ' </a>';
							echo divMsg($dd, $href, $title);
						showXHTML_td_E();
						showXHTML_td('nowrap="nowrap"' . $display, showSender($RS->fields['sender']));
						showXHTML_td('nowrap="nowrap"' . $display, showDatetime($RS->fields['submit_time']));
					showXHTML_tr_E();
					$RS->MoveNext();
				}
			}
			if (intval($cnt) > intval($lines)) {
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="3"');
						showXHTML_mytitle_more('onclick="mod_' . $id . '_more(); return false;"');
					showXHTML_td_E();
				showXHTML_tr_E();
			}
		showXHTML_table_E();
		$msg = ($isEdit) ? $MSG['msg_reposition_here'][$sysSession->lang] : '&nbsp;';
		showXHTML_mytitle_postit($id, $msg);
	showXHTML_mytitle_E();

	showXHTML_form_B('action="/message/read.php" method="post" enctype="multipart/form-data" style="display:none"', 'fm_' . $id . '_read');
		showXHTML_input('hidden', 'serial', '0', '', '');
		showXHTML_input('hidden', 'page'  , '10000', '', '');
	showXHTML_form_E('');

	$js = <<< BOF
	function mod_{$id}_resize() {
		if (dragID != "{$id}") return false;
		var nodes = null;
		var objName = "{$id}";
		var obj = document.getElementById("tab_" + objName);
		var isSmall = false;
		var wd = 0, cnt = 0;
		if ((typeof(obj) != "object") || (obj == null)) return false;
		isSmall = (parseInt(curSize) <= {$defLSize});
		wd = isSmall ? {$Ld} : {$Rd};
		cnt = (obj.rows.length <= ({$lines} + 2)) ? obj.rows.length : obj.rows.length - 1;
		for (var i = 1; i < cnt; i++) {
			obj.rows[i].cells[1].style.display = isSmall ? "none" : "";
			obj.rows[i].cells[2].style.display = isSmall ? "none" : "";
			nodes = obj.rows[i].cells[0].getElementsByTagName("div");
			for (var j = 0; j < nodes.length; j++) {
				nodes[j].style.width = wd + "px";
			}
		}
	}

	/**
	 * read message
	 * @param integer val : message serial
	 * @return
	 **/
	function mod_{$id}_read(val){
		var obj = document.getElementById("fm_{$id}_read");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		obj.serial.value = val;
		obj.submit();
	}

	function mod_{$id}_more() {
		window.location.replace("/message/index.php");
	}

BOF;
	showXHTML_script('inline', $js);
?>

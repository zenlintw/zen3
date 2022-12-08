<?php
	/**
	 * 群組討論室列表
	 *
	 * @since   2004/10/21
	 * @author  ShenTing Lin
	 * @version $Id: chat_group_manage.php,v 1.1 2009-06-25 09:27:39 edi Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');

	$sysSession->cur_func = '2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = strip_tags($caption);
		return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
	}

	function showSubject($val, $act) {
		global $sysSession;
		$lang = getCaption($val);
		return divMsg(200, '<a href="javascript:;" onclick="goChat(\'' . $act . '\'); return false;" class="cssAnchor">' . htmlspecialchars_decode($lang[$sysSession->lang]) . '</a>', strip_tags(htmlspecialchars_decode($lang[$sysSession->lang])));
	}

	// 檢查課程編號
	$csid = intval($sysSession->course_id);
	if (($csid <= 10000000) || ($csid >= 100000000)) {
		echo $MSG['msg_csid_error'][$sysSession->lang];
		showXHTML_body_E();
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '課程編號錯誤');
		die();
	}

	// 取得所有分組次 (Begin)
	$teams = array();
	$RS = dbGetStMr('WM_student_separate', '`team_id`, `team_name`', "`course_id`={$csid} order by `permute`", ADODB_FETCH_ASSOC);
	if ($RS) {
		while (!$RS->EOF) {
			$tid = intval($RS->fields['team_id']);
			$lang = getCaption($RS->fields['team_name']);
			$team[$tid] = $lang[$sysSession->lang];
			$RS->MoveNext();
		}
	}
	// 取得所有分組次 (End)
	$tid = intval($_POST['tid']);
	if (empty($tid) && is_array($team)) {
		// 取得第一個分組次
		reset($team);
		list($key, $val) = each($team);
		$tid = intval($key);
		reset($team);
	} else {
		$tid = intval($_POST['tid']);
	}

	// 計算總共有幾筆資料
	list($total_msg) = dbGetStSr('WM_student_group', 'count(*)', "`course_id`={$csid} AND `team_id`={$tid}", ADODB_FETCH_NUM);
	$total_msg = intval($total_msg);

	// 計算總共分幾頁
	$lines = (defined('sysPostPerPage')) ? sysPostPerPage : 10;
	$total_page = ceil($total_msg / $lines);

	// 產生下拉換頁選單
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['all_page'][$sysSession->lang];

	// 設定下拉換頁選單顯示第幾頁
	$page_no = isset($_POST['page']) ? intval($_POST['page']) : 1;
	if (($page_no < 0) || ($page_no > $total_page)) $page_no = $total_page;

	// 產生執行的 SQL 指令
	$sqls = '';
	if (!empty($page_no)) {
		$limit = intval($page_no - 1) * $lines;
		$sqls .= " limit {$limit}, {$lines} ";
	}
	$lang = strtolower($sysSession->lang);
	$js = <<< BOF
	var theme      = "{$sysSession->theme}";
	var lang       = "{$lang}";
	var total_page = "{$total_page}";

	/**
	 * 進入聊天室
	 * @param string val : 聊天室編號
	 **/
	function goChat(val) {
		if (typeof(parent.c_sysbar) == "object") {
			if (typeof(parent.c_sysbar.goChatroom) == "function") parent.c_sysbar.goChatroom(val);
		} else if (typeof(parent.s_sysbar) == "object") {
			if (typeof(parent.s_sysbar.goChatroom) == "function") parent.s_sysbar.goChatroom(val);
		}
	}

	/**
	 * 設定聊天室
	 * @param string val : 聊天室編號
	 **/
	function setChat(val) {
		var obj = document.getElementById("editFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.chat_id.value = val;
		obj.submit();
	}

	/**
	 * change page
	 * @param integer n : action type or page number
	 * @return
	 **/
	function go_page(n){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		switch(n){
			case -1:	// 第一頁
				obj.page.value = 1;
				break;
			case -2:	// 前一頁
				obj.page.value = parseInt(obj.page.value) - 1;
				if (parseInt(obj.page.value) == 0) obj.page.value = 1;
				break;
			case -3:	// 後一頁
				obj.page.value = parseInt(obj.page.value) + 1;
				break;
			case -4:	// 最末頁
				obj.page.value = parseInt(total_page);
				break;
			default:	// 指定某頁
				obj.page.value = parseInt(n);
				break;
		}
		obj.submit();
	}

	function chgTeam(val) {
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		obj.page.value = 1;
		obj.tid.value = val;
		obj.submit();
	}

	function goto_chatgroup() {
		window.location.replace("chat_manage.php");
	}

	window.onload = function () {
		var obj1 = null, obj2 = null;
		obj1 = document.getElementById("toolbar1");
		obj2 = document.getElementById("toolbar2");
		if ((obj1 != null) && (obj2 != null)) {
			obj2.innerHTML = obj1.innerHTML;
		}
	};
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['course'][$sysSession->lang] . $MSG['tabs_chat_list'][$sysSession->lang], 'tabs1', "goto_chatgroup();");
		$ary[] = array($MSG['group_chat_list'][$sysSession->lang], 'tabs2', '');
		$colspan = 'colspan="5"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 2); //, form_id, table_id, form_extra, isDragable);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td($colspan, $MSG['msg_help_groupchat_manage'][$sysSession->lang]);
				showXHTML_tr_E();
				// 工具列 (Begin)
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B($colspan . ' id="toolbar1"');
						echo '&nbsp;' , $MSG['page_no_3'][$sysSession->lang];
						showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
						echo '&nbsp;';
						showXHTML_input('button', 'fp', $MSG['page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
						showXHTML_input('button', 'pp', $MSG['page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
						showXHTML_input('button', 'np', $MSG['page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
						showXHTML_input('button', 'lp', $MSG['page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
						echo '&nbsp;&nbsp;' , $MSG['th_group_div'][$sysSession->lang];
						showXHTML_input('select', 'tid', $team, $tid, 'class="cssInput" onchange="chgTeam(this.value);"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 工具列 (End)
				if ($total_msg <= 0) {
					showXHTML_tr_B('colspan="' . $cols . '" align="center" colspan="2" class="cssTrOdd"');
						showXHTML_td('', $MSG['msg_no_student_group'][$sysSession->lang]);
					showXHTML_tr_E();
				} else {
					// 標題 (Begin)
					showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_group_name'][$sysSession->lang]);
						showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_room_name'][$sysSession->lang]);
						showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_admin'][$sysSession->lang]);
						showXHTML_td('align="center"', $MSG['th_action'][$sysSession->lang]);
					showXHTML_tr_E();
					// 標題 (End)

					$RS = dbGetStMr('WM_student_group', '`group_id`, `caption`, `captain`', "`course_id`={$csid} AND `team_id`={$tid} order by `permute` {$sqls}", ADODB_FETCH_ASSOC);
					if ($RS) {
						while (!$RS->EOF) {
							$gid = intval($RS->fields['group_id']);
							$owner = sprintf('%d_%d_%d', $csid, $tid, $gid);
							list($rid, $title, $media,$host) = dbGetStSr('WM_chat_setting', '`rid`,`title` , `media`,`host`', "`owner`='{$owner}'", ADODB_FETCH_NUM);
							if (empty($media)) $media = 'disable';

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								$lang = getCaption($RS->fields['caption']);
								showXHTML_td('',divMsg(200, htmlspecialchars_decode($lang[$sysSession->lang])));
								showXHTML_td_B('nowrap="noWrap"');
									echo showSubject($title, trim($rid));
								showXHTML_td_E();
								showXHTML_td('', $host);
								// showXHTML_td('align="center"', $mediaStatus[$media]);
								showXHTML_td_B('nowrap="noWrap" align="center"');
									showXHTML_input('button', 'btnEdit' , $MSG['btn_edit'][$sysSession->lang] , '', 'class="cssBtn" onclick="setChat(\'' . trim($rid) . '\')"');
								showXHTML_td_E();
							showXHTML_tr_E();
							$RS->MoveNext();
						}
					}
				}
				// 工具列 (Begin)
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td($colspan . ' id="toolbar2"', '&nbsp;');
				showXHTML_tr_E();
				// 工具列 (End)
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="chat_group_manage.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
			showXHTML_input('hidden', 'sortby', $sortby , '', '');
			showXHTML_input('hidden', 'order' , $order  , '', '');
			showXHTML_input('hidden', 'page'  , $page_no, '', '');
			showXHTML_input('hidden', 'tid'   , $tid    , '', '');
		showXHTML_form_E('');

		showXHTML_form_B('action="chat_group_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editFm');
			showXHTML_input('hidden', 'page'   , $page_no                            , '', '');
			showXHTML_input('hidden', 'tid'    , $tid                                , '', '');
			showXHTML_input('hidden', 'chat_id', ''                                  , '', '');
			showXHTML_input('hidden', 'ticket' , md5(sysTicketSeed . $_COOKIE['idx']), '', '');
		showXHTML_form_E();
	showXHTML_body_E();
?>

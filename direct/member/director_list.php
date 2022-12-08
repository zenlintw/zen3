<?php
	/**
	 * �ɮv�P�U�ЦC��
	 *
	 * @since   2004/06/30
	 * @author  ShenTing Lin
	 * @version $Id: director_list.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/direct/member/member_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '300100600';
	$sysSession->restore();
	if (!aclVerifyPermission(300100600, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$kind    = isset($_POST['kind']) ? trim($_POST['kind']) : '';
	$keyword = stripslashes(trim($_POST['keyword']));
	$roles   = isset($_POST['roles']) ? trim($_POST['roles']) : 'all';
	$caid    = intval($sysSession->class_id);

	// �P�B checkbox (Begin)
	$lsList  = trim($_POST['lsList']);
	$lsAry   = explode(',', $lsList);
	$tmp     = array();
	foreach ($lsAry as $val) {
		$val = trim($val);
		if (empty($val)) continue;
		$tmp[] = '"' . $val . '" : true';
	}
	$lsStr = (count($tmp) > 0) ? implode(',', $tmp) : '';
	$lsStr = 'var lsObj = {' . $lsStr . '};';
	// �P�B checkbox (End)

	$users = getClassDirector($roles, $kind, $keyword);
	$isDirector = ($users[$sysSession->username]['role'] == $sysRoles['director']);

	// ½��
	$lines = 10;
	$total = count($users);
	$total_page = ceil($total / $lines);

	// �]�w�U�Դ��������ܲĴX��
	if (isset($_POST['page']))
		$page_no = intval($_POST['page']);
	else
		$page_no = 1;
	if (($page_no < 0) || ($page_no > $total_page)) $page_no = $total_page;

	// ���ͤU�Դ������
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['all_page'][$sysSession->lang];

	$js = <<< BOF
	var total_page = "{$total_page}";
	var pg = "{$page_no}";
	// �P�B checkbox
	{$lsStr}

	var MSG_SELECT_ALL    = "{$MSG['btn_select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['btn_select_cancel'][$sysSession->lang]}";

	/**
	 * ½��
	 * @param integer n : �ʧ@�O�έ���
	 **/
	function go_page(n){
		var obj = document.getElementById("fmAction");
		if (obj == null) return false;
		switch(n){
			case -1:	// �Ĥ@��
				pg = 1;
				break;
			case -2:	// �e�@��
				pg = parseInt(pg) - 1;
				if (parseInt(pg) == 0) pg = 1;
				break;
			case -3:	// ��@��
				pg = parseInt(pg) + 1;
				break;
			case -4:	// �̥���
				pg = parseInt(total_page);
				break;
			default:	// ���w�Y��
				pg = parseInt(n);
				break;
		}
		// �P�B checkbox (Begin)
		var ary = new Array();
		for (var i in lsObj) {
			if (lsObj[i]) ary[ary.length] = i;
		}
		obj.lsList.value = ary.toString();
		// �P�B checkbox (End)
		obj.page.value = pg;
		obj.submit();
	}

	/**
	 * �j�M
	 **/
	function find() {
		var obj = document.getElementById("fmList");
		if (obj != null) {
			obj.action = "";
			obj.submit();
		}
	}

	/**
	 * �H�H
	 **/
	function sendMail() {
		var obj = document.getElementById("fmList");
		if (obj != null) {
			obj.action = "director_mail.php";
			obj.submit();
		}
	}

	function clearData() {
		var obj = document.getElementById("fmList");
		if (obj != null) {
			obj.action = "";
			obj.keyword.value = "";
			obj.submit();
		}
	}

	function synBtn() {
		var btn1 = document.getElementById("btnDel1");
		var btn2 = document.getElementById("btnDel2");
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) j++;
			lsObj[nodes[i].value] = nodes[i].checked;   // �P�B checkbox
		}
		if (btn1 != null) btn1.disabled = !(j > 0);
		if (btn2 != null) btn2.disabled = !(j > 0);
		btn1 = document.getElementById("btnMail1");
		btn2 = document.getElementById("btnMail2");
		if (btn1 != null) btn1.disabled = !(j > 0);
		if (btn2 != null) btn2.disabled = !(j > 0);
	}

	/**
	 * ��������Υ����� checkbox
	 * @version 1.0
	 **/
	function chgCheckbox() {
		var bol = false, j = 0;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == true) bol = true;
			else j++;
			lsObj[nodes[i].value] = nodes[i].checked;   // �P�B checkbox
		}
		nowSel = (j == 0);
		if (obj  != null) obj.checked = nowSel;
		if (btn1 != null) btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		synBtn();
	}

	/**
	 * �P�B����Υ��������s�P checkbox
	 * @version 1.1
	 **/
	var nowSel = false;
	function selfunc() {
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if (obj == null) return false;
		nowSel = !nowSel;
		obj.checked = nowSel;
		if (btn1 != null) btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		select_func('', obj.checked);
		synBtn();
	}

	window.onload = function () {
		var obj1 = document.getElementById("tools1");
		var obj2 = document.getElementById("tools2");
		var txt = "";
		if ((obj1 != null) && (obj2 != null)) {
			txt = obj1.innerHTML;
			txt = txt.replace("btnDel1", "btnDel2");
			txt = txt.replace("btnMail1", "btnMail2");
			obj2.innerHTML = txt.replace("btnSel1", "btnSel2");
		}
		chgCheckbox();
	};
BOF;

	if ($isDirector) {
		$js .= <<< BOF
	var MSG_SURE_DELETE  = "{$MSG['msg_sure_delete'][$sysSession->lang]}";
	var MSG_SELECT_FIRST = "{$MSG['msg_select_first'][$sysSession->lang]}";

	function delAssistant() {
		var obj = null;
		var nodes = document.getElementsByTagName("input");
		var ary = new Array();
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck") || (!nodes[i].checked)) continue;
			ary[ary.length] = nodes[i].value;
		}
		if (ary.length <= 0) {
			alert(MSG_SELECT_FIRST);
			return false;
		}
		obj = document.getElementById("fmDel");
		if (obj != null) {
			if (confirm(MSG_SURE_DELETE)) {
				obj.users.value = ary.toString();
				obj.submit();
			}
		}
	}

	function addAssistant() {
		window.location.replace("/direct/member/director_add.php");
	}
BOF;
	}

//					showXHTML_td('', $MSG['msg_help_add_assistant'][$sysSession->lang]);
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_director_list'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		$colspan = 'colspan="5"';
		showXHTML_tabFrame_B($ary, 1, 'fmList', '', 'action="" method="post" enctype="multipart/form-data" style="display: inline;"'); //, isDragable);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B($colspan);
						// showXHTML_input('hidden', 'lsList', $lsList, '', '');
						echo $MSG['msg_search1'][$sysSession->lang];
						showXHTML_input('select', 'kind', $aryUser, $kind, 'class="cssInput"');
						echo $MSG['msg_search2'][$sysSession->lang];
						$keyword = htmlspecialchars($keyword);
						$word = empty($keyword) ? $MSG['msg_search_keyword'][$sysSession->lang]: $keyword;
						showXHTML_input('text', 'keyword', $word, '', 'size="30" class="cssInput" onmouseover="this.select();this.focus();"');
						echo $MSG['msg_search3'][$sysSession->lang];
						$ary = array(
							'all'       => $MSG['role_all'][$sysSession->lang],
							'assistant' => $MSG['role_assistant'][$sysSession->lang],
							'director'  => $MSG['role_director'][$sysSession->lang]
						);
						showXHTML_input('select', 'roles', $ary, $roles, 'class="cssInput"');
						showXHTML_input('button', '', $MSG['btn_search'][$sysSession->lang], '', 'class="cssBtn" onclick="find();"');
						if (!empty($keyword)) {
							showXHTML_input('button', '', $MSG['btn_clear'][$sysSession->lang], '', 'class="cssBtn" onclick="clearData();"');
						}
					showXHTML_td_E();
				showXHTML_tr_E();
				// ½�����s (Begin)
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('id="tools1" ' . $colspan);
						if ($isDirector) {
							showXHTML_input('button', 'btnSel1', $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSel1" class="cssBtn" onclick="selfunc()"');
						}
						echo $MSG['page_no'][$sysSession->lang];
						showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);" style="width: 50px"', '', 0);
						showXHTML_input('button', 'fp', $MSG['page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'pp', $MSG['page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'np', $MSG['page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'lp', $MSG['page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						if ($isDirector) {
							echo '&nbsp;';
							showXHTML_input('button', 'ab', $MSG['btn_add_assistant'][$sysSession->lang], '', 'class="cssBtn" onclick="addAssistant();"');
							showXHTML_input('button', 'btnDel1', $MSG['btn_del_assistant'][$sysSession->lang], '', 'id="btnDel1" class="cssBtn" disabled="disabled" onclick="delAssistant();"');
							echo '&nbsp;';
							showXHTML_input('button', 'btnMail1', $MSG['btn_director_mail'][$sysSession->lang], '', 'id="btnMail1" class="cssBtn" onclick="sendMail();"');
						}
					showXHTML_td_E();
				showXHTML_tr_E();
				// ½�����s (End)
				showXHTML_tr_B('class="cssTrHead"');
					if ($isDirector) {
						showXHTML_td_B('align="center" width="20" title="' . $MSG['th_checkbox_title'][$sysSession->lang] . '"');
							showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');
							// echo $MSG['th_checkbox'][$sysSession->lang]
						showXHTML_td_E();
					}
					showXHTML_td('align="center" width="40" title="' . $MSG['th_serial'][$sysSession->lang] . '"', $MSG['th_serial'][$sysSession->lang]);
					showXHTML_td('align="center" width="260" title="' . $MSG['th_username'][$sysSession->lang] . '"', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('align="center" width="260" title="' . $MSG['th_realname'][$sysSession->lang] . '"', $MSG['th_realname'][$sysSession->lang]);
					showXHTML_td('align="center" width="170" title="' . $MSG['th_role'][$sysSession->lang] . '"', $MSG['th_role'][$sysSession->lang]);
				showXHTML_tr_E();

				// �C�X��� (Begin)
				$i = ($page_no > 0) ? ($page_no - 1) * $lines : 0;
				$end = ($page_no > 0) ? $lines : $total;
				$user = array_slice($users, $i, $end);
				foreach ($user as $key => $val) {
					$key = $val['username'];
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					$realname = divMsg(258, $aryAccount[$key]['realname']);
					// �ഫ���� int -> str
					$role = array_search($val['role']&($sysRoles['director']|$sysRoles['assistant']), $sysRoles);
					$idx = 'role_' . $role;

					showXHTML_tr_B($col);
						if ($isDirector) {
							showXHTML_td_B('align="center"');
								if ($val['role'] == $sysRoles['director']) {
									echo '&nbsp;';
								} else {
									$ck = in_array($key, $lsAry) ? ' checked="checked"' : '';
									showXHTML_input('checkbox', 'user[]', $key, '', 'onclick="chgCheckbox(); event.cancelBubble=true;"' . $ck);
								}
							showXHTML_td_E();
						}
						showXHTML_td('align="center"', ++$i);
						showXHTML_td('nowrap', $key);
						showXHTML_td('nowrap', $realname);
						showXHTML_td('nowrap', $MSG[$idx][$sysSession->lang]);
					showXHTML_tr_E();
				}
				// �C�X��� (End)

				// ½�����s (Begin)
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('id="tools2" ' . $colspan);
					showXHTML_td_E();
				showXHTML_tr_E();
				// ½�����s (End)
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';

		// ½��
		showXHTML_form_B('action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data" style="display:none"', 'fmAction');
			showXHTML_input('hidden', 'page'   , $page_no, '', '');
			showXHTML_input('hidden', 'roles'  , $roles  , '', '');
			showXHTML_input('hidden', 'kind'   , $kind   , '', '');
			showXHTML_input('hidden', 'keyword', $keyword, '', '');
			showXHTML_input('hidden', 'lsList' , $lsList , '', '');
		showXHTML_form_E('');
		// �R��
		$ticket = md5(sysTicketSeed . 'Delete_assistant' . $_COOKIE['idx'] . $sysSession->username);
		showXHTML_form_B('action="director_save.php" method="post" enctype="multipart/form-data" style="display:none"', 'fmDel');
			showXHTML_input('hidden', 'users' , '', '', '');
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
		showXHTML_form_E('');
	showXHTML_body_E();
?>

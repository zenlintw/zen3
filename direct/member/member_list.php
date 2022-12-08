<?php
	/**
	 * �����C��
	 *
	 * @since   2004/06/30
	 * @author  ShenTing Lin
	 * @version $Id: member_list.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/direct/member/member_lib.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '30010060';
	$sysSession->restore();
	if (!aclVerifyPermission(300100600, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!isset($ASSIGN_COURSE)) $ASSIGN_COURSE = false;
	$tabs_title = ($ASSIGN_COURSE) ? $MSG['tabs_assign_course'][$sysSession->lang] : $MSG['tabs_member_list'][$sysSession->lang];
// ============================================================================
	$kind    = isset($_POST['kind']) ? trim($_POST['kind']) : '';
    
	// # 47266 Chrome ��J�uTEST-STU�v�L�k�j�M
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

	// # 47266 Chrome ��J�uTEST-STU�v�L�k�j�M�A�W�[ addslashes
    $director = getClassDirector($roles, $kind, addslashes($keyword));
	$member   = getClassMember($roles, $kind, addslashes($keyword));
	// �X�ָ��
	$users = $member + $director; // ���ϥ� array_merge�A�O�]�������ѼƦr�զ��� index �|�Q���]�A�ӳo���O�ڭn��
	ksort($users, SORT_STRING);   // �j��ϥΦr��覡�ӱƧ�

	// ½��
	$lines = sysPostPerPage;
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
		obj.action = "";
		obj.submit();
	}

	/**
	 * ��ܸԲӸ��
	 **/
	function showDetail(user, val) {
		var obj = document.getElementById("fmAction");

		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.action = "member_detail.php";
		obj.user.value = user;
		obj.msgtp.value = val;
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
			obj.action = "member_mail.php";
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

	/**
	 * �P�B���s�����A
	 **/
	function synBtn() {
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) j++;
			lsObj[nodes[i].value] = nodes[i].checked;   // �P�B checkbox
		}
		
		blndisable = true;
		for(x in lsObj)
		{
			if (lsObj[x]) 
			{
				blndisable = false;
				break;
			}
		}
		
		var btn1 = document.getElementById("btnMail1"); // �����޲z - ������� - �H�H�\�त���s
		var btn2 = document.getElementById("btnMail2");
		if (btn1 != null) btn1.disabled = blndisable;
		if (btn2 != null) btn2.disabled = blndisable;
		
		var btn1 = document.getElementById("btnAssign1"); // �ǭ��׽Һ޲z - �׽ҫ��� - �D��H���\�त���s
		var btn2 = document.getElementById("btnAssign2");
		if (btn1 != null) btn1.disabled = blndisable;
		if (btn2 != null) btn2.disabled = blndisable;
	}

	/**
	 * ��������Υ����� checkbox
	 * @version 1.0
	 **/
	function chgCheckbox() {
		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
			else j++;
			lsObj[nodes[i].value] = nodes[i].checked;   // �P�B checkbox
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;
		if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		synBtn();
	}

	/**
	 * �P�B����Υ��������s�P checkbox
	 * @version 1.0
	 **/
	var nowSel = false;
	function selfunc() {
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((obj == null) || (btn1 == null) || (btn2 == null)) return false;
		nowSel = !nowSel;
		obj.checked = nowSel;
		btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		select_func('', obj.checked);
		synBtn();
	}

	window.onload = function () {
		var obj1 = document.getElementById("tools1");
		var obj2 = document.getElementById("tools2");
		var txt = "";
		if ((obj1 != null) && (obj2 != null)) {
			txt = obj1.innerHTML;
			txt = txt.replace("btnSel1" , "btnSel2");
			txt = txt.replace("btnMail1", "btnMail2");
			txt = txt.replace("btnAssign1", "btnAssign2");
			obj2.innerHTML = txt;
		}
		chgCheckbox();
	};
BOF;

	if ($ASSIGN_COURSE) {
		$js .= $enroll_js;
	}

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($tabs_title, 'tabs1');
		echo '<div align="center">';
		$colspan = 'colspan="10"';
		showXHTML_tabFrame_B($ary, 1, 'fmList', '', 'action="" method="post" enctype="multipart/form-data" style="display: inline;"'); //, isDragable);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				if ($ASSIGN_COURSE) {
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td_B($colspan);
							$ary = array(
								array($MSG['msg_step_12'][$sysSession->lang], 'help'  , 1, 'goHelp();'),
								array($MSG['msg_step_2'][$sysSession->lang] , 'member', 1),
								array($MSG['msg_step_3'][$sysSession->lang] , 'course', 1, 'assign();'),
								array($MSG['msg_step_4'][$sysSession->lang] , 'review', $objAssoc->getValues('function', 'review'), 'goReview();'),
								array($MSG['msg_step_5'][$sysSession->lang] , 'result', $objAssoc->getValues('function', 'result'))
							);
							showStep($ary, 'member');
						showXHTML_td_E();
					showXHTML_tr_E();
				}
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B($colspan);
						echo $MSG['msg_search1'][$sysSession->lang];
						showXHTML_input('select', 'kind', $aryUser, $kind, 'class="cssInput"');
						echo $MSG['msg_search2'][$sysSession->lang];
						$keyword = htmlspecialchars($keyword);
						$word = empty($keyword) ? $MSG['msg_search_keyword'][$sysSession->lang]: $keyword;
						showXHTML_input('text', 'keyword', $word, '', 'size="30" class="cssInput" onmouseover="this.select();this.focus();"');
						echo $MSG['msg_search3'][$sysSession->lang];
						$tmp = array('all' => $MSG['role_all'][$sysSession->lang]);
						$ary = array_merge($tmp, $directRoles);
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
						showXHTML_input('button', 'btnSel1' , $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSel1" class="cssBtn" onclick="selfunc()"');
						echo $MSG['page_no'][$sysSession->lang];
						showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);" style="width: 50px"', '', 0);
						showXHTML_input('button', 'fp', $MSG['page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'pp', $MSG['page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'np', $MSG['page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'lp', $MSG['page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						echo '&nbsp;';
						if ($ASSIGN_COURSE) {
							showXHTML_input('button', 'btnAssign1', $MSG['btn_assign'][$sysSession->lang], '', 'id="btnAssign1" class="cssBtn" onclick="assign();"');
						} else {
							showXHTML_input('button', 'btnMail1', $MSG['btn_mail'][$sysSession->lang], '', 'id="btnMail1" class="cssBtn" onclick="sendMail();"');
						}
					showXHTML_td_E();
				showXHTML_tr_E();
				// ½�����s (End)
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td_B('align="center" title="' . $MSG['th_checkbox_title'][$sysSession->lang] . '"');
						showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');
						// echo $MSG['th_checkbox'][$sysSession->lang]
					showXHTML_td_E();
					showXHTML_td('align="center" title="' . $MSG['th_serial'][$sysSession->lang] . '"', $MSG['th_serial'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_username'][$sysSession->lang] . '"', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_realname'][$sysSession->lang] . '"', $MSG['th_realname'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_gender'][$sysSession->lang] . '"'  , $MSG['th_gender'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_role'][$sysSession->lang] . '"'    , $MSG['th_role'][$sysSession->lang]);
					showXHTML_td('align="center" title="E-mail"', 'E-mail');
					showXHTML_td('align="center" title="' . $MSG['th_personal'][$sysSession->lang] . '"', $MSG['th_personal'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_record'][$sysSession->lang] . '"'  , $MSG['th_record'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_result'][$sysSession->lang] . '"'  , $MSG['th_result'][$sysSession->lang]);
				showXHTML_tr_E();

				// �C�X��� (Begin)
				reset($users);
				$i = ($page_no > 0) ? ($page_no - 1) * $lines : 0;
				$end = ($page_no > 0) ? $lines : $total;
				$user = array_slice($users, $i, $end);
				foreach ($user as $key => $val) {
					$key = $val['username'];
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					$realname = divMsg(120, $aryAccount[$key]['realname']);
					// �ഫ���� int -> str
					if ($val['role'] > $sysRoles['student']) $val['role'] &= ($sysRoles['director']|$sysRoles['assistant']);
					$role = array_search($val['role'], $sysRoles);
					$idx = 'role_' . $role;
					// �ϥ�
					$icon = '<img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_folder.gif" width="16" height="16" border="0" alt="' . $MSG['btn_alt_detail'][$sysSession->lang] . '" title="' . $MSG['btn_alt_detail'][$sysSession->lang] . '">';
					// $detail = '<a href="javascript:;" onclick="return false;">' . $icon . '</a>';
					// $email = showEmail($aryAccount[$key]['email']);
					$email = '<a href="mailto:' . $aryAccount[$key]['email'] . '" class="cssAnchor">' . $aryAccount[$key]['email'] . '</a>';
					if ($aryAccount[$key]['gender'] == 'F') {
						$gender = '<img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/female.gif" width="23" height="20" border="0" alt="' . $MSG['gender_female'][$sysSession->lang] . '" title="' . $MSG['gender_female'][$sysSession->lang] . '">';
					} else {
						$gender = '<img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/male.gif" width="23" height="20" border="0" alt="' . $MSG['gender_male'][$sysSession->lang] . '" title="' . $MSG['gender_male'][$sysSession->lang] . '">';
					}

					showXHTML_tr_B($col);
						showXHTML_td_B('align="center"');
							$ck = in_array($key, $lsAry) ? ' checked="checked"' : '';
							showXHTML_input('checkbox', 'user[]', $key, '', 'onclick="chgCheckbox(); event.cancelBubble=true;"' . $ck);
							// echo $MSG['th_checkbox'][$sysSession->lang]
						showXHTML_td_E();
						showXHTML_td('align="center"', ++$i);
						showXHTML_td('nowrap', $key);
						showXHTML_td('nowrap', $realname);
						showXHTML_td('align="center" nowrap', $gender);
						showXHTML_td('nowrap', $MSG[$idx][$sysSession->lang]);
						// showXHTML_td('nowrap', $aryAccount[$key][1]);
						showXHTML_td('nowrap', $email);
						$detail = '<a href="javascript:;" onclick="showDetail(\'' . $key . '\', 1); event.cancelBubble = true; return false;">' . $icon . '</a>';
						showXHTML_td('align="center" onclick="showDetail(\'' . $key . '\', 1);"', $detail);
						$detail = '<a href="javascript:;" onclick="showDetail(\'' . $key . '\', 2); event.cancelBubble = true; return false;">' . $icon . '</a>';
						showXHTML_td('align="center" onclick="showDetail(\'' . $key . '\', 2);"', $detail);
						$detail = '<a href="javascript:;" onclick="showDetail(\'' . $key . '\', 3); event.cancelBubble = true; return false;">' . $icon . '</a>';
						showXHTML_td('align="center" onclick="showDetail(\'' . $key . '\', 3);"', $detail);
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

		showXHTML_form_B('action="member_list.php" method="post" enctype="multipart/form-data" style="display:none"', 'fmAction');
    	    showXHTML_input('hidden', 'msgtp'  , ''      , '', '');
    	    showXHTML_input('hidden', 'user'   , ''      , '', '');
			showXHTML_input('hidden', 'page'   , $page_no, '', '');
			showXHTML_input('hidden', 'roles'  , $roles  , '', '');
			showXHTML_input('hidden', 'kind'   , $kind   , '', '');
			showXHTML_input('hidden', 'keyword', $keyword, '', '');
			showXHTML_input('hidden', 'lsList' , $lsList , '', '');
			if ($ASSIGN_COURSE) {
				showXHTML_input('hidden', 'Assign' , 'true', '', '');
				if (isset($_POST['wiseguy']) && (trim($_POST['wiseguy']) == 'back')) {
					showXHTML_input('hidden', 'wiseguy', 'back', '', '');
				}
			}
		showXHTML_form_E('');
	showXHTML_body_E();
?>

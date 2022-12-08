<?php
	/**
	 * �˵����Z
	 *
	 * @since   2004/07/07
	 * @author  ShenTing Lin
	 * @version $Id: member_grade.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/direct/member/member_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	$sysSession->cur_func = '1400200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

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

	function clearData() {
		var obj = document.getElementById("fmList");
		if (obj != null) {
			obj.action = "";
			obj.keyword.value = "";
			obj.submit();
		}
	}

	function synDelBtn() {
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
		synDelBtn();
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
		synDelBtn();
	}

	function showDetail(val) {
		var obj = document.getElementById("fmAction");
		if ((val == "") || (obj == null)) return false;
		obj.action = "member_grade_detail.php";
		obj.user.value = val;
		obj.submit();
	}

	window.onload = function () {
		var obj1 = document.getElementById("tools1");
		var obj2 = document.getElementById("tools2");
		var txt = "";
		if ((obj1 != null) && (obj2 != null)) {
			txt = obj1.innerHTML;
			txt = txt.replace("btnDel1", "btnDel2");
			obj2.innerHTML = txt.replace("btnSel1", "btnSel2");
		}
		chgCheckbox();
	};
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_member_grade'][$sysSession->lang], 'tabs1');
		$colspan = 'colspan="8"';
		echo '<div align="center">';
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
						echo $MSG['msg_search4'][$sysSession->lang];
						showXHTML_input('button', '', $MSG['btn_search'][$sysSession->lang], '', 'class="cssBtn" onclick="find();"');
						if (!empty($keyword)) {
							showXHTML_input('button', '', $MSG['btn_clear'][$sysSession->lang], '', 'class="cssBtn" onclick="clearData();"');
						}
					showXHTML_td_E();
				showXHTML_tr_E();
				// ½�����s (Begin)
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('id="tools1" ' . $colspan);
						// bug.751 showXHTML_input('button', 'btnSel1', $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSel1" class="cssBtn" onclick="selfunc()"');
						echo $MSG['page_no'][$sysSession->lang];
						showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);" style="width: 50px"', '', 0);
						showXHTML_input('button', 'fp', $MSG['page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'pp', $MSG['page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'np', $MSG['page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
						showXHTML_input('button', 'lp', $MSG['page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
					showXHTML_td_E();
				showXHTML_tr_E();
				// ½�����s (End)
				// ���D (Begin)
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center" width="40" title="' . $MSG['th_serial'][$sysSession->lang] . '"', $MSG['th_serial'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_username'][$sysSession->lang] . '"'    , $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_realname'][$sysSession->lang] . '"'    , $MSG['th_realname'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_enroll'][$sysSession->lang] . '"'      , $MSG['th_enroll'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_pass'][$sysSession->lang] . '"'        , $MSG['th_pass'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_not_pass'][$sysSession->lang] . '"'    , $MSG['th_not_pass'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_average'][$sysSession->lang] . '"'     , $MSG['th_average'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_score_detail'][$sysSession->lang] . '"', $MSG['th_score_detail'][$sysSession->lang]);
				showXHTML_tr_E();
				// ���D (End)
				// �C�X��� (Begin)
				reset($users);
				$i = ($page_no > 0) ? ($page_no - 1) * $lines : 0;
				for ($j = 0; $j < $i; $j++) {
					next($users);
				}
				$enroll = getClassGrade(array_keys($users));	// �N�����Z�q�j�餤���X��
				$end = ($page_no > 0) ? $lines : $total;
				for ($j = 0; $j < $end; $j++) {
					if (!(list($key, $val) = each($users))) break;
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					// 47266 Chroem ���K�B�z�b���L���S����ܧ��㪺���D
                    $username = divMsg('', $key);
					$realname = divMsg(140, $aryAccount[$key]['realname']);
					// �ϥ�
					$icon = '<img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_folder.gif" width="16" height="16" border="0" alt="' . $MSG['btn_alt_detail'][$sysSession->lang] . '" title="' . $MSG['btn_alt_detail'][$sysSession->lang] . '">';
					$detail = '<a href="javascript:;" onclick="return false;">' . $icon . '</a>';
					showXHTML_tr_B($col);
						showXHTML_td('align="center"', ++$i);
						showXHTML_td('nowrap', $username);
						showXHTML_td('nowrap', $realname);
						showXHTML_td('align="right" nowrap', $enroll[$key]['total_course']);
						showXHTML_td('align="right" nowrap', $enroll[$key]['greater']);
						showXHTML_td('align="right" nowrap', $enroll[$key]['smaller']);
						showXHTML_td('align="right" nowrap', $enroll[$key]['total_avg']);
						$enc = @mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $key, 'ecb');
						$encname = base64_encode($enc);
						showXHTML_td('align="center" nowrap onclick="showDetail(\'' . $encname . '\');"', $detail);
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
		$sb = isset($_POST['sortby']) ? trim($_POST['sortby']) : '';
		$ob = isset($_POST['order'])  ? trim($_POST['order']) : '';
		showXHTML_form_B('action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data" style="display:none"', 'fmAction');
			showXHTML_input('hidden', 'page'   , $page_no, '', '');
			showXHTML_input('hidden', 'roles'  , $roles  , '', '');
			showXHTML_input('hidden', 'kind'   , $kind   , '', '');
			showXHTML_input('hidden', 'keyword', $keyword, '', '');
			showXHTML_input('hidden', 'lsList' , $lsList , '', '');
			showXHTML_input('hidden', 'user'   , ''      , '', '');
			showXHTML_input('hidden', 'sortby' , $sb     , '', '');
			showXHTML_input('hidden', 'order'  , $ob     , '', '');
		showXHTML_form_E('');
	showXHTML_body_E();
?>

<?php
	/**
	 * 審核規則列表
	 *
	 * 建立日期：2003/04/21
	 * @author  ShenTing Lin
	 * @version $Id: review_list.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '2200100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$sqls  = 'select A.flow_serial,A.title,count(B.idx) as cnt from WM_review_syscont as A' .
	         ' left join WM_review_sysidx as B on A.flow_serial=B.flow_serial' .
	         ' group by A.flow_serial order by A.permute,A.flow_serial';
	chkSchoolId('WM_review_syscont');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$RS = $sysConn->Execute($sqls);
	$str .= "var rvCnt=new Array();\n";
	while (!$RS->EOF) {
		$temp = getCaption($RS->fields['title']);
		$tstr = $temp[$sysSession->lang];
		$str .= "rvCnt[{$RS->fields['flow_serial']}]='{$RS->fields['cnt']}\\t{$tstr}'; \n";
		$RS->MoveNext();
	}

	$js = <<< BOF
	$str
	var MSG_SELECT_DEL    = "{$MSG['msg_delete_select'][$sysSession->lang]}";
	var MSG_CONFIRM_DEL   = "{$MSG['msg_delete_sure'][$sysSession->lang]}";

	var MSG_CAN_NOT_UP    = "{$MSG['msg_not_move_up'][$sysSession->lang]}";
	var MSG_CAN_NOT_DOWN  = "{$MSG['msg_not_move_down'][$sysSession->lang]}";
	var MSG_SELECT_ALL    = "{$MSG['msg_select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['msg_select_cancel'][$sysSession->lang]}";
	var MSG_RV_CONFIRM    = "{$MSG['msg_rv_confirm'][$sysSession->lang]}";
	var MSG_RV_CONFIRM1   = "{$MSG['msg_rv_confirm1'][$sysSession->lang]}";

	/**
	 * 切換全選或全消的 checkbox
	 * @version 1.0
	 **/
	function chgCheckbox() {
		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;
		if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
	}

	/**
	 * 同步全選或全消的按鈕與 checkbox
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
	}
// ////////////////////////////////////////////////////////////////////////////
	/**
	 * 交換節點
	 * @param object node1 : 節點
	 * @param object node2 : 節點
	 **/
	function swapNode(node1, node2) {
		var pnode1 = null, pnode2 = null, tnode1 = null, tnode2 = null;
		var attr1 = null, attr2 = null;

		if ((typeof(node1) != "object") || (node1 == null)
			|| (typeof(node2) != "object") || (node2 == null))
		{
			return false;
		}
		if (isIE && (BVER == "5.0")) {
			var style1 = node1.className;
			var style2 = node2.className;
			node1.swapNode(node2);
			node1.className = style2;
			node2.className = style1;
		} else {
			pnode1 = node1.parentNode;
			pnode2 = node2.parentNode;
			tnode1 = node1.cloneNode(true);
			tnode2 = node2.cloneNode(true);
			tnode1.className = node2.className;
			tnode2.className = node1.className;
			pnode1.replaceChild(tnode2, node1);
			pnode2.replaceChild(tnode1, node2);
		}
		return true;
	}

	/**
	 * 排序
	 * @param integer val :
	 *     0 : 向上
	 *     1 : 向下
	 * @return
	 **/
	function permute(val) {
		var node1 = null, node2 = null;
		var pnode = null;
		var nid = new Array();
		var idx = 0;
		nid = getCkVal();
		if (nid.length <= 0) {
			alert("{$MSG['msg_permute_select'][$sysSession->lang]}");
			return false;
		}
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		nid = new Array();
		if (val == 0) {
			for (var i = 0; i < nodes.length; i++) {
				if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
				if (nodes[i].checked) {
					node1 = nodes[i].parentNode.parentNode;
					if (node1.rowIndex == 2) {
						alert(MSG_CAN_NOT_UP);
						return false;
					}
					nid[nid.length] = idx;
					swapNode(node1, node1.parentNode.rows[node1.rowIndex - 1]);
				}
				idx = i;
			}
		} else {
			for (var i = nodes.length - 1; i >= 0 ; i--) {
				if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
				if (nodes[i].checked) {
					node1 = nodes[i].parentNode.parentNode;
					if (node1.rowIndex == node1.parentNode.rows.length - 2) {
						alert(MSG_CAN_NOT_DOWN);
						return false;
					}
					nid[nid.length] = idx;
					swapNode(node1, node1.parentNode.rows[node1.rowIndex + 1]);
				}
				idx = i;
			}
		}
		if (isIE) {
			for (var i = 0; i < nid.length; i++) {
				nodes[nid[i]].checked = true;
			}
		}
	}

	/**
	 * 儲存順序
	 **/
	var resWin = null;
	function savePermute() {
		var obj = null;
		var nodes = document.getElementsByTagName("input");
		var ary = new Array();
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			ary[ary.length] = nodes[i].value;
		}
		obj = document.getElementById("nodeids");
		obj.value = ary.toString();
		resWin = window.open("about:blank", "resWin", "width=300,height=200,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbar=0,resizable=1");
		obj = document.getElementById("mainFm");
		obj.submit();
	}
// ////////////////////////////////////////////////////////////////////////////
	/**
	 * 取得勾選的議題編號
	 * @return array nid
	 **/
	function getCkVal() {
		var nid = new Array();
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return nid;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) {
				nid[nid.length] = nodes[i].value;
			}
		}
		return nid;
	}

	/**
	 * 設定審核規則
	 * @param string val : 規則的編號
	 **/
	function addRule(val) {
		var obj = document.getElementById("editFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.nid.value = val;
		obj.submit();
	}

	/**
	 * 刪除勾選的議題
	 **/
	function delRule() {
		var nid = new Array();
		var obj = null;
		var val = "";
		nid = getCkVal();
		if (nid.length <= 0) {
			alert(MSG_SELECT_DEL);
			return false;
		}
		obj = document.getElementById("delFm");
		val = nid.toString();
		ary = val.split(',');
		if ((ary != null) && (ary.length > 0)) {
			var msg = '';
			for (var i = 0; i < ary.length; i++) {
				if (rvCnt[ary[i]] != null && typeof(rvCnt[ary[i]]) != 'undefined'){
					sAry = rvCnt[ary[i]].split('\\t');
					if (parseInt(sAry[0]) > 0){
						temp = MSG_RV_CONFIRM;
						temp = temp.replace(/<%RV_CNT%>/ig, sAry[0]);
						msg += temp.replace(/<%RV_TITLE%>/ig, sAry[1]);
					}
				}
			}
			if (msg != ''){
				msg += MSG_RV_CONFIRM1;
			}
			if (confirm(msg+MSG_CONFIRM_DEL)){
				if ((obj != null) && (val != "")) {
					obj.nids.value = val;
					obj.submit();
				}
			}
		}
	}

	window.onload = function () {
		chkBrowser();
	};
BOF;

	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = $caption;
		return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
	}

	function showNum() {
		global $myTable;
		return $myTable->get_index();
	}

	function showSubject($serial, $subject) {
		global $sysSession;
		$lang = getCaption($subject);
		$str = '<a href="javascript:void();" class="cssAnchor" onclick="return false;">' . $lang[$sysSession->lang] . '</a>';
		$str = divMsg(670, $str, $lang[$sysSession->lang]);
		return $str;
	}

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_script('include', '/lib/common.js');
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_list'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'mainFm', '', 'action="review_permute_save.php" target="resWin" method="post" enctype="multipart/form-data" style="display:inline"');
			showXHTML_input('hidden', 'nodeids', '', '', 'id="nodeids"');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'savePermute' . $_COOKIE['idx']), '', '');

			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';

			// 工具列
			$toolbar = new toolbar();
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', 'po', $MSG['btn_new'][$sysSession->lang],   '', 'class="button01" onclick="addRule(0)"');
			$toolbar->add_input('button', 'dl', $MSG['btn_del'][$sysSession->lang],   '', 'class="button01" onclick="delRule()"');
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', 'up', '&uarr;'     ,   '', 'class="button01" onclick="permute(0)"');
			$toolbar->add_input('button', 'dw', '&darr;'   ,   '', 'class="button01" onclick="permute(1)"');
			$toolbar->add_input('button', 'sv', $MSG['btn_save_permute'][$sysSession->lang],   '', 'class="button01" onclick="savePermute()"');
			$myTable->set_def_toolbar($toolbar);

			// 排序
			$myTable->add_sort('serial', '`flow_serial` ASC', '`flow_serial` DESC');
			$myTable->add_sort('kind'  , '`kind` ASC'       , '`kind` DESC');
			$myTable->add_sort('title' , '`title` ASC'      , '`title` DESC');
			// $myTable->set_sort(true, 'user', 'asc');

			// 全選全消的按鈕
			$myTable->set_select_btn(true, 'btnSel', $MSG['msg_select_all'][$sysSession->lang], 'onclick="selfunc()"');

			// 資料
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

			$ck2 = new toolbar();
			$ck2->add_input('checkbox', 'rfid[]'  , '%flow_serial', '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
			$ck2->add_input('hidden'  , 'pmutes[]', '%permute', '', '');

			// 欄位
			$myTable->add_field($ck1                                    , $MSG['select_all_msg'][$sysSession->lang], ''      , $ck2   , ''           , 'align="center"');
			$myTable->add_field($MSG['title_serial'][$sysSession->lang] , $MSG['alt_serial'][$sysSession->lang]    , 'serial', ''     , 'showNum'    , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['title_subject'][$sysSession->lang], $MSG['alt_subject'][$sysSession->lang]   , 'title' , '%flow_serial %title', 'showSubject', 'nowrap="noWrap" onclick="addRule(%flow_serial)"');
			// $myTable->add_field($MSG['title_kind'][$sysSession->lang]   , $MSG['alt_kind'][$sysSession->lang]      , 'kind'  , '%1'   , 'showName'   , 'nowrap="noWrap"');
			// $myTable->add_field($MSG['title_action'][$sysSession->lang] , $MSG['alt_action'][$sysSession->lang]    , ''      , ''     , 'showEmail'  , 'nowrap="noWrap"');

			$myTable->set_page(true, 1, 10);

			// SQL 查詢指令
			$tab    = 'WM_review_syscont';
			$fields = '`flow_serial`, `kind`, `title`, `permute`';
			$where  = "1 order by `permute` ASC";
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();

	showXHTML_form_B('action="review_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editFm');
		showXHTML_input('hidden', 'nid', '', '', '');
		showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'setRule' . $_COOKIE['idx']), '', '');
	showXHTML_form_E();

	showXHTML_form_B('action="review_delete.php" method="post" enctype="multipart/form-data" style="display:none"', 'delFm');
		showXHTML_input('hidden', 'nids', '', '', '');
		showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'delRule' . $_COOKIE['idx']), '', '');
	showXHTML_form_E();
?>

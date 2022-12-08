<?php
	/**
	 * 課程列表
	 *
	 * @since   2004/07/14
	 * @author  ShenTing Lin
	 * @version $Id: enroll_course_list.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/direct/enroll/enroll_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/course_tree.php');
	
	$sysSession->cur_func = '1100300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 儲存勾選的人員資料 (Begin)
	$objAssoc->restore();
	$objAssoc->setValues('function', 'course', 1);
	if (isset($_POST['Assign']) && (trim($_POST['Assign']) == 'true')) {
		storeMemberData();

		// 清除資料
		$_POST['page']    = '1';
		$_POST['sortby']  = 'csid';
		$_POST['order']   = 'asc';
		$_POST['lsList']  = '';
		$_POST['keyword'] = '';
		$_POST['ticket']  = sysEncode(10000000);
	}
	$objAssoc->store();

	// 儲存勾選的人員資料 (End)
	if (isset($_POST['wiseguy']) && (trim($_POST['wiseguy']) == 'back')) {
		if (count($objAssoc->assoc_ary['course_list']) > 0) {
			$_POST['lsList']  = implode(',', $objAssoc->assoc_ary['course_list']);
		}

		$_POST['page']    = $objAssoc->getValues('course_other', 'page');
		$_POST['sortby']  = $objAssoc->getValues('course_other', 'sortby');
		$_POST['order']   = $objAssoc->getValues('course_other', 'order');
		$_POST['ticket']  = $objAssoc->getValues('course_other', 'ticket');
		$_POST['keyword'] = $objAssoc->getValues('course_other', 'keyword');
		// $_POST['course']   = implode(',', $objAssoc->assoc_ary['course']);
	}

	// 同步 checkbox (Begin)
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
	// 同步 checkbox (End)

	// 解碼 $gid
	$gid = 10000000;
	if (isset($_POST['ticket'])) {
		$enc = trim($_POST['ticket']);
		$gid = sysDecode($enc);
		$gid = intval($gid);
		$genc = '&ticket=' . $enc;
	} else if (isset($_GET['ticket'])) {
		$enc = trim($_GET['ticket']);
		$gid = sysDecode($enc);
		$gid = intval($gid);
		$genc = '&ticket=' . $enc;
	} else {
		$genc = '';
	}

	$js = <<< BOF
	var theme = "{$sysSession->theme}";
	var lang = "{$lang}";
	// 同步 checkbox
	{$lsStr}

	var MSG_SELECT_ALL    = "{$MSG['msg_select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['msg_select_cancel'][$sysSession->lang]}";
	/**
	 * 同步按鈕的狀態
	 * @version 1.1
	 * @param boolean isAll : 是否要參考 lsObj
	 * @varsion
	 **/

	var aryBtn = new Array("btnSel", "btnConfirm");
	function synBtn(isAll) {
		var btn = null, attr = null;
		var cnt = 0;
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type != "checkbox") continue;
			attr = nodes[i].getAttribute("exclude");
			if ((attr == null) || (attr == "false")) {
				if (nodes[i].checked) cnt++;
				if (typeof(lsObj) == "object") lsObj[nodes[i].value] = nodes[i].checked;   // 同步 checkbox
			}
		}
		// 同步 checkbox (Begin)
		if ((typeof(isAll) == "boolean") && isAll) {
			var ary = new Array();
			if (typeof(lsObj) == "object") {
				for (var i in lsObj) {
					if (lsObj[i]) ary[ary.length] = i;
				}
				cnt = ary.length;
			}
		}
		// 同步 checkbox (End)
		for (var i = 1; i < aryBtn.length; i++) {
			btn = document.getElementById(aryBtn[i] + "1");
			if (btn != null) btn.disabled = !(cnt > 0);
			btn = document.getElementById(aryBtn[i] + "2");
			if (btn != null) btn.disabled = !(cnt > 0);
		}
	}

	/**
	 * 切換全選或全消的 checkbox
	 * @version 1.0
	 **/
	function chgCheckbox() {
		var btn = null, attr = null;
		var bol = true;
		var cnt = 0;
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type != "checkbox") continue;
			attr = nodes[i].getAttribute("exclude");
			if ((attr == null) || (attr == "false")) {
				if (nodes[i].checked == false) bol = false;
				else cnt++;
				if (typeof(lsObj) == "object") lsObj[nodes[i].value] = nodes[i].checked;   // 同步 checkbox
			}
		}

		nowSel = bol;
		btn = document.getElementById("ck");
		if (btn  != null) btn.checked = bol;
		btn = document.getElementById("btnSel1");
		if (btn != null) btn.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		btn = document.getElementById("btnSel2");
		if (btn != null) btn.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		synBtn(true);
	}

	/**
	 * 同步全選或全消的按鈕與 checkbox
	 * @version 1.0
	 **/
	var nowSel = false;
	function selfunc() {
		var obj  = document.getElementById("ck");
		// if ((obj == null) || (btn1 == null) || (btn2 == null)) return false;
		if (obj == null) return false;
		nowSel = !nowSel;
		obj.checked = nowSel;
		obj = document.getElementById("btnSel1");
		obj.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		obj = document.getElementById("btnSel2");
		obj.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		select_func('', nowSel);
		synBtn(true);
	}
// ////////////////////////////////////////////////////////////////////////////
	function getTarget() {
		var obj = null;
		switch (this.name) {
			case "s_main": obj = parent.s_catalog; break;
			case "c_main": obj = parent.c_catalog; break;
			case "main"  : obj = parent.catalog;   break;
			case "s_catalog": obj = parent.s_main; break;
			case "c_catalog": obj = parent.c_main; break;
			case "catalog"  : obj = parent.main;   break;
		}
		return obj;
	}

	function rmUnload(kind) {
		var obj = null;
		if (typeof(kind) == "string") {
			obj = document.getElementById("actFm");
			if (obj == null) return false;
			obj.page.value = (kind == 'page') ? pg : 1;
			obj.sortby.value = sb;
			obj.order.value  = od;
			// 同步 checkbox (Begin)
			var ary = new Array();
			if (typeof(lsObj) == "object") {
				for (var i in lsObj) {
					if (lsObj[i]) ary[ary.length] = i;
				}
				obj.lsList.value = ary.toString();
			}
			// 同步 checkbox (End)
			window.onunload = function () {};
			obj.submit();
		} else {
			window.onunload = function () {};
		}
	}
// ////////////////////////////////////////////////////////////////////////////
	function confirmData(bURL) {
		var obj = document.getElementById("mainFm");
		var nodes = null;
		var ary = new Array();
		if (obj == null) return false;
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || !nodes[i].checked) continue;
			ary[ary.length] = nodes[i].value;
		}
		obj = document.getElementById("actFm");
		if (obj == null) return false;
		// obj.action = "enroll_confirm.php";
		obj.courses.value = ary.toString();
		// 同步 checkbox (Begin)
		ary = new Array();
		if (typeof(lsObj) == "object") {
			for (var i in lsObj) {
				if (lsObj[i]) ary[ary.length] = i;
			}
			obj.lsList.value = ary.toString();
		}
		// 同步 checkbox (End)
		if ((typeof(bURL) != "undefined") && (bURL != "")) {
			obj.action = bURL;
			obj.wiseguy.value = "back";
		} else {
			if (ary.length <= 0) {
				alert("{$MSG['msg_select_course_first'][$sysSession->lang]}");
				return false;
			}
			obj.action = "enroll_confirm.php";
			obj.wiseguy.value = "";
		}
		obj.submit();
	}

	function goHelp() {
		window.location.replace("enroll_help.php");
	}

	function goMember() {
		confirmData("member_list.php");
	}

	window.onload = function () {
		var obj = null;
		var str = location.pathname;
		obj = getTarget();
		if (obj != null) {
			str = str.replace("enroll_course_list.php", "enroll_group_list.php");
			if (str != obj.location.pathname) {
				obj.location.replace(str);
			}
		}

		var obj1 = document.getElementById("tools11");
		var obj2 = document.getElementById("tools21");
		var txt = "";
		if ((obj1 != null) && (obj2 != null)) {
			txt = obj1.innerHTML;
			for (var i = 0; i < aryBtn.length; i++) {
				txt = txt.replace(aryBtn[i] + "1" , aryBtn[i] + "2");
			}
			obj2.innerHTML = txt;
		}

		chgCheckbox();
	};

	window.onunload = function () {
		var obj = null;
		obj = getTarget();
		if (obj != null) {
			obj.location.href = "about:blank";
		}
	};
BOF;

	function showSubject($subject) {
		global $sysSession;
		$lang = getCaption($subject);
		$str  = divMsg(150, $lang[$sysSession->lang], $lang[$sysSession->lang]);
		return $str;
	}

	function showDatetime($val1, $val2) {
		global $sysSession, $sysConn, $MSG;

		$time1 = $sysConn->UnixTimeStamp($val1);
		$time2 = $sysConn->UnixTimeStamp($val2);

		$res  = $MSG['from2'][$sysSession->lang];
		$res .= (empty($val1)) ? $MSG['now'][$sysSession->lang] : date('Y/m/d', $time1);
		$res .= '<br>';
		$res .= $MSG['to2'][$sysSession->lang];
		$res .= (empty($val2)) ? $MSG['forever'][$sysSession->lang] : date('Y/m/d', $time2);
		//if (empty($val1) && empty($val2)) $res = $MSG['msg_unlimit'][$sysSession->lang];

		// return divMsg(130, $res, htmlspecialchars($res));
		return $res;
	}

	function showDetail($val) {
		return '&nbsp;';
	}

	function showCheckBox($val) {
		global $lsAry;
		$ck = in_array($val, $lsAry) ? ' checked="checked"' : '';
		showXHTML_input('checkbox', '', $val, '', 'onclick="chgCheckbox(); event.cancelBubble=true;"' . $ck);
	}

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['tabs_course_list'][$sysSession->lang], 'tabs1');

		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, mainFm, '', 'action="enroll_confirm.php" method="post" enctype="multipart/form-data" style="display: inline;" onsubmit="return false;"');
			showXHTML_input('hidden', 'folder_id', '', '', '');

			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';
			ob_start();
				$ary = array(
					array($MSG['msg_step_12'][$sysSession->lang], 'help'  , 1, 'goHelp();'),
					array($MSG['msg_step_2'][$sysSession->lang] , 'member', 1, 'goMember();'),
					array($MSG['msg_step_3'][$sysSession->lang] , 'course', 1),
					array($MSG['msg_step_4'][$sysSession->lang] , 'review', 1, 'confirmData()'),
					array($MSG['msg_step_5'][$sysSession->lang] , 'result', $objAssoc->getValues('function', 'result'))
				);
				showStep($ary, 'course');
				$content = ob_get_contents();
			ob_end_clean();
			$myTable->display['help_class'] = 'cssTrEvn';
			$myTable->add_help($content);

			// 工具列
			$toolbar = new toolbar();
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', 'btnConfirm1', $MSG['btn_confirm_data'][$sysSession->lang]  , '', 'id="btnConfirm1" class="cssBtn" onclick="confirmData();"');
			$myTable->set_def_toolbar($toolbar);

			// 全選全消的按鈕
			$myTable->set_select_btn(true, 'btnSel', $MSG['msg_select_all'][$sysSession->lang], 'onclick="selfunc()"');
			// 翻頁
			$myTable->display['page_func'] = 'rmUnload("page"); return true';
			// 排序
			$myTable->add_sort('csid'       , '`course_id` ASC', '`course_id` DESC');
			$myTable->add_sort('caption'    , '`caption` ASC'  , '`caption` DESC');
			$myTable->add_sort('enroll_date', '`en_begin` ASC' , '`en_begin` DESC');
			$myTable->add_sort('study_date' , '`st_begin` ASC' , '`st_begin` DESC');
			$myTable->set_sort(true, 'csid', 'asc', 'rmUnload("sort"); return true');

			// 資料
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

			$ck2 = new toolbar();
			$ck2->add_input('checkbox', 'fid[]', '%0', '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');

			$myTable->add_field($ck1    , $MSG['select_all_msg'][$sysSession->lang], ''           , '%0'   , 'showCheckBox' , 'width="20" align="center"');
			// $myTable->add_field($MSG['th_course_id_title'][$sysSession->lang]  , '', 'csid'       , '%0'   , ''             , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['th_course_name_title'][$sysSession->lang], '', 'caption'    , '%2'   , 'showSubject'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['th_enroll_date_title'][$sysSession->lang], '', 'enroll_date', '%5 %6', 'showDatetime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['th_study_date_title'][$sysSession->lang] , '', 'study_date' , '%7 %8', 'showDatetime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['th_teacher_title'][$sysSession->lang]    , '', ''           , '%3'   , 'showSubject'  , 'nowrap="noWrap"');
			// $myTable->add_field($MSG['th_detail_title'][$sysSession->lang]     , '', ''           , '%0'   , 'showDetail'   , 'align="center" nowrap="noWrap"');

			// $sysConn->debug = true;
			if ($gid <= 10000000) {
				$table  = '`WM_term_course`';
				$fields = '`WM_term_course`.*';
				$where  = '`course_id`!=10000000 AND `kind`="course" AND `status`<9 ';
			} else {
				$table  = 'WM_term_group LEFT JOIN `WM_term_course` ON `WM_term_group`.`child`=`WM_term_course`.`course_id`';
				$fields = '`WM_term_course`.*, `WM_term_group`.`child`';
				$where  = "`WM_term_group`.`parent`={$gid} AND `WM_term_course`.`course_id`!=10000000 AND `WM_term_course`.`kind`='course' ";
			}
			$myTable->set_sqls($table, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';

		$ticket = trim($_POST['ticket']);
		$lsList = trim($_POST['lsList']);
		$sortby = (isset($_POST['sortby'])) ? trim($_POST['sortby']) : 'csid';
		$order  = trim($_POST['order']);
		$page   = trim($_POST['page']);
		showXHTML_form_B('action="" method="post" enctype="multipart/form-data" style="display: none;"', 'actFm');
			showXHTML_input('hidden', 'page'   , $page   , '', '');
			showXHTML_input('hidden', 'sortby' , $sortby , '', '');
			showXHTML_input('hidden', 'order'  , $order  , '', '');
			showXHTML_input('hidden', 'ticket' , $ticket , '', '');
			showXHTML_input('hidden', 'keyword', $keyword, '', '');
			showXHTML_input('hidden', 'lsList' , $lsList , '', '');
			showXHTML_input('hidden', 'courses', ''      , '', '');
			showXHTML_input('hidden', 'wiseguy', ''      , '', '');
		showXHTML_form_E();
	showXHTML_body_E('');

?>

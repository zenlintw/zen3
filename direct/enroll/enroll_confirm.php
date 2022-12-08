<?php
	/**
	 * 確認選課狀態
	 *
	 * @since   2004/07/29
	 * @author  ShenTing Lin
	 * @version $Id: enroll_confirm.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/direct/enroll/enroll_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '1100300100';
	$sysSession->restore();
	if (!aclVerifyPermission(1100300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 儲存勾選的課程資料 (Begin)
	$objAssoc->restore();
	$objAssoc->setValues('function', 'review', 1);
	if (isset($_POST['Assign']) && (trim($_POST['Assign']) == 'true')) {
		// 儲存人員資料
		storeMemberData();
	} else if (!isset($_POST['wiseguy']) || empty($_POST['wiseguy'])) {
		// 儲存課程資料
		storeCourseData();
		// 註記
		$objAssoc->setValues('course_other', 'wiseguy', 'back');
	}
	$objAssoc->store();

	// 儲存勾選的課程資料 (End)

	$js = <<< BOF
	var ticket = "";
// ////////////////////////////////////////////////////////////////////////////
	var MSG_SELECT_COURSE = "{$MSG['msg_select_course_first'][$sysSession->lang]}";
	var MSG_SELECT_MEMBER = "{$MSG['msg_select_member_first'][$sysSession->lang]}";
	// 全選全消的訊息 (Select all and Cancel select message)
	var MSG_SELECT_ALL    = "{$MSG['btn_select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['btn_select_cancel'][$sysSession->lang]}";

	// 需要同步的按鈕，第一個保留給 checkbox，第二個保留給全選與全消按鈕 (need syn btns, first item is reserve)
	var synBtnList = {
		"common"     : ["", "btnSel"],
		 "tabMember" : ["ck1", "btnSelMember"],
		 "tabCourse" : ["ck2", "btnSelCourse"]
	};
	var ckNowSel = new Object();

	function initSynBtn() {
		var obj = null, nodes = null, attr = null;
		var cnt = 0, leng = 0, acnt = 0, aleng = 0;
		for (var i in synBtnList) {
			if (i == "common") continue;
			obj = document.getElementById(i);
			if (obj == null) continue;
			nodes = obj.getElementsByTagName("input");
			if ((nodes == null) || (nodes.length <= 0)) continue;
			cnt = 0;
			leng = 0;
			for (var j = 0; j < nodes.length; j++) {
				if (nodes[j].type != "checkbox") continue;
				attr = nodes[j].getAttribute("exclude");
				if ((attr == null) || (attr == "false")) {
					leng++;
					if (nodes[j].checked) cnt++;
					// 同步 checkbox
					if (typeof(lsObj) == "object") {
						if (typeof(lsObj[i]) != "object") lsObj[i] = new Object();
						lsObj[i][nodes[j].value] = nodes[j].checked;
					}
				}
			}

			acnt  += parseInt(cnt);
			aleng += parseInt(leng);
			ckNowSel[i] = [parseInt(leng), parseInt(cnt)];
		}
		ckNowSel["common"] = [parseInt(aleng), parseInt(acnt)];
	}

	/**
	 * 同步按鈕的狀態
	 * @version 2.0
	 * @param boolean isAll : 是否要參考 lsObj
	 * @varsion
	 **/
	function synBtn(isAll) {
		var obj = null, nodes = null, attr = null;
		var cnt = 0, leng = 0, acnt = 0, aleng = 0;
		var bol = false;
		for (var i in synBtnList) {
			if (i == "common") continue;

			aleng += parseInt(ckNowSel[i][0]);
			acnt  += parseInt(ckNowSel[i][1]);
			bol = (parseInt(ckNowSel[i][0]) == parseInt(ckNowSel[i][1]));
			obj = document.getElementById(synBtnList[i][0]);
			if (obj != null) obj.checked = bol;
			obj = document.getElementById(synBtnList[i][1]);
			if (obj != null) obj.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
			obj = document.getElementById(synBtnList[i][1] + "1");
			if (obj != null) obj.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
			obj = document.getElementById(synBtnList[i][1] + "2");
			if (obj != null) obj.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
			for (var j = 2; j < synBtnList[i].length; j++) {
				obj = document.getElementById(synBtnList[i][j]);
				if (obj != null) obj.disabled = !(ckNowSel[i][1] > 0);
				obj = document.getElementById(synBtnList[i][j] + "1");
				if (obj != null) obj.disabled = !(ckNowSel[i][1] > 0);
				obj = document.getElementById(synBtnList[i][j] + "2");
				if (obj != null) obj.disabled = !(ckNowSel[i][1] > 0);
			}
		}

		if (typeof(synBtnList["common"]) == "undefined") return false;
		bol = (parseInt(acnt) == parseInt(aleng));
		obj = document.getElementById(synBtnList["common"][0]);
		if (obj != null) obj.disabled = !(acnt > 0);
		obj = document.getElementById(synBtnList["common"][1]);
		if (obj != null) obj.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		obj = document.getElementById(synBtnList["common"][1] + "1");
		if (obj != null) obj.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		obj = document.getElementById(synBtnList["common"][1] + "2");
		if (obj != null) obj.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		for (var j = 2; j < synBtnList["common"].length; j++) {
			obj = document.getElementById(synBtnList["common"][j]);
			if (obj != null) obj.disabled = !(acnt > 0);
			obj = document.getElementById(synBtnList["common"][j] + "1");
			if (obj != null) obj.disabled = !(acnt > 0);
			obj = document.getElementById(synBtnList["common"][j] + "2");
			if (obj != null) obj.disabled = !(acnt > 0);
		}
	}
	/**
	 * 切換全選或全消的 checkbox
	 * @version 1.0
	 **/
	function chgCheckbox(obj) {
		var pnode = null;
		if ((typeof(obj) != "object") || (obj == null)) return false;
		pnode = obj.parentNode;
		while (pnode != null) {
			if (typeof(synBtnList[pnode.id]) == "undefined") {
				pnode = pnode.parentNode;
			} else {
				if (obj.checked) {
					ckNowSel[pnode.id][1]++;
				} else {
					ckNowSel[pnode.id][1]--;
				}
				if (typeof(lsObj) == "object") {
					if (typeof(lsObj[pnode.id]) != "object") lsObj[pnode.id] = new Object();
					lsObj[pnode.id][obj.value] = obj.checked;
				}
				synBtn(false);
				pnode = null;
			}
		}
	}

	/**
	 * 同步全選或全消的按鈕與 checkbox
	 * @version 2.0
	 **/
	var ckNowSel = new Object();
	function selfunc(srcObjName) {
		var obj = null;
		var bol = false;
		var cnt = 0; leng = 0;

		if ((typeof(srcObjName) == "undefined") || (srcObjName == "") || (srcObjName == "common")) {
			srcObjName = "";
			cnt = 0;
			leng = 0;
			for (var i in ckNowSel) {
				if (i == "common") continue;
				leng += ckNowSel[i][0];
				cnt  += ckNowSel[i][1];
			}
			bol = (leng == cnt);
			for (var i in ckNowSel) {
				if (i == "common") continue;
				ckNowSel[i][1] = (bol) ? 0 : ckNowSel[i][0];
			}
		} else {
			leng = ckNowSel[srcObjName][0];
			cnt  = ckNowSel[srcObjName][1];
			bol = (leng == cnt);
			ckNowSel[srcObjName][1] = (bol) ? 0 : ckNowSel[srcObjName][0];
		}


		if ((typeof(synBtnList[srcObjName]) != "undefined") && (typeof(synBtnList[srcObjName][0]) != "undefined")) {
			obj = document.getElementById(synBtnList[srcObjName][0]);
			if (obj != null) obj.checked = bol;
		}
		select_func(srcObjName, !bol);
		synBtn();
	}

	window.onload = function () {
		var obj = document.getElementById("tools1");
		var txt = "";
		if (obj != null) txt = obj.innerHTML;
		for (var i = 0; i < synBtnList["common"].length; i++) {
			if (synBtnList["common"][i] == "") continue;
			txt = txt.replace(synBtnList["common"][i] + "1" , synBtnList["common"][i] + "2");
		}
		obj = document.getElementById("tools2");
		if (obj != null) obj.innerHTML = txt;
		initSynBtn();
		synBtn();
	};
// ////////////////////////////////////////////////////////////////////////////
	function goHelp() {
		var obj = document.getElementById("wiseFm");
		if (obj != null) {
			obj.action = "enroll_help.php";
			obj.submit();
		}
	}

	function goMember() {
		var obj = document.getElementById("wiseFm");
		if (obj != null) {
			obj.wiseguy.value = "back";
			obj.action = "member_list.php";
			obj.submit();
		}
	}

	function goCourse() {
		var obj = document.getElementById("wiseFm");
		if (obj != null) {
			obj.wiseguy.value = "back";
			obj.action = "enroll_course_list.php";
			obj.submit();
		}
	}

	function goSave() {
		var obj = null;
		var cnt = 0;

		if (parseInt(ckNowSel["tabMember"][1]) <= 0) {
			 alert(MSG_SELECT_MEMBER);
			 return false;
		}

		if (parseInt(ckNowSel["tabCourse"][1]) <= 0) {
			 alert(MSG_SELECT_COURSE);
			 return false;
		}

		obj = document.getElementById("actFM");
		if (obj != null) obj.submit();
	}
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_review'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actFM', '', 'action="enroll_save.php" method="post" enctype="multipart/form-data" style="display: inline;"');
			$member = $objAssoc->assoc_ary['member_list'];
			$course = $objAssoc->assoc_ary['course_list'];
			$cnt = max(count($member), count($course));
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="1" class="cssTable"');
				// 精靈列
				$colt = ($colt == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($colt);
					showXHTML_td_B('colspan="2"');
						$ary = array(
							array($MSG['msg_step_12'][$sysSession->lang], 'help'  , 1, 'goHelp();'),
							array($MSG['msg_step_2'][$sysSession->lang] , 'member', 1, 'goMember();'),
							array($MSG['msg_step_3'][$sysSession->lang] , 'course', 1, 'goCourse();'),
							array($MSG['msg_step_4'][$sysSession->lang] , 'review', 1),
							array($MSG['msg_step_5'][$sysSession->lang] , 'result', 1, 'goSave();')
						);
						showStep($ary, 'review');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 工具列
				$colt = ($colt == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($colt);
					showXHTML_td_B('align="center" colspan="2" id="tools1"');
						showXHTML_input('button', 'btnSel1', $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSel1" class="cssBtn" onclick="selfunc();"');
						showXHTML_input('button', 'btnSmt' , $MSG['btn_submit'][$sysSession->lang]    , '', 'onclick="goSave();" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 資料
				$colt = ($colt == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($colt);
					showXHTML_td_B('valign="top"');
						// 人員
						showXHTML_table_B('width="375" border="0" cellspacing="1" cellpadding="3" id="tabMember" class="cssTable"');
							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td_B('colspan="2"');
									showXHTML_input('button', 'btnSelMember' , $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSelMember" class="cssBtn" onclick="selfunc(\'tabMember\')"');
								showXHTML_td_E();
								showXHTML_td('align="center" colspan="2"', $MSG['head_member_list'][$sysSession->lang]);
							showXHTML_tr_E();
							showXHTML_tr_B('class="cssTrHead"');
								showXHTML_td_B('width="25" align="center" nowrap title="' . $MSG['th_checkbox_title'][$sysSession->lang] . '"');
									showXHTML_input('checkbox', 'ck1', '', '', 'id="ck1" exclude="true" onclick="selfunc(\'tabMember\')" checked="checked"');
									// echo $MSG['th_checkbox'][$sysSession->lang]
								showXHTML_td_E();
								showXHTML_td('width="40" align="center" nowrap title="' . $MSG['th_serial'][$sysSession->lang] . '"', $MSG['th_serial'][$sysSession->lang]);
								showXHTML_td('align="center" nowrap title="' . $MSG['th_username'][$sysSession->lang] . '"', $MSG['th_username'][$sysSession->lang]);
								showXHTML_td('align="center" nowrap title="' . $MSG['th_realname'][$sysSession->lang] . '"', $MSG['th_realname'][$sysSession->lang]);
							showXHTML_tr_E();
							$col = '';
							$i = 0;
							foreach ($member as $key => $val) {
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									$user = getUserDetailData($val);
									showXHTML_td_B('align="center"');
										showXHTML_input('checkbox', 'member[]', $val, '', 'onclick="chgCheckbox(this);" checked="checked"');
									showXHTML_td_E();
									showXHTML_td('align="center"', ++$i);
									showXHTML_td('nowrap', divMsg(100, $val));
									showXHTML_td('nowrap', divMsg(180, $user['realname']));
								showXHTML_tr_E();
							}
							for (; $i < $cnt; $i++) {
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center"');
										showXHTML_input('checkbox', '', '', '', 'exclude="true" style="visibility: hidden;"');
									showXHTML_td_E();
									showXHTML_td('', '&nbsp;');
									showXHTML_td('', '&nbsp;');
									showXHTML_td('', '&nbsp;');
								showXHTML_tr_E();
							}
						showXHTML_table_E();

					showXHTML_td_E();
					showXHTML_td_B('valign="top"');
						// 課程
						showXHTML_table_B('width="375" border="0" cellspacing="1" cellpadding="3" id="tabCourse" class="cssTable"');
							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td_B('colspan="2"');
									showXHTML_input('button', 'btnSelCourse' , $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSelCourse" class="cssBtn" onclick="selfunc(\'tabCourse\')"');
								showXHTML_td_E();
								showXHTML_td('align="center" colspan="2"', $MSG['head_course_list'][$sysSession->lang]);
							showXHTML_tr_E();
							showXHTML_tr_B('class="cssTrHead"');
								showXHTML_td_B('width="25" align="center" nowrap title="' . $MSG['th_checkbox_title'][$sysSession->lang] . '"');
									showXHTML_input('checkbox', 'ck2', '', '', 'id="ck2" exclude="true" onclick="selfunc(\'tabCourse\')" checked="checked"');
								showXHTML_td_E();
								showXHTML_td('width="40" align="center" nowrap title="' . $MSG['th_serial'][$sysSession->lang]      . '"', $MSG['th_serial'][$sysSession->lang]);
								showXHTML_td('align="center" nowrap title="'            . $MSG['th_course_id_title'][$sysSession->lang]   . '"', $MSG['th_course_id'][$sysSession->lang]);
								showXHTML_td('align="center" nowrap title="'            . $MSG['th_course_name_title'][$sysSession->lang] . '"', $MSG['th_course_name'][$sysSession->lang]);
							showXHTML_tr_E();
							$col = '';
							$i = 0;
							$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
							$course_caption = $sysConn->GetAssoc('select course_id, caption from WM_term_course where course_id in('.implode(',', $course).')');
							foreach ($course as $key => $val) {
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									$lang = getCaption($course_caption[$val]);
									showXHTML_td_B('align="center"');
										showXHTML_input('checkbox', 'course[]', $val, '', 'onclick="chgCheckbox(this);" checked="checked"');
									showXHTML_td_E();
									showXHTML_td('align="center"', ++$i);
									showXHTML_td('', $val);
									showXHTML_td('nowrap', divMsg(210, $lang[$sysSession->lang]));
								showXHTML_tr_E();
							}
							for (; $i < $cnt; $i++) {
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center"');
										showXHTML_input('checkbox', '', '', '', 'exclude="true" style="visibility: hidden;"');
									showXHTML_td_E();
									showXHTML_td('', '&nbsp;');
									showXHTML_td('', '&nbsp;');
									showXHTML_td('', '&nbsp;');
								showXHTML_tr_E();
							}
						showXHTML_table_E();

					showXHTML_td_E();
				showXHTML_tr_E();
				// 工具列
				$colt = ($colt == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($colt);
					showXHTML_td('align="center" colspan="2" id="tools2"', ' &nbsp;');
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="" method="post" enctype="multipart/form-data" style="display: none;"', 'wiseFm');
			showXHTML_input('hidden', 'wiseguy', '', '', '');
		showXHTML_form_E();
	showXHTML_body_E();
?>

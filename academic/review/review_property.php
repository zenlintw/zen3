<?php
	/**
	 * 選課審核流程
	 *
	 * @since   2004/02/24
	 * @author  ShenTing Lin
	 * @version $Id: review_property.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/academic/review/review_init.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	if (!aclVerifyPermission(1100100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$nid    = intval($_POST['nid']);

	$ticket = md5(sysTicketSeed . 'saveRule' . $_COOKIE['idx'] . $nid);
	$rvEdit = (trim($_POST['ticket']) == $ticket);

	$ticket = md5(sysTicketSeed . 'setRule' . $_COOKIE['idx']);
	$rvAdd  = (trim($_POST['ticket']) == $ticket);

	if (!$rvAdd && !$rvEdit) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$dd = array(
		'title'  => '',       // 標題
		'desc'   => '',       // 描述
		'role'   => 'none',   // 由誰審核
		'other'  => '',       // 指定誰審核
		'assign' => array(0), // 指派給哪些課程
	);

	if (!empty($nid)) {
		if ($rvAdd) {
			$RS = dbGetStSr('WM_review_syscont', '`title`, `content`', "`flow_serial`={$nid}", ADODB_FETCH_ASSOC);
			$dd['title'] = $RS['title'];
			// 由誰審核
			$role = parseXMLRole('<manifest>' . $RS['content'] . '</manifest>');
			$role = getRole($role['account']);
			$dd['role']  = $role[0];
			$dd['other'] = $role[1];
			// 取得此規則屬於那些群組與課程
			$RS = dbGetStMr('WM_review_sysidx', '`discren_id`', "`flow_serial`={$nid}", ADODB_FETCH_ASSOC);
			while (!$RS->EOF) {
				$dd['assign'][] = $RS->fields['discren_id'];
				$RS->MoveNext();
			}
		} else if ($rvEdit) {
			$lang['Big5']        = stripslashes(trim($_POST['rvCaption_big5']));
			$lang['GB2312']      = stripslashes(trim($_POST['rvCaption_gb']));
			$lang['en']          = stripslashes(trim($_POST['rvCaption_en']));
			$lang['EUC-JP']      = stripslashes(trim($_POST['rvCaption_jp']));
			$lang['user_define'] = stripslashes(trim($_POST['rvCaption_user']));
			$dd['title']         = addslashes(serialize($lang));
			$dd['assign']        = explode(',', $_POST['rvCSGP']);
			$dd['role']          = $_POST['rvRole'];
			$dd['other']         = $_POST['rvRoleOther'];
		}
		$title = $MSG['tabs_review_edit'][$sysSession->lang];
	} else {
		$title = $MSG['tabs_review_add'][$sysSession->lang];
	}
	// 取得此規則屬於那些群組與課程
	$csSelCsID = $dd['assign'];

	$js = <<< BOF
	var lang = "{$sysSession->lang}";
	var selGpIDs = new Array(0);

	var MSG_NEED_TITLE  = "{$MSG['msg_need_title'][$sysSession->lang]}";
	var MSG_ASSIGN_ROLE = "{$MSG['msg_assign_role'][$sysSession->lang]}";
	var MSG_NEED_COURSE = "{$MSG['msg_need_course'][$sysSession->lang]}";

	/**
	 * 顯示指定人員對話框
	 * @param string val : 點選角色的代號
	 **/
	function showAssign(val) {
		var obj = document.getElementById("spanRole");
		if (obj != null) {
			if (val == "other") {
				obj.style.display = "";
				document.getElementById("rvRoleOther").focus();
			} else {
				obj.style.display = "none";
			}
		}
	}

	/**
	 * 尋找人員
	 **/
	var queryWin = null;
	function findStud() {
		if ((queryWin != null) && (!queryWin.closed)) {
			queryWin.focus();
		} else {
			queryWin = window.open("/academic/review/stud_query.php", "_blank", "width=800,height=450,top=20,left=20,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1,scrollbars=1");
		}
	}

	/**
	 * 設定點選的帳號
	 * @param string val : 帳號
	 **/
	function setRoleUser(val) {
		var obj = document.getElementById("rvRoleOther");
		if (obj != null) {
			obj.value = val;
			obj.focus();
		}
	}

	function checkData() {
		var role = "";

		var obj = document.getElementById("dataFm");
		if (obj == null) return false;

		// 檢查標題 (check title)
		if (!chk_multi_lang_input(1, true, MSG_NEED_TITLE)) return false;

		for (var i = 0; i < obj.rvRole.length; i++) {
			if (obj.rvRole[i].checked) role = obj.rvRole[i].value;
		}
		// 檢查是否為指定人選 (check assign role)
		if (role == "other") {
			if (obj.rvRoleOther.value == "") {
				alert(MSG_ASSIGN_ROLE);
				obj.rvRoleOther.focus();
				return false;
			}
		}
		obj.submit();
	}

	function gotoList() {
		window.location.replace("review_list.php");
	}

	window.onunload = function () {
		if ((queryWin != null) && (!queryWin.closed)) queryWin.close();
	};
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js, false);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($title, 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'dataFm', '', 'action="review_save.php" method="post" enctype="multipart/form-data" style="display: inline;"');
			showXHTML_input('hidden', 'rvCSGP', ''  , '', '');
			showXHTML_input('hidden', 'nid'   , $nid, '', '');
			$ticket = md5(sysTicketSeed . 'saveRule' . $_COOKIE['idx'] . $nid);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');

			showXHTML_table_B('width="760" aling="center" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

				// 標題
				$lang = old_getCaption($dd['title']);
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$arr_names = array('Big5'		=>	'rvCaption_big5',
								   'GB2312'		=>	'rvCaption_gb',
								   'en'			=>	'rvCaption_en',
								   'EUC-JP'		=>	'rvCaption_jp',
								   'user_define'=>	'rvCaption_user'
								   );
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['td_title'][$sysSession->lang]);
					showXHTML_td_B('');
						$multi_lang = new Multi_lang(false, $lang, $col); // 多語系輸入框
						$multi_lang->show(true, $arr_names);
					showXHTML_td_E();
					showXHTML_td('valign="top"', $MSG['td_title_help'][$sysSession->lang]);
				showXHTML_tr_E();
				// 由誰審核
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['td_review'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'rvRole', $roles, $dd['role'], 'onclick="showAssign(this.value);"', '<br />');
						$show = ($dd['role'] == 'other') ? '' : 'display: none; ';
						echo '<span id="spanRole" style="' . $show . 'padding: 0px 0px 0px 20px;">';
						showXHTML_input('text', 'rvRoleOther', $dd['other'], '', 'size="32" maxlength="32" id="rvRoleOther" class="cssInput"', 'separator');
						showXHTML_input('button', 'btnFind', $MSG['btn_find'][$sysSession->lang], '', 'class="cssBtn" onclick="findStud()"');
						echo '</span>';
					showXHTML_td_E();
					showXHTML_td(' valign="top"', $MSG['td_review_help'][$sysSession->lang]);
				showXHTML_tr_E();
				// 按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="3"');
						showXHTML_input('button', 'btnSave', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn" onclick="checkData()"');
						showXHTML_input('button', 'btnSave', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="gotoList()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E('');

	showXHTML_body_E();
?>

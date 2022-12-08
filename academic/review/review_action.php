<?php
	/**
	 * 審核學員
	 *
	 * @since   2004/03/15
	 * @author  ShenTing Lin
	 * @version $Id: review_action.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/editor.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '400300700';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket = md5(sysTicketSeed . 'doReviews' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
	    wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$js = <<< BOF
	var MSG_NEED_TITLE   = "{$MSG['msg_need_caption'][$sysSession->lang]}";
	var MSG_NEED_CONTENT = "{$MSG['msg_need_content'][$sysSession->lang]}";

	function chkData() {
		var obj = document.getElementById("actFm");
		if (obj == null) return false;
		if (obj.caption.value == "") {
			alert(MSG_NEED_TITLE);
			obj.caption.focus();
			return false;
		}

		if (obj.note.value == "") {
			alert(MSG_NEED_CONTENT);
			obj.note.focus();
			return false;
		}
		obj.submit();
	}

	function chgCont(val) {
		var oT1 = null, oT2 = null, obj = null, obj1 = null, obj2 = null;

		if ((typeof(editor) != "object") || (editor == null)) return false;
		obj = document.getElementById("eCaption");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		obj1 = document.getElementById("divOK");
		obj2 = document.getElementById("divDeny");
		oT1  = document.getElementById("divOKTitle");
		oT2  = document.getElementById("divDenyTitle");

		switch (val) {
			case "ok" :
				if (obj2 != null) obj2.innerHTML = editor.getHTML();
				// if (obj1 != null) editor.setHTML(obj1.innerHTML);
                $('iframe').contents().find("body").html(obj1.innerHTML);  
				if (oT2 != null) oT2.innerHTML = obj.value;
				if (oT1 != null) obj.value = oT1.innerHTML;
				break;
			case "deny" :
				if (obj1 != null) obj1.innerHTML = editor.getHTML();
				// if (obj2 != null) editor.setHTML(obj2.innerHTML);
                $('iframe').contents().find("body").html(obj2.innerHTML);
				if (oT1 != null) oT1.innerHTML = obj.value;
				if (oT2 != null) obj.value = oT2.innerHTML;
				break;
			default:
		}
	}

	function gotoList() {
		window.location.replace("review_review.php");
	}
BOF;

	$did = intval($_POST['did']);
	$RS = dbGetStSr('WM_review_flow', '*', "`idx`={$did}", ADODB_FETCH_ASSOC);

	// 取出真實姓名
	list($fn, $ln) = dbGetStSr('WM_user_account', '`first_name`, `last_name`', "`username`='{$RS['username']}'", ADODB_FETCH_NUM);
	$realname = checkRealname($fn,$ln);

	// 目前所有的課程狀態，延續 WM2 的屬性
	$CourseStatusList = array(
			5 => $MSG['param_prepare'][$sysSession->lang],
			1 => $MSG['param_open_a'][$sysSession->lang],
			2 => $MSG['param_open_a_date'][$sysSession->lang],
			3 => $MSG['param_open_n'][$sysSession->lang],
			4 => $MSG['param_open_n_date'][$sysSession->lang],
			0 => $MSG['param_close'][$sysSession->lang]
			/* 6 => iconv('Big5', 'UTF-8', '限管理員') */
		);

	// 取出修課名稱
	$CS = dbGetStSr('WM_term_course', '`caption`, `teacher`, `en_begin`, `en_end`, `st_begin`, `st_end`, `status`, `n_limit`, `a_limit`', "`course_id`={$RS['discren_id']}", ADODB_FETCH_ASSOC);
	$lang = getCaption($CS['caption']);

	// 處理課程的日期
	// 報名日期
	$en_begin = trim($CS['en_begin']);
	$en_end   = trim($CS['en_end']);
	if (!empty($en_begin)) $en_begin = $MSG['msg_cs_begin'][$sysSession->lang] . $en_begin;
	if (!empty($en_end))   $en_end   = $MSG['msg_cs_end'][$sysSession->lang] . $en_end;
	$enroll = $en_begin . ' ' . $en_end;
	// 上課日期
	$st_begin = trim($CS['st_begin']);
	$st_end   = trim($CS['st_end']);
	if (!empty($st_begin)) $st_begin = $MSG['msg_cs_begin'][$sysSession->lang] . $st_begin;
	if (!empty($st_end))   $st_end   = $MSG['msg_cs_end'][$sysSession->lang] . $st_end;
	$study = $st_begin . ' ' . $st_end;

	list($n_cnt,$a_cnt) = dbGetStSr('WM_term_major',
									'sum(if(role & ' . $sysRoles['student'] . ', 1, 0)) , sum(if(role & ' . $sysRoles['auditor'] . ', 1, 0))',
									'`course_id`=' . intval($RS['discren_id']),
									ADODB_FETCH_NUM);

	showXHTML_head_B($MSG['tabs_review_action'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/jquery/jquery.min.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_review_action'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actFm', '', 'action="review_action1.php" method="post" enctype="multipart/form-data" style="display: inline;"'); // isDragable);
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . $did . 'singledoReviews' . $_COOKIE['idx']), '', '');
			showXHTML_input('hidden', 'did', $did, '', '');
			$cols = 3;
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('', $RS['username']);
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_realname'][$sysSession->lang]);
					showXHTML_td('', $realname);
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_create_time'][$sysSession->lang]);
					showXHTML_td('', $RS['create_time']);
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_sel_course'][$sysSession->lang]);
					showXHTML_td('', $lang[$sysSession->lang]);
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_course_teacher'][$sysSession->lang]);
					showXHTML_td('', $CS['teacher'] . '&nbsp;');
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_course_enroll'][$sysSession->lang]);
					showXHTML_td('', $enroll);
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_course_study'][$sysSession->lang]);
					showXHTML_td('', $study);
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_course_state'][$sysSession->lang]);
					showXHTML_td('', $CourseStatusList[$CS['status']]);
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_course_student'][$sysSession->lang]);
					showXHTML_td('', intval($n_cnt) . ' / ' . ($CS['n_limit'] ? intval($CS['n_limit']) : $MSG['msg_unlimit'][$sysSession->lang]));
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_course_auditor'][$sysSession->lang]);
					showXHTML_td('', intval($a_cnt) . ' / ' . ($CS['a_limit'] ? intval($CS['a_limit']) : $MSG['msg_unlimit'][$sysSession->lang]));
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_review_pass'][$sysSession->lang]);
					showXHTML_td_B();
						$ary = array(
							'ok' => $MSG['msg_ok_student'][$sysSession->lang],
							// 'ok_a' => $MSG['msg_ok_auditor'][$sysSession->lang],
							'deny' => $MSG['msg_no_pass'][$sysSession->lang],
						);
                        // #47111 Chrome[管理者/課程管理/審核學員/審核] 「是否通過審核」連點同一個radiobutton兩下，內文就無法依選項切換內容。
						showXHTML_input('radio', 'pass', $ary, 'ok', 'onchange="chgCont(this.value)"', '<br />');
					showXHTML_td_E();
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				// 由何種方式傳送
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_note_type'][$sysSession->lang]);
					showXHTML_td_B();
						$ary = array(
							'mail' => 'E-mail',
							'msg'  => $MSG['msg_message'][$sysSession->lang],
							'both' => $MSG['msg_message_mail'][$sysSession->lang]
						);
						showXHTML_input('radio', 'method', $ary, 'mail', '', '');
					showXHTML_td_E();
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E();
				// 標題
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_title'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('text', 'caption', $MSG['msg_ok_title'][$sysSession->lang], '', 'size="80" id="eCaption" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('valign="top"', $MSG['th_title_help'][$sysSession->lang]);
				showXHTML_tr_E();
				// 內文
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" nowrap="NoWrap" class="cssTrHead"', $MSG['th_content'][$sysSession->lang]);
					showXHTML_td_B();
						$val = $MSG['msg_ok_text'][$sysSession->lang];
						$oEditor = new wmEditor;
						$oEditor->setValue($val);
						$oEditor->addContType('ctype', 'html');
						$oEditor->generate('note');
					showXHTML_td_E();
					showXHTML_td('valign="top"', $MSG['th_content_help'][$sysSession->lang]);
				showXHTML_tr_E();
				// 工具列
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="3"');
						showXHTML_input('button', 'btn1', $MSG['btn_submit'][$sysSession->lang], '', 'onclick="chkData()" class="cssBtn"');
						showXHTML_input('button', 'btn2', $MSG['btn_return_list'][$sysSession->lang], '', 'onclick="gotoList()" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '<div id="divOK" style="display: none">' . $MSG['msg_ok_text'][$sysSession->lang] . '</div>';
		echo '<div id="divDeny" style="display: none">' . $MSG['msg_deny_text'][$sysSession->lang] . '</div>';
		echo '<div id="divOKTitle" style="display: none">' . $MSG['msg_ok_title'][$sysSession->lang] . '</div>';
		echo '<div id="divDenyTitle" style="display: none">' . $MSG['msg_deny_title'][$sysSession->lang] . '</div>';
	showXHTML_body_E();
?>

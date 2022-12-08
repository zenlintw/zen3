<?php
	/**
	 * 寄信點名
	 *
	 * @since   2004/05/12
	 * @author  ShenTing Lin
	 * @version $Id: stud_mailto.php,v 1.1 2010/02/24 02:40:31 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
    require_once(sysDocumentRoot . '/lang/app_server_push.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '500300300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

    // app訊息推播 - Begin
    if (sysEnableAppServerPush) {
        // 啟用推播模組
        $totalTabs = 3;
    } else {
        $totalTabs = 2;
    }
	$tabs = min($totalTabs, max(1, $_GET['tabs']));
    // app訊息推播 - End

	$js = <<< BOF
	var MSG_ALL             = "{$MSG['all'][$sysSession->lang]}";
	var MSG_FORMAT_ERROR    = "{$MSG['rs_format_error'][$sysSession->lang]}";
	var MSG_NEED_DATA       = "{$MSG['rs_need_data'][$sysSession->lang]}";
	var MSG_RESULT_EMPTY    = "{$MSG['rs_no_result'][$sysSession->lang]}";
	var MSG_BTN_SEND        = "{$MSG['rs_btn_send_mail'][$sysSession->lang]}";
	var MSG_SELECT_ALL      = "{$MSG['select_all'][$sysSession->lang]}";
	var MSG_CANCEL_ALL      = "{$MSG['select_cancel'][$sysSession->lang]}";
	var MSG_progress_minute = "{$MSG['roll_minute'][$sysSession->lang]}";

	/**
	 * 同步按鈕
	 **/
	function synBtns(val) {
		var node = null, nodes = null;
		var btn1 = document.getElementById("btnSend1");
		var btn2 = document.getElementById("btnSend2");

		node = (val == '') ? document : document.getElementById(val);
		if ((typeof(node) != "object") || (node == null)) return false;
		nodes = node.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) j++;
		}
		if (btn1 != null) btn1.disabled = !(j > 0);
		if (btn2 != null) btn2.disabled = !(j > 0);
	}

	/**
	 * 切換全選或全消的 checkbox
	 * @version 1.1
	 **/
	function chgCheckbox(val) {
		var node = null, nodes = null;
		var bol = true;
		var obj  = document.getElementById("ck");

		node = (val == '') ? document : document.getElementById(val);
		if ((typeof(node) != "object") || (node == null)) return false;
		nodes = node.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;

		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");

		if (btn1 != null) btn1.value = nowSel ? MSG_CANCEL_ALL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = nowSel ? MSG_CANCEL_ALL : MSG_SELECT_ALL;

		synBtns(val);
	}

	/**
	 * 同步全選或全消的按鈕與 checkbox
	 * @version 1.0
	 **/
	var nowSel = false;
	function selfunc(val) {
		var obj  = document.getElementById("ck");
		if (obj == null) return false;
		nowSel = !nowSel;
		obj.checked = nowSel;

		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");

		if (btn1 != null) btn1.value = nowSel ? MSG_CANCEL_ALL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = nowSel ? MSG_CANCEL_ALL : MSG_SELECT_ALL;

		select_func(val, obj.checked);
		synBtns(val);
	}
// ////////////////////////////////////////////////////////////////////////////

	function chgTabs(val) {
		location.href = 'stud_mailto.php?tabs=' + val;
	}

BOF;

	showXHTML_head_B($MSG['mail_roll_call'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
        echo '<style>.bg01 {background-color: FFF;}</style>';
	showXHTML_head_E();
	showXHTML_body_B();

        // app訊息推播 - Begin
        if (sysEnableAppServerPush) {
            // 啟用推播模組
            $ary = array(
                array($MSG['mail_roll_call_manual'][$sysSession->lang],     '',  'chgTabs(1);'),
                array($MSG['mail_roll_call_system'][$sysSession->lang],     '',  'chgTabs(2);'),
                array($MSG['app_server_push'][$sysSession->lang],           '',  'chgTabs(3);')
            );
        } else {
            $ary = array(
                array($MSG['mail_roll_call_manual'][$sysSession->lang],     '',  'chgTabs(1);'),
                array($MSG['mail_roll_call_system'][$sysSession->lang],     '',  'chgTabs(2);')
            );
        }
        // app訊息推播 - End

		echo '<div align="center">';
                $display_css['table'] = 'width="1000"';
		showXHTML_tabFrame_B($ary, $tabs, 'fmMailto', null, null, null, null, $display_css);
			switch ($tabs) {
				case 1: include_once(sysDocumentRoot . '/teach/student/stud_mailto_manual.php'); break;   // 立即點名
				case 2: include_once(sysDocumentRoot . '/teach/student/stud_mailto_system.php'); break;   // 自動點名設定
                // app訊息推播 - Begin
                case 3: include_once(sysDocumentRoot . '/teach/student/app_server_push.php'); break;      // APP訊息推播
                // app訊息推播 - End
			}
		showXHTML_tabFrame_E();
		echo '</div>';
		
		showXHTML_form_B('action="stud_mailto_modify.php" method="post" enctype="multipart/form-data" style="display:none"', 'modifyFm');
			showXHTML_input('hidden', 'nid', '', '', '');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'modify' . $_COOKIE['idx']), '', '');
		showXHTML_form_E();

		showXHTML_form_B('action="stud_mailto_chgstats.php" method="post" enctype="multipart/form-data" style="display:none"', 'chgStatusFm');
			showXHTML_input('hidden', 'nids', '', '', '');
			showXHTML_input('hidden', 'func', '', '', '');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'chgStatus' . $_COOKIE['idx']), '', '');
		showXHTML_form_E();
		
	showXHTML_body_E();
?>

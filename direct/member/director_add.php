<?php
	/**
	 * 新增助教
	 *
	 * @since   2004/07/05
	 * @author  ShenTing Lin
	 * @version $Id: director_add.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/direct_member_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '300100400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$js = <<< BOF
	var MSG_NEED_DATA = "{$MSG['msg_input_user'][$sysSession->lang]}";
	function chkData() {
		var obj = document.getElementById("users");
		if (obj == null) return false;
		if (obj.value == "") {
			alert(MSG_NEED_DATA);
			return false;
		}
	}

	function go() {
		window.location.replace("/direct/member/director_list.php");
	}

	window.onload = function () {
		var obj = document.getElementById("users");
		if (obj != null) obj.focus();
	};
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_director_add'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'fmAdd', '', 'action="director_save.php" method="post" enctype="multipart/form-data" style="display: inline;" onsubmit="return chkData();"', false);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				// 內容
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td_B();
						$ticket = md5(sysTicketSeed . 'Director_add' . $_COOKIE['idx'] . $sysSession->username);
						showXHTML_input('hidden', 'ticket', $ticket, '', '');
						showXHTML_input('textarea', 'users', '', '', 'id="users" cols="55" rows="18" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('valign="top"', $MSG['msg_help_add_assistant'][$sysSession->lang]);
				showXHTML_tr_E();
				// 按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('id="tools" align="center" colspan="3"');
						showXHTML_input('submit', 'ab', $MSG['btn_add'][$sysSession->lang], '', 'class="cssBtn"');
						showXHTML_input('button', 'rb', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="go();"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

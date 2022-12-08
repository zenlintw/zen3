<?php
	/**
	 * 以其它身份登入
	 *
	 * 建立日期：2003/02/06
	 * @author  ShenTing Lin
	 * @version $Id: login.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/stud_account.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '600200100';
	$sysSession->restore();
	if (!aclVerifyPermission(600200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/* 安全性檢查 */

	$js = <<< BOF
	function checkFm() {
		var obj = document.getElementById("login");
		if (obj == null) return false;
		if (obj.username.value.search(/^[\w-_.]+$/) !== 0) {
			alert("{$MSG['title80'][$sysSession->lang]}");
			return false;
		}
	}
BOF;
	showXHTML_head_B($MSG['title79'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B();
					$ary[] = array($MSG['title81'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_form_B('action="relogin.php" method="post" style="display:inline;" onsubmit="return checkFm()"', 'login');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('', $MSG['title82'][$sysSession->lang]);
						showXHTML_tr_E();

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B();
									showXHTML_input('text', 'username', '', '', 'class="cssInput"');
							showXHTML_td_E();
						showXHTML_tr_E();

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B();
									showXHTML_input('submit', '', $MSG['confirm'][$sysSession->lang], '', 'class="cssBtn"');
									showXHTML_input('reset', '', $MSG['title83'][$sysSession->lang], '', 'class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
					showXHTML_form_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	showXHTML_body_E();
?>

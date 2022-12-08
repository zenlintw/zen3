<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/022                                                                     *
	*		work for  : 教師身份登入                                                                    *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teacher_login.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '600200100';
	$sysSession->restore();
	if (!aclVerifyPermission(600200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$js = <<< BOF
	function checkFm() {
		var obj = document.getElementById("login");
		if (obj == null) return false;
		if (obj.username.value.search(/^[\w-]+$/) !== 0) {
			alert("{$MSG['title5'][$sysSession->lang]}");
			return false;
		}

		/*
            disable submit button
        */
        var obj2 = document.getElementById("btn_submit");
        obj2.disabled = true;
	}
BOF;

	showXHTML_head_B($MSG['teacher_login_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['teacher_login_title'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_form_B('action="teach_relogin.php" method="post" style="display:inline;" onsubmit="return checkFm()"', 'login');
					$ticket = md5($sysSession->ticket . $_COOKIE['idx'] . $sysSession->school_id . $sysSession->school_name);
					showXHTML_input('hidden', 'ticket', $ticket, '', '');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
						    showXHTML_td_B('');
						        echo $MSG['title2'][$sysSession->lang];
						    showXHTML_td_E();
						showXHTML_tr_E();

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('');
									showXHTML_input('text', 'username', '', '', 'class="cssInput"');
							showXHTML_td_E();
						showXHTML_tr_E();

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B('');
									showXHTML_input('submit', '', $MSG['title3'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
									showXHTML_input('reset', '', $MSG['title4'][$sysSession->lang], '', 'class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
					showXHTML_form_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	showXHTML_body_E();
?>

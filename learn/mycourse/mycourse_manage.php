<?php
	/**
	 * 我的課程的後台管理
	 *
	 * @since   2004/09/23
	 * @author  ShenTing Lin
	 * @version $Id: mycourse_manage.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
	require_once(sysDocumentRoot . '/lang/mycourse.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	// $sysSession->cur_func='2500400200';
	// $sysSession->restore();
	if (!aclVerifyPermission(2500400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$js = <<< BOF
	var MSG_EMPTY = "{$MSG['msg_empty_mod'][$sysSession->lang]}";

	function chkSelect() {
		var node = null, nodes = null;
		var cnt = 0;
		node = document.getElementById("mymanage");
		if (node == null) return false;
		nodes = node.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].nodeType != 1) || (nodes[i].type != "checkbox")) continue;
			if (nodes[i].checked) cnt++;
		}
		if (cnt <= 0) return confirm(MSG_EMPTY);
		return true;
	}
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/learn/personal/lib.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_personal'][$sysSession->lang], 'tabsSet', 'doFunc(1)');
		$ary[] = array($MSG['tabs_tagline'][$sysSession->lang] , 'tabsTag', 'doFunc(2)');
		$ary[] = array($MSG['tabs_mycourse_manage'][$sysSession->lang], 'tabsMyCourse', '');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 3, 'mymanage', '', 'action="mycourse_manage_save.php" method="post" enctype="multipart/form-data" style="display: inline;" onsubmit="return chkSelect();"', false);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('colspan="2"', $MSG['msg_mod_enable'][$sysSession->lang]);
				showXHTML_tr_E();

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" width="100" class="cssTrHead"', $MSG['th_mod_enable'][$sysSession->lang]);
					showXHTML_td_B();
					/*
						// 登入資訊
						$visible = $myConfig->getValues('LoginInfo', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_logininfo', '', '', 'id="ck_logininfo"' . $ck);
						echo '<label for="ck_logininfo">' . $MSG['tabs_login_info'][$sysSession->lang] . '</label><br>';

						// 快速連結
						$visible = $myConfig->getValues('ShortLink', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_shortlink', '', '', 'id="ck_shortlink"' . $ck);
						echo '<label for="ck_shortlink">' . $MSG['tabs_short_link'][$sysSession->lang] . '</label><br>';
					*/
						// 我的課程
						$visible = $myConfig->getValues('MyCourse', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_mycourse', '', '', 'id="ck_mycourse"' . $ck);
						echo '<label for="ck_mycourse">' . $MSG['tabs_mycourse'][$sysSession->lang] . '</label><br>';

						// 我的辦公室
						$isTeach = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
						if ($isTeach) {
							$visible = $myConfig->getValues('MyOffice', 'visibility');
							$ck = ($visible == 'visible') ? ' checked' : '';
							showXHTML_input('checkbox', 'ck_myoffice', '', '', 'id="ck_myoffice"' . $ck);
							echo '<label for="ck_myoffice">' . $MSG['tabs_myoffice'][$sysSession->lang] . '</label><br>';
						}

						// 我的行事曆
						$visible = $myConfig->getValues('CalendarUser', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_calendaruser', '', '', 'id="ck_calendaruser"' . $ck);
						echo '<label for="ck_calendaruser">' . $MSG['tabs_calendar_user'][$sysSession->lang] . '</label><br>';

						// 課程行事曆
						$visible = $myConfig->getValues('CalendarCourse', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_calendarcourse', '', '', 'id="ck_calendarcourse"' . $ck);
						echo '<label for="ck_calendarcourse">' . $MSG['tabs_calendar_courses'][$sysSession->lang] . '</label>';
						echo $MSG['msg_cale_course'][$sysSession->lang] . '<br>';

						// 校務行事曆
						$visible = $myConfig->getValues('CalendarSchool', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_calendarschool', '', '', 'id="ck_calendarschool"' . $ck);
						echo '<label for="ck_calendarschool">' . $MSG['tabs_calendar_school'][$sysSession->lang] . '</label><br>';

						// 訊息中心
						$visible = $myConfig->getValues('MyMessageCenter', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_msgcenter', '', '', 'id="ck_msgcenter"' . $ck);
						echo '<label for="ck_msgcenter">' . $MSG['tabs_message'][$sysSession->lang] . '</label><br>';

						// 最新消息
						$visible = $myConfig->getValues('MyNews', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_news', '', '', 'id="ck_news"' . $ck);
						echo '<label for="ck_news">' . $MSG['tabs_news'][$sysSession->lang] . '</label><br>';

						// 常見問題
						$visible = $myConfig->getValues('MyFAQ', 'visibility');
						$ck = ($visible == 'visible') ? ' checked' : '';
						showXHTML_input('checkbox', 'ck_faq', '', '', 'id="ck_faq"' . $ck);
						echo '<label for="ck_faq">' . $MSG['tabs_faq'][$sysSession->lang] . '</label>';
					showXHTML_td_E();
				showXHTML_tr_E();

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="2"');
						$ticket = md5('MyCourseManage' . sysTicketSeed . $_COOKIE['idx']);
						showXHTML_input('hidden', 'ticket', $ticket, '', '');
						showXHTML_input('submit', 'btnSave' , $MSG['btn_save'][$sysSession->lang] , '', 'class="cssBtn"');
						showXHTML_input('reset' , 'btnReset', $MSG['btn_reset'][$sysSession->lang], '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

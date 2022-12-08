<?php
	/**
	 * 儲存我的課程的結果
	 *
	 * @since   2004/09/23
	 * @author  ShenTing Lin
	 * @version $Id: mycourse_manage_save.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
	require_once(sysDocumentRoot . '/lang/mycourse.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	// $sysSession->cur_func='2200400200';
	// $sysSession->restore();
	if (!aclVerifyPermission(2500400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$mapCkName = array(
//		'ck_logininfo'      => 'LoginInfo',
//		'ck_shortlink'      => 'ShortLink',
		'ck_mycourse'       => 'MyCourse',
		'ck_myoffice'       => 'MyOffice',
		'ck_calendaruser'   => 'CalendarUser',
		'ck_calendarcourse' => 'CalendarCourse',
		'ck_calendarschool' => 'CalendarSchool',
		'ck_msgcenter'      => 'MyMessageCenter',
		'ck_news'      		=> 'MyNews',
		'ck_faq'      		=> 'MyFAQ'
	);

	$magMsg = array(
		'ck_logininfo'      => 'tabs_login_info',
		'ck_shortlink'      => 'tabs_short_link',
		'ck_mycourse'       => 'tabs_mycourse',
		'ck_myoffice'       => 'tabs_myoffice',
		'ck_calendaruser'   => 'tabs_calendar_user',
		'ck_calendarcourse' => 'tabs_calendar_courses',
		'ck_calendarschool' => 'tabs_calendar_school',
		'ck_msgcenter'      => 'tabs_message',
		'ck_news'           => 'tabs_news',
		'ck_faq'            => 'tabs_faq'
	);

	$ticket = md5('MyCourseManage' . sysTicketSeed . $_COOKIE['idx']);
	if ($ticket != trim($_POST['ticket'])) {
		die($MSG['access_deny'][$sysSession->lang]);
	}


	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/learn/personal/lib.js');
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_personal'][$sysSession->lang], 'tabsSet', 'doFunc(1)');
		$ary[] = array($MSG['tabs_tagline'][$sysSession->lang] , 'tabsTag', 'doFunc(2)');
		$ary[] = array($MSG['tabs_mycourse_manage'][$sysSession->lang], 'tabsMyCourse', '');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 3);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('', $MSG['msg_manage_save_success'][$sysSession->lang]);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B();
						$isTeach = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
						foreach ($mapCkName as $key => $val) {
							if (($key == 'ck_myoffice') && (!$isTeach)) continue;
							$visible = isset($_POST[$key]) ? 'visible' : 'hidden';
							$myConfig->setValues($val, 'visibility', $visible);

							// 檢查有沒有將模組放到要顯示的位置 (Begin)
							$posH = in_array($val, $myConfig->assoc_ary['MyConfig_Head']);
							$pos1 = in_array($val, $myConfig->assoc_ary['MyConfig_Col1']);
							$pos2 = in_array($val, $myConfig->assoc_ary['MyConfig_Col2']);
							if (!($posH || $pos1 || $pos2)) {
								if (in_array($val, $MyCfg_Head)) $myConfig->setValues('MyConfig_Head', '', $val);
								if (in_array($val, $MyCfg_Col1)) $myConfig->setValues('MyConfig_Col1', '', $val);
								if (in_array($val, $MyCfg_Col2)) $myConfig->setValues('MyConfig_Col2', '', $val);
							}
							// 檢查有沒有將模組放到要顯示的位置 (End)

							echo '<div>';
							echo isset($_POST[$key]) ? $MSG['msg_visible'][$sysSession->lang] : $MSG['msg_hidden'][$sysSession->lang];
							echo $MSG[$magMsg[$key]][$sysSession->lang];
							echo '</div>';
						}
						$myConfig->store();
					showXHTML_td_E();
				showXHTML_tr_E();

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center"');
						showXHTML_input('button', 'btn_ok'    , $MSG['btn_ok'][$sysSession->lang],            '', 'onclick="doFunc(3);" class="cssBtn"');
						showXHTML_input('button', 'btn_return', $MSG['btn_return_manage'][$sysSession->lang], '', 'onclick="doFunc(4);" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

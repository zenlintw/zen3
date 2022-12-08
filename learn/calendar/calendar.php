<?php
	/**
	 * 行事曆
	 *
	 * 建立日期：2003/03/13
	 * @author  ShenTing Lin
	 * @version $Id: calendar.php,v 1.1 2010/02/24 02:39:04 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	switch ($calEnv){
		case 'academic':
			$sysSession->cur_func='2300300400';
			$ownerid = $sysSession->school_id;
			$editable = 'school';
			break;
		case 'teach':
			$sysSession->cur_func='2300200400';
			$ownerid = $sysSession->course_id;
			$editable = 'course';
			break;
		case 'direct':
			$sysSession->cur_func='2300400400';
			$ownerid = $sysSession->class_id;
			$editable = 'class';
			break;
		default:
			$sysSession->cur_func='2300100400';
			$ownerid = $sysSession->username;
			$calEnv	= 'learn';
			$editable = 'personal';
			break;
	}
	$sysSession->restore();
//	echo '<div align="right">TESTING / sysSession->username=>',$sysSession->username,' / calEnv=>',$calEnv,' / sysSession->cur_func=>',$sysSession->cur_func,'</div>';
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	setTicket();
	$ticket = md5($sysSession->username . 'Calendar' . $sysSession->ticket . $sysSession->school_id);
	$str    = date('Y-n-j', time());
	$date   = explode('-', $str);
	$date[1]--;

	$calLmt = (isset($calLmt)?$calLmt:'N');
	$colCnt = ($calLmt=='N'?6:4);

	$js = <<< BOF
	var env		= "{$calEnv}";
	var ownerid	= "{$ownerid}";
	var editable	= "{$editable}";
	var msgYear     = "{$MSG['year'][$sysSession->lang]}";
	var msgMonth    = "{$MSG['month'][$sysSession->lang]}";
	var msgDay      = "{$MSG['day'][$sysSession->lang]}";
	var msgNoMemo   = "{$MSG['no_memo'][$sysSession->lang]}";
	var msgClass    = "{$MSG['memo_class'][$sysSession->lang]}";
	var msgCourse   = "{$MSG['memo_course'][$sysSession->lang]}";
	var msgPersonal = "{$MSG['memo_personal'][$sysSession->lang]}";
	var msgSchool   = "{$MSG['memo_school'][$sysSession->lang]}";
	var msgMemoNum  = "{$MSG['memo_num'][$sysSession->lang]}";
	var msgContent  = "{$MSG['title_content'][$sysSession->lang]}";
	var msgEdit     = "{$MSG['btn_edit'][$sysSession->lang]}";
	var msgDelete   = "{$MSG['btn_delete'][$sysSession->lang]}";
	var msgFrom     = "{$MSG['from'][$sysSession->lang]}";
	var msgTo       = "{$MSG['to'][$sysSession->lang]}";
	var msgModify   = "{$MSG['tabs_modify'][$sysSession->lang]}";
	var msgAdd      = "{$MSG['tabs_add'][$sysSession->lang]}";
	var msgMemoEdit = "{$MSG['msg_modify'][$sysSession->lang]}";
	var msgMemoAdd  = "{$MSG['msg_add'][$sysSession->lang]}";
	var msgAddSucc  = "{$MSG['msg_add_success'][$sysSession->lang]}";
	var msgAddFail  = "{$MSG['msg_add_success'][$sysSession->lang]}";
	var msgUpdSucc  = "{$MSG['msg_update_success'][$sysSession->lang]}";
	var msgUpdFail  = "{$MSG['msg_update_fail'][$sysSession->lang]}";
	var msgDelSucc  = "{$MSG['msg_del_success'][$sysSession->lang]}";
	var msgDelFail  = "{$MSG['msg_del_fail'][$sysSession->lang]}";
	var msgSubject  = "{$MSG['msg_subject_fill'][$sysSession->lang]}";
	var msgSureDel  = "{$MSG['confirm_delete'][$sysSession->lang]}";
	var msgNoSetForm= "{$MSG['no_setting_form'][$sysSession->lang]}";
	var msgSetSaved = "{$MSG['setting_saved'][$sysSession->lang]}";
	var msgTimeError= "{$MSG['msg_time_error'][$sysSession->lang]}";
	var beforeAry	= new Array("{$MSG['zero_day'][$sysSession->lang]}",
								"1 {$MSG['msg_alert_before1'][$sysSession->lang]}",
								"2 {$MSG['msg_alert_before1'][$sysSession->lang]}",
								"3 {$MSG['msg_alert_before1'][$sysSession->lang]}",
								"4 {$MSG['msg_alert_before1'][$sysSession->lang]}",
								"5 {$MSG['msg_alert_before1'][$sysSession->lang]}",
								"6 {$MSG['msg_alert_before1'][$sysSession->lang]}",
								"7 {$MSG['msg_alert_before1'][$sysSession->lang]}"
								);
	var msgErrRep	= "{$MSG['msg_error_repeat'][$sysSession->lang]}";

	var ticket  = "{$ticket}";
	var orgYear = {$date[0]}, orgMonth = {$date[1]}, orgDay = {$date[2]};
	var theYear = {$date[0]}, theMonth = {$date[1]}, theDay = {$date[2]};
	var theme   = "/theme/{$sysSession->theme}/{$sysSession->env}/";
	var calLmt  = "{$calLmt}";
	var colCnt  = {$colCnt};

	window.onload = function () {
		var obj = null;
		xmlHttp = XmlHttp.create();
		xmlVars = XmlDocument.create();
		xmlDoc = XmlDocument.create();
		if (env == 'learn') {
			xmlSetting = XmlDocument.create();
			do_setting('set_load');
		}
		Today();
		obj = document.getElementById("TitleID2");
		if (obj != null) obj.style.visibility = "hidden";
		obj = document.getElementById("ImgL2");
		if (obj != null) obj.style.visibility = "hidden";
		obj = document.getElementById("ImgR2");
		if (obj != null) obj.style.visibility = "hidden";
	}
BOF;

	showXHTML_head_B($MSG['heml_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_script('include', '/learn/calendar/calendar.js');
	showXHTML_head_E('');
	showXHTML_body_B('');
		showXHTML_table_B('width="300" border="0" cellspacing="0" cellpadding="0" style="position: absolute; left: 10px; top: 8px; z-index: 10;" id="tabsTitle"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['tabs_month'][$sysSession->lang],  'tabsMonth', 'displayMemo(false)');
					$ary[] = array($MSG['tabs_memo'][$sysSession->lang],  'tabsTemp', 'displayMemo(true)');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup"');
					showXHTML_table_B('width="300" height="300" border="0" cellspacing="1" cellpadding="0" id="tabsMonth" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B('height="20" colspan="7"');
								showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
									showXHTML_tr_B('class="cssTrHead"');
										showXHTML_td_B('align="left"');
											echo $MSG['dominical_year'][$sysSession->lang];
											$end = $date[0] + 3;
											$sel = array();
											for ($i = ($date[0] - 3); $i <= $end; $i++) {
												$sel[$i] = $i;
											}
											showXHTML_input('select', 'listYear', $sel, $date[0], 'id="listYear" onchange="chgYear(this.value);" class="cssInput"');
											echo $MSG['year'][$sysSession->lang];
											$sel = array();
											for ($i = 1; $i <= 12; $i++) {
												$sel[$i] = $i;
											}
											showXHTML_input('select', 'listMonth', $sel, ($date[1] + 1), 'id="listMonth" onchange="chgMonth(this.value);" class="cssInput"');
											echo $MSG['month'][$sysSession->lang];
										showXHTML_td_E('');
										showXHTML_td_B('align="right"');
											showXHTML_input('button', '', $MSG['today'][$sysSession->lang], '', 'onclick="Today();" class="cssBtn"');
										showXHTML_td_E('');
									showXHTML_tr_E('');
								showXHTML_table_E('');
							showXHTML_td_E('');
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('height="20" align="center" class="cssCaleFont01"', $MSG['sunday'][$sysSession->lang]);
							showXHTML_td('align="center"', $MSG['monday'][$sysSession->lang]);
							showXHTML_td('align="center"', $MSG['tuesday'][$sysSession->lang]);
							showXHTML_td('align="center"', $MSG['wednesday'][$sysSession->lang]);
							showXHTML_td('align="center"', $MSG['thursday'][$sysSession->lang]);
							showXHTML_td('align="center"', $MSG['friday'][$sysSession->lang]);
							showXHTML_td('align="center" class="cssCaleFont02"', $MSG['saturday'][$sysSession->lang]);
						showXHTML_tr_E('');

					for($i=0;$i<6;$i++)
					{
						showXHTML_tr_B('height="40" class="cssTrEvn"');
							showXHTML_td('width="40" align="left" valign="top"', '&nbsp;');
							showXHTML_td('width="40" align="left" valign="top"', '&nbsp;');
							showXHTML_td('width="40" align="left" valign="top"', '&nbsp;');
							showXHTML_td('width="40" align="left" valign="top"', '&nbsp;');
							showXHTML_td('width="40" align="left" valign="top"', '&nbsp;');
							showXHTML_td('width="40" align="left" valign="top"', '&nbsp;');
							showXHTML_td('width="40" align="left" valign="top"', '&nbsp;');
						showXHTML_tr_E('');
					}

					showXHTML_table_E('');
					showXHTML_table_B('width="300" height="1" border="0" cellspacing="0" cellpadding="0" id="tabsTemp" class="cssTable" style="border-bottom-style: none; border-right-style: none; display: none;"');
						showXHTML_tr_B('');
							showXHTML_td('', '');
						showXHTML_tr_E('');
					showXHTML_table_E('');
					showXHTML_table_B('width="300" border="0" cellspacing="0" cellpadding="0" id="flag1"');
						showXHTML_tr_B('class="cssTrEvn" style="background-color:transparent"');
							showXHTML_td_B('height="20" align="right"');
								echo '<img src="/theme/'.$sysSession->theme.'/'.$sysSession->env.'/cale_personal.gif" width="12" height="14" align="absmiddle">';
								echo $MSG['flag_personal'][$sysSession->lang];
								echo '<img src="/theme/'.$sysSession->theme.'/'.$sysSession->env.'/cale_course.gif" width="12" height="14" align="absmiddle">';
								echo $MSG['flag_course'][$sysSession->lang];
								// bug 976 班級行事曆先隱藏
								// echo '<img src="/theme/'.$sysSession->theme.'/'.$sysSession->env.'/cale_class.gif" width="12" height="14" align="absmiddle">';
								// echo $MSG['flag_class'][$sysSession->lang];
								echo '<img src="/theme/'.$sysSession->theme.'/'.$sysSession->env.'/cale_school.gif" width="12" height="14" align="absmiddle">';
								echo $MSG['flag_school'][$sysSession->lang];
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		showXHTML_table_B('width="450" border="0" cellspacing="0" cellpadding="0" id="memoTable" style="position: absolute; left: 320px; top: 8px; z-index: 5;"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['tabs_memo'][$sysSession->lang],  'tabsMemo');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup"');
					showXHTML_table_B('width="450" border="0" cellspacing="1" cellpadding="3" id="tabsList" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B('colspan="'.$colCnt.'" align="right"');
								showXHTML_table_B('width="440" border="0" cellspacing="0" cellpadding="0"');
									showXHTML_tr_B('class="cssTrHead"');
										showXHTML_td_B('id="TodayCaption"');
											echo '&nbsp;';
										showXHTML_td_E('');
										showXHTML_td_B('align="right" colspan="4"');
										if ($calLmt == 'N')
										{
											showXHTML_input('button', '', $MSG['btn_add'][$sysSession->lang], '', 'onclick="do_func(\'add\')" class="cssBtn"');
											if ($calEnv == 'learn')
											showXHTML_input('button', '', $MSG['btn_advance'][$sysSession->lang], '', 'onclick="do_func(\'adv_func\')" class="cssBtn"');
										}
										showXHTML_td_E('');
									showXHTML_tr_E('');
								showXHTML_table_E('');
							showXHTML_td_E('');
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('width="20" align="center"',  $MSG['title_num'][$sysSession->lang]);
							showXHTML_td('width="20" align="center"',  $MSG['titel_type'][$sysSession->lang]);
							showXHTML_td('width="100" align="center"', $MSG['title_time'][$sysSession->lang]);
							showXHTML_td('width="260" align="center"', $MSG['title_subject'][$sysSession->lang]);
							if ($calLmt == 'N')
							{
								showXHTML_td('width="50" colspan="2" align="center"', $MSG['title_manage'][$sysSession->lang]);
							}
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('colspan="'.$colCnt.'" align="right"');
								showXHTML_table_B('width="440" border="0" cellspacing="0" cellpadding="0"');
									showXHTML_tr_B('class="cssTrEvn"');
										showXHTML_td_B('');
											showXHTML_input('checkbox', '', '', '', 'onclick="dockMemo(this.checked)"');
										showXHTML_td_E($MSG['dock_layer'][$sysSession->lang]);
										showXHTML_td_B('align="right"');
										if ($calLmt == 'N')
										{
											showXHTML_input('button', '', $MSG['btn_add'][$sysSession->lang], '', 'onclick="do_func(\'add\')" class="cssBtn"');
											if ($calEnv == 'learn')
											showXHTML_input('button', '', $MSG['btn_advance'][$sysSession->lang], '', 'onclick="do_func(\'adv_func\')" class="cssBtn"');
										}
										showXHTML_td_E('');
									showXHTML_tr_E('');
								showXHTML_table_E('');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
					showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" id="flag2" style="display:none"');
						showXHTML_tr_B('class="cssTrEvn" style="background-color:transparent"');
							showXHTML_td_B('height="20" align="right"');
								echo '<img src="/theme/'.$sysSession->theme.'/'.$sysSession->env.'/cale_personal.gif" width="12" height="14" align="absmiddle">';
								echo $MSG['flag_personal'][$sysSession->lang];
								echo '<img src="/theme/'.$sysSession->theme.'/'.$sysSession->env.'/cale_course.gif" width="12" height="14" align="absmiddle">';
								echo $MSG['flag_course'][$sysSession->lang];
								// bug 976 班級行事曆先隱藏
								// echo '<img src="/theme/'.$sysSession->theme.'/'.$sysSession->env.'/cale_class.gif" width="12" height="14" align="absmiddle">';
								// echo $MSG['flag_class'][$sysSession->lang];
								echo '<img src="/theme/'.$sysSession->theme.'/'.$sysSession->env.'/cale_school.gif" width="12" height="14" align="absmiddle">';
								echo $MSG['flag_school'][$sysSession->lang];
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		showXHTML_table_B('width="470" border="0" cellspacing="0" cellpadding="0" id="memoCycDel" onmousedown="dragLayer(\'memoCycDel\', 0, 0, 400, 30)" style="position: absolute; left: 300px; top: 80px; z-index: 16; visibility : hidden"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array('<span id="delTitle1">' . $MSG['tabs_cycdel'][$sysSession->lang] . '</span>',  'tabsDelete');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup"');
					showXHTML_form_B('method="post" enctype="multipart/form-data" style="display:inline"', 'delFm');
						showXHTML_input('hidden', 'idx', '', '', '');
					showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" id="tabsEdit" class="cssTable"');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="center" valign="top"', $MSG['delete_options'][$sysSession->lang]);
							showXHTML_td_B('width="450" nowrap="noWrap"');
							echo "<input type='radio' name='delete_choice' id='delete_choice' value='single'>{$MSG['title_repeat_single'][$sysSession->lang]}";
							echo "<input type='radio' name='delete_choice' id='delete_choice' value='all' checked>{$MSG['title_repeat_period'][$sysSession->lang]}";
							showXHTML_td_E('');
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('colspan =2 width="450" nowrap="noWrap"');
								showXHTML_input('button', '', $MSG['btn_delete'][$sysSession->lang], '', 'class="cssBtn" onclick="DelMemo(true);"');
								showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="DelMemo(false);"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
					showXHTML_form_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		showXHTML_table_B('width="470" border="0" cellspacing="0" cellpadding="0" id="memoEdit" onmousedown="dragLayer(\'memoEdit\', 0, 0, 400, 30)" style="position: absolute; left: 340px; top: 48px; z-index: 15; visibility : hidden"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array('<span id="editTitle1">' . $MSG['tabs_modify'][$sysSession->lang] . '</span>',  'tabsEdit');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup"');
					showXHTML_form_B('method="post" enctype="multipart/form-data" style="display:inline"', 'editFm');
					showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" id="tabsEdit" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B('colspan="2" id="editTitle2"');
								echo $MSG['msg_modify'][$sysSession->lang];
							showXHTML_td_E('');
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="center" valign="top"', $MSG['title_time'][$sysSession->lang]);
							showXHTML_td_B('width="450" nowrap="noWrap"');
								showXHTML_input('hidden', 'idx', '', '', '');
								$hour = array('-1' => '');
								for ($i = 0; $i <= 23; $i++) $hour[$i] = $i;

								$minute = array('-1' => '');
								for ($i = 0; $i <= 11; $i++) $minute[$i * 5] = $i * 5;

								echo $MSG['from'][$sysSession->lang];
								showXHTML_input('select', 'bhour'  , $hour  , '-1', 'class="cssInput" exclude="true"');
								echo ':';
								showXHTML_input('select', 'bminute', $minute, '-1', 'class="cssInput" exclude="true"');
								echo $MSG['to'][$sysSession->lang];
								showXHTML_input('select', 'ehour'  , $hour  , '-1', 'class="cssInput" exclude="true"');
								echo ':';
								showXHTML_input('select', 'eminute', $minute, '-1', 'class="cssInput" exclude="true"');

								echo "<br><br>\r\n{$MSG['title_repeat'][$sysSession->lang]}<br>\r\n&nbsp;";
									 $repeat_choices = Array($MSG['title_repeat_single'][$sysSession->lang],
									 $MSG['title_repeat_period'][$sysSession->lang] );
								echo "<input type='radio' name='repeat_choice' id='repeat_choice' value='0' onclick='loopLimit(0);'>{$repeat_choices[0]}<br>&nbsp;";
								echo "<input type='radio' name='repeat_choice' id='repeat_choice' value='1' onclick='loopLimit(1);'>{$repeat_choices[1]}<br>";
								//showXHTML_input('radio','repeat_choice',$repeat_choices,0,'',"<br>&nbsp;");
								echo "&nbsp;&nbsp;&nbsp;&nbsp;";
								$repeat_types = Array("day"  =>$MSG['title_repeat_day'][$sysSession->lang],
													  "week" =>$MSG['title_repeat_week'][$sysSession->lang],
													  "month"=>$MSG['title_repeat_month'][$sysSession->lang],);
								showXHTML_input('select', 'repeat_type', $repeat_types, 'day', 'id="repeat_type" onchange="loopLimit(1);" class="cssInput" exclude="true"');
								echo $MSG['title_once'][$sysSession->lang]. $MSG['until'][$sysSession->lang];

								$end = $date[0] + 3;
								$sel = array();
								for ($i = ($date[0] - 3); $i <= $end; $i++) {
									$sel[$i] = $i;
								}
								showXHTML_input('select', 'repeatEndYear', $sel, $date[0], 'id="repeatEndYear" class="cssInput" exclude="true"');
								echo $MSG['year'][$sysSession->lang];
								$sel = array();
								for ($i = 1; $i <= 12; $i++) {
									$sel[$i] = $i;
								}
								showXHTML_input('select', 'repeatEndMonth', $sel, ($date[1] + 1), 'id="repeatEndMonth" class="cssInput" exclude="true"');
								echo $MSG['month'][$sysSession->lang];
								$sel = array();
								for ($i = 1; $i <= 31; $i++) {
									$sel[$i] = $i;
								}
								showXHTML_input('select', 'repeatEndDay', $sel, $date[2], 'id="repeatEndDay" class="cssInput" exclude="true"');
								echo $MSG['day'][$sysSession->lang].$MSG['stop'][$sysSession->lang];
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="center"', $MSG['title_subject'][$sysSession->lang]);
							showXHTML_td_B('');
								showXHTML_input('text', 'subject', '', '', 'size="60" class="cssInput" onKeyPress="if (event.keyCode==13) {return false;}"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="center"', $MSG['title_content'][$sysSession->lang]);
							showXHTML_td_B('');
								showXHTML_input('textarea', 'content', '', '', 'rows="8" cols="55" class="cssInput"');
								echo '<br />';
								showXHTML_input('checkbox', 'ishtml', '', '', 'id="ishtml"');
								echo '<label for="ishtml">' . $MSG['ishtml'][$sysSession->lang] . '</label>';
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="center"', $MSG['title_alert_type'][$sysSession->lang]);
							showXHTML_td_B('');
								showXHTML_input('checkbox', 'alertTypeLogin', 'login', '', 'id="alertTypeLogin" onclick="remindType(true);"');
								echo $MSG['alert_type_login'][$sysSession->lang]."<br>\r\n";
								showXHTML_input('checkbox', 'alertTypeEmail', 'email', '', 'id="alertTypeEmail" onclick="remindType(true);"');
								echo $MSG['alert_type_email'][$sysSession->lang]."\r\n";
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="center"', $MSG['title_alert_time'][$sysSession->lang]);
							showXHTML_td_B('');
							$before = array();
							$before[0] = $MSG['zero_day'][$sysSession->lang];
							for($i=1;$i<=7;$i++)
								$before[$i]=$i.' '.$MSG['msg_alert_before1'][$sysSession->lang];

							echo '<div id="email_remind" style="visibility:hidden;">';
							showXHTML_input('select', 'before', $before, -1, 'id="before" class="cssInput"');
							showXHTML_td_E($MSG['msg_alert_before'][$sysSession->lang].'</div>');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B('colspan="2" align="center"');
								showXHTML_input('button', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn" onclick="do_func(\'save\')"');
								showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="displayEditMemo(false);"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
					showXHTML_form_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

	if ($calEnv != 'learn' && $calLmt == 'N')
	{
		showXHTML_table_B('width="300" border="0" cellspacing="0" cellpadding="0" id="calImport" style="position: absolute; top: 360px;"');
			showXHTML_tr_B('');
				showXHTML_td_B('height="20"');
					showXHTML_input('button','btnImp',$MSG['import'][$sysSession->lang].$MSG['heml_title'][$sysSession->lang],'','class="cssBtn" onclick="showDialog(\'/learn/calendar/cal_import.php?calEnv='.$calEnv.'\', true, self, true, 0, 0, \'360px\',\'180px\')"');
					echo "&nbsp;&nbsp;<a href='javascript:;' class='cssAnchor' onClick='newChgCSS(\"/learn/calendar/help{$sysSession->lang}.htm\",\"impReadMe\",600,480)'>{$MSG['csv_readme'][$sysSession->lang]}</a>";
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	}

	showXHTML_body_E('');
?>

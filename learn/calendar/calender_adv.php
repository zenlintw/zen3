<?php
	/**
	 * 登入時的 行事曆 提醒訊息
	 *
	 * @since   2004/07/21
	 * @author  Amm Lee
	 * @version $Id: calender_adv.php,v 1.1 2010/02/24 02:39:04 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_cal_alert.php');
	require_once(sysDocumentRoot . '/lang/calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	switch ($sysSession->cur_func) {
		case '2300300400':
			$calEnv = 'academic';
			break;
		case '2300200400':
			$calEnv = 'teach';
			break;
		case '2300400400':
			$calEnv = 'direct';
			break;
		default:
			$sysSession->cur_func = '2300100400';
			$sysSession->restore();
			$calEnv	= 'learn';
			break;
	}

	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$ticket = md5($sysSession->username . 'Calendar' . $sysSession->ticket . $sysSession->school_id);
	$str = date('Y-n-j', time());
	$date = explode('-', $str);
	$date[1]--;
	
	$MySettings = LoadMyCalSetting();
	list($login_alert) = dbGetStSr('WM_cal_setting','login_alert',"username='{$sysSession->username}'", ADODB_FETCH_NUM);

	$js = <<< BOF
		var ticket  = "{$ticket}";
		var orgYear = {$date[0]}, orgMonth = {$date[1]}, orgDay = {$date[2]};
		var theYear = {$date[0]}, theMonth = {$date[1]}, theDay = {$date[2]};
		var xmlHttp = null,xmlVars = null,xmlDoc = null,xmlSetting = null;

		function cal_set(){
			var xmlHttp     = XmlHttp.create();
			var xmlVars     = XmlDocument.create();
			var xmlDoc      = XmlDocument.create();
			var xmlSetting  = XmlDocument.create();
			var login_alert = '';
			// get radio value
			var inputs      = document.getElementsByTagName('input');

			for(var i = 0; i < inputs.length; i++){
				if ((inputs[i].type.toLowerCase() == 'radio') && (inputs[i].checked)){
					login_alert = inputs[i].value;
				}
			}

			// get checkbox
			var obj = document.getElementById('form_setting');

			var showArr = new Array();
			var txt  = "<manifest><ticket>" + ticket + "</ticket>";
			txt += "<action>set_save</action>";
			txt += "<calEnv>{$calEnv}</calEnv>";

			if(obj.show_course.checked) showArr[showArr.length] = "course";
			// if(obj.show_class.checked)  showArr[showArr.length] = "class";
			if(obj.show_school.checked) showArr[showArr.length] = "school";
			also_show = showArr.join(",");
			txt += "<also_show>" + also_show + "</also_show>";
			txt += "<login_alert>" + login_alert + "</login_alert></manifest>";
			xmlSetting.loadXML(txt);
			xmlHttp.open("POST", "/learn/calendar/cale_memo.php", false);
			xmlHttp.send(xmlSetting);
			xmlDoc.loadXML(xmlHttp.responseText);
			ticket = getNodeValue(xmlDoc, "ticket");
			val = getNodeValue(xmlDoc, "status");
			if (parseInt(val) > 0) alert("{$MSG['setting_saved'][$sysSession->lang]}");
			else alert("{$MSG['setting_nochange'][$sysSession->lang]}");
			opener.ticket = ticket;
			opener.do_func("month", 0);
			opener.do_func("day", 0);
			self.close();
		}

BOF;
	showXHTML_head_B($MSG['title_advance'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);

	showXHTML_head_E('');
	showXHTML_body_B('leftmargin="7" topmargin="7" ');
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title_advance'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_table_B('id ="mainTable" width="350" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_form_B('action="cal_import.php" method="post" enctype="multipart/form-data" target="_self" style="display:inline"', 'delFm');
								showXHTML_td_B('colspan="2"');
									showXHTML_input('submit','btnImp',$MSG['import'][$sysSession->lang].$MSG['heml_title'][$sysSession->lang],'','');
									echo "&nbsp;&nbsp;<a href='javascript:;' onClick='showDialog(\"help{$sysSession->lang}.htm\", true,\"impReadMe\", true, 0, 0, \"600px\",\"480px\", \"resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,scrollbars=yes\")'>{$MSG['csv_readme'][$sysSession->lang]}</a>";
								showXHTML_td_E('');
							showXHTML_form_E('');
						showXHTML_tr_E('');
						showXHTML_form_B('','form_setting');
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('width="180"');
									echo $MSG['also_show'][$sysSession->lang];
								showXHTML_td_E('');

								showXHTML_td_B('');
									$course_status = '';
									if (in_array('course',$MySettings)){
										$course_status = 'checked';
									}
									showXHTML_input('checkbox', 'show_course', '1' , '',$course_status);
									echo $MSG['memo_course'][$sysSession->lang];
									echo '<img src="/theme/' . $sysSession->theme . '/learn/cale_course.gif" width="12" height="14" align="absmiddle">';
									echo '<br>';

									/*
									echo '<div style="display=none">';	// bug 976 班級行事曆先隱藏
									$class_status = '';
									if (in_array('class',$MySettings)){
										$class_status = 'checked';
									}
									$class_status = '';						// bug 976 班級行事曆先隱藏
									showXHTML_input('checkbox', 'show_class', '1' , '',$class_status);
									echo $MSG['memo_class'][$sysSession->lang];
									echo '<img src="/theme/' . $sysSession->theme . '/learn/cale_class.gif" width="12" height="14" align="absmiddle">';
									echo '<br>';
									echo '</div>';								// bug 976 班級行事曆先隱藏
									*/
									
									$school_status = '';
									if (in_array('school',$MySettings)){
										$school_status = 'checked';
									}
									showXHTML_input('checkbox', 'show_school', '1' , '',$school_status);
									echo $MSG['memo_school'][$sysSession->lang];
									echo '<img src="/theme/' . $sysSession->theme . '/learn/cale_school.gif" width="12" height="14" align="absmiddle">';
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('width="180"');
									echo $MSG['remind_me'][$sysSession->lang];
								showXHTML_td_E('');
								showXHTML_td_B('');
									$sel = array(
                                           'Y'=>$MSG['yes'][$sysSession->lang],
                                           'N'=>$MSG['no'][$sysSession->lang]
                                           );
                                    if (empty($login_alert)){
                                    	$login_alert = 'Y';
                                	}
                                    showXHTML_input('radio','login_alert', $sel, $login_alert, '','<br>');
								showXHTML_td_E('');
							showXHTML_tr_E('');
						showXHTML_form_E('');
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('align="center" colspan="2"');
								showXHTML_input('button', 'btnSave', $MSG['btn_save'][$sysSession->lang],'','onclick="cal_set();" class="cssBtn"');
								showXHTML_input('button', '', $MSG['win_colse'][$sysSession->lang] , '', 'class="cssBtn" onclick="window.close()"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');

?>

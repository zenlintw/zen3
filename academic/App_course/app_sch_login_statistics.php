<?php
	/**
	 * 學校統計資料 - 登入次數統計 (APP專用)
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
    require_once(sysDocumentRoot . '/lang/app_sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$select_type = intval($_POST['type_report']);
	if (empty($select_type)) $select_type = 1;

	$week_year = intval($_POST['week_year']);
	if (empty($week_year)) $week_year = date('Y');

	$week_year1 = intval($_POST['week_year1']);
	if (empty($week_year1)) $week_year1 = date('Y');


	$js = <<< EOF

	function check_data(){
		var obj = document.getElementById('queryFm');
		var temp = '';

		if (obj.type_report[0].checked){
		    if (obj.single_day.value == '')
		    {
		        alert("{$MSG['date_required'][$sysSession->lang]}");
		        return false;
			}
		}
		else if (obj.type_report[1].checked){
			// 結束日要大於開始日
			val1 = obj.daily_from_date.value.replace(/[\D]/ig, '');
			val2 = obj.daily_over_date.value.replace(/[\D]/ig, '');
			if ((val1.length==0) || (val2.length==0)) {
				alert("{$MSG['title171'][$sysSession->lang]}");
				return false;
			}
			if (parseInt(val1) >= parseInt(val2)) {
				alert("{$MSG['title170'][$sysSession->lang]}");
				obj.daily_from_date.click();
				return false;
			}
		}
		else if (obj.type_report[2].checked){
			// 結束日要大於開始日
			val1 = obj.en_begin_date.value.replace(/[\D]/ig, '');
			val2 = obj.en_end_date.value.replace(/[\D]/ig, '');
			if ((val1.length==0) || (val2.length==0)) {
				alert("{$MSG['title171'][$sysSession->lang]}");
				return false;
			}
			if (parseInt(val1) >= parseInt(val2)) {
				alert("{$MSG['title170'][$sysSession->lang]}");
				obj.en_begin_date.click();
				return false;
			}
		}

		obj.action = 'app_sch_login_statistics1.php';
		return true;
		// obj.submit();
	}

	// 秀日曆的函數
	function Calendar_setup(ifd, fmt, btn, shtime) {
		Calendar.setup({
			inputField  : ifd,
			ifFormat    : fmt,
			showsTime   : shtime,
			time24      : true,
			button      : btn,
			singleClick : true,
			weekNumbers : false,
			step        : 1
		});
	}
EOF;

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_script('include', 'app_sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('align="center" width="600" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['tab_app_login'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
				showXHTML_form_B('action="" method="post" enctype="multipart/form-data" target="_self" onsubmit="return check_data();" style="display:inline"', 'queryFm');

					showXHTML_table_B('id ="mainTable" width="600" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('width="80"', $MSG['title35'][$sysSession->lang]);
							showXHTML_td_B('width="520"');

							// 單日報表
							showXHTML_input('radio', 'type_report', array(1 => $MSG['title36'][$sysSession->lang]), $select_type);
							showXHTML_input('text', 'single_day', '', '', 'id="single_day" readonly="readonly" class="cssInput"');
							echo '<br>';

							/* 日報表  add by wiseguy for TODO#1269 */
							showXHTML_input('radio', 'type_report', array(5 => $MSG['daily_report_colon'][$sysSession->lang]), $select_type);
							echo $MSG['from'][$sysSession->lang];
							showXHTML_input('text', 'daily_from_date', '', '', 'id="daily_from_date" readonly="readonly" class="cssInput"');
							echo $MSG['to'][$sysSession->lang];
							showXHTML_input('text', 'daily_over_date', '', '', 'id="daily_over_date" readonly="readonly" class="cssInput"');
                            echo '<br>';

                            // 周報表
                            showXHTML_input('radio', 'type_report', array(2 => $MSG['title8'][$sysSession->lang]), $select_type);
							echo $MSG['from'][$sysSession->lang];
							showXHTML_input('text', 'en_begin_date', '', '', 'id="en_begin_date" readonly="readonly" class="cssInput"');
							echo $MSG['to'][$sysSession->lang];
							showXHTML_input('text', 'en_end_date', '', '', 'id="en_end_date" readonly="readonly" class="cssInput"');

						    echo '<br>';
						    // 月報表
						    showXHTML_input('radio', 'type_report', array(3 => $MSG['title9'][$sysSession->lang]), $select_type);
							echo $MSG['from'][$sysSession->lang];

							// 年
							$val = 2004;	$this_year = intval(date('Y'));
						    for($j=$val; $j<=$this_year; $j++) $Y[$j]=$j;
						    echo $MSG['year1'][$sysSession->lang];
						    showXHTML_input('select', 'month_year', $Y, $this_year, 'class="cssInput" id="month_year" ');
						    echo $MSG['year'][$sysSession->lang];

							// 月
							for($j=1; $j<=12; $j++) $M[$j]=$j;
						    showXHTML_input('select', 'month', $M, 1, 'class="cssInput" id="month" ');
						    echo $MSG['month'][$sysSession->lang];

							echo '&nbsp;' , $MSG['to'][$sysSession->lang];

						    // 年1
						    echo $MSG['year1'][$sysSession->lang];
						    showXHTML_input('select', 'month_year1', $Y, $this_year, 'class="cssInput" id="month_year1" ');
						    echo $MSG['year'][$sysSession->lang];

							// 月
						    echo $MSG['month1'][$sysSession->lang];
						    showXHTML_input('select', 'month1', $M, 1, 'class="cssInput" id="month1" ');
						    echo $MSG['month'][$sysSession->lang] , $MSG['title37'][$sysSession->lang], '<br>';

							// 年報表
							showXHTML_input('radio', 'type_report', array(4 => $MSG['title10'][$sysSession->lang]), $select_type);
							echo $MSG['from'][$sysSession->lang];

							// 年
						    echo $MSG['year1'][$sysSession->lang];
						    showXHTML_input('select', 'year_year', $Y, $this_year, 'class="cssInput" id="year_year" ');
						    echo $MSG['year'][$sysSession->lang], '&nbsp;', $MSG['to'][$sysSession->lang];

						    // 年1
						    echo $MSG['year1'][$sysSession->lang];
						    showXHTML_input('select', 'year_year1', $Y, $this_year, 'class="cssInput" id="year_year1" ');
						    echo $MSG['year'][$sysSession->lang] . $MSG['title37'][$sysSession->lang];

							showXHTML_td_E('');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="2" align="center"');
								showXHTML_input('submit','btnImp',$MSG['title11'][$sysSession->lang],'','');
								showXHTML_input('reset','btnImp',$MSG['title22'][$sysSession->lang],'','');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
					showXHTML_form_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');

?>

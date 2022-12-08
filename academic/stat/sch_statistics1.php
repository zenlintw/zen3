<?php
	/**
	 * 學校統計資料 - 課程統計
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_statistics1.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/Week2YearMonthDay.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$js = <<< EOF

		// Export data  (dialog)
		function displayDialog(name,sel_type){
			var obj             = document.getElementById(name);

			// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel (pixel)
			obj.style.left      = document.body.scrollLeft + document.body.offsetWidth - 460;
			// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel (pixel)
			obj.style.top       = document.body.scrollTop  + 30;
			obj.style.display   = '';
			var obj2            = document.exportForm;
			obj2.sel_type.value = sel_type;
		}

		function hiddenDialog(name){
			document.getElementById(name).style.display = 'none';
		}

		function checkExport(){
			var csv_content = '',htm_content = '',xml_content = '',col='';
			var obj2        = document.getElementById('exportForm');
			var obj         = document.getElementById('mainTable');
			var total_rows  = 0;

			if (obj2.sel_type.value == 'today'){
				total_rows = obj.rows.length - 1;
			}else{
				total_rows = obj.rows.length -2;
			}

			for (var i = 0; i < total_rows; i++) {
				col = col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				if (i == 0){
					csv_content += obj.rows[i].cells[0].innerHTML + "<br>";
					htm_content += '<tr '+ col + '><td colspan="2">'+ obj.rows[i].cells[0].innerHTML+ "</td><tr>";
					xml_content += '<summary>'+obj.rows[i].cells[0].innerHTML + '</summary>';
				}else if (obj2.sel_type.value == 'today'){
					csv_content += obj.rows[i].cells[0].innerHTML + ',' + "<br>";
					htm_content += '<tr '+ col + '><td>'+ obj.rows[i].cells[0].innerHTML + '</td></tr>';
					xml_content += '<record>'+obj.rows[i].cells[0].innerHTML + '</record>';
				}else {
					csv_content += obj.rows[i].cells[0].innerHTML + ',' + obj.rows[i].cells[1].innerHTML + "<br>";
					htm_content += '<tr '+ col + '><td>'+ obj.rows[i].cells[0].innerHTML + '</td><td>'+obj.rows[i].cells[1].innerHTML + "</td><tr>";
					xml_content += '<record><date_range>'+obj.rows[i].cells[0].innerHTML + '</date_range><data>' + obj.rows[i].cells[1].innerHTML + "</data></record>";
				}
			}

			var obj2 = document.getElementById('exportForm');
			var nodes = obj2.getElementsByTagName('input');
			var sel_count = 0;
			for (var i = 0 ; i < nodes.length ; i++){
				if ((nodes[i].type == "checkbox") && (nodes[i].checked)){
					switch (nodes[i].value){
						case 'csv':
							obj2.csv_content.value = csv_content;
							sel_count++;
							break;
						case 'htm':
							obj2.htm_content.value = htm_content;
							sel_count++;
							break;
						case 'xml':
							obj2.xml_content.value = xml_content;
							sel_count++;
							break;
					}
				}
			}

			if (sel_count > 0){
				hiddenDialog('exportTable');
				obj2.submit();
				document.getElementById('btn_export').disabled = false;
			}
			else {
				alert("{$MSG['no_select_data_format'][$sysSession->lang]}");
			}
		}
EOF;

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('width="450" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title2'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_table_B('id ="mainTable" width="450" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						switch ($_POST['type_report']){
							case 1:		// 目前正在上課中的課程
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('');
										$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
										chkSchoolId('WM_term_course');
										$cour = $sysConn->GetCol('select count(B.username) from WM_term_course as A left join WM_term_major as B on A.course_id = B.course_id ' .
											             		 'where A.course_id > 10000000 and A.kind = "course" and ' .
														         '(A.st_begin is NULL || A.st_begin = "0000-00-00" || A.st_begin <= CURDATE())  and ' .
														         '(A.st_end is NULL || A.st_end = "0000-00-00" || A.st_end >= CURDATE())  ' .
											                     'and A.status in (1,2,3,4) group by A.course_id' );

										echo str_replace(array('%NOW%', '%TOTAL_COURSE%', '%TOTAL_PEOPLE%'),
										                 array(date("Y-m-d"), count($cour), array_sum($cour)),
										                 $MSG['title13'][$sysSession->lang]);
									showXHTML_td_E('');
								showXHTML_tr_E('');

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center"');
										showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true;displayDialog(\'exportTable\',\'today\'); " id="btn_export"');
								break;
							case 2:		// 週報表
								$begin = explode('-',$_POST['en_begin_date']);
								$end   = explode('-',$_POST['en_end_date']);
								$temp  = intval(strftime("%U",mktime(0,0,0,$begin[1],$begin[2],$begin[0])));
								$temp1 = intval(strftime("%U",mktime(0,0,0,$end[1],$end[2],$end[0])));
								$temp_year  = $begin[0];
								$temp_year1 = $end[0];

								$show_week = array();
								if ($temp_year == $temp_year1){
									for ($i = $temp;$i<=$temp1;$i++){
										$show_week[$temp_year . '-' . $i] = 0;
									}
								}else{
									for($i = $temp_year;$i <= $temp_year1;$i++){
										$temp_week = intval(strftime("%U",mktime(0,0,0,12,31,$i)));
										if ($i == $temp_year1){
											for ($j = 1;$j<=$temp1;$j++){
												$show_week[$temp_year1 . '-' . $j] = 0;
											}
										}else if ($i == $temp_year){
											$temp_week = intval(strftime("%U",mktime(0,0,0,12,31,$i)));
											for ($j = $temp;$j<=$temp_week;$j++){
												$show_week[$i . '-' . $j] = 0;
											}
										}else{
											$temp_week = intval(strftime("%U",mktime(0,0,0,12,31,$i)));
											for ($j = 1;$j<=$temp_week;$j++){
												$show_week[$i . '-' . $j] = 0;
											}
										}
									}
								}
								$show_week[$MSG['title172'][$sysSession->lang]] = 0;

								// from 的 年月日
								$w_temp = Week2YearMonthDay($temp_year,$temp);
								$w_date = date("Y-m-d",$w_temp[0]);

								// to 的 年月日
								$w_temp  = '';
								$w_temp1 = '';

								$w_temp  = Week2YearMonthDay($temp_year1,$temp1);

								$w_date1 = date("Y-m-d",$w_temp[6]);

								// 使用者輸入的日期
								$choice_date = $w_date . '~' . $w_date1;

								$rs = dbGetStMr('WM_term_course', 'st_begin', 'course_id > 10000000 and kind = "course" and ' .
								                                  			  '(st_begin is NULL || st_begin = "0000-00-00" || st_begin between "'.$w_date.'" and "'.$w_date1.'") and '.
								                                              'status not in (0,5,9)', ADODB_FETCH_ASSOC);
								if ($rs) while($row = $rs->FetchRow()) {
									if (empty($row['st_begin']))
										$show_week[$MSG['title172'][$sysSession->lang]]++;
									else {
										$real_w_array = explode('-',$row['st_begin']);
										$month        = ((substr($real_w_array[1],0,1) == '0') ? substr($real_w_array[1],1,2) : $real_w_array[1]);
										$date         = ((substr($real_w_array[2],0,1) == '0') ? substr($real_w_array[2],1,2) : $real_w_array[2]);
										$real_week    = intval(strftime("%U",mktime(0,0,0,$month,$date,$real_w_array[0])));
										if ($real_week == 0){
											$w_year = intval($real_w_array[0]) - 1;
											$real_year = $w_year . '-' . intval(strftime("%U",mktime(0,0,0,12,31,$w_year)));
										}else{
											$real_year = $real_w_array[0] . '-' . $real_week;
										}
										$show_week[$real_year]++;
									}
								}

								$w_temp = mktime(0,0,0,1,1,2004);
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" colspan="2"');
										echo $MSG['from'][$sysSession->lang], $w_date, $MSG['to'][$sysSession->lang], $w_date1;
										echo str_replace(array('%TOTAL_COURSE%', '%TYPE_KIND%'),
										                 array('<font color="red">' . array_sum($show_week) . '</font>', $MSG['week'][$sysSession->lang]),
										                 $MSG['title23'][$sysSession->lang]);
									showXHTML_td_E('');
								showXHTML_tr_E('');

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="center"', $MSG['week'][$sysSession->lang]);
									showXHTML_td('align="center"', $MSG['title16'][$sysSession->lang]);
								showXHTML_tr_E('');

								// 最大值
								$max_value = max($show_week);
								foreach ($show_week as $key => $value){
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

									showXHTML_tr_B($col);
										showXHTML_td_B('align="left" width="230"');

											// 判斷日期的 是否有 - , 否的話則是 "未設定上課日期"
											if (substr_count($key,'-') > 0){
												// from 的年月日
												$w_temp  = '';
												$temp2   = explode('-',$key);
												$w_temp  = Week2YearMonthDay(intval($temp2[0]),intval($temp2[1]));
												$w_temp1 = date("Y-m-d",$w_temp[0]);

												// to 的年月日
												$w_temp3 = date("Y-m-d",$w_temp[6]);

												// 年-月-日
												 $x_scale[] = $w_temp1 . '~' . $w_temp3 . ' ';

												echo $w_temp1 . '~' . $w_temp3;
											}else{
												 $x_scale[] = "show_lang_msg";
												echo $key;
											}
										showXHTML_td_E('');
										showXHTML_td('align="left"', $value);
									showXHTML_tr_E('');

									// 開幾門課
									$y_scale[] = $value;

								}

								if (count($x_scale) > 0){
									$str_x_scale = implode(',',$x_scale);
								}
								if (count($y_scale) > 0){
									$str_y_scale = implode(',',$y_scale);
								}

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="right" ', $MSG['title17'][$sysSession->lang]);
									showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'cour_week_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
								showXHTML_tr_E('');

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" colspan="2"');
										showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true; displayDialog(\'exportTable\',\'week\');" id="btn_export"');
								break;
							case 3:		// 月報表
								$show_month = array();
								$temp_year  = intval($_POST['month_year']);
								$temp_year1 = intval($_POST['month_year1']);
								$temp       = intval($_POST['month']);
								$temp1      = intval($_POST['month1']);
								if ($temp_year > $temp_year1) {
									$temp_year  = intval($_POST['month_year1']);
									$temp_year1 = intval($_POST['month_year']);
									$temp       = intval($_POST['month1']);
									$temp1      = intval($_POST['month']);
								}

								for ($j = $temp_year; $j <= $temp_year1; $j++) {
									for ($i = ($j == $temp_year ? $temp : 1); $i <= ($j == $temp_year1 ? $temp1 : 12); $i++) {
										$idx = sprintf('%d-%02d', $j, $i);
										$show_month[$idx] = 0;
									}
								}
								$show_month[$MSG['title172'][$sysSession->lang]] = 0;

								// TO 的 年月日
								$w_temp = mktime(0,0,0,01,01,$_POST['month_year1']);

								$w_date = date('Y-m-d',strtotime("+" . intval($_POST['month1']) . "  month last day",$w_temp));

								// 使用者輸入的日期
								$choice_date = $_POST['month_year'] . '-' . ((strlen($_POST['month']) == 1)? '0' . $_POST['month']: $_POST['month']) . "-01" . '~' . $w_date;

								$rs = dbGetStMr('WM_term_course', 'st_begin', 'course_id > 10000000 and kind = "course" and ' .
								                                  '(st_begin is NULL || st_begin = "0000-00-00" || st_begin between "' . $_POST['month_year'] . '-' . ((strlen($_POST['month']) == 1)? '0' . $_POST['month']: $_POST['month']) . '-01" and "'.$w_date.'") and '.
								                                  'status not in (0,5,9)', ADODB_FETCH_ASSOC);
								if ($rs) while($row = $rs->FetchRow()) {
									if (empty($row['st_begin']))
										$show_month[$MSG['title172'][$sysSession->lang]]++;
									else {
										$temp_month = explode('-',$row['st_begin']);
										$year_month = date('Y-m',mktime (0,0,0,$temp_month[1],$temp_month[2],$temp_month[0]));
										$show_month[$year_month]++;
									}
								}

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" colspan="2"');
										echo $MSG['from'][$sysSession->lang];
										echo $_POST['month_year'] . '-' .  $_POST['month'];
										echo $MSG['to'][$sysSession->lang];
										echo $_POST['month_year1'] . '-' .  $_POST['month1'];
										echo str_replace(array('%TOTAL_COURSE%', '%TYPE_KIND%'),
										                 array('<font color="red">' . array_sum($show_month) . '</font>', $MSG['month'][$sysSession->lang]),
										                 $MSG['title23'][$sysSession->lang]);
									showXHTML_td_E('');
								showXHTML_tr_E('');

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="center"', $MSG['month'][$sysSession->lang]);
									showXHTML_td('align="center"', $MSG['title16'][$sysSession->lang]);
								showXHTML_tr_E('');

								// 最大值
								$max_value = max($show_month);
								foreach ($show_month as $key => $value){
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

									showXHTML_tr_B($col);
										showXHTML_td('align="left"', $key);
										showXHTML_td('align="left"', $value);
									showXHTML_tr_E('');
									// 年-月

									if (substr_count($key,'-') > 0){
										$x_scale[] = $key . ' ';
									}else{
										$x_scale[] = "show_lang_msg";
									}
									// 開課數
									$y_scale[] = $value;
								}

								if (count($x_scale) > 0){
									$str_x_scale = implode(',',$x_scale);
								}
								if (count($y_scale) > 0){
									$str_y_scale = implode(',',$y_scale);
								}

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="right"', $MSG['title17'][$sysSession->lang]);
									showXHTML_td('align="left" ', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'cour_month_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
								showXHTML_tr_E('');

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" colspan="2"');
										showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'',' onclick="this.disabled=true; displayDialog(\'exportTable\',\'month\');" id="btn_export"');
								break;
							case 4:		// 年報表
								$show_year = array();

								$temp_year  = min(intval($_POST['year_year']), intval($_POST['year_year1']));
							    $temp_year1 = max(intval($_POST['year_year']), intval($_POST['year_year1']));

							    // 使用者輸入的日期
								$choice_date = $temp_year . '-01-01 ~ ' . $temp_year1 . '-12-31';

							    for($i = $temp_year;$i <= $temp_year1;$i++)
									$show_year[$i] = 0;
								$show_year[$MSG['title172'][$sysSession->lang]] = 0;

								$rs = dbGetStMr('WM_term_course', 'st_begin', 'course_id > 10000000 and kind = "course" and ' .
								                                  '(st_begin is NULL || st_begin = "0000-00-00" || st_begin between "'.$temp_year.'-01-01" and "'.$temp_year1.'-12-31") and '.
								                                  'status not in (0,5,9)', ADODB_FETCH_ASSOC);
								if ($rs) while($row = $rs->FetchRow()) {
									if (empty($row['st_begin']))
										$show_year[$MSG['title172'][$sysSession->lang]]++;
									else {
										$temp_month = explode('-',$row['st_begin']);
										$show_year[$temp_month[0]]++;
									}
								}

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" colspan="2"');
										echo $MSG['from'][$sysSession->lang];
										echo $_POST['year_year'];
										echo $MSG['to'][$sysSession->lang];
										echo $_POST['year_year1'];
										echo str_replace(array('%TOTAL_COURSE%', '%TYPE_KIND%'),
								              			 array('<font color="red">' . array_sum($show_year) . '</font>', $MSG['year'][$sysSession->lang]),
								              			 $MSG['title23'][$sysSession->lang]);
									showXHTML_td_E('');
								showXHTML_tr_E('');

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center"');
										echo $MSG['year'][$sysSession->lang];
									showXHTML_td_E('');

									showXHTML_td_B('align="center"');
										echo $MSG['title16'][$sysSession->lang];
									showXHTML_td_E('');
								showXHTML_tr_E('');

								// 最大值
								$max_value = max($show_year);
								foreach ($show_year as $key => $value){
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

									showXHTML_tr_B($col);
										showXHTML_td('align="left"', $key);
										showXHTML_td('align="left"', $value);
									showXHTML_tr_E('');
									// 年-月
									if (is_numeric($key)){
										$x_scale[] = $key . ' ';
									}else{
										$x_scale[] = 'show_lang_msg';
									}
									// 開課數
									$y_scale[] = $value;
								}

								if (count($x_scale) > 0){
									$str_x_scale = implode(',',$x_scale);
								}
								if (count($y_scale) > 0){
									$str_y_scale = implode(',',$y_scale);
								}

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="right" ', $MSG['title17'][$sysSession->lang]);
									showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'cour_year_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
								showXHTML_tr_E('');

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" colspan="2"');
										showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true; displayDialog(\'exportTable\',\'year\');" id="btn_export"');
								break;

						}
							showXHTML_input('button','btnImp',$MSG['print'][$sysSession->lang],'','onclick="javascript:window.print();"');
							showXHTML_input('button','btnImp',$MSG['title14'][$sysSession->lang],'','onclick="do_fun(1);"');
						showXHTML_td_E('');
					showXHTML_tr_E('');
				showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		//  圖表
    	showXHTML_form_B('action="" method="post" target="viewGraphWin" enctype="multipart/form-data" style="display:none"', 'GraphFm');
	    	showXHTML_input('hidden', 'x_scale', '', '', '');
	    	showXHTML_input('hidden', 'y_scale', '', '', '');
	    	showXHTML_input('hidden', 'max_val', '', '', '');
	    	showXHTML_input('hidden', 'period_date', '', '', '');
    	showXHTML_form_E();

		// 匯出
		$ary = array(array($MSG['title19'][$sysSession->lang], '', ''));
		showXHTML_tabFrame_B($ary, 1, 'exportForm', 'exportTable', 'action="sch_cour_export.php" method="POST" style="display: inline" target="empty"', true);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');

		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td('', $MSG['title20'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('checkboxes', 'type_kinds[]', array('csv' => 'Excel (.csv)',
		        													'htm' => 'HTML table (.htm)',
		        													'xml' => 'XML (.xml)'), array('csv'), '', '<br>');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('', $MSG['title21'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('text', 'download_name', 'cour_stat.zip', '', 'maxlength="60" size="40" class="box02"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td_B('colspan="2" align="center"');
		      	showXHTML_input('hidden', 'sel_type', '', '', '');
		        showXHTML_input('hidden', 'csv_content', '', '', '');
		  		showXHTML_input('hidden', 'htm_content', '', '', '');
    	  		showXHTML_input('hidden', 'xml_content', '', '', '');
    	  		showXHTML_input('hidden', 'function_name', $MSG['title2'][$sysSession->lang], '', '');
    	  		showXHTML_input('hidden', 'adv_file', 'cour_stat', '', '');
		        showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="checkExport();"');
		        showXHTML_input('button', '', $MSG['title22'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'exportTable\'); document.getElementById(\'btn_export\').disabled = false;"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		  showXHTML_table_E();

	showXHTML_body_E('');

?>

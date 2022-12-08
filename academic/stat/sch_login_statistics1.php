<?php
	/**
	 * 學校統計資料 - 登入次數統計
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_login_statistics1.php,v 1.1 2010/02/24 02:38:43 saly Exp $
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

	if (isset($_POST['state_sel']) && !in_array($_POST['state_sel'], array('>','<','=','>=','<=','!='))) die('Incorrect select_sel.');

	$sqls = 'select B.username,B.log_time ' .
			' from WM_log_others as B  ' .
			' join '.sysDBname.'.WM_all_account as A ' .
			' on A.username = B.username ' .
			' where B.function_id=600100100 and B.result_id = 0 ';

	$cond_count = count($_POST['login']);
	chkSchoolId('WM_user_account');

	$ary = array('single_day', 'daily_from_date', 'daily_over_date', 'en_begin_date', 'en_end_date');
	foreach ($ary as $val)
	{
		$_POST[$val] = trim($_POST[$val]);
		if (!empty($_POST[$val]))
		{
			$date = preg_split('/\D+/', $_POST[$val], -1, PREG_SPLIT_NO_EMPTY);
			$_POST[$val] = sprintf('%04d-%02d-%02d', $date[0], $date[1], $date[2]);
		}
	}

	for ($i=0;$i< $cond_count;$i++){
		switch ($_POST['login'][$i]){
			case 1:		// 男的或女的
				switch ($_POST['gender_sel']){
					case 1:		// 男生
						$cond = " and A.gender='M'";
						break;
					case 2:		// 女生
						$cond = " and A.gender='F'";
						break;
				}
				break;
			case 2:		// 哪個年齡層
				// 今年
				$this_year          = intval(date('Y'));
				$_POST['year_old']  = intval($_POST['year_old']);
				$_POST['year_old1'] = intval($_POST['year_old1']);
				$y1                 = min($_POST['year_old'], $_POST['year_old1']);
				$y2                 = max($_POST['year_old'], $_POST['year_old1']);

				$from_year          = ($this_year - $y1) . '-12-31';
				$to_year            = ($this_year - $y2) . '-01-01';
				$cond              .= ' and A.birthday between "' . $to_year . '" and "' . $from_year . '"';

				break;
			case 4:		// 來自哪個位址
				$cond .= ' and B.remote_address like "%' . escape_LIKE_query_str(trim($_POST['login_ip'])) . '%"';
				break;
		}
	}

	$sqls .= $cond;

	$js = <<< EOF

		/*
		 * Export data
		*/
		function displayDialog(name,sel_type){
			var obj = document.getElementById(name);
			/*
			 * 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
			 */
			obj.style.left      = document.body.scrollLeft + document.body.offsetWidth - 460;
			/*
			 * 對話框上緣對齊          = [捲動的上座標] 下移 10 個 pixel
			 */
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

			total_rows = obj.rows.length -2;

			for (var i = 0; i < total_rows; i++) {

				col = col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

				if (i == 0){
					csv_content += obj.rows[i].cells[0].innerHTML + "<br>";

					htm_content += '<tr '+ col + '><td colspan="2">'+ obj.rows[i].cells[0].innerHTML+ "</td><tr>";

					xml_content += '<summary>'+obj.rows[i].cells[0].innerHTML + '</summary>';
				}else {
					csv_content += obj.rows[i].cells[0].innerHTML + ',' + obj.rows[i].cells[1].innerHTML + "<br>";

					htm_content += '<tr '+ col + '><td>'+ obj.rows[i].cells[0].innerHTML + '</td><td>'+ obj.rows[i].cells[1].innerHTML + "</td><tr>";

					xml_content += '<record><date_range>'+obj.rows[i].cells[0].innerHTML + '</date_range><data>' + obj.rows[i].cells[1].innerHTML + "</data></record>";
    			}
			}

			var obj2      = document.getElementById('exportForm');
			var nodes     = obj2.getElementsByTagName('input');
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
	showXHTML_script('inline', $js);
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('width="500" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary   = array();
					$ary[] = array($MSG['title3'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_table_B('id ="mainTable" width="500" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

							chkSchoolId(sysDBprefix . 'term_major');
							// 登入總人數
							$total_count = 0;

							switch ($_POST['type_report']){
								case 1:		// 單日報表

									$sqls .= sprintf(' and B.log_time BETWEEN "%s 00:00:00" AND "%s 23:59:59"', $_POST['single_day'], $_POST['single_day']);
									$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
									$rs = $sysConn->Execute($sqls);

									$result = array_pad(array(), 24, 0);
									if ($rs && $rs->RecordCount() > 0){
									    if (is_array($_POST['login']) && in_array('3',$_POST['login']) )
										    $condition_of_3s = dbGetCol('WM_term_major',
																		'username',
																		'1 GROUP BY username HAVING count(*) ' . $_POST['state_sel'] . max(0, $_POST['total_course']));
			  							while($fields = $rs->FetchRow()){
											$temp  = explode(' ',$fields['log_time']);
											$temp1 = explode(':',$temp[1]);

											$hour = intval($temp1[0]);

											// 判斷是否有勾選 修課的狀況	(begin)
											if (count($_POST['login']) > 0){
												if (in_array('3', $_POST['login']))
												{
													if (in_array($fields['username'], $condition_of_3s) && array_key_exists($hour, $result)) {
														$result[$hour] = $result[$hour] + 1;
													}
												}
												else
												{
													if (array_key_exists($hour, $result)) {
														$result[$hour] = $result[$hour] + 1;
													}
												}
											}
											else
											{
												if (array_key_exists($hour, $result)) {
													$result[$hour] = $result[$hour] + 1;
												}
											}
											// 判斷是否有勾選 修課的狀況	(end)
			  							}
			  						}

									$total_count = array_sum($result);

									// 使用者輸入的日期
									$choice_date = $_POST['single_day'];
									$msg = str_replace(array('%DATE%', '%TOTAL_PEOPLE%'),
									                   array($choice_date, '<font color="red">' . $total_count . '</font>'),
									                   $MSG['title44'][$sysSession->lang]);
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
										showXHTML_td('align="center" colspan="2"', $msg);
									showXHTML_tr_E('');

									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
										showXHTML_td('align="center"', $MSG['hour'][$sysSession->lang]);
										showXHTML_td('align="center"', $MSG['title45'][$sysSession->lang]);
									showXHTML_tr_E('');

									// 最大值
									$max_value = 0;

									foreach ($result as $hour => $p_number){

										// 取最大值
										if ($p_number > $max_value){
											$max_value = $p_number;
										}

										$x_scale[] = $hour;
										$y_scale[] = $p_number;
										$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
										showXHTML_tr_B($col);
											showXHTML_td('align="center"', $hour);
											showXHTML_td('align="center"', $p_number);
										showXHTML_tr_E('');
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
										showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'login_single_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
									showXHTML_tr_E('');

									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
										showXHTML_td_B('align="center" colspan="2"');
											showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true; displayDialog(\'exportTable\',\'today\');" id="btn_export"');
									break;

								case 5:		/* 連續日報表 add by wiseguy for TODO#1269 */

									$sqls = preg_replace('/^select\b.*\bfrom/isU', 'select left(B.log_time, 10) AS D, count(*) from', $sqls) . " and B.log_time >= '{$_POST['daily_from_date']} 00:00:00' and B.log_time <= '{$_POST['daily_over_date']} 23:59:59' GROUP BY D";
									$rs = $sysConn->GetAssoc($sqls);
									$from_time = strtotime($_POST['daily_from_date']); $over_time = strtotime($_POST['daily_over_date']);
									for ($i=$from_time; $i<=$over_time; $i+=86400)
									{
									    $that_day = date('Y-m-d', $i);
									    if (!isset($rs[$that_day])) $rs[$that_day] = 0; else settype($rs[$that_day], 'int');
									}
									ksort($rs);

                                    $choice_date = "{$_POST['daily_from_date']} ~ {$_POST['daily_over_date']}";
                                    $genger_sels = array($MSG['title26'][$sysSession->lang], $MSG['title27'][$sysSession->lang], $MSG['title28'][$sysSession->lang]);
                                    settype($_POST['login'], 'array');
									showXHTML_tr_B('class="cssTrEvn"');
										showXHTML_td('colspan="2"', $MSG['stat_duration_colon'][$sysSession->lang] . $choice_date . '<br>' .
                                                         			$MSG['gender_colon'][$sysSession->lang] . $genger_sels[$_POST['gender_sel']] . '<br>' .
                                                         			$MSG['age_colon'][$sysSession->lang] . (in_array('2', $_POST['login']) ? "{$_POST['year_old']}~{$_POST['year_old1']}" : '0~100')
													);
									showXHTML_tr_E();

									showXHTML_tr_B('class="cssTrHead"');
									    showXHTML_td('style="font-weight: bold; text-align: center"', $MSG['title155'][$sysSession->lang]);
									    showXHTML_td('style="font-weight: bold; text-align: center"', $MSG['title45'][$sysSession->lang]);
									showXHTML_tr_E();
									$col = true;
									foreach ($rs as $date => $count)
									{
										showXHTML_tr_B($col ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
										    showXHTML_td('', $date);
										    showXHTML_td('align="right" style="right-padding: 0.8em"', $count);
										showXHTML_tr_E();
										$col ^= true;
									}
									showXHTML_tr_B($col ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
										showXHTML_td('', $MSG['total_amount'][$sysSession->lang]);
										showXHTML_td('align="right" style="right-padding: 0.8em"', array_sum($rs));
									showXHTML_tr_E();
									$col ^= true;

                                    $str_x_scale = implode(',', array_keys($rs));
                                    $str_y_scale = implode(',', array_values($rs));
									$max_value   = max($rs);

									showXHTML_tr_B($col ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
										showXHTML_td('align="right" ', $MSG['title17'][$sysSession->lang]);
										showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'login_daily_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
									showXHTML_tr_E();
									$col ^= true;

									showXHTML_tr_B($col ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
										showXHTML_td_B('align="center" colspan="2"');
											showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true; displayDialog(\'exportTable\',\'year\');" id="btn_export"');
									break;

								case 2:		// 週報表
									$begin      = explode('-',$_POST['en_begin_date']);
									$end        = explode('-',$_POST['en_end_date']);
									$week       = intval(strftime("%U",mktime(0,0,0,$begin[1],$begin[2],$begin[0])));
									$week1      = intval(strftime("%U",mktime(0,0,0,$end[1],$end[2],$end[0])));

									$temp_year  = $begin[0];
									$temp_year1 = $end[0];
									$temp       = $week;
									$temp1      = $week1;

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

									// from 的 年月日
									$w_temp  = Week2YearMonthDay(intval($temp_year),intval($temp));
									$w_date  = date("Y-m-d",$w_temp[0]);

									// to 的 年月日
									$w_temp  = '';
									$w_temp1 = '';
									$w_temp  = Week2YearMonthDay(intval($temp_year1),intval($temp1));
									$w_date1 = date("Y-m-d",$w_temp[6]);

									$sqls .= ' and B.log_time between "' . $w_date . ' 00:00:00" and "' . $w_date1 . ' 23:59:59"';
                                    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
									$rs = $sysConn->Execute($sqls);

									// 使用者輸入的日期
									$choice_date = $w_date . '~' . $w_date1;

									if ($rs->RecordCount() > 0){	// if begin
                                        if (is_array($_POST['login']) && in_array('3',$_POST['login']) )
										    $condition_of_3s = dbGetCol('WM_term_major',
																		'username',
																		'1 GROUP BY username HAVING count(*) ' . $_POST['state_sel'] . max(0, $_POST['total_course']));
										while ($rs1 = $rs->FetchRow()){		// while begin
											$temp = explode(' ',$rs1['log_time']);

											// temp[0] -> year-month-day  temp[1] -> hour:min:sec
											$temp1 = explode('-',$temp[0]);

											$month = ((substr($temp1[1],0,1) == '0')? substr($temp1[1],1,2):$temp1[1]);
											$date  = ((substr($temp1[2],0,1) == '0')? substr($temp1[2],1,2):$temp1[2]);

											$week  = intval(strftime("%U",mktime(0,0,0,$month,$date,$temp1[0])));

											if ($week == 0){
												$w_year = intval($temp1[0]) - 1;

												$year_week = $w_year . '-' . intval(strftime("%U",mktime(0,0,0,12,31,$w_year)));

											}else{
												$year_week = $temp1[0] . '-' . $week;
											}

											if (is_array($_POST['login']) && in_array('3',$_POST['login']) ) {

												if (in_array($rs1['username'], $condition_of_3s)){
													if (array_key_exists($year_week,$show_week)){
														$show_week[$year_week] += 1;
													}else{
														$show_week[$year_week] = 1;
													}
												}else{
													continue;
												}
											}else{
												if (array_key_exists($year_week,$show_week)){
													$show_week[$year_week] += 1;
												}else{
													$show_week[$year_week] = 1;
												}
											}
										}		// while end
									}		// if end

									$total_count = array_sum($show_week);

									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

									showXHTML_tr_B($col);
											showXHTML_td_B('align="center" colspan="2"');
												echo $MSG['from'][$sysSession->lang], $w_date, $MSG['to'][$sysSession->lang], $w_date1,
													 str_replace(array('%TOTAL_PEOPLE%', '%TYPE_KIND%'),
												                 array('<font color="red">' . $total_count . '</font>', $MSG['week'][$sysSession->lang]),
												                 $MSG['title46'][$sysSession->lang]);
											showXHTML_td_E('');
									showXHTML_tr_E('');

									showXHTML_tr_B($col);
											showXHTML_td('align="center"', $MSG['week'][$sysSession->lang]);
											showXHTML_td('align="center"', $MSG['title45'][$sysSession->lang]);
									showXHTML_tr_E('');

									// 最大值
									$max_value = 0;

									foreach ($show_week as $key => $value){

										// 取最大值
										if ($value > $max_value){
											$max_value = $value;
										}
										$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
										showXHTML_tr_B($col);
											showXHTML_td_B('align="left" width="250"');
												// from 的年月日
												$w_temp    = '';
												$temp2     = explode('-',$key);
												$w_temp    = Week2YearMonthDay(intval($temp2[0]),intval($temp2[1]));
												$w_temp1   = date("Y-m-d",$w_temp[0]);

												// to 的年月日
												$w_temp3   = date("Y-m-d",$w_temp[6]);
												$x_scale[] = $w_temp1 . '~' . $w_temp3;

												echo $w_temp1 . '~' . $w_temp3;

												$y_scale[] = $value;

											showXHTML_td_E('');
											showXHTML_td('align="left" width="250"', $value);
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
										showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'login_week_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
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

									// TO 的 年月日
									$w_temp = mktime(0,0,0,01,01,$temp_year1);

									$w_date = date('Y-m-d',strtotime("+" . intval($temp1) . "  month last day",$w_temp));

									// 使用者輸入的日期
									$choice_date = $temp_year . '-' . ((strlen($temp) == 1)? '0' . $temp: $temp) . "-01" . '~' . $w_date;

									$sqls .= ' and B.log_time between "' .
											$temp_year . '-' . ((strlen($temp) == 1)? '0' . $temp: $temp) . '-01' .
											' 00:00:00" and "' . $w_date . ' 23:59:59"';
                                    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
									$rs = $sysConn->Execute($sqls);

									if ($rs->RecordCount() > 0){	// if begin
									    if (is_array($_POST['login']) && in_array('3',$_POST['login']) )
										    $condition_of_3s = dbGetCol('WM_term_major',
																		'username',
																		'1 GROUP BY username HAVING count(*) ' . $_POST['state_sel'] . max(0, $_POST['total_course']));
										while ($rs1 = $rs->FetchRow()){		// while begin

											$year_month = date('Y-m',strtotime($rs1['log_time']));

											if (is_array($_POST['login']) && in_array('3',$_POST['login']) ) {

												if (in_array($rs1['username'], $condition_of_3s)){
													if (array_key_exists($year_month,$show_month)){
														$show_month[$year_month] += 1;
													}else{
														$show_month[$year_month] = 1;
													}
												}else{
													continue;
												}
											}else{
												if (array_key_exists($year_month,$show_month)){
													$show_month[$year_month] += 1;
												}else{
													$show_month[$year_month] = 1;
												}
											}
										}	// while end
									}	// if end

									$total_count = array_sum($show_month);

									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
											showXHTML_td_B('align="center" colspan="2"');
												echo $MSG['from'][$sysSession->lang];
												echo $_POST['month_year'] . '-' . ((strlen($_POST['month']) == 1)? '0' . $_POST['month']: $_POST['month']) . "-01";
												echo $MSG['to'][$sysSession->lang];
												echo $w_date;

												$msg = str_replace('%TOTAL_PEOPLE%','<font color="red">' . $total_count . '</font>',$MSG['title46'][$sysSession->lang]);

												$msg = str_replace('%TYPE_KIND%',$MSG['month'][$sysSession->lang],$msg);

												echo $msg;

											showXHTML_td_E('');

									showXHTML_tr_E('');
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
											showXHTML_td('align="center"', $MSG['month'][$sysSession->lang]);
											showXHTML_td('align="center"', $MSG['title45'][$sysSession->lang]);
									showXHTML_tr_E('');

									// 最大值
									$max_value = max($show_month);

									foreach ($show_month as $key => $value){

										$x_scale[] = $key;
										$y_scale[] = $value;
										$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
										showXHTML_tr_B($col);
											showXHTML_td('align="left" width="250"', $key);
											showXHTML_td('align="left" width="250"', $value);
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
										showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'login_month_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
									showXHTML_tr_E('');

									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
										showXHTML_td_B('align="center" colspan="2"');
											showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'',' onclick="this.disabled=true; displayDialog(\'exportTable\',\'month\');" id="btn_export"');
									break;
								case 4:		// 年報表
									$show_year = array();

									$temp_year  = intval($_POST['year_year']);
									$temp_year1 = intval($_POST['year_year1']);
									if ($temp_year > $temp_year1) {
										$temp_year  = intval($_POST['month_year1']);
										$temp_year1 = intval($_POST['month_year']);
									}

									for($i = $temp_year;$i <= $temp_year1;$i++){
										$show_year[$i] = 0;
									}

									// 使用者輸入的日期
									$choice_date = $temp_year . '-01-01 ~ ' . $temp_year1 . '-12-31';

									$sqls .= ' and B.log_time between "' .
											 $temp_year . '-01-01' .
											 ' 00:00:00" and "' .  $temp_year1 . '-12-31 23:59:59"';
                                    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
									$rs = $sysConn->Execute($sqls);

									if ($rs->RecordCount() > 0){		// if begin
                                        if (is_array($_POST['login']) && in_array('3',$_POST['login']) )
											$condition_of_3s = dbGetCol('WM_term_major',
																		'username',
																		'1 GROUP BY username HAVING count(*) ' . $_POST['state_sel'] . max(0, $_POST['total_course']));
										while ($rs1 = $rs->FetchRow()){		// while begin

											$year = date('Y',strtotime($rs1['log_time']));

											// if begin
											if (is_array($_POST['login']) && in_array('3',$_POST['login']) ) {

												if (in_array($rs1['username'], $condition_of_3s)){
													if (array_key_exists($year,$show_year)){
														$show_year[$year] += 1;
													}else{
														$show_year[$year] = 1;
													}
												}else{
													continue;
												}
											}else {
												if (array_key_exists($year,$show_year)){
													$show_year[$year] += 1;
												}else{
													$show_year[$year] = 1;
												}
											}
											// if end

										}	// while end
									}	// if end

									$total_count = array_sum($show_year);


									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
											showXHTML_td_B('align="center" colspan="2"');
												echo $MSG['from'][$sysSession->lang];
												echo $temp_year . '-01-01';
												echo $MSG['to'][$sysSession->lang];
												echo $temp_year1 . '-12-31';

												$msg = str_replace('%TOTAL_PEOPLE%','<font color="red">' . $total_count . '</font>',$MSG['title46'][$sysSession->lang]);

												$msg = str_replace('%TYPE_KIND%',$MSG['year'][$sysSession->lang],$msg);

												echo $msg;

											showXHTML_td_E('');

									showXHTML_tr_E('');

									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
											showXHTML_td('align="center"', $MSG['year'][$sysSession->lang]);
											showXHTML_td('align="center"', $MSG['title45'][$sysSession->lang]);
									showXHTML_tr_E('');

									// 最大值
									$max_value = 0;

									foreach ($show_year as $key => $value){

										// 取最大值
										if ($value > $max_value){
											$max_value = $value;
										}
										$x_scale[] = $key;
										$y_scale[] = $value;
										$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
										showXHTML_tr_B($col);
											showXHTML_td('align="left" width="250"', $key);
											showXHTML_td('align="left" width="250"', $value);
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
										showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'login_year_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
									showXHTML_tr_E('');
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
										showXHTML_td_B('align="center" colspan="2"');
											showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true; displayDialog(\'exportTable\',\'year\');" id="btn_export"');
									break;

							}
								showXHTML_input('button','btnImp',$MSG['print'][$sysSession->lang],'','onclick="javascript:window.print();"');
								showXHTML_input('button','btnImp',$MSG['title43'][$sysSession->lang],'','onclick="do_fun(2);"');
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
	    	showXHTML_input('hidden', 'type', '', '', '');
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
		        showXHTML_input('text', 'download_name', 'login_stat.zip', '', 'maxlength="60" size="40" class="box02"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td_B('colspan="2" align="center"');
		      	showXHTML_input('hidden', 'sel_type', '', '', '');
		        showXHTML_input('hidden', 'csv_content', '', '', '');
		  		showXHTML_input('hidden', 'htm_content', '', '', '');
    	  		showXHTML_input('hidden', 'xml_content', '', '', '');
    	  		showXHTML_input('hidden', 'function_name', $MSG['title3'][$sysSession->lang], '', '');
		        showXHTML_input('hidden', 'adv_file', 'login_times_stat', '', '');
		        showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="checkExport();"');
		        showXHTML_input('button', '', $MSG['title22'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'exportTable\'); document.getElementById(\'btn_export\').disabled = false;"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		  showXHTML_table_E();
        showXHTML_tabFrame_E();

	showXHTML_body_E('');
?>

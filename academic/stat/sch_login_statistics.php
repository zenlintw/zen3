<?php
	/**
	 * 學校統計資料 - 登入次數統計
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_login_statistics.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/Week2YearMonthDay.php');
	
	require_once(sysDocumentRoot . '/lang/teach_statistics.php');
	require_once(sysDocumentRoot . '/mooc/models/statistics.php');
	require_once(sysDocumentRoot . '/lib/course.php');
	

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 輸入值的處理
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

	// assign
	$smarty->assign('post', $_POST);
	$smarty->assign('MSG', $MSG);
	$smarty->assign('sysSession', $sysSession);
	$smarty->assign('thisYear', date('Y'));
	
	//預設週的啟始日與截止日
	$smarty->assign('defaultStartWeekDate', date('Y-m-01',time()));
	$smarty->assign('defaultEndWeekDate', date('Y-m-t',time()));
	
	$reportType = !isset($_POST['type_report'])?1:max(min(5,intval($_POST['type_report'])),1);
	$smarty->assign('reportType', $reportType);
	
	$sqls = 'select B.username,B.log_time ' .
    	' from WM_log_others as B  ' .
    	' join '.sysDBname.'.WM_all_account as A ' .
    	' on A.username = B.username ' .
    	' where B.function_id=600100100 and B.result_id = 0 ';
	
	$reportResult = '';
	switch ($reportType){
	    case 1:		// 目前正在上課中的課程
	        if (empty($_POST['single_day'])) $_POST['single_day'] = date('Y-m-d');
	        $sqls .= sprintf(' and B.log_time BETWEEN "%s 00:00:00" AND "%s 23:59:59"', $_POST['single_day'], $_POST['single_day']);
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			$rs = $sysConn->Execute($sqls);

			$result = array_pad(array(), 24, 0);
			if ($rs && $rs->RecordCount() > 0){
				while($fields = $rs->FetchRow()){
					$temp  = explode(' ',$fields['log_time']);
					$temp1 = explode(':',$temp[1]);

					$hour = intval($temp1[0]);
					if (array_key_exists($hour, $result)) {
					    $result[$hour] = $result[$hour] + 1;
					}
				}
			}
			$total_count = array_sum($result);

			// 使用者輸入的日期
			$choice_date = $_POST['single_day'];
			$msg = str_replace(array('%DATE%', '%TOTAL_PEOPLE%'),
			                   array($choice_date, '<font color="red">' . $total_count . '</font>'),
			                   $MSG['title44'][$sysSession->lang]);
			
			$reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$msg.'</SPAN>';
			
			$reportResult .= '<thead><tr>';
			$reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['hour'][$sysSession->lang].'</th>';
			$reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title45'][$sysSession->lang].'</th>';
			$reportResult .= '</tr></thead>';

			// 最大值
			$max_value = 0;
			foreach ($result as $hour => $p_number){
				// 取最大值
				if ($p_number > $max_value){
					$max_value = $p_number;
				}
				$x_scale[] = $hour;
				$y_scale[] = $p_number;
				$reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$hour.'</td>';
				$reportResult .= '<td class="text-left">'.$p_number.'</td></tr>';
			}

			if (count($x_scale) > 0){
				$str_x_scale = implode(',',$x_scale);
			}
			if (count($y_scale) > 0){
				$str_y_scale = implode(',',$y_scale);
			}
			
			$action="login_single_graph.php";
			$smarty->assign('choice_date', $choice_date);
			$smarty->assign('total_count', $total_count);
			$smarty->assign('dataCategories', implode("','",$x_scale));
			$smarty->assign('x_scale', implode(",",$x_scale));
			$smarty->assign('dataSeries', implode(",",$y_scale));
			$smarty->assign('action', $action);
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
            $msg = $MSG['stat_duration_colon'][$sysSession->lang] . $choice_date ;
            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$msg.'</SPAN>';
            $reportResult .= '<thead><tr>';
            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['title155'][$sysSession->lang].'</th>';
            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title45'][$sysSession->lang].'</th>';
            $reportResult .= '</tr></thead>';
            
            foreach ($rs as $date => $count)
            {
                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$date.'</td>';
                $reportResult .= '<td class="text-left">'.$count.'</td></tr>';
            }
            
            $action="login_daily_graph.php";
			
            $smarty->assign('choice_date', $choice_date);
            $smarty->assign('total_count', array_sum($rs));
            $smarty->assign('dataCategories', implode("','",array_keys($rs)));
            $smarty->assign('x_scale', implode(",",array_keys($rs)));
            $smarty->assign('dataSeries', implode(",",array_values($rs)));
            $smarty->assign('action', $action);
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
                    $condition_of_3s = dbGetCol('WM_term_major','username','1 GROUP BY username HAVING count(*) ' . $_POST['state_sel'] . max(0, $_POST['total_course']));
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
        
            $msg = $MSG['from'][$sysSession->lang]. $w_date. $MSG['to'][$sysSession->lang]. $w_date1;
            $msg .= str_replace(
                array('%TOTAL_PEOPLE%', '%TYPE_KIND%'),
                array('<font color="red">' . $total_count . '</font>', 
                $MSG['week'][$sysSession->lang]),$MSG['title46'][$sysSession->lang]);
            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$msg.'</SPAN>';
            $reportResult .= '<thead><tr>';
            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['week'][$sysSession->lang].'</th>';
            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title45'][$sysSession->lang].'</th>';
            $reportResult .= '</tr></thead>';
            $action="login_week_graph.php";
            // 最大值
            $max_value = 0;
        
            foreach ($show_week as $key => $value){
        
                // 取最大值
                if ($value > $max_value){
                    $max_value = $value;
                }
                // from 的年月日
                $w_temp    = '';
                $temp2     = explode('-',$key);
                $w_temp    = Week2YearMonthDay(intval($temp2[0]),intval($temp2[1]));
                $w_temp1   = date("Y-m-d",$w_temp[0]);
        
                // to 的年月日
                $w_temp3   = date("Y-m-d",$w_temp[6]);
                $x_scale[] = $w_temp1 . '~' . $w_temp3;
                $y_scale[] = $value;
                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$w_temp1 . '~' . $w_temp3.'</td>';
                $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
            }
        
            if (count($x_scale) > 0){
                $str_x_scale = implode(',',$x_scale);
            }
            if (count($y_scale) > 0){
                $str_y_scale = implode(',',$y_scale);
            }
            $smarty->assign('choice_date', $choice_date);
            $smarty->assign('total_count', $total_count);
            $smarty->assign('dataCategories', implode("','",$x_scale));
            $smarty->assign('x_scale', implode(",",$x_scale));
            $smarty->assign('dataSeries', implode(",",$y_scale));
            $smarty->assign('action', $action);
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

            $msg = $MSG['from'][$sysSession->lang].$_POST['month_year'] . '-' . ((strlen($_POST['month']) == 1)? '0' . $_POST['month']: $_POST['month']) . "-01".$MSG['to'][$sysSession->lang].$w_date;
            $msg .= str_replace('%TOTAL_PEOPLE%','<font color="red">' . $total_count . '</font>',$MSG['title46'][$sysSession->lang]);
            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$msg.'</SPAN>';
            $reportResult .= '<thead><tr>';
            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['month'][$sysSession->lang].'</th>';
            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title45'][$sysSession->lang].'</th>';
            $reportResult .= '</tr></thead>';
            
            $action="login_month_graph.php";
            // 最大值
            $max_value = max($show_month);
        
            foreach ($show_month as $key => $value){
        
                $x_scale[] = $key;
                $y_scale[] = $value;
                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$key.'</td>';
                $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
            }
        
            if (count($x_scale) > 0){
                $str_x_scale = implode(',',$x_scale);
            }
            if (count($y_scale) > 0){
                $str_y_scale = implode(',',$y_scale);
            }
            $smarty->assign('choice_date', $choice_date);
            $smarty->assign('total_count', $total_count);
            $smarty->assign('dataCategories', implode("','",$x_scale));
            $smarty->assign('x_scale', implode(",",$x_scale));
            $smarty->assign('dataSeries', implode(",",$y_scale));
            $smarty->assign('action', $action);
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
            
            $msg = $MSG['from'][$sysSession->lang].$temp_year . '-01-01'.$MSG['to'][$sysSession->lang].$temp_year1 . '-12-31';
            $msg .= str_replace('%TOTAL_PEOPLE%','<font color="red">' . $total_count . '</font>',$MSG['title46'][$sysSession->lang]);
            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$msg.'</SPAN>';
            $reportResult .= '<thead><tr>';
            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['year'][$sysSession->lang].'</th>';
            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title45'][$sysSession->lang].'</th>';
            $reportResult .= '</tr></thead>';
            
            $action="login_year_graph.php";
            
            // 最大值
            $max_value = 0;
            foreach ($show_year as $key => $value){
                // 取最大值
                if ($value > $max_value){
                    $max_value = $value;
                }
                $x_scale[] = $key;
                $y_scale[] = $value;
                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$key.'</td>';
                $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
            }
        
            if (count($x_scale) > 0){
                $str_x_scale = implode(',',$x_scale);
            }
            if (count($y_scale) > 0){
                $str_y_scale = implode(',',$y_scale);
            }
            $smarty->assign('choice_date', $choice_date);
            $smarty->assign('total_count', $total_count);
            $smarty->assign('dataCategories', implode("','",$x_scale));
            $smarty->assign('x_scale', implode(",",$x_scale));
            $smarty->assign('dataSeries', implode(",",$y_scale));
            $smarty->assign('action', $action);
            break;
        
	}
	$smarty->assign('reportResult', $reportResult);
        $smarty->assign('msg', $MSG);
	$smarty->display('academic/stat/sch_login_statistics.tpl');

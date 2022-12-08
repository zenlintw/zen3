<?php
	/**
	 * 學校統計資料 - 上課次數統計
	 *
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_course_statistics.php,v 1.1 2010/02/24 02:38:43 saly Exp $
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
	
	$course_rang = !isset($_POST['ck_course_rang'])?1:max(min(3,intval($_POST['ck_course_rang'])),1);
	$reportType = !isset($_POST['type_report'])?1:max(min(5,intval($_POST['type_report'])),1);
	$smarty->assign('reportType', $reportType);
	
	$aSql = array(
	   'WM_term_major as M'    => array('a' => 'M.course_id in ()'),
	   'WM_log_classroom as B' => array('a' => 'B.function_id=2500100200','c' => 'B.result_id = 0')
	);
	
	chkSchoolId('WM_term_major');
	// 課程篩選條件 begin
	if ($_POST['ck_course_rang'] == 2)
	{
        if ($_POST['single_group_id'] == '10000000') // 未分組
        {
            $courses = $sysConn->GetCol('select B.course_id ' .
            'from WM_term_course as B ' .
            'left join WM_term_group as G ' .
            'on B.kind="course" and B.course_id=G.child ' .
            'where G.child is NULL');
        }
        elseif ($_POST['single_group_id'])
        {
            $courses = array_keys(getAllCourseInGroup(intval($_POST['single_group_id'])));
        }
        else
            die('group id required.');
	    
        $aSql['WM_term_major as M']['a'] = str_replace(' in ()', ' in (' . implode(',', $courses) . ')', $aSql['WM_term_major as M']['a']);
	}else if ($_POST['ck_course_rang'] == 3){
	    $aSql['WM_term_major as M']['a'] = str_replace(' in ()', '=' . intval($_POST['single_course_id']), $aSql['WM_term_major as M']['a']);
	}else{
	    unset($aSql['WM_term_major as M']['a']);
	}
	// 課程篩選條件 end
	$user_account_flag = 0;

	// 產生 SQL begin
	foreach ($aSql as $k => $v) if (count($v) == 0) unset($aSql[$k]);
	
	$sqls = 'SELECT B.log_time FROM ' . implode(',', array_keys($aSql)) . ' WHERE ';
	if ($aSql['WM_term_major as M'])
	{
	    $sqls .= implode(' AND ', $aSql['WM_term_major as M']);
	    if ($aSql['WM_term_major as M'] && $aSql['WM_user_account as A']) $sqls .= ' AND M.username=A.username';
	    $sqls .= ' AND ';
	}
	if ($aSql['WM_user_account as A'])
	{
	    $sqls .= implode(' AND ', $aSql['WM_user_account as A']);
	    if ($aSql['WM_user_account as A'] && $aSql['WM_log_classroom as B']) $sqls .= ' AND A.username=B.username';
	    $sqls .= ' AND ';
	}
	if ($aSql['WM_log_classroom as B'])
	{
	    $sqls .= implode(' AND ', $aSql['WM_log_classroom as B']);
	    if ($aSql['WM_log_classroom as B'] && $aSql['WM_term_major as M']) $sqls .= ' AND B.username=M.username AND B.department_id=M.course_id';
	}
	// 產生 SQL end
	
	
	
	$reportResult = '';
	switch ($reportType){
	    case 1:		// 單日報表
	        if (empty($_POST['single_day'])) $_POST['single_day'] = date('Y-m-d');
	        $result = array_pad(array(), 24, 0);
	        $sqls  .= sprintf(' and B.log_time BETWEEN "%s 00:00:00" AND "%s 23:59:59"', $_POST['single_day'], $_POST['single_day']);
	        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	        $rs = $sysConn->Execute($sqls);
	        if ($rs && $rs->RecordCount() > 0)
	        {
	            while($fields = $rs->FetchRow())
	            {
	                $temp  = explode(' ', $fields['log_time']);
	                $temp1 = explode(':', $temp[1]);
	    
	                $hour = intval($temp1[0]);
	                if (array_key_exists($hour, $result))
	                    $result[$hour] = $result[$hour] + 1;
	            }
	        }
	        $total_count = array_sum($result);
	    
	        // 使用者輸入的日期
	        $choice_date = $_POST['single_day'];
	        $msg = str_replace(
	           array('%DATE%', '%TOTAL_PEOPLE%'),
	           array($choice_date, '<font color="red">' . $total_count . '</font>'),
	           $MSG['title67'][$sysSession->lang]);
	        	
	        $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$MSG['title73'][$sysSession->lang] . $msg.'</SPAN>';
	        	
	        $reportResult .= '<thead><tr>';
	        $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['hour'][$sysSession->lang].'</th>';
	        $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title69'][$sysSession->lang].'</th>';
	        $reportResult .= '</tr></thead>';
	    
	        // 最大值
	        $max_value = max($result);
	        foreach ($result as $hour => $p_number){
	            $x_scale[] = $hour;
	            $y_scale[] = $p_number;
	            $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$hour.'</td>';
	            $reportResult .= '<td class="text-left">'.$p_number.'</td></tr>';
	        }
	    
	        if (count($x_scale) > 0){
	            $str_x_scale = implode(',', $x_scale);
	        }
	        if (count($y_scale) > 0){
	            $str_y_scale = implode(',', $y_scale);
	        }
	    
                $action="cour_login_single_graph.php";
	    
	        $smarty->assign('choice_date', $choice_date);
	        $smarty->assign('total_count', $total_count);
	        $smarty->assign('dataCategories', implode("','",$x_scale));
                $smarty->assign('x_scale', implode(",",$x_scale));
	        $smarty->assign('dataSeries', implode(",",$y_scale));
                $smarty->assign('action', $action);
	    break;
	        
        case 5:		/* 連續日報表 add by wiseguy for TODO#1269 */
            $sqls = preg_replace('/^select\b.*\bfrom/isU', 'select left(B.log_time, 10) AS D, count(*) from', $sqls) . " and B.log_time between '{$_POST['daily_from_date']} 00:00:00' and '{$_POST['daily_over_date']} 23:59:59' GROUP BY D";
            $rs = $sysConn->GetAssoc($sqls);
            $from_time = strtotime($_POST['daily_from_date']); $over_time = strtotime($_POST['daily_over_date']);
            for ($i=$from_time; $i<=$over_time; $i+=86400)
            {
                $that_day = date('Y-m-d', $i);
                if (!isset($rs[$that_day])) $rs[$that_day] = 0; else settype($rs[$that_day], 'int');
            }
            ksort($rs);
        
            $choice_date = "{$_POST['daily_from_date']} ~ {$_POST['daily_over_date']}";
            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$MSG['stat_duration_colon'][$sysSession->lang] . $choice_date.'</SPAN>';
            $reportResult .= '<thead><tr>';
            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['title155'][$sysSession->lang].'</th>';
            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title69'][$sysSession->lang].'</th>';
            $reportResult .= '</tr></thead>';
            
            $col = true;
            foreach ($rs as $date => $count)
            {
                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$date.'</td>';
                $reportResult .= '<td class="text-left">'.$count.'</td></tr>';
            }
            $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$MSG['total_amount'][$sysSession->lang].'</td>';
            $reportResult .= '<td class="text-left">'.array_sum($rs).'</td></tr>';
            
            $action="cour_login_daily_graph.php";
        
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
                    $temp_week = ($i == $temp_year1) ? $temp1      : intval(strftime('%U', mktime(0, 0, 0, 12, 31, $i)));
                    $pfix      = ($i == $temp_year1) ? $temp_year1 : $i;
                    $j         = ($i == $temp_year)  ? $temp       : 1;
                    for (; $j <= $temp_week; $j++)
                    {
                        $show_week[$pfix . '-' . $j] = 0;
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
                while ($rs1 = $rs->FetchRow()){		// while begin
                    $temp  = explode(' ',$rs1['log_time']);
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
                
                    if (array_key_exists($year_week,$show_week)){
                        $show_week[$year_week] += 1;
                    }
                }	// while end
            }	// if end
        
            $total_count = array_sum($show_week);
            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$MSG['from'][$sysSession->lang]. $w_date. $MSG['to'][$sysSession->lang]. $w_date1.'</SPAN>';
            $reportResult .= '<thead><tr>';
            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['week'][$sysSession->lang].'</th>';
            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title69'][$sysSession->lang].'</th>';
            $reportResult .= '</tr></thead>';
            foreach ($show_week as $key => $value){
                // from 的年月日
                $w_temp  = '';
                $temp2   = explode('-',$key);
                $w_temp  = Week2YearMonthDay(intval($temp2[0]),intval($temp2[1]));
                $w_temp1 = date("Y-m-d",$w_temp[0]);
                
                // to 的年月日
                $w_temp3   = date("Y-m-d",$w_temp[6]);
                $x_scale[] = $w_temp1 . '~' . $w_temp3;
                $y_scale[] = $value;
                
                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$w_temp1 . '~' . $w_temp3.'</td>';
                $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
            }
            $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$MSG['total_amount'][$sysSession->lang].'</td>';
            $reportResult .= '<td class="text-left">'.$total_count.'</td></tr>';
            
            $action="cour_login_week_graph.php";
			
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
            	
            if($rs)
            {
                if ($rs->RecordCount() > 0){	// if begin
                    while ($rs1 = $rs->FetchRow()){		// while begin
                        $year_month = date('Y-m',strtotime($rs1['log_time']));
        
                        if (array_key_exists($year_month,$show_month)){
                            $show_month[$year_month] += 1;
                        }else{
                            $show_month[$year_month] = 1;
                        }
                    }	// while end
                }	// if end
            }
        
            $total_count = array_sum($show_month);
            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$MSG['from'][$sysSession->lang]. sprintf('%d-%02d-01', $temp_year, $temp).$MSG['to'][$sysSession->lang]. $w_date.'</SPAN>';
            $reportResult .= '<thead><tr>';
            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['month'][$sysSession->lang].'</th>';
            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title69'][$sysSession->lang].'</th>';
            $reportResult .= '</tr></thead>';
        
            foreach ($show_month as $key => $value){
                $x_scale[] = $key;
                $y_scale[] = $value;
                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$key.'</td>';
                $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
            }
            $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$MSG['total_amount'][$sysSession->lang].'</td>';
            $reportResult .= '<td class="text-left">'.$total_count.'</td></tr>';
            
            $action="cour_login_month_graph.php";
			
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
                while ($rs1 = $rs->FetchRow()){		// while begin
                    $year = date('Y',strtotime($rs1['log_time']));
                    if (array_key_exists($year,$show_year)){
                        $show_year[$year] += 1;
                    }else{
                        $show_year[$year] = 1;
                    }
                }	// while end
            }	// if end
        
            $total_count = array_sum($show_year);
            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$MSG['from'][$sysSession->lang].$temp_year.'-01-01'.$MSG['to'][$sysSession->lang]. $temp_year1. '-12-31'.'</SPAN>';
            $reportResult .= '<thead><tr>';
            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['year'][$sysSession->lang].'</th>';
            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title69'][$sysSession->lang].'</th>';
            $reportResult .= '</tr></thead>';
        
            foreach ($show_year as $key => $value){
                $x_scale[] = $key;
                $y_scale[] = $value;
                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$key.'</td>';
                $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
            }
            $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$MSG['total_amount'][$sysSession->lang].'</td>';
            $reportResult .= '<td class="text-left">'.$total_count.'</td></tr>';
            
            $action="cour_login_year_graph.php";
			
            $smarty->assign('choice_date', $choice_date);
            $smarty->assign('total_count', $total_count);
            $smarty->assign('dataCategories', implode("','",$x_scale));
            $smarty->assign('x_scale', implode(",",$x_scale));
            $smarty->assign('dataSeries', implode(",",$y_scale));
            $smarty->assign('action', $action);
        break;
	}
	$smarty->assign('courseRange', $course_rang);
	$smarty->assign('reportResult', $reportResult);
	$smarty->display('academic/stat/sch_course_statistics.tpl');

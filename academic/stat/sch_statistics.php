<?php
	/**
	 * 學校統計資料 - 課程統計
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_statistics.php,v 1.1 2010/02/24 02:38:43 saly Exp $
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
	
	$this_year = date('Y');
	
	// assign
	$smarty->assign('post', $_POST);
	$smarty->assign('MSG', $MSG);
	$smarty->assign('sysSession', $sysSession);
	$smarty->assign('thisYear', $this_year);
	
	//預設週的啟始日與截止日
	$smarty->assign('defaultStartWeekDate', date('Y-m-01',time()));
	$smarty->assign('defaultEndWeekDate', date('Y-m-t',time()));
	
	$reportType = !isset($_POST['type_report'])?1:max(min(4,intval($_POST['type_report'])),1);
	$smarty->assign('reportType', $reportType);
	
	$reportResult = '';
	switch ($reportType){
	    case 1:		// 目前正在上課中的課程
	        chkSchoolId('WM_term_course');
	        $cour = $sysConn->GetCol('select count(B.username) from WM_term_course as A left join WM_term_major as B on A.course_id = B.course_id ' .
	        'where A.course_id > 10000000 and A.kind = "course" and ' .
	        '(A.st_begin is NULL || A.st_begin = "0000-00-00" || A.st_begin <= CURDATE())  and ' .
	        '(A.st_end is NULL || A.st_end = "0000-00-00" || A.st_end >= CURDATE())  ' .
	        'and A.status in (1,2,3,4) group by A.course_id' );
	        
	        $reportResult = str_replace(
	           array('%NOW%', '%TOTAL_COURSE%', '%TOTAL_PEOPLE%'),
	           array(date("Y-m-d"), count($cour), array_sum($cour)),
	           $MSG['title13'][$sysSession->lang]);
	        break;
	    case 2:
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
	        $show_week[$MSG['title172B'][$sysSession->lang]] = 0;
	        
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
	            if (empty($row['st_begin']) || $row['st_begin']=='0000-00-00')
	                $show_week[$MSG['title172B'][$sysSession->lang]]++;
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
	        
	        // summary
	        $w_temp = mktime(0,0,0,1,1,2004);
	        $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$MSG['from'][$sysSession->lang]. $w_date. $MSG['to'][$sysSession->lang]. $w_date1;
	        $reportResult .= str_replace(
	           array('%TOTAL_COURSE%', '%TYPE_KIND%'),
	           array('<font color="red">' . array_sum($show_week) . '</font>', $MSG['week'][$sysSession->lang]),
	           $MSG['title23'][$sysSession->lang]).'</SPAN>';
	        $reportResult .= '<thead><tr>';
	        $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['week'][$sysSession->lang].'</th>';
	        $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title16'][$sysSession->lang].'</th>';
	        $reportResult .= '</tr></thead>';
			
                $action="cour_week_graph.php";
        
	        // 最大值
	        $max_value = max($show_week);
	        foreach ($show_week as $key => $value){
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
	        
	                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'. $w_temp1 . '~' . $w_temp3.'</td>';
	            }else{
	                $x_scale[] = $MSG['title172B'][$sysSession->lang];
	                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'.$key.'</td>';
	            }
	            
                $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
	        
	            // 開幾門課
	            $y_scale[] = $value;
	        
	        }
	        
	        $smarty->assign('choice_date', $choice_date);
	        $smarty->assign('total_courses', array_sum($show_week));
	        $smarty->assign('dataCategories', implode("','",$x_scale));
                $smarty->assign('x_scale', implode(",",$x_scale));
	        $smarty->assign('dataSeries', implode(",",$y_scale));
                $smarty->assign('action', $action);
                $smarty->assign('dataMax', $max_value);
	        
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
	            $show_month[$MSG['title172B'][$sysSession->lang]] = 0;
	        
	            // TO 的 年月日
	            $w_temp = mktime(0,0,0,01,01,$_POST['month_year1']);
	        
	            $w_date = date('Y-m-d',strtotime("+" . intval($_POST['month1']) . "  month last day",$w_temp));
	        
	            // 使用者輸入的日期
	            $choice_date = $_POST['month_year'] . '-' . ((strlen($_POST['month']) == 1)? '0' . $_POST['month']: $_POST['month']) . "-01" . '~' . $w_date;
	        
	            $rs = dbGetStMr('WM_term_course', 'st_begin', 'course_id > 10000000 and kind = "course" and ' .
	            '(st_begin is NULL || st_begin = "0000-00-00" || st_begin between "' . $_POST['month_year'] . '-' . ((strlen($_POST['month']) == 1)? '0' . $_POST['month']: $_POST['month']) . '-01" and "'.$w_date.'") and '.
	            'status not in (0,5,9)', ADODB_FETCH_ASSOC);
	            if ($rs) while($row = $rs->FetchRow()) {
	                if (empty($row['st_begin']) || $row['st_begin']=='0000-00-00')
	                    $show_month[$MSG['title172B'][$sysSession->lang]]++;
	                else {
	                    $temp_month = explode('-',$row['st_begin']);
	                    $year_month = date('Y-m',mktime (0,0,0,$temp_month[1],$temp_month[2],$temp_month[0]));
	                    $show_month[$year_month]++;
	                }
	            }
	        
	            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
	            showXHTML_tr_B($col);
	            showXHTML_td_B('align="center" colspan="2"');
	        
	            // summary
	            $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$MSG['from'][$sysSession->lang].$_POST['month_year'] . '-' .  $_POST['month'].$MSG['to'][$sysSession->lang].$_POST['month_year1'] . '-' .  $_POST['month1'];
	            $reportResult .= str_replace(
	               array('%TOTAL_COURSE%', '%TYPE_KIND%'),
	               array('<font color="red">' . array_sum($show_month) . '</font>', $MSG['month'][$sysSession->lang]),
    	           $MSG['title23'][$sysSession->lang]).'</SPAN>';
	            $reportResult .= '<thead><tr>';
	            $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['month'][$sysSession->lang].'</th>';
	            $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title16'][$sysSession->lang].'</th>';
	            $reportResult .= '</tr></thead>';
	            
                    $action="cour_month_graph.php";
	            
	            // 最大值
	            $max_value = max($show_month);
	            foreach ($show_month as $key => $value){
	                $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'. $key.'</td>';
	                if (substr_count($key,'-') > 0){
	                    $x_scale[] = $key . ' ';
	                }else{
	                    $x_scale[] = $MSG['title172B'][$sysSession->lang];
	                }
	                $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
	                // 開課數
	                $y_scale[] = $value;
	            }
	            $smarty->assign('choice_date', $choice_date);
	            $smarty->assign('total_courses', array_sum($show_month));
	            $smarty->assign('dataCategories', implode("','",$x_scale));
                    $smarty->assign('x_scale', implode(",",$x_scale));
	            $smarty->assign('dataSeries', implode(",",$y_scale));
                    $smarty->assign('action', $action);
                    $smarty->assign('dataMax', $max_value);
	            break;
            case 4:		// 年報表
                $show_year = array();
                $temp_year  = min(intval($_POST['year_year']), intval($_POST['year_year1']));
                $temp_year1 = max(intval($_POST['year_year']), intval($_POST['year_year1']));
            
                // 使用者輸入的日期
                $choice_date = $temp_year . '-01-01 ~ ' . $temp_year1 . '-12-31';
                for($i = $temp_year;$i <= $temp_year1;$i++){
                    $show_year[$i] = 0;
                }
                $show_year[$MSG['title172B'][$sysSession->lang]] = 0;
        
                $rs = dbGetStMr('WM_term_course', 'st_begin', 'course_id > 10000000 and kind = "course" and ' .
                    '(st_begin is NULL || st_begin = "0000-00-00" || st_begin between "'.$temp_year.'-01-01" and "'.$temp_year1.'-12-31") and '.
                    'status not in (0,5,9)', ADODB_FETCH_ASSOC);
                if ($rs) while($row = $rs->FetchRow()) {
                    if (empty($row['st_begin']) || $row['st_begin']=='0000-00-00'){
                        $show_year[$MSG['title172B'][$sysSession->lang]]++;
                    }else {
                        $temp_month = explode('-',$row['st_begin']);
                        $show_year[$temp_month[0]]++;
                    }
                }
        
                // summary
                $reportResult = '<SPAN class="text-lef" style="font-size:medium;line-height:1.5em;">'.$MSG['from'][$sysSession->lang].$_POST['year_year'].$MSG['to'][$sysSession->lang].$_POST['year_year1'];
                $reportResult .= str_replace(array('%TOTAL_COURSE%', '%TYPE_KIND%'),
                    array('<font color="red">' . array_sum($show_year) . '</font>', $MSG['year'][$sysSession->lang]),
                    $MSG['title23'][$sysSession->lang]).'</SPAN>';
                $reportResult .= '<thead><tr>';
                $reportResult .= '<th class="text-left" data-sort="string" width="43%">'.$MSG['year'][$sysSession->lang].'</th>';
                $reportResult .= '<th class="text-left" data-sort="string">'.$MSG['title16'][$sysSession->lang].'</th>';
                $reportResult .= '</tr></thead>';
				
				$action="cour_year_graph.php";
        
				// 最大值
				$max_value = max($show_year);
				foreach ($show_year as $key => $value){
				    $reportResult .= '<tr><td class="text-left breakword" style="width:480px;">'. $key.'</td>';
				    if (is_numeric($key)){
				        $x_scale[] = $key . ' ';
				    }else{
				        $x_scale[] = $MSG['title172B'][$sysSession->lang];
				    }
				    $reportResult .= '<td class="text-left">'.$value.'</td></tr>';
				    // 開課數
				    $y_scale[] = $value;
				}
				$smarty->assign('choice_date', $choice_date);
				$smarty->assign('total_courses', array_sum($show_year));
				$smarty->assign('dataCategories', implode("','",$x_scale));
				$smarty->assign('x_scale', implode(",",$x_scale));
				$smarty->assign('dataSeries', implode(",",$y_scale));
				$smarty->assign('action', $action);
				$smarty->assign('dataMax', $max_value);
			break;
	}
	$smarty->assign('reportResult', $reportResult);
	// output
	$smarty->display('academic/stat/sch_statistics.tpl');
?>
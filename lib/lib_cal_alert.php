<?php
	/**
	 * 行事曆提醒(Alert)函式庫
	 *
	 * 建立日期：2004/3/31
	 * @author  KuoYang Tsao
	 * @version $Id: lib_cal_alert.php,v 1.1 2010/02/24 02:39:33 saly Exp $: lib_cal_alert.php
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	
    if (!function_exists('LoadMyCalSetting')) {
		/**
		 * 取得個人的行事曆設定值(要不要顯示其他行事曆)
		 **/
		function LoadMyCalSetting() {
			global $sysSession;
	
			list($also_show) = dbGetStSr('WM_cal_setting', 'also_show', 'username="' . $sysSession->username . '"', ADODB_FETCH_NUM);
			return isSet($also_show) ? explode(',', $also_show) : Array('course', 'class', 'school'); // 預設為全部都有
		}
    }
	$MyCalendarSettings = LoadMyCalSetting();

/*************************************
	副程式群開始
 *************************************/

	/**
	 * 回圈列出行事曆內容
	 *	( 為以下Get XXXX Alert 而設 )
	 */
	function LoopMemoRS2($RS, &$cnt,&$str,&$tempAr) {
		global $sysSession;
		$ok = true;
		if(!$RS)
			return;
		while (!$RS->EOF) {
			$id = intval($RS->fields['idx']);
			
			if(isset($tempAr)) {
				if(in_array($id, $tempAr)) {	// 此筆已經列過
					$ok = false;		//  標示為不再列出
				} else {			// 若未列過
					$ok = true;		//  則標示為要列
					$tempAr[] = $id;	//  並記入陣列中
				}
			}


			if($ok) {
				if (strpos($RS->fields['alert_type'],'login') !== false) {

					$cnt++;
					$caption = getCaption($RS->fields['caption']);
					
					if (is_array($caption) && count($caption)) $caption = $caption[$sysSession->lang];
			
	                $during = '';
					if (($RS->fields['type']=='course'||$RS->fields['type']=='school') && strpos($RS->fields['relative_type'],'begin')!= false) {
						$type = str_replace("begin","end",$RS->fields['relative_type']);
					    list($end_date,$end_time) = dbGetStSr('WM_calendar', 'memo_date,time_end', 'type="'.$RS->fields['type'].'" and relative_id='.$RS->fields['relative_id'].' and relative_type="'.$type.'"', ADODB_FETCH_NUM);
					    if ($end_date!='' && $end_time!='') {
					        $during = $RS->fields['memo_date'].' '.substr($RS->fields['time_begin'],0,5).' ~ '.$end_date.' '.substr($end_time,0,5);	
					    } else {
					    	$during = $RS->fields['memo_date'].' '.substr($RS->fields['time_begin'],0,5).' ~ '.$RS->fields['memo_date'].' '.substr($RS->fields['time_end'],0,5);	
					    }
					}
				    if (($RS->fields['type']=='course'||$RS->fields['type']=='school') && strpos($RS->fields['relative_type'],'end')!= false) {
						$type = str_replace("end","begin",$RS->fields['relative_type']);
					    list($beg_date,$beg_time) = dbGetStSr('WM_calendar', 'memo_date,time_begin', 'type="'.$RS->fields['type'].'" and relative_id='.$RS->fields['relative_id'].' and relative_type="'.$type.'"', ADODB_FETCH_NUM);
					    $during = $beg_date.' '.$beg_time.' ~ '.$RS->fields['memo_date'].' '.substr($RS->fields['time_end'],0,5);	
					}
				    if ($RS->fields['type']=='course' && strpos($RS->fields['relative_type'],'delay')!= false) {
					    $during = $RS->fields['memo_date'].' 00:00 ~ '.$RS->fields['memo_date'].' '.substr($RS->fields['time_end'],0,5);	
					}
	
					$str .= '<memo id="' . $id . '">' .
						'<memo_date>' . $RS->fields['memo_date'] . '</memo_date>' .
						'<username>' . $RS->fields['username'] . '</username>' .
						'<time_begin>' . $RS->fields['time_begin'] . '</time_begin>' .
						'<time_end>' . $RS->fields['time_end'] . '</time_end>' .
						'<repeat>' . $RS->fields['repeat'] . '</repeat>' .
						'<repeat_end>' . $RS->fields['repeat_end'] . '</repeat_end>' .
						'<subject>' . htmlspecialchars($RS->fields['subject']) . '</subject>' .
					    '<course_name>' . $caption . '</course_name>' .
						'<alert_type>' . $RS->fields['alert_type'] . '</alert_type>' .
						'<alert_before>' . $RS->fields['alert_before'] . '</alert_before>' .
						'<content type="' . $RS->fields['ishtml'] . '">' . htmlspecialchars($RS->fields['content']) . '</content>' .
					    '<during>' . $during . '</during>' .
						'</memo>';
				    }
			}

			$RS->MoveNext();
		}
	}

	/**
	 * 取得個人的行事曆
	 * @parm string $username : 使用者名稱( 個人 )
	 **/
	function GetPersonAlert($username) {
		global $sysConn;
		// 取個人行事曆
		$sql = "select c.* from WM_calendar c " .
			   "where c.username='{$username}' and c.type='person' " .
			   "AND CURRENT_DATE  between DATE_SUB(c.memo_date, INTERVAL c.alert_before DAY) and c.memo_date order by c.memo_date, c.time_begin";
		chkSchoolId('WM_calendar');
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		$cnt=0;
		$str = '';
		$tempArr = null;
		LoopMemoRS2($RS, $cnt, $str, $tempArr);
		$xmlstr = '<personal num="' . intval($cnt) . '">' . $str . '</personal>';
		return $xmlstr;
	}

	/**
	 * 取得個人課程的行事曆
	 * @parm string $username : 使用者名稱( 個人 )
	 **/
	function GetCourseAlert($username) {
		global $sysConn, $MyCalendarSettings, $sysRoles;
		$tempArr = Array();	// 避免重複列課程用( 詳見 LoopMemoRS2() )
		$cnt = 0;
		$str = '';
		if(in_array('course', $MyCalendarSettings)) {	// 學員設定要顯示課程行事曆
			// 取個人選課之課程行事曆
			$sql = "select c.*,x.caption from WM_calendar c INNER JOIN WM_term_major t " .
				   "ON c.username=t.course_id " .
				   "INNER JOIN WM_term_course x ON t.course_id=x.course_id ".
				   "where t.username='{$username}' and c.type='course' " .
				   "AND CURRENT_DATE  between DATE_SUB(c.memo_date, INTERVAL c.alert_before DAY) and c.memo_date order by c.memo_date, c.time_begin";
			chkSchoolId('WM_calendar');
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			$RS = $sysConn->Execute($sql);
			LoopMemoRS2($RS, $cnt, $str, $tempArr);
		}

		// 取個人任課之課程行事曆
		$sql = "select c.*,x.caption from WM_calendar c INNER JOIN WM_term_major t " .
			   "ON c.username=t.course_id and t.username='{$username}' and t.role&" .
			   ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) .
			   ' INNER JOIN WM_term_course x ON t.course_id=x.course_id ' .
			   'where c.type="course" AND CURRENT_DATE  between DATE_SUB(c.memo_date, INTERVAL c.alert_before DAY) and c.memo_date ' .
			   'order by c.memo_date, c.time_begin';
        chkSchoolId('WM_calendar');
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		LoopMemoRS2($RS, $cnt, $str, $tempArr);
		$xmlstr = '<course num="' . intval($cnt) . '">' . $str . '</course>';
		return $xmlstr;
	}

	/**
	 * 取得個人班級的行事曆
	 * @parm string $username : 使用者名稱( 個人 )
	 **/
	function GetClassAlert($username) {
		global $sysConn, $MyCalendarSettings, $sysRoles;
		$tempArr = Array();	// 避免重複列班級用( 詳見 LoopMemoRS2() )
		$cnt = 0;
		$str = '';
		if(in_array('class', $MyCalendarSettings)) {	// 學員設定要顯示班級行事曆
			// 取個人選課之班級行事曆
			$sql = "select c.*,x.caption from WM_calendar c INNER JOIN WM_class_member t " .
				   "ON c.username=t.class_id " .
				   "INNER JOIN WM_class_main x ON t.class_id=x.class_id ".
				   "where t.username='{$username}' and c.type='class' " .
				   "AND CURRENT_DATE  between DATE_SUB(c.memo_date, INTERVAL c.alert_before DAY) and c.memo_date order by c.memo_date, c.time_begin";
            chkSchoolId('WM_calendar');
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			$RS = $sysConn->Execute($sql);
			LoopMemoRS2($RS, $cnt, $str, $tempArr);
		}

		// 取個人任導之班級行事曆
		$sql = "select c.*,x.caption from WM_calendar c INNER JOIN WM_class_member t " .
			   "ON c.username=t.class_id " .
			   "INNER JOIN WM_class_main x ON t.class_id=x.class_id ".
			   "where t.username='{$username}' and t.role&" . ($sysRoles['director'] | $sysRoles['assistant']) . " and c.type='class' " .
			   "AND CURRENT_DATE  between DATE_SUB(c.memo_date, INTERVAL c.alert_before DAY) and c.memo_date order by c.memo_date, c.time_begin";
        chkSchoolId('WM_calendar');
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		LoopMemoRS2($RS, $cnt, $str, $tempArr);
		$xmlstr = '<class num="' . intval($cnt) . '">' . $str . '</class>';
		return $xmlstr;
	}

	/**
	 * 取得學校的行事曆
	 * @parm string $username : 使用者名稱( 個人 )
	 **/
	function GetSchoolAlert($school_id) {
		global $sysConn, $MyCalendarSettings;
		$cnt = 0;
		$str = '';
		$tempArr = null;
		if(in_array('school', $MyCalendarSettings)) {	// 學員設定要顯示學校行事曆
			// 取學校行事曆
			$sql = "select c.* from WM_calendar c " .
				   "where c.username='{$school_id}' and c.type='school' " .
				   "AND CURRENT_DATE  between DATE_SUB(c.memo_date, INTERVAL c.alert_before DAY) and c.memo_date order by c.memo_date, c.time_begin";
			chkSchoolId('WM_calendar');
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			$RS = $sysConn->Execute($sql);
			LoopMemoRS2($RS, $cnt, $str, $tempArr);
		}
		$xmlstr = '<school num="' . intval($cnt) . '">' . $str . '</school>';
		return $xmlstr;
	}

	function popCal($sec=5)
	{
		global $sysSession, $sysConn;
		list($last_login) = dbGetStSr('WM_sch4user','last_login',"username='{$sysSession->username}'", ADODB_FETCH_NUM);
		$tm1 = intval(strtotime($last_login)) + intval($sec);
		$tm2 = intval(strtotime(date("Y-m-d H:i:s")));
		return ($tm1 >= $tm2?'Y':'N');
	}

/*************************************
	副程式群結束
 *************************************/


/*************************************
	主函式( 讓登入處呼叫此 function )
 *************************************/

	function GetCalendarAlert() {
		global $sysSession, $sysConn, $MyCalendarSettings;
		$xmlstr  = '<?xml version="1.0" encoding="UTF-8"?><manifest>' .
		           GetPersonAlert($sysSession->username) .
		           GetCourseAlert($sysSession->username) .
		           GetClassAlert($sysSession->username) .
		           GetSchoolAlert($sysSession->school_id) .
		           '</manifest>';
		return $xmlstr;
	}

	/**
	 * GetCalendarHTML()
	 *     取得我的行事曆登入之 HTML
	 * @return
	 **/
	function GetCalendarHTML() {
		global $sysSession, $sysConn, $MyCalendarSettings;

		// calendar
		$cal_type = array('personal','course','class','school');
		$cal_num = count($cal_type);

		$cal_msg = GetCalendarAlert();

		$doc = domxml_open_mem(preg_replace('/\sxmlns\s*=\s*"[^"]*"\s/U', ' ', $cal_msg));
		// init xpath
	    $xpath = @xpath_new_context($doc);
		// for begin
		for ($i = 0;$i < $cal_num;$i++){
		    $obj = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '[@num > 0]/memo');
		    $p_nodeset = $obj->nodeset;
		    $t_p_count = count($p_nodeset);
		    for ($i=0; $i< $t_p_count; $i++){
		        $children = $p_nodeset[$i]->child_nodes();
		        foreach ($children as $child) {
					foreach ($child->child_nodes() as $sub){
						$p_node = $sub->parent_node();

					}
				}
			}
		}
		return $xmlstr;
	}
?>

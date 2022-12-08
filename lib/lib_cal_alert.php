<?php
	/**
	 * ��ƾ䴣��(Alert)�禡�w
	 *
	 * �إߤ���G2004/3/31
	 * @author  KuoYang Tsao
	 * @version $Id: lib_cal_alert.php,v 1.1 2010/02/24 02:39:33 saly Exp $: lib_cal_alert.php
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	
    if (!function_exists('LoadMyCalSetting')) {
		/**
		 * ���o�ӤH����ƾ�]�w��(�n���n��ܨ�L��ƾ�)
		 **/
		function LoadMyCalSetting() {
			global $sysSession;
	
			list($also_show) = dbGetStSr('WM_cal_setting', 'also_show', 'username="' . $sysSession->username . '"', ADODB_FETCH_NUM);
			return isSet($also_show) ? explode(',', $also_show) : Array('course', 'class', 'school'); // �w�]����������
		}
    }
	$MyCalendarSettings = LoadMyCalSetting();

/*************************************
	�Ƶ{���s�}�l
 *************************************/

	/**
	 * �^��C�X��ƾ䤺�e
	 *	( ���H�UGet XXXX Alert �ӳ] )
	 */
	function LoopMemoRS2($RS, &$cnt,&$str,&$tempAr) {
		global $sysSession;
		$ok = true;
		if(!$RS)
			return;
		while (!$RS->EOF) {
			$id = intval($RS->fields['idx']);
			
			if(isset($tempAr)) {
				if(in_array($id, $tempAr)) {	// �����w�g�C�L
					$ok = false;		//  �Хܬ����A�C�X
				} else {			// �Y���C�L
					$ok = true;		//  �h�Хܬ��n�C
					$tempAr[] = $id;	//  �ðO�J�}�C��
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
	 * ���o�ӤH����ƾ�
	 * @parm string $username : �ϥΪ̦W��( �ӤH )
	 **/
	function GetPersonAlert($username) {
		global $sysConn;
		// ���ӤH��ƾ�
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
	 * ���o�ӤH�ҵ{����ƾ�
	 * @parm string $username : �ϥΪ̦W��( �ӤH )
	 **/
	function GetCourseAlert($username) {
		global $sysConn, $MyCalendarSettings, $sysRoles;
		$tempArr = Array();	// �קK���ƦC�ҵ{��( �Ԩ� LoopMemoRS2() )
		$cnt = 0;
		$str = '';
		if(in_array('course', $MyCalendarSettings)) {	// �ǭ��]�w�n��ܽҵ{��ƾ�
			// ���ӤH��Ҥ��ҵ{��ƾ�
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

		// ���ӤH���Ҥ��ҵ{��ƾ�
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
	 * ���o�ӤH�Z�Ū���ƾ�
	 * @parm string $username : �ϥΪ̦W��( �ӤH )
	 **/
	function GetClassAlert($username) {
		global $sysConn, $MyCalendarSettings, $sysRoles;
		$tempArr = Array();	// �קK���ƦC�Z�ť�( �Ԩ� LoopMemoRS2() )
		$cnt = 0;
		$str = '';
		if(in_array('class', $MyCalendarSettings)) {	// �ǭ��]�w�n��ܯZ�Ŧ�ƾ�
			// ���ӤH��Ҥ��Z�Ŧ�ƾ�
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

		// ���ӤH���ɤ��Z�Ŧ�ƾ�
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
	 * ���o�Ǯժ���ƾ�
	 * @parm string $username : �ϥΪ̦W��( �ӤH )
	 **/
	function GetSchoolAlert($school_id) {
		global $sysConn, $MyCalendarSettings;
		$cnt = 0;
		$str = '';
		$tempArr = null;
		if(in_array('school', $MyCalendarSettings)) {	// �ǭ��]�w�n��ܾǮզ�ƾ�
			// ���Ǯզ�ƾ�
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
	�Ƶ{���s����
 *************************************/


/*************************************
	�D�禡( ���n�J�B�I�s�� function )
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
	 *     ���o�ڪ���ƾ�n�J�� HTML
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

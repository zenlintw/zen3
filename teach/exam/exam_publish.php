<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/03/21                                                            *
	 *		work for  : change exam's publish status                                          *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

    // 日期是否設定
    function hasSetDate($date)
    {
        return ($date != '' &&
                $date != '0000-00-00 00:00:00' &&
                $date != '9999-12-31 00:00:00'
               ) ? true : false;
    }


    function sync_calendar($rs)
    {
        global $sysSession, $sysConn, $MSG;
        static $save_tpl;

        if (empty($save_tpl)) $save_tpl = <<< EOB
<manifest>
	<ticket />
	<action>save</action>
	<calEnv>teach</calEnv>
	<year>%u</year>
	<month>%u</month>
	<day>%u</day>
	<idx>%s</idx>
	<time_begin>%u:%u</time_begin>
	<time_end>%u:%u</time_end>
	<repeat>none</repeat>
	<repeat_endY />
	<repeat_endM />
	<repeat_endD />
	<subject>%s</subject>
	<alert_type>login,email</alert_type>
	<alert_before>%u</alert_before>
	<content type="text">%s</content>
</manifest>
EOB;
        $titles = getCaption($rs['title']);

        $idx = $sysConn->GetCol('select calendar_id from WM_calendar_exam where exam_id=' . $rs['exam_id'] . ' order by calendar_id');
		$date1 = getdate(strtotime($rs['begin_time']));
		$date2 = getdate(strtotime($rs['close_time']));
		if(strncmp($rs['begin_time'], $rs['close_time'], 10) == 0) // 起始同一天
		{
			if (hasSetDate($rs['begin_time']))
			{
				//MIS#24968 by Small 2012/05/23
				$ary_hold_date = array($date1['year'],$date1['mon'],$date1['mday']);
				$ary_chg_date = array('*YEAR*','*MONTH*','*DAY*');
				
				$save_xml = sprintf($save_tpl, $date1['year'], $date1['mon']-1, $date1['mday'], $idx[0],
									$date1['hours'], $date1['minutes'], $date2['hours'], $date2['minutes'],
									str_replace($ary_chg_date,$ary_hold_date,$MSG['hold_exam_today'][$sysSession->lang]) . htmlspecialchars($titles[$sysSession->lang]) . $MSG['right_quote'][$sysSession->lang], 4,
									str_replace($ary_chg_date,$ary_hold_date,$MSG['hold_something_today'][$sysSession->lang]) . htmlspecialchars($titles[$sysSession->lang]) . $MSG['attention_please1'][$sysSession->lang]
								   );
				$ret = saveMemo(domxml_open_mem($save_xml), 'course');
			    if (empty($idx[0]) && preg_match('/<manifest id="(\d+)"/', $ret, $regs))
	                dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$rs['exam_id']}");
            }
            elseif ($idx[0]) // 原本有日期改為不限，要刪掉)
            {
            	dbDel('WM_calendar_exam', 'calendar_id=' . $idx[0]);
            	dbDel('WM_calendar', 'idx=' . $idx[0]);
            }

            if ($idx[1]) // 不同天改為同一天 (就會多一筆設定出來，要刪掉)
            {
            	dbDel('WM_calendar_exam', 'calendar_id=' . $idx[1]);
            	dbDel('WM_calendar', 'idx=' . $idx[1]);
            }
		}
		else
		{
			if (hasSetDate($rs['begin_time']))
			{
				//MIS#24968 by Small 2012/05/23
				$ary_hold_date = array($date1['year'],$date1['mon'],$date1['mday']);
				$ary_chg_date = array('*YEAR*','*MONTH*','*DAY*');
			
			
				$save_xml = sprintf($save_tpl, $date1['year'], $date1['mon']-1, $date1['mday'], $idx[0],
									$date1['hours'], $date1['minutes'], 23, 55,
									str_replace($ary_chg_date,$ary_hold_date,$MSG['left_quote'][$sysSession->lang]) . htmlspecialchars($titles[$sysSession->lang]) . $MSG['begin_to_exam'][$sysSession->lang], 4,
									str_replace($ary_chg_date,$ary_hold_date,$MSG['hold_something_today'][$sysSession->lang]) . htmlspecialchars($titles[$sysSession->lang]) . $MSG['attention_please1'][$sysSession->lang]
								   );
				$ret = saveMemo(domxml_open_mem($save_xml), 'course');
	    		if (empty($idx[0]) && preg_match('/<manifest id="(\d+)"/', $ret, $regs))
	                dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$rs['exam_id']}");

	            if ($idx[1] && !hasSetDate($rs['close_time'])) // 有第二筆行事曆，卻沒設結束日期
	            {
	            	dbDel('WM_calendar_exam', 'calendar_id=' . $idx[1]);
    	        	dbDel('WM_calendar', 'idx=' . $idx[1]);
	            }
			}

			if (hasSetDate($rs['close_time']))
			{
				//MIS#24968 by Small 2012/05/23
				$ary_hold_date = array($date1['year'],$date1['mon'],$date1['mday']);
				$ary_chg_date = array('*YEAR*','*MONTH*','*DAY*');
			
				$save_xml = sprintf($save_tpl, $date2['year'], $date2['mon']-1, $date2['mday'], $idx[1],
									0, 0, $date2['hours'], $date2['minutes'],
								str_replace($ary_chg_date,$ary_hold_date,$MSG['left_quote'][$sysSession->lang]) . htmlspecialchars($titles[$sysSession->lang]) . $MSG['stop_to_exam'][$sysSession->lang], 1,
								str_replace($ary_chg_date,$ary_hold_date,$MSG['today_is'][$sysSession->lang]) . htmlspecialchars($titles[$sysSession->lang]) . $MSG['attention_please2'][$sysSession->lang]
								   );
				$ret = saveMemo(domxml_open_mem($save_xml), 'course');
	    		if (empty($idx[1]) && preg_match('/<manifest id="(\d+)"/', $ret, $regs))
	                dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$rs['exam_id']}");

	            if ($idx[0] && !hasSetDate($rs['begin_time'])) // 有第一筆行事曆，卻沒設開始日期
	            {
	            	dbDel('WM_calendar_exam', 'calendar_id=' . $idx[0]);
    	        	dbDel('WM_calendar', 'idx=' . $idx[0]);
	            }
	        }
		}
    }


	//ACL begin
	if (QTI_which == 'exam') {
		include_once(sysDocumentRoot . '/lib/lib_calendar.php');
		include_once(sysDocumentRoot . '/lang/exam_teach.php');

		$sysSession->cur_func='1600200200';
		$sysSession->restore();
		if (!aclVerifyPermission(1600200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700200200';
		$sysSession->restore();
		if (!aclVerifyPermission(1700200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800200200';
		$sysSession->restore();
		if (!aclVerifyPermission(1800200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket = md5(sysTicketSeed . $course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');
	}
	if (!ereg('^[0-9]+(,[0-9]+)*$', $_POST['lists'])) {	// 檢查 lists
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
	   die('Fake lists.');
	}

	//dbSet('WM_qti_' . QTI_which . '_test', 'publish=(publish+0)%3+1', "exam_id in ({$_POST['lists']})");
	// 把關閉的狀態忽略掉
	dbSet('WM_qti_' . QTI_which . '_test', 'publish=(publish+0)%2+1,begin_time="0000-00-00 00:00:00",close_time="9999-12-31 00:00:00"', "exam_id in ({$_POST['lists']}) AND `type` != 5");

	if ($sysConn->Affected_Rows())
	{
            $rs = dbGetStMr('WM_qti_' . QTI_which . '_test', 'exam_id,title,publish,begin_time,close_time', "exam_id in ({$_POST['lists']})", ADODB_FETCH_ASSOC);
            // 與行事曆同步 start
            if ($rs)
                while($fields = $rs->FetchRow())
                    if ($fields['publish'] == 'action')
                    {
                    	if (QTI_which == 'exam')
                        sync_calendar($fields);
                    }
                    elseif ($fields['publish'] == 'prepare')
                    {
                    	$calendar_begin_type = QTI_which . '_begin';
                        $calendar_end_type   = QTI_which . '_end';
                        $calendar_delay_type = QTI_which . '_delay';
                        $calendar_ids = $sysConn->GetCol("select idx from WM_calendar where (relative_type='{$calendar_begin_type}' or relative_type='{$calendar_end_type}' or relative_type='{$calendar_delay_type}') and relative_id={$fields['exam_id']}");
                        if (is_array($calendar_ids) && count($calendar_ids))
                            dbDel('WM_calendar', 'idx in (' . implode(',', $calendar_ids) . ')');
                        dbDel('WM_calendar_exam', "exam_id={$fields['exam_id']}");
                    }
            // 與行事曆同步 end
    }

	header('Location: exam_maintain.php' . ($_POST['referer']?"?{$_POST['referer']}":''));
?>

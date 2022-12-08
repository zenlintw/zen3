<?php
    /**
     * 教室環境的 mooc menu
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/lib_cal_alert.php');
    require_once(sysDocumentRoot . '/lang/sysbar.php');
    require_once(sysDocumentRoot . '/mooc/models/forum.php');

    // Header
    $smarty->display('common/tiny_header.tpl');
    
    //  判斷 行事曆 是否有無 當天 要提醒的事 (begin)
	list($login_alert,$alert_num,$alert_date) = dbGetStSr('WM_cal_setting','login_alert,alert_num,alert_date',"username='{$sysSession->username}'", ADODB_FETCH_NUM);

	$sys_date = date('Y-m-d');

	if ($login_alert == 'N'){
		if (empty($alert_num) || empty($alert_date)){
			dbSet('WM_cal_setting',"alert_num=1,alert_date=NOW()", "username='{$sysSession->username}'");
		} else if ($sys_date != $alert_date){
			dbSet('WM_cal_setting',"alert_date=NOW()", "username='{$sysSession->username}'");

			if ($alert_num > 0){
				dbSet('WM_cal_setting',"alert_num=0", "username='{$sysSession->username}'");

				list($login_alert,$alert_num,$alert_date) = dbGetStSr('WM_cal_setting','login_alert,alert_num,alert_date',"username='{$sysSession->username}'", ADODB_FETCH_NUM);
			}
		}
	}

	// calendar kind
	$cal_type = array('personal','course','class','school');
	$cal_num = count($cal_type);
    
	// compute total calender number
    $total_cal = 0;

	$cal_msg = GetCalendarAlert();

	if ($doc = @domxml_open_mem(preg_replace('/\sxmlns\s*=\s*"[^"]*"\s/U', ' ', $cal_msg))) {

		// for begin
		for ($i = 0;$i < $cal_num;$i++){
			// init xpath
	   	 	$xpath = @xpath_new_context($doc);

	   	 	// 取得 個人、課程、班級、學校的 總數(num)  的值
	   	 	$obj          = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '[@num > 0]/memo');
	   	 	$page_nodeset = $obj->nodeset;
	   	 	$total_count  = count($page_nodeset);

	   	 	// 取得 行事曆 的 每篇 有無登入要顯示
			$type_obj   = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '/memo/alert_type/text()');
	   	 	$type_obj1  = $type_obj->nodeset[0];
			$login_type = $type_obj1->content;

	   	 	// 判斷 有無 num 的值
	   	 	if ($total_count > 0){
				if  (strpos($login_type,"login")!==false) {
	 			$cal_count[] = $total_count;
	   	 		}
	   	 	}
		}
   }

	if ($cal_count && count($cal_count) > 0){
		$cal_count = array_sum($cal_count);
	}else{
		$cal_count = 0;
	}

    // Content
    // 要切換到哪個選單項目 (Begin)
    $label = $sysSession->goto_label;
    $sysSession->goto_label = '';    // 用過就清除
    $sysSession->restore();
    // 要切換到哪個選單項目 (End)
    
	$isPopCal = popCal(5);
    
    // 取課程公告版編號
    $rsForum = new forum();
    $bulletinId = $rsForum->getCourseAnnId($sysSession->course_id, 1);

    if ($sysSession->username != 'guest') {
        $selary[10000000] = $MSG['my_courses'][$sysSession->lang];
        
        $selcs = array(); $teach = array();
        if ($rs = dbGetCourses('M.course_id,C.caption', $sysSession->username, $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']))
            while($fields = $rs->FetchRow())
                $teach[$fields['course_id']] = $fields['caption'];
        
        if ($rs = dbGetCourses('M.course_id,C.caption', $sysSession->username, $sysRoles['auditor']|$sysRoles['student']))
            while($fields = $rs->FetchRow())
                $selcs[$fields['course_id']] = $fields['caption'];
        
        $opts = '<option value="' . 10000000 . '">' . $MSG['my_courses'][$sysSession->lang] . '</option>';
        if (is_array($teach) && count($teach))
        {
            $opts .= '<optgroup label="' . $MSG['msg_list_teacher'][$sysSession->lang] . '">';
            foreach($teach as $tid => $title)
            {
                $opts .= '<option value="' . $tid . '"' . ($tid==$sysSession->course_id ? ' selected' : '') . '>' . fetchTitle($title) . '</option>';
            }
            $opts .= '</optgroup>';
            $opts .= '<optgroup label="' . $MSG['msg_list_student'][$sysSession->lang] . '">';
        }

        foreach($selcs as $tid => $title)
        {
            if (isset($teach[$tid])) continue;
            $opts .= '<option value="' . $tid . '"' . ($tid==$sysSession->course_id ? ' selected' : '') . '>' . fetchTitle($title) . '</option>';
        }

        if (is_array($teach) && count($teach)) $opts .= '</optgroup>';
        $selectCourseHtml = '<div class="" id="CourseDrop" style="padding-left:10px;padding-top:8px;width:100%;background-color:#ECECEC"><select name="selcourse" id="selcourse" class="cssInput" style="width:220px;font-size:1.2em;" onchange="if (this.value == 10000000){parent.s_sysbar.goPersonal();}else{parent.chgCourse(this.value, 1, 1);}" onclick="event.cancelBubble = true;">'.$opts.'</select></div>';
    }else{
        $selectCourseHtml = '';
    }

    $smarty->assign(array(
        'userTheme'             => '/theme/' . $sysSession->theme . '/learn/',
        'userLang'              => strtolower($sysSession->lang),
        'sysGotoLabel'          => $label,
        'fmDefault'             => 's_main',
        'MSG_SysError'          => $MSG['system_error'][$sysSession->lang],
        'MSG_NotSupportBrowser' => $MSG['not_support_browser'][$sysSession->lang],
        'MSG_CantLoadLib'       => $MSG['need_lib'][$sysSession->lang],
        'MSG_NoTitle'           => $MSG['no_title'][$sysSession->lang],
        'MSG_NEED_VARS'         => $MSG['msg_need_vars'][$sysSession->lang],
        'MSG_DATA_ERROR'        => $MSG['msg_data_error'][$sysSession->lang],
        'MSG_IP_DENY'           => $MSG['msg_ip_deny'][$sysSession->lang],
        'MSG_ADMIN_ROLE'        => $MSG['msg_admin_role'][$sysSession->lang],
        'MSG_DIRECTOR_ROLE'     => $MSG['msg_director_role'][$sysSession->lang],
        'MSG_TEACHER_ROLE'      => $MSG['msg_teacher_role'][$sysSession->lang],
        'MSG_STUEDNT_ROLE'      => $MSG['msg_student_role'][$sysSession->lang],
        'MSG_SLID_ERROR'        => $MSG['msg_sid_error'][$sysSession->lang],
        'MSG_CAID_ERROR'        => $MSG['msg_caid_error'][$sysSession->lang],
        'MSG_CSID_ERROR'        => $MSG['msg_csid_error'][$sysSession->lang],
        'MSG_CS_DELTET'         => $MSG['msg_course_delete'][$sysSession->lang],
        'MSG_CS_NOT_OPEN'       => $MSG['msg_course_close'][$sysSession->lang],
        'MSG_BAD_BOARD_ID'      => $MSG['msg_bad_board_id'][$sysSession->lang],
        'MSG_BAD_BOARD_RANGE'   => $MSG['msg_bad_board_range'][$sysSession->lang],
        'MSG_BOARD_NOTOPEN'     => $MSG['msg_board_notopen'][$sysSession->lang],
        'MSG_BOARD_CLOSE'       => $MSG['msg_board_closed'][$sysSession->lang],
        'MSG_BOARD_DISABLE'     => $MSG['msg_board_disable'][$sysSession->lang],
        'MSG_BOARD_TAONLY'      => $MSG['msg_board_taonly'][$sysSession->lang],
        'MSG_IN_CHAT_ROOM'      => $MSG['msg_in_chat'][$sysSession->lang],
        'cal_count'             => $cal_count,
        'login_alert'           => $login_alert,
        'alert_num'             => $alert_num,
        'alert_date'            => $alert_date,
        'sys_date'              => $sys_date,
        'isPopCal'              => $isPopCal,
        'showSidebar'           => (always_show_sidebar == 1) ? 'true' : 'false',
        'courseId'              => $sysSession->course_id,
        'course_bulletin'       => $bulletinId,
        'COURSE_DROPLIST'       => $selectCourseHtml
    ));
    $smarty->display('mooc_sysbar.tpl');

    // Footer
    $smarty->display('common/tiny_footer.tpl');

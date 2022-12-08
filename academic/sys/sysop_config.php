<?php
	/**
	 * 常數定義檔
	 *
	 * @since   2005/05/20
	 * @author  Amm Lee
	 * @version $Id: sysop_config.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
    require_once(sysDocumentRoot . '/lang/sysop_config.php');
    require_once(sysDocumentRoot . '/lang/app_server_push.php');
    require_once(sysDocumentRoot . '/lang/app_course_manage.php');

	// 判斷是否為管理者 BEGIN
	chkSchoolId('WM_manager');
	$cm = $sysConn->GetOne("select count(*) from WM_manager where username = '{$sysSession->username}' and (school_id = " . intval($_POST['sid']) . " or level & {$sysRoles['root']})");
	if ($cm == 0)
		die($MSG['illegal_access'][$sysSession->lang]);

	// 判斷是否為管理者 END

	/*
	 * 1. 讀取在 base 目錄下 有無 config.txt
	 * 2. 判斷此帳號是否具有管理者權限 且 是否可存取 config.txt
	 */
    function configRead($file) {
        if (file_exists($file))
        {
            $fp = fopen($file, 'r');
            // 讀出整個檔案內容
            $dec_content = fread($fp,filesize($file));
            // 解開編碼
            $org_content = other_dec($dec_content);
            $temp_array  = explode("\r\n", $org_content);
            if (is_array($temp_array))
            {
                $temp_count = count($temp_array);
                for ($i = 0; $i < $temp_count; $i++)
                {
                    $item = explode('@', trim($temp_array[$i]));

                    // $item[0] 欄位名稱
                    // $item[1] 欄位值
                    if (strpos($item[1], '(at)') !== false) $item[1] = str_replace('(at)', '@', $item[1]);
                    if ($item[0] == 'sysAvailableChars')
                        $Data[$item[0]] = explode(',', $item[1]);
                    else
                        $Data[$item[0]]= $item[1];
                }
            }
            fclose($fp);
            return $Data;
        }
    }
    // 取得目前學校常數
    $fname = sysDocumentRoot . '/base/' . trim($_POST['sid']) . '/config.txt';
    $Da = configRead($fname);
    // 取得所有學校常數 (MASTER)
    $fname2 = sysDocumentRoot . '/base/config.txt';
    $masterDa = configRead($fname2);

	$js = <<< BOF

	function check_data(){
		var obj = document.getElementById('configForm');
		var temp = '';

		if (obj == null) return false;

		// 開課限量 (sysCourseLimit)
		temp = obj.sysCourseLimit.value;

		if (temp.length == 0){
			alert("{$MSG['alert_course_limit_empty'][$sysSession->lang]}");
			obj.sysCourseLimit.focus();
			return false;
		}

		for(var i=0;i < temp.length;i++){
			if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
				alert("{$MSG['alert_course_limit_num'][$sysSession->lang]}");
				obj.sysCourseLimit.focus();
				return false;
			}
		}

		// 試題限量 (CourseQuestionsLimit)
		temp = obj.CourseQuestionsLimit.value;
		if (temp.length == 0){
			alert("{$MSG['alert_exam_limit_empty'][$sysSession->lang]}");
			obj.CourseQuestionsLimit.focus();
			return false;
		}

		for(var i=0;i < temp.length;i++){
			if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
				alert("{$MSG['alert_exam_limit_num'][$sysSession->lang]}");
				obj.CourseQuestionsLimit.focus();
				return false;
			}
		}

		// 試卷限量 (CourseExamQuestionsLimit)
		temp = obj.CourseExamQuestionsLimit.value;

		if (temp.length == 0){
			alert("{$MSG['alert_test_limit_empty'][$sysSession->lang]}");
			obj.CourseExamQuestionsLimit.focus();
			return false;
		}

		if (temp == 0){
			alert("{$MSG['alert_test_limit_zero'][$sysSession->lang]}");
			obj.CourseExamQuestionsLimit.focus();
			return false;
		}

		for(var i=0;i < temp.length;i++){
			if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
				alert("{$MSG['alert_test_limit_num'][$sysSession->lang]}");
				obj.CourseExamQuestionsLimit.focus();
				return false;
			}
		}

		// 系統Time out (systemTimeOutLimit)
		temp = obj.systemTimeOutLimit.value;

		if (temp.length == 0){
			alert("{$MSG['td_system_timeout_empty'][$sysSession->lang]}");
			obj.systemTimeOutLimit.focus();
			return false;
		}

		if (temp == 0){
			alert("{$MSG['td_system_timeout_num'][$sysSession->lang]}");
			obj.systemTimeOutLimit.focus();
			return false;
		}else{
			for(var i=0;i < temp.length;i++){
				if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
					alert("{$MSG['td_system_timeout_num'][$sysSession->lang]}");
					obj.systemTimeOutLimit.focus();
					return false;
				}
			}
		}

		// 帳號規則 (Account_firstchr)
		temp = obj.Account_firstchr.value;

		if (temp.length == 0){
			alert("{$MSG['td_account_first_empty'][$sysSession->lang]}");
			obj.Account_firstchr.focus();
			return false;
		}

		if (! ((temp == 0) || (temp == 1) || (temp == 2))){
			alert("{$MSG['td_account_first_num'][$sysSession->lang]}");
			obj.Account_firstchr.focus();
			return false;
		}

		// 學員帳號限量 (sysMaxUser)
		temp = obj.sysMaxUser.value;
		if (temp.length == 0){
			alert("{$MSG['td_account_num_empty'][$sysSession->lang]}");
			obj.sysMaxUser.focus();
			return false;
		}

		for(var i=0;i < temp.length;i++){
			if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
				alert("{$MSG['td_account_num2'][$sysSession->lang]}");
				obj.sysMaxUser.focus();
				return false;
			}
		}

		// 學員登入帳號限量 (sysMaxConcurrentUser)
		temp = obj.sysMaxConcurrentUser.value;
		if (temp.length == 0){
			alert("{$MSG['td_sysMaxConcurrentUser_empty'][$sysSession->lang]}");
			obj.sysMaxConcurrentUser.focus();
			return false;
		}

		for(var i=0;i < temp.length;i++){
			if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
				alert("{$MSG['td_sysMaxConcurrentUser_num'][$sysSession->lang]}");
				obj.sysMaxConcurrentUser.focus();
				return false;
			}
		}

		// 閱讀時數 (pathNodeTime)
		// 最短  (pathNodeTimeShortlimit)
		temp = obj.pathNodeTimeShortlimit.value;
		if (temp.length == 0){
			alert("{$MSG['td_pathNodeTimeShortlimit_empty'][$sysSession->lang]}");
			obj.pathNodeTimeShortlimit.focus();
			return false;
		}

		if (temp == 0){
			alert("{$MSG['td_pathNodeTimeShortlimit_zero'][$sysSession->lang]}");
			obj.pathNodeTimeShortlimit.focus();
			return false;
		}else{
			var min_num = 0;
			var short_tmp = '';
			var objRegExp = /\d/;
			for(var i=0;i < temp.length;i++){
				if(! objRegExp.test(temp.charAt(i))){
					alert("{$MSG['td_pathNodeTimeShortlimit_zero'][$sysSession->lang]}");
					obj.pathNodeTimeShortlimit.focus();
					return false;
				}
			}
		}
		// 最長  (pathNodeTimeLonglimit)
		temp = obj.pathNodeTimeLonglimit.value;
		if (temp.length == 0){
			alert("{$MSG['td_pathNodeTimeLonglimit_empty'][$sysSession->lang]}");
			obj.pathNodeTimeLonglimit.focus();
			return false;
		}

		if (temp == 0){
			alert("{$MSG['td_pathNodeTimeLonglimit_zero'][$sysSession->lang]}");
			obj.pathNodeTimeLonglimit.focus();
			return false;
		}else{
			for(var i=0;i < temp.length;i++){
				if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
					alert("{$MSG['td_pathNodeTimeLonglimit_zero'][$sysSession->lang]}");
					obj.pathNodeTimeLonglimit.focus();
					return false;
				}
			}
		}

		// 可包裝的課程內容大小 (CoursePackLimit)
		temp = obj.CoursePackLimit.value;
		if (temp.length == 0){
			alert("{$MSG['td_CoursePackLimit_empty'][$sysSession->lang]}");
			obj.CoursePackLimit.focus();
			return false;
		}

		if (temp == 0){
			alert("{$MSG['td_CoursePackLimit_num'][$sysSession->lang]}");
			obj.CoursePackLimit.focus();
			return false;
		}else{
			for(var i=0;i < temp.length;i++){
				if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
					alert("{$MSG['td_CoursePackLimit_num'][$sysSession->lang]}");
					obj.CoursePackLimit.focus();
					return false;
				}
			}
		}

		// 匯出試題題數 (ExamPackLimit)
		temp = obj.ExamPackLimit.value;
		if (temp.length == 0){
			alert("{$MSG['td_ExamPackLimit_empty'][$sysSession->lang]}");
			obj.ExamPackLimit.focus();
			return false;
		}
		for(var i=0;i < temp.length;i++){
			if (! ((temp.charAt(i) >= 0) && (temp.charAt(i) <= 9))){
				alert("{$MSG['td_ExamPackLimit_num'][$sysSession->lang]}");
				obj.ExamPackLimit.focus();
				return false;
			}
		}

		// 啟用 join net (joinet)
		temp = obj.joinet.value;

		switch (temp){
			case 'Y':
				if (obj.MMC_Server.value.length == 0){
					alert("{$MSG['td_MMC_Server_empty'][$sysSession->lang]}");
					obj.MMC_Server.focus();
					return false;
				}
				if (obj.MMC_Server_port.value.length == 0){
					alert("{$MSG['td_MMC_Server_port_empty'][$sysSession->lang]}");
					obj.MMC_Server_port.focus();
					return false;
				}
				break;
			case 'N':
				obj.MMC_Server.value = '';
				obj.MMC_Server_port.value = '';
				break;
		}

		// 啟用 Anicam Live (Anicam Live)
		temp = obj.anicam.value;

		switch (temp){
			case 'Y':
				if (obj.MMS_Server.value.length == 0){
					alert("{$MSG['td_MMS_Server_empty'][$sysSession->lang]}");
					obj.MMS_Server.focus();
					return false;
				}
				if (obj.MMS_Server_port.value.length == 0){
					alert("{$MSG['td_MMS_Server_port_empty'][$sysSession->lang]}");
					obj.MMS_Server_port.focus();
					return false;
				}
				break;
			case 'N':
				obj.MMS_Server.value = '';
				obj.MMS_Server_port.value = '';
				break;
		}

		// 可使用的語系
		var isSelect = false;	// 判斷是否至少有選擇一個語系以上
		var tr_obj = document.getElementById('tr_langs');
		if (typeof(tr_obj) != 'object') return false;
		var langs = tr_obj.getElementsByTagName('input');
		for(var i = 0; i < langs.length; i++) {
			if (langs[i].type == 'checkbox' && langs[i].checked) {
				isSelect = true;
				break;
			}
		}
		if (!isSelect) {
			alert("{$MSG['available_chars_empty'][$sysSession->lang]}");
			langs[0].focus();
			return false;
		}

        // 啟用 LCMS
        temp = false;
        if (obj.sysLcmsEnable.length > 1) {
            temp = obj.sysLcmsEnable[1].checked;
        }

        if (temp && (obj.sysLcmsHost.value === '')) {
            obj.sysLcmsHost.focus();
            alert("{$MSG['td_lcms_host_empty'][$sysSession->lang]}");
            return false;
        }



		var radio_obj = document.getElementsByTagName('input');

		total_len = radio_obj.length;

		for (var i=0;i < total_len;i++){
			if ((radio_obj[i].type == 'radio') &&
				(radio_obj[i].name != 'Grade_Calculate') &&
				(radio_obj[i].name != 'sysEnableCaptcha') &&
				(radio_obj[i].name != 'sysEnable3S') &&
				(radio_obj[i].name != 'sysLcmsEnable') &&
				(radio_obj[i].name != 'sysEnableMooc') &&
				(radio_obj[i].name != 'sysEnableApp') &&
				(radio_obj[i].name != 'sysEnableAppServerPush') &&
				(radio_obj[i].name != 'sysEnableAppCoursePicture') &&
				(radio_obj[i].name != 'sysEnableAppCourseExam') &&
				(radio_obj[i].name != 'sysEnableAppBackgroundLogo') &&
				(radio_obj[i].name != 'sysEnableAppQuestionnaire') &&
			    (radio_obj[i].name != 'show_sidebar')&&
			    (radio_obj[i].name != 'is_portal')&&
			    (radio_obj[i].name != 'is_independent')&&
			    (radio_obj[i].name != 'enableQuickReview')&&
			    (radio_obj[i].name != 'sysEnableAppISunFuDon')&&
			    (radio_obj[i].name != 'enableLiveService') &&
			    (radio_obj[i].name != 'EnableMulitServer')) {
				radio_obj[i].disabled = true;
			}
		}

        if ($('#system_pause').val() == 'Y'){
            if (!confirm("{$MSG['confirm_system_pause2'][$sysSession->lang]}")){
               $('#system_pause').val('N');
               $('#system_pause').focus();
               return false;
            }else{
                if (obj.system_pause_start_time.value == ''){
                    alert("{$MSG['errmsg_empty_pause_starttime'][$sysSession->lang]}");
                    obj.system_pause_start_time.focus();
                    return false;
                }

                if (obj.system_pause_end_time.value == ''){
                    alert("{$MSG['errmsg_empty_pause_endtime'][$sysSession->lang]}");
                    obj.system_pause_end_time.focus();
                    return false;
                }

                if (obj.system_pause_allowip.value == ''){
                    alert("{$MSG['errmsg_empty_pause_allowip'][$sysSession->lang]}");
                    obj.system_pause_allowip.focus();
                    return false;
                }
            }
        }

		return true;

	}

	function limit_show(title_tag,radio_val){
		var obj = document.getElementById(title_tag);
		if (obj == null) return false;
		switch (parseInt(radio_val)){
			case 0:
				obj.style.display = "none";
				obj.value = 0;
				break;
			case 1:
				obj.style.display = '';
				obj.value = '';
				break;
		}

	}

	function firstchar(title_tag,radio_val){
		var obj = document.getElementById(title_tag);
		if (obj == null) return false;
		obj.value = radio_val;
	}
    /* readonlyRadio(radio的name, 預設checked的radio的index) */
    function readonlyRadio(name,defaultCheckedIndex) {
        document.getElementsByName(name)[defaultCheckedIndex].checked = true; 
        return false; 
    }

    /* 顯示"啟用付費"選項 */
    function showPaid(obj) {
        if (1 == obj.value) {
            document.getElementById('paidDiv').style.display = 'inline-block';
        } else {
            document.getElementById('paidDiv').style.display = 'none';
            document.getElementById('paidDiv').getElementsByTagName('input')[0].checked = false;
        }
    }

    function showSocket(obj) {
        if (1 == obj.value) {
            document.getElementById('tr_socket').style.display = 'contents';
        } else {
            document.getElementById('tr_socket').style.display = 'none';
            $('#sysWebsocketHost').val('');
        }
        checkedTab1();
    }

    function checkedTab1()
	{
		var tab1Table = document.getElementById("tab1Table");
		var ii = tab1Table.rows.length-1;
		var cc = 1;
		for(var i=1; i<ii; i++)
			if (tab1Table.rows[i].style.display != "none")
			{
				cc ^= 1;
				tab1Table.rows[i].className = "bg0" + (cc + 3) + " font01";
			}
	}

    function confirmSystemPause(val){
        if (val != 'Y') return;
        if (!confirm("{$MSG['confirm_system_pause'][$sysSession->lang]}")){
            $('#system_pause').val('N');
        }
    }

    // 秀日曆的函數
    function Calendar_setup(ifd, fmt, btn, shtime)
    {
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

    window.onload = function(){
        Calendar_setup("system_pause_start_time", "%Y-%m-%d %H:%M", "system_pause_start_time", true);
        Calendar_setup("system_pause_end_time", "%Y-%m-%d %H:%M", "system_pause_end_time", true);
    }

BOF;

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
        showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
        $calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
        $calendar->load_files();
        showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs');
        echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, '', '', 'id="configForm" action="sysop_config1.php" method="post" enctype="multipart/form-data" style="display:inline" onsubmit="return check_data();" style="display: inline;"', false);
			showXHTML_input('hidden', 'sid', trim($_POST['sid']), '', '');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tab1Table" ');
				// html 標題
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center" width="200" ', $MSG['td_item'][$sysSession->lang]);
					showXHTML_td('align="center" width="310"', $MSG['td_content'][$sysSession->lang]);
					showXHTML_td('align="center" width="250"', $MSG['td_comment'][$sysSession->lang]);
				showXHTML_tr_E('');
				// 開課限量
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_course_limit'][$sysSession->lang]);
					showXHTML_td_B('');
						$radio_sysCourseLimit = max(0, $Da['sysCourseLimit']);
						$style_sysCourseLimit = ($radio_sysCourseLimit > 0) ? '' : 'style="display:none"';
						echo '<input type="radio" name="radio_sysCourseLimit" value="0" onclick="limit_show(\'sysCourseLimit\',this.value);" ' . (($radio_sysCourseLimit == 0)? ' checked':'') . '>' . $MSG['unlimit'][$sysSession->lang] . '<br>';
						echo '<input type="radio" name="radio_sysCourseLimit" value="1" onclick="limit_show(\'sysCourseLimit\',this.value);"' . (($radio_sysCourseLimit > 0)? ' checked':' ') . '>' . $MSG['limit'][$sysSession->lang];
						showXHTML_input('text' , 'sysCourseLimit', $radio_sysCourseLimit, '', 'size="10" maxlength="10" id="sysCourseLimit" class="cssInput" ' . $style_sysCourseLimit);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_course_limit_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 試題限量
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_exam_limit'][$sysSession->lang]);
					showXHTML_td_B('');
						$radio_CourseQuestionsLimit = max(0, $Da['CourseQuestionsLimit']);
						$style_CourseQuestionsLimit = ($radio_CourseQuestionsLimit > 0) ? '' : 'style="display:none"';
						echo '<input type="radio" name="radio_CourseQuestionsLimit" value="0" onclick="limit_show(\'CourseQuestionsLimit\',this.value);"' . (($radio_CourseQuestionsLimit == 0) ? 'checked':'') . '>' . $MSG['unlimit'][$sysSession->lang] . '<br>';
						echo '<input type="radio" name="radio_CourseQuestionsLimit" value="1" onclick="limit_show(\'CourseQuestionsLimit\',this.value);"' . (($radio_CourseQuestionsLimit > 0) ? 'checked':'') . '>' . $MSG['limit'][$sysSession->lang];
						showXHTML_input('text', 'CourseQuestionsLimit',$radio_CourseQuestionsLimit , '', 'size="10" maxlength="10" id="CourseQuestionsLimit" class="cssInput" ' . $style_CourseQuestionsLimit);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_exam_limit_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 試卷限量
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_test_limit'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('text', 'CourseExamQuestionsLimit', (($Da['CourseExamQuestionsLimit'] != '') ? intval($Da['CourseExamQuestionsLimit']) : '200'), '', 'size="25" maxlength="10" id="CourseExamQuestionsLimit" class="cssInput"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_test_limit_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 系統TimeOut
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_system_timeout'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('text', 'systemTimeOutLimit', (($Da['systemTimeOutLimit'] != '') ?  intval($Da['systemTimeOutLimit']) : '120'), '', 'size="25" maxlength="10" id="systemTimeOutLimit" class="cssInput"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_system_timeout_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 帳號規則
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_account_first'][$sysSession->lang]);
					showXHTML_td_B('');
						$radio_firstchr = max(0, $Da['Account_firstchr']);
						echo '<input type="radio" name="radio_Account_firstchr" value="0" onclick="firstchar(\'Account_firstchr\',this.value)" ' . (($radio_firstchr == 0)? ' checked':'')  . '>' . $MSG['number_character'][$sysSession->lang] . '<br>';
						echo '<input type="radio" name="radio_Account_firstchr" value="1" onclick="firstchar(\'Account_firstchr\',this.value)" ' . (($radio_firstchr == 1)? ' checked':'') . '>' . $MSG['number'][$sysSession->lang] . '<br>';
						echo '<input type="radio" name="radio_Account_firstchr" value="2" onclick="firstchar(\'Account_firstchr\',this.value)" ' . (($radio_firstchr == 2)? ' checked':'') . '>' . $MSG['character'][$sysSession->lang];
						showXHTML_input('text', 'Account_firstchr', $radio_firstchr, '', 'id="Account_firstchr" style="display:none"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_account_first_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 學員帳號限量
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_account_num'][$sysSession->lang]);
					showXHTML_td_B('');
						$radio_sysMaxUser = max(0, $Da['sysMaxUser']);
						$style_sysMaxUser = ($radio_sysMaxUser > 0) ? '' : 'style="display:none"';
						echo '<input type="radio" name="radio_sysMaxUser" value="0" onclick="limit_show(\'sysMaxUser\',this.value);" ' . (($radio_sysMaxUser == 0)? ' checked':'') . '>' . $MSG['unlimit'][$sysSession->lang] . '<br>';
						echo '<input type="radio" name="radio_sysMaxUser" value="1" onclick="limit_show(\'sysMaxUser\',this.value);"' . (($radio_sysMaxUser > 0)? ' checked':' ') . '>' . $MSG['limit'][$sysSession->lang];
						showXHTML_input('text', 'sysMaxUser', (($radio_sysMaxUser > 0) ? $Da['sysMaxUser'] : 0), '', 'size="10" maxlength="10" id="sysMaxUser" class="cssInput" ' . $style_sysMaxUser);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_account_num_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 學員登入帳號限量
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_sysMaxConcurrentUser'][$sysSession->lang]);
					showXHTML_td_B('');
						$radio_sysMaxConcurrentUser = max(0, $Da['sysMaxConcurrentUser']);
						$style_sysMaxConcurrentUser = ($radio_sysMaxConcurrentUser > 0) ? '' : 'style="display:none"';
						echo '<input type="radio" name="radio_sysMaxConcurrentUser" value="0" onclick="limit_show(\'sysMaxConcurrentUser\',this.value);" ' . (($radio_sysMaxConcurrentUser == 0)? ' checked':'') . '>' . $MSG['unlimit'][$sysSession->lang] . '<br>';
						echo '<input type="radio" name="radio_sysMaxConcurrentUser" value="1" onclick="limit_show(\'sysMaxConcurrentUser\',this.value);"' . (($radio_sysMaxConcurrentUser > 0)? ' checked':' ') . '>' . $MSG['limit'][$sysSession->lang];
						showXHTML_input('text', 'sysMaxConcurrentUser', (($radio_sysMaxConcurrentUser > 0) ? $Da['sysMaxConcurrentUser'] : 0), '', 'size="10" maxlength="10" id="sysMaxConcurrentUser" class="cssInput" ' .$style_sysMaxConcurrentUser);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_sysMaxConcurrentUser_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 帳號最短限字
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_sysAccountMinLen'][$sysSession->lang]);
					showXHTML_td_B('');
						$min_array = array();
						for ($min = 2; $min <= 20; $min++) {
							$min_array[$min] = $min;
						}
						if (strlen($Da['sysAccountMinLen']) > 0)
							$sysAccountMinLen = $Da['sysAccountMinLen'];
						else
							$sysAccountMinLen = 2;
						showXHTML_input('select', 'sysAccountMinLen', $min_array, $sysAccountMinLen, 'size="1"');
					showXHTML_td_E('');

					showXHTML_td('', $MSG['td_sysAccountMinLen_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 帳號最長限字
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_sysAccountMaxLen'][$sysSession->lang]);
					showXHTML_td_B('');
						if (strlen($Da['sysAccountMaxLen']) > 0)
							$sysAccountMaxLen = $Da['sysAccountMaxLen'];
						else
							$sysAccountMaxLen = 20;
						showXHTML_input('select', 'sysAccountMaxLen', $min_array, $sysAccountMaxLen, 'size="1"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_sysAccountMaxLen_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 閱讀時數
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_pathNodelimit'][$sysSession->lang]);
					showXHTML_td_B('');
						// 最短
						if (strlen($Da['pathNodeTimeShortlimit']) > 0)
							$pathNodeTimeShortlimit = $Da['pathNodeTimeShortlimit'];
						else
							$pathNodeTimeShortlimit = 3;

						// 最長
						if (strlen($Da['pathNodeTimeLonglimit']) > 0)
							$pathNodeTimeLonglimit = $Da['pathNodeTimeLonglimit'];
						else
							$pathNodeTimeLonglimit = 21600;

						showXHTML_input('text', 'pathNodeTimeShortlimit', $pathNodeTimeShortlimit, '', 'size="10" maxlength="10" id="pathNodeTimeShortlimit" class="cssInput" ');
						echo $MSG['td_second'][$sysSession->lang];
						echo ' ~ ';
						showXHTML_input('text', 'pathNodeTimeLonglimit', $pathNodeTimeLonglimit, '', 'size="10" maxlength="10" id="pathNodeTimeLonglimit" class="cssInput" ');
						echo $MSG['td_second'][$sysSession->lang];
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_pathNodelimit_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 可包裝的課程內容大小
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_CoursePackLimit'][$sysSession->lang]);
					showXHTML_td_B('');
						if (strlen($Da['CoursePackLimit']) > 0)
							$CoursePackLimit = $Da['CoursePackLimit'];
						else
							$CoursePackLimit = 512000;
						showXHTML_input('text', 'CoursePackLimit', $CoursePackLimit, '', 'size="10" maxlength="10" id="CoursePackLimit" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_CoursePackLimit_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 匯出試題題數
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_ExamPackLimit'][$sysSession->lang]);
					showXHTML_td_B('');
						if (strlen($Da['ExamPackLimit']) > 0)
							$ExamPackLimit = $Da['ExamPackLimit'];
						else
							$ExamPackLimit = 200;
						showXHTML_input('text', 'ExamPackLimit', $ExamPackLimit, '', 'size="10" maxlength="10" id="ExamPackLimit" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_ExamPackLimit_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 啟用 join net
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_joinet'][$sysSession->lang]);
					showXHTML_td_B('');
						$open_join = array('Y'=>$MSG['td_jn_use'][$sysSession->lang],'N'=>$MSG['td_jn_not_use'][$sysSession->lang]);
						showXHTML_input('select', 'joinet', $open_join, ((strlen($Da['joinet']) > 0) ? $Da['joinet'] : 'N'), 'size="1"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_joinet_default'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 定義使用 join net 的 MMC_Server
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_MMC_Server'][$sysSession->lang]);
					showXHTML_td_B('');
						if (strlen($Da['MMC_Server']) > 0)
							$MMC_Server = $Da['MMC_Server'];
						else
							$MMC_Server = '';
						showXHTML_input('text', 'MMC_Server', $MMC_Server,'', 'size="20" id="MMC_Server" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E('');

				// 定義使用 join net 的 MMC_Server_port
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_MMC_Server_port'][$sysSession->lang]);
					showXHTML_td_B('');
						if (strlen($Da['MMC_Server_port']) > 0)
							$MMC_Server_port = $Da['MMC_Server_port'];
						else
							$MMC_Server_port = '';
						showXHTML_input('text', 'MMC_Server_port', $MMC_Server_port,'', 'size="10" id="MMC_Server_port" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E('');

				// 定義使用 小組 joinnet
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"',$MSG['td_MMC_group_enable'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('select', 'joinnet_group', $open_join, ((strlen($Da['joinnet_group']) > 0) ? $Da['joinnet_group'] : 'N'), 'size="1"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_MMC_group_enable_desc'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 啟用 Anicam Live
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_anicam'][$sysSession->lang]);
					showXHTML_td_B('');
						$open_anicam = array();

						$open_anicam['Y'] = $MSG['td_jn_use'][$sysSession->lang];
						$open_anicam['N'] = $MSG['td_jn_not_use'][$sysSession->lang];

						$anicam = in_array($Da['anicam'], array('Y', 'N')) ? $Da['anicam'] : 'N';

						showXHTML_input('select', 'anicam', $open_anicam, $anicam, 'size="1"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_anicam_default'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 定義使用 Anicam 的 Media Server
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_MMS_Server'][$sysSession->lang]);
					showXHTML_td_B('');
						$MMS_Server = trim($Da['MMS_Server']);
						showXHTML_input('text', 'MMS_Server', $MMS_Server,'', 'size="20" id="MMS_Server" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E('');

				// 定義使用 Anicam 的 Media Server port
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_MMS_Server_port'][$sysSession->lang]);
					showXHTML_td_B('');
						if ($Da['MMS_Server_port'] != '')
							$MMS_Server_port = $Da['MMS_Server_port'];
						else
							$MMS_Server_port = '';
						showXHTML_input('text', 'MMS_Server_port', $MMS_Server_port,'', 'size="10" id="MMS_Server_port" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E('');

				// 啟用 Breeze Live
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze'][$sysSession->lang]);
					showXHTML_td_B('');
						$open_breeze['Y'] = $MSG['td_jn_use'][$sysSession->lang];
						$open_breeze['N'] = $MSG['td_jn_not_use'][$sysSession->lang];
						$breeze = (in_array($Da['breeze'], array('Y', 'N')))? $Da['breeze'] : 'N';
						showXHTML_input('select', 'breeze', $open_breeze, $breeze, 'size="1"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_breeze_default'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 定義使用 Breeze 的 MMS
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze_Server'][$sysSession->lang]);
					showXHTML_td_B('');
						$BREEZE_SERVER_ADDR = trim($Da['BREEZE_SERVER_ADDR']);
						showXHTML_input('text', 'BREEZE_SERVER_ADDR', $BREEZE_SERVER_ADDR,'', 'size="40" id="BREEZE_SERVER_ADDR" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E('');

				// 定義使用 Breeze 的 Account Administrator
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze_admin'][$sysSession->lang]);
					showXHTML_td_B('');
						$BREEZE_LOGIN = trim($Da['BREEZE_LOGIN']);
						showXHTML_input('text', 'BREEZE_LOGIN', $BREEZE_LOGIN,'', 'size="20" id="BREEZE_LOGIN" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('',$MSG['td_breeze_admin_desc'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze_pwd'][$sysSession->lang]);
					showXHTML_td_B('');
						$BREEZE_PASSWORD = trim($Da['BREEZE_PASSWORD']);
						showXHTML_input('text', 'BREEZE_PASSWORD', $BREEZE_PASSWORD,'', 'size="20" id="BREEZE_PASSWORD" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('',$MSG['td_breeze_pwd_desc'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze_accesskey'][$sysSession->lang]);
					showXHTML_td_B('');
						$BREEZE_ACCESSKEY = trim($Da['BREEZE_ACCESSKEY']);
						showXHTML_input('text', 'BREEZE_ACCESSKEY', $BREEZE_ACCESSKEY,'', 'size="20" id="BREEZE_ACCESSKEY" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('',$MSG['td_breeze_accesskey_desc'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze_user_group'][$sysSession->lang]);
					showXHTML_td_B('');
						$BREEZE_USER_GROUP = trim($Da['BREEZE_USER_GROUP']);
						showXHTML_input('text', 'BREEZE_USER_GROUP', $BREEZE_USER_GROUP,'', 'size="20" id="BREEZE_USER_GROUP" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('',$MSG['td_breeze_user_group_desc'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze_schoolid'][$sysSession->lang]);
					showXHTML_td_B('');
						$BREEZE_SCHOOL_ID = trim($Da['BREEZE_SCHOOL_ID']);
						showXHTML_input('text', 'BREEZE_SCHOOL_ID', $BREEZE_SCHOOL_ID,'', 'size="20" id="BREEZE_SCHOOL_ID" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('',$MSG['td_breeze_schoolid_desc'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze_meeting_folder'][$sysSession->lang]);
					showXHTML_td_B('');
						$BREEZE_WM_MEETING_FOLDER_ID = trim($Da['BREEZE_WM_MEETING_FOLDER_ID']);
						showXHTML_input('text', 'BREEZE_WM_MEETING_FOLDER_ID', $BREEZE_WM_MEETING_FOLDER_ID,'', 'size="20" id="BREEZE_WM_MEETING_FOLDER_ID" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('',$MSG['td_breeze_meeting_folder_desc'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_breeze_meeting_folder1'][$sysSession->lang]);
					showXHTML_td_B('');
						$BREEZE_WM_MEETING_FOLDER_ID1 = trim($Da['BREEZE_WM_MEETING_FOLDER_ID1']);
						showXHTML_input('text', 'BREEZE_WM_MEETING_FOLDER_ID1', $BREEZE_WM_MEETING_FOLDER_ID1,'', 'size="20" id="BREEZE_WM_MEETING_FOLDER_ID1" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('',$MSG['td_breeze_meeting_folder1_desc'][$sysSession->lang]);
				showXHTML_tr_E('');


				// 啟用 白板系統設定
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_white_board_system'][$sysSession->lang]);
					showXHTML_td_B('');
						$white_board_array = array();
						$white_board_array['Y'] = $MSG['td_jn_use'][$sysSession->lang];
						$white_board_array['N'] = $MSG['td_jn_not_use'][$sysSession->lang];
						$white_board = in_array($Da['White_Board'], array('Y', 'N')) ? $Da['White_Board'] : 'N';
						showXHTML_input('select', 'White_Board', $white_board_array, $white_board, 'size="1"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_system_default'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 啟用 語音討論版設定
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['td_voice_board'][$sysSession->lang]);
					showXHTML_td_B('');
						$voice_array = array();
						$voice_array['Y'] = $MSG['td_jn_use'][$sysSession->lang];
						$voice_array['N'] = $MSG['td_jn_not_use'][$sysSession->lang];
						$voicd_board = in_array($Da['Voice_Board'], array('Y', 'N')) ? $Da['Voice_Board'] : 'N';
						showXHTML_input('select', 'Voice_Board', $voice_array, $voicd_board, 'size="1"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['td_system_default'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 是否以學分數為加權數
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['grade_calculate'][$sysSession->lang]);
					showXHTML_td_B('');
						$voice_array = array();
						$radio_grade_calculate = in_array($Da['Grade_Calculate'], array('Y', 'N')) ? $Da['Grade_Calculate'] : 'Y';
						echo '<input type="radio" name="Grade_Calculate" value="Y" ' . (($radio_grade_calculate == 'Y')? ' checked':'') . '>' . $MSG['grade_calculate_Y'][$sysSession->lang] . '&nbsp;';
						echo '<input type="radio" name="Grade_Calculate" value="N" '. (($radio_grade_calculate == 'N')? ' checked':'') . '>' . $MSG['grade_calculate_N'][$sysSession->lang];

					showXHTML_td_E('');
					showXHTML_td('', $MSG['grade_calculate_note'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 可使用的語系
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="tr_langs"');
					showXHTML_td('align="right"', $MSG['available_chars'][$sysSession->lang]);
					showXHTML_td_B('');
						//$lang_arr = array('Big5', 'GB2312', 'en', 'EUC-JP', 'user_define');
						$lang_arr = array('Big5', 'GB2312', 'en');
						if (!isSet($Da['sysAvailableChars']) || !is_array($Da['sysAvailableChars']))	// 繁簡體為預設語系
							$Da['sysAvailableChars'] = explode(',', sysAvailableChars);
						foreach($lang_arr as $lang) {
							showXHTML_input('checkbox', 'sysAvailableChars[]', $lang, in_array($lang, $Da['sysAvailableChars']) ? 'checked=true': '', '', '');
							echo $MSG[$lang][$sysSession->lang] . '<br />';
						}
					showXHTML_td_E('');
					showXHTML_td('', $MSG['available_chars_note'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 是否啟用 Captcha 圖形檢核
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['use_captcha'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'sysEnableCaptcha', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnableCaptcha']);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['use_captcha_tips'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 是否啟用 3S 規則編輯
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['use_sss'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'sysEnable3S', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnable3S']);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['use_sss_tips'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 是否啟用 LCMS
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['use_lcms'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'sysLcmsEnable', array($MSG['td_jn_not_use'][$sysSession->lang], $MSG['td_jn_use'][$sysSession->lang]), $Da['sysLcmsEnable']);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['use_lcms_tips'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 設定 LCMS 的網址
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['lcms_host'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('text', 'sysLcmsHost', $Da['sysLcmsHost'], '', 'size="40" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['lcms_host_tips'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 是否啟用Mooc模組
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['use_mooc'][$sysSession->lang]);
					showXHTML_td_B('');
					    if (!isset($Da['sysEnableMooc'])) $Da['sysEnableMooc']=DEFAULT_ENABLE_MOOC;
					    showXHTML_input('radio', 'sysEnableMooc', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnableMooc']);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['use_mooc_tips'][$sysSession->lang]);
				showXHTML_tr_E('');

                // 學習環境側邊欄是否持續開啟
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['show_sidebar'][$sysSession->lang]);
                    showXHTML_td_B('');
                        if (!in_array($Da['show_sidebar'],array(0,1))) $Da['show_sidebar'] = 0;
                        showXHTML_input('radio', 'show_sidebar', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['show_sidebar']);
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['show_sidebar_tips'][$sysSession->lang]);
                showXHTML_tr_E('');

				// 是否啟用 訊息推播
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['app_server_push'][$sysSession->lang]);
                    showXHTML_td_B('');
                        showXHTML_input('radio', 'sysEnableAppServerPush', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnableAppServerPush']);
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['app_server_push_tips'][$sysSession->lang]);
                showXHTML_tr_E('');

                // 是否啟用 課程圖片設定(教師可用)
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['app_course_picture_setting'][$sysSession->lang]);
                    showXHTML_td_B('');
                        showXHTML_input('radio', 'sysEnableAppCoursePicture', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnableAppCoursePicture']);
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['app_course_picture_setting_tips'][$sysSession->lang]);
                showXHTML_tr_E('');

                // 行動 APP
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right" rowspan="3"', '行動 APP');
                    showXHTML_td_B('colspan="2"');
                        showXHTML_input('radio', 'sysEnableApp', array(1 => '啟用', 0=> '關閉'), $Da['sysEnableApp']);
                    showXHTML_td_E('');
                showXHTML_tr_E('');
                showXHTML_tr_B($col);
                    showXHTML_td_B('colspan="2"');
                        echo 'iOS 識別';
                        showXHTML_input('text', 'sysAppIosId', $Da['sysAppIosId'], '', 'size="40" class="cssInput" ');
                        echo '<br>iOS appstore url';
                        showXHTML_input('text', 'sysAppIosUrl', $Da['sysAppIosUrl'], '', 'size="40" class="cssInput" ');
                    showXHTML_td_E('');
                showXHTML_tr_E('');
                showXHTML_tr_B($col);
                    showXHTML_td_B('colspan="2"');
                        echo 'Android 識別';
                        showXHTML_input('text', 'sysAppAndroidId', $Da['sysAppAndroidId'], '', 'size="40" class="cssInput" ');
                        echo '<br>Android play store url';
                        showXHTML_input('text', 'sysAppAndroidUrl', $Da['sysAppAndroidUrl'], '', 'size="40" class="cssInput" ');
                    showXHTML_td_E('');                
                showXHTML_tr_E('');
                
                // 是否啟用 行動測驗設定
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['app_course_exam_setting'][$sysSession->lang]);
                    showXHTML_td_B('');
                        showXHTML_input('radio', 'sysEnableAppCourseExam', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnableAppCourseExam']);
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['app_course_exam_setting_tips'][$sysSession->lang]);
                showXHTML_tr_E('');

				// 是否啟用行動問卷
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['app_questionnaire_setting'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'sysEnableAppQuestionnaire', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnableAppQuestionnaire']);
					showXHTML_td_E('');
					showXHTML_td('', $MSG['item_questionnaire_tips'][$sysSession->lang]);
				showXHTML_tr_E('');

                // 是否啟用 APP 背景 Logo 自行置換
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['app_background_logo_setting'][$sysSession->lang]);
                    showXHTML_td_B('');
                        showXHTML_input('radio', 'sysEnableAppBackgroundLogo', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnableAppBackgroundLogo']);
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['app_background_logo_setting_tips'][$sysSession->lang]);
                showXHTML_tr_E('');
		
				// 是否啟用愛上互動
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['app_isunfudon_setting'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'sysEnableAppISunFuDon', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['sysEnableAppISunFuDon'], 'onchange="showSocket(this);"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['item_isunfudon_tips'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 設定 Websocket 的網址
				if ($Da['sysEnableAppISunFuDon']==1) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					$show = 'contents';
				} else {
					$show = 'none';
				}
				showXHTML_tr_B($col . ' id="tr_socket" style="display:'.$show.'"');
					showXHTML_td('align="right"', $MSG['socket_host'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('text', 'sysWebsocketHost', $Da['sysWebsocketHost'], '', 'id="sysWebsocketHost" size="40" class="cssInput" ');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['socket_host_tips'][$sysSession->lang]);
				showXHTML_tr_E('');
				
                
                // 是否為獨立校
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['independent_school'][$sysSession->lang]);
                    showXHTML_td_B('');
                        if (!in_array($Da['is_independent'],array(0,1))) $Da['is_independent'] = 1;
                        showXHTML_input('radio', 'is_independent', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['is_independent']);
                    showXHTML_td_E('');
                    showXHTML_td();
                showXHTML_tr_E('');
                
                // 是否為入口網校
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['portal_school'][$sysSession->lang]);
                    showXHTML_td_B('');
                        // 如果已有其他學校設定為入口網校且不為該學校
                        $hasPortalSchool = $masterDa['portal_school'] != null && $masterDa['portal_school'] != $_POST['sid'];
                        if (!in_array($Da['is_portal'],array(0,1))) $Da['is_portal'] = 0;
                        showXHTML_input('radio', 'is_portal', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['is_portal'], (($hasPortalSchool)?'onclick="return readonlyRadio(this.name,0);"':' onchange="showPaid(this);" '));
                        echo '<div id="paidDiv" style="'.(($Da['is_portal'])?'display: inline-block;':'display: none;').'">'; // '.($Da['is_portal'])?'':'style="display: none;"'.'>';
                            showXHTML_input('checkbox', 'enablePaid', '1', (($masterDa['enablePaid'] == 1) ? 'checked=true': ''), '', '');
                            echo $MSG['enable_paid'][$sysSession->lang];
                        echo '</div>';
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['portal_school_tips'][$sysSession->lang]);
                showXHTML_tr_E('');

                // 是否開啟快通車
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['enable_quick_review'][$sysSession->lang]);
                    showXHTML_td_B('');
                        if (!in_array($Da['enableQuickReview'],array(0,1))) $Da['enableQuickReview'] = 1;
                        showXHTML_input('radio', 'enableQuickReview', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['enableQuickReview']);
                    showXHTML_td_E('');
                    showXHTML_td();
                showXHTML_tr_E('');
                
                // 是否開啟直播服務
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['enable_live_service'][$sysSession->lang]);
                    showXHTML_td_B('');
                        if (!in_array($Da['enableLiveService'],array(0,1))) $Da['enableLiveService'] = 1;
                        showXHTML_input('radio', 'enableLiveService', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['enableLiveService']);
                    showXHTML_td_E('');
                    showXHTML_td();
                showXHTML_tr_E('');

				// 是否有多台主機進行更新 (wm3update)
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['enable_mulit_service'][$sysSession->lang]);
                    showXHTML_td_B('');
                        if (!in_array($Da['EnableMulitServer'],array(0,1))) $Da['EnableMulitServer'] = 0;
                        showXHTML_input('radio', 'EnableMulitServer', array($MSG['grade_calculate_N'][$sysSession->lang], $MSG['grade_calculate_Y'][$sysSession->lang]), $Da['EnableMulitServer']);
                    showXHTML_td_E('');
                    showXHTML_td();
                showXHTML_tr_E('');
				// 是否有多台主機進行更新 (wm3update)

				// 多站台主機設定 (wm3update)
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['td_MulitServer'][$sysSession->lang]);
                    showXHTML_td_B('colspan="2"');
                        if (empty($Da['MulitServer_content'])){
                            $Da['MulitServer_content'] = '';
                        }else{
                            $allAllowIps = explode(";", $Da['MulitServer_content']);
                            $Da['MulitServer_content'] = implode("\n",$allAllowIps);
                        }
                        showXHTML_input('textarea', 'MulitServer_content', $Da['MulitServer_content'],'', 'rows=10 cols=80 id="MulitServer_content" class="cssInput" ');
                    showXHTML_td_E('');
                showXHTML_tr_E('');
				// 多站台主機設定 (wm3update)

                // 啟用 停機公告
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['enable_system_pause'][$sysSession->lang]);
                    showXHTML_td_B('');
                        $open_system_pause = array('Y'=>$MSG['td_jn_use'][$sysSession->lang],'N'=>$MSG['td_jn_not_use'][$sysSession->lang]);
                        showXHTML_input('select', 'system_pause', $open_system_pause, ((strlen($Da['system_pause']) > 0) ? $Da['system_pause'] : 'N'), 'id="system_pause" size="1" onChange="confirmSystemPause(this.value);"');
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['td_system_pause_default'][$sysSession->lang]);
                showXHTML_tr_E('');

                // 停機起迄時間設定
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['td_system_pause_during'][$sysSession->lang]);
                    showXHTML_td_B('');
                        echo $MSG['system_pause_start_time'][$sysSession->lang];
                        showXHTML_input('text', 'system_pause_start_time', $Da['system_pause_start_time'],'', 'size="20" id="system_pause_start_time" class="cssInput" readonly');
                        echo $MSG['system_pause_end_time'][$sysSession->lang];
                        showXHTML_input('text', 'system_pause_end_time', $Da['system_pause_end_time'],'', 'size="20" id="system_pause_end_time" class="cssInput" readonly');
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['td_system_pause_during_comment'][$sysSession->lang]);
                showXHTML_tr_E('');

                // 定義停機期間可使用的測試IP
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['td_system_pause_allowip'][$sysSession->lang]);
                    showXHTML_td_B('');
                        // 為避免啟用停機時，忘了加上操作者的IP, 會無法再進入更改
                        if (empty($Da['system_pause_allowip'])){
                            $Da['system_pause_allowip'] = $_SERVER['REMOTE_ADDR'];
                        }else{
                            $allAllowIps = explode(";", $Da['system_pause_allowip']);
                            if (!in_array($_SERVER['REMOTE_ADDR'], $allAllowIps)) {
                                $allAllowIps[] = $_SERVER['REMOTE_ADDR'];
                            }
                            $Da['system_pause_allowip'] = implode("\n",$allAllowIps);
                        }
                        showXHTML_input('textarea', 'system_pause_allowip', $Da['system_pause_allowip'],'', 'rows=5 cols=40 id="system_pause_allowip" class="cssInput" ');
                    showXHTML_td_E('');
                    showXHTML_td('', $MSG['td_system_pause_allowip_comment'][$sysSession->lang]);
                showXHTML_tr_E('');

                // 停機公告內容
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right"', $MSG['td_system_pause_content'][$sysSession->lang]);
                    showXHTML_td_B('colspan="2"');
                        if (empty($Da['system_pause_content'])){
                            $Da['system_pause_content'] = $MSG['td_system_pause_content_default'][$sysSession->lang];
                        }else{
                            $Da['system_pause_content'] = str_replace('[newline]',"\n",$Da['system_pause_content']);
                        }
                        showXHTML_input('textarea', 'system_pause_content', $Da['system_pause_content'],'', 'rows=10 cols=80 id="system_pause_content" class="cssInput" ');
                    showXHTML_td_E('');
                showXHTML_tr_E('');

				// button
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="3" align="center"');
                        // 其他不在此頁面處理的 MASTER 常數
                        $MasterAry = array();
                        if (is_array($masterDa)) {
                            foreach($masterDa as $k => $v) {
                                if (in_array($k, $MasterAry)) {
                                    showXHTML_input('hidden', $k, $v, '', '', '');
                                }
                            }
                        }
                        // 其他不在此頁面處理的一般常數
                        $DaAry = array('openerDefaultTea');
                        foreach($Da as $k => $v) {
                            if (in_array($k, $DaAry)) {
                                showXHTML_input('hidden', $k, $v, '', '', '');
                            }                    
                        }
						showXHTML_input('submit', '', $MSG['btn_store'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
						showXHTML_input('reset', '', $MSG['btn_reset'][$sysSession->lang], '', 'id="btn_reset" class="cssBtn"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_tabFrame_E();
	showXHTML_body_E('');
	echo '</div>';

?>

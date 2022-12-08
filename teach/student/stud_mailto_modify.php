<?php
	/**
     * 目  的 : 自動點名規則新增修改
     *
     * @since   2005/09/28
     * @author  Edi Chen
     * @version $Id: stud_mailto_modify.php,v 1.1 2010/02/24 02:40:31 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
    require_once(sysDocumentRoot . '/lib/editor.php');
	require_once(sysDocumentRoot . '/teach/student/stud_mailto_lib.php');

	$ticket = md5(sysTicketSeed . 'modify' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$dd = array();
	$nid = intval(trim($_POST['nid']));
	if (!empty($nid))
	{
		$dd = dbGetStSr('WM_roll_call', '*', "serial_id={$nid}", ADODB_FETCH_ASSOC);
		$begin_time = $end_time = '';
	}
	else	// 新增點名條件時，點名期間預設為課程起迄時間
	{
		list($begin_time, $end_time) = dbGetStSr('WM_term_course', 'st_begin, st_end', 'course_id=' . $sysSession->course_id);
	}
	
	$mtTypeValue   = (!empty($dd['mtType']))    ? $dd['mtType']   :'login';
	$mtFilterValue = (!empty($dd['mtFilter']))  ? $dd['mtFilter'] :''     ;
	$mtOpValue     = (!empty($dd['mtOP']))      ? $dd['mtOP']     :''     ;
	$mtValue       = (strlen($dd['mtVal']) > 0) ? $dd['mtVal']    :''     ;
	$mtTeam        = (!empty($dd['team_id']))   ? $dd['team_id']  :''     ;
	$mtGroup       = (!empty($dd['group_id']))  ? $dd['group_id'] :''     ;

	$js = <<< BOF
	var MSG_ALL                   = "{$MSG['all'][$sysSession->lang]}";
	var MSG_FORMAT_ERROR          = "{$MSG['rs_format_error'][$sysSession->lang]}";
	var MSG_NEED_DATA             = "{$MSG['rs_need_data'][$sysSession->lang]}";
	var MSG_RESULT_EMPTY          = "{$MSG['rs_no_result'][$sysSession->lang]}";
	var MSG_BTN_SEND              = "{$MSG['rs_btn_send_mail'][$sysSession->lang]}";
	var MSG_SELECT_ALL            = "{$MSG['select_all'][$sysSession->lang]}";
	var MSG_CANCEL_ALL            = "{$MSG['select_cancel'][$sysSession->lang]}";
	var MSG_SUBJECT_Lesson        = "{$MSG['roll_call_mail_subject_default1'][$sysSession->lang]}";
	var MSG_SUBJECT_EXAM          = "{$MSG['roll_call_mail_subject_default2'][$sysSession->lang]}";
	var MSG_SUBJECT_HW            = "{$MSG['roll_call_mail_subject_default3'][$sysSession->lang]}";
	var MSG_SUBJECT_Questionnaire = "{$MSG['roll_call_mail_subject_default4'][$sysSession->lang]}";
	var MSG_CONTENT_Lesson        = "{$MSG['roll_call_mail_content_default1'][$sysSession->lang]}";
	var MSG_CONTENT_EXAM          = "{$MSG['roll_call_mail_content_default2'][$sysSession->lang]}";
	var MSG_CONTENT_HW            = "{$MSG['roll_call_mail_content_default3'][$sysSession->lang]}";
	var MSG_CONTENT_Questionnaire = "{$MSG['roll_call_mail_content_default4'][$sysSession->lang]}";
	var MSG_progress_minute       = "{$MSG['roll_minute'][$sysSession->lang]}";

	// 秀日曆的函數
	function Calendar_setup(ifd, fmt, btn, shtime) {
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

	/**
	 * 回自動點名頁面
	 */
	function goRollCall() {
		location.href = 'stud_mailto.php?tabs=2';
	}

	/**
	 * 儲存自動點名規則設定
	 */
	function saveSetting() {
		var pattern = /\d+/;
		if (!pattern.test(document.getElementById('mtVal').value)) {	// 檢查點名條件是否有輸入
			alert('{$MSG["rs_format_error"][$sysSession->lang]}');
			document.getElementById('mtVal').focus();
			return false;
		}
		
		if (document.setFm.frequence[0].checked && !pattern.test(document.getElementById('freq_once_day').value)) {
			alert('{$MSG["rs_need_data"][$sysSession->lang]}');
			document.getElementById('freq_once_day').click();
			return false;
		}
		
		// begin_time, end_time
		var begin_time = document.getElementById('begin_time').value.replace(/[\D]/ig, '');
		var end_time   = document.getElementById('end_time').value.replace(/[\D]/ig, '');
		if (begin_time == '')
		{
			alert('{$MSG["msg_no_begin_date"][$sysSession->lang]}');
			document.getElementById('begin_time').click();
			return false;
		}
		else if (end_time == '')
		{
			alert('{$MSG["msg_no_end_date"][$sysSession->lang]}');
			document.getElementById('end_time').click();
			return false;
		}
		else if (begin_time != '' && end_time != '' && parseInt(begin_time) >= end_time)
		{
			alert('{$MSG["msg_date_error"][$sysSession->lang]}');
			document.getElementById('begin_time').click();
			return false;
		}
		
		document.getElementById('setFm').submit();
	}

	window.onload = function () {
		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		if ((typeof(xmlTeam) != "object") || (xmlTeam == null)) xmlTeam = XmlDocument.create();
		getGroup();

		var mtFilterValue = '{$mtFilterValue}';
		if (mtFilterValue.length > 0)
		{
			getFilter('{$mtTypeValue}',mtFilterValue);
		}else{
			getFilter('{$mtTypeValue}');
		}

		var mtOpValue = '{$mtOpValue}';
        var mtValue   = '{$mtValue}';
        var mtTeam    = '{$mtTeam}';
        var mtGroup   = '{$mtGroup}';
        
        if (mtOpValue.length > 0)
        {
            for(i=0;i<document.setFm.mtOP.options.length; i++)
            {
                if (document.setFm.mtOP.options[i].value == mtOpValue)
                {
                    document.setFm.mtOP.options[i].selected = true;
                    break;
                }
            }
        }
        if (mtValue.length > 0)
        {
            if (document.setFm.mtVal.type == 'text')
            {
                document.setFm.mtVal.value = mtValue;
            }else if (document.setFm.mtVal.type == 'select'){
                for(i=0;i<document.setFm.mtVal.options.length; i++)
                {
                    if (document.setFm.mtVal.options[i].value == mtValue)
                    {
                        document.setFm.mtVal.options[i].selected = true;
                        break;
                    }
                }
            }
        }
		Calendar_setup("begin_time"		, "%Y-%m-%d", "begin_time"		, false);
		Calendar_setup("end_time"		, "%Y-%m-%d", "end_time"		, false);
		Calendar_setup("freq_once_day"	, "%Y-%m-%d", "freq_once_day"	, false);
		
		$("#mtTeam option[value='"+mtTeam+"']").attr('selected','selected');
		if (mtTeam!='') {
		    buildGroup(mtTeam);
		    $("#mtGroup option[value='"+mtGroup+"']").attr('selected','selected');
		}

	};

	/**
	 * 縮減附檔
	 **/
	// #47450 Chrome [教師/人員管理/寄信與點名/自動點名設定/新增] 只選取一個檔案，按下「縮減附檔」，不會把檔案清掉：改仿審核學員的操作方式
    function cut_attachs(){
        /*
		var curNode = document.getElementById('upload_base');
		var delNode = curNode.previousSibling;
		if (files <= 1){
            var curNode = document.getElementById('mail_attach_files[]');
            curNode.value = "";
                $(curNode).after($(curNode).clone(true)).remove();
			return;
		}
		
		delNode.parentNode.removeChild(delNode);
		*/
		/* #56039 原方式在更多附件將 chrome 分流，縮減檔案未分流，造成IE8會砍表格 */
		var curNode = getNextSiblingTd(getFirstChild(document.getElementById('upload_box')));
		var delNode = getLastChild(curNode);
		if (files <=1) {
			/* IE 的安全機制，無法用value=""來清空檔案，使用 clone 方式 */
			var newNode = delNode.cloneNode(true);
			newNode.getElementsByTagName("input")[0].value = "";
			delNode.parentNode.replaceChild(newNode, delNode);
			return;
		}
		delNode.parentNode.removeChild(delNode);
		files--;
	}

BOF;

	showXHTML_head_B($MSG['mail_roll_call'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_script('inline', $js);
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', 'stud_mailto.js');
        showXHTML_script('include', '/lib/jquery/jquery.min.js');
	showXHTML_head_E();
	showXHTML_body_B('');
		echo '<div align="center">';
		$ary = array();
		if (empty($nid)) {
			$ary[] = array($MSG['roll_call_rull_add'][$sysSession->lang], 'tabs_host');
		} else {
			$ary[] = array($MSG['roll_call_rull_modify'][$sysSession->lang], 'tabs_host');
		}
		showXHTML_tabFrame_B($ary, 1, 'setFm', '', 'action="stud_mailto_modify1.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			showXHTML_input('hidden', 'nid', $nid, '', '');
			$ticket = md5(sysTicketSeed . 'modify' . $_COOKIE['idx'] . $nid);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_table_B('align="center" width="760" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
				$col = 'class="cssTrOdd"';
				// 說明文字
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('colspan="3"', $MSG['mail_roll_call_system_readme2'][$sysSession->lang]);
				showXHTML_tr_E();

				// 啟用
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead" width="100"', $MSG['roll_call_enable'][$sysSession->lang]);
					showXHTML_td_B('width="450"');
						$ary = array (
								'enable' 	=> $MSG['roll_call_enable'][$sysSession->lang],
								'disable'	=> $MSG['roll_call_disable'][$sysSession->lang]
								);
						showXHTML_input('radio', 'enable', $ary, $dd['enable'] ? $dd['enable'] : 'enable', '', '');
					showXHTML_td_E('');
					showXHTML_td('width="210"', $MSG['roll_call_help1'][$sysSession->lang]);
				showXHTML_tr_E();

				// 點名對象
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_roles'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('select', 'role', $mt_roles, $dd['role'] ? $dd['role'] : 'student', 'id="role" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('');
				showXHTML_tr_E();

				// 分組
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_group'][$sysSession->lang]);
					showXHTML_td_B('');
						echo '<span id="spanTeam"></span>&nbsp;<span id="spanGroup"></span>';
					showXHTML_td_E();
					showXHTML_td('&nbsp;');
				showXHTML_tr_E();

				// 點名條件
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_cond'][$sysSession->lang]);
					showXHTML_td_B('');
					if (empty($dd['mtType']))
					{
					    showXHTML_input('select', 'mtType', $mt_type, 'login', 'id="mtType" class="cssInput" onchange="getFilter(this.value)"');
					    echo '<span id="spanType">&nbsp;</span>&nbsp;<span id="spanOP">&nbsp;</span>';
					}else{
					    showXHTML_input('select', 'mtType', $mt_type, $dd['mtType'], 'id="mtType" class="cssInput"  onchange="getFilter(this.value)"');
					    echo '<span id="spanType">&nbsp;</span>&nbsp;<span id="spanOP">&nbsp;</span>';
					}
					showXHTML_td_E();
					showXHTML_td('&nbsp;');
				showXHTML_tr_E();

				// 頻率
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_freq'][$sysSession->lang]);
					showXHTML_td_B('');
						// 單次
						$frequence = ($dd['frequence'] ? $dd['frequence'] : 'once');
						$freq_val = ($dd['freq_extra'] ? $dd['freq_extra'] : '');
						echo '<input type="radio" id="sysRadioBtn4" name="frequence" value="once" '. ($frequence == 'once' ? 'checked' : '') .'><label for="sysRadioBtn4">',$MSG['roll_call_freq_once'][$sysSession->lang],'</label>';
						showXHTML_input('text', 'freq_once_day', $frequence == 'once' ? $freq_val : '', '', 'id="freq_once_day" readonly="readonly" class="cssInput"');
						// 每天
						echo '<br><input type="radio" id="sysRadioBtn5" name="frequence" value="day" '. ($frequence == 'day' ? 'checked' : '') .'><label for="sysRadioBtn5">',$MSG['roll_call_freq_day'][$sysSession->lang],'</label>';
						// 每週
						echo '<br><input type="radio" id="sysRadioBtn6" name="frequence" value="week" '. ($frequence == 'week' ? 'checked' : '') .'><label for="sysRadioBtn6">',$MSG['roll_call_freq_week'][$sysSession->lang],'</label>';
						showXHTML_input('select', 'freq_week_day', $mt_days, $frequence == 'week' ? $freq_val : '', 'id="freq_week_day" class="cssInput"');
						echo '<label for="sysRadioBtn6">' , $MSG['once'][$sysSession->lang] , '</label>';
						// 每月
						if ($frequence != 'month') $freq_val = 1;
						echo '<br><input type="radio" id="sysRadioBtn7" name="frequence" value="month" '. ($frequence == 'month' ? 'checked' : '') .'><label for="sysRadioBtn7">',$MSG['roll_call_freq_month'][$sysSession->lang],'</label>';
						echo str_repeat(chr(9), $sysIndent), '<select id="freq_month_day" name="freq_month_day">';
						for ($i = 1; $i <= 31; $i++) echo '<option value="',$i,'" ', $i == $freq_val ? 'selected="selected"' : '','>', $i, '</option>';
						echo '</select>',str_repeat(chr(9), $sysIndent),'<label for="sysRadioBtn7">' , $MSG['once'][$sysSession->lang] , '</label>';
					showXHTML_td_E();
					showXHTML_td('', $MSG['roll_call_help2'][$sysSession->lang]);
				showXHTML_tr_E();

				// 點名期間
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_duration'][$sysSession->lang]);
					showXHTML_td_B('');
						if ($dd['begin_time'] && $dd['begin_time'] != '0000-00-00 00:00:00') $begin_time = date('Y-m-d', strtotime($dd['begin_time']));
						if ($dd['end_time']   && $dd['end_time']   != '0000-00-00 00:00:00') $end_time   = date('Y-m-d', strtotime($dd['end_time']));
						echo $MSG['from'][$sysSession->lang];
						showXHTML_input('text', 'begin_time', $begin_time, '', 'id="begin_time" readonly="readonly" class="cssInput"');
						echo $MSG['to'][$sysSession->lang];
						showXHTML_input('text', 'end_time', $end_time, '', 'id="end_time" readonly="readonly" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('', $MSG['roll_call_help3'][$sysSession->lang]);
				showXHTML_tr_E();

				// 通知信標題
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_mail_subject'][$sysSession->lang]);
					showXHTML_td_B('');
						// #47161 Chrome 增加id屬性
                        showXHTML_input('text', 'mail_subject', $dd['mail_subject'] ? $dd['mail_subject'] : $MSG['roll_call_mail_subject_default'][$sysSession->lang], '', 'class="cssInput" size="64" maxlength="200" id="mail_subject"');

					showXHTML_td_E();
					showXHTML_td('', $MSG['roll_call_help4'][$sysSession->lang]);
				showXHTML_tr_E();

				// 副本收件者
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_mail_cc'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('checkbox', 'mail_cc', '', isSet($dd['mail_cc']) ? ($dd['mail_cc'] ? 'checked' : '') : 'checked', 'id="mail_cc"', '');
						echo '<label for="mail_cc">', $MSG['roll_call_mail_cc_direct'][$sysSession->lang], '</label>';
					showXHTML_td_E('');
					showXHTML_td('&nbsp;');
				showXHTML_tr_E();

				// 內容
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_mail_content'][$sysSession->lang]);
					showXHTML_td_B('');
						$oEditor = new wmEditor;
						$oEditor->addContType('isHTML', 1);
						if ($dd['mail_content']) $oEditor->setValue($dd['mail_content']);
						$oEditor->generate('mail_content', '450');
					showXHTML_td_E();
					showXHTML_td('&nbsp;');
				showXHTML_tr_E();

				// 附件列表
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				//Chrome
				showXHTML_tr_B($col . "id=\"upload_list\"");
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_mail_attach_list'][$sysSession->lang]);
					showXHTML_td_B('');

					if ($dd['mail_attach'] && $dd['serial_id']) {
						$save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/rollcall/%d/',
		  				 							$sysSession->school_id,
		  				 							$sysSession->course_id,
		  				 							$dd['serial_id']);
		  				$save_uri = substr($save_path, strlen(sysDocumentRoot));

						$attach = ereg('^a:[0-9]+:{s:', $dd['mail_attach']) ? unserialize($dd['mail_attach']) : array();

						foreach($attach as $k => $v)
							if (is_file($save_path . $v)) {
								echo $MSG['roll_call_rm'][$sysSession->lang];
								showXHTML_input('checkbox', 'rm_files[]', rawurlencode($v));
								printf('<a href="%s%s" target="_blank" class="cssAnchor">%s</a><br />', $save_uri, rawurlencode($v), htmlspecialchars($k));
							}
					}
					showXHTML_td_E('');
					showXHTML_td('');
				showXHTML_tr_E();

				// 附件上傳
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				// Chrome
				showXHTML_tr_B($col . "id=\"upload_box\"");
					showXHTML_td('class="cssTrHead"', $MSG['roll_call_mail_attach_upload'][$sysSession->lang]);
					showXHTML_td_B('');
						echo '<span style="display: block;">';
						showXHTML_input('file', 'mail_attach_files[]', '', '', 'size="50" class="cssInput"');
						echo '</span>';
					showXHTML_td_E();
					$msgAry = array('%MIN_SIZE%'	=> 	'<span style="color: red; font-weight:bold">' . ini_get('upload_max_filesize') . '</span>',
								 	'%MAX_SIZE%'	=>	'<span style="color: red; font-weight:bold">' . ini_get('post_max_size') . '</span>'
								);
					showXHTML_td('', strtr($MSG['roll_call_help5'][$sysSession->lang], $msgAry));
				showXHTML_tr_E();

				// 確定 取消按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				//Chrome
				showXHTML_tr_B($col . "id=\"upload_base\"");
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['more_attachments'][$sysSession->lang], '', 'class="cssBtn" onclick="more_file(this);"');
						showXHTML_input('button', '', $MSG['less_attachments'][$sysSession->lang], '', 'class="cssBtn" onclick="cut_attachs();"');
						echo '&nbsp;';
						showXHTML_input('button', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn" onclick="saveSetting()"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="goRollCall()"');
					showXHTML_td_E();
				showXHTML_tr_E();

			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

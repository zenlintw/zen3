<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/06                                                            *
	 *		work for  : grade manage                                                          *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/grade.php');
	require_once(sysDocumentRoot . '/lang/peer_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '1400100200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (ereg('^[0-9]+$', $_GET['gid'])){

	$RS         = dbGetStSr('WM_grade_list', '*', "grade_id={$_GET['gid']}", ADODB_FETCH_ASSOC);
	$titles     = unserialize($RS['title']);
	$today_time = date('Y-m-d H:i');

	$alert_msg  = ($_GET['status'] == 1) ? ('alert("' . $MSG['save_complete'][$sysSession->lang] . '");') : '';

	// 開始 output HTML
	showXHTML_head_B($MSG['add'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	  $scr = <<< EOB
function maskKeyPress(objEvent) {
    var iKeyCode = objEvent.which ? objEvent.which : objEvent.keyCode;
    switch (iKeyCode) {
        case 8:
        case 9:
        case 13:
        case 27:
        case 35:
        case 36:
        case 46:
        case 190:
            return true;
        default:
            return (iKeyCode >= 48 && iKeyCode <= 57);
    }
}

function checkFields() {
	if (!chk_multi_lang_input(1, true, "{$MSG['title_least_one'][$sysSession->lang]}")) return false;
        var objForm = document.getElementById('saveForm');
	var d1 = objForm.chk_begin.checked ? objForm.publish_begin.value : false;
	var d2 = objForm.chk_end.checked   ? objForm.publish_end.value   : false;
	if (d1 && d2 && d1 >= d2 && $('#sysRadioBtn2').attr('checked') === 'checked') {
		alert('{$MSG['begintime_less_then_overtime'][$sysSession->lang]}');
		return false;
	}

    // 成績公告日期應大於評分結束日期與作答結束日期
    if ($('#source').val() === '4' && $('#sysRadioBtn3').attr('checked') === 'checked' && $("input[name='chk_begin']").attr('checked') === 'checked') {
        if ($('#close_time').val() !== '9999-12-31 00:00:00' && $('#publish_begin').val() < $('#close_time').val()) {
            alert('{$MSG['publish_greater_answer'][$sysSession->lang]}');
            return false;
        }

        if ($('#end_date').val() !== '9999-12-31 00:00:00' && $('#publish_begin').val() < $('#end_date').val()) {
            alert('{$MSG['publish_greater_rating'][$sysSession->lang]}');
            return false;
        }
    }
    noBtn = false;
    objForm.submit();
}

function chgPublish(val) {
    var obj = document.getElementById('span_publish');
    if (obj) {
        obj.style.display = val == 'yes' ? '' : 'none';
    }
}

// 控制輸入日期的input是否display
function showDateInput(objName, state) {
    var obj = document.getElementById(objName);
    if (obj != null)
        obj.style.display = state ? "" : "none";
}
// 秀日曆的函數
function Calendar_setup(ifd, fmt, btn, shtime) {
    Calendar.setup({
        inputField: ifd,
        ifFormat: fmt,
        showsTime: shtime,
        time24: true,
        button: btn,
        singleClick: true,
        weekNumbers: false,
        step: 1
    });
}
var noSave = false;
var noBtn = true;
var st_id = '{$sysSession->cur_func}{$sysSession->course_id}{$_GET['gid ']}';
var isMZ = (navigator.userAgent.indexOf(' MSIE ') == -1); // 瀏覽器是否為 Mozilla
window.onload = function() {
    Calendar_setup('publish_begin', '%Y-%m-%d %H:%M', 'publish_begin', true);
    Calendar_setup('publish_end', '%Y-%m-%d %H:%M', 'publish_end', true);
};

/* 客製 begin */
var wtuc_get_keypress = false; /* 是否有輸入或點 input 的欄位 */

if (isMZ) window.captureEvents(Event.KEYPRESS);

function captureKey(e) {
    if ((event.keyCode > 0) && (!wtuc_get_keypress)) {
        wtuc_get_keypress = true;
    }
}
document.onkeypress = captureKey;

window.onbeforeunload = function() {
    if (wtuc_get_keypress && noBtn) {
        return "{$MSG['wtuc_edit_grade'][$sysSession->lang]}";

    }
};

EOB;
      showXHTML_script('include', '/lib/jquery/jquery-1.7.2.min.js');
	  showXHTML_script('inline', $scr);
	  $xajax_save_temp->printJavascript('/lib/xajax/');
	  $calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	  $calendar->load_files();
	showXHTML_head_E();
	showXHTML_body_B();
		if ($RS)
		{
			$ary = array(array($MSG['grade_property'][$sysSession->lang], 'tabsSet1'));
			showXHTML_tabFrame_B($ary, 1, 'saveForm', '', 'action="grade_modify2.php" method="POST" style="display: inline" ');
			  showXHTML_table_B('id="tabsSet1" border="0" cellpadding="3" cellspacing="1" class="box01"');

				$arr_names = array('Big5'		=>	'title[Big5]',
							   	   'GB2312'		=>	'title[GB2312]',
							   	   'en'			=>	'title[en]',
							   	   'EUC-JP'		=>	'title[EUC-JP]',
							   	   'user_define'=>	'title[user_define]'
							   	   );
				showXHTML_tr_B('class="bg04 font01"');
					showXHTML_td('width="100"', $MSG['title'][$sysSession->lang]);
					showXHTML_td_B();
						$multi_lang = new Multi_lang(false, $titles, 'class="bg04 font01"'); // 多語系輸入框
						$multi_lang->show(true, $arr_names);
					showXHTML_td_E();
				showXHTML_tr_E();

			    showXHTML_tr_B('class="bg03 font01"');
			      showXHTML_td('width="100"', $MSG['percent'][$sysSession->lang]);
			      showXHTML_td_B('');
			        showXHTML_input('text', 'percent', $RS['percent'], '', 'size="4" class="box02" onkeypress="return maskKeyPress(event);"'); echo "%";
			      showXHTML_td_E();
			    showXHTML_tr_E();
			    showXHTML_tr_B('class="bg04 font01"');
			      showXHTML_td('width="100"', $MSG['publish'][$sysSession->lang]);
			      showXHTML_td_B('');
			      	$isPublish = !($RS['publish_begin'] == '0000-00-00 00:00:00' && $RS['publish_end'] == '0000-00-00 00:00:00');
			      	$isBgnChk  = $isPublish && $RS['publish_begin'] != '1970-01-01 00:00:00';
			      	$isEndChk  = $isPublish && $RS['publish_end']   != '9999-12-31 00:00:00';
			      	showXHTML_table_B('border="0"');
              			showXHTML_tr_B('class="cssTrOdd"');
              				showXHTML_td_B();
              					showXHTML_input('radio', 'rdoPublish', array('yes'=> $MSG['msgPublish'][$sysSession->lang]), $isPublish ? 'yes' : 'no', 'onclick="chgPublish(this.value)"');
              				showXHTML_td_E();
              				showXHTML_td_B('');
              					echo '<span id="span_publish" style="display: ' . ($isPublish ? '' : 'none') . '">';
			      				showXHTML_input('checkbox', 'chk_begin', '', $isBgnChk, 'onclick="showDateInput(\'span_publish_begin\', this.checked)"');
              					echo $MSG['msg_enable_begin'][$sysSession->lang], '<span id="span_publish_begin" style="display: ', ($isBgnChk ? '' : 'none'), ';">', $MSG['msg_enable_date'][$sysSession->lang];
              					showXHTML_input('text', 'publish_begin', $isBgnChk ? substr($RS['publish_begin'], 0, 16): $today_time, '', 'id="publish_begin" readonly="readonly" class="cssInput" ');
              					echo '</span><br>';
              					showXHTML_input('checkbox', 'chk_end', '', $isEndChk, 'onclick="showDateInput(\'span_publish_end\', this.checked)"');
              					echo $MSG['msg_enable_end'][$sysSession->lang], '<span id="span_publish_end" style="display: ', ($isEndChk ? '' : 'none'), ';">', $MSG['msg_enable_date'][$sysSession->lang];
              					showXHTML_input('text', 'publish_end', $isEndChk ? substr($RS['publish_end'], 0, 16): $today_time, '', 'id="publish_end" readonly="readonly" class="cssInput" ');
              					echo '</span></span>';

                                // 如果為同儕互評增加顯示評分結束日期
                                showXHTML_input('hidden', 'source', $RS['source']);
                                if ($RS['source'] === '4') {
                                    list($close_time, $end_date)       = dbGetStSr('WM_qti_peer_test', 'close_time, end_date', "exam_id={$RS['property']}", ADODB_FETCH_NUM);
                                    showXHTML_input('hidden', 'close_time', $close_time);
                                    showXHTML_input('hidden', 'end_date', $end_date);
                                }
              					showXHTML_td_E();
              			showXHTML_tr_E();
              			showXHTML_tr_B('class="cssTrOdd"');
              				showXHTML_td_B();
              					showXHTML_input('radio', 'rdoPublish', array('no'=> $MSG['msgNoPublish'][$sysSession->lang]), $isPublish ? 'yes' : 'no', 'onclick="chgPublish(this.value)"');
              				showXHTML_td_E();
              				showXHTML_td('', '&nbsp;');
              			showXHTML_tr_E();
              		showXHTML_table_E();
			      showXHTML_td_E();
			    showXHTML_tr_E();
				showXHTML_tr_B('class="bg03 font01"');
				  showXHTML_td('width="100"', $MSG['student_grade'][$sysSession->lang]);
				  showXHTML_td_B('');
				  /*showXHTML_td_B('colspan="2"');
				    showXHTML_input('hidden', 'grade_id', $_GET['gid']);
				    showXHTML_input('submit', '', $MSG['complete_modify'][$sysSession->lang], '', 'class="button01"');
				  showXHTML_td_E();
				showXHTML_tr_E();

			  showXHTML_table_E();*/
			// showXHTML_tabFrame_E();
			//echo '</form><form id="grade_form" accept-charset="UTF-8" lang="ZH-TW" action="grade_modify1.php" method="POST" style="display: inline" onsubmit="xajax_clean_temp(st_id);">';
			
			
                $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		        $sqls = 'select M.username,A.first_name,A.last_name,G.score,G.comment from WM_term_major as M ' .
			        	'left join WM_user_account as A on M.username = A.username ' .
			        	"left join WM_grade_item as G on G.grade_id={$_GET['gid']} and M.username=G.username " .
			        	"where M.course_id={$sysSession->course_id} and (M.role & {$sysRoles['student']}) order by M.username";
		        $RS = $sysConn->Execute($sqls);
		        if ($RS){
				$ary = array(array($MSG['modify_grade'][$sysSession->lang]));

		        	// showXHTML_tabFrame_B($ary, 1, '', '', 'action="grade_modify1.php" method="POST" style="display: inline"');

				      showXHTML_table_B('id="tabsSet2" border="0" cellpadding="3" cellspacing="1" class="box01"');
				        showXHTML_tr_B('class="bg02 font01"');
				          showXHTML_td('width="190"', $MSG['student'][$sysSession->lang]);
				          showXHTML_td('width="50"' , $MSG['score'][$sysSession->lang]);
				          showXHTML_td('width="420"', $MSG['comment'][$sysSession->lang]);
				        showXHTML_tr_E();
		        	        while(!$RS->EOF){
		        	        	$cla = $cla == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
				        	showXHTML_tr_B($cla);
                                // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
                                $realname = checkRealname($RS->fields['first_name'],$RS->fields['last_name']);
                              showXHTML_td('', $realname . ' (' . $RS->fields['username'] . ')');
				        	  showXHTML_td_B('', $MSG['score'][$sysSession->lang]);
				        	    showXHTML_input('text', 'fields[' . $RS->fields['username'] . '][0]', $RS->fields['score'], '', 'size="5" class="box02" onkeypress="return maskKeyPress(event);" onchange="noSave=true;"');
				        	  showXHTML_td_E();
				        	  showXHTML_td_B('', $MSG['comment'][$sysSession->lang]);
				        	    showXHTML_input('text', 'fields[' . $RS->fields['username'] . '][1]', $RS->fields['comment'], '', 'size="60" class="box02" onchange="noSave=true;"');
				        	  showXHTML_td_E();
				        	showXHTML_tr_E();
		        	        	$RS->MoveNext();
		        	        }
		        	        $cla = $cla == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
		        	        /*showXHTML_tr_B($cla);
				          showXHTML_td_B('colspan="3"');
				            showXHTML_input('hidden', 'grade_id', $_GET['gid']);
				            showXHTML_input('submit', '', $MSG['complete_modify'][$sysSession->lang], '', 'class="button01"');
				          showXHTML_td_E();
				        showXHTML_tr_E();*/
		        	      showXHTML_table_E();
		        	showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="bg03 font01"');
				    showXHTML_td_B('colspan="2"');
				    showXHTML_input('hidden', 'grade_id', $_GET['gid']);
				    showXHTML_input('button', '', $MSG['complete_modify'][$sysSession->lang], '', 'onclick="checkFields();" class="button01"');
				  showXHTML_td_E();
                showXHTML_td_E();
			  showXHTML_table_E();

	  			showXHTML_tabFrame_E();
		        }
		        else
		        	echo $sysConn->ErrorNo(), ': ', $sysConn->ErrorMsg();
			
		}

	}
	else {
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $_GET['gid'] , 1, 'auto', $_SERVER['PHP_SELF'], 'Incorrect grade_id');
		die('Incorrect grade_id.');
	}
	showXHTML_body_E();
?>

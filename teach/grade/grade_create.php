<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                         *
     *      Creation  : 2003/06/06                                                            *
     *      work for  : grade manage                                                          *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
    require_once(sysDocumentRoot . '/lib/multi_lang.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lang/grade.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    
    $sysSession->cur_func = '1400100100';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    $course_id = $sysSession->course_id; //10000000;

    // 取得所有正式生
    $students = array();
	$RS = dbGetStMr('WM_term_major as M,WM_user_account as A',
					'M.username,A.first_name,A.last_name',
					"M.course_id={$sysSession->course_id} and (M.role & {$sysRoles['student']}) and M.username = A.username order by M.username",
					ADODB_FETCH_ASSOC);
    if ($RS)
    {
        while($fields = $RS->FetchRow())
            $students[$fields['username']] = htmlspecialchars(checkRealname($fields['first_name'], $fields['last_name']));
        $lists = array(0 => $MSG['all_students'][$sysSession->lang]);    
    }

    // 取得所有分組
    $sqls = 'select T.team_id, T.team_name, G.group_id, G.caption, D.username ' .
            'from WM_student_separate as T ' .
            'left join WM_student_group as G on T.course_id=G.course_id and T.team_id=G.team_id ' .
            'left join WM_student_div as D on G.course_id=D.course_id and G.group_id=D.group_id and G.team_id=D.team_id ' .
            "where T.course_id={$sysSession->course_id} " .
            'order by T.team_id,G.group_id,D.username';
    $users = array();
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    if (($rs = $sysConn->Execute($sqls)) && $rs->RecordCount())
    {
        $prevTeam=0; $prevGroup=0;
        while($fields = $rs->FetchRow())
        {
            $tid = intval($fields['team_id']);
            $gid = intval($fields['group_id']);
            $users[$tid][$gid][] = $fields['username'];
            if ($prevTeam != $tid)
            {
                $t = unserialize($fields['team_name']);
                $lists[$tid] = '&nbsp;+&nbsp;' . htmlspecialchars($t[$sysSession->lang], ENT_QUOTES, 'UTF-8');
                $t = unserialize($fields['caption']);
                $groups[$tid][$gid] = htmlspecialchars($t[$sysSession->lang], ENT_QUOTES, 'UTF-8');
            }
            elseif($prevGroup != $gid)
            {
                $t = unserialize($fields['caption']);
                $groups[$tid][$gid] = htmlspecialchars($t[$sysSession->lang], ENT_QUOTES, 'UTF-8');
            }
            $prevTeam=$tid; $prevGroup=$gid;
        }
    }

	 $today_time = date('Y-m-d H:i');
    $scr = <<< EOB

var all_students_table = '';
var all_students       = new Array();
    all_students[0]    = new Object();

EOB;

    foreach($students as $user => $realname)
    {
        $scr .= sprintf("\tall_students[0]['%s'] = '%s';\n", $user, addslashes($realname));
    }

    foreach($users as $tid => $team)
    {
        $scr .= sprintf("\tall_students[%d] = new Array();\n\tall_students[%d][0] = new Array();\n", $tid, $tid);
        foreach($team as $gid => $user)
        {
            $scr .= sprintf("\tall_students[%d][0][%d] = '%s';\n\tall_students[%d][%d] = new Array('%s');\n", $tid, $gid, $groups[$tid][$gid], $tid, $gid, implode("','", $user));
        }
    }

    // 開始 output HTML
    showXHTML_head_B($MSG['add'][$sysSession->lang]);
      showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");

      $scr .= <<< EOB
/* 如果是 Mozilla/Firefox 則加上 outerHTML/innerText 的支援 */
if (navigator.userAgent.indexOf(' Gecko/') != -1)
{
	HTMLElement.prototype.__defineSetter__('outerHTML', function(s){
	   var range = this.ownerDocument.createRange();
	   range.setStartBefore(this);
	   var fragment = range.createContextualFragment(s);
	   this.parentNode.replaceChild(fragment, this);
	});

	HTMLElement.prototype.__defineGetter__('outerHTML', function() {
	   return new XMLSerializer().serializeToString(this);
	});

	HTMLElement.prototype.__defineGetter__('innerText', function() {
	  return this.innerHTML.replace(/<[^>]+>/g, '');
	});
}

function maskKeyPress(objEvent)
{
    var iKeyCode = objEvent.which ? objEvent.which : objEvent.keyCode;
    switch(iKeyCode)
    {
        case 8:
        case 9:
        case 13:
        case 27:
        case 35:
        case 36:
        case 46:
        case 190:
            return;
        default:
            if (iKeyCode<48 || iKeyCode>57)
            {
                objEvent.cancelBubble=true;
                return false;
			}
    }
}

function switchWhich(n)
{
    var IH = '<table id="tabsSet1" border="0" cellpadding="3" cellspacing="1" class="box01" >' +
             '  <tr class="bg02 font01" >' +
             '      <td class="cssTd" width="190">{$MSG['student'][$sysSession->lang]}</td>' +
             '      <td class="cssTd" width="50" >{$MSG['score'][$sysSession->lang]}</td>' +
             '      <td class="cssTd" width="420">{$MSG['comment'][$sysSession->lang]}</td>' +
             '  </tr>';
    var col='4';

    if (n > 0)  // for group
    {
	    document.getElementById('userSample').style.display = 'none';
	    document.getElementById('groupSample').style.display = '';

        for(var i=1; i<all_students[n].length; i++)
        {
            if (typeof(all_students[n][i]) != 'undefined')
            {
                col = col == '3' ? '4' : '3';
                IH += '<tr class="bg0' + col + ' font01" >' +
                      ' <td class="cssTd"  >' + '(' + i + ') ' + all_students[n][0][i] + ' </td>' +
                      ' <td class="cssTd"  >' +
                      ' <input type="text" name="fields[' + i + '][0]" value="" size="5" class="box02" onkeypress="return maskKeyPress(event);" onchange="noSave=true;">' +
                      ' </td>' +
                      ' <td class="cssTd"  >' +
                      ' <input type="text" name="fields[' + i + '][1]" value="" size="60" class="box02" onchange="noSave=true;">' +
                      ' </td>' +
                      '</tr>';
            }
        }
	    document.getElementById('tabsSet1').outerHTML = IH + '</table>';
    }
    else    // for ALL student
    {
	    document.getElementById('userSample').style.display = '';
	    document.getElementById('groupSample').style.display = 'none';
		document.getElementById('tabsSet1').outerHTML = all_students_table;
    }

    document.getElementById('tabsSet1').style.display = (document.getElementById('importType').value == '1' ? '': 'none');
}

function switchSource(n){
    var obj=document.getElementById('procTable');
    var nodes = obj.getElementsByTagName('select');
    for(var i=1; i<4; i++) nodes[i].style.display = (n == i) ? '' : 'none';
    for(var i=4; i<nodes.length; i++) {
        if (n == 9){
            nodes[i].style.backgroundColor='';
            nodes[i].disabled = false;
        }
        else{
            nodes[i].style.backgroundColor='#C0C0C0';
            nodes[i].disabled = true;
        }
    }
    var nodes = obj.getElementsByTagName('input');
    for(var i=0; i<nodes.length-1; i++) {
        if (n == 9){
            nodes[i].style.backgroundColor='';
            nodes[i].disabled = false;
        }
        else{
            nodes[i].style.backgroundColor='#C0C0C0';
            nodes[i].disabled = true;
        }
    }

    for(var i=1; i<obj.rows.length-1; i++)
        obj.rows[i].style.display = (n == 9) ? '': 'none';
}

function chgPublish(val)
{
	var obj = document.getElementById('span_publish');
	if (obj)
	{
		obj.style.display = val == 'yes' ? '' : 'none';
	}
}
// ===================================== Javascript 日期自動產生函式 ==================================================
function checkFields(objForm)
{
    var fields = objForm.getElementsByTagName('select');
    for(var i =0; i< fields.length; i++)
        if (fields[i].name == 'source' && fields[i].value != '9') return true;

	if (!chk_multi_lang_input(1, true, "{$MSG['title_least_one'][$sysSession->lang]}")) return false;

	var d1 = objForm.chk_begin.checked ? objForm.publish_begin.value : false;
	var d2 = objForm.chk_end.checked   ? objForm.publish_end.value   : false;
	if (d1 && d2 && d1 >= d2 && $('#sysRadioBtn2').attr('checked') === 'checked') {
		alert('{$MSG['begintime_less_then_overtime'][$sysSession->lang]}');
		return false;
	}

	if (objForm.importType.value == '2' && objForm.importfile.value == '')
	{
		alert('{$MSG['chioce csv file first'][$sysSession->lang]}');
		return false;
	}

	xajax_clean_temp(st_id);
    return true;
}

function setImportType(type)
{
	document.getElementById('importType').value = type;
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

var noSave = false;
var st_id  = '{$sysSession->cur_func}{$sysSession->course_id}';
window.onload = function() {
	all_students_table=document.getElementById('tabsSet1').outerHTML;
	Calendar_setup('publish_begin' , '%Y-%m-%d %H:%M', 'publish_begin' , true);
	Calendar_setup('publish_end'   , '%Y-%m-%d %H:%M', 'publish_end'   , true);
	document.getElementById('publish_begin').value = '{$today_time}';
	document.getElementById('publish_end').value   = '{$today_time}';
	xajax_check_temp(st_id, 'mainForm');
	window.setInterval(function(){if (noSave) xajax_save_temp(st_id, document.getElementById('mainForm').innerHTML);}, 100000);
};
EOB;
      showXHTML_script('include', '/lib/jquery/jquery-1.7.2.min.js');
      showXHTML_script('inline', $scr);
      $xajax_save_temp->printJavascript('/lib/xajax/');
      $calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
		$calendar->load_files();
    showXHTML_head_E();
    showXHTML_body_B();
      showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
        showXHTML_tr_B();
          showXHTML_td_B();
            $ary[] = array($MSG['add'][$sysSession->lang]);
            showXHTML_tabs($ary, 1);
          showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_tr_B();
          showXHTML_td_B('valign="top" class="bg01"');
        showXHTML_form_B('id="mainForm" action="grade_create1.php" method="POST" onsubmit="return checkFields(this);" enctype="multipart/form-data" style="display: inline"');

          showXHTML_input('hidden', 'source', 9); // 如果底下的註解打開，這行要註解起來。
          showXHTML_input('hidden', 'importType', '1', '', 'id="importType"');

          showXHTML_table_B('id="procTable" border="0" cellpadding="3" cellspacing="1" class="box01" width="1000"');
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
                showXHTML_input('text', 'percent', '10', '', 'size="4" class="box02" onkeypress="return maskKeyPress(event);"'); echo "%";
              showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B('class="bg04 font01"');
              showXHTML_td('width="100"', $MSG['publish'][$sysSession->lang]);
              showXHTML_td_B('');
              		showXHTML_table_B('border="0"');
              			showXHTML_tr_B('class="cssTrOdd"');
              				showXHTML_td_B();
              					showXHTML_input('radio', 'rdoPublish', array('yes'=> $MSG['msgPublish'][$sysSession->lang]), 'yes', 'onclick="chgPublish(this.value)"');
              				showXHTML_td_E();
              				showXHTML_td_B('');
              					echo '<span id="span_publish">';
              					showXHTML_input('checkbox', 'chk_begin', '', '', 'onclick="showDateInput(\'span_publish_begin\', this.checked)"');
              					echo $MSG['msg_enable_begin'][$sysSession->lang], '<span id="span_publish_begin" style="display: none;">', $MSG['msg_enable_date'][$sysSession->lang];
              					showXHTML_input('text', 'publish_begin', '', '', 'id="publish_begin" readonly="readonly" class="cssInput"');
              					echo '</span><br>';
              					showXHTML_input('checkbox', 'chk_end', '', '', 'onclick="showDateInput(\'span_publish_end\', this.checked)"');
              					echo $MSG['msg_enable_end'][$sysSession->lang], '<span id="span_publish_end" style="display: none;">', $MSG['msg_enable_date'][$sysSession->lang];
              					showXHTML_input('text', 'publish_end', '', '', 'id="publish_end" readonly="readonly" class="cssInput"');
              					echo '</span></span>';
              				showXHTML_td_E();
              			showXHTML_tr_E();
              			showXHTML_tr_B('class="cssTrOdd"');
              				showXHTML_td_B();
              					showXHTML_input('radio', 'rdoPublish', array('no'=> $MSG['msgNoPublish'][$sysSession->lang]), 'yes', 'onclick="chgPublish(this.value)"');
              				showXHTML_td_E();
              				showXHTML_td('', '&nbsp;');
              			showXHTML_tr_E();
              		showXHTML_table_E();
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td('', $MSG['for_target'][$sysSession->lang]);
              showXHTML_td_B('');
                showXHTML_input('select', 'which', $lists, '', 'onchange="switchWhich(this.value);"');
              showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg04 font01"');
              showXHTML_td('width="100"', $MSG['student_grade'][$sysSession->lang]);
              showXHTML_td_B('');
                showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
                  showXHTML_tr_B();
                    showXHTML_td_B();
                      $ary = array(array($MSG['input_by_hand'][$sysSession->lang], 'tabsSet1',  'setImportType(1);'),
                                   array($MSG['upload_import'][$sysSession->lang], 'tabsSet2',  'setImportType(2);'));
                      showXHTML_tabs($ary, 1);
                    showXHTML_td_E();
                  showXHTML_tr_E();
                  showXHTML_tr_B();
                    showXHTML_td_B('valign="top" class="bg01"');

                      showXHTML_table_B('id="tabsSet1" border="0" cellpadding="3" cellspacing="1" class="box01"');
                        showXHTML_tr_B('class="bg02 font01"');
                          showXHTML_td('width="190"', $MSG['student'][$sysSession->lang]);
                          showXHTML_td('width="50"',  $MSG['score'][$sysSession->lang]);
                          showXHTML_td('width="420"', $MSG['comment'][$sysSession->lang]);
                        showXHTML_tr_E();
                        foreach($students as $user => $realname)
                        {
                            $cla = $cla == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
                            showXHTML_tr_B($cla);
                              showXHTML_td('', sprintf('%s (%s)', $realname, $user));
                              showXHTML_td_B('', $MSG['score'][$sysSession->lang]);
                                showXHTML_input('text', 'fields[' . $user . '][0]', '', '', 'size="5" class="box02" onkeypress="return maskKeyPress(event);" onchange="noSave=true;"');
                              showXHTML_td_E();
                              showXHTML_td_B('', $MSG['comment'][$sysSession->lang]);
                                showXHTML_input('text', 'fields[' . $user . '][1]', '', '', 'size="60" class="box02" onchange="noSave=true;"');
                              showXHTML_td_E();
                            showXHTML_tr_E();
                        }
                      showXHTML_table_E();

                      showXHTML_table_B('id="tabsSet2" border="0" cellpadding="3" cellspacing="1" class="box01" style="display:none"');
                        showXHTML_tr_B('class="cssTrEvn"');
                        	showXHTML_td('align="right"', $MSG['file_tile'][$sysSession->lang]);
                          	showXHTML_td_B('');
                            	showXHTML_input('file', 'importfile', '', '', 'size="40" class="cssInput"');
                          	showXHTML_td_E();
                          	showXHTML_td('', $MSG['import_file_help'][$sysSession->lang]);
                        showXHTML_tr_E();

                        showXHTML_tr_B('class="cssTrOdd"');
                        	showXHTML_td('align="right"', $MSG['file_format_tile'][$sysSession->lang]);
                        	showXHTML_td_B('');
								$file_type = array('Big5'	=>	$MSG['Big5'][$sysSession->lang],
								                   'GB2312'	=>	$MSG['GB2312'][$sysSession->lang],
								                   'en'		=>	$MSG['en'][$sysSession->lang],
								                   //	先不處理日文 'EUC-JP'	=>	$MSG['EUC-JP'][$sysSession->lang],
								                   'UTF-8'	=>	$MSG['UTF-8'][$sysSession->lang]);
								showXHTML_input('select', 'file_format', $file_type, ($sysSession->lang == 'user_define' ? 'UTF-8' : $sysSession->lang), 'class="cssInput" style="width: 158px"');
							showXHTML_td_E();
							showXHTML_td('', $MSG['import_format_help'][$sysSession->lang]);
                        showXHTML_tr_E();
                        showXHTML_tr_B('class="cssTrEvn"');
                          showXHTML_td_B('colspan="3"');
                          	 echo $MSG['hint_caption'][$sysSession->lang], "<br>\n";
                            showXHTML_input('textarea', '', "user_id,80,comments\nuser_id,70,comments\nuser_id,95,comments\n:\n:", '', 'id="userSample"  rows="5" cols="30" class="box02" readonly');
                            showXHTML_input('textarea', '', "1,80,comments\n2,70,comments\n3,95,comments\n:\n:",                   '', 'id="groupSample" rows="5" cols="30" class="box02" style="display: none" readonly');
                          showXHTML_td_E();
                        showXHTML_tr_E();
                      showXHTML_table_E();

                    showXHTML_td_E();
                  showXHTML_tr_E();
                showXHTML_table_E();

              showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td_B('colspan="2"');
                showXHTML_input('submit', '', $MSG['sure_to_add'][$sysSession->lang], '', 'class="button01"');
              showXHTML_td_E();
            showXHTML_tr_E();

          showXHTML_table_E();
        showXHTML_form_E();
          showXHTML_td_E();
        showXHTML_tr_E();
      showXHTML_table_E();
    showXHTML_body_E();
?>

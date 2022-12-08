<?php
/**************************************************************************************************
 *                                                                                                *
 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *		Programmer: Wiseguy Liang                                                         *
 *		Creation  : 2003/06/06                                                            *
 *		work for  : grade manage                                                          *
 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *      version $Id: grade_maintain.php,v 1.1 2010/02/24 02:40:27 saly Exp $                                                                                           *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/grade.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
if (sysEnableAppServerPush) {
    require_once(sysDocumentRoot . '/lang/app_server_push.php');
}

$sysSession->cur_func = '1400100200';
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$course_id = $sysSession->course_id; //10000000;

$sources = array(
    1 => $MSG['works'][$sysSession->lang],
    2 => $MSG['exams'][$sysSession->lang],
    3 => $MSG['quest'][$sysSession->lang],
    4 => $MSG['peer'][$sysSession->lang],
    9 => $MSG['userdef'][$sysSession->lang]
);

// 取得成績單數，及總百分比數
list($total_item, $total_percent) = dbGetStSr('WM_grade_list', 'count(*),sum(percent)', "course_id={$sysSession->course_id}", ADODB_FETCH_NUM);
// 開始 output HTML
showXHTML_head_B($MSG['grade_manage'][$sysSession->lang]);
showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
showXHTML_script('include', '/lib/dragLayer.js');

$scr = <<< EOB
var alls = false;
var MSG_CONFIRM_PUSH = "{$MSG['app_push_message_confirm'][$sysSession->lang]}";
var MSG_APP_PUSH_SUCCESS = "{$MSG['app_push_message_success'][$sysSession->lang]}";
var MSG_APP_PUSH_FAIL = "{$MSG['app_push_message_fail'][$sysSession->lang]}";
var MSG_APP_PUSH_SQL_ERROR = "{$MSG['app_push_message_sql_error'][$sysSession->lang]}";
var MSG_APP_PUSH_NO_SUCH_ROLE = "{$MSG['app_push_message_no_such_role'][$sysSession->lang]}";
/**
 * 手動推播成績
 * @param integer courseID 課程編號
 * @param integer gradeID 成績編號
 **/
function gradeNotify(courseID, gradeID) {

    var alertMessage = '', result;
    var pushObject = new Object();
    var resultObject = null;

    if (!confirm(MSG_CONFIRM_PUSH)) {
        return;
    }
    
    pushObject = {
        courseID: courseID,
        gradeID: gradeID
    };
    
    $.ajax({
        url: '../../lib/app_course_grade_push_handler.php',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(pushObject),
        error: function(xhr) {
            alert(MSG_APP_PUSH_FAIL);
        },
        success: function(result) {
            switch (result.message) {
                case 'app_push_message_success':
                    alertMessage = MSG_APP_PUSH_SUCCESS;
                    break;
                case 'app_push_message_fail':
                    alertMessage = MSG_APP_PUSH_FAIL;
                    break;
                case 'app_push_message_sql_error':
                    alertMessage = MSG_APP_PUSH_SQL_ERROR;
                    break;
                case 'app_push_message_no_such_role':
                    alertMessage = MSG_APP_PUSH_NO_SUCH_ROLE;
                    break;
            }

            alert(alertMessage);
        }
    });
}
function selectAll(mode){
	var nodes = document.getElementById('procTable').getElementsByTagName('input');
	for(var i=0; i<nodes.length; i++) nodes[i].checked = mode;
	alls = mode;
	xchg_words(alls);
}

/**
 * 切換全選或全消的 checkbox
 **/
function chgCheckbox() {
	var bol = true;
	var nodes = document.getElementsByTagName("input");
	var obj  = document.getElementById("ck_box");

	if ((nodes == null) || (nodes.length <= 0)) return false;
	for (var i = 0; i < nodes.length; i++) {
		if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck_box")) continue;

		if (nodes[i].name == 'item[]'){
			if (nodes[i].checked == false) bol = false;
		}
	}

	if (obj  != null) {
        obj.checked = bol;
        alls = bol;
    }

	if (bol){
		xchg_words(bol);
	}else{
		xchg_words(bol);
	}
}

function selectAll2(button)
{
	alls = !alls;
	xchg_words(alls);
	selectAll(alls);
}

function xchg_words(mode)
{
	if (mode)
	{
		document.getElementById('toolbar1').getElementsByTagName('input')[0].value =
		document.getElementById('toolbar2').getElementsByTagName('input')[0].value = '{$MSG['unselect_all'][$sysSession->lang]}';
	}
	else
	{
		document.getElementById('toolbar1').getElementsByTagName('input')[0].value =
		document.getElementById('toolbar2').getElementsByTagName('input')[0].value = '{$MSG['select_all'][$sysSession->lang]}';
	}
}

function processFunc(n){
	var obj = document.getElementById('procTable');
	var nodes = obj.getElementsByTagName('input');
	var tmp, newNode;
	var lists = new Array(), permute = new Array();
	var idx = 0;
	for(var i=1; i<nodes.length; i++) if (nodes[i].checked) lists[idx++] = nodes[i].value;

	if (n > 1 && n < 8 && idx == 0)
	{
		alert('{$MSG['select_first'][$sysSession->lang]}');
		return;
	}

	switch(n){
		case 1: // add
			location.replace('grade_create.php');
			break;
		case 2: // modify
			if (idx > 1) alert('{$MSG['edit_the_frst'][$sysSession->lang]}');
			location.replace('grade_modify.php?gid=' + lists[0]);
			break;
		case 3: // delete
			if (!confirm('{$MSG['are_you_sure'][$sysSession->lang]}')) return;
			location.replace('grade_remove.php?gid=' + lists.join(','));
			break;
		case 4: // mail_to
			document.getElementById('mailForm').lists.value = lists.join(',');
			displayDialog('mailTable');
			break;
		case 5: // export
			document.getElementById('exportForm').lists.value = lists.join(',');
			displayDialog('exportTable');
			break;
		case 6: // up
			for(var i=2; i<obj.rows.length; i++){
				tmp = obj.rows[i].cells[0].getElementsByTagName('input');
				if (tmp[0].checked){
					newNode = obj.rows[i].cloneNode(true);
					newNode = obj.rows[i].parentNode.insertBefore(newNode, obj.rows[i-1]);
					newNode.getElementsByTagName('input')[0].checked = true;
					tmp = obj.rows[i-1].className;
					obj.rows[i-1].className = obj.rows[i].className;
					obj.rows[i].className = tmp;
					obj.deleteRow(i+1);
				}
			}
			document.getElementById('toolbar1').getElementsByTagName('table')[0].rows[0].cells[0].lastChild.disabled = false;
			document.getElementById('toolbar2').getElementsByTagName('table')[0].rows[0].cells[0].lastChild.disabled = false;
			break;
		case 7: // down
			for(var i=obj.rows.length-2; i>=1; i--){
				tmp = obj.rows[i].cells[0].getElementsByTagName('input');
				if (tmp[0].checked){
					newNode = obj.rows[i+1].cloneNode(true);
					check = obj.rows[i+1].cells[0].firstChild.checked;
					newrow = obj.rows[i].parentNode.insertBefore(newNode, obj.rows[i]);
					newrow.cells[0].firstChild.checked = check;
					tmp = obj.rows[i+1].className;
					obj.rows[i+1].className = obj.rows[i].className;
					obj.rows[i].className = tmp;
					obj.deleteRow(i+2);
				}
			}
			document.getElementById('toolbar1').getElementsByTagName('table')[0].rows[0].cells[0].lastChild.disabled = false;
			document.getElementById('toolbar2').getElementsByTagName('table')[0].rows[0].cells[0].lastChild.disabled = false;
			break;
		case 8: // save permute
			for(var i=1; i<nodes.length; i++) {
			    if (nodes[i].type != 'button' && nodes[i].value != '') {
			        permute.push(nodes[i].value);
			    }
			}
			location.replace('grade_order.php?gid=' + permute.toString());
			break;
	}
}

function displayDialog(name)
{
	var obj = document.getElementById(name);
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 460;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
	obj.style.top   = document.body.scrollTop  + 30;
	obj.style.display = '';
}

function hiddenDialog(name)
{
	document.getElementById(name).style.display = 'none';
}

// 匯出成績
function exportGrade() {
	hiddenDialog('exportTable');
	var obj = document.getElementById('exportForm');
	obj.target = 'empty';
	obj.submit();
	obj.target = '_self';
}

function checkExport()
{
	var obj = document.getElementById('exportForm');
	var elements = obj.getElementsByTagName('input');
	for(var i=0; i<elements.length; i++)
	{
		if (elements[i].type == 'checkbox' && elements[i].checked)
		{
			elements[elements.length-2].disabled = false;
			return;
		}
	}
	elements[elements.length-2].disabled = true;
}

function edit_item(obj)
{
	document.getElementById('mainForm').reset();
	obj.parentNode.parentNode.cells[0].getElementsByTagName('input')[0].checked = true;
	processFunc(2);
}

function rm_whitespace(node){
	switch(node.nodeType){
		case 1:
			for(var i=node.childNodes.length-1; i>=0; i--) rm_whitespace(node.childNodes[i]);
			break;
		case 3:
			if (node.nodeValue.search(/^\s+$/) === 0) node.parentNode.removeChild(node);
			break;
	}
}

window.onload=function(){
	var t1 = document.getElementById('toolbar1');
	rm_whitespace(t1);
	document.getElementById('toolbar2').innerHTML = t1.innerHTML;
        $('#toolbar1, #toolbar2').find('input,button').css('margin-right', '0.5em');
};

EOB;
showXHTML_script('include', "/lib/jquery/jquery.min.js");
showXHTML_script('inline', $scr);
showXHTML_head_E();
showXHTML_body_B();
showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
showXHTML_tr_B();
showXHTML_td_B();
$ary[] = array(
    $MSG['grade_manage'][$sysSession->lang],
    'tabsSet',
    ''
);
showXHTML_tabs($ary, 1);
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_tr_B();
showXHTML_td_B('valign="top" class="bg01"');
showXHTML_form_B('style="display: inline"', 'mainForm');

showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');
showXHTML_tr_B('class="cssTrEvn"');
showXHTML_td_B('id="toolbar1"');
echo '<table width="100%"><tr class="font01"><td nowrap>', "\n";
showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', 'class="cssBtn" onclick="selectAll2(this);"');
showXHTML_input('button', '', $MSG['add'][$sysSession->lang], '', 'class="cssBtn" onclick="processFunc(1);"');
showXHTML_input('button', '', $MSG['modify'][$sysSession->lang], '', 'class="cssBtn" onclick="processFunc(2);"');
showXHTML_input('button', '', $MSG['del'][$sysSession->lang], '', 'class="cssBtn" onclick="processFunc(3);"');
showXHTML_input('button', '', $MSG['mailto'][$sysSession->lang], '', 'class="cssBtn" onclick="processFunc(4);"');
showXHTML_input('button', '', $MSG['export'][$sysSession->lang], '', 'class="cssBtn" onclick="processFunc(5);"');
echo str_repeat(chr(9), $sysIndent), '&nbsp;<button type="button" class="cssBtn" style="width: 40" onclick="processFunc(6);">&uarr;</button>', "\n";
echo str_repeat(chr(9), $sysIndent), '&nbsp;<button type="button" class="cssBtn" style="width: 40" onclick="processFunc(7);">&darr;</button>', "\n";
showXHTML_input('button', '', $MSG['save_permute'][$sysSession->lang], '', 'class="cssBtn" onclick="processFunc(8);" disabled');
echo '</td><td align="right">', $MSG['total'][$sysSession->lang], ' ', $total_item, ' ', $MSG['grade_item'][$sysSession->lang], ' ', $total_percent, ' %</td></tr></table>', "\n";
showXHTML_td_E();
showXHTML_tr_E();

showXHTML_tr_B('class="cssTrOdd"');
showXHTML_td_B();
showXHTML_table_B('id="procTable" border="0" cellpadding="3" cellspacing="1" class="cssTable" width="760" style=" word-wrap: break-word; word-break: break-all;"');
showXHTML_tr_B('class="cssTrHead"');
showXHTML_td_B('width="24"');
showXHTML_input('checkbox', '', '', '', 'id="ck_box" onclick="selectAll(this.checked);"');
showXHTML_td_E();
showXHTML_td('width="390"', $MSG['title'][$sysSession->lang]);
showXHTML_td('width="110"', $MSG['source'][$sysSession->lang]);
showXHTML_td('width="80"', $MSG['percent'][$sysSession->lang]);
showXHTML_td('width="180"', $MSG['publish'][$sysSession->lang]);
if (sysEnableAppServerPush) {
    showXHTML_td('width="180"', $MSG['app_grade_push_item'][$sysSession->lang]);
}
showXHTML_tr_E();

$RS = dbGetStMr('WM_grade_list', '*', "course_id={$sysSession->course_id} order by permute,grade_id", ADODB_FETCH_ASSOC);
if ($RS)
    while (!$RS->EOF) {
        $cla = $cla == 'class="cssTrOdd"' ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
        showXHTML_tr_B($cla);
        showXHTML_td_B();
        // ' onclick="chgCheckbox(); event.cancelBubble=true;"'
        // showXHTML_input('checkbox', 'item[]', $RS->fields['grade_id'],'onclick="chgCheckbox()"');
        showXHTML_input('checkbox', 'item[]', $RS->fields['grade_id'], '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
        showXHTML_td_E();
        if (is_array($titles = getCaption($RS->fields['title'])));
        else
            $titles[$sysSession->lang] = $RS->fields['title'];
        showXHTML_td('', '<a href="javascript:;" onclick="edit_item(this); return false;" class="cssAnchor">' . htmlspecialchars($titles[$sysSession->lang]) . '</a>');
        showXHTML_td('', $sources[$RS->fields['source']]);
        showXHTML_td('align="right"', $RS->fields['percent'] . ' %');
        if ($RS->fields['publish_begin'] == '0000-00-00 00:00:00' && $RS->fields['publish_end'] == '0000-00-00 00:00:00') {
            $publish_time = $MSG['msgNoPublish'][$sysSession->lang];
        } else {
            $publish_time = $MSG['from2'][$sysSession->lang] . (($RS->fields['publish_begin'] == '1970-01-01 00:00:00' || $RS->fields['publish_begin'] == '0000-00-00 00:00:00') ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS->fields['publish_begin']))) . '<br />' . $MSG['to2'][$sysSession->lang] . ($RS->fields['publish_end'] == '9999-12-31 00:00:00' ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS->fields['publish_end'])));
        }
        showXHTML_td('style="font-size: 10px"', $publish_time);
        if (sysEnableAppServerPush) {
            showXHTML_td_B();
                showXHTML_input('button', '', $MSG['app_push_button'][$sysSession->lang], '', 'onclick="gradeNotify('. $sysSession->course_id . ',' . $RS->fields['grade_id'] . ');"');
            showXHTML_td_E();
        }
        showXHTML_tr_E();
        $RS->MoveNext();
    }

showXHTML_table_E();
showXHTML_td_E();
showXHTML_tr_E();

showXHTML_tr_B('class="cssTrEvn font01"');
showXHTML_td_B('id="toolbar2"');
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_table_E();

showXHTML_form_E();
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_table_E();


// 郵寄選項
$ary = array(
    array(
        $MSG['mail_grade_list'][$sysSession->lang]
    )
);
showXHTML_tabFrame_B($ary, 1, 'mailForm', 'mailTable', 'action="grade_mail.php" method="POST" target="empty" style="display: inline"', true);
showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');
showXHTML_tr_B('class="cssTrEvn"');
showXHTML_td('', $MSG['grade_list_type'][$sysSession->lang]);
showXHTML_td_B();
showXHTML_input('radio', 'grade_type', array(
    'all' => $MSG['mail_by_all'][$sysSession->lang],
    'per' => $MSG['mail_by_per'][$sysSession->lang],
    'score' => $MSG['mail_by_score'][$sysSession->lang]
), 'per', '', '<br>');
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_tr_B('class="cssTrOdd"');
showXHTML_td('', $MSG['teacher_comment'][$sysSession->lang]);
showXHTML_td_B();
showXHTML_input('textarea', 'grade_comment', '', '', 'class="cssInput" rows=6, cols=40');
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_tr_B('class="cssTrEvn"');
showXHTML_td_B('colspan="2" align="center"');
showXHTML_input('hidden', 'lists', '');
showXHTML_input('submit', '', $MSG['mailto'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'mailTable\');"');
showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'mailTable\');"');
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_table_E();
showXHTML_tabFrame_E();

// 匯出選項
$ary = array(
    array(
        $MSG['export_grade_list'][$sysSession->lang]
    )
);
showXHTML_tabFrame_B($ary, 1, 'exportForm', 'exportTable', 'action="grade_export.php" method="POST" style="display: inline"', true, false);
showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');
showXHTML_tr_B('class="cssTrEvn"');
showXHTML_td('', $MSG['grade_list_type'][$sysSession->lang]);
showXHTML_td_B();
showXHTML_input('checkboxes', 'grade_kinds[]', array(
    'csv' => 'Excel (.csv)',
    'htm' => 'HTML table (.htm)',
    'xml' => 'XML (.xml)'
), array(
    'csv'
), 'onclick="checkExport();"', '<br>');
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_tr_B('class="cssTrOdd"');
showXHTML_td('', $MSG['download_filename'][$sysSession->lang]);
showXHTML_td_B();
showXHTML_input('text', 'download_name', $sysSession->course_id . '.zip', '', 'maxlength="60" size="40" class="box02"');
echo '<br><span style="color: red">' . $MSG['select_unzip'][$sysSession->lang] . '</span>';
echo $MSG['desc_export'][$sysSession->lang];
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_tr_B('class="cssTrEvn"');
showXHTML_td_B('colspan="2" align="center"');
showXHTML_input('hidden', 'lists', '');
// showXHTML_input('submit', '', $MSG['export'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'exportTable\');"');
showXHTML_input('button', '', $MSG['export'][$sysSession->lang], '', 'onclick="exportGrade();"');
showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'exportTable\');"');
showXHTML_td_E();
showXHTML_tr_E();
showXHTML_table_E();
showXHTML_tabFrame_E();


showXHTML_body_E();
<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/03/21                                                            *
	 *		work for  : exam sub-system maintain interface                                    *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
    include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

	//ACL begin
	if (QTI_which == 'exam') {
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
	else if (QTI_which == 'peer') {
		$sysSession->cur_func='1710200200';
		$sysSession->restore();
		if (!aclVerifyPermission(1710200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

		}
	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	$ticket = md5(sysTicketSeed . $course_id . $_SERVER['QUERY_STRING']);
	$exam_types = array($MSG['exam_type1'][$sysSession->lang],
					    $MSG['exam_type2'][$sysSession->lang],
					    $MSG['exam_type3'][$sysSession->lang],
					    $MSG['exam_type4'][$sysSession->lang],
					    $MSG['exam_type5'][$sysSession->lang]
					   );

	$publishes = array('prepare' => $MSG['publish_state1'][$sysSession->lang],
					   'action'  => $MSG['publish_state2'][$sysSession->lang],
					   'close'   => $MSG['publish_state3'][$sysSession->lang]
					  );

	$count_types = array('none'    => $MSG['count_type0'][$sysSession->lang],
					     'first'   => $MSG['count_type1'][$sysSession->lang],
					     'last'    => $MSG['count_type2'][$sysSession->lang],
					     'max'     => $MSG['count_type3'][$sysSession->lang],
					     'min'     => $MSG['count_type4'][$sysSession->lang],
					     'average' => $MSG['count_type5'][$sysSession->lang]
					    );
	$announce_types = array('never'       => $MSG['announce_type1'][$sysSession->lang],
					        'now'         => $MSG['announce_type2'][$sysSession->lang],
					        'close_time'  => $MSG['announce_type3'][$sysSession->lang],
					        'user_define' => $MSG['announce_type4'][$sysSession->lang]
					       );

	$which_qti = QTI_which;

    chkSchoolId('WM_qti_' . QTI_which . '_test');
	$already_exameds = $sysConn->GetCol('select distinct T.exam_id from WM_qti_' . QTI_which .
										'_test as T inner join WM_qti_' . QTI_which .
										'_result as R on T.exam_id=R.exam_id where T.course_id=' .
										$course_id . ' order by T.exam_id');
	$aes = 'var already_exameds = new Array();';
	if (is_array($already_exameds) && count($already_exameds))
	{
		 $aes .= vsprintf(str_repeat(' already_exameds[%u]=true;', count($already_exameds)), $already_exameds);
	}


	function genForGuestLink($instance)
	{
	    global $course_id;

	    $salt = rand(100000, 999999);
	    $url  = sprintf('/Q/%u/%u/%u/1/', $course_id, $instance, $salt);
	    return $url . md5($_SERVER['HTTP_HOST'] . $url);
	}


	// 開始 output HTML
    showXHTML_head_B($MSG['exam_maintain'][$sysSession->lang], '8');
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
        showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
        showXHTML_CSS('include', "/public/css/common.css");
        showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");
        showXHTML_CSS('include', "/theme/default/learn_mooc/peer.css");

        showXHTML_script('include', '/lib/dragLayer.js');
        showXHTML_script('include', '/lib/jquery/jquery.min.js');
        showXHTML_script('include', '/theme/default/sparkline/js/jquery.sparkline.min.js');
        showXHTML_script('include', "/theme/default/bootstrap/js/bootstrap-tooltip.js");
        showXHTML_script('include', '/teach/peer/exam_maintain.js');


	  $scr = <<< EOB
$aes
var isIE            = (navigator.userAgent.search('MSIE') == -1) ? false : true;
var _GSE_MODE_FIRST	= 1;
var _GSE_MODE_LAST	= 2;
var _GSE_MODE_BOTH	= 3;
var _GSE_MODE_ALL	= 4;
var notSave         = false;
var MSG_EXIT        = "{$MSG['changed_but_not_saved'][$sysSession->lang]}";
var MSG_SELECT_ONE_ITEM_FIRST = "{$MSG['select_one_item_first'][$sysSession->lang]}";
var MSG_DELETE_CONFIRM = "{$MSG['delete_confirm'][$sysSession->lang]}";
var MSG_RESET_CONFIRM  = "{$MSG['reset_confirm'][$sysSession->lang]}";
var _ENV            = "{$topDir}";

window.onbeforeunload=function()
{
	if (notSave) return MSG_EXIT;
};

/**
 * 刪除 Mozilla 讀入 XML 時產生的空節點
 */
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

/*
 * 取得勾選的項目
 */
function getSelElement(mode){
	var obj = document.getElementById('displayPanel');
	var nodes = obj.getElementsByTagName('input');
	var ret = '';

	for(var i=0; i<nodes.length; i++){
		switch(mode){
			case _GSE_MODE_FIRST:	// 取第一個
				if (nodes.item(i).checked) return i;
				break;
			case _GSE_MODE_LAST :	// 取最後一個
			case _GSE_MODE_BOTH :	// 取第一個和最後一個
			case _GSE_MODE_ALL  :	// 取全部有勾選的
				if (nodes.item(i).checked) ret += (i + ',');
				break;
		}
	}
	ret = ret.replace(/,$/, '');
	var aa = ret.split(',');
	if (aa.length < 2 && (mode == _GSE_MODE_LAST || mode == _GSE_MODE_BOTH) ){
		alert('{$MSG['Least_two_selected_elements'][$sysSession->lang]}');
		return false;
	}
	switch(mode){
		case _GSE_MODE_LAST:
			return aa[aa.length-1];
		case _GSE_MODE_BOTH:
			return (aa[0] + ',' + aa[aa.length-1]);
		default:
			return ret;
	}
}

/*
 * 執行功能
 */
function executing(idx){
	if ((idx == 1 || idx == 2 || idx == 3 || idx == 6 || idx == 7 ||idx == 8 || idx == 11) && notSave) {
		if (confirm(MSG_EXIT))
			notSave = false;
		else
			return;
	}
	switch(idx){
		case  1:	// 新增
			if (this.name == 'main')
				parent.document.getElementById('workarea').cols = '0,*';
			else
				parent.document.getElementById('envCourse').cols = '0,*';
			var obj = document.getElementById('procform');
			obj.action = 'exam_create.php';
			obj.submit();
			 break;
		case  2:	// 修改
			var cur = getSelElement(_GSE_MODE_FIRST);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			if (typeof(already_exameds[parseInt(nodes.item(parseInt(cur)).value)]) != 'undefined' &&
			    !confirm('{$MSG['already_examed'][$sysSession->lang]}')
			   )
			{
				nodes.item(parseInt(cur)).checked = false;
				return;
			}
			if (this.name == 'main')
				parent.document.getElementById('workarea').cols = '0,*';
			else
				parent.document.getElementById('envCourse').cols = '0,*';
			obj = document.getElementById('procform');
			obj.action = 'exam_modify.php';
			obj.lists.value = nodes.item(parseInt(cur)).value;
			obj.submit();
			 break;
		case  3:	// 刪除
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			if (!confirm('{$MSG['delete_confirm'][$sysSession->lang]}')) return;
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			var aa = cur.split(',');
			var ii = 0, tmp = '';
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i], 10);
				tmp += nodes.item(ii).value + ',';
			}
			obj = document.getElementById('procform');
			obj.action = 'exam_remove.php';
			obj.lists.value = tmp.replace(/,$/, '');
			self.onunload = null;
			obj.submit();
			 break;
		case  4:	// 權限
			alert('Not yet provided.');
			return;
			 break;
		case  5:	// 儲存
			notSave = false;
			var obj = document.getElementById('displayPanel');
			var exams = obj.getElementsByTagName('input');
			var lists = [];
			for(var i=0; i<exams.length; i++){
				if (exams[i].type == 'checkbox') lists[lists.length] = exams[i].value;
			}
			if (lists.length <= 0) return;
			obj = document.getElementById('procform');
			obj.action = 'exam_order.php';
			obj.lists.value = lists.join(",");
			self.onunload = null;
			obj.submit();
			break;
		case  6:	// 批改
			var cur = getSelElement(_GSE_MODE_FIRST);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			if (this.name == 'main')
				parent.document.getElementById('workarea').cols = '0,*';
			else
				parent.document.getElementById('envCourse').cols = '0,*';
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			obj = document.getElementById('procform');
			obj.action = 'exam_correct.php';
			obj.lists.value = nodes.item(parseInt(cur)).value;
			obj.submit();
			break;
		case  7:	// 發布
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			var aa = cur.split(',');
			var ii = 0, tmp = '';
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i], 10);
				tmp += nodes.item(ii).value + ',';
			}
			obj = document.getElementById('procform');
			obj.action = 'exam_publish.php';
			obj.lists.value = tmp.replace(/,$/, '');
			self.onunload = null;
			obj.submit();
			break;
		case  8:	// 重置
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			if (!confirm("{$MSG['reset_confirm'][$sysSession->lang]}")) return;
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			var aa = cur.split(',');
			var ii = 0, tmp = '';
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i], 10);
				tmp += nodes.item(ii).value + ',';
			}
			obj = document.getElementById('procform');
			obj.action = 'exam_reset.php';
			obj.lists.value = tmp.replace(/,$/, '');
			self.onunload = null;
			obj.submit();
			break;
		case  9:	// 上移
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			var obj = document.getElementById('displayPanel');
			var aa = cur.split(',');
			var ii = 0, tmp;
			for(var i=0; i<aa.length; i++){
				ii = parseInt(aa[i], 10);
				if (ii == 0) continue;
				tmp = obj.rows[ii].cloneNode(true);
				obj.rows[ii].parentNode.removeChild(obj.rows[ii]);
				if (ii+1 == obj.rows.length)
					obj.rows[ii].parentNode.appendChild(tmp);
				else
					obj.rows[ii].parentNode.insertBefore(tmp, obj.rows[ii+1]);
			}
			notSave = true;
			break;
		case  10:	// 下移
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			var obj = document.getElementById('displayPanel');
			var aa = cur.split(',');
			var ii = 0, tmp;
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i], 10);
				if ((ii+1) == (obj.rows.length-1)) continue;
				tmp = obj.rows[ii+2].cloneNode(true);
				obj.rows[ii+2].parentNode.removeChild(obj.rows[ii+2]);
				obj.rows[ii+1].parentNode.insertBefore(tmp, obj.rows[ii+1]);
			}
			notSave = true;
			break;
		case 11:	// 匯出
			var cur = getSelElement(_GSE_MODE_FIRST);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			var tmp = nodes.item(parseInt(cur,10)).value ;
			if (tmp.search(/^\d+$/) == 0)
			{
				parent.empty.location.href = 'exam_export.php?' + tmp;
				executing(14);
			}
			else
				alert('exam_id incorrect.');
			break;
		case 12:	// 匯入
			displayDialog('ImportTable');
			break;
		case 13:	// 全選
		case 14:	// 全消
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			for(var i=0; i<nodes.length; i++)
				if (nodes.item(i).getAttribute('type') == 'checkbox')
					nodes.item(i).checked = (idx & 1) ? true : false;
			break;
		case  15:	// 複製
			var cur = getSelElement(_GSE_MODE_ALL);
			if (cur === false || cur === '') { alert('{$MSG['select_one_item_first'][$sysSession->lang]}'); return; }
			var obj = document.getElementById('displayPanel');
			var nodes = obj.getElementsByTagName('input');
			var aa = cur.split(',');
			var ii = 0, tmp = '';
			for(var i=(aa.length-1); i>=0; i--){
				ii = parseInt(aa[i], 10);
				tmp += nodes.item(ii).value + ',';
			}
			document.getElementById('CopyForm').lists.value = tmp.replace(/,$/, '');
			if (_ENV == 'academic')
			{
				self.onunload = null;
				document.getElementById('CopyForm').submit();
			}else{
	            displayDialog('CopyTable');
	        }
			break;
	}
}

function getTarget() {
	var obj = null;
	switch (this.name) {
		case "s_main"   : obj = parent.s_catalog; break;
		case "c_main"   : obj = parent.c_catalog; break;
		case "main"     : obj = parent.catalog;   break;
		case "s_catalog": obj = parent.s_main; break;
		case "c_catalog": obj = parent.c_main; break;
		case "catalog"  : obj = parent.main;   break;
	}
	return obj;
}

window.onload = function(){
	rm_whitespace(document.documentElement);
};

window.onunload = function(){
	var obj = getTarget();
	if ((typeof(obj) == 'object') && (obj != null))
		obj.location.replace('about:blank');
};

function selectRang(from, to)
{
	var objTable = document.getElementById('displayPanel');
	if (from > to)
	{
		var swap = from;
		from = to;
		to = swap;
	}
	from = Math.max(from,1);
	to   = Math.min(objTable.rows.length-1, to);
	for(var i=from; i<=to; i++)
		objTable.rows[i].cells[0].getElementsByTagName('input')[0].checked ^= true;
}

function edit_item(obj)
{
	document.getElementById('form1').reset();
	obj.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.cells[0].getElementsByTagName('input')[0].checked = true;
	executing(2);
}

function displayDialog(obj_id)
{
	var obj = document.getElementById(obj_id);
	if (obj == null) return;
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(500)] 再左移 10 個 pixel
	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 550;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
	obj.style.top   = document.body.scrollTop  + 10;
	obj.style.display = '';
}

function checkCopyTo(option)
{
	var c = !(parseInt(option.value) && option.checked);

	var elements = option.form.getElementsByTagName('input');
	for(var i=0; i<elements.length; i++)
	{
	    if (elements[i].type == 'checkbox') elements[i].disabled = c;
	}
}

function sureCopy(form)
{
	document.getElementById('CopyTable').style.display='none';
	if (form.which_copy_to[1].checked)
	{
		var elements = form.getElementsByTagName('input');
		var c = 0;
		for(var i=0; i<elements.length; i++)
		{
		    if (elements[i].type == 'checkbox') c++;
		}
		if (c < 1)
		{
		    alert('{$MSG['select_one_item_first'][$sysSession->lang]}');
		    return false;
		}
	}

	self.onunload = null;
	return true;
}

$(function() {
    $('.sparkpie').sparkline('html', {type: 'pie', sliceColors: ['#f3800f', '#dadada'], offset: -90, width: '23px', height: '23px', disableHighlight: true, borderColor: '#000000',disableTooltips: true} );
    $('.exam-percent-tips,.exam-type-tips').tooltip('hide');
});

EOB;
	showXHTML_script('inline', $scr);
	showXHTML_head_E();

	showXHTML_body_B();

        echo '<div style="min-width: 725px; margin: auto auto; padding-left: 2em; padding-right: 2em;">
                <ul class="bar" id="peer-page-title">
                    <li class="left">
                        <span>' . $MSG['homeworkandreport'][$sysSession->lang] . '</span>
                    </li>
                    <li class="right">
                        <button type="button" class="btn btn-primary btn-blue add span2">' . $MSG['addhomework'][$sysSession->lang] . '</button>
                    </li>
                </ul>
                <div class="navbar-form"></div>
                <div class="box box-padding-t-1 box-padding-lr-3">';

        // 取同儕互評資料
        $RS = dbGetStMr('WM_qti_' . QTI_which . '_test',
                      'exam_id, title, type, publish, begin_time, close_time, count_type, percent, announce_type,
                       announce_time, assess, start_date, end_date, assess_type, peer_percent, self_percent,
                       teacher_percent, peer_times, assess_way, assess_relation',
                      "course_id = $course_id order by sort, exam_id desc", ADODB_FETCH_ASSOC);
        if ($sysConn->ErrorNo() > 0) {
            echo $sysConn->ErrorMsg();
        }
        if ($RS && $RS->RecordCount() >= 1) {

            while(!$RS->EOF){
                // 取是否為群組作業
                $assignmentsForGroup = getAssignmentsForGroup(null, 'peer');
                $iconAssignmentsType = 'icon-user-blue';
                $imgTitle            = $MSG['for personal'][$sysSession->lang];
                if (isset($assignmentsForGroup[$RS->fields['exam_id']])) {
                    $iconAssignmentsType = 'icon-group-blue';
                    $imgTitle            = $MSG['for group'][$sysSession->lang];
                }

                // 取作業名稱
                $title = (strpos($RS->fields['title'], 'a:') === 0) ?
                         unserialize($RS->fields['title']):
                         array('Big5'		    => $RS->fields['title'],
                                'GB2312'	    => $RS->fields['title'],
                                'en'		    => $RS->fields['title'],
                                'EUC-JP'	    => $RS->fields['title'],
                                'user_define'	=> $RS->fields['title']
                         );

                // 取繳交作業期間
                $now = date('Y-m-d H:i:s');
                if (($now >= $RS->fields['begin_time'] and $now <= $RS->fields['close_time']) or ($RS->fields['begin_time'] === null and $RS->fields['close_time'] === null) or
                ($RS->fields['begin_time'] === null and $now <= $RS->fields['close_time']) or ($now >= $RS->fields['begin_time'] and $RS->fields['close_time'] === null)) {
                    $isPay = ' active';
                } else {
                    $isPay = '';
                }

                $begin_time = substr($RS->fields['begin_time'], 0, 10);
                $close_time = substr($RS->fields['close_time'], 0, 10);
                if ($begin_time === '0000-00-00') {
                    $begin_time = $MSG['now'][$sysSession->lang];
                }
                if ($close_time === '9999-12-31') {
                    $close_time = $MSG['forever'][$sysSession->lang];
                }
                $payDate = $begin_time . ' ~ ' . $close_time;

                // 取進入評分期間
                if (($now >= $RS->fields['start_date'] and $now <= $RS->fields['end_date']) or ($RS->fields['start_date'] === null and $RS->fields['end_date'] === null) or
                ($RS->fields['start_date'] === null and $now <= $RS->fields['end_date']) or ($now >= $RS->fields['start_date'] and $RS->fields['end_date'] === null)) {
                    $isRating = ' active';
                } else {
                    $isRating = '';
                }

                $start_date = substr($RS->fields['start_date'], 0, 10);
                $end_date   = substr($RS->fields['end_date'], 0, 10);
                if ($start_date === '0000-00-00') {
                    $start_date = $MSG['now'][$sysSession->lang];
                }
                if ($end_date === '9999-12-31') {
                    $end_date = $MSG['forever'][$sysSession->lang];
                }
                $ratingDate = $start_date . ' ~ ' . $end_date;

                // 取成績公告期間
                $rsGradeList = dbGetStMr('WM_grade_list','publish_begin, publish_end',
                    'course_id = ' . sprintf('%08u', $sysSession->course_id) .
                    ' and property = ' . sprintf('%09u', $RS->fields['exam_id']));
                if($rsGradeList && $rsGradeList->RecordCount() >= 1) {
                    while(!$rsGradeList->EOF) {
                        $score_begin_time = $rsGradeList->fields['publish_begin'];
                        $score_close_time = $rsGradeList->fields['publish_end'];
                        $rsGradeList->MoveNext();
                    }
                    if (($now >= $score_begin_time and $now <= $score_close_time) or ($score_begin_time === null and $score_close_time === null) or
                    ($score_begin_time === null and $now <= $score_close_time) or ($now >= $score_begin_time and $score_close_time === null)) {
                        $isScore = ' active';
                    } else {
                        $isScore = '';
                    }

                    if ($score_begin_time === $score_close_time && $score_close_time === '0000-00-00 00:00:00') {
                        $scoreDate = $MSG['score_publish0'][$sysSession->lang];
                    } else {
                        if ($score_begin_time === '1970-01-01 00:00:00' || $score_begin_time === '0000-00-00 00:00:00') {
                            $score_begin_time = $MSG['now'][$sysSession->lang];
                        }
                        if ($score_close_time === '9999-12-31 00:00:00') {
                            $score_close_time = $MSG['forever'][$sysSession->lang];
                        }
                        $scoreDate = substr($score_begin_time, 0, 10) . ' ~ ' . substr($score_close_time, 0, 10);
                    }
                } else {
                    $scoreDate = $MSG['score_publish0'][$sysSession->lang];
                }

                   echo '<ul class="bar">
                             <li class="left">
                                 <div class="' . $iconAssignmentsType . ' exam-type-tip" data-toggle="tooltip" title="' . $MSG['assignment_type'][$sysSession->lang] . ': ' . $imgTitle . '"></div>
                                 <span class="sparkpie exam-percent-tips" data-toggle="tooltip" title="作業比重: ' . $RS->fields['percent']. '%">' . $RS->fields['percent']. ',' . (100 - $RS->fields['percent']) . '</span>
                                 <div class="title">' . htmlspecialchars($title[$sysSession->lang]) . '</div>
                             </li>
                             <li class="right">
                                 <table class="table table-bordered table-striped peer-func-list">
                                     <tbody>
                                         <tr data-id="' . $RS->fields['exam_id'] . '">
                                             <td class="pull-center active">
                                                 <div class="edit">' . $MSG['edit'][$sysSession->lang] . '</div>
                                             </td>
                                             <td class="pull-center active">
                                                 <div class="delete">' . $MSG['remove'][$sysSession->lang] . '</div>
                                             </td>
                                             <td class="pull-center active">
                                                 <div class="clear">' . $MSG['clear'][$sysSession->lang] . '</div>
                                             </td>
                                         </tr>
                                     </tbody>
                                 </table>
                             </li>
                         </ul>
                         <table class="table table-bordered table-striped mooc-process">
                             <tbody>
                                 <tr>
                                     <td class="lcms-table-td-text_gray pull-center part-trisection pay' . $isPay. '" style="cursor: auto;">
                                         <div class="process-title">' . $MSG['rd_student_homework'][$sysSession->lang] . '</div>
                                         <div class="process-period">' . $payDate . '</div>
                                     </td>
                                     <td class="lcms-table-td-text_gray pull-center part-trisection rating' . $isRating. '" style="cursor: auto;">
                                         <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>
                                         <div class="process-period">' . $ratingDate . '</div>
                                     </td>
                                     <td class="lcms-table-td-text_gray pull-center score' . $isScore. '" style="cursor: auto;">
                                        <div class="process-title">' . $MSG['table_title9'][$sysSession->lang] . '</div>
                                        <div class="process-period">' . $scoreDate . '</div>
                                     </td>
                                 </tr>
                             </tbody>
                         </table>';

                $RS->MoveNext();
            }
        } else {
            echo '<div>目前沒有任何作業</div>
                  <div style="height: 1em;">&nbsp;</div>';
        }

        echo '  </div>
              </div>';

        echo '<div class="form-footer-space"></div>';

        // 事件用表單
        echo '<form id="form1" name="form1" accept-charset="UTF-8" lang="zh-tw" style="display:inline" action="" method="POST">
                <input type="hidden" name="ticket" value="' . $ticket . '">
                <input type="hidden" name="referer" value="' . $_SERVER['QUERY_STRING'] . '">
                <input type="hidden" name="lists" class="lists" value="">
              </form>';

	showXHTML_body_E();
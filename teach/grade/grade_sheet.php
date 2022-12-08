<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *      Programmer: Wiseguy Liang                                                                 *
	 *      Creation  : 2003/06/06                                                                    *
	 *      work for  : grade manage                                                                  *
	 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 *      重要變數：                                                                                *
	 *              $allUsers：所有本課正式生 HASH Table。Key=帳號；Value=姓名                        *
	 *              $grades：所有成績 Hash Table (四維)。                                             *
	 *                      Key=grade_id,                                                             *
	 *                      [key]['percent'] = 百分比                                                 *
	 *                      [key]['title'] = 成績標題                                                 *
	 *                      [key]['grade'][帳號]['score']=某學生在某成績欄的分數                      *
	 *                      [key]['grade'][帳號]['comment']=某學生在某成績欄的評語                    *
	 *                                                                                                *
	 *                                                                                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/grade.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');
    
	$sysSession->cur_func = '1400300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}


	// 取得所有正式生
	$allUsers = array();
	$RS = dbGetStMr('WM_term_major as M, WM_user_account as A',
					'M.username,A.first_name,A.last_name',
					"M.course_id={$sysSession->course_id} and (M.role & {$sysRoles['student']}) and M.username=A.username order by M.username",
					ADODB_FETCH_ASSOC);
	if ($RS)
		while(!$RS->EOF){
			// $allUsers[$RS->fields['username']] = $RS->fields['last_name'] . $RS->fields['first_name'];
            // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
            $allUsers[$RS->fields['username']] = checkRealname($RS->fields['first_name'],$RS->fields['last_name']);
			$RS->MoveNext();
		}
	else {
		$errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
		die($errMsg);
	}
	// 取得所有成績列表
	$RS = dbGetStMr('WM_grade_list', 'grade_id,title,percent', "course_id={$sysSession->course_id} order by permute,grade_id", ADODB_FETCH_ASSOC);
	$grades = array();
	if ($RS)
		while(!$RS->EOF){
			if (strpos($RS->fields['title'], 'a:') === 0)
					$titles = getCaption($RS->fields['title']);
				else
					$titles[$sysSession->lang] = $RS->fields['title'];
			$grades[$RS->fields['grade_id']] = array('title' => $titles[$sysSession->lang], 'percent' => $RS->fields['percent']);
			$RS->MoveNext();
		}
	else
		die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());

	// 	判斷是否有成績列表
	$grades_length = count($grades);
	if ($grades_length == 0){
		$count_grades = 0;	// false
	}else{
		$count_grades = 1;	// true
	}

	// 取得所有學生的所有成績
	$allStudent = implode("','", array_keys($allUsers));
	foreach($grades as $grade_id => $v){
		$RS = dbGetStMr('WM_grade_item', 'username,score,comment', "grade_id=$grade_id and username in ('$allStudent')", ADODB_FETCH_ASSOC);
		if ($RS)
			while(!$RS->EOF){
				$grades[$grade_id]['grade'][$RS->fields['username']] = array('score' => $RS->fields['score'], 'comment' => $RS->fields['comment']);
				$RS->MoveNext();
			}
		else
			die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
	}
	$allGrades = implode(',', array_keys($grades));

	// 判斷是否有無所有學生的成績
	if (empty($allGrades)){
		$allGrades = 0; // false
	}

	/*
	 * 判斷當成績名稱的項目，但尚未對任一學生有打分數，則 $all_stud_grade 為 0 (false)
	 */
	if (count($grades) > 0){
		foreach($grades as $grade_id => $v){
			if (is_array($grades[$grade_id]['grade'])){
				$all_stud_grade = 1; // true
				break;
			}else{
				$all_stud_grade = 0; // false
			}
		}
	}else{
		$all_stud_grade = 0; // false
	}
	// 開始 output HTML
	showXHTML_head_B($MSG['online_sheet'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	  showXHTML_CSS('inline', "
.adj    {cursor: pointer; position: relative; top: 8px; width: 100%; font-size: 1.1em; color: blue; margin-top: 0; padding-top: 0; line-height: 0.6em; border-top: 1px dashed white}
");
  	  showXHTML_script('include', '/lib/dragLayer.js');
	  showXHTML_script('include', '/lib/xmlextras.js');


	  $isSaved = ($_SERVER['argv'][0] == 'saved') ? "alert('{$MSG['save_complete'][$sysSession->lang]}               ');" : '';
	  $scr = <<< EOB
var students         = new Array('','{$allStudent}');					// 所有學生帳號陣列
var grades           = new Array(0,{$allGrades});						// 所有成績ID陣列
var percent          = new Array();										// 百分比陣列
var modifiedElements = new Object();									// 記錄哪個成績被修改，以送回 SERVER 端儲存
var keepTop          = 2, keepRight = 3, keepLeft = 1, keepBottom = 3;	// 試算表 Table 上下左右各有幾行是不能修改的
var isMZ             = (navigator.userAgent.toLowerCase().indexOf('firefox') > -1);	// 瀏覽器是否為 Mozilla
var key_UP           = 38;												// 向上鍵按鈕值
var key_DOWN         = 40;												// 向下鍵按鈕值
var key_LEFT         = 37;												// 向左鍵按鈕值
var key_RIGHT        = 39;												// 向右鍵按鈕值
var xx               = 0, yy = 0;										// 試算表游標位置
var tblOfficeX       = 0, tblOfficeY = 0;								// 試算表游標座標
var sheetTablePanel;
var count_grades     = {$count_grades};									// 判斷是否有成績列表(list)
var all_stud_grade   = {$all_stud_grade};								// 判斷是否有無所有學生的成績

/**
 * 移動試算表的游標 (n: 方向；m: 是否判斷要繞轉
 */
function cursorMove(n, m){
	switch(n){
		case 1: // right
			if (xx == sheetTablePanel.rows[0].cells.length - keepRight - 1){
				xx = keepLeft;
				if (m) cursorMove(2, false);
			}
			else
				xx++;
			break;
		case 2: // down
			if (yy == sheetTablePanel.rows.length - keepBottom - 1){
				yy = keepTop;
				if (m) cursorMove(1, false);
			}
			else
				yy++;
			break;
		case 3: // left
			if (xx == keepLeft){
				xx = (sheetTablePanel.rows[0].cells.length - keepRight - 1);
				if (m) cursorMove(4, false);
			}
			else
				xx--;
			break;
		case 4: // up
			if (yy == keepTop){
				yy = sheetTablePanel.rows.length - keepBottom - 1;
				if (m) cursorMove(3, false);
			}
			else
				yy--;
			break;
	}
}

/**
 * 編輯某個成績欄
 */
function editCell(){
	enableSaveBtm();
	var cell = this;
	var obj = document.getElementById('inputBox');
	var ipbox = obj.getElementsByTagName('input')[0];
	if (obj.style.display == '' &&  // 如果現在正在輸入
		sheetTablePanel.rows[yy].cells[xx].innerHTML != ipbox.value && // 而且輸入新資料
		ipbox.value.search(/^([0-9]+(\.[0-9]+)?)?$/) === 0) // 而且是合法分數
	{
		sheetTablePanel.rows[yy].cells[xx].innerHTML = ipbox.value;
		if (typeof(modifiedElements[grades[xx]]) == 'undefined') modifiedElements[grades[xx]] = new Object();
		modifiedElements[grades[xx]][students[yy-1]] = ipbox.value;
		calculate(xx,yy);
		enableSaveBtm();
	}

	xx = cell.cellIndex;
	yy = cell.parentNode.rowIndex;
	ipbox.style.width  = cell.offsetWidth  - (isMZ ? 1 : 0);
	ipbox.style.height = cell.offsetHeight - (isMZ ? 1 : 0);
	obj.style.left     = cell.offsetLeft + tblOfficeX;
	obj.style.top      = cell.offsetTop  + tblOfficeY;
	obj.style.display  = '';
	ipbox.value        = sheetTablePanel.rows[yy].cells[xx].innerHTML.replace(/^\s+|\s+$/, '');
	ipbox.focus();
	ipbox.select();
}

/**
 * 取得試算表游標應該出現的絕對座標
 */
function getParentOffset(obj, which){
	switch(obj.tagName){
		case 'HTML':
			return 0;
			break;
		case 'TABLE':
		case 'TD':
			return (which ? obj.offsetLeft : obj.offsetTop) + getParentOffset(obj.parentNode, which);
			break;
		default:
			return getParentOffset(obj.parentNode, which);
			break;
	}
}

/**
 * 網頁載入後的初始化動作 (計算試算表的結果)
 */
var no_rank  = false;
var no_sum   = false;
var no_level = false;

function init(isFirst)
{
	// var msgwin = window.open('javascript:document.write("<body leftMargin=0 topMargin=0 marginwidth=0 marginheight=0><table width=200 height=100><tr><td align=center><h3>{$MSG['please_waiting'][$sysSession->lang]}</h3></td></tr></table></" + "body>")', '', 'width=200,height=100,top=250,left=400,status=0,resizable=0,scrollbars=0');
	sheetTablePanel.rows[0].cells[0].innerHTML = '{$MSG['please_waiting'][$sysSession->lang]}';

	var grades_length = sheetTablePanel.rows[0].cells.length - keepLeft - keepRight;
	var ie   = sheetTablePanel.rows.length - keepBottom;
	var je   = 0;
	var x    = keepLeft;
	var y    = sheetTablePanel.rows[0].cells.length - keepRight -1;
	no_rank  = false;
	no_sum   = false;
	no_level = false;

	if (isFirst)
	{
		for(var i=keepTop; i<ie; i++)
		{
			je = sheetTablePanel.rows[0].cells.length - keepRight;
			for(var j=keepLeft; j<je; j++)
				sheetTablePanel.rows[i].cells[j].onclick=editCell;
		}

		tblOfficeX = getParentOffset(sheetTablePanel, true);
		tblOfficeY = getParentOffset(sheetTablePanel, false);

		je = grades_length+keepLeft;
		for(var i=keepLeft; i<je; i++)
			percent[i] = parseFloat('0' + sheetTablePanel.rows[keepTop-1].cells[i].getElementsByTagName('span')[0].innerHTML) / 100;
	}

	no_rank  = true; // 不排名
	no_level = true; // 不算高低標
	ie = sheetTablePanel.rows.length - keepBottom;
	for(var i=keepTop; i<ie; i++){
		if (i >= ie - 1)             no_rank  = false; // 最後一次要排名
		if (i >= ie - grades_length) no_level = false; // 最後一輪要算高低標
		calculate(x++, i);
		if (x > y) x = keepLeft;
	}

	if (x < y)
	{
		no_rank = true;
		no_sum  = true;
		while(x < y)
		{
			if (x >= y - 1) { no_rank = false; no_sum = false; }
			calculate(x++, ie-1);
		}
	}
	no_rank  = false;
	no_sum   = false;
	no_level = false;

	sheetTablePanel.rows[0].cells[0].innerHTML = '';
	// msgwin.close();
}

var php, editor = new Object();
editor.setHTML = function(x)
{
	modifiedElements = php.unserialize(unescape(x));

	for (var i=1; i<grades.length; i++)
	{
	    if (typeof(modifiedElements[grades[i]]) == 'undefined') continue;
	    for (var j=1; j<students.length; j++)
	    {
            if (typeof(modifiedElements[grades[i]][students[j]]) == 'undefined') continue;
	        sheetTablePanel.rows[j+keepTop-1].cells[i+keepLeft-1].innerHTML = modifiedElements[grades[i]][students[j]];
		}
	}

	window.setTimeout('init(false)', 1);
	enableSaveBtm();
};
var st_id = '{$sysSession->cur_func}{$sysSession->course_id}';
window.onload=function(){
	php             = new PHP_Serializer();
	sheetTablePanel = document.getElementById('sheetTable');
	btm             = document.getElementById('saveBtm');
	window.setTimeout('init(true)', 1);
	$isSaved
	xajax_check_temp(st_id, 'FCK.editor');
	window.setInterval(function(){if (neverSaved) xajax_save_temp(st_id, escape(php.serialize(modifiedElements)) );}, 100000);
};

/**
 * 將整個陣列加總
 */
function array_sum(ary, length){
	var total = 0;
	if (length){
	    var lbound = ary.length-length;
	    var i = ary.length;
	    while(--i >= lbound)
			total += ary[i];
		return Math.round((total / length) * Math.pow(10,2)) / Math.pow(10,2);
	}
	else
		return 0;
}

/**
 * 數值型陣列排序方法 (Jscript 的 sort() 內定以字串做排序)
 */
function sortMethod(a, b){
	return a - b;
}

/**
 * 計算 x 軸與 y 軸上的結果
 */
function calculate(x,y){
	var total = 0;
	var grades_length = sheetTablePanel.rows[0].cells.length - keepLeft - keepRight;
	var users_length  = sheetTablePanel.rows.length - keepTop - keepBottom;
	var valid_grades_length = grades_length;	// 配分比重不為0的成績項目數
	var ranks = new Array();
	var c=0, prev = -1;
	var scores = new Array();
	var ie = 0, je = 0, i = 0, row, f;

	if (!no_sum)
	{
		// total & average
		i = ie = grades_length+keepLeft;
		row = sheetTablePanel.rows[y];
		while(--i >= keepLeft)
		{
		    total += (f = row.cells[i].firstChild) ?
		    		(parseFloat(f.nodeValue) * percent[i] || 0) : 0;
		    if (percent[i] == 0) valid_grades_length--;
		}
		// for(var i=keepLeft; i<ie; i++) total += parseFloat('0' + sheetTablePanel.rows[y].cells[i].innerHTML) * percent[i];

		if (total > 0){
			row.cells[ie].firstChild.nodeValue = Math.round(total * Math.pow(10,2)) / Math.pow(10,2);
			row.cells[ie+1].firstChild.nodeValue = (valid_grades_length ? (Math.round((total / valid_grades_length) * Math.pow(10,2)) / Math.pow(10,2)) : 0);
		}else{
			row.cells[ie].firstChild.nodeValue = '';
			row.cells[ie+1].firstChild.nodeValue = '';
		}
	}

	if (!no_level)
	{
		// high-level & low-level
		i = ie = users_length+keepTop;
		// for(var i=keepTop; i<ie; i++){
		while(--i >= keepTop){
			scores[c] = (f = sheetTablePanel.rows[i].cells[x].firstChild) ?
					(parseFloat(f.nodeValue) || 0) : 0;

			ranks[c++] = (f = sheetTablePanel.rows[i].cells[grades_length+1].firstChild) ?
					(parseFloat(f.nodeValue) || 0) : 0;
		}
		scores.sort(sortMethod);

		// 當無任何成績項目 則不顯示高標 低標的分數 (grade)
		if (count_grades) {
			var hight_grade = array_sum(scores, scores.length >> 1);
			if (hight_grade > 0){
				sheetTablePanel.rows[ie].cells[x].innerHTML = hight_grade;
			}

			var low_grade = array_sum(scores, scores.length);
			if (low_grade > 0){
				sheetTablePanel.rows[ie+1].cells[x].innerHTML = low_grade;
			}

		}
	}

	if (!no_rank)
	{
		ranks.sort(sortMethod);
		c=1;
		je = grades_length+keepLeft;
		for(var i=ranks.length-1; i>=0; i--){
			if (prev == ranks[i]) continue;
			for(var j=keepTop; j<ie; j++){
				// 當無任何成績項目 則不顯示排名
				if (count_grades)
					if (sheetTablePanel.rows[j].cells[je].innerHTML == ranks[i]) sheetTablePanel.rows[j].cells[je+2].innerHTML = c;
			}
			c++;
			prev = ranks[i];
		}
	}
}

/**
 * 取得 USER 按方向鍵
 */
document.onkeydown=function(e) {
	var key_code = isMZ ? e.keyCode : event.keyCode;
	var prev_xx = xx; var prev_yy = yy;
	switch(key_code){
		case key_DOWN :
			cursorMove(2, true); break;
		case key_UP :
			cursorMove(4, true); break;
		case key_LEFT :
			cursorMove(3, true); break;
		case key_RIGHT :
			cursorMove(1, true); break;
		default:
			return true;
	}
	var obj = document.getElementById('inputBox');
	var ipbox = obj.getElementsByTagName('input')[0];
	if (obj.style.display == '')  // 如果現在正在輸入
	{
		if (sheetTablePanel.rows[prev_yy].cells[prev_xx].innerHTML != ipbox.value && // 而且輸入新資料
			ipbox.value.search(/^([0-9]+(\.[0-9]+)?)?$/) === 0) // 而且是合法分數
		{
			sheetTablePanel.rows[prev_yy].cells[prev_xx].innerHTML = ipbox.value;
			if (typeof(modifiedElements[grades[prev_xx]]) == 'undefined') modifiedElements[grades[prev_xx]] = new Object();
			eval("modifiedElements[grades[prev_xx]]['" + students[prev_yy-1] + "'] ='" + ipbox.value + "';");
			calculate(prev_xx,prev_yy);
			enableSaveBtm();
		}

		obj.style.display='none';
		sheetTablePanel.rows[yy].cells[xx].onclick();
	}

	if(isMZ)
		e.cancelBubble=true;
	else
		event.cancelBubble=true;
};

/**
 * 觀看圖表
 */
function viewGraph(n){
	window.open('about:blank', 'viewGraphWin', 'width=470, height=320, toolbar=0, menubar=0, scrollbars=0, resizable=1, status=0');
	// lib/jpgraph/src/Examples/example20.php
	var users_length  = sheetTablePanel.rows.length - keepBottom + 2;
	var scores = '';
	for(var i=keepTop; i<users_length; i++){
		scores += parseFloat('0' + sheetTablePanel.rows[i].cells[n].innerHTML) + ',';
	}
	var obj = document.getElementById('viewGraphForm');
	obj.scores.value = scores.replace(/,$/, '');
	obj.submit();
}

/**
 * 將有修改的成績，送回 SERVER 端儲存
 */
function submitModified(){
	
	var cell = this;
	var obj = document.getElementById('inputBox');
	var ipbox = obj.getElementsByTagName('input')[0];
	if (obj.style.display == '' &&  // 如果現在正在輸入
		sheetTablePanel.rows[yy].cells[xx].innerHTML != ipbox.value && // 而且輸入新資料
		ipbox.value.search(/^([0-9]+(\.[0-9]+)?)?$/) === 0) // 而且是合法分數
	{
		sheetTablePanel.rows[yy].cells[xx].innerHTML = ipbox.value;
		if (typeof(modifiedElements[grades[xx]]) == 'undefined') modifiedElements[grades[xx]] = new Object();
		modifiedElements[grades[xx]][students[yy-1]] = ipbox.value;
		calculate(xx,yy);
		enableSaveBtm();
	}
	
	var x;
	var tmp='';
	for(x in modifiedElements){
		tmp += x + ':';
		if (typeof(modifiedElements[x]) == 'object'){
			for(y in modifiedElements[x]){
				tmp += y + '=' + modifiedElements[x][y] + '&';
			}
			tmp = tmp.replace(/&$/, ';');
		}
	}
	var obj = document.getElementById('saveForm');
	obj.modified.value = tmp.replace(/;$/, '');

	xajax_clean_temp(st_id);
	// 客製 begin
	window.onbeforeunload=null;
	// 客製 end
	obj.submit();
}

/**
 * 試算表放大縮小功能
 */
var zoomSizes = new Array(8, 10, 12, 14, 18, 20, 22, 24, 28, 32);
var zoomIdx = 2;
function zoom(mode){
	var nodes = document.getElementsByTagName('table')[0].getElementsByTagName('td');
	if (mode){
		if (zoomIdx == 9) return; else zoomIdx++;
	}
	else{
		if (zoomIdx == 0) return; else zoomIdx--;
	}
	for(var i=0; i<nodes.length; i++){
		nodes[i].style.fontSize = zoomSizes[zoomIdx] + 'px';
	}
	cm_not_yet_calc = true;
}

var th_idx = 0;
function property(x, gid){
    batchAdjustComplete(x, false);

	var propertyPanel = document.getElementById('gradePropertyPanel');
	var inputs = propertyPanel.getElementsByTagName('input');

	inputs[0].value = sheetTablePanel.rows[0].cells[x].firstChild.getAttribute('title');
	inputs[1].value = parseInt(sheetTablePanel.rows[1].cells[x].getElementsByTagName('span')[0].innerHTML);
	inputs[2].value = gid;

	propertyPanel.style.left = getParentOffset(sheetTablePanel.rows[0].cells[x], true);
	propertyPanel.style.top  = getParentOffset(sheetTablePanel.rows[0].cells[x], false);
	propertyPanel.style.display = 'inline';
	th_idx = x;
	return false;
}

function property_complete(form, isApply)
{
	document.getElementById('gradePropertyPanel').style.display = 'none';
	if (isApply)
	{
		sheetTablePanel.rows[0].cells[th_idx].firstChild.setAttribute('title', form.gradeCaption.value);
		sheetTablePanel.rows[0].cells[th_idx].firstChild.innerHTML = form.gradeCaption.value.substr(0,4);
		sheetTablePanel.rows[1].cells[th_idx].getElementsByTagName('span')[0].innerHTML = form.gradePercent.value + ' % ';
		percent[th_idx] = parseFloat(form.gradePercent.value) / 100;
		form.submit();
		form.reset();
		window.setTimeout('init(true)', 1);
	}
}

var neverSaved = false;
var btm;
function enableSaveBtm(){
	neverSaved = true;
	btm.disabled = false;
	btm.style.fontWeight = 'bold';
	btm.style.borderColor = 'red';
}

function exportCSV()
{
	var form = document.getElementById('exportForm');
	form.table_html.value = sheetTablePanel.innerHTML.replace(/\bhref="javascript:;"/ig, '').replace(/\bon\w=("[^"]*"|[^"\s]+)/ig, '');
	form.submit();
}

var cm_not_yet_calc = true;
function markcolumn(td, isShow)
{
	var frameBox = document.getElementById('columnMarker');
	if (isShow)
	{
	    if (cm_not_yet_calc)
	    {
			var column_idx = td.cellIndex;
			var y1         = getParentOffset(td, false);
			var t          = td.parentNode.parentNode.parentNode;
			var tailTd     = t.rows[t.rows.length-keepBottom-1].cells[column_idx];
			var y2         = getParentOffset(tailTd, false);

			// #47358 Chrome [辦公室/成績管理/成績總表] 批次加減分數的功能「+/-」無法使用：調整紅色框線範圍，不要壓到+/-
            frameBox.style.height  = y2 - y1 + (isMZ ? 0 : 1)-3;
			frameBox.style.top     = td.offsetTop  + tblOfficeY + td.offsetHeight +2;
			cm_not_yet_calc = false;
		}

		frameBox.style.width   = td.offsetWidth - (isMZ ? 1 : 0);
		frameBox.style.left    = td.offsetLeft + tblOfficeX;
		frameBox.style.display = '';
	}
	else
	    frameBox.style.display = 'none';
}

var column_idx = -1;
function batchAdjust(td)
{
    property_complete(td, false);

    column_idx = td.cellIndex;
	var batchAdjustPanel = document.getElementById('batchAdjustScorePanel');

	batchAdjustPanel.style.left = getParentOffset(sheetTablePanel.rows[0].cells[column_idx], true)-50;
	batchAdjustPanel.style.top  = getParentOffset(sheetTablePanel.rows[0].cells[column_idx], false);
	document.getElementById('inputBox').style.display = 'none';
	batchAdjustPanel.style.display = 'inline';
	batchAdjustPanel.style.zIndex = 2;
}

function batchAdjustComplete(form, isApply)
{
	if (isApply)
	{
		var headTd = sheetTablePanel.rows[keepTop].cells[column_idx];
		var tailTd = sheetTablePanel.rows[sheetTablePanel.rows.length-keepBottom-1].cells[column_idx];
		var origin_score, new_score, score;
		var min_score = parseInt('0' + form.min_score.value, 10);
		var max_score = parseInt('0' + form.max_score.value, 10);

		if (form.adjustType[0].checked)
		{
			score = form.shift_score.value;
			
			if (score.search(/^[-|+]?[0-9]+(\.[0-9]+)?$/) === 0) // 而且是合法分數
			{
			    score = parseFloat(score);
				for(var i=keepTop; i<sheetTablePanel.rows.length-keepBottom; i++)
				{
				    if ((origin_score = sheetTablePanel.rows[i].cells[column_idx].innerHTML.replace(/^\s+|\s+$/g, '')) == '') continue;
					origin_score = parseFloat(origin_score);
					new_score    = Math.round((origin_score + score) * Math.pow(10,2)) / Math.pow(10,2);
					if (form.min_limit.checked) new_score = Math.max(new_score, min_score);
					if (form.max_limit.checked) new_score = Math.min(new_score, max_score);
					sheetTablePanel.rows[i].cells[column_idx].innerHTML = new_score;
					if (typeof(modifiedElements[grades[column_idx]]) == 'undefined') modifiedElements[grades[column_idx]] = new Object();
					modifiedElements[grades[column_idx]][students[i-1]] = new_score;
					calculate(column_idx,i);
				}
				enableSaveBtm();
			}
			else
			    alert('Incorrect score.');
            form.reset();
		}
		else if (form.adjustType[1].checked)
		{
				for(var i=keepTop; i<sheetTablePanel.rows.length-keepBottom; i++)
				{
				    if ((origin_score = sheetTablePanel.rows[i].cells[column_idx].innerHTML.replace(/^\s+|\s+$/g, '')) == '') continue;
					origin_score = parseFloat(origin_score);
					new_score    = Math.round(Math.pow(origin_score, 0.5) * 1000) / 100;
					if (form.min_limit.checked) new_score = Math.max(new_score, min_score);
					if (form.max_limit.checked) new_score = Math.min(new_score, max_score);
					sheetTablePanel.rows[i].cells[column_idx].innerHTML = new_score;
					if (typeof(modifiedElements[grades[column_idx]]) == 'undefined') modifiedElements[grades[column_idx]] = new Object();
					modifiedElements[grades[column_idx]][students[i-1]] = new_score;
					calculate(column_idx,i);
				}
				enableSaveBtm();
				form.reset();
		}
		else if (form.adjustType[2].checked)
		{
		    score = form.user_defined_formula.value;
		    if (score.search(/^[0-9S+*/^.()-]+$/i) === 0)
			{
			    score = score.replace(/([S0-9.]+|\([^)]+\))\s*\^\s*([S0-9.]+|\([^)]+\))/ig, 'Math.pow($1, $2)');
				for(var i=keepTop; i<sheetTablePanel.rows.length-keepBottom; i++)
				{
				    if ((origin_score = sheetTablePanel.rows[i].cells[column_idx].innerHTML.replace(/^\s+|\s+$/g, '')) == '') continue;
                    origin_score = parseFloat(origin_score);
					eval('new_score = Math.round((' + score.replace(/S/i, 'origin_score') + ') * Math.pow(10,2)) / Math.pow(10,2);');
					if (form.min_limit.checked) new_score = Math.max(new_score, min_score);
					if (form.max_limit.checked) new_score = Math.min(new_score, max_score);
					sheetTablePanel.rows[i].cells[column_idx].innerHTML = new_score;
					if (typeof(modifiedElements[grades[column_idx]]) == 'undefined') modifiedElements[grades[column_idx]] = new Object();
					modifiedElements[grades[column_idx]][students[i-1]] = new_score;
					calculate(column_idx,i);
				}
				enableSaveBtm();
			}
			else
			    alert('Incorrect formula.');
            form.reset();
		}
	}
	
	document.getElementById('batchAdjustScorePanel').style.display = 'none';
}

function checkInteger(e)
{
	if (isMZ) event = e;
	if ((event.keyCode < 48 || (event.keyCode > 57 && event.keyCode < 96) || (event.keyCode > 105)) &&  // 不是數字
		event.keyCode != 8  &&                          // 不是 Backspace
		event.keyCode != 9  &&                          // 不是 Tab
		event.keyCode != 35 &&                          // 不是 Home
		event.keyCode != 36 &&                          // 不是 End
		event.keyCode != 46 &&                          // 不是 Delete
		event.keyCode != 110 &&                         // 不是小數點
		event.keyCode != 190                            // 不是小數點
	   )
	{
	    event.cancelBubble=true;
	    return false;
	}
}

function checkAdjust(e)
{
	if (isMZ) event = e;
	if ((event.keyCode < 48 || (event.keyCode > 57 && event.keyCode < 96) || (event.keyCode > 105)) &&  // 不是數字
		event.keyCode != 8   &&                         // 不是 Backspace
		event.keyCode != 9   &&                         // 不是 Tab
		event.keyCode != 35  &&                         // 不是 Home
		event.keyCode != 36  &&                         // 不是 End
		event.keyCode != 46  &&                         // 不是 Delete
		event.keyCode != 189 &&                         // 不是 -
		event.keyCode != 110 &&                         // 不是 .
		event.keyCode != 190 &&                         // 不是 .
		event.keyCode != 107 &&                         // 不是 +		＠MIS#18853-批次調整分數：平移幾分，不能用減號鍵輸入成功
		event.keyCode != 109 &&                         // 不是 -		＠補上+、-、左、右、上、下 by Small 2010-10-27
		event.keyCode != 37  &&                         // 不是 left
		event.keyCode != 38  &&                         // 不是 up
		event.keyCode != 39  &&                         // 不是 right
		event.keyCode != 40                             // 不是 down
	   )
	{
	    event.cancelBubble=true;
	    return false;
	}
}

window.onunload=function(){
	if(neverSaved) return '';
};

/* 客製 begin */
var wtuc_get_keypress = false;	/* 是否有輸入或點 input 的欄位 */

if (isMZ) window.captureEvents(Event.KEYPRESS);

function captureKey(e){
	if((event.keyCode > 0) && (!wtuc_get_keypress)){
		wtuc_get_keypress = true;
	}
}
document.onkeypress=captureKey;

window.onbeforeunload=function(){
	if(wtuc_get_keypress) {		
		return "{$MSG['wtuc_edit_grade'][$sysSession->lang]}";
	}
};
/* 客製 end */
EOB;
// '{$MSG['comfirm_exit'][$sysSession->lang]}';
	  showXHTML_script('inline', $scr);
	  $xajax_save_temp->printJavascript('/lib/xajax/');
	  showXHTML_script('include', '/lib/PHP_Serializer.js');
	showXHTML_head_E();
	showXHTML_body_B();
	  showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
		showXHTML_tr_B();
		  showXHTML_td_B();
		$ary[] = array($MSG['online_sheet'][$sysSession->lang], 'tabsSet',  '');
		showXHTML_tabs($ary, 1);
		  showXHTML_td_E();
		showXHTML_tr_E();
		showXHTML_tr_B();
		  showXHTML_td_B('valign="top" class="bg01"');
		showXHTML_form_B('style="display: inline"');
		  showXHTML_table_B('border="0" cellpadding="0" cellspacing="1" class="box01"');
			showXHTML_tr_B('class="bg03 font01" style="height: 28px"');
			  showXHTML_td_B();
			echo $MSG['cursor_direct'][$sysSession->lang];
 			    showXHTML_input('select', '', array($MSG['disable'][$sysSession->lang],
								'&rarr;',
								'&darr;',
								'&larr;',
								'&uarr;'), 0, 'size="1" id="cursorDir" class="box02" onkeypress="this.blur();"');
				showXHTML_input('button', '', $MSG['save_result'][$sysSession->lang], '', 'id="saveBtm" class="button01" onclick="submitModified();" disabled');
				showXHTML_input('button', '', $MSG['export_csv'][$sysSession->lang], '', 'class="button01" onclick="exportCSV();"');
			  showXHTML_td_E();
			  showXHTML_td_B('align="right"');
				echo '<img src="/theme/', $sysSession->theme, '/teach/plus.gif"  title="Zoom In"  border="0" valign="absmiddle" onclick="zoom(1)" style="cursor: pointer">&nbsp;',
					 '<img src="/theme/', $sysSession->theme, '/teach/minus.gif" title="Zoom Out" border="0" valign="absmiddle" onclick="zoom(0)" style="cursor: pointer">';
			  showXHTML_td_E();
			showXHTML_tr_E();
/*
			showXHTML_tr_B('class="bg03 font01" style="height: 28px"');
			  showXHTML_td('colspan="2"',$MSG['desc_export'][$sysSession->lang]);
			showXHTML_tr_E();
*/
			showXHTML_tr_B('class="bg04 font01"');
			  showXHTML_td_B('colspan="2"');

			showXHTML_table_B('id="sheetTable" border="0" cellpadding="3" cellspacing="1" class="box01"');

			  showXHTML_tr_B('class="bg02 font01"');
				showXHTML_td('align="center" style="font-weight: bold; color: red"', '&nbsp;');
				$i = 1;
				foreach($grades as $grade_id => $v){
				showXHTML_td('align="center" style="font-weight: bold"', '<a href="javascript:;" onclick="return property(' . $i++ . ',' . $grade_id . ");\" title=\"{$v['title']}\" class=\"cssAnchor\">" . mb_strimwidth($v['title'], 0, 6, '', 'UTF-8') . '</a>');
				}
				showXHTML_td('rowspan="2" valign="bottom" align="center" style="font-weight: bold" nowrap', $MSG['total_score'][$sysSession->lang]);
				showXHTML_td('rowspan="2" valign="bottom" align="center" style="font-weight: bold" nowrap', $MSG['average'][$sysSession->lang]);
				showXHTML_td('rowspan="2" valign="bottom" align="center" style="font-weight: bold" nowrap', $MSG['ranking'][$sysSession->lang]);
			  showXHTML_tr_E();

			  showXHTML_tr_B('class="bg02 font01" style="line-height: 3.6em;"');
				showXHTML_td('valign="bottom" align="center" style="font-weight: bold" nowrap', $MSG['student'][$sysSession->lang]);
				foreach($grades as $grade_id => $v){
				showXHTML_td('align="center" style="font-weight: bold; line-height: 0.8em"', '<span style="position: relative; top: 4px; display: block;width: 3em;">' . $v['percent'] . ' %</span><br><span onmouseover="markcolumn(this.parentNode,true);" onmouseout="markcolumn(this.parentNode,false);" onclick="batchAdjust(this.parentNode);" title="' . $MSG['batch adjust score'][$sysSession->lang] . '" class="adj">+/-</span>');
				}
			  showXHTML_tr_E();

			  foreach($allUsers as $username => $realname){
				$cla = $cla == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
				showXHTML_tr_B($cla);
				  showXHTML_td('nowrap', "$realname ($username)");
				  // $total_score = 0;
				  foreach($grades as $grade_id => $v){
					showXHTML_td('align="right"', $v['grade'][$username]['score']);
					// $total_score += intval($v['grade'][$username]['score']);
				  }
				  // showXHTML_td('align="right"', $total_score);
				  // showXHTML_td('align="right"', empty($grades_length) ? 0 : round($total_score / $grades_length, 1));
				  showXHTML_td('align="right"', '&nbsp;');
				  showXHTML_td('align="right"', '&nbsp;');
				  showXHTML_td('align="right"', '&nbsp;');
				showXHTML_tr_E();
			  }
			  $cla = $cla == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
			  showXHTML_tr_B($cla);
				showXHTML_td('align="right" title="' . $MSG['high_level_hint'][$sysSession->lang] . '"', $MSG['high_level'][$sysSession->lang]);
				for($i=0; $i<$grades_length; $i++) showXHTML_td('align="right"', '&nbsp;');
				showXHTML_td('colspan="3"', '&nbsp;');
			  showXHTML_tr_E();
			  $cla = $cla == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
			  showXHTML_tr_B($cla);
				showXHTML_td('align="right" title="' . $MSG['low_level_hint'][$sysSession->lang] . '"', $MSG['low_level'][$sysSession->lang]);
				for($i=0; $i< $grades_length; $i++) showXHTML_td('align="right"', '&nbsp;');
			showXHTML_td('colspan="3"', '&nbsp;');
			  showXHTML_tr_E();
			  $cla = $cla == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
			  showXHTML_tr_B($cla);
				showXHTML_td('align="right" title="' . $MSG['graph_hint'][$sysSession->lang] . '"', $MSG['graph'][$sysSession->lang]);
				$i = 0;
	            foreach($grades as $grade_id => $v){
					$temp = ($i+1);

					if (is_array($grades[$grade_id]['grade'])){
						$gd_graph = '<img src="/theme/default/teach/graph.gif" border="0" onclick="viewGraph(%NUM%);" style="cursor: pointer">';
					}else{
						$gd_graph = '&nbsp';
					}

					$gd_graph1 = str_replace('%NUM%',$temp,$gd_graph);

					showXHTML_td('align="right"',$gd_graph1);

					$i++;
	            }


				showXHTML_td('colspan="3"', '&nbsp;');
			  showXHTML_tr_E();

			showXHTML_table_E();

			  showXHTML_td_E();
			showXHTML_tr_E();
		  showXHTML_table_E();

		showXHTML_form_E();
		  showXHTML_td_E();
		showXHTML_tr_E();
	  showXHTML_table_E();


	  $ary = array(array($MSG['grade_context'][$sysSession->lang]));
	  showXHTML_tabFrame_B($ary, 1, 'gradePropertyForm', 'gradePropertyPanel', 'action="grade_modify3.php" method="POST" target="empty" style="display: inline"', true);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="box01"');
			showXHTML_tr_B('class="bg03 font01"');
			  showXHTML_td('', $MSG['title'][$sysSession->lang]);
			  showXHTML_td_B();
			    showXHTML_input('text', 'gradeCaption', '', '', 'size="30" maxlength="128" class="box02"');
			  showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B('class="bg04 font01"');
			  showXHTML_td('', $MSG['percent'][$sysSession->lang]);
			  showXHTML_td_B();
			    showXHTML_input('text', 'gradePercent', '', '', 'size="8" maxlength="5" class="box02"'); echo "%";
			  showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B('class="bg03 font01"');
			  showXHTML_td_B('colspan="2" align="right" style="padding-right: 1em"');
			    showXHTML_input('hidden', 'grade_id', '');
			    showXHTML_input('button', '', $MSG['sure'][$sysSession->lang]  , '', 'class="cssBtn" onclick="property_complete(this.form, true);"');
			    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="property_complete(this.form, false);"');
			  showXHTML_td_E();
			showXHTML_tr_E();
          showXHTML_table_E();
	  showXHTML_tabFrame_E();


	  $ary = array(array($MSG['batch adjust score'][$sysSession->lang]));
	  showXHTML_tabFrame_B($ary, 1, 'batchAdjustScoreForm', 'batchAdjustScorePanel', 'method="POST" style="display: inline"', true);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="box01"');
			showXHTML_tr_B('class="bg03 font01"');
			  showXHTML_td_B();
			    showXHTML_input('radio', 'adjustType', array(1 => ''), 1);
			  showXHTML_td_E();
			  showXHTML_td_B();
			    echo $MSG['shift score'][$sysSession->lang];
				showXHTML_input('text', 'shift_score', '', '', 'size="6" maxlength="6" class="box02" onkeydown="return checkAdjust();"');
				echo $MSG['shift score2'][$sysSession->lang];
			  showXHTML_td_E();
			  showXHTML_td('', $MSG['input the score'][$sysSession->lang]);
			showXHTML_tr_E();
			
			showXHTML_tr_B('class="bg04 font01"');
			  showXHTML_td_B();
			    showXHTML_input('radio', 'adjustType', array(2 => ''));
			  showXHTML_td_E();
			  showXHTML_td('colspan="2"', $MSG['square root and multiply 10'][$sysSession->lang]);
			showXHTML_tr_E();

			showXHTML_tr_B('class="bg03 font01"');
			  showXHTML_td_B();
			    showXHTML_input('radio', 'adjustType', array(3 => ''), 1);
			  showXHTML_td_E();
			  showXHTML_td_B('style="line-height: 1.5em"');
			    echo $MSG['user defined formula :'][$sysSession->lang], '<br>';
				showXHTML_input('text', 'user_defined_formula', '', '', 'size="30" maxlength="30" class="box02"');
			  showXHTML_td_E();
			  showXHTML_td('', '<ul style="line-height: 1.5em; margin-bottom: 0; margin-left: 1.5em">' . $MSG['user defined formula tips'][$sysSession->lang] . '</ul>');
			showXHTML_tr_E();

			showXHTML_tr_B('class="bg04 font01"');
			  showXHTML_td('', '');
			  showXHTML_td_B('colspan="2"');
			    showXHTML_input('checkbox', 'min_limit', 1, 1); echo $MSG['score low bound'][$sysSession->lang];
                showXHTML_input('text', 'min_score', 0, '', 'size="5" maxlength="3" class="cssInput" onkeydown="return checkInteger();"'); echo $MSG['score low bound1'][$sysSession->lang], '<br>';
			    showXHTML_input('checkbox', 'max_limit', 2, 2); echo $MSG['score high bound'][$sysSession->lang];
			    showXHTML_input('text', 'max_score', 100, '', 'size="5" maxlength="3" class="cssInput" onkeydown="return checkInteger();"'); echo $MSG['score high bound1'][$sysSession->lang];
			  showXHTML_td_E();
			showXHTML_tr_E();

			showXHTML_tr_B('class="bg03 font01"');
			  showXHTML_td_B('colspan="3" align="right" style="padding-right: 1em"');
			    showXHTML_input('button', '', $MSG['sure'][$sysSession->lang]  , '', 'class="cssBtn" onclick="batchAdjustComplete(this.form, true);"');
			    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="batchAdjustComplete(this.form, false);"');
			  showXHTML_td_E();
			showXHTML_tr_E();
          showXHTML_table_E();
	  showXHTML_tabFrame_E();

          // ipad chrome 另開視窗
          if (isMobileBrowser() === true) {
              $csv_target = 'csv';
          } else {
              $csv_target = 'empty';
          }

	  echo <<< EOB
<div id="inputBox" style="position: absolute; display: none">
  <form style="display: inline" onsubmit="return false;">
	<input type="text" maxlength="6" style="border:2px orange solid; font-size: 16px">
  </form>
</div>

<div id="columnMarker" style="position: absolute; display: none; border: 1px dashed red"></div>

<form id="saveForm" method="POST" action="grade_save.php">
<input type="hidden" name="modified" value="">
</form>
<form id="viewGraphForm" method="POST" action="grade_graph1.php" target="viewGraphWin">
<input type="hidden" name="scores" value="">
</form>
<form id="exportForm" method="POST" action="grade_exportCSV.php" target="{$csv_target}">
<input type="hidden" name="table_html" value="">
</form>

EOB;
	  $scr = <<< EOB
/**
 * 取得 USER 在編修試算表某儲存格時，按鍵事件
 */
document.getElementById('inputBox').getElementsByTagName('input')[0].onkeydown=function (e){
	var key_code = isMZ ? e.keyCode : event.keyCode;
	var dir = parseInt(document.getElementById('cursorDir').value);
	var obj = document.getElementById('sheetTable');

	switch(key_code){
		case 13: // 按了 Enter
			if (this.value.search(/^([0-9]+(\.[0-9]+)?)?$/) !== 0){
				window.status = '{$MSG['format_incorrect'][$sysSession->lang]}';
				this.select(); return false;
			}
			if (typeof(modifiedElements[grades[xx]]) == 'undefined') modifiedElements[grades[xx]] = new Object();
			obj.rows[yy].cells[xx].innerHTML = this.value;
			eval("modifiedElements[grades[xx]]['" + students[yy-1] + "'] ='" + this.value + "';");
			window.setTimeout('calculate(' + xx + ',' + yy + ')', 1);
			// calculate(xx,yy);
			enableSaveBtm();
			if (dir > 0 && dir < 5)
			{
				cursorMove(dir, true);
				this.value = obj.rows[yy].cells[xx].innerHTML
			}
			else
			{
				document.getElementById('inputBox').style.display = 'none';
				dir = 0;
			}
			if (dir) obj.rows[yy].cells[xx].onclick();
			break;
		case 27: // 按了 Esc
			document.getElementById('inputBox').style.display = 'none';
			break;
		case key_DOWN :
		case key_UP :
		case key_LEFT :
		case key_RIGHT :
		case 8:
		case 35:
		case 36:
		case 46:
		case 190:
			return; // 按了方向鍵，不處理，直接沸升交由上面的 document.keypress 處理
		default:
			if(key_code < 48 || (key_code > 57 && key_code < 96) || (key_code > 105 && key_code != 110 && key_code != 190))
			{
				if(isMZ)
					e.cancelBubble=true;
				else
					event.cancelBubble=true;
				return false;
			}
			break;
	}
	if(isMZ)
		e.cancelBubble=true;
	else
		event.cancelBubble=true;
	window.status = '';
}
EOB;
	  showXHTML_script('inline', $scr);
	echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
	showXHTML_body_E();
?>

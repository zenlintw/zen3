<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/11                                                            *
	 *		work for  :                                                                       *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *      $Id: stud_groups.php,v 1.1 2010/02/24 02:40:31 saly Exp $:                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	
	$sysSession->cur_func='1000400300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 預設的群組名稱
	$default_grpup_name = $MSG['new_group']['Big5'] . '\\f' . $MSG['new_group']['GB2312'] . '\\f' . $MSG['new_group']['en'] . '\\f' . $MSG['new_group']['EUC-JP'] . '\\f' . $MSG['new_group']['user_define'];
	// 尚未分組的群組名稱
	$never_group_name = $MSG['never_grouping']['Big5'] . '\\f' . $MSG['never_grouping']['GB2312'] . '\\f' . $MSG['never_grouping']['en'] . '\\f' . $MSG['never_grouping']['EUC-JP'] . '\\f' . $MSG['never_grouping']['user_define'];

	switch (strtolower($sysSession->lang)) {
		case 'big5'        : $lang = 0; break;
		case 'gb2312'      : $lang = 1; break;
		case 'en'          : $lang = 2; break;
		case 'euc-jp'      : $lang = 3; break;
		case 'user_define' : $lang = 4; break;
	}

	function escapeSingleQuote($str)
	{
		static $pattern = array('\\' => '\\\\', "'" => "\\'");
		return strtr($str, $pattern);
	}

		// 開始 output HTML
		showXHTML_head_B($MSG['student_grouping'][$sysSession->lang]);
		  showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
		  $scr = <<< EOB

var times_id = 1;
var newGroupId = 9001;
var langIndex = {$lang};

var notSave = false;
var MSG_EXIT    = "{$MSG['alert_msg'][$sysSession->lang]}";

EOB;
		$acl = Array();
		$curGroups = <<< EOB
// ID, group_name, captain, member_array
var curGroups = new Array(
			  new Array(0, '{$never_group_name}', '', new Array(
EOB;
		$sqls = 'select M.username,A.first_name,A.last_name ' .
				'from WM_term_major as M left join WM_user_account as A ' .
				"on M.username=A.username where M.course_id={$sysSession->course_id} and (M.role & {$sysRoles['student']}) " .
				'order by M.username';
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sqls);
		$allStudents = array();
        // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
        while(!$RS->EOF){
            $allStudents[$RS->fields['username']] = escapeSingleQuote(checkRealname($RS->fields['first_name'],$RS->fields['last_name']));
            $RS->MoveNext();
        }

		$minTid = intval($_GET['tid']);
		if(empty($minTid)){
			list($minTid) = dbGetStSr('WM_student_group', 'min(team_id)', "course_id={$sysSession->course_id}", ADODB_FETCH_NUM);
			if(empty($minTid)){
				$tean_name = array( 'Big5'        => $MSG['new_group']['Big5'],
									'GB2312'      => $MSG['new_group']['GB2312'],
									'en'          => $MSG['new_group']['en'],
									'EUC-JP'      => $MSG['new_group']['EUC-JP'],
									'user_define' => $MSG['new_group']['user_define']
								);
				$tean_name  = serialize($tean_name);
				dbNew('WM_student_separate','course_id,team_id,team_name', "{$sysSession->course_id},1,'".$tean_name."'");
				$minTid = 1;
			}
		}

		$sqls = 'select G.*,D.username,A.first_name,A.last_name 
		         from WM_student_group as G
		         left join WM_student_div as D on G.course_id=D.course_id and G.group_id=D.group_id and G.team_id=D.team_id 
		         left join WM_user_account as A on D.username=A.username
                         join WM_term_major as M on M.username=A.username and G.course_id=M.course_id ' .
		        "where G.course_id={$sysSession->course_id} and G.group_id and G.team_id=$minTid AND role&32 " .
		        'order by G.permute,G.team_id,G.group_id,D.username';
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sqls);
		$cur_gid = ''; $grouping = '';
		while(!$RS->EOF){
            // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
            $realname = escapeSingleQuote(checkRealname($RS->fields['first_name'],$RS->fields['last_name']));
			unset($allStudents[$RS->fields['username']]);
			if ($RS->fields['group_id'] != $cur_gid){
				$tt = unserialize($RS->fields['caption']);
				$tt['Big5']			= escapeSingleQuote($tt['Big5']		 );
				$tt['GB2312']		= escapeSingleQuote($tt['GB2312']	 );
				$tt['en']			= escapeSingleQuote($tt['en']		 );
				$tt['EUC-JP']		= escapeSingleQuote($tt['EUC-JP']	 );
				$tt['user_define']	= escapeSingleQuote($tt['user_define']);

				// 群組的名稱
				$tt_string = $tt['Big5'] . '\\f' . $tt['GB2312'] . '\\f' . $tt['en'] . '\\f' . $tt['EUC-JP'] . '\\f' . $tt['user_define'];
				
				if (empty($RS->fields['username']))
					$grouping .= (empty($cur_gid)?'':"'')),\n" ) . "\t\t\t  new Array({$RS->fields['group_id']},'{$tt_string}','{$RS->fields['captain']}', new Array('',";
                else
                	$grouping .= (empty($cur_gid)?'':"'')),\n" ) . "\t\t\t  new Array({$RS->fields['group_id']},'{$tt_string}','{$RS->fields['captain']}', new Array('{$RS->fields['username']}\\t{$realname}',";

				$instance = sprintf('%4u%04u',$RS->fields['team_id'],$RS->fields['group_id']);
				$acl[] = aclGetAclArrayByInstance($sysSession->cur_func, $sysSession->course_id, $instance);

				$cur_gid = $RS->fields['group_id'];
			}
			else if (!empty($RS->fields['username']))
				$grouping .= "'{$RS->fields['username']}\\t{$realname}',";

			$RS->MoveNext();
		}
		foreach($allStudents as $k => $v) $curGroups .= "'$k\\t$v',";
		$curGroups .= "'')),\n" . $grouping . (empty($grouping) ? '' : "'')),\n");

		$curGroups .= <<< EOB
			   ''
			 );
EOB;
		if(!empty($acl)) {
			foreach($acl as $item)
				$acl1[] = 'new Array('.implode(",\n",$item).')';

			$acl_lists = 'var acl_lists = new Array('.implode(',',$acl1).");\n";
		} else
			$acl_lists = '';
		$scr .= "\n".$curGroups . "\n".$acl_lists;
		  $scr .= <<< EOB

var acl_undo = new Array();
curGroups.pop();
for(var i=0; i<curGroups.length; i++) curGroups[i][3].pop();

function neverSave(){
    notSave = true;
	document.getElementById('saveButton').style.fontWeight = 'bold';
}

function removeElement(idx, val){
	var a = curGroups[idx][3].join('\\n');
	a = a.replace(val, '');
	curGroups[idx][3] = (a.search(/^\\n*$/) == 0) ? (new Array()) : a.replace(/^\\n+|\\n+$/g, '').split(/\\n+/);
}

function grouping(group_id){

	var procTable = document.getElementById('groupsTab');
	var procForm  = document.getElementById('procForm');
	var nodes = new Array();
	var idx = -1;

	for(var i=0; i<curGroups.length; i++){
		if (typeof(curGroups[i]) != 'undefined' && curGroups[i][0] == group_id){
			idx = i;
			break;
		}
	}
	if (idx == -1) return;

	for(var i=0; i<curGroups.length; i++){
		if (i == idx || typeof(curGroups[i]) == 'undefined') continue;
		nodes = procTable.rows[1].cells[i].getElementsByTagName('input');
		for(var j=nodes.length-1; j>=0; j--){
			if (nodes[j].checked){
				curGroups[idx][3].push(un_htmlspecialchars(nodes[j].value));
				removeElement(i, un_htmlspecialchars(nodes[j].value));
			}
		}
	}
	neverSave(); DisplayGroups();
}

var dump_ident=0;
function str_repeat(s,n) { var ss = '';  for(var i=0;i<n;i++) ss += s; return ss;}
function dump(ar,name) {
	dump_ident++;
	var ident = str_repeat('  ',dump_ident);
	var ret = '';
	if(!ar) {
		dump_ident--;
		return 'null';
	}
	if(!ar.length) {
		dump_ident--;
		return ar;
	}
	for(var i=0;i<ar.length;i++) {
		ret += '\\n'+ident +'  '+ name +'['+i+']=>';
		if(typeof(ar[i])=='object')
			ret += dump(ar[i], name + '[' + i + ']');
		else
			ret += ar[i];
	}
	dump_ident--;
	return ret;
}

function addGroup(){
	// curGroups.pop();
	curGroups.push(new Array(newGroupId, '{$default_grpup_name}', '', new Array()));
	acl_lists.push(new Array());
	var acl_idx = getAclIdFromGroupId(newGroupId++);
	if(acl_idx != -1) {
		if(!new_acl_row(acl_idx))
			alert('new_acl_row (' + acl_idx + ') failed');
	}
	// curGroups.push('');
	neverSave(); DisplayGroups();
}

function rmGroup(){
	if (!confirm("{$MSG['chk_delgroup'][$sysSession->lang]}")) return false;
	var procTable = document.getElementById('groupsTab');
	var nodes = procTable.rows[0].getElementsByTagName('input');
	for(var i=nodes.length-1; i>=0; i--){
		if (nodes[i].checked)
		for(var j=curGroups.length-1; j>0; j--){
			if (typeof(curGroups[j]) != 'undefined' && nodes[i].value == curGroups[j][0]){
			    curGroups[0][3] = curGroups[0][3].concat(curGroups[j][3]);
				var acl_idx = getAclIdFromGroupId(curGroups[j][0]);
				if(acl_idx != -1) {
					if(!rm_acl_row(acl_idx))
						alert('rm_acl_row (' + acl_idx + ') failed');
					// show_acl_table_html();
					// alert('j='+j+'\\n1.acl_lists:\\n' + dump(acl_lists,'acl_lists'));
				}

				// rm_acl_row(curGroups[j][0]);
				curGroups.splice(j,1);
				acl_lists.splice(acl_idx,1);
				// alert('2.acl_lists:\\n' + dump(acl_lists,'acl_lists'));
				break;
			}
		}
	}
	neverSave(); DisplayGroups();
}

function cleanGroup(){
	if (!confirm("{$MSG['js_msg09'][$sysSession->lang]}")) return false;
	for (var i=1; i<curGroups.length; i++){
		curGroups[0][3] = curGroups[0][3].concat(curGroups[i][3]);
		curGroups[i][3] = new Array();
	}
	curGroups[0][3].sort();
	neverSave(); DisplayGroups();
}

function DisplayGroups(){
	var IM = '<table border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable" id="groupsTab">';
	var cla = 'bg04';
	var title = '';

	IM += '<tr class="font01">';
	for(var i=0; i<curGroups.length; i++){
		if (typeof(curGroups[i]) == 'undefined') continue;
		cla = cla == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn' ;
		// title = curGroups[i][1].split(/\\f/, 5);
		title = curGroups[i][1].split('\\f');
		IM += '<td width="200" nowrap class="' + cla + '">' + (i?('<input type="checkbox" value="' +
		      curGroups[i][0] + '">'):'') + '<a href="javascript:;" onclick="grouping(' +
		      curGroups[i][0] + '); return false;">' + title[langIndex] +
		      '</a>' + (i > 0 ? ('&nbsp;&nbsp;<a href="javascript:;" onclick="setProperty(' + curGroups[i][0] +
		      '); return false;"><img border="0" align="absmiddle" src="/theme/default/teach/icon_property.gif"></a></td>')
		      : '</td>');
	}

	var uname = null;
	var captain = "";
	cla = 'cssTrOdd';
	IM += '</tr><tr class="font01">';
	for(var i=0; i<curGroups.length; i++){
		if (typeof(curGroups[i]) == 'undefined') continue;
		cla = cla == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn' ;
		IM += '<td width="200" valign="top" nowrap class="' + cla + '">';
		for(var j=0; j<curGroups[i][3].length; j++){

			uname = curGroups[i][3][j].split(/\\t/, 2);
			captain = (uname[0] == curGroups[i][2]) ? ' class="font02"' : '';

			if ((curGroups[i][3][j].indexOf(/\\t/) == -1) && (curGroups[i][3][j].length == 1)) continue;

			var temp = htmlspecialchars(curGroups[i][3][j]);
			if (temp != '')
			IM += '<input type="checkbox" value="' + temp + '">&nbsp;<span' + captain + '>' + curGroups[i][3][j] + '</span> <br>';
		}
		IM += '</td>';
	}
	IM += '</tr></table>';

	document.getElementById('procForm').innerHTML = IM;

	// Hide "Group Property" if click any input element.
	var objs = document.getElementById('tabsSet1').getElementsByTagName('input');
	for(var i = 0; i < objs.length; i++) objs[i].onmousedown = function(){ document.getElementById('setPropertyPanel').style.display = 'none'; };
}

function freezeBox(){
	var obj = document.getElementById('setPropertyPanel');
	// obj.style.top   = document.body.scrollTop  + 60;
	// obj.style.left  = document.body.scrollLeft + screen.width - 400;
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel

	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 460;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
	obj.style.top   = document.body.scrollTop  + 60;

}

// 設定小組內容 (group content)
var isSetProperty = false;
function setProperty(gid)
{
	if (isSetProperty) setPropertyCancel();
    isSetProperty = true;
	var idx = -1;
	var obj = document.getElementById('setPropertyForm');
	for(var i = 1; i<curGroups.length; i++){
		if (typeof(curGroups[i]) == 'undefined') continue;
		if (curGroups[i][0] == gid){
			idx = i; break;
		}
	}
	if (idx == -1) { alert('group not found.'); return; }

	// var title = curGroups[idx][1].split(/\\f/, 5);
	var title = curGroups[idx][1].split('\\f');

	if (obj.group_name_big5 != null) {
		if (typeof(title[0]) != 'undefined'){
			obj.group_name_big5.value = title[0];
		}else{
			obj.group_name_big5.value = '';
		}
	}

	if (obj.group_name_gb != null) {
		if (typeof(title[1]) != 'undefined'){
			obj.group_name_gb.value = title[1];
		}else{
			obj.group_name_gb.value = '';
		}
	}

	if (obj.group_name_en != null) {
		if (typeof(title[2]) != 'undefined'){
			obj.group_name_en.value = title[2];
		}else{
			obj.group_name_en.value = '';
		}
	}

	if (obj.group_name_jp != null) {
		if (typeof(title[3]) != 'undefined'){
			obj.group_name_jp.value = title[3];
		}else{
			obj.group_name_jp.value = '';
		}
	}
	if (obj.group_name_ud != null) {
		if (typeof(title[4]) != 'undefined'){
			obj.group_name_ud.value = title[4];
		}else{
			obj.group_name_ud.value = '';
		}
	}

	obj.captain.value         = curGroups[idx][2];
	obj.idx.value             = idx;

	// 列出組長 (List Captain) (Begin)
	var uname = null;
	var ck = "";
	var str = '<select name="captain" class="cssInput">';
	str += '<option value=""></option>';
	for (var i = 0; i < curGroups[idx][3].length; i++) {
		if (typeof(curGroups[idx]) == 'undefined') continue;
		uname = curGroups[idx][3][i].split(/\\t/, 2);
		ck = (uname[0] == curGroups[idx][2]) ? ' selected' : '';
		str += '<option value="' + uname[0] + '"' + ck + '>' + curGroups[idx][3][i] + '</option>';
	}
	str += '</select>';
	document.getElementById('lstCaptain').innerHTML = str;
	// 列出組長 (List Captain) (End)

	// 顯示 ACL 設定 (acl setting)
	if(idx>0) {
		// 儲存 undo 資訊 (undo info)
		copyArray( acl_lists,acl_undo );
		copyACLTable(idx-1, 'read');
	}

	freezeBox();
	document.getElementById('setPropertyPanel').style.display = '';
	window.onscroll=freezeBox;
}

// 自 group_id (gid) 取得相對應之 acl index
function getAclIdFromGroupId(gid) {
	for(var i=1;i<curGroups.length;i++) {
		if(curGroups[i][0]==gid) return (i-1);
	}
	return -1
}

function add_acl(frm) {
	var idx = frm.idx.value;
	if(idx==null || idx<1 )
		return;
	acl_idx = idx -1; // curGroups 陣列比 acl_lists 陣列多一筆 (row)

	acl_hidden_flag=true;
	init_add_list(acl_idx);
}

function setPropertyComplete(){
	var obj = document.getElementById('setPropertyForm');
	var idx = parseInt(obj.idx.value);

	var title = curGroups[idx][1].split('\\f');
	curGroups[idx][1] = (obj.group_name_big5 ? obj.group_name_big5.value : title[0]) + '\\f' +
			    		(obj.group_name_gb   ? obj.group_name_gb.value   : title[1]) + '\\f' +
			    		(obj.group_name_en   ? obj.group_name_en.value   : title[2]) + '\\f' +
			    		(obj.group_name_jp   ? obj.group_name_jp.value   : title[3]) + '\\f' +
			    		(obj.group_name_ud   ? obj.group_name_ud.value   : title[4]);
	curGroups[idx][2] = obj.captain.value;

	if(idx>0) {
		// 清除 undo 資訊 (undo info)
		acl_undo = new Array();
		copyACLTable(idx-1, 'save');
	}

	obj = document.getElementById('setPropertyPanel');
	obj.style.display = 'none';
	window.onscroll=null;
	neverSave(); DisplayGroups();
	isSetProperty = false;
}
/*
	copyArray 複製陣列( 遞迴 )
	解決直接指定陣列 a=b 時, 若改變 a[i] , 則出現 b[i] 也會隨之改變之問題
 */
function copyArray(ar_src, ar_dst) {
	for(i in ar_src) {
		if(typeof(ar_src[i]) == 'object') {
			ar_dst[i] = new Array(ar_src[i].length);
			copyArray(ar_src[i], ar_dst[i]);
		} else
			ar_dst[i] = ar_src[i];
	}
}

function setPropertyCancel() {
	document.getElementById('setPropertyPanel').style.display='none';

	// 暫存恢復面板 id
	var obj = document.getElementById('setPropertyForm');
	var idx = parseInt(obj.idx.value);
	if(idx>0) {
		acl_index = idx - 1;
		// Undo ACL
		copyArray( acl_undo, acl_lists );
		acl_undo = new Array();
		generate_list(acl_index);
		copyACLTable(acl_index,'save');

		// var acl_dis = document.getElementById('aclDisplayPanel_'+(idx-1));
		// acl_dis.id = 'aclDisplayPanel';
	}
	isSetProperty = false;
}

function movePlace(m){
	var objTable = document.getElementById('groupsTab');
	var groups = objTable.rows[0].cells.length, xx;
	var tmp;

	if (groups < 2) return;
	if (m){	// Back
		for(var i=groups-1; i>0; i--){
			xx = objTable.rows[0].cells[i].getElementsByTagName('input');
			if (xx[0].type == 'checkbox' && xx[0].checked){
				if (i == groups-1) continue;  // exclude last group
				tmp = curGroups[i];
				curGroups[i] = curGroups[i+1];
				curGroups[i+1] = tmp;
			}
		}
	}
	else{	// forward
		for(var i=2; i<groups; i++){
			xx = objTable.rows[0].cells[i].getElementsByTagName('input');
			if (xx[0].type == 'checkbox' && xx[0].checked){
				tmp = curGroups[i];
				curGroups[i] = curGroups[i-1];
				curGroups[i-1] = tmp;
			}
		}
	}
	neverSave(); DisplayGroups();
}

function generateResults(){
	var results = '';
    
	xajax_clean_temp(st_id);
    notSave = false;
    
	for(var i=1; i<curGroups.length; i++){
		if (typeof(curGroups[i]) == 'undefined') continue;
		for(var j=0; j<4; j++){
			if (j == 3)
				results += curGroups[i][3].join('\\b');
			else
				results += curGroups[i][j] + '&loz;'; // '\\r';
		}
		results += '\\n';
	}
	var als = new Array();
	for(var i=0;i<acl_lists.length;i++)
		als[i] = acl_lists[i].join('\\n');
	var obj = document.getElementById('saveForm');
	obj.results.value = results.replace(/\\n$/, '');
	obj.acl_lists.value = als.join('\\f');
	obj.submit();

}

// 分組次上下移 (move)
function teamOrder(m){
	var obj = document.getElementById('sortTeam');
	var tmp, newNode;
	if (m){ // down
		for(var i=obj.rows.length-1; i>=0; i--){
			tmp = obj.rows[i].cells[0].getElementsByTagName('input');
			if (tmp[0].checked){
				/*
				 * 判斷是否已經為最後一筆資料
				 */
				if (typeof(obj.rows[i+1]) == 'undefined'){

					alert("{$MSG['move_down_error'][$sysSession->lang]}");

					return false;
				}else{
					newNode = obj.rows[i+1].cloneNode(true);
					obj.rows[i].parentNode.insertBefore(newNode, obj.rows[i]);
					tmp = obj.rows[i+1].className;
					obj.rows[i+1].className = obj.rows[i].className;
					obj.rows[i].className = tmp;
					obj.deleteRow(i+2);
				}
			}
		}
	}
	else{	// up
		for(var i=0; i<obj.rows.length; i++){

			tmp = obj.rows[i].cells[0].getElementsByTagName('input');

			if (tmp[0].checked) {
				tmp1 = obj.rows[i-1].cells[0].getElementsByTagName('input');
				if ((typeof(obj.rows[i-1]) == 'undefined') || (tmp1[0].id=="ck")) {
					alert("{$MSG['move_up_error'][$sysSession->lang]}");
					return false;
				}else{
					newNode = obj.rows[i].cloneNode(true);
					newNode = obj.rows[i].parentNode.insertBefore(newNode, obj.rows[i-1]);
					newNode.getElementsByTagName('input')[0].checked = true;
					tmp = obj.rows[i-1].className;
					obj.rows[i-1].className = obj.rows[i].className;
					obj.rows[i].className = tmp;
					obj.deleteRow(i+1);
				}
			}
		}
	}
}

// 更改單一分組次的名稱 (rename)
function team_rename(k,bg,gb,en,jp,ud){
	var obj1 = document.getElementById('setTeamForm');
	obj1.team_id.value = k;
	if (obj1.team_name_big5)	obj1.team_name_big5.value = bg;
	if (obj1.team_name_gb)		obj1.team_name_gb.value   = gb;
	if (obj1.team_name_en)		obj1.team_name_en.value   = en;
	if (obj1.team_name_jp)		obj1.team_name_jp.value   = jp;
	if (obj1.team_name_ud)		obj1.team_name_ud.value   = ud;

	var obj = document.getElementById('setTeamPanel');
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel (pixel)
	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 460;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel   (pixel)
	obj.style.top   = document.body.scrollTop  + 30;
	obj.style.display = '';
}


// 新增分組次 (add group time)
function team_add(){
	var obj = document.getElementById('addTeamPanel');
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 460;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
	obj.style.top   = document.body.scrollTop  + 30;
	obj.style.display = '';
}

// 刪除分組次 (del)
function team_del(){
	var obj = document.getElementById('orders');
	if (!confirm("{$MSG['chk_del'][$sysSession->lang]}")) return false;
	obj.action = 'stud_groups_deltimes.php';
	obj.submit();
	// window.location.replace("stud_groups_deltimes.php");
}


function setTeamComplete(){
	var obj = document.getElementById('setTeamPanel');
}

// ACL functions BEGIN

function is_object(o) {
	return ((typeof(o)!='undefined') && (typeof(o)=='object'));
}

/* 產生一列 ACL table row ( 隱藏的 table )
 * @param: id = 指定的 <TR id=""> ( 此為 group id )
 */
function new_acl_row(acl_id) {
	var acl_tab = document.getElementById('tab_acl_hidden');
	if(!is_object(acl_tab)) return false;

	try {
		var row  = acl_tab.insertRow( 0 );
		row.id   = 'acl_row_' + acl_id;
	} catch(e) {
		alert('new acl row failed:(' + acl_id +')'+e);
		return false; }
	if(!is_object(row)) return false;
	try {
		var cell = row.insertCell(0);
		cell.id  = 'aclPanel_' + acl_id;
	} catch(e) {
		alert('new acl cell failed:'+e);
		return false; }
	return is_object(cell);
}

/* 移除一列 ACL table row ( 隱藏的 table )
 * @param: id = 指定的 <TR id="">
 */
function rm_acl_row(acl_id) {
	var acl_tab = document.getElementById('tab_acl_hidden');
	if(!is_object(acl_tab)) return false;

	for(var i=0;i<acl_tab.rows.length;i++)
	{
		try {
			var row  = acl_tab.rows[i];
			if(row.id  == 'acl_row_' + acl_id) {
				acl_tab.deleteRow(i);
				return true;
			}
		} catch(e) {
			// alert('del acl row failed:(' + acl_id +')'+e);
			return false;
		}
	}
	return true;
}
function show_acl_table_html() {
	var tb = document.getElementById('tab_acl_hidden');
	alert(tb.innerHTML);
}
function show_acl_data_html(i, action) {
	var a_id= (action=='save'?'aclDisplayPanel':('aclDisplayPanel_'+i));
	var a_d = document.getElementById(a_id);
	var a_h = document.getElementById('aclPanel_'+i);
	alert(a_id + '=\\n' + a_d.innerHTML+'\\n\\naclPanel_'+i + '=\\n' + a_h.innerHTML);
}

function generate_acl_rows() {
	var acl_tab = document.getElementById('tab_acl_hidden');
	if(!is_object(acl_tab)) return false;
	var i;
	for(i=acl_tab.rows.length-1;i>=0;i--)
		acl_tab.deleteRow(i);

	for(i=0;i<acl_lists.length;i++) {
		new_acl_row(i);
		if(typeof(acl_lists[i]) != 'undefined' && acl_lists[i].length) {
			copyACLTable(i,'read');
			// show_acl_data_html(i,'read');
			// init_add_list(i);
			acl_index = i;
			generate_list(i);
			copyACLTable(i,'save');
			// show_acl_data_html(i,'save');
		}
	}
	// show_acl_table_html();
}

// 複製目前顯示 ACL 清單到 (copy)
// action : 'save' 儲存到隱藏 table , 'read':自隱藏 table 讀到顯示 table
function copyACLTable(acl_id,action) {
	var acl_dis = document.getElementById('aclDisplayPanel'+(action=='save'?'_'+acl_id:''));
	var acl_hid = document.getElementById('aclPanel_'+acl_id);
	var acl_tab = document.getElementById('tab_acl_hidden');

	if(acl_dis!=null && acl_hid!=null) {
		if(action=='save')
			acl_hid.innerHTML = acl_dis.innerHTML;
		else
			acl_dis.innerHTML = acl_hid.innerHTML;
	} else
		alert('acl_display or acl_hidden undefined');

	acl_dis.id = 'aclDisplayPanel'+(action=='save'?'':('_'+acl_id));
	// alert('action='+action+'\\nacl_id=' + acl_id + '\\nacl_dis.id='+acl_dis.id + '\\ninnerHTML=\\n' + acl_dis.innerHTML);
}

// ACL functions END

/**
 * 同步全選或全消的按鈕與 checkbox
 * @version 1.0
 **/
var nowSel = false;
var MSG_SELECT_CANCEL = "{$MSG['select_cancel'][$sysSession->lang]}";
var MSG_SELECT_ALL = "{$MSG['select_all'][$sysSession->lang]}";

function selected_box() {
	var j=0; count =0;
	var box = new Array(); box1= new Array();
	var btn1 = document.getElementById("btnSel1");
	if (btn1 == null) return false;
	nowSel = !nowSel;
    xchg_words(nowSel);

	var obj = document.getElementById("sortTeam");
	var nodes = obj.getElementsByTagName('input');
	for(var i=0; i<nodes.length; i++){
		if (nodes.item(i).getAttribute("type")=="checkbox"){
			nodes.item(i).checked = nowSel ?1:0;
		}
	}
}

/**
 * 切換全選或全消的 checkbox
 **/
function chgCheckbox() {
	var bol = true;
	var obj1 = document.getElementById('sortTeam');
	var nodes = obj1.getElementsByTagName("input");
	var obj  = document.getElementById("ck");
	var btn1 = document.getElementById("btnSel1");
	if ((nodes == null) || (nodes.length <= 0)) return false;
	for (var i = 0; i < nodes.length; i++) {
		if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
		if (nodes[i].checked == false) bol = false;
	}
	if (obj  != null) {
        obj.checked = bol;
        nowSel = bol;
    }
	if (btn1 != null) {
        xchg_words(bol);
    }
}

/**
 * 變更全選按鈕的文字
 **/
function xchg_words(mode)
{
	if (mode)
	{
		document.getElementById('toolbar1').getElementsByTagName('input')[0].value =
		document.getElementById('toolbar2').getElementsByTagName('input')[0].value = MSG_SELECT_CANCEL;
	}
	else
	{
		document.getElementById('toolbar1').getElementsByTagName('input')[0].value =
		document.getElementById('toolbar2').getElementsByTagName('input')[0].value = MSG_SELECT_ALL;
	}
}

// 切換tabs
function chgTabs() {
	document.getElementById('setPropertyPanel').style.display = 'none';
	if (notSave) {
		if(!confirm(MSG_EXIT)) {
			notSave = false;
			tabsSelect(1);
			neverSave();
		}
		else
			notSave = false;
	}
}

// 切換分組次
function chgTid(tid) {
	if (notSave) {
		if (!confirm(MSG_EXIT)) {
			var o_tid = "{$minTid}";
			if (o_tid > 0)
				document.getElementById('tidSel').value = o_tid;
		}
		else {
			notSave = false;
			location.replace('stud_groups.php?tid='+tid);
		}
	}
	else
		location.replace('stud_groups.php?tid='+tid);
}

var php, editor = new Object();
var st_id = '{$sysSession->cur_func}{$sysSession->course_id}{$minTid}';

window.onload = function () {
	DisplayGroups();
	generate_acl_rows();
	var t1 = document.getElementById('toolbar1');
	document.getElementById('toolbar2').innerHTML = t1.innerHTML;

	/*Modify By Edi:隱藏其它成員的設定 Start*/
	document.getElementById("setPropertyTB").rows[2].style.display="none";
	document.getElementById("setPropertyTB").rows[3].className = "cssTrEvn";
	/*Modify By Edi:隱藏其它成員的設定 End*/

	php = new PHP_Serializer();
	editor.setHTML = function(x)
	{
	    curGroups = php.unserialize(unescape(x));
	    DisplayGroups();
	};

    xajax_check_temp(st_id, 'FCK.editor');
	window.setInterval(function(){if (notSave) xajax_save_temp(st_id, escape(php.serialize(curGroups)));}, 100000);

};


window.onbeforeunload = function () {
	if (notSave)
		return "{$MSG['alert_msg'][$sysSession->lang]}";
};

EOB;
		  showXHTML_script('include', '/lib/dragLayer.js');
		  showXHTML_script('include', '/lib/common.js');
		  showXHTML_script('inline', $scr);
		  $xajax_save_temp->printJavascript('/lib/xajax/');
		  showXHTML_script('include', '/lib/PHP_Serializer.js');
		showXHTML_head_E();
		showXHTML_body_B('');
		  showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
		    showXHTML_tr_B();
		      showXHTML_td_B();

		      	$style_tabsSet1 = '';
		      	$style_tabsSet3 = '';

		      	if (strlen($_GET['order']) > 0){
		      		$order = trim($_GET['order']);

					switch ($order){
						case 1:
							$style_tabsSet1 = '';
							$style_tabsSet3 = 'display:none;';
							break;
						case 3:
							$style_tabsSet1 = 'display:none;';
							$style_tabsSet3 = '';

							$order = $order -1;
							break;
					}
		      	}else{
					$order = 1;

					$style_tabsSet1 = '';
					$style_tabsSet3 = 'display:none;';
		    	}

		        $ary = array( array($MSG['student_grouping'][$sysSession->lang],   'tabsSet1',  'chgTabs();'),
		                      array($MSG['separating_sort'][$sysSession->lang],    'tabsSet3',  'chgTabs();')
		                    );
		        showXHTML_tabs($ary, $order);
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B();
		      showXHTML_td_B('valign="top" class="bg01"');

			showXHTML_table_B('id="tabsSet1" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse;' . $style_tabsSet1 . '" class="box01" ');
			  showXHTML_tr_B('class="bg03 font01"');
			    showXHTML_td_B();
			      showXHTML_form_B('style="display: inline"');
			        echo $MSG['grouping_times'][$sysSession->lang];
			        $RS = dbGetStMr('WM_student_separate', 'team_id,team_name', "course_id={$sysSession->course_id} order by permute,team_id", ADODB_FETCH_ASSOC);
			        if ($sysConn->ErrorNo() > 0) die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
					$team_name = array();
					$team_names = array();
			        while(!$RS->EOF){
						$tn = getCaption($RS->fields['team_name']);
						$tn['Big5']   	   = htmlspecialchars($tn['Big5']);
						$tn['GB2312'] 	   = htmlspecialchars($tn['GB2312']);
						$tn['en']     	   = htmlspecialchars($tn['en']);
						$tn['EUC-JP'] 	   = htmlspecialchars($tn['EUC-JP']);
						$tn['user_define'] = htmlspecialchars($tn['user_define']);
			        	$team_name[$RS->fields['team_id']] = $tn[$sysSession->lang];
			        	$team_names[$RS->fields['team_id']] = array('Big5'        => addslashes($tn['Big5']),
			        											    'GB2312'      => addslashes($tn['GB2312']),
			        											    'en'          => addslashes($tn['en']),
			        											    'EUC-JP'      => addslashes($tn['EUC-JP']),
			        											    'user_define' => addslashes($tn['user_define']));
			        	// if ($minTid == $RS->fields['team_id']) $cur_team = $tn;
			        	$RS->MoveNext();
			        }
			        showXHTML_input('select', 'tidSel', $team_name, $minTid, 'size="1" onchange="chgTid(this.value);"');
			      showXHTML_form_E();
			    showXHTML_td_E();
			  showXHTML_tr_E();

			  showXHTML_tr_B('class="bg02 font01"');
			    showXHTML_td_B();
			      showXHTML_form_B('style="display: inline"');
			        echo $MSG['grouping_tip'][$sysSession->lang];
			        showXHTML_input('button', '', $MSG['add_group'][$sysSession->lang],         '', 'class="cssBtn" onclick="addGroup();"');
			        showXHTML_input('button', '', $MSG['rm_group'][$sysSession->lang],          '', 'class="cssBtn" onclick="rmGroup();"');
			        showXHTML_input('button', '', $MSG['clean_grouping'][$sysSession->lang],    '', 'class="cssBtn" onclick="cleanGroup();"');
			        showXHTML_input('button', '', $MSG['grouping_complete'][$sysSession->lang], '', 'class="cssBtn" onclick="generateResults();" id="saveButton"');
			        echo str_repeat(chr(9), $sysIndent), '<button type="button" class="cssBtn" style="font-size: 16px; width: 40" onclick="movePlace(0);">&larr;</button>', "\n";
			        echo str_repeat(chr(9), $sysIndent), '<button type="button" class="cssBtn" style="font-size: 16px; width: 40" onclick="movePlace(1);">&rarr;</button>', "\n";
			      showXHTML_form_E();
			    showXHTML_td_E();
			  showXHTML_tr_E();

			  showXHTML_tr_B('class="cssTrEvn"');
			    showXHTML_td_B();
			      showXHTML_form_B('style="display: inline"', 'procForm');

			      showXHTML_form_E();
			    showXHTML_td_E();
			  showXHTML_tr_E();
		        showXHTML_table_E();

		  // 新增分組次名稱
		  $ary = array(array($MSG['new_grouping_times'][$sysSession->lang], 'tabsSet3',  ''));
		  showXHTML_tabFrame_B($ary, 1, 'addTeamForm', 'addTeamPanel', 'method="POST" action="stud_groups_addtimes.php" style="display:inline" onsubmit="return chk_multi_lang_input(1, true)"', true);
		  showXHTML_table_B('border="0" width="440" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
		    $arr_names = array('Big5'		=>	'team_name_big5',
							   'GB2312'		=>	'team_name_gb',
							   'en'			=>	'team_name_en',
							   'EUC-JP'		=>	'team_name_jp',
							   'user_define'=>	'team_name_ud'
							   );
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('', $MSG['team_name'][$sysSession->lang]);
				showXHTML_td_B('');
					$multi_lang3 = new Multi_lang(false, '', 'class="cssTrEvn"'); // 多語系輸入框
					$multi_lang3->show(true, $arr_names);
				showXHTML_td_E();
			showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrOdd"');
            //#47393 Chrome [教師/人員管理/學員分組/分組次管理/新增] 新增分組次的分組次名稱，右邊框線不見。colspan:3->2
		      showXHTML_td_B('colspan="2"');
		        showXHTML_input('submit', '', $MSG[complete][$sysSession->lang], '', 'class="cssBtn"');
		        showXHTML_input('button', '', $MSG[cancel][$sysSession->lang], '',   'class="cssBtn" onclick="document.getElementById(\'addTeamPanel\').style.display=\'none\';"');
		      showXHTML_td_E();
		    showXHTML_tr_E();

		  showXHTML_table_E();
		  showXHTML_tabFrame_E();

			// 分組次排序
		      showXHTML_form_B('action="stud_groups_order.php" method="POST" style="display:inline" id="orders"');
			showXHTML_table_B('id="tabsSet3" border="0" cellpadding="3" cellspacing="1" style="' . $style_tabsSet3. 'border-collapse: collapse;" class="cssTable" width="760" ');
			  showXHTML_tr_B('class="cssTrEvn"');
			    // 47170 [Chrome][教師/人員管理/學員分組/分組次管理] 右邊框線變淡
                showXHTML_td_B('width="760" id="toolbar1"');
				  showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang],    '', 'id="btnSel1" class="cssBtn" onclick="selected_box();"');
			      echo '&nbsp;';
			      showXHTML_input('button', '', $MSG['add_groupingtimes'][$sysSession->lang],     '', "class=\"button01\" onclick=\"team_add();\"");
			      showXHTML_input('button', '', $MSG['rm_grouping_times'][$sysSession->lang],     '', "class=\"button01\" onclick=\"team_del();\"");
			      echo '&nbsp;'.str_repeat(chr(9), $sysIndent);
			      showXHTML_input('button', '', '&uarr;',     '', "class=\"button01\" onclick=\"teamOrder(0);\"");
			      echo str_repeat(chr(9), $sysIndent);
			      showXHTML_input('button', '', '&darr;',     '', "class=\"button01\" onclick=\"teamOrder(1);\"");
			      // echo str_repeat(chr(9), $sysIndent), '<button type="button" class="cssBtn" style="font-size: 16px; width: 40" onclick="teamOrder(0);">&uarr;</button>', "\n";
			      // echo str_repeat(chr(9), $sysIndent), '<button type="button" class="cssBtn" style="font-size: 16px; width: 40" onclick="teamOrder(1);">&darr;</button>', "\n";
			      showXHTML_input('submit', '', $MSG['save_permuting'][$sysSession->lang], '', 'class="cssBtn"');
			    showXHTML_td_E();
			  showXHTML_tr_E();
			  showXHTML_tr_B('class="cssTrEvn"');
			    showXHTML_td_B();
			        showXHTML_table_B('id="sortTeam" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable" width="100%"');
			          	showXHTML_tr_B('class="cssTrHead"');
			          	 showXHTML_td_B('width="25" align="left" title="' . $MSG['td_alt_sel'][$sysSession->lang] . '"');
							showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selected_box();"');
						 showXHTML_td_E();
						 showXHTML_td('align="left" nowrap="NoWrap"', $MSG['team_name'][$sysSession->lang]);
			          	 showXHTML_td('align="left" nowrap="NoWrap"', $MSG['login_action'][$sysSession->lang]);
			          	showXHTML_tr_E();
			          foreach($team_name as $k => $v){
			          	$cla = $cla == 'class="cssTrOdd"' ? 'class="cssTrEvn"' : 'class="cssTrOdd"' ;
			          	showXHTML_tr_B($cla);
			          	  showXHTML_td_B();
			          	    showXHTML_input('checkbox','tlist[]', $k,'','onclick="chgCheckbox();"');
			          	    showXHTML_input('hidden', 'teams[]', $k);
			          	  showXHTML_td_E();
			          	  // showXHTML_td('', $v . '(' . $k .')');
			          	  	showXHTML_td('', $v);
			          	  showXHTML_td_B();
			          	    showXHTML_input('button', '', $MSG['btm_modify'][$sysSession->lang], '', 'style="24" class="cssBtn" onclick="team_rename('.$k.',\''.$team_names[$k]['Big5'].'\',\''.$team_names[$k]['GB2312'].'\',\''.$team_names[$k]['en'].'\',\''.$team_names[$k]['EUC-JP'].'\',\''.$team_names[$k]['user_define'].'\');"');
			          	  showXHTML_td_E();
			          	showXHTML_tr_E();
			          }
			        showXHTML_table_E();
			    showXHTML_td_E();
			  showXHTML_tr_E();
			  showXHTML_tr_B('class="cssTrEvn"');
			    // #47170 [Chrome][教師/人員管理/學員分組/分組次管理] 右邊框線變淡
                showXHTML_td_B('width="760" id="toolbar2"');
			    showXHTML_td_E();
			  showXHTML_tr_E();
		        showXHTML_table_E();
		      showXHTML_form_E();

		      showXHTML_td_E();
		    showXHTML_tr_E();
		  showXHTML_table_E();

		  // 儲存分組結果
		  showXHTML_form_B('action="stud_groups1.php" method="POST"', 'saveForm');
		    showXHTML_input('hidden', 'results');
		    showXHTML_input('hidden', 'team_id', $minTid);
		    showXHTML_input('hidden', 'acl_lists', '');		// ACL 清單
		  showXHTML_form_E();

		  // 組設定
		  $ary = array(array($MSG['group_property'][$sysSession->lang], 'tabsSet',  ''));
		  showXHTML_tabFrame_B($ary, 1, 'setPropertyForm', 'setPropertyPanel', 'style="display:inline"', true);
		  showXHTML_table_B('border="0" width="440" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable" id="setPropertyTB"');

			$arr_names = array('Big5'		=>	'group_name_big5',
							   'GB2312'		=>	'group_name_gb',
							   'en'			=>	'group_name_en',
							   'EUC-JP'		=>	'group_name_jp',
							   'user_define'=>	'group_name_ud'
							   );
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('', $MSG['team_name'][$sysSession->lang]);
				showXHTML_td_B('');
					$multi_lang1 = new Multi_lang(false, '', 'class="cssTrEvn"'); // 多語系輸入框
					$multi_lang1->show(true, $arr_names);
				showXHTML_td_E();
			showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('', $MSG['captain'][$sysSession->lang]);
		      showXHTML_td_B('nowrap="nowrap" id="lstCaptain"');
		        showXHTML_input('select', 'captain', array(), '', 'class="cssInput"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td('', $MSG['other_member'][$sysSession->lang]);
		      showXHTML_td_B('');
		        showXHTML_input('button', '', 'add', '', 'class="cssBtn" onClick="add_acl(this.form);"');
		      showXHTML_td_E();
		      showXHTML_td('id="aclDisplayPanel"', '');
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td_B('colspan="2"');
		        showXHTML_input('hidden', 'idx');
		        showXHTML_input('button', '', $MSG[complete][$sysSession->lang], '', 'class="cssBtn" onclick="setPropertyComplete();"');
		        showXHTML_input('button', '', $MSG[cancel][$sysSession->lang],   '',   'class="cssBtn" onclick="setPropertyCancel()"');
		      	// showXHTML_input('button', '','dump acl_lists',   '',   'class="cssBtn" onclick="alert(\'acl_lists:\\n\'+dump(acl_lists,\'acl_lists\'));"');
		      showXHTML_td_E();
		    showXHTML_tr_E();

		  showXHTML_table_E();
		  showXHTML_tabFrame_E();

		// 修改分組次的名稱
		  $ary = array(array($MSG['team_rename'][$sysSession->lang], 'tabsSet',  ''));
		  showXHTML_tabFrame_B($ary, 1, 'setTeamForm', 'setTeamPanel', 'method="POST" action="stud_groups_rentimes.php" style="display:inline" onsubmit="return chk_multi_lang_input(3, true)"', true);
		  showXHTML_table_B('border="0" width="440" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');

			$arr_names = array('Big5'		=>	'team_name_big5',
							   'GB2312'		=>	'team_name_gb',
							   'en'			=>	'team_name_en',
							   'EUC-JP'		=>	'team_name_jp',
							   'user_define'=>	'team_name_ud'
							   );
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('', $MSG['team_name'][$sysSession->lang]);
				showXHTML_td_B('');
					$multi_lang2 = new Multi_lang(false, '', 'class="cssTrEvn"'); // 多語系輸入框
					$multi_lang2->show(true, $arr_names);
				showXHTML_td_E();
			showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td_B('colspan="2"');
		        showXHTML_input('hidden', 'team_id', '');
		        showXHTML_input('submit', '', $MSG[complete][$sysSession->lang], '', 'class="cssBtn"');
		        showXHTML_input('button', '', $MSG[cancel][$sysSession->lang], '',   'class="cssBtn" onclick="document.getElementById(\'setTeamPanel\').style.display=\'none\';"');
		      showXHTML_td_E();
		    showXHTML_tr_E();

		  showXHTML_table_E();
		  showXHTML_tabFrame_E();

		// 供 ACL Control Panel 放置各小組顯示列表暫存 table
		  showXHTML_table_B('id="tab_acl_hidden" style="position:absolute;display:none"');
		  showXHTML_table_E('');

	  	  aclGenerateAclControlPanel2();

		echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
		showXHTML_body_E();
?>

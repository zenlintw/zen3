<?php
	/**
	 * 新增人員資料
	 *
	 * 修改自 /academic/stud/stud_chk_add.php
	 *
	 * @since   2005/11/30
	 * @author  Hubert
	 * @version $Id: stud_chk_add.php,v 1.1 2010/02/24 02:40:30 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	// 同步 checkbox (Begin)
	$chkAry   = explode(',', trim($_POST['chkbox']));
	$tmp     = array();
	foreach ($chkAry as $val) {
		$val = trim($val);
		if (empty($val)) continue;
		$tmp[] = '"' . $val . '" : true';
	}
	$lsStr = (count($tmp) > 0) ? implode(',', $tmp) : '';
	$lsStr = 'var lsObj = {' . $lsStr . '};';
	// 同步 checkbox (End)

	$S_TG = ($_POST['sTarget']!=''?$_POST['sTarget']:$_GET['sTarget']);
	$S_RL = ($_POST['sRole']!=''?$_POST['sRole']:$_GET['sRole']);
	$S_TP = ($_POST['sType']!=''?$_POST['sType']:$_GET['sType']);
	//echo '1kw=>',$_GET['keyword'],' / decode=>',sysDecode($_GET['keyword']),'<br>';
	if (trim($_POST['keyword'])!='')
	{
		$S_KW = trim($_POST['keyword']);
	} else {
		$S_KW = ($_GET['keyword']!=''?base64_decode($_GET['keyword']):'');
		$S_KW = (($S_KW!='' && $S_KW != $MSG['title80'][$sysSession->lang])?$S_KW:'');
	}

	$where = " B.`username` != '" . sysRootAccount . "'";
	if ($S_TG == 'cou')
	{
		$where.= " and A.`course_id`={$sysSession->course_id}";
		switch ($S_RL)
		{
			case 'stu':
				$where.= ' and (A.`role` & ' . $sysRoles['student'] . ')';
				break;
			case 'aud':
				$where.= ' and (A.`role` & ' . $sysRoles['auditor'] . ')';
				break;
			default:
				$where.= ' and (A.`role` & ' . ($sysRoles['student'] | $sysRoles['auditor']) . ')';
				break;
		}
	}

	if ($S_KW != '' && $S_KW != $MSG['title80'][$sysSession->lang]){
		switch ($S_TP) {
			case 'nam'    :
				if ($where != '') $where.= ' and';
                $where.= ' if(B.first_name REGEXP "^[0-9A-Za-z _-]$" && B.last_name REGEXP "^[0-9A-Za-z _-]$", concat(B.first_name, " ", B.last_name), concat(B.last_name, B.first_name)) LIKE "%' . escape_LIKE_query_str(addslashes($S_KW)) . '%" ';
				break;
			case 'usr' :
				if ($where != '') $where.= ' and';
				$where.= ' B.`username` like "%' . escape_LIKE_query_str(addslashes($S_KW)) . '%" ';
				break;
		}
	}

	if ($_POST['page_num'] != ''){
		$page_num = intval($_POST['page_num']);
	}else if ($_GET['page_num'] != ''){
		$page_num = intval($_GET['page_num']);
	}

	if (empty($page_num)) $page_num = sysPostPerPage;

	// 處理接收的資料 (End)

	// 處理顯示的資料 (Begin)
	/**
	 * 處理資料，過長的部份隱藏
	 * @param integer $width   : 要顯示的寬度
	 * @param string  $caption : 顯示的文字
	 * @param string  $title   : 浮動的提示文字，若沒有設定，則跟 $caption 依樣
	 * @return string : 處理後的文字
	 **/
	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = $caption;
		return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
	}

	/**
	 * 顯示身分
	 * @param string $val : 身分別
	 * @return string : 要顯示的文字
	 **/
	function showRole($val) {
		global $S_TG, $MSG, $sysSession, $sysRoles, $sysConn;
		static $course_roles, $course_students;
		
		if (!isset($course_roles)) $course_roles = array_reverse(array_slice($sysRoles, 1, 9));
		
		if ($S_TG == 'all'){
			if (!isset($course_students)) {
				$course_students = $sysConn->GetAssoc('select username, role from WM_term_major where course_id = "'.$sysSession->course_id.'"');
			}
			$val = $course_students[$val];
		}
		
		$user = '';
		foreach ($course_roles as $role => $role_value)
			if ($val & $role_value)
				$user .= $MSG[$role][$sysSession->lang] . ' & ';    
		
		return $user ? substr($user, 0, -3) : '&nbsp;';
	}

	/**
	 * 顯示帳號
	 * @param string $val : 帳號
	 * @return string : 要顯示的文字
	 **/
	function showUser($val) {
		return divMsg(200, $val);
	}

	/**
	 * 顯示姓名
	 * @param string $f : 名字
	 * @param string $l : 姓名
	 * @return string : 要顯示的文字
	 **/
	function showName($f, $l) {
		return divMsg(200, checkRealname($f, $l));
	}

	/**
	 * 顯示性別的圖示
	 * @param string $val : 性別
	 * @return string : 要顯示的圖示
	 **/
	function showGender($val) {
		global $sysSession;

		$gender  = "/theme/default/{$sysSession->env}/";
		$gender .= ($val == 'M') ? 'male.gif' : 'female.gif';
		return '<img src="' . $gender . '" type="image/jpeg" border="0" align="absmiddle">';
	}

	function showJobs($val) {
		return divMsg(100, $val);
	}

	function showUnit($val) {
		return divMsg(100, getCaption($val));
	}

	function showDetail($val) {
		global $sysSession, $MSG;
		$icon = '<img src="/theme/' . $sysSession->theme . '/academic/icon_folder.gif" width="16" height="16" border="0" alt="' . $MSG['btn_alt_detail'][$sysSession->lang] . '">';
		$detail = '<a href="javascript:;" onclick="return false;">' . $icon . '</a>';
		return $detail;
	}

	function showCheckBox($val) {
		global $chkAry;
		$ck = in_array($val, $chkAry) ? ' checked="checked"' : '';
		showXHTML_input('checkbox', '', $val, '', 'id="c_'.$val.'" onclick="chgCheckbox(); event.cancelBubble=true;"' . $ck);
	}
	// 處理顯示的資料 (End)

	$js = <<< BOF
	var page_num = {$page_num};
	var MSG_SELECT_ALL     = "{$MSG['title77'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL  = "{$MSG['title78'][$sysSession->lang]}";
	{$lsStr}

	function Page_Row(row){
		queryForm('dataTabs', 'queryStud', row);
  }

	function queryForm(fId, tId, rows) {
		var obj = null, fObj = null, tObj = null, nodes = null, idx = '';
		var val = new Array();

		fObj = document.getElementById(fId);
		if (typeof(fObj) == 'object')
		{
			nodes = fObj.getElementsByTagName('select');
    	for(var i=0; i<nodes.length; i++){
    		idx = nodes[i].name;
    	  switch (idx){
    	  	case 'page_num':
    	  	case 'sTarget':
    	  	case 'sRole':
    	  	case 'sType':
    	  		val[idx] = nodes[i].options[nodes[i].selectedIndex].value;
    	  		break;
    	  }
    	}
    	obj = document.getElementById('keyword');
			if (typeof(obj) == 'object')
			{
				val[keyword] = obj.value;
			}

		}

		tObj = document.getElementById(tId);
		if (typeof(tObj) == 'object')
		{
			tObj.page_num.value = ((rows == null || rows == '')?val['page_num']:rows);
			tObj.sTarget.value = val['sTarget'];
			tObj.sRole.value = val['sRole'];
			tObj.sType.value = val['sType'];
			tObj.keyword.value = val[keyword];
			tObj.submit();
		}

	}

	function chgPageSort() {
		var obj = null, fObj = null, nodes = null, idx = '', lnk = '';

		fObj = document.getElementById('dataTabs');
		if (typeof(fObj) == 'object')
		{
			nodes = fObj.getElementsByTagName('select');
    	for(var i=0; i<nodes.length; i++){
    		idx = nodes[i].name;
    	  switch (idx){
    	  	case 'page_num':
    	  	case 'sTarget':
    	  	case 'sRole':
    	  	case 'sType':
    	  		lnk += "&"+idx+"="+nodes[i].options[nodes[i].selectedIndex].value;
    	  		break;
    	  }
    	}
    	obj = document.getElementById('queryStud');
			if (typeof(obj) == 'object')
			{
				lnk += "&keyword="+obj.keyword.value;
			}

		}
		return "&cIdx=4"+lnk;
	}

	function chOpStu(n){
		var val = '', attr = null, ml = '', ss = /,$/;
		var msg = "{$MSG['msg_title05'][$sysSession->lang]}";
		var obj = document.getElementById("addStud");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.op.value = n;
		var tObj = document.getElementById('dataTabs');
    	var nodes = tObj.getElementsByTagName('input');
    	for(var i=0; i<nodes.length; i++){
			attr = nodes[i].getAttribute("exclude");
			if ((nodes[i].type == 'checkbox') && (nodes[i].checked == true) && (attr == null)){
				ml += nodes[i].value + ',';
        	}
    	}
    	ml = ml.replace(ss, '');
    	if (ml.length == 0){
			alert(msg);
			return false;
    	}
    	obj.userlist.value = ml;
    	obj.cIdx.value = chgPageSort();
    	obj.submit();
	}

	function chgTarget(){
		var obj = null, val = '';
		obj = document.getElementById('sTarget');
		val = obj.options[obj.selectedIndex].value;
		switch (val){
			case 'cou':
				objSwt('sRole'  , false);
				objSwt('sType'  , false);
				objSwt('keyword', false);
				objSwt('sBtn'   , false);
				break;
			case 'all':
				objSwt('sRole'  , true);
				objSwt('sType'  , false);
				objSwt('keyword', false);
				objSwt('sBtn'   , false);
				break;
			default:
				objSwt('sRole'  , true);
				objSwt('sType'  , true);
				objSwt('keyword', true);
				objSwt('sBtn'   , true);
				break;
		}
	}

	function objSwt(oId ,arg){
		var obj = null;
		obj = document.getElementById(oId);
		if (typeof(obj) == 'object'){
			if (arg == null || arg == '') arg = false;
			obj.disabled = arg;
		}
	}

BOF;

	showXHTML_head_B($MSG['msg_title04'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/academic/course/course_list.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();

			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';

			$sTarAry = Array(
				'0'    => $MSG['msg_title03'][$sysSession->lang],
				'cou'  => $MSG['title84'][$sysSession->lang],
				'all'  => $MSG['title85'][$sysSession->lang]
			);

			$sRolAry = Array(
				'0'    => $MSG['all'][$sysSession->lang],
				'stu'  => $MSG['student'][$sysSession->lang],
				'aud'  => $MSG['auditor'][$sysSession->lang]
			);

			$sTypAry = Array(
				'0'    => $MSG['msg_title03'][$sysSession->lang],
				'nam'  => $MSG['realname'][$sysSession->lang],
				'usr'  => $MSG['username'][$sysSession->lang]
			);

			// 工具列
			$toolbar = new toolbar();
			$toolbar->add_caption($MSG['title83'][$sysSession->lang] . $MSG['msg_title02'][$sysSession->lang]);
			$toolbar->add_input('select', 'sTarget', $sTarAry  , $S_TG, 'id="sTarget" class="cssInput" onChange="chgTarget();"');
			$toolbar->add_caption($MSG['title79'][$sysSession->lang] . $MSG['msg_title02'][$sysSession->lang]);
			$toolbar->add_input('select', 'sRole'  , $sRolAry  , $S_RL, 'id="sRole" class="cssInput"');
			$toolbar->add_input('select', 'sType'  , $sTypAry  , $S_TP, 'id="sType" class="cssInput"');
			$toolbar->add_input('text'  , 'keyword', ($S_KW!=''?$S_KW:$MSG['title80'][$sysSession->lang]), '', 'id="keyword" size="30" maxlength="60" class="cssInput" onclick="this.value=\'\'" onKeyPress="if (event.keyCode==13) queryForm(\'dataTabs\', \'queryStud\', \'\');"');
			$toolbar->add_input('button', '', $MSG['title82'][$sysSession->lang], '', 'id="sBtn" class="button01" onclick="queryForm(\'dataTabs\', \'queryStud\', \'\');"');
			$myTable->add_toolbar($toolbar);

			// 全選全消的按鈕
			$myTable->set_select_btn(true, 'btnSel', $MSG['title77'][$sysSession->lang], 'onclick="selfunc()"');

			// 排序
			$myTable->add_sort('user'  , 'B.`username` ASC', 'B.`username` DESC');

			if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
				$myTable->add_sort('name'  , 'B.`last_name` ASC, B.`first_name` ASC', 'B.`last_name` DESC, B.`first_name` DESC');
			} else {
				$myTable->add_sort('name'  , 'B.`first_name` ASC, B.`last_name` ASC', 'B.`first_name` DESC, B.`last_name` DESC');
			}
			$myTable->set_sort(true, 'user', 'asc', 'chgPageSort()');

			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

			$ck2 = new toolbar();
			$ck2->add_input('checkbox', 'fid[]', '%0', '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
			$myTable->add_field($ck1, $MSG['msg_title77'][$sysSession->lang], '' , '%1'   , 'showCheckBox', 'align="center"' );
			$myTable->add_field($MSG['title79'][$sysSession->lang] , '', ''      , '%0'   , 'showRole'    , ''               );
			$myTable->add_field($MSG['username'][$sysSession->lang], '', 'user'  , '%1'   , 'showUser'    , 'nowrap="noWrap"');
			$myTable->add_field($MSG['realname'][$sysSession->lang], '', 'name'  , '%2 %3', 'showName'    , 'nowrap="noWrap"');
			$myTable->set_page(true, 1, $page_num, 'chgPageSort()');
			if (empty($S_TG))
			{
				$tab    = 'WM_user_account as B';
				$fields = 'B.`username` as val,B.`username`,B.`first_name`,B.`last_name`';
				$where = '1=2';
			}elseif ($S_TG == 'cou'){
				$tab    = 'WM_term_major as A inner join WM_user_account as B on A.username=B.username';
				$fields = 'A.`role`,B.`username`,B.`first_name`,B.`last_name`';
			}elseif ($S_TG == 'all'){
				$tab    = 'WM_user_account as B';
				$fields = 'B.`username` as val,B.`username`,B.`first_name`,B.`last_name`';
			}

			$myTable->set_sqls($tab, $fields, ($where==''?1:$where));
			$myTable->show();

		if (!empty($S_TG))
		{
				showXHTML_form_B('action="stud_addrm1.php?4" method="post" style="display:inline"', 'addStud');
					showXHTML_table_B('align="center" width="760" border="0" cellspacing="1" cellpadding="3" id="tabAddStud"');
						showXHTML_tr_B('');
							showXHTML_td_B('align="left"');
							$ticket = md5($sysSession->ticket . $sysSession->school_id . $sysSession->username . 'add');
								showXHTML_input('hidden', 'ticket', $ticket);
								showXHTML_input('hidden', 'op');
								showXHTML_input('hidden', 'cIdx');
								showXHTML_input('textarea', 'userlist', '', '', 'style="display:none"');
							if ($S_TG == 'all')
							{
								showXHTML_input('button', '', $MSG['add_student'][$sysSession->lang], '', 'class="button01" style="width: 120" onclick="chOpStu(1);"');
								showXHTML_input('button', '', $MSG['add_auditor'][$sysSession->lang], '', 'class="button01" style="width: 120" onclick="chOpStu(2);"');
							}
								showXHTML_input('button', '', $MSG['aud2stu'][$sysSession->lang],     '', 'class="button01" style="width: 278px;" onclick="chOpStu(3);"');
								showXHTML_input('button', '', $MSG['stu2aud'][$sysSession->lang],     '', 'class="button01" style="width: 278px;" onclick="chOpStu(4);"');
								showXHTML_input('button', '', $MSG['remove'][$sysSession->lang],      '', 'class="button01" style="width: 90"  onclick="chOpStu(5);"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
				showXHTML_form_E();
		}

		showXHTML_form_B('action="' . $_SERVER['PHP_SELF'] . '?4" method="post" enctype="multipart/form-data" style="display:none"', 'queryStud');
			showXHTML_input('hidden', 'chkbox'  , trim($_POST['chkbox']), '', '');
			showXHTML_input('hidden', 'sTarget' , $S_TG    );
			showXHTML_input('hidden', 'sRole'   , $S_RL    );
			showXHTML_input('hidden', 'sType'   , $S_TP    );
			showXHTML_input('hidden', 'keyword' , ($S_KW!=''?base64_encode($S_KW):''));
			showXHTML_input('hidden', 'page_num', $page_num);
		showXHTML_form_E('');

	showXHTML_body_E();
?>

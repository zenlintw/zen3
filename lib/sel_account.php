<?php
   /**
	* 檔案說明
	*	提供acl_api.php 中挑選個帳號, 目前只提供 academic/teach 環境底下使用
	* PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	*
	* LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	* 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	* 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	*
	* @package     WM3
	* @author      Edi Chen <edi@sun.net.tw>
	* @copyright   2000-2005 SunNet Tech. INC.
	* @version     CVS: $Id: sel_account.php,v 1.1 2010/02/24 02:39:34 saly Exp $
	* @link        http://demo.learn.com.tw/1000110138/index.html
	* @since       2006-03-09
	*/

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/sel_account.php');
	require_once(sysDocumentRoot . '/lib/username.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end

// {{{ 變數宣告 begin
	$env = $sysSession->env ? $sysSession->env : 'teach';

	if ($env == 'academic')
		$hidden_roles = array('guest', 'senior', 'paterfamilias', 'superintendent', 'root');
	else
		$hidden_roles = array('guest', 'senior', 'paterfamilias', 'superintendent', 'director', 'manager', 'administrator', 'root');

	$arr_roles = array();
	foreach ($sysRoles as $k => $v)
		if (!in_array($k, $hidden_roles))
			$arr_roles[$k] = $MSG[$k][$sysSession->lang];

	$arr_types = array('account' => $MSG['account'][$sysSession->lang],
					   'name'	 => $MSG['name'][$sysSession->lang] );

	$role =   $_POST['role']  && in_array($_POST['role'], array_keys($arr_roles)) 				// 預設顯示的身分
			? $_POST['role']
			: ($_GET['role']  && in_array($_GET['role'] , array_keys($arr_roles)) ? $_GET['role']  : 'all');

	$type =   $_POST['type']  && in_array($_POST['type'], array_keys($arr_types)) 				// 預設查詢帳號或者姓名
			? $_POST['type']
			: ($_GET['type']  && in_array($_GET['type'] , array_keys($arr_types)) ? $_GET['type']  : 'account');

	$page_num = $_POST['page_num'] 												// 頁數控制
			? $_POST['page_num']
			: ($_GET['page_num']  ? $_GET['page_num']  : sysPostPerPage);

	$keyword = $_POST['keyword']  												// 關鍵字
			? $_POST['keyword']
			: ($_GET['keyword'] ? base64_decode($_GET['keyword']) : '');

/*
	$members = $_POST['members'] 												// 頁數控制
			? $_POST['members']
			: ($_GET['members']  ? $_GET['members']  : '');
	$member_arr = explode(',', $members);
*/
// }}} 變數宣告 end

// {{{ 函數宣告 begin
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
	 * 顯示帳號
	 * @param string $val : 帳號
	 * @return string : 要顯示的文字
	 **/
	function showUser($val) {
		return divMsg(200, $val);
	}

	/**
	 * 顯示姓名
	 */
	function showName($fName, $lName, $lang) {
		return divMsg(200, checkRealname($fName, $lName));
	}

	function showCheckBox($uid) {
		// global $member_arr;
		echo '<input type="checkbox" name="uid[]" value="' . $uid . '" onclick="chgCheckbox(); event.cancelBubble=true;">'; // . (in_array($uid, $member_arr) ? 'checked=true' : '') .'>';
	}

	/**
	 * 處理 #1820 問題：把重複名稱的 select 的 name 去掉，並另建 hidden 的 input
	 */
	function adjectDuplicateName($data)
	{
		global $page_num, $page_no;

		return preg_replace(array('/(id="tools11" >)/',
								  '/name="(ap|page_num)" class="cssInput" onchange="/'),
							array('\1<input type="hidden" name="ap" value="' . $page_no . '"><input type="hidden" name="page_num" value="' . $page_num . '">',
								  'class="cssInput" onchange="this.form.\1.value=this.value;'),
							$data);
	}

// }}} 函數宣告 end

// {{{ 主程式 begin

	// 設定SQL Start
	$group_by = ' group by A.username';
	switch($role) {
		case 'all' 		 	:
			$table = $env == 'academic' ? sysDBname.'.WM_all_account as A' : sysDBname.'.WM_all_account as A left join '.sysDBprefix . $sysSession->school_id.'.WM_term_major as B on A.username = B.username';
			$where = $env == 'academic' ? ('A.username != "'.sysRootAccount.'"') : ('B.course_id = ' . $sysSession->course_id . ' and A.username !="'.sysRootAccount.'"');
			break;
		case 'auditor' 	 	:
		case 'student' 	 	:
		case 'assistant' 	:
		case 'instructor'	:
		case 'teacher'	 	:
			$table = sysDBname.'.WM_all_account as A left join '.sysDBprefix . $sysSession->school_id.'.WM_term_major as B on A.username = B.username';
			$where = 'B.role & ' . $sysRoles[$role] . ' and A.username != "'.sysRootAccount.'"';
			if ($env != 'academic') $where .= ' and B.course_id = ' . $sysSession->course_id;
			break;
		case 'class_instructor' :
			$table = sysDBname.'.WM_all_account as A left join '.sysDBprefix . $sysSession->school_id.'.WM_class_member as B on A.username = B.username';
			$where .= 'B.role &  64 and A.username != "'.sysRootAccount.'"';
			break;
		case 'director'	 	:
			$table = sysDBname.'.WM_all_account as A left join '.sysDBprefix . $sysSession->school_id.'.WM_class_member as B on A.username = B.username';
			$where .= 'B.role & ' . $sysRoles[$role] . ' and A.username != "'.sysRootAccount.'"';
			break;
		case 'manager'	 	:
		case 'administrator':
			$table = 'WM_user_account as A left join '.sysDBname.'.WM_manager as B on A.username = B.username';
			$where = 'B.level & ' . $sysRoles[$role] . ' and B.school_id = ' . $sysSession->school_id;
			break;
	}
	if ($keyword != '') {
		if ($type == 'account') {
			$where .= ' and A.username like "%' . escape_LIKE_query_str(addslashes($keyword)) . '%" ';
		}
		else if ($type == 'name') {
			$where .= ' and if(A.first_name REGEXP "^[0-9A-Za-z _-]*$" && A.last_name REGEXP "^[0-9A-Za-z _-]*$", concat(A.first_name, " ", A.last_name), concat(A.last_name, A.first_name)) like "%'. escape_LIKE_query_str(addslashes($keyword)) .'%"';
		}
	}
	// 設定SQL End

// "{$members}";	// 記錄已經勾選的帳號
	$js = <<< EOB
	var MSG_SELECT_ALL    = "{$MSG['select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['cancel_all'][$sysSession->lang]}";
	var members = opener.$('#extra_member').val().replace(/^\s+|\s+$/g, '').replace(/\s+/g, ',');

	function Page_Row(row){
		query(row);
	}

	function query(rows) {
		var obj = new Array();	// 因為page_num出現兩次,先用obj暫存起來
		var qForm = document.getElementById('queryFm');
		if (typeof(qForm) != 'object') return;

		selects = document.getElementsByTagName('select');
		for(var i = 0; i < selects.length; i++)
			obj[selects[i].name] = selects[i].value;
		obj['keyword'] = document.getElementById('keyword').value;
		qForm.role.value 	= obj['role'];
		qForm.type.value 	= obj['type'];
		qForm.keyword.value = obj['keyword'];
		qForm.page_num.value= (rows && rows != '' ? rows : obj['page_num']);
		qForm.members.value = getMember();
		qForm.submit();
	}

	function chgPage() {
		var obj = document.getElementById('queryFm');
		if (typeof(obj) != 'object') return;
		members = getMember();
		return '&role=' + obj.role.value + '&type=' + obj.type.value + '&keyword=' + obj.keyword.value + '&page_num=' + obj.page_num.value; // + (members != '' ? ('&members=' + members) : '');
	}

	/**
	 *	控制全選/全消的按鈕
	 */
	var nowSel = false;
	function selfunc() {
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((obj == null) || (btn1 == null) || (btn2 == null)) return false;

		nowSel = !nowSel;
		obj.checked = nowSel;
		btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			nodes[i].checked = nowSel;
		}
	}

	/**
	 *	控制每當點選一個checkbox後,檢查是否要改變全選/全消的狀態
	 */
	function chgCheckbox() {
		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;
		if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
	}

	function getMember() {
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return members;

		if (members != '') members = ',' + members + ',';

		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;

			if (members.indexOf((','+nodes[i].value+',')) != -1) {
				if (!nodes[i].checked)
					members = members.replace((','+nodes[i].value+','), ',');
			}
			else {
				if (nodes[i].checked)
					members += members == '' ? (','+nodes[i].value+',') : (nodes[i].value+',');
			}
		}

		return members.replace(/^,/,'').replace(/,$/,'');
	}

	function setMember() {
		if (!opener) window.close();
		opener.$('#extra_member').val(replace_all(getMember(), ',', '\\n'));
		window.close();
	}

	/**
	 * 取代字串中所有符合搜尋的字串
	 */
	function replace_all(str, find, replace) {
		while (str.indexOf(find) != -1)
			str = str.replace(find, replace);
		return str;
	}

	window.onload = function () {
		if (members == '') return;

		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return;

		var result = false;
		for (var i = 1; i < nodes.length; i++) {
			if (nodes[i].name != "uid[]") continue;
			if (members.search(new RegExp('\\\\b' + nodes[i].value + '\\\\b')) > -1)
				nodes[i].checked = true;
		}
	};


EOB;
	// UI
	showXHTML_head_B('');
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
        showXHTML_script('include', '/lib/jquery/jquery-1.7.2.min.js', true, null, 'UTF-8');
		showXHTML_script('inline', $js);
	showXHTML_head_E();

	showXHTML_body_B();
		$ary = array( array($MSG['sel_account_title'][$sysSession->lang], 'tabsSet1',  '') );
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'tabFm', '', 'method="post" enctype="multipart/form-data" style="display:inline"');
			$myTable = new table();
			$myTable->extra = 'width="520" border="0" cellspacing="1" cellpadding="3" id="tb_sel" class="cssTable"';
			// 查詢工具列
			$toolbar = new toolbar();
			$toolbar->add_caption($MSG['role'][$sysSession->lang]);
			$toolbar->add_input('select', 'role', $arr_roles, $role, 'id="role" class="cssInput" onchange="query();"');
			$toolbar->add_input('select', 'type', $arr_types, $type, 'id="type" class="cssInput"');
			$toolbar->add_input('text'  , 'keyword', $keyword, '', 'id="keyword" size="30" maxlength="60" class="cssInput" onKeyPress="if (event.keyCode==13) {query();return false;}"');
			$toolbar->add_input('button', '', $MSG['search'][$sysSession->lang], '', 'id="sBtn" class="button01" onclick="query();"');
			$myTable->add_toolbar($toolbar);

			// 全選全消的按鈕
			$myTable->set_select_btn(true, 'btnSel', $MSG['select_all'][$sysSession->lang], 'onclick="selfunc()"');

			// 確定與關閉的工具列
			$toolbar1 = new toolbar();
			$toolbar1->add_caption('&nbsp;&nbsp;');
			$toolbar1->add_input('button', '', $MSG['sure'][$sysSession->lang] , '', 'class="cssBtn" onclick="setMember();"');
			$toolbar1->add_input('button', '', $MSG['close'][$sysSession->lang] , '', 'class="cssBtn" onclick="window.close();"');
			$toolbar1->add_caption('&nbsp;&nbsp;');
			$myTable->set_def_toolbar($toolbar1);

			// 資料
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

			$myTable->add_field($ck1, $MSG['select_cancle'][$sysSession->lang], '', '%0'   	 	 , 'showCheckBox', 'width="20" align="center"');
			$myTable->add_field($MSG['account'][$sysSession->lang], ''		  , '', '%0'         , 'showUser'    , 'nowrap="noWrap"');
			$myTable->add_field($MSG['name'][$sysSession->lang], ''			  , '', '%1 %2 %3'   , 'showName'    , 'nowrap="noWrap"');
			$myTable->set_page(true, 1, $page_num, 'chgPage();');
			// $sysConn->debug = true;
			$myTable->set_sqls($table, 'A.username, A.first_name, A.last_name, A.language', $where . $group_by);
			$myTable->display['page_num'] = true;

			// 修正 #1820 問題 begin
			ob_start('adjectDuplicateName');
			$myTable->show();
			$page_no = $myTable->display['page_no'];
			ob_end_flush();
			// 修正 #1820 問題 end

			// $sysConn->debug = false;
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm');
			showXHTML_input('hidden', 'role', $role, '', '');
			showXHTML_input('hidden', 'type', $type, '', '');
			showXHTML_input('hidden', 'keyword', ($keyword != '' ? base64_encode($keyword) : ''), '', '');
			showXHTML_input('hidden', 'page_num', $page_num, '', '');
			showXHTML_input('hidden', 'members',  '' , '', '');
		showXHTML_form_E('');

	showXHTML_body_E();
// }}} 主程式 end

?>
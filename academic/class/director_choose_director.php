<?php
	/**
	 * 管理者 - 導師管理 - 新增 / 卸除 or 查詢 - 先找出特定的班級 - 再新增班級導師。
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: director_choose_director.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2005-09-22
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');

	// 變數宣告 begin
	if(isset($_POST['class_id']))
		$class_id = base64_decode($_POST['class_id']);
	else
		$class_id = base64_decode($_GET['class_id']);

	if($_POST['pre_chk_user'] != ''){
		$org_pre_chk_user = trim($_POST['pre_chk_user']);
		$pre_chk_user_tmp = base64_decode($org_pre_chk_user);
		$pre_chk_user_ary = preg_split('/[^\w.-]+/', $pre_chk_user_tmp, -1, PREG_SPLIT_NO_EMPTY);
	}elseif($_GET['pre_chk_user'] != ''){
		$org_pre_chk_user = trim($_GET['pre_chk_user']);
		$pre_chk_user_tmp = base64_decode($org_pre_chk_user);
		$pre_chk_user_ary = preg_split('/[^\w.-]+/', $pre_chk_user_tmp, -1, PREG_SPLIT_NO_EMPTY);
	}

	// 編碼的 class_id
	$encode_class_id = base64_encode($class_id);

	list($class_caption) = dbGetStSr('WM_class_main','caption','class_id=' . $class_id, ADODB_FETCH_NUM);
	if(strlen($class_caption) == 0) {
		header('Location: director_choose_class.php');
	}
	$class_name = unserialize($class_caption);

	if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
		$exec_func   = '2400200200';
		$remove_type = 'type=' . trim($_GET['type']);

		$class_help  = str_replace('%CLASS%','<font color="#0000FF">' . $class_name[$sysSession->lang] . '</font>',$MSG['title72'][$sysSession->lang]);

		$table_alias = 'U.';

		$btn_method  = 'onclick="next_stage();"';
	}else if($_GET['type'] == 'query'){	// 由班級查出此班級中所有的導師(或助理)
		$exec_func   = '2400200300';

		$table_alias = 'U.';
		$remove_type = 'type=' . trim($_GET['type']);
		$class_help  = str_replace('%CLASS%','<font color="#0000FF">' . $class_name[$sysSession->lang] . '</font>',$MSG['title82'][$sysSession->lang]);

		$btn_method  = 'onclick                          ="javascript:window.location.href=\'director_main.php\';"';
	}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
		$exec_func  = '2400200100';

		$class_help = str_replace('%CLASS%','<font color="#0000FF">' . $class_name[$sysSession->lang] . '</font>',$MSG['title57'][$sysSession->lang]);

		$btn_method = 'onclick="next_stage();"';
	}


	$sysSession->cur_func = $exec_func;
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{

	}

	// 尋找人員的陣列
	$search_ary = array(
		'real'    => $MSG['title39'][$sysSession->lang],
		'account' => $MSG['title38'][$sysSession->lang]
	);

	if (isset($_POST['D_searchkey'])) {
		$sType = trim($_POST['D_searchkey']);
	} else if (isset($_GET['D_searchkey'])) {
		$sType = trim($_GET['D_searchkey']);
	}else{
		$sType = 'real';
	}

	if(isset($_POST['page_change'])) {

		$sWord = other_dec(trim($_POST['D_keyword']));

		$js_keyword = $sWord;
	}else{
		if (isset($_POST['D_keyword'])) {
			$sWord = trim($_POST['D_keyword']);
			$js_keyword = $sWord;
		} else if (isset($_GET['D_keyword'])) {
			$sWord = other_dec(trim($_GET['D_keyword']));
			$js_keyword = $sWord;
		}else{
			$sWord = $MSG['D_keyword'][$sysSession->lang];
			$js_keyword = '';
		}
	}

	if (!empty($sType) && isset($js_keyword)) {
		switch ($sType) {
			case 'real'    :    // 姓名
				if (isset($sWord)){
					$sqls = ' and if(' . $table_alias . 'first_name REGEXP "^[0-9A-Za-z _-]$" && ' . $table_alias . 'last_name REGEXP "^[0-9A-Za-z _-]$", concat(' . $table_alias . 'first_name, " ", ' . $table_alias . 'last_name), concat(' . $table_alias . 'last_name, ' . $table_alias . 'first_name)) LIKE "%' . escape_LIKE_query_str(addslashes($js_keyword)) . '%" ';
				}
				break;
			case 'account' :    // 帳號
				if (isset($sWord)){
					$sqls = ' AND ' . $table_alias . 'username like "%' . escape_LIKE_query_str(addslashes($js_keyword)) . '%" ';
				}
				break;
		}
		$sWord = other_enc($sWord);
	}

	if ($_POST['page_num'] != ''){
		$page_num = intval($_POST['page_num']);
	}elseif ($_GET['page_num'] != ''){
		$page_num = intval($_GET['page_num']);
	}

	if (empty($page_num)) $page_num = sysPostPerPage;

	if ($_POST['D_page'] != ''){
		$page = intval($_POST['D_page']);
	}elseif ($_GET['D_page'] != ''){
		$page = intval($_GET['D_page']);
	}
	if (empty($page)) $page = 1;

	// 變數宣告 end

	// 函數宣告 begin
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

	function showCheckBox($user) {
		global $class_id,$sysRoles,$_GET,$pre_chk_user_ary;

		$isDirect = aclCheckRole($user, $sysRoles['director'] | $sysRoles['assistant'], $class_id);

		if((count($pre_chk_user_ary) > 0) && in_array($user,$pre_chk_user_ary)){
			$user_check = ' checked';
		}else{
			$user_check = '';
		}
		if ((! $isDirect) || ($_GET['type'] == 'remove')){
			$user_id = base64_encode($user . '_' . $class_id);
			showXHTML_input('checkbox', 'role[]', $user_id, '', 'id="' . $user_id . '" onClick="chgCheckbox(this);" ' . $user_check);
		}else
			return '&nbsp;';
	}
	/**
	 * 顯示帳號
	 * @param string $val : 帳號
	 * @return string : 要顯示的文字
	 **/
	function showUser($val) {
		$user = $val;
		//Chrome
		return divMsg(230, $user, $val);
	}

	/**
	 * 顯示姓名
	 * @param string $f : 名字
	 * @param string $l : 姓名
	 * @param string $user_lang : 預設語系
	 * @return string : 要顯示的文字
	 **/
	function showName($f, $l) {
        // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
        $real = checkRealname($f,$l);
		return divMsg(200, $real);
	}
	/**
	 * 顯示身份
	 **/
	function showRole($user,$role) {
		global $class_id,$direct_ary,$MSG,$sysSession,$sysRoles,$_GET,$pre_chk_user_ary;

		$isDirect = aclCheckRole($user, $sysRoles['director'] | $sysRoles['assistant'], $class_id);

		$director_key  = base64_encode($user . '_' . $class_id . '_director');
		$assistant_key = base64_encode($user . '_' . $class_id . '_assistant');

		$user_id = base64_encode($user . '_' . $class_id);

		// 尋找身份的陣列
		$direct_ary = array(
			$director_key  => $MSG['title45'][$sysSession->lang],
			$assistant_key => $MSG['title44'][$sysSession->lang]
		);

		if((count($pre_chk_user_ary) > 0) && in_array($user,$pre_chk_user_ary))
			$sel_disabled = '';
		else
			$sel_disabled = ' disabled';

		if($_GET['type'] == 'remove') {
			$delete_key = base64_encode($user . '_' . $class_id . '_DEL');
			// 尋找身份的陣列
			$direct_ary[$delete_key] = $MSG['title73'][$sysSession->lang];

			// 下拉選單 預設值
			$default_select =  '';
			switch(intval($role)){
				case $sysRoles['assistant']:
				case ($sysRoles['assistant'] | $sysRoles['student']):
					$default_select = $assistant_key;
					break;
				case $sysRoles['director']:
				case ($sysRoles['director'] | $sysRoles['student']):
					$default_select = $director_key;
					break;
			}

			showXHTML_input('select', 'select_role[]',$direct_ary, $default_select, 'class="cssInput" id="' . $user_id . '" ' . $sel_disabled);
		}elseif($_GET['type'] == 'query'){
			switch(intval($role)){
				case $sysRoles['assistant']:
				case ($sysRoles['assistant'] | $sysRoles['student']):
					return $MSG['title44'][$sysSession->lang];
					break;
				case $sysRoles['director']:
				case ($sysRoles['director'] | $sysRoles['student']):
					return $MSG['title45'][$sysSession->lang];
					break;
			}
		}else{
			if($isDirect) {
				$role = aclCheckRole($user, ($sysRoles['director']|$sysRoles['assistant']|$sysRoles['student']), $class_id, true);
				switch(intval($role)){
					case $sysRoles['assistant']:
					case ($sysRoles['assistant'] | $sysRoles['student']):
						return $MSG['title44'][$sysSession->lang];
						break;
					case $sysRoles['director']:
					case ($sysRoles['director'] | $sysRoles['student']):
						return $MSG['title45'][$sysSession->lang];
						break;
					default:
						showXHTML_input('select', 'select_role[]',$direct_ary, '', 'class="cssInput" id="' . $user_id . '" ' . $sel_disabled);
						break;
				}
			}else{
				showXHTML_input('select', 'select_role[]',$direct_ary, '', 'class="cssInput" id="' . $user_id . '" ' . $sel_disabled);
			}
		}
	}
	/*
	 *顯示 登入的 button
	 */
	function showBtn($user) {
		global $MSG,$sysSession,$class_id;
		showXHTML_input('button', '', $MSG['title87'][$sysSession->lang], '', 'class="cssBtn" onclick="Direct_Login(\'' . $user . '\',' . $class_id . ');"');
	}
	// 函數宣告 end

	// 主程式 begin
	$js = <<< BOF
	var sType             = "{$sType}";
	var page_num          = {$page_num};
	var keyword           = "{$sWord}";
	var MSG_SELECT_ALL    = "{$MSG['title55'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['title56'][$sysSession->lang]}";
	var class_id          = "{$encode_class_id}";
	var remove_type       = "{$remove_type}";
	var pre_chk_user      = "{$org_pre_chk_user}";

	function Page_Row(row){
		var obj = null;
		var val1 = '', val2 = '',val3;
		obj = document.getElementById("D_searchkey");
		if (obj != null) val1 = obj.value;

		obj = document.getElementById("D_keyword");
		if (obj != null) val2 = obj.value;

		if (val2 == "") val1 = 'real';
		obj = document.getElementById("queryFm2");
		if (obj != null) {
			obj.D_searchkey.value = val1;
			obj.D_keyword.value   = keyword;
			obj.page_num.value   = row;
			obj.submit();
		}
	}

	function queryUser() {
		var obj = null;
		var val1 = '', val2 = '',val3 = '', val4 = '';
		obj = document.getElementById("D_searchkey");
		if (obj != null) val1 = obj.value;

		obj = document.getElementById("D_keyword");

		if (obj != null) val2 = obj.value;

		obj = document.getElementById("page_num");
		if (obj != null) val3 = obj.value;

		if (val2 == "") val1 = 'real';

		obj = document.getElementById("queryFm");
		if (obj != null) {
			obj.D_searchkey.value = val1;
			obj.D_keyword.value   = val2;
			obj.page_num.value    = val3;
			obj.submit();
		}
	}
	function chgPageSort() {
		if (class_id == "") return "";
		var url_link = '';
		if(remove_type.length > 0) {
			url_link = "&class_id=" + class_id +"&D_keyword="+keyword+'&D_searchkey='+sType+'&'+remove_type;
		}else{
			url_link = "&class_id=" + class_id +"&D_keyword="+keyword+'&D_searchkey='+sType;
		}
		if(pre_chk_user.length > 0){
			url_link += '&pre_chk_user='+pre_chk_user;
		}
		return url_link;
	}

	function next_stage() {
		var fobj = document.DForm;
		fobj.submit();
	}

	function Direct_Login(user,class_id) {
		var obj = document.DirectLogin;
		obj.username.value = user;
		obj.class_id.value = class_id;
		obj.submit();
	}
	var aryBtns = ["btnSel", "btnPage", "btnFirst", "btnPrev", "btnNext", "btnLast", "btnStep"];
	window.onload = function () {
		obj1 = document.getElementById("tools12");
		obj2 = document.getElementById("tools21");
		if ((obj1 == null) || (obj2 == null)) return false;
		txt = obj1.innerHTML;
		for (var i = 0; i < aryBtns.length; i++) {
			txt = txt.replace(aryBtns[i] + "1", aryBtns[i] + "2");
		}
		obj2.innerHTML = txt;
		synBtn();
	}
BOF;
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', 'director.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'queryTable');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'DForm', 'Dtable', 'action="director_choose_class_preview.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" encType="multipart/form-data" onsubmit="return false;" style="display: inline"');
			showXHTML_input('hidden', 'class_id'  , $encode_class_id, '', 'id="class_id"');

			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';
			$myTable->display['help_class'] = 'cssTrHelp';

			$myTable->add_help($class_help);

			// 工具列
			$toolbar = new toolbar();
			$toolbar->add_caption($MSG['title49'][$sysSession->lang]);
			$toolbar->add_input('select', 'D_searchkey', $search_ary, $sType, 'id="D_searchkey" class="cssInput"');
			$toolbar->add_input('text', 'D_keyword', htmlspecialchars(stripslashes($js_keyword)), '', 'id="D_keyword" size="20"  class="cssInput" onclick="this.value=\'\'"');

			// 每頁顯示幾筆
			$toolbar->add_input('button', '', $MSG['title51'][$sysSession->lang], '', 'class="cssBtn" onclick="queryUser()"');
			$myTable->add_toolbar($toolbar);

			// 全選全消的按鈕
			if($_GET['type'] != 'query')
				$myTable->set_select_btn(true, 'btnSel', $MSG['msg_select_all'][$sysSession->lang], 'onclick="selfunc()"');

			// 工具列
			$toolbar = new toolbar();
			// 設定每頁可顯示幾筆
			define('SET_PAGE_LINE',2);
			$page_array = array(sysPostPerPage=> $MSG['title89'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);
			$toolbar->add_caption($MSG['every_page'][$sysSession->lang]);
			$toolbar->add_input('select', 'page_num', $page_array, $page_num, 'class="cssInput" onchange="Page_Row(this.value);" style="width: 50px"', '');
			$myTable->set_def_toolbar($toolbar);

			// 上一頁 & 下一頁 的 button
			$toolbar->add_caption('&nbsp;&nbsp;&nbsp;');
			$toolbar->add_input('button', 'btnPre', $MSG['title16'][$sysSession->lang]   , '', 'id="btnPre" class="cssBtn" onclick="javascript:window.location.href=\'director_choose_class.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'')  . '\';"');
			if($_GET['type'] == 'query')
				$toolbar->add_input('button', 'btnMain1', $MSG['title83'][$sysSession->lang]   , '', 'id="btnMain1" class="cssBtn" ' . $btn_method);
			else
				$toolbar->add_input('button', 'btnStep1', $MSG['title11'][$sysSession->lang]   , '', 'id="btnStep1" class="cssBtn" ' . $btn_method);

			$myTable->set_def_toolbar($toolbar);

			// 排序
			$myTable->add_sort('account'  , '`username` ASC', '`username` DESC');
			if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
				$myTable->add_sort('name'  , '`last_name` ASC, `first_name` ASC', '`last_name` DESC, `first_name` DESC');
			} else {
				$myTable->add_sort('name'  , '`first_name` ASC, `last_name` ASC', '`first_name` DESC, `last_name` DESC');
			}

			$myTable->set_sort(true, 'account', 'asc', 'chgPageSort()');

			// 欄位
			// 資料
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');
			if($_GET['type'] != 'query')
				$myTable->add_field($ck1, '', '', '%0'   , 'showCheckBox'  , 'align="center"');
			$myTable->add_field($MSG['title41'][$sysSession->lang], '', ''  , '%0 %4'   , 'showRole'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title38'][$sysSession->lang], '', 'user'  , '%0'   , 'showUser'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title39'][$sysSession->lang], '', 'name'  , '%1 %2 %3', 'showName'  , 'nowrap="noWrap"');
			if($_GET['type'] == 'query')
				$myTable->add_field($MSG['title86'][$sysSession->lang], '', ''  , '%0', 'showBtn'  , 'nowrap="noWrap" align="center"');


			// $myTable->set_page(true, 1, 10, 'chgPageSort()');
			$myTable->set_page(true, $page, $page_num, 'chgPageSort()');

			if(($_GET['type'] == 'remove') || ($_GET['type'] == 'query')){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務 or // 由班級查出此班級中所有的導師(或助理)
				$tab    = 'WM_user_account as U inner join WM_class_member as M on U.username = M.username';
				$fields = 'U.username,U.first_name,U.last_name,U.language,M.role';
				$where = ' U.username!="' . sysRootAccount . '" ' .
						 ' and M.class_id=' . $class_id .
						 ' and M.role&' . ($sysRoles['director'] | $sysRoles['assistant']) .
						 $sqls;
			}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
				// SQL 查詢指令
				$tab    = 'WM_user_account';
				$fields = '`username`,`first_name`,`last_name`,`language`';
				$where = '`username`!="' . sysRootAccount . '" ' . $sqls;
			}

			//  顯示幾筆
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="director_choose_director.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm');
			showXHTML_input('hidden', 'D_searchkey'   , $sType           , '', '');
			showXHTML_input('hidden', 'D_keyword'     , other_enc($sWord), '', '');
			showXHTML_input('hidden', 'page_num'      , $page_num        , '', '');
			showXHTML_input('hidden', 'class_id'      , $encode_class_id , '', '');
			showXHTML_input('hidden', 'pre_chk_user'  , $org_pre_chk_user, '', '');

		showXHTML_form_E('');

		showXHTML_form_B('action="director_choose_director.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm2');
			showXHTML_input('hidden', 'D_searchkey'   , $sType           , '', '');
			showXHTML_input('hidden', 'D_keyword'     , other_enc($sWord), '', '');
			showXHTML_input('hidden', 'page_num'      , $page_num        , '', '');
			showXHTML_input('hidden', 'class_id'      , $encode_class_id , '', '');
			showXHTML_input('hidden', 'pre_chk_user'  , $org_pre_chk_user, '', '');
			showXHTML_input('hidden', 'page_change'   , 'Y'              , '', 'id="page_change"');
		showXHTML_form_E('');

		showXHTML_form_B('action="/academic/relogin.php" method="post" enctype="multipart/form-data" style="display:none"', 'DirectLogin');
			showXHTML_input('hidden', 'username', ''      , '', '');
			showXHTML_input('hidden', 'class_id', ''      , '', '');
			showXHTML_input('hidden', 'go_where', 'direct', '', '');
		showXHTML_form_E('');

	showXHTML_body_E();
	// 主程式 end
?>

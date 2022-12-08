<?php
	/**
	 * 管理者 - 導師管理 - 新增 / 卸除 - 先找到特定導師，再指定他要帶領的班級。(適用於一個導師要帶領多個班級)
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      amm lee <amm@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: director_choose_director1.php,v 1.1 2010/02/24 02:38:14 saly Exp $
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

	if($_GET['type'] == 'remove'){	// 修改或卸除：修改或卸除已存在的導師(或助理)的職務
		$exec_func = '2400200200';
		$remove_type = 'type=' . trim($_GET['type']);
		$html_title = $MSG['title76'][$sysSession->lang];
	}else if($_GET['type'] == 'query'){	// 由班級查出此班級中所有的導師(或助理)
		$exec_func = '2400200300';
		$remove_type = 'type=' . trim($_GET['type']);
		$html_title = $MSG['title81'][$sysSession->lang];
	}else{	// 新增：新增一個或多個導師(或助理)到某個班級中
		$exec_func = '2400200100';
		$html_title = $MSG['title60'][$sysSession->lang];
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
					$sqls = ' and if(U.first_name REGEXP "^[0-9A-Za-z _-]$" && U.last_name REGEXP "^[0-9A-Za-z _-]$", concat(U.first_name, " ", U.last_name), concat(U.last_name, U.first_name)) LIKE "%' . escape_LIKE_query_str(addslashes($js_keyword)) . '%" ';
				}
				break;
			case 'account' :    // 帳號
				if (isset($sWord)){
					$sqls = 'AND U.username like "%' . escape_LIKE_query_str(addslashes($js_keyword)) . '%" ';
				}
				break;
		}
		$sWord = other_enc($sWord);
	}

	if (isset($_POST['page_num'])){
		$page_num = intval($_POST['page_num']);
	}else if (isset($_GET['page_num'])){
		$page_num = intval($_GET['page_num']);
	}
	if (empty($page_num)) $page_num = sysPostPerPage;

	if (isset($_POST['D_page'])){
		$page = intval($_POST['D_page']);
	}else if (isset($_GET['D_page'])){
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
		global $class_id,$sysRoles;

		$isDirect = aclCheckRole($user, $sysRoles['director'] | $sysRoles['assistant'], $class_id);
		if(! $isDirect) {
			$user_id = base64_encode($user . '_' . $class_id);
			showXHTML_input('checkbox', 'role[]', $user_id, '', 'id="' . $user_id . '" onClick="chgCheckbox(this);"');
		}else
			Return '&nbsp;';
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
	function showName($user,$f, $l,$user_lang) {
		global $sysSession;
        // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
        $real = checkRealname($f,$l);
		$encode_user = base64_encode($user);

		return '<a href="javascript:void;" onclick="href_link(\'' . $encode_user . '\'); return false;" class="cssAnchor">' . divMsg(200, $real) . '</a>';
	}
	function showRadio($val) {
		$encode_val = base64_encode($val);

		showXHTML_input('radio', 'username', array($encode_val=>''), '', 'onclick="event.cancelBubble=true;chgBtnStat();"');
	}
	// 函數宣告 end

	// 主程式 begin
	$js = <<< BOF
	var sType             = "{$sType}";
	var page_num          = {$page_num};
	var keyword           = "{$sWord}";
	var MSG_SELECT_ALL    = "{$MSG['title55'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['title56'][$sysSession->lang]}";
	var remove_type       = "{$remove_type}";

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
		if(remove_type.length > 0)
			return "&D_keyword="+keyword+'&D_searchkey='+sType+'&'+remove_type;
		else
			return "&D_keyword="+keyword+'&D_searchkey='+sType;
	}
	function next_stage() {
		var nodes = document.getElementsByTagName("input");
		var radio_true = 0;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type == "radio") && (nodes[i].checked == true)) {
				radio_true++;
				break;
			}
		}
		if(radio_true == 0) {
			alert("{$MSG['title88'][$sysSession->lang]}");
			return false;
		}
		var fobj = document.DForm;
		fobj.submit();
	}
	function href_link(user_val) {
		var fobj = document.hrefFm;
		fobj.username.value = user_val;
		fobj.submit();
	}
	
	function chgBtnStat() {
		document.all.btnNext[0].disabled = '';
		document.all.btnNext[1].disabled = '';
	}
BOF;
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', 'director.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'queryTable');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'DForm', 'Dtable', 'action="director_choose_class1.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" encType="multipart/form-data" onsubmit="return false;" style="display: inline"');
			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';
			$myTable->display['help_class'] = 'cssTrHelp';
			$myTable->add_help($html_title);

			// 工具列
			$toolbar = new toolbar();

			$toolbar->add_caption($MSG['title49'][$sysSession->lang]);
			$toolbar->add_input('select', 'D_searchkey', $search_ary, $sType, 'id="D_searchkey" class="cssInput"');
			$toolbar->add_input('text', 'D_keyword', htmlspecialchars(stripslashes($js_keyword)), '', 'id="D_keyword" size="20"  class="cssInput" onclick="this.value=\'\'"');

			// 每頁顯示幾筆
			$toolbar->add_input('button', '', $MSG['title51'][$sysSession->lang], '', 'class="cssBtn" onclick="queryUser()"');
			$myTable->add_toolbar($toolbar);

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
			$toolbar->add_input('button', 'btnPre', $MSG['title16'][$sysSession->lang]   , '', 'id="btnPre" class="cssBtn" onclick="javascript:window.location.href=\'director_add.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '\';"');
			$toolbar->add_input('button', 'btnNext', $MSG['title11'][$sysSession->lang]   , '', 'id="btnNext" class="cssBtn" onclick="next_stage();" disabled="true" name="btnNext[]"');
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
			$myTable->add_field($MSG['title54'][$sysSession->lang], '', ''    , '%0'         , 'showRadio', 'align="center"');
			$myTable->add_field($MSG['title38'][$sysSession->lang], '', 'user', '%0'         , 'showUser' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title39'][$sysSession->lang], '', 'name', '%0 %1 %2 %3', 'showName' , 'nowrap="noWrap"');

			// $myTable->set_page(true, 1, 10, 'chgPageSort()');
			$myTable->set_page(true, $page, $page_num, 'chgPageSort()');

			// SQL 查詢指令
			$fields = 'distinct U.`username`,U.`first_name`,U.`last_name`,U.`language`';
			if ($_GET['type'] == 'query' || $_GET['type'] == 'remove')
			{
				$tab    = 'WM_user_account as U inner join WM_class_member as C on U.username = C.username';
				$where  = 'U.`username`!="' . sysRootAccount . '" ' . $sqls . ' and C.role & ' . ($sysRoles['director'] | $sysRoles['assistant']);
			}
			else
			{
				$tab    = 'WM_user_account as U';
				$where  = 'U.`username`!="' . sysRootAccount . '" ' . $sqls;
			}
			
			//  顯示幾筆
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="director_choose_director1.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm');
			showXHTML_input('hidden', 'D_searchkey', $sType           , '', 'id="searchkey"');
			showXHTML_input('hidden', 'D_keyword'  , other_enc($sWord), '', 'id="keyword"');
			showXHTML_input('hidden', 'page_num'   , $page_num        , '', 'id="page_num"');
		showXHTML_form_E('');

		showXHTML_form_B('action="director_choose_class1.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'hrefFm');
			showXHTML_input('hidden', 'username'  , '', '', 'id="class_id"');
		showXHTML_form_E('');

		showXHTML_form_B('action="director_choose_director1.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm2');
			showXHTML_input('hidden', 'D_searchkey'  , $sType           , '', 'id="searchkey"');
			showXHTML_input('hidden', 'D_keyword'    , other_enc($sWord), '', 'id="keyword"');
			showXHTML_input('hidden', 'page_num'     , $page_num        , '', 'id="page_num"');
			showXHTML_input('hidden', 'page_change'  , 'Y'              , '', 'id="page_change"');
		showXHTML_form_E('');

	showXHTML_body_E();
	// 主程式 end
?>

<?php
	/**
	 * �޲z�� - �ɮv�޲z - �s�W / ����  - �����S�w�ɮv�A�A���w�L�n�a�⪺�Z�šC(�A�Ω�@�Ӿɮv�n�a��h�ӯZ��)
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      Amm Lee <amm@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: director_choose_class1.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2006-01-09
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lib/character_class.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');

	// �ܼƫŧi begin
	if($_GET['type'] == 'remove'){	// �ק�Ψ����G�ק�Ψ����w�s�b���ɮv(�ΧU�z)��¾��
		$exec_func = '2400200200';
		$remove_type = 'type=' . trim($_GET['type']);
	}else if($_GET['type'] == 'query'){	// �ѯZ�Ŭd�X���Z�Ť��Ҧ����ɮv(�ΧU�z)
		$exec_func = '2400200300';
		$remove_type = 'type=' . trim($_GET['type']);
	}else{	// �s�W�G�s�W�@�өΦh�Ӿɮv(�ΧU�z)��Y�ӯZ�Ť�
		$exec_func = '2400200100';
	}
	$sysSession->cur_func=$exec_func;
	$sysSession->restore();
	if (!aclVerifyPermission($exec_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	if (isset($_POST['username'])) {
		$encode_user = trim($_POST['username']);
		$user = base64_decode($encode_user);
	}elseif (isset($_GET['username'])){
		$encode_user = trim($_GET['username']);
		$user = base64_decode($encode_user);
	}

	$user_result = checkUsername($user);
	if($user_result == 2) {
		$user_ary = getUserDetailData($user);
		// �d�߬Y�H ���Щ� ���X�ӯZ��
		$class_permit = $sysRoles['director'] | $sysRoles['assistant'];
		$user_class_ary = WMdirector::listClass($user,$class_permit);
	}else{
		header('Location : director_choose_director1.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:''));
	}

	if($_GET['type'] == 'remove'){	// �ק�Ψ����G�ק�Ψ����w�s�b���ɮv(�ΧU�z)��¾��
		$help_tmp = str_replace('%USERNAME%',$user,$MSG['title77'][$sysSession->lang]);
		$help_tmp = str_replace('%REALNAME%',$user_ary['realname'],$help_tmp);

		$btn_method = 'onclick="next_stage();"';
	}else if($_GET['type'] == 'query'){	// �ѯZ�Ŭd�X���Z�Ť��Ҧ����ɮv(�ΧU�z)
		$help_tmp = str_replace('%USERNAME%',$user,$MSG['title84'][$sysSession->lang]);
		$help_tmp = str_replace('%REALNAME%',$user_ary['realname'],$help_tmp);

		$btn_method = 'onclick="javascript:window.location.href=\'director_main.php\';"';
	}else{	// �s�W�G�s�W�@�өΦh�Ӿɮv(�ΧU�z)��Y�ӯZ�Ť�
		$help_tmp = str_replace('%USERNAME%',$user,$MSG['title61'][$sysSession->lang]);
		$help_tmp = str_replace('%REALNAME%',$user_ary['realname'],$help_tmp);

		$btn_method = 'onclick="next_stage();"';
	}
	// �M�䳡�����}�C
	$search_ary = array(
		'dep_id'	=> $MSG['title36'][$sysSession->lang],
		'caption'	=> $MSG['title37'][$sysSession->lang],
	);
	if(isset($_POST['page_change'])) {
		$sType = trim($_POST['searchkey']);
		$sWord = trim($_POST['keyword']);

		$sWord = other_dec(trim($_POST['keyword']));

		$js_keyword = $sWord;
	}else{
		if (isset($_POST['searchkey'])) {
			$sType = trim($_POST['searchkey']);
			$sWord = trim($_POST['keyword']);
			$js_keyword = $sWord;
		} else if (isset($_GET['searchkey'])) {
			$sType = trim($_GET['searchkey']);
			$sWord = trim($_GET['keyword']);

			$sWord = other_dec(trim($_GET['keyword']));

			$js_keyword = $sWord;
		}else{
			$sType = 'dep_id';
			$sWord = ''; // $MSG['title50'][$sysSession->lang]
			$js_keyword = '';
		}
	}

	if (!empty($sType) && isset($js_keyword)) {
		if($sType == 'dep_id') {
			$sqls = 'AND dep_id like "%' . escape_LIKE_query_str(addslashes($js_keyword)) . '%" ';
		}else{
			$class_ids = serialized_search(escape_LIKE_query_str(addslashes($js_keyword)), 'WM_class_main', 'class_id,caption');
			$sqls = 'AND class_id in (' . implode(',', array_keys($class_ids)) . ')';
		}

		$sWord = other_enc($sWord);
	}
	if (isset($_POST['page_num'])){
		$page_num = intval($_POST['page_num']);
	}else if (isset($_GET['page_num'])){
		$page_num = intval($_GET['page_num']);
	}

	if (empty($page_num)) $page_num = sysPostPerPage;

	$radio_checked = false; // radio �w�]�O�_���Q���
	// �ܼƫŧi end

	// ��ƫŧi begin
	/**
	 * ��ܳ����N��
	 * @param string $val : �b��
	 * @return string : �n��ܪ���r
	 **/
	function showDep($val) {
		return $val;
	}
	/**
	 * ��ܳ����W��
	 * @param string $class_id : ����
	 * @param string $val : ������serialize
	 * @return string : �n��ܪ���r
	 **/
	function showName($class_id,$val) {
		global $sysSession;
		$class_ary = unserialize($val);

		return $class_ary[$sysSession->lang];
	}

	/**
	 * ��ܨ���
	 **/
	function showRole($class_id) {
		global $user,$direct_ary,$MSG,$sysSession,$sysRoles,$_GET;

		$isDirect = aclCheckRole($user, $sysRoles['director'] | $sysRoles['assistant'], $class_id);

		$director_key	= base64_encode($user . '_' . $class_id . '_director');
		$assistant_key	= base64_encode($user . '_' . $class_id . '_assistant');
		$user_id		= base64_encode($user . '_' . $class_id);

		// �M�䨭�����}�C
		$direct_ary = array(
			$director_key	=> $MSG['title45'][$sysSession->lang],
			$assistant_key	=> $MSG['title44'][$sysSession->lang]
		);

		// �Z�� - ����
		$role = aclCheckRole($user, ($sysRoles['director']|$sysRoles['assistant']|$sysRoles['student']), $class_id, true);

		if($_GET['type'] == 'remove') {
			$delete_key = base64_encode($user . '_' . $class_id . '_DEL');
			// �M�䨭�����}�C
			$direct_ary[$delete_key] = $MSG['title73'][$sysSession->lang];

			// �U�Կ�� �w�]��
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

			showXHTML_input('select', 'select_role[]',$direct_ary, $default_select, 'class="cssInput" id="' . $user_id . '" disabled');
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
						showXHTML_input('select', 'select_role[]',$direct_ary, '', 'class="cssInput" id="' . $user_id . '" disabled');
						break;
				}
			}else{
				showXHTML_input('select', 'select_role[]',$direct_ary, '', 'class="cssInput" id="' . $user_id . '" disabled');
			}
		}
	}
	function showCheckBox($class_id) {
		global $user,$sysRoles,$_GET;

		$isDirect = aclCheckRole($user, $sysRoles['director'] | $sysRoles['assistant'], $class_id);
		if((! $isDirect) || ($_GET['type'] == 'remove')){
			$user_id = base64_encode($user . '_' . $class_id);
			showXHTML_input('checkbox', 'role[]', $user_id, '', 'id="' . $user_id . '" onClick="chgCheckbox(this);"');
		}else
			return '&nbsp;';
	}
	/*
	 *��� �n�J�� button
	 */
	function showBtn($class_id) {
		global $MSG,$sysSession,$user;
		showXHTML_input('button', '', $MSG['title87'][$sysSession->lang], '', 'class="cssBtn" onclick="Direct_Login(\'' . $user . '\',' . $class_id . ');"');
	}
	// ��ƫŧi end

	// �D�{�� begin
	$js = <<< BOF
	var sType             = "{$sType}";
	var page_num          = {$page_num};
	var keyword           = "{$sWord}";
	var MSG_SELECT_ALL    = "{$MSG['title55'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['title56'][$sysSession->lang]}";
	var remove_type       = "{$remove_type}";
	var username          = "{$encode_user}";

	function Page_Row(row){
		var obj = null;
		var val1 = '', val2 = '',val3;
		obj = document.getElementById("sType");
		if (obj != null) val1 = obj.value;

		obj = document.getElementById("sWord");
		if (obj != null) val2 = obj.value;

		if (val2 == "") val1 = 'dep_id';
		obj = document.getElementById("queryFm2");
		if (obj != null) {
			obj.searchkey.value = val1;
			obj.keyword.value   = keyword;
			obj.page_num.value  = row;
			obj.submit();
		}
	}

	function queryClass() {
		var obj = null;
		var val1 = '', val2 = '',val3;
		obj = document.getElementById("sType");
		if (obj != null) val1 = obj.value;

		obj = document.getElementById("sWord");
		if (obj != null) val2 = obj.value;

		obj = document.getElementById("page_num");
		if (obj != null) val3 = obj.value;

		if (val2 == "") val1 = 'dep_id';
		obj = document.getElementById("queryFm");
		if (obj != null) {
			obj.searchkey.value = val1;
			obj.keyword.value   = val2;
			obj.page_num.value  = val3;
			obj.submit();
		}
	}

	function chgPageSort() {
		if (sType == "") return "";
		var val = '';
		obj = document.getElementById("page_num");
		if (obj != null) val = obj.value;
		if(val == '') {
			val = 10;
		}
		if(remove_type.length > 0)
			return "&searchkey=" + sType+"&keyword="+keyword+'&page_num='+val+'&username='+username+'&'+remove_type;
		else
			return "&searchkey=" + sType+"&keyword="+keyword+'&page_num='+val+'&username='+username;
	}
	function next_stage() {
		var fobj = document.CForm;
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
		showXHTML_tabFrame_B($ary, 1, 'CForm', 'Ctable', 'action="director_choose_director_preview.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" encType="multipart/form-data" onsubmit="return false;" style="display: inline"');
			showXHTML_input('hidden', 'username'  , base64_encode($user), '', 'id="username"');
			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';
			$myTable->display['help_class'] = 'cssTrHelp';
			$myTable->add_help($help_tmp);

			// �u��C
			$toolbar = new toolbar();

			$toolbar->add_caption($MSG['title49'][$sysSession->lang]);
			$toolbar->add_input('select', 'sType', $search_ary, $sType, 'id="sType" class="cssInput"');
			$toolbar->add_input('text', 'sWord', htmlspecialchars(stripslashes($js_keyword)), '', 'id="sWord" size="20"  class="cssInput" onclick="this.value=\'\'"');

			// �C����ܴX��
			$toolbar->add_input('button', '', $MSG['title51'][$sysSession->lang], '', 'class="cssBtn" onclick="queryClass()"');
			$myTable->add_toolbar($toolbar);

			// ������������s
			if($_GET['type'] != 'query')
				$myTable->set_select_btn(true, 'btnSel', $MSG['msg_select_all'][$sysSession->lang], 'onclick="selfunc()"');

			// �u��C
			$toolbar = new toolbar();

			// �]�w�C���i��ܴX��
			define('SET_PAGE_LINE',2);
			$page_array = array(sysPostPerPage=> $MSG['title89'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);
			$toolbar->add_caption($MSG['every_page'][$sysSession->lang]);
			$toolbar->add_input('select', 'page_num', $page_array, $page_num, 'class="cssInput" onchange="Page_Row(this.value);" style="width: 50px"', '');
			$myTable->set_def_toolbar($toolbar);
			// �W�@�� & �U�@�� �� button
			$toolbar->add_caption('&nbsp;&nbsp;&nbsp;');
			$toolbar->add_input('button', 'btnPre', $MSG['title16'][$sysSession->lang]   , '', 'id="btnPre" class="cssBtn" onclick="javascript:window.location.href=\'director_choose_director1.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '\';"');
			if($_GET['type'] == 'query')
				$toolbar->add_input('button', 'btnMain1', $MSG['title83'][$sysSession->lang]   , '', 'id="btnMain1" class="cssBtn" ' . $btn_method);
			else
				$toolbar->add_input('button', 'btnStep1', $MSG['title11'][$sysSession->lang]   , '', 'id="btnStep1" class="cssBtn" ' . $btn_method);

			$myTable->set_def_toolbar($toolbar);

			// �Ƨ�
			$myTable->add_sort('dep_id' , '`dep_id` ASC' , '`dep_id` DESC');
			$myTable->add_sort('caption', '`caption` ASC', '`caption` DESC');

			$myTable->set_sort(true, 'dep_id', 'asc', 'chgPageSort()');

			// ���
			$ck1 = new toolbar();
			if($_GET['type'] != 'query'){
				$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');
				$myTable->add_field($ck1, '', '', '%0'   , 'showCheckBox'  , 'align="center"');
			}
			$myTable->add_field($MSG['title41'][$sysSession->lang], '', ''  		, '%0'   , 'showRole'	, 'nowrap="noWrap"');
			$myTable->add_field($MSG['title36'][$sysSession->lang], '', 'dep_id'	, '%2'   , 'showDep'	, 'nowrap="noWrap"');
			$myTable->add_field($MSG['title37'][$sysSession->lang], '', 'caption'	, '%0 %1', 'showName'	, 'nowrap="noWrap"');
			if($_GET['type'] == 'query')
				$myTable->add_field($MSG['title86'][$sysSession->lang], '', ''  , '%0', 'showBtn'  , 'nowrap="noWrap" align="center"');

			// $myTable->set_page(true, 1, 10, 'chgPageSort()');
			$myTable->set_page(true, 1, $page_num, 'chgPageSort()');

			// SQL �d�߫��O
			$tab    = 'WM_class_main as M';
			$fields = '`class_id`,`caption`,`dep_id`';

			if(($_GET['type'] == 'remove') || ($_GET['type'] == 'query')){	// �ק�Ψ����G�ק�Ψ����w�s�b���ɮv(�ΧU�z)��¾��
				if(count($user_class_ary) > 0) {
					$search_class_id = ' and `class_id` in (' . implode(',',array_keys($user_class_ary)) . ') ';
				}else{
					$search_class_id = ' and `class_id` in (999)';
				}
			}

			$where = '`class_id` > 1000000 ' . $sqls . $search_class_id;

			//  ��ܴX��
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();

		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="director_choose_class1.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm');
			showXHTML_input('hidden', 'searchkey' , $sType              , '', 'id="searchkey"');
			showXHTML_input('hidden', 'keyword'   , other_enc($sWord)   , '', 'id="keyword"');
			showXHTML_input('hidden', 'page_num'  , $page_num           , '', 'id="page_num"');
			showXHTML_input('hidden', 'username'  , base64_encode($user), '', 'id="username"');
		showXHTML_form_E('');

		showXHTML_form_B('action="director_choose_class1.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm2');
			showXHTML_input('hidden', 'searchkey'    , $sType              , '', 'id="searchkey"');
			showXHTML_input('hidden', 'keyword'      , other_enc($sWord)   , '', 'id="keyword"');
			showXHTML_input('hidden', 'page_num'     , $page_num           , '', 'id="page_num"');
			showXHTML_input('hidden', 'username'     , base64_encode($user), '', 'id="username"');
			showXHTML_input('hidden', 'page_change'  , 'Y'                 , '', 'id="page_change"');
		showXHTML_form_E('');


		showXHTML_form_B('action="/academic/relogin.php" method="post" enctype="multipart/form-data" style="display:none"', 'DirectLogin');
			showXHTML_input('hidden', 'username', ''      , '', '');
			showXHTML_input('hidden', 'class_id', ''      , '', '');
			showXHTML_input('hidden', 'go_where', 'direct', '', '');
		showXHTML_form_E('');

	showXHTML_body_E();

	// �D�{�� end
?>

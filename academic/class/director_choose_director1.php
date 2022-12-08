<?php
	/**
	 * �޲z�� - �ɮv�޲z - �s�W / ���� - �����S�w�ɮv�A�A���w�L�n�a�⪺�Z�šC(�A�Ω�@�Ӿɮv�n�a��h�ӯZ��)
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
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
	// �ܼƫŧi begin

	if($_GET['type'] == 'remove'){	// �ק�Ψ����G�ק�Ψ����w�s�b���ɮv(�ΧU�z)��¾��
		$exec_func = '2400200200';
		$remove_type = 'type=' . trim($_GET['type']);
		$html_title = $MSG['title76'][$sysSession->lang];
	}else if($_GET['type'] == 'query'){	// �ѯZ�Ŭd�X���Z�Ť��Ҧ����ɮv(�ΧU�z)
		$exec_func = '2400200300';
		$remove_type = 'type=' . trim($_GET['type']);
		$html_title = $MSG['title81'][$sysSession->lang];
	}else{	// �s�W�G�s�W�@�өΦh�Ӿɮv(�ΧU�z)��Y�ӯZ�Ť�
		$exec_func = '2400200100';
		$html_title = $MSG['title60'][$sysSession->lang];
	}
	$sysSession->cur_func = $exec_func;
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{

	}

	// �M��H�����}�C
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
			case 'real'    :    // �m�W
				if (isset($sWord)){
					$sqls = ' and if(U.first_name REGEXP "^[0-9A-Za-z _-]$" && U.last_name REGEXP "^[0-9A-Za-z _-]$", concat(U.first_name, " ", U.last_name), concat(U.last_name, U.first_name)) LIKE "%' . escape_LIKE_query_str(addslashes($js_keyword)) . '%" ';
				}
				break;
			case 'account' :    // �b��
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

	// �ܼƫŧi end

	// ��ƫŧi begin
	/**
	 * �B�z��ơA�L������������
	 * @param integer $width   : �n��ܪ��e��
	 * @param string  $caption : ��ܪ���r
	 * @param string  $title   : �B�ʪ����ܤ�r�A�Y�S���]�w�A�h�� $caption �̼�
	 * @return string : �B�z�᪺��r
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
	 * ��ܱb��
	 * @param string $val : �b��
	 * @return string : �n��ܪ���r
	 **/
	function showUser($val) {
		$user = $val;
		//Chrome
		return divMsg(230, $user, $val);
	}

	/**
	 * ��ܩm�W
	 * @param string $f : �W�r
	 * @param string $l : �m�W
	 * @param string $user_lang : �w�]�y�t
	 * @return string : �n��ܪ���r
	 **/
	function showName($user,$f, $l,$user_lang) {
		global $sysSession;
        // Bug#1263 �u��m�W����ܤ����ӭӤH�y�t�A�ӫ��ӭӤH�m�W���]�w by Small 2006/12/28
        $real = checkRealname($f,$l);
		$encode_user = base64_encode($user);

		return '<a href="javascript:void;" onclick="href_link(\'' . $encode_user . '\'); return false;" class="cssAnchor">' . divMsg(200, $real) . '</a>';
	}
	function showRadio($val) {
		$encode_val = base64_encode($val);

		showXHTML_input('radio', 'username', array($encode_val=>''), '', 'onclick="event.cancelBubble=true;chgBtnStat();"');
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

			// �u��C
			$toolbar = new toolbar();

			$toolbar->add_caption($MSG['title49'][$sysSession->lang]);
			$toolbar->add_input('select', 'D_searchkey', $search_ary, $sType, 'id="D_searchkey" class="cssInput"');
			$toolbar->add_input('text', 'D_keyword', htmlspecialchars(stripslashes($js_keyword)), '', 'id="D_keyword" size="20"  class="cssInput" onclick="this.value=\'\'"');

			// �C����ܴX��
			$toolbar->add_input('button', '', $MSG['title51'][$sysSession->lang], '', 'class="cssBtn" onclick="queryUser()"');
			$myTable->add_toolbar($toolbar);

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
			$toolbar->add_input('button', 'btnPre', $MSG['title16'][$sysSession->lang]   , '', 'id="btnPre" class="cssBtn" onclick="javascript:window.location.href=\'director_add.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '\';"');
			$toolbar->add_input('button', 'btnNext', $MSG['title11'][$sysSession->lang]   , '', 'id="btnNext" class="cssBtn" onclick="next_stage();" disabled="true" name="btnNext[]"');
			$myTable->set_def_toolbar($toolbar);

			// �Ƨ�
			$myTable->add_sort('account'  , '`username` ASC', '`username` DESC');
			if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
				$myTable->add_sort('name'  , '`last_name` ASC, `first_name` ASC', '`last_name` DESC, `first_name` DESC');
			} else {
				$myTable->add_sort('name'  , '`first_name` ASC, `last_name` ASC', '`first_name` DESC, `last_name` DESC');
			}
			$myTable->set_sort(true, 'account', 'asc', 'chgPageSort()');

			// ���
			// ���
			$myTable->add_field($MSG['title54'][$sysSession->lang], '', ''    , '%0'         , 'showRadio', 'align="center"');
			$myTable->add_field($MSG['title38'][$sysSession->lang], '', 'user', '%0'         , 'showUser' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title39'][$sysSession->lang], '', 'name', '%0 %1 %2 %3', 'showName' , 'nowrap="noWrap"');

			// $myTable->set_page(true, 1, 10, 'chgPageSort()');
			$myTable->set_page(true, $page, $page_num, 'chgPageSort()');

			// SQL �d�߫��O
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
			
			//  ��ܴX��
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
	// �D�{�� end
?>

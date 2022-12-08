<?php
	/**
	 * �޲z�� - �ɮv�޲z - �s�W / ���� or �d��- ����X�S�w���Z��
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
	 * @version     CVS: $Id: director_choose_class.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2006-01-09
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');
	// �ܼƫŧi begin

	if($_GET['type'] == 'remove'){	// �ק�Ψ����G�ק�Ψ����w�s�b���ɮv(�ΧU�z)��¾��
		$exec_func = '2400200200';
		$remove_type = 'type=' . trim($_GET['type']);
		$html_title = $MSG['title71'][$sysSession->lang];
	}else if($_GET['type'] == 'query'){	// �ѯZ�Ŭd�X���Z�Ť��Ҧ����ɮv(�ΧU�z)
		$exec_func = '2400200300';
		$remove_type = 'type=' . trim($_GET['type']);
		$html_title = $MSG['title81'][$sysSession->lang];
	}else{	// �s�W�G�s�W�@�өΦh�Ӿɮv(�ΧU�z)��Y�ӯZ�Ť�
		$exec_func = '2400200100';
		$html_title = $MSG['title52'][$sysSession->lang];
	}
	$btn_next_stage = $MSG['title11'][$sysSession->lang];

	$sysSession->cur_func=$exec_func;
	$sysSession->restore();
	if (!aclVerifyPermission($exec_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}
	// �M��Z�Ū��}�C
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

			$sWord = other_dec(trim($_GET['keyword']));
			$js_keyword = $sWord;
		}else{
			$sType = 'dep_id';
			$sWord = '';// $MSG['title50'][$sysSession->lang]
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

		return '<a href="javascript:void;" onclick="href_link(\'' . base64_encode($class_id) . '\'); return false;" class="cssAnchor">' . $class_ary[$sysSession->lang] . '</a>';
	}

	function showRadio($val) {
		$encode_val = base64_encode($val);
		showXHTML_input('radio', 'class_id', array($encode_val=>''), '', 'onclick="event.cancelBubble=true; enableButton();" ');
	}
	// ��ƫŧi end

	// �D�{�� begin
	$js = <<< BOF
	var sType       = "{$sType}";
	var page_num    = {$page_num};
	var keyword     = "{$sWord}";
	var remove_type = "{$remove_type}";

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
		var link_value = "&searchkey=" + sType+"&keyword="+keyword;
		if(link_value.length > 0){
			if (remove_type.length > 0)
				return link_value + '&' + remove_type;
			else
				return link_value;
		}else
			return remove_type;
	}
	function next_stage() {
		var nodes = document.getElementsByTagName("input");
		var radio_true = 0;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type == "radio") && (nodes[i].checked == true))
				radio_true++;
		}
		if(radio_true == 0) {
			alert("{$MSG['title88'][$sysSession->lang]}");
			return false;
		}
		var fobj = document.CForm;
		fobj.submit();
	}

	function href_link(click_val) {
		var fobj = document.hrefFm;
		fobj.class_id.value = click_val;
		fobj.submit();
	}

	// �������ƮɡA�~���I��U�@�B
	function enableButton(){
		var obj = document.getElementById('btnNext');
		if (obj == null) return false;

		obj.disabled = false;

		/*#47389 [Safari][�޲z��/�Z�ź޲z/�ɮv�޲z] �b�Z�ŦC��e����ܤ@�ӯZ�šA�e���������u�U�@�B�v���s�S��enable�C�Gdom�������D*/
        if (document.getElementById('dataTabs').lastChild.lastChild.previousSibling !== undefined) {
			// chrome or safari
			// document.getElementById('dataTabs').lastChild.childNodes[16].childNodes[1].childNodes[15].disabled = false;
            document.getElementById('dataTabs').lastChild.lastChild.previousSibling.childNodes[1].childNodes[15].disabled = false;
		} else {
			// ie
			document.getElementById('dataTabs').lastChild.lastChild.firstChild.childNodes[15].disabled = false;
		}
	}

BOF;

showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'queryTable');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'CForm', 'Ctable', 'action="director_choose_director.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" encType="multipart/form-data" onsubmit="return false;" style="display: inline"');
			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';
			$myTable->display['help_class'] = 'cssTrHelp';
			$myTable->add_help($html_title);

			// �u��C
			$toolbar = new toolbar();

			$toolbar->add_caption($MSG['title49'][$sysSession->lang]);
			$toolbar->add_input('select', 'sType', $search_ary, $sType, 'id="sType" class="cssInput"');
			$toolbar->add_input('text', 'sWord', htmlspecialchars(stripslashes($js_keyword)), '', 'id="sWord" size="20"  class="cssInput" onclick="this.value=\'\'"');

			// �C����ܴX��
			$toolbar->add_input('button', '', $MSG['title51'][$sysSession->lang], '', 'class="cssBtn" onclick="queryClass()"');
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
			$toolbar->add_input('button', 'btnNext', $btn_next_stage   , '', 'id="btnNext" class="cssBtn" onclick="next_stage();" disabled="disabled"');
			$myTable->set_def_toolbar($toolbar);

			// �Ƨ�
			$myTable->add_sort('dep_id' , '`dep_id` ASC' , '`dep_id` DESC');
			$myTable->add_sort('caption', '`caption` ASC', '`caption` DESC');

			$myTable->set_sort(true, 'dep_id', 'asc', 'chgPageSort()');

			// ���
			$myTable->add_field($MSG['title54'][$sysSession->lang], '', '', '%0'   , 'showRadio'  , 'align="center"');
			$myTable->add_field($MSG['title36'][$sysSession->lang], '', 'dep_id'  , '%2'   , 'showDep'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title37'][$sysSession->lang], '', 'caption'  , '%0 %1', 'showName'  , 'nowrap="noWrap"');


			// $myTable->set_page(true, 1, 10, 'chgPageSort()');
			$myTable->set_page(true, 1, $page_num, 'chgPageSort()');

			// SQL �d�߫��O
			$tab    = 'WM_class_main';
			$fields = '`class_id`,`caption`,`dep_id`';
			$where  = '`class_id` > 1000000 ' . $sqls;

			//  ��ܴX��
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="director_choose_class.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm');
			showXHTML_input('hidden', 'searchkey'   , $sType           , '', 'id="searchkey"');
			showXHTML_input('hidden', 'keyword'     , other_enc($sWord), '', 'id="keyword"');
			showXHTML_input('hidden', 'page_num'    , $page_num        , '', 'id="page_num"');
			showXHTML_input('hidden', 'class_page'  , $class_page      , '', 'id="class_page"');
		showXHTML_form_E('');

		showXHTML_form_B('action="director_choose_director.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'hrefFm');
			showXHTML_input('hidden', 'class_id'  , '', '', 'id="class_id"');
		showXHTML_form_E('');

		showXHTML_form_B('action="director_choose_class.php' . (strlen($remove_type) > 0 ? '?' . $remove_type:'') . '" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm2');
			showXHTML_input('hidden', 'page_change'  , 'Y'              , '', 'id="page_change"');
			showXHTML_input('hidden', 'searchkey'    , $sType           , '', 'id="searchkey"');
			showXHTML_input('hidden', 'keyword'      , other_enc($sWord), '', 'id="keyword"');
			showXHTML_input('hidden', 'page_num'     , $page_num        , '', 'id="page_num"');
			showXHTML_input('hidden', 'class_page'   , $class_page      , '', 'id="class_page"');
		showXHTML_form_E('');

	showXHTML_body_E();

	// �D�{�� end
?>

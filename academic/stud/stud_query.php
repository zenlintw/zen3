<?php
	/**
	 * 查詢人員資料
	 *
	 * 修改自 /academic/stud/stud_query.php
	 *
	 * @since   2004/02/25
	 * @author  ShenTing Lin
	 * @version $Id: stud_query.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/stud_account.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
    
	$sysSession->cur_func = '400400100';
    $sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 尋找人員的陣列
	$search_ary = array(
		'real'    => $MSG['realname'][$sysSession->lang],
		'account' => $MSG['username'][$sysSession->lang],
		'email'   => $MSG['email'][$sysSession->lang],
	);
        
        list($arr_school) = dbGetAll(sysDBname.'.CO_school', 'canReg_ext,canReg_fb_id,canReg_fb_secret', '`school_id`="' . $sysSession->school_id . '" ');
        
	//帳號綁定
	$bind_ary = array(
		'all'    => $MSG['co_bind_all'][$sysSession->lang],
		'fb' => $MSG['co_bind_fb'][$sysSession->lang],
		'none'   => $MSG['co_bind_none'][$sysSession->lang],
	);

	// 處理接收的資料 (Begin)
	$qStr  = '';
	$sqls  = '';

	$stud = trim($_GET['stud']);
	if (!empty($stud)) {
		$enc   = base64_decode($stud);
		$ids   = @mcrypt_decrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $enc, 'ecb');
		$str   = explode('~~', $ids);
		$sType = trim($str[0]);
		$sWord = trim($str[1]);
		$js_keyword = $sWord;
	} else if (isset($_POST['searchkey'])) {
		$sType = trim($_POST['searchkey']);
		$sWord = trim($_POST['keyword']);
		$js_keyword = $sWord;
	} else {
		$sType = 'all';
		$sWord = '';
		$js_keyword = '';
	}

	if (!empty($sType) && isset($sWord)) {
		switch ($sType) {
			case 'real'    :    // 姓名
				if (isset($sWord)){
					if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
						$sqls = sprintf('and CONCAT(ifnull(last_name," "), ifnull(first_name," ")) like "%%%s%%"', escape_LIKE_query_str(addslashes($sWord)));
					} else {
						$sqls = sprintf('and CONCAT(ifnull(first_name," "), " ", ifnull(last_name," ")) like "%%%s%%"', escape_LIKE_query_str(addslashes($sWord)));
					}
				}
				break;
			case 'account' :    // 帳號
				if (isset($sWord)){
					$sqls = 'AND Wua.username like "%' . escape_LIKE_query_str(addslashes($sWord)) . '%" ';
				}
				break;
			case 'email'   :    // E-mail
				if (isset($sWord)){
					$sqls = 'AND email like "%' . escape_LIKE_query_str(addslashes($sWord)) . '%" ';
				}
				break;
			default:
		}
		$qStr = $sType . '~~' . $sWord;
		$str  = $qStr;
		$enc  = @mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $str, 'ecb');
		$qStr = base64_encode($enc);
		$qStr = str_replace('+','%2B',$qStr);
		$qStr = rawurlencode($qStr);
	}

    $Bindtype = trim($_GET['bindtype']);
    
    if (!empty($Bindtype)) {
        $sBindtype = trim($_GET['bindtype']);
    }else if (isset($_POST['bindtype'])){
        $sBindtype = trim($_POST['bindtype']);
    }
    $co_sqls = 'LEFT';
    switch($sBindtype){
        default:
        case 'all':
            $sqls .= '';            
            break;
        case 'fb':
            $co_sqls = "INNER";
            $co_qStr = $sBindtype;
            break;
        case 'none':
            $co_sqls = "LEFT";
            $co_sqls2 = " AND Cfa.id IS NULL";
            $co_qStr = $sBindtype;
            break;
    }

	if ($_POST['page_num'] != ''){
		$page_num = intval($_POST['page_num']);
	}else if ($_GET['page_num'] != ''){
		$page_num = intval($_GET['page_num']);
	}

	if (empty($page_num)) $page_num = sysPostPerPage;
	$page_num = max(1, $page_num);

	// 目前正在第幾頁
	if($_GET['page_input'] != '') {
		$at_page = intval($_GET['page_input']);
	}

	// 查詢總筆數 & 總頁數
        list($total_num) = dbGetStSr('WM_user_account AS Wua '.$co_sqls.' JOIN '.sysDBname.'.CO_fb_account AS Cfa ON Wua.username = Cfa.username '.$co_sqls2, 'count(Wua.username)', 'Wua.username !="' . sysRootAccount . '" ' . $sqls, ADODB_FETCH_NUM);
	$total_num = intval($total_num);
	$js_total_page = ceil($total_num / $page_num);

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
	 * 顯示帳號
	 * @param string $val : 帳號
	 * @return string : 要顯示的文字
	 **/
	function showUser($val) {
		global $rvInclude;
		if ($rvInclude) {
			$user = '<a href="javascript:;" onclick="return false;" class="cssAnchor">' . $val . '</a>';
		} else {
			$user = $val;
		}
		return divMsg(130, $user, $val);
	}

	/**
	 * 顯示姓名
	 * @param string $f : 名字
	 * @param string $l : 姓名
	 * @return string : 要顯示的文字
	 **/
	function showName($f, $l) {
        // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
		return divMsg(200, checkRealname($f,$l));
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

	/**
	 * 顯示 E-mail
	 * @param string $val : E-mail
	 * @return string : 可點選的 E-mail
	 **/
	function showEmail($val) {
		$email = (empty($val)) ? '&nbsp;' : '<a href="mailto:' . $val . '" class="cssAnchor">' . $val . '</a>';
		return $email;
	}

	function showDetail($val) {
		global $sysSession, $MSG;
		$icon = '<img src="/theme/' . $sysSession->theme . '/academic/icon_folder.gif" width="16" height="16" border="0" alt="' . $MSG['btn_alt_detail'][$sysSession->lang] . '">';
		$detail = '<a href="javascript:;" onclick="return false;">' . $icon . '</a>';
		return $detail;
	}

	function showAccountBind($bind_fb){
		return ($bind_fb=='Y'?'<img src="/theme/default/academic/fb_logo.png" width="16" height="16" border="0" />':'');
	}
	// 處理顯示的資料 (End)

	$js = <<< BOF
	var stud                 = "{$qStr}";
	var co_qStr 		 = "{$co_qStr}";
	var page_num             = {$page_num};
	var keyword              = "{$js_keyword}";
	var sBindtype              = "{$sBindtype}";
	var total_page           = {$js_total_page};
	var MSG_page_exceed_org  = "{$MSG['go_page_input_error'][$sysSession->lang]}";
	var MSG_page_exceed      = MSG_page_exceed_org.replace('%TOTAL_PAGE%',total_page);;
	var MSG_page_input_error = "{$MSG['go_page_input_error2'][$sysSession->lang]}";

	function Page_Row(row){
		var obj = null;
		var val1 = '', val2 = '',val3;
		obj = document.getElementById("sType");
		if (obj != null) val1 = obj.value;

		obj = document.getElementById("sWord");
		if (obj != null) val2 = obj.value;

		if (val2 == "") val1 = 'all';
		obj = document.getElementById("queryFm");
		if (obj != null) {
			obj.searchkey.value = val1;
			obj.keyword.value   = keyword;
			obj.bindtype.value   = sBindtype;
			obj.page_num.value  = row;
			obj.submit();
		}
    }

	function queryUser() {
		var obj = null;
		var val1 = '', val2 = '',val3;
		obj = document.getElementById("sType");
		if (obj != null) val1 = obj.value;

		obj = document.getElementById("sWord");
		if (obj != null) val2 = obj.value;

		obj = document.getElementById("page_num");
		if (obj != null) val3 = obj.value;

		obj = document.getElementById("sBindtype");
		if (obj != null) val4 = obj.value;

		if (val2 == "") val1 = 'all';
		obj = document.getElementById("queryFm");
		if (obj != null) {
			obj.searchkey.value = val1;
			obj.keyword.value   = val2;
			obj.page_num.value  = val3;
			obj.bindtype.value  = val4;
			obj.submit();
		}
	}

	/**
	 * 顯示 基本資料
	 **/
	function showDetail(user, val) {
		var obj = document.getElementById("actFm");

		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.user.value = user;
		obj.msgtp.value = val;
		obj.submit();
	}

	function chgPageSort() {
		if (stud == "" && co_qStr == "") return "";
		return "&stud=" + stud +"&bindtype="+co_qStr;
	}

	function setUser(val) {
		var obj = window.opener;
		if (typeof(obj) == "object") {
			obj.setRoleUser(val);
		}
		window.close();
	}
	function btn_page(btnGo) {
		var str = location.pathname;
		var obj = btnGo.previousSibling.previousSibling;
		var tmp_page = obj.value;

        if (tmp_page.search(/^[1-9]\d*$/) == -1)
		{
			alert(MSG_page_input_error);
			obj.focus();
			return false;
        }

		tmp_page = parseInt(tmp_page);

		if(tmp_page > total_page){
			alert(MSG_page_exceed);
			obj.focus();
			return false;
		}

		if (stud != ""){
			str += "?stud=" + stud+'&page='+tmp_page+'&page_input='+tmp_page;
		}else{
			str += '?page='+tmp_page+'&page_input='+tmp_page;
		}
		location.replace(str);
	}
BOF;

    // SHOW_PHONE_UI 常數定義於 /mooc/academic/stud/stud_query.php
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1) {
        require_once(sysDocumentRoot . '/lang/people_manager.php');
        $smarty->assign('search_ary', $search_ary);
        $smarty->assign('sType', $sType);
        $smarty->assign('sWord', $sWord);
        $smarty->assign('page_num', $page_num);
        $smarty->assign('sBindtype', $sBindtype);

        $smarty->assign('inlineJS', $js);

        // SQL 查詢指令
        $current_page = intval($_POST['page']) == 0 ? '1' : intval($_POST['page']);
        $current_page = min($current_page, $js_total_page);

        $start = ($current_page-1)*$page_num;
        
        $tab    = '(SELECT Wua.`username`,Wua.`first_name`,Wua.`last_name`,Wua.`gender`,Wua.`email`, IF(Cfa.id IS NULL, \'N\', \'Y\') AS bind_fb FROM WM_user_account AS Wua LEFT JOIN '.sysDBname.'.CO_fb_account AS Cfa ON Wua.username = Cfa.username) AS WM_user_account';
        $fields = '*';
        $where = '`username`!="' . sysRootAccount . '" ' . str_replace("Wua.","",$sqls) . sprintf("LIMIT %d, %d",$start, $page_num);

        $datas = dbGetAll($tab, $fields, $where);
        $users = array();
        if (is_array($datas)) for($i=0, $size=count($datas); $i<$size; $i++) {
            $users[$i] = $datas[$i];
            $users[$i]['realname'] =  checkRealname($datas[$i]['first_name'], $datas[$i]['last_name']);
            $users[$i]['encUsername'] = sysNewEncode($users[$i]['username']);
        }

        $smarty->assign('datalist', $users);
        $smarty->assign('totalUserCount', $total_num);
        $smarty->assign('current_page', $current_page);

        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/site_header.tpl');
        $smarty->display('academic/stud/stud_query.tpl');
        $smarty->display('common/tiny_footer.tpl');
        exit;
    }

	showXHTML_head_B($MSG['query_people'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['query_people'][$sysSession->lang], 'queryTable');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1);
			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';
			
			

			// 工具列
			$toolbar = new toolbar();

			$toolbar->add_caption($MSG['search_keyword'][$sysSession->lang]);
			$toolbar->add_input('select', 'sType', $search_ary, $sType, 'id="sType" class="cssInput"');

			$toolbar->add_caption($MSG['inside'][$sysSession->lang]);
			$toolbar->add_input('text', 'sWord', htmlspecialchars(stripslashes($sWord)), '', 'placeholder="'.$MSG['keyword'][$sysSession->lang].'" id="sWord" size="20"  class="cssInput" onclick="this.value=\'\'"');
			$toolbar->add_caption($MSG['inside1'][$sysSession->lang] . '&nbsp;&nbsp;');

                        if($arr_school['canReg_ext'] === 'FB'){
                            $toolbar->add_caption($MSG['co_account_bind'][$sysSession->lang].'：');
                            $toolbar->add_input('select', 'sBindtype', $bind_ary, $sBindtype, 'id="sBindtype" class="cssInput"');
                        } else {
                            $toolbar->add_input('hidden', 'sBindtype', $bind_ary, 'all', 'id="sBindtype" class="cssInput"');
                        }

			$toolbar->add_input('button', '', $MSG['confirm'][$sysSession->lang], '', 'class="cssBtn" onclick="queryUser()"');
			$myTable->add_toolbar($toolbar);

			// 手動輸入第幾頁
			$toolbar = new toolbar();
			define('SET_PAGE_LINE', 6);
			$toolbar->add_caption($MSG['page_title'][$sysSession->lang]);
			$toolbar->add_input('text', 'page_input', $at_page, '', 'id="page_input" size="2"  class="cssInput"');
			$toolbar->add_caption($MSG['page_title2'][$sysSession->lang]);
			$toolbar->add_input('button', '', 'Go', '', 'class="cssBtn" onclick="btn_page(this);"');

			// 設定每頁可顯示幾筆
			define('SET_PAGE_LINE',8);
			$page_array = array(sysPostPerPage=> $MSG['default_amount'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);
			$toolbar->add_caption($MSG['every_page'][$sysSession->lang]);
			$toolbar->add_input('select', 'page_num', $page_array, $page_num, 'class="cssInput" onchange="Page_Row(this.value);" style="width: 50px"', '');

			$myTable->set_def_toolbar($toolbar);

			// 排序
			$myTable->add_sort('user'  , '`username` ASC', '`username` DESC');
			$myTable->add_sort('gender', '`gender` ASC'  , '`gender` DESC');
			$myTable->add_sort('email' , '`email` ASC'   , '`email` DESC');
			if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
				$myTable->add_sort('name'  , '`last_name` ASC, `first_name` ASC', '`last_name` DESC, `first_name` DESC');
			} else {
				$myTable->add_sort('name'  , '`first_name` ASC, `last_name` ASC', '`first_name` DESC, `last_name` DESC');
			}
			$myTable->set_sort(true, 'user', 'asc', 'chgPageSort()');

			// 欄位
			if (!isset($rvInclude)) $rvInclude= false;
			if ($rvInclude) $extra = ' onclick="setUser(\'%username\')"';
			$myTable->add_field($MSG['username'][$sysSession->lang], '', 'user'  , '%username'             , 'showUser'  , 'nowrap="noWrap"' . $extra);
			$myTable->add_field($MSG['realname'][$sysSession->lang], '', 'name'  , '%first_name %last_name', 'showName'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['gender'][$sysSession->lang]  , '', 'gender', '%gender'               , 'showGender', 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['email'][$sysSession->lang]   , '', 'email' , '%email'                , 'showEmail' , 'nowrap="noWrap"');
			if (!$rvInclude) {
				if($arr_school['canReg_ext'] === 'FB'){
                                    $myTable->add_field($MSG['co_account_bind'][$sysSession->lang] , '', '', '%bind_fb', 'showAccountBind', 'align="center" nowrap="noWrap"');
                                }
				$myTable->add_field($MSG['title56'][$sysSession->lang] , '', '', '%username', 'showDetail', 'align="center" nowrap="noWrap" onclick="showDetail(\'%username\',1);"');
				$myTable->add_field($MSG['title57'][$sysSession->lang] , '', '', '%username', 'showDetail', 'align="center" nowrap="noWrap" onclick="showDetail(\'%username\',2);"');
				$myTable->add_field($MSG['title58'][$sysSession->lang] , '', '', '%username', 'showDetail', 'align="center" nowrap="noWrap" onclick="showDetail(\'%username\',3);"');
			}

			// $myTable->set_page(true, 1, 10, 'chgPageSort()');
			$myTable->set_page(true, 1, $page_num, 'chgPageSort()');

			// SQL 查詢指令
			$tab    = 'WM_user_account AS Wua '.$co_sqls.' JOIN '.sysDBname.'.CO_fb_account AS Cfa ON Wua.username = Cfa.username '.$co_sqls2;
			$fields = 'Wua.`username`,Wua.`first_name`,Wua.`last_name`,Wua.`gender`,Wua.`email`, IF(Cfa.id IS NULL, \'N\', \'Y\') AS bind_fb';
			$where = 'Wua.username !="' . sysRootAccount . '" ' . $sqls;

			//  顯示幾筆

			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="stud_query.php" method="post" enctype="multipart/form-data" style="display:none"', 'queryFm');
			showXHTML_input('hidden', 'searchkey', $sType, '', 'id="searchkey"');
			showXHTML_input('hidden', 'keyword'  , $sWord, '', 'id="keyword"');
			showXHTML_input('hidden', 'page_num'  , $page_num, '', 'id="page_num"');
			showXHTML_input('hidden', 'bindtype'  , $sBindtype, '', 'id="bindtype"');
		showXHTML_form_E('');

		//  學員資訊
        showXHTML_form_B('action="stud_query1.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
    	    showXHTML_input('hidden', 'msgtp', '', '', '');
    	    showXHTML_input('hidden', 'user', '', '', '');
        showXHTML_form_E();
	showXHTML_body_E();
?>

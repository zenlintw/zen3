<?php
	/**
     * 學習路徑備份還原
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     *          則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     *          照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Edi Chen <edi@sun.net.tw>
     * @copyright   2000-2007 SunNet Tech. INC.
     * @version     CVS: $Id: cour_path_recover.php,v 1.1 2010/02/24 02:40:23 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2007-01-02
     */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');

	$cid = trim($_GET['cid']);
	$recover_cid = sysNewDecode($cid);

// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end

// {{{ 變數宣告 begin
    $page_num = $_GET['page_num']  ? intval($_GET['page_num'])  : sysPostPerPage;	// 頁數控制
    $cour_path_reload = $_GET['cour_path_reload'] == 'true' ? 'true' : 'false';	// 是否需要重新載入學習路徑

// }}} 變數宣告 end

// {{{ 函數宣告 begin

	/**
	 * 顯示節點數
	 * @param string $content 學習路徑內容
	 */
	function showItemCnt($content) {
		if (is_string($content) && strlen($content))
			return substr_count($content, '</item>');
		else
			return 0;
	}

	/**
	 * 顯示checkbox
	 * @param int $sid serial id
	 */
	function showCheckBox($sid) {
		return '<div align="center"><input type="checkbox" name="sid[]" value="' . sysNewEncode($sid) . '" onclick="chgCheckbox(); event.cancelBubble=true;"></div>';
	}

	/**
	 * 顯示功能
	 * @param int $sid serial id
	 */
	function showFunc($sid) {
		global $MSG, $sysSession;
		$sid = sysNewEncode($sid);
		return '<input type="button" value="'.$MSG['preview'][$sysSession->lang].'" class="cssBtn" onclick="preview(\''.str_replace('+', '%252B', $sid).'\');">&nbsp;&nbsp;'.
		       '<input type="button" value="'.$MSG['recover'][$sysSession->lang].'" class="cssBtn" onclick="recover(\''.$sid.'\');">';
	}

// }}} 函數宣告 end

// {{{ 主程式 begin
	$js = <<< EOB
	var MSG_SELECT_ALL    = "{$MSG['toolbtm15'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['toolbtm16'][$sysSession->lang]}";
	var MSG_NO_SELECT	  = "{$MSG['no_select'][$sysSession->lang]}";

	var cour_path_reload  = "{$cour_path_reload}";
	var isClosed		  = true;
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
		btn2.value = btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

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
			if (nodes[i].checked == false) {
				bol = false;
				break;
			}
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;
		if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
  	}

	/**
	 * 改變每頁顯示筆數時觸發的funciton
	 */
	function Page_Row(row){
		isClosed = false;
		location.replace('cour_path_recover.php?page_num=' + row + '&cour_path_reload={$cour_path_reload}&cid={$cid}');
  	}

  	/**
  	 * 改變頁數時觸發的function(跳頁數,上下頁,首末頁)
  	 */
  	function chgPage() {
  		isClosed = false;
		return '&page_num={$page_num}&cour_path_reload={$cour_path_reload}&cid={$cid}';
  	}

  	/**
  	 * 刪除備份的學習路徑
  	 */
  	function cour_path_del() {
  		isClosed = false;
  		if (!confirm("{$MSG['msg_delete_confirm'][$sysSession->lang]}")) return;

  		// 先檢查是否有點選了checkbox
  		var nodes = document.getElementsByTagName("input");
  		if ((nodes == null) || (nodes.length <= 0)) return false;
  		var hasSel = false;
  		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) {
				hasSel = true;
				break;
			}
		}

		if (!hasSel) {
			alert(MSG_NO_SELECT);
			return;
		}

  		var obj = document.getElementById('tabFm');
  		if (obj) {
  			obj.func.value = 'del';
  			obj.submit();
  		}
  	}

  	/**
  	 * 還原所選擇的學習路徑
  	 */
  	function recover(sid) {
  		isClosed = false;
  		if (!confirm("{$MSG['msg_recover_confirm'][$sysSession->lang]}")) return;
		var obj = document.getElementById('tabFm');
  		if (obj) {
  			obj.func.value = 'recover';
  			obj.rid.value  = sid;
  			obj.submit();
  		}
  	}

  	function preview(sid) {
  		window.open('cour_path_preview.php?sid='+sid, '', 'top=150px,left=200px,width=360px,height=450px,toolbar=0,menubar=0,scrollbars=1,resizable=1,status=0');
  	}

  	window.onunload = function() {
		if (cour_path_reload == 'true' && isClosed)	// 學習路徑已變更, 需要重新載入
			opener.parent.c_sysbar.chgMenuItem('SYS_02_02_003');
  	};
EOB;

	showXHTML_head_B('');
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array( array($MSG['learn_path'][$sysSession->lang].$MSG['toolbtm18'][$sysSession->lang], 'tabsSet1',  '') );
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'tabFm', '', 'method="post" enctype="multipart/form-data" style="display:inline" action="cour_path_recover1.php"', false, false);
			showXHTML_input('hidden', 'func', '', '', ''); // del or recover
			showXHTML_input('hidden', 'rid' , '', '', ''); // if recover, set recover id
			showXHTML_input('hidden', 'cid' , $cid, '', '');
			$myTable = new table();
			$myTable->extra = 'width="550" border="0" cellspacing="1" cellpadding="3" id="tb_sel" class="cssTable"';

			// 全選全消的按鈕
			$myTable->set_select_btn(true, 'btnSel', $MSG['toolbtm15'][$sysSession->lang], 'onclick="selfunc()"');

			// 刪除備份與關閉的工具列
			$toolbar1 = new toolbar();
			$toolbar1->add_caption('&nbsp;');
			$toolbar1->add_input('button', '', $MSG['toolbtm04'][$sysSession->lang]	, '', 'class="cssBtn" onclick="cour_path_del();"');
			$toolbar1->add_input('button', '', $MSG['close'][$sysSession->lang] 	, '', 'class="cssBtn" onclick="window.close();"');
			// $toolbar1->add_caption('&nbsp;&nbsp;');
			$myTable->set_def_toolbar($toolbar1);

			// 標題與資料
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

			$myTable->add_field($ck1, $MSG['select_all_msg'][$sysSession->lang], '', '%serial'     , 'showCheckBox', 'nowrap="noWrap"');
			$myTable->add_field($MSG['msg_datetime2'][$sysSession->lang]    , '', '', '%update_time', ''            , 'nowrap="noWrap"');
			$myTable->add_field($MSG['item_cnt'][$sysSession->lang]        , '', '', '%content'    , 'showItemCnt' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['modifier'][$sysSession->lang]        , '', '', '%username'   , ''            , 'nowrap="noWrap"');
			$myTable->add_field($MSG['function'][$sysSession->lang]        , '', '', '%serial'     , 'showFunc'    , 'nowrap="noWrap"');
			$myTable->set_page(true, 1, $page_num, 'chgPage();');

			// $myTable->set_sqls('WM_term_path', 'serial, content, username, update_time', 'course_id = ' . $sysSession->course_id . ' order by serial desc');
			$myTable->set_sqls('WM_term_path', 'serial, content, username, update_time', 'course_id = ' . $recover_cid . ' order by serial desc');
			$myTable->display['page_num'] = true;
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
// }}} 主程式 end
?>
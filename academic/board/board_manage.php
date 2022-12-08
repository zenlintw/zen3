<?php
	/**
     * 目  的 : 討論版管理
     *
     * @since   2005/08/02
     * @author  Edi Chen
     * @version $Id: board_manage.php,v 1.1 2010/02/24 02:38:13 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/board_manage.php');
	require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	// 議題狀態
	$titleStatus = array(
		'disable' => $MSG['type_disable'][$sysSession->lang],
		'open'    => $MSG['type_open'][$sysSession->lang],
		'taonly'  => $MSG['type_taonly'][$sysSession->lang]
	);
	
	// 可見與否
	$arr_msg_visible = array(
		'visible' => $MSG['title_visible'][$sysSession->lang],
		'hidden'  => $MSG['title_hidden'][$sysSession->lang]
	);
	
	chkSchoolId('WM_news_subject');
	// 取得系統預設六個討論版的board_id,並且判斷是否存在WM_news_subject, WM_chat_records, WM_term_subject中
	$sqls = "select `board_id`, `type` from WM_news_subject where `type` in ('news','teacher', 'faq', 'suggest', 'comment') union select `board_id`, `type` from WM_chat_records where `type` = 'school' and `owner_id` = $sysSession->school_id";
	$sys_boards = $sysConn->GetAssoc($sqls);
	$types = array('news', 'faq', 'comment', 'suggest', 'school', 'teacher');
	foreach ($types as $value)
		if (!$sys_boards || !in_array($value, $sys_boards)) {
			addNewsBoards($MSG[$value], $result, $value);
			if ($result['board_id'])
				$sys_boards[$result['board_id']] = $value;
		}

	$sys_board_ids = array_keys($sys_boards);

	$sqls = 'select `node_id`,`board_id` from WM_term_subject where `board_id` in ('. implode(',', $sys_board_ids) .') and course_id =' . $sysSession->school_id;
	$ids = $sysConn->GetAssoc($sqls);
	$ids = array_values($ids);
	foreach ($sys_board_ids as $bid)
		if (!in_array($bid, $ids)) dbNew('WM_term_subject', 'course_id, board_id', "$sysSession->school_id, $bid");

	/**
	 * 目的 : 取得已經出現在sysbar上的討論版
	 * personal.xml 	=> 個人區
	 * system.xml   	=> 校園廣場
	 * adm_class		=> 導師辦公室
	 * course.xml		=> 教室
	 * adm_course.xml	=> 教師辦公室
	 */
	function getSysbarBoard() {
		global $sysSession;
		$sysbar_bid = array();
		$sys_xml = array('personal.xml', 'system.xml', 'adm_class.xml', 'course.xml', 'adm_course.xml');
		$dir = sysDocumentRoot . "/base/{$sysSession->school_id}/system";
		if (!@is_dir($dir)) @mkdir($dir);
		foreach ($sys_xml as $xml) {
			$filename = sysDocumentRoot . "/base/{$sysSession->school_id}/system/{$xml}";
			if (!empty($filename) && @is_file($filename)) {
				@$xmldoc = domxml_open_file($filename);
				if (!$xmldoc) return;
				$ctx = xpath_new_context($xmldoc);
				$nodes = $ctx->xpath_eval('//item/href[@kind="6"]'); // 6 為議題討論版
				if (is_array($nodes->nodeset) && count($nodes->nodeset))
					foreach($nodes->nodeset as $node)
						$sysbar_bid[] = $node->get_content();
			}
		}
		return $sysbar_bid;
	}

	$sysbar_bid = getSysbarBoard();


	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = $caption;
		return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
	}

	function showSubject($val, $act, $nid) {
		global $sysSession, $sys_boards, $sysbar_bid;
		$lang = getCaption(stripslashes($val));
		$bid = sysEncode($act);
		$type = $sys_boards[$act] ? $sys_boards[$act] : (in_array($act, $sysbar_bid) ? 'sysbar' : 'others');
		return divMsg(130, '<input type="hidden" id="nid_'.$nid.'" value="'.$type.'"><a href="javascript:void(null);" onclick="return false;" class="cssAnchor">' . htmlspecialchars_decode($lang[$sysSession->lang]) . '</a>', strip_tags(htmlspecialchars_decode($lang[$sysSession->lang])) . '" onclick="goBoard(\'' . $bid . '\', \'' . $type . '\')');
	}

	function showStatus($val) {
		global $titleStatus;
		return divMsg(30, $titleStatus[$val]);
	}
	
	function showVisiblity($val) {
		global $arr_msg_visible;
		return $arr_msg_visible[$val];
	}

	function showOpentime($val) {
		global $sysSession, $sysConn, $MSG;
		$time = $sysConn->UnixTimeStamp($val);
		$data = intval($val);
		$msg = $MSG['from2'][$sysSession->lang] . ( (empty($data)) ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', $time));
		return divMsg(150, $msg);
	}

	function showClosetime($val) {
		global $sysSession, $sysConn, $MSG;
		$time = $sysConn->UnixTimeStamp($val);
		$data = intval($val);
		$msg = $MSG['to2'][$sysSession->lang] . ( (empty($data)) ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', $time));
		return divMsg(150, $msg);
	}

	function showSharetime($val) {
		global $sysSession, $sysConn, $MSG;
		$time = $sysConn->UnixTimeStamp($val);
		$data = intval($val);
		return (empty($data)) ? divMsg(130, $MSG['unlimit'][$sysSession->lang]) : divMsg(130, date('Y-m-d H:i', $time));
	}

	function showAction($nid, $bid) {
		global $sys_boards, $MSG,$sysSession;
		$type = $sys_boards[$bid] ? $sys_boards[$bid] : 'others';
		return '<input type="button" value="'.$MSG['btm_modify'][$sysSession->lang].'" class="cssBtn" onclick="setBoard(\''.$nid.'\',\''.$type.'\')"';
	}

	$js = <<< EOB
	var MSG_SELECT_ALL        = "{$MSG['toolbtm15'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL     = "{$MSG['toolbtm16'][$sysSession->lang]}";
	var MSG_SysError          = "{$MSG['system_error'][$sysSession->lang]}";
	var MSG_BAD_BOARD_ID      = "{$MSG['msg_bad_board_id'][$sysSession->lang]}";
	var MSG_BAD_BOARD_RANGE   = "{$MSG['msg_bad_board_range'][$sysSession->lang]}";
	var MSG_BOARD_NOTOPEN     = "{$MSG['msg_board_notopen'][$sysSession->lang]}";
	var MSG_BOARD_CLOSE       = "{$MSG['msg_board_closed'][$sysSession->lang]}";
	var MSG_BOARD_DISABLE     = "{$MSG['msg_board_disable'][$sysSession->lang]}";
	var MSG_BOARD_TAONLY      = "{$MSG['msg_board_taonly'][$sysSession->lang]}";

	var xmlHttp = XmlHttp.create();
	var xmlDocs = XmlDocument.create();
	var xmlVars = XmlDocument.create();

	/**
	 * 設定議題討論版
	 * @param string val : 議題討論版編號
	 **/
	function setBoard(val, type) {
		var obj = document.getElementById("editFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.nid.value = val;
		obj.type.value = type;
		obj.submit();
	}

	/**
	 * 切換全選或全消的 checkbox
	 **/
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

		select_func('', obj.checked);
	}

	/**
	 * 取得勾選的議題編號
	 * @return array nid
	 **/
	function getCkVal() {
		var nid = new Array();
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return nid;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) {
				nid[nid.length] = nodes[i].value;
			}
		}
		return nid;
	}

	/**
	 * 刪除勾選的議題
	 **/
	function delBoard() {
		var nid = new Array();
		var obj = null;
		var val = "";
		nid = getCkVal();
		if (nid.length <= 0) {
			alert("{$MSG['msg_delete_select'][$sysSession->lang]}");
			return false;
		}

		// 檢查是否為系統預設之討論版
		var msg = '';
		for(i = 0; i < nid.length; i++) {
			eval("obj = document.getElementById('nid_' + nid[i])");
			if (obj.value == 'news' || obj.value == 'faq' || obj.value == 'comment' || obj.value == 'suggest' || obj.value == 'teacher' || obj.value == 'school') {
				msg += "< " + obj.parentNode.title + "> ";
			}
		}
		if (msg != '') {
			alert(msg + "{$MSG['msg_defBid'][$sysSession->lang]}");
			return;
		}

		// 檢查是否為已經在sysbar上的討論版
		var msg = '';
		for(i = 0; i < nid.length; i++) {
			eval("obj = document.getElementById('nid_' + nid[i])");
			if (obj.value == 'sysbar') {
				msg += "< " + obj.parentNode.title + "> ";
			}
		}
		if (msg != '') {
			alert(msg + "{$MSG['msg_sysbarBid'][$sysSession->lang]}");
			return;
		}

		if (!confirm("{$MSG['msg_delete_sure'][$sysSession->lang]}")) return false;
		obj = document.getElementById("delFm");
		val = nid.toString();
		if ((obj != null) && (val != "")) {
			obj.nids.value = val;
			obj.submit();
		}
	}

	/**
	 * 交換節點
	 * @param object node1 : 節點
	 * @param object node2 : 節點
	 **/
	function swapNode(node1, node2) {
		var pnode1 = null, pnode2 = null, tnode1 = null, tnode2 = null;
		var attr1 = null, attr2 = null;

		if ((typeof(node1) != "object") || (node1 == null)
			|| (typeof(node2) != "object") || (node2 == null))
		{
			return false;
		}
		if (isIE && (BVER == "5.0")) {
			var style1 = node1.className;
			var style2 = node2.className;
			node1.swapNode(node2);
			node1.className = style2;
			node2.className = style1;
		} else {
			pnode1 = node1.parentNode;
			pnode2 = node2.parentNode;
			tnode1 = node1.cloneNode(true);
			tnode2 = node2.cloneNode(true);
			tnode1.className = node2.className;
			tnode2.className = node1.className;
			pnode1.replaceChild(tnode2, node1);
			pnode2.replaceChild(tnode1, node2);
		}
		return true;
	}

	/**
	 * 排序
	 * @param integer val :
	 *     0 : 向上
	 *     1 : 向下
	 * @return
	 **/
	function permute(val) {
		var node1 = null, node2 = null;
		var pnode = null;
		var nid = new Array();
		var idx = 0;
		nid = getCkVal();
		if (nid.length <= 0) {
			alert("{$MSG['msg_permute_select'][$sysSession->lang]}");
			return false;
		}
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		nid = new Array();
		if (val == 0) {
			for (var i = 0; i < nodes.length; i++) {
				if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
				if (nodes[i].checked) {
					node1 = nodes[i].parentNode.parentNode;
					if (node1.rowIndex == 2) {
						alert("{$MSG['msg_not_move_up'][$sysSession->lang]}");
						return false;
					}
					nid[nid.length] = idx;
					swapNode(node1, node1.parentNode.rows[node1.rowIndex - 1]);
				}
				idx = i;
			}
		} else {
			for (var i = nodes.length - 1; i >= 0 ; i--) {
				if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
				if (nodes[i].checked) {
					node1 = nodes[i].parentNode.parentNode;
					if (node1.rowIndex == node1.parentNode.rows.length - 2) {
						alert("{$MSG['msg_not_move_down'][$sysSession->lang]}");
						return false;
					}
					nid[nid.length] = idx;
					swapNode(node1, node1.parentNode.rows[node1.rowIndex + 1]);
				}
				idx = i;
			}
		}
		if (isIE) {
			for (var i = 0; i < nid.length; i++) {
				nodes[nid[i]].checked = true;
			}
		}
	}

	/**
	 * 儲存順序
	 **/
	var resWin = null;
	function savePermute() {
		var obj = null;
		var nodes = document.getElementsByTagName("input");
		var ary = new Array();
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			ary[ary.length] = nodes[i].value;
		}
		obj = document.getElementById("nodeids");
		obj.value = ary.toString();
		// resWin = window.open("about:blank", "resWin", "width=150,height=120,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbar=0,resizable=1");
		OpenNamedWin("about:blank", "resWin", 130,80);
		obj = document.getElementById("muteFm");
		obj.submit();
	}

	function goBoard(val, type) {
		var txt = "";
		var res = false;

		if (val == "") return false;

		txt = "<manifest><board_id>" + val + "</board_id></manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) {
			alert(MSG_SysError);
			return;
		}

		xmlHttp.open("POST", "goto_board.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		txt = xmlHttp.responseText;
		if (txt != "") {
			switch (txt) {
				case "Bad_ID"       : alert(MSG_BAD_BOARD_ID)   ; break;
				case "Bad_Range"    : alert(MSG_BAD_BOARD_RANGE); break;
				case "board_notopen": alert(MSG_BOARD_NOTOPEN)  ; break;
				case "board_close"  : alert(MSG_BOARD_CLOSE)    ; break;
				case "board_disable": alert(MSG_BOARD_DISABLE)  ; break;
				case "board_taonly" : alert(MSG_BOARD_TAONLY)   ; break;
				default:
			}
			return;
		}

		switch (type) {
			case 'news':    parent.main.location.replace('/learn/news/index_news.php');    break;
			case 'faq':     parent.main.location.replace('/learn/news/index_faq.php');     break;
			case 'comment': parent.main.location.replace('/learn/news/index_comment.php'); break;
			case 'suggest': parent.main.location.replace('/learn/news/index_suggest.php'); break;
			case 'teacher': parent.main.location.replace('/learn/news/index_teacher.php'); break;
			case 'school':  parent.main.location.replace('/learn/chatrec/school.php');     break;
			default:        parent.main.location.replace('/forum/index.php');
		}
	}
	
EOB;

	// 開始頁面展現
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
			$ary = array(
					array($MSG['board_manage_title'][$sysSession->lang], 'tabsSet1',  '')
				);
			echo '<div align="center">';
			showXHTML_tabFrame_B($ary, 1, 'muteFm', '', 'action="board_permute_save.php" target="resWin" method="post" enctype="multipart/form-data" style="display:inline"');
			showXHTML_input('hidden', 'nodeids', '', '', 'id="nodeids"');

			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';

			// 工具列
			$toolbar = new toolbar();
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', '', $MSG['btm_add'][$sysSession->lang] , '', 'class="cssBtn" onclick="setBoard(\'\',\'\')"');
			$toolbar->add_input('button', '', $MSG['btm_rm'][$sysSession->lang]  , '', 'class="cssBtn" onclick="delBoard()"');
			$toolbar->add_caption('&nbsp;&nbsp;');
			$myTable->set_def_toolbar($toolbar);
			// 全選全消的按鈕
			$myTable->set_select_btn(true, 'btnSel', $MSG['toolbtm15'][$sysSession->lang], 'onclick="selfunc()"');

			// 資料
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

			$ck2 = new toolbar();
			$ck2->add_input('checkbox', 'node_id[]', '%0', '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
            $ck2->add_input('hidden'  , 'pmutes[]' , '%4', '', '');

			$btns = new toolbar();
			$btns->add_input('button', '', $MSG['btm_modify'][$sysSession->lang], '', 'class="cssBtn" onclick="setBoard(\'%0\')"');
			$myTable->add_field($ck1                                       , $MSG['select_all_msg'][$sysSession->lang], '', $ck2, ''             , 'width="16" align="center"');
			$myTable->add_field($MSG['title_subject'][$sysSession->lang]   , '', '', '%5 %1 %0' , 'showSubject'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_open_time'][$sysSession->lang] , '', '', '%6'    , 'showOpentime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_close_time'][$sysSession->lang], '', '', '%7'    , 'showClosetime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_share_time'][$sysSession->lang], '', '', '%8'    , 'showSharetime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_status'][$sysSession->lang]    , '', '', '%2'    , 'showStatus'   , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['title_visible'][$sysSession->lang]   , '', '', '%3'    , 'showVisiblity', 'align="center"' );
			// $myTable->add_field($MSG['title_action'][$sysSession->lang]    , '', '', $btns   , ''             , 'align="center"' );
			$myTable->add_field($MSG['title_action'][$sysSession->lang]    , '', '', '%0 %1' , 'showAction'  , 'align="center"' );

			$tab    = 'WM_term_subject left join WM_bbs_boards on WM_bbs_boards.board_id = WM_term_subject.board_id';
			$fields = '`node_id`, `WM_term_subject`.`board_id`, `state`, `visibility`, `permute`, `bname`, `open_time`, `close_time`, `share_time`';
			$where  = "`course_id`={$sysSession->school_id} order by `permute`, `WM_term_subject`.`board_id`";
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="board_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editFm');
			showXHTML_input('hidden', 'nid', '', '', '');
			showXHTML_input('hidden', 'type', '', '', '');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'setBoard' . $_COOKIE['idx']), '', '');
		showXHTML_form_E();

		showXHTML_form_B('action="board_delete.php" method="post" enctype="multipart/form-data" style="display:none"', 'delFm');
			showXHTML_input('hidden', 'nids', '', '', '');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'delBoard' . $_COOKIE['idx']), '', '');
		showXHTML_form_E();
	showXHTML_body_E();
?>

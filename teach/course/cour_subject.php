<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *      Programmer: Wiseguy Liang                                                                 *
	 *      Creation  : 2003/08/10                                                                    *
	 *      work for  :                                                                               *
	 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
    require_once(sysDocumentRoot . '/lib/lstable.php');
    require_once(sysDocumentRoot . '/teach/course/cour_lib.php');
    require_once(sysDocumentRoot . '/lang/teach_course.php');
    require_once(sysDocumentRoot . '/lang/forum.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '900100300';
	$sysSession->board_ownerid = $sysSession->course_id;
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = $caption;
		return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
	}

    function showSubject($val, $act) {
        global $sysSession;
        $lang = getCaption($val);
        $bid = sysEncode($act);
        return divMsg(130, '<a href="javascript:void(null);" onclick="return false;" class="cssAnchor">' . htmlspecialchars_decode($lang[$sysSession->lang]) . '</a>', strip_tags(htmlspecialchars_decode($lang[$sysSession->lang])) . '" onclick="co_goBoard(\'' . $bid . '\')');
    }

	function showStatus($val) {
		global $titleStatus;
		if ($val == 'public') $val = 'open'; 
        return divMsg(120, $titleStatus[$val]);
	}

	function showVisiblity($val) {
		global $sysSession, $MSG;
		return $val == 'visible' ? $MSG['title_visible'][$sysSession->lang] : $MSG['title_hidden'][$sysSession->lang];
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
    
    $RS = dbGetStMr('WM_term_course as A left join WM_term_subject as B on A.course_id=B.course_id',
                    'B.board_id,B.node_id',
                    "A.course_id={$sysSession->course_id} and FIND_IN_SET(B.board_id,CONCAT(A.discuss,',',A.bulletin)) order by B.board_id",
                    ADODB_FETCH_ASSOC);
    $defBid = '';
    while (!$RS->EOF) {
        $defBid .= $RS->fields['node_id'].',';
        $RS->MoveNext();
    }
    $defBid = preg_replace("/\,$/i", "", $defBid);;

    $js = <<< EOB
    var MSG_SELECT_ALL    = "{$MSG['toolbtm15'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL = "{$MSG['toolbtm16'][$sysSession->lang]}";
    var MsgInputFile   = "{$MSG['input_file'][$sysSession->lang]}";
    var defBid = "{$defBid}";

	/**
	 * 設定議題討論版
	 * @param string val : 議題討論版編號
	 **/
	function setSubject(val) {
		var obj = document.getElementById("editFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.nid.value = val;
		obj.submit();
	}

	/**
	 * 切換全選或全消的 checkbox
	 **/
	function chgCheckbox() {
		var bol   = true;
		var nodes = document.getElementsByTagName("input");
		var obj   = document.getElementById("ck");
		var btn1  = document.getElementById("btnSel1");
		var btn2  = document.getElementById("btnSel2");
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
	function delSubject() {
		var nid = new Array();
		var obj = null;
		var val = "";
		nid = getCkVal();
		if (nid.length <= 0) {
			alert("{$MSG['msg_delete_select'][$sysSession->lang]}");
			return false;
		}
		if (!confirm("{$MSG['msg_delete_sure'][$sysSession->lang]}")) return false;
		obj = document.getElementById("delFm");
		val = nid.toString();
		if (!chkDelSubject(defBid, val)){
			alert("{$MSG['msg_defBid'][$sysSession->lang]}");
			return false;
		}
		if ((obj != null) && (val != "")) {
			obj.nids.value = val;
			obj.submit();
		}
	}

	/**
	 * 檢查所勾選的版是否為[課程討論],[課程公告]
	 **/
	function chkDelSubject(dfBid, ckBid) {
		if (dfBid == null || dfBid == '') return true;
		bAry = dfBid.split(',');
		ckAry = ckBid.split(',');
		if ((bAry == null) || (bAry.length <= 0)) return true;
		for (var i = 0; i < ckAry.length; i++)
			for (var j = 0; j < bAry.length; j++)
				if (ckAry[i] == bAry[j])
					return false;
		return true;
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
		var obj   = null;
		var nodes = document.getElementsByTagName("input");
		var ary   = new Array();
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			ary[ary.length] = nodes[i].value;
		}
		obj = document.getElementById("nodeids");
		obj.value = ary.toString();
		// resWin = window.open("about:blank", "resWin", "width=150,height=120,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbar=0,resizable=1");
        // #47333 Chrome  提示視窗太小看不到文字-->加大視窗
        OpenNamedWin("about:blank", "resWin", 230,200);
		obj = document.getElementById("muteFm");
		obj.submit();
	}

	function goBoard(val) {
		if ((typeof(parent.c_sysbar) == "object") && (typeof(parent.c_sysbar.goBoard) == "function")) {
			parent.c_sysbar.goBoard(val);
		}
	}

    function goto_group() {
        window.location.replace("cour_group_subject.php");
    }
    // custom
    function co_goBoard(val) {
        obj = document.getElementById("goBd");
        obj.xbid.value = val;
        obj.submit();
    }

    
function showExportAllDlg(val) {
        OpenNamedWin("/forum/export_all_dlg.php?id="+val, val, 300, 290);
}

function showImportAllDlg() {
        showDialog("import_all_dlg.php",true,window, true, 0, 0, '290px', '290px', 'status=0');
        // OpenNamedWin("import_all_dlg.php", "import_all", 400, 150);
}

function displayImportAllUI(val) {

    displayHiddenUI("importall_ui", "btnImportAll", val)
}

/**
 * show or hidden Layer UI
 * @pram string ui_name : layer name
 * @pram string on_btn  : trigger button
 * @pram boolean val :
 *               true  : show
 *               false : hidden
 * @return none
 **/
function displayHiddenUI(ui_name, on_btn, val) {
	var obj = document.getElementById(ui_name);
	var sclTop = 0, oHeight = 0;
	if (obj == null) return false;
	
		var import_id = document.getElementById("import_id");
		import_id.value = val;
		sclTop = parseInt(document.body.scrollTop);
		oHeight = (isMZ) ? parseInt(window.innerHeight) : document.body.offsetHeight;
		if ((parseInt(obj.style.top) < sclTop) ||
			(parseInt(obj.style.top) > (sclTop + oHeight))) {
			obj.style.top = sclTop + 50;
		}
	layerAction(ui_name, val);
}

/**
 * Import UI user presses OK or Cancel
 * @param boolean val :
 *               true  : user press OK
 *               false : user press Cancel
 * @param int type :
 *                   1 : import
 *                   2 : import all
 * @return none
 **/
function OnImpOK(frm, type) {
	var v = frm.file_import.value;
	var len = v.length;
	if(v=='') {
		alert(MsgInputFile);
		frm.file_import.focus();
		return false;
	}  else if(type == 1 && v.toLowerCase().lastIndexOf(".tgz") != (len-4) && v.toLowerCase().lastIndexOf(".tar.gz") != (len-7)) {
		alert(MsgExt1);
		frm.file_import.focus();
		return false;
	} else if (type == 2 && v.toLowerCase().lastIndexOf(".zip") != (len-4)) {
		alert(MsgExt2);
		frm.file_import.focus();
		return false;
	} else {
		frm.submit();
		return true;
	}
}

function OnImportAllButton(val) {
	var f = document.getElementById("form_importall");
	if(val) {
		if(OnImpOK(f, 2))	displayImportAllUI(false);
	} else {
		displayImportAllUI(false);
	}
}

	chkBrowser();
EOB;
    // SHOW_PHONE_UI 常數定義於 /mooc/teach/course/cour_subject.php
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1) {
        $tab    = 'WM_term_subject left join WM_bbs_boards on WM_bbs_boards.board_id = WM_term_subject.board_id';
        $fields = '`node_id`, `WM_term_subject`.`board_id`, `state`, `visibility`, `permute`, `bname`, `open_time`, `close_time`, `share_time`';
        $where  = "`course_id`={$sysSession->course_id} order by `permute`";
        $datas = dbGetAll($tab, $fields, $where, ADODB_FETCH_ASSOC);
        for($i=0, $size=count($datas); $i<$size; $i++) {
            if ($datas[$i]['state']== 'public') $datas[$i]['state'] = 'open';
            $datas[$i]['state'] = $titleStatus[$datas[$i]['state']];
        }

        // assign
        $smarty->assign('datalist', $datas);
        $smarty->assign('ticket', md5(sysTicketSeed . 'setBoard' . $_COOKIE['idx']));
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/course_header.tpl');
        $smarty->display('phone/teach/course/cour_subject.tpl');
        $smarty->display('common/tiny_footer.tpl');
        exit;
    }
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
        showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
			$ary = array(
					array($MSG['subject_title'][$sysSession->lang], 'tabsSet1',  ''),
					array($MSG['subject_title1'][$sysSession->lang], 'tabsSet2',  "goto_group();")
				);
			echo '<div align="center">';
                        $display_css['table'] = 'width="1200"';
			showXHTML_tabFrame_B($ary, 1, 'muteFm', '', 'action="cour_permute_save.php" target="resWin" method="post" enctype="multipart/form-data" style="display:inline"', null, null, $display_css);
			showXHTML_input('hidden', 'nodeids', '', '', 'id="nodeids"');

			$myTable = new table();
			$myTable->extra = 'width="1000" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';

			// 工具列
			$toolbar = new toolbar();
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', '', $MSG['btm_add'][$sysSession->lang] , '', 'class="cssBtn" onclick="setSubject(\'\')"');
			$toolbar->add_input('button', '', $MSG['btm_rm'][$sysSession->lang]  , '', 'class="cssBtn" onclick="delSubject()"');
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', '', $MSG['btn_up'][$sysSession->lang]  , '', 'class="cssBtn" onclick="permute(0);"');
			$toolbar->add_input('button', '', $MSG['btn_down'][$sysSession->lang], '', 'class="cssBtn" onclick="permute(1);"');
			$toolbar->add_input('button', '', $MSG['btm_save_permute'][$sysSession->lang], '', 'class="cssBtn" onclick="savePermute();"');
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
            $btns->add_input('button', '', $MSG['btm_modify'][$sysSession->lang], '', 'class="cssBtn" onclick="setSubject(\'%0\')"');
            $btns->add_input('button', '', $MSG['btm_export'][$sysSession->lang], '', 'class="cssBtn" onclick="showExportAllDlg(\'%1\')"');
            $btns->add_input('button', '', $MSG['btm_import'][$sysSession->lang], '', 'class="cssBtn" onclick="displayImportAllUI(\'%1\')"');

			$myTable->add_field($ck1                                       , $MSG['select_all_msg'][$sysSession->lang], '', $ck2, ''             , 'width="20" align="center"');
			$myTable->add_field($MSG['title_subject'][$sysSession->lang]   , '', '', '%5 %1' , 'showSubject'  , '');
			$myTable->add_field($MSG['title_open_time'][$sysSession->lang] , '', '', '%6'    , 'showOpentime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_close_time'][$sysSession->lang], '', '', '%7'    , 'showClosetime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_share_time'][$sysSession->lang], '', '', '%8'    , 'showSharetime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_status'][$sysSession->lang]    , '', '', '%2'    , 'showStatus'   , 'align="center"');
			$myTable->add_field($MSG['title_visible'][$sysSession->lang]   , '', '', '%3'    , 'showVisiblity', 'align="center"' );
			$myTable->add_field($MSG['title_action'][$sysSession->lang]    , '', '', $btns   , ''             , 'align="center"' );

			$tab    = 'WM_term_subject left join WM_bbs_boards on WM_bbs_boards.board_id = WM_term_subject.board_id';
			$fields = '`node_id`, `WM_term_subject`.`board_id`, `state`, `visibility`, `permute`, `bname`, `open_time`, `close_time`, `share_time`';
			$where  = "`course_id`={$sysSession->course_id} order by `permute`";
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();

		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="cour_subject_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editFm');
			showXHTML_input('hidden', 'nid', '', '', '');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'setBoard' . $_COOKIE['idx']), '', '');
		showXHTML_form_E();

        showXHTML_form_B('action="cour_subject_delete.php" method="post" enctype="multipart/form-data" style="display:none"', 'delFm');
            showXHTML_input('hidden', 'nids', '', '', '');
            showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'delBoard' . $_COOKIE['idx']), '', '');
        showXHTML_form_E();
        // custom 
        showXHTML_form_B('action="/forum/m_node_list.php" method="post" enctype="multipart/form-data" style="display:none"', 'goBd');
            showXHTML_input('hidden', 'cid', $sysSession->course_id, '', '');
            showXHTML_input('hidden', 'xbid', '', '', '');
        showXHTML_form_E();
        
        
        
    $ary = array();
    $ary[] = array($MSG['import'][$sysSession->lang], 'import_ui');
    echo '<div align="center">';
    // showXHTML_tabFrame_B($ary, 1, 'form_import', 'import_ui', 'action="import.php" style="display: inline" method="post" enctype="multipart/form-data"', true);
        // showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
            // showXHTML_tr_B('class="cssTrEvn"');
                // showXHTML_td('nowrap="nowrap"', $MSG['import_note'][$sysSession->lang]);
            // showXHTML_tr_E('');
            // showXHTML_tr_B('class="cssTrOdd"');
                // showXHTML_td_B();
                    // showXHTML_input('file','file_import','','','class="cssInput"');

                    // showXHTML_input('button','btnImpOK',$MSG['import'][$sysSession->lang],'','onclick="OnImportButton(true);"');
                    // showXHTML_input('button','btnImpCancel',$MSG['cancel'][$sysSession->lang],'','onclick="OnImportButton(false);"');
                // showXHTML_td_E();
            // showXHTML_tr_E();
        // showXHTML_table_E('');
    // showXHTML_tabFrame_E();
    echo '</div>';
    $ticket=md5(sysTicketSeed . 'Board' . $_COOKIE['idx']);
    $ary = array();
    $ary[] = array($MSG['import_all'][$sysSession->lang], 'importall_ui');
    echo '<div align="center">';
    showXHTML_tabFrame_B($ary, 1, 'form_importall', 'importall_ui', 'action="/forum/import_all.php" style="display: inline" method="post" enctype="multipart/form-data"', true);
        showXHTML_input('hidden','ticket',$ticket);
        showXHTML_input('hidden','import_id','','','id="import_id"');
        showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
            showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td('nowrap="nowrap"', sprintf($MSG['import_note1'][$sysSession->lang], ini_get('upload_max_filesize')));
            showXHTML_tr_E('');
            showXHTML_tr_B('class="cssTrOdd"');
                showXHTML_td_B();
                    showXHTML_input('file','file_import','','','class="cssInput" size="50"');
                showXHTML_td_E();
            showXHTML_tr_E('');
            
            if(strlen($sysSession->board_ownerid)==8) {    // 只有課程討論版可以新建
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td_B();
                    $rdo_items = Array(
                                'new'=>$MSG['import_choice1'][$sysSession->lang],
                                'old'=>$MSG['import_choice2'][$sysSession->lang]
                                );
                        showXHTML_input('radio','import_choice',$rdo_items,'new','',"<br>");
                    showXHTML_td_E();
                showXHTML_tr_E('');
            } else {
                    showXHTML_input('hidden','import_choice','old');
            }
            showXHTML_tr_B('class="cssTrOdd"');
                showXHTML_td_B();
                    showXHTML_input('button','',$MSG['import_all'][$sysSession->lang],'','onclick="OnImportAllButton(true);"');
                    showXHTML_input('button','',$MSG['cancel'][$sysSession->lang],'','onclick="OnImportAllButton(false);"');
                showXHTML_td_E();
            showXHTML_tr_E();
        showXHTML_table_E('');
    showXHTML_tabFrame_E();
    echo '</div>';
    
    
    showXHTML_form_B('action="" method="post" style="display:none"', 'export_all_form');
        showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'BoardExp' . $_COOKIE['idx'] . $sysSession->board_id));
        showXHTML_input('hidden', 'boaid','');
    showXHTML_form_E();
    
    
    showXHTML_body_E();
    
?>

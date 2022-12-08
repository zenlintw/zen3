<?php
	/**
	 * 批次同意修課
	 *
	 * @since   2004/03/16
	 * @author  ShenTing Lin
	 * @version $Id: review_actmail.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lib/editor.php');
	require_once(sysDocumentRoot . '/academic/review/review_lib.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1100500100';
	$sysSession->restore();
	if (!aclVerifyPermission(1100500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$act = '';
	$_POST['ticket'] = trim($_POST['ticket']);
	// 同意
	$ticket = md5(sysTicketSeed . 'doOKReviews' . $_COOKIE['idx']);
	if ($_POST['ticket'] == $ticket) {
		$act = 'ok';
	}

	// 不同意
	$ticket = md5(sysTicketSeed . 'doDenyReviews' . $_COOKIE['idx']);
	if ($_POST['ticket'] == $ticket) {
		$act = 'deny';
	}

	// 發生非預期的動作
	if (empty($act)) {
	   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$js = <<< BOF
	var MSG_SELECT_ALL    = "{$MSG['btn_select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['btn_cancel_select'][$sysSession->lang]}";
	var MSG_NEED_SEL      = "{$MSG['msg_need_sel'][$sysSession->lang]}";

	function doReview(val) {
		var obj = document.getElementById("actFm");
		if (obj == null) return false;
		obj.did.value = val;
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
		if (obj != null) {
			nowSel = !nowSel;
			obj.checked = nowSel;
		}
		if (btn1 != null) btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		select_func('tabs1', obj.checked);
	}

	/**
	 * 取得所有點選的規則
	 * @return array : 選取的人員
	 **/
	function getSelCk() {
		var obj = null, nodes = null, attr = null;
		var ary = new Array();
		obj = document.getElementById("tabs1");
		if (obj == null) return ary;
		nodes = obj.getElementsByTagName("input");
		if (nodes == null) return ary;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (!nodes[i].checked)) continue;
			attr = nodes[i].getAttribute("exclude");
			if ((attr != null) || (attr == "true")) continue;
			ary[ary.length] = nodes[i].value;
		}
		return ary;
	}

	function chkData() {
		var obj = document.getElementById("mainFm");
		var ary = null;
		if (obj == null) return false;
		ary = getSelCk();
		if (ary.length <= 0) {
			alert(MSG_NEED_SEL);
			tabsSelect(1);
			return false;
		}
		obj.submit();
	}

	/**
	 * 新增附檔
	 **/
	var files = 1;
	var col = '';
	function more_attachs(){
		if (files >= 10){
			alert("{$MSG['msg_file_max'][$sysSession->lang]}");
			return;
		}
		var curNode = document.getElementById('upload_box');
		var nxtNode = document.getElementById('upload_base');
		var newNode = curNode.cloneNode(true);
		if (col == "") col = curNode.className;
		col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
		newNode.className = col;
		curNode.parentNode.insertBefore(newNode, nxtNode);
		newNode.getElementsByTagName("input")[0].value = "";
		files++;
	}

	/**
	 * 縮減附檔
	 **/
	function cut_attachs(){
		var curNode = document.getElementById('upload_base');
		var delNode = curNode.previousSibling;

		if (files <= 1){
			// var newNode = delNode.cloneNode(true);	// 若原本有選定檔案則清空
			// delNode.parentNode.replaceChild(newNode, delNode);
			// return;

            // #47150 Chrome dom匹配不正確
            var curNode = document.getElementById('uploads[]');
            curNode.value = "";
			return;
		}

		delNode.parentNode.removeChild(delNode);
		col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
		files--;
	}

	function gotoList() {
		window.location.replace("review_review.php");
	}

	window.onload = function () {
		selfunc();
		if (typeof(event) == "object") tabsSelect(1);
	};
BOF;

	showXHTML_head_B($MSG['title_review_mail'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		if ($act == 'ok') $ary[] = array($MSG['tabs_review_ok'][$sysSession->lang], "tabs1");
		if ($act == 'deny') $ary[] = array($MSG['tabs_review_deny'][$sysSession->lang], "tabs1");
		$ary[] = array($MSG['tabs_mail'][$sysSession->lang], "tabs2");
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 2, 'mainFm', '', 'method="post" action="review_actmail1.php" enctype="multipart/form-data" style="display: inline"'); // isDragable);
			$ticket = md5(sysTicketSeed . $act . 'doReviews' . $_COOKIE['idx']);
			showXHTML_input('hidden', 'ticket', $ticket, 'default', '');
			// 確認人員列表 (Begin)
			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" id="tabs1" class="cssTable" style="display: none;"';

			// 說明
			$myTable->add_help($MSG['msg_sure_member'][$sysSession->lang]);
			// 沒有資料
			$myTable->add_no_data_help($MSG['msg_no_data'][$sysSession->lang]);

			// 工具列
			$toolbar = new toolbar();
			$toolbar->add_caption('&nbsp;&nbsp;');
			$toolbar->add_input('button', 'btnOK', $MSG['btn_ok_send'][$sysSession->lang], '', 'onclick="chkData()" class="cssBtn"');
			$toolbar->add_input('button', 'btnMail', $MSG['btn_ok_write_mail'][$sysSession->lang], '', 'onclick="tabsSelect(2)" class="cssBtn"');
			$toolbar->add_input('button', 'btnDeny' , $MSG['btn_return_list'][$sysSession->lang] , '', 'onclick="gotoList()" class="cssBtn"');
			$myTable->set_def_toolbar($toolbar);
			$myTable->set_page(false);  // 關閉分頁
			// 全選全消的按鈕
			$myTable->set_select_btn(true, 'btnSel', $MSG['btn_select_all'][$sysSession->lang], 'onclick="selfunc()"');
			// 資料
			$ck1 = new toolbar();
			$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');

			$ck2 = new toolbar();
			$ck2->add_input('checkbox', 'fid[]', '%0', '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');

			$myTable->add_field($ck1                                                  , $MSG['th_select_title'][$sysSession->lang], '', $ck2, '' , 'width="20" align="center"');
			$myTable->add_field($MSG['th_serial'][$sysSession->lang]                  , ''                , '', ''  , 'showNum'       , 'align="center" nowrap="noWrap"');
			$myTable->add_field(divMsg(120, $MSG['th_username'][$sysSession->lang])   , '" nowrap="noWrap', '', '%1', 'showUsername'  , 'nowrap="noWrap"');
			$myTable->add_field(divMsg(120, $MSG['th_realname'][$sysSession->lang])   , '" nowrap="noWrap', '', '%1', 'showRealname'  , 'nowrap="noWrap"');
			$myTable->add_field(divMsg(120, $MSG['th_sel_course'][$sysSession->lang]) , '" nowrap="noWrap', '', '%3', 'showCourseName', 'nowrap="noWrap"');
			// #47147 Chrome 標題文字重疊
            $myTable->add_field(divMsg(130 , $MSG['th_n_limit'][$sysSession->lang])    , '" nowrap="noWrap', '', '%3', 'showNLimit'    , 'align="center" nowrap="noWrap"');
			$myTable->add_field(divMsg(130 , $MSG['th_a_limit'][$sysSession->lang])    , '" nowrap="noWrap', '', '%3', 'showALimit'    , 'align="center"' );
			$myTable->add_field(divMsg(160, $MSG['th_create_time'][$sysSession->lang]), '" nowrap="noWrap', '', '%2', 'showDatetime'  , 'align="center" width="160"' );

			$ary    = preg_replace('/\W+/', "','", $_POST['dids']);
			$tab    = 'WM_review_flow';
			$fields = '`idx`, `username`, `create_time`, `discren_id`, `content`';
			$where  = "`idx` in ('{$ary}') order by `discren_id`, `create_time`";
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
			// 確認人員列表 (End)
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="cssTable"');
				// 標題
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['th_title'][$sysSession->lang]);
					showXHTML_td_B();
						$val = ($act == 'ok') ? $MSG['msg_ok_title'][$sysSession->lang] : $MSG['msg_deny_title'][$sysSession->lang];
						showXHTML_input('text', 'caption', $val, '', 'size="80" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('valign="top"', $MSG['th_title_help'][$sysSession->lang]);
				showXHTML_tr_E();
				// 內文
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['th_content'][$sysSession->lang]);
					showXHTML_td_B();
						$val = ($act == 'ok') ? $MSG['msg_ok_text'][$sysSession->lang] : $MSG['msg_deny_text'][$sysSession->lang];
						$oEditor = new wmEditor;
						$oEditor->setValue($val);
						$oEditor->addContType('ctype', 'html');
						$oEditor->generate('note');
					showXHTML_td_E();
					showXHTML_td('valign="top"', $MSG['th_content_help'][$sysSession->lang]);
				showXHTML_tr_E();
				// 由何種方式傳送
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['th_method'][$sysSession->lang]);
					showXHTML_td_B();
						$ary = array(
							'mail' => 'E-mail',
							'msg'  => $MSG['msg_message'][$sysSession->lang],
							'both' => $MSG['msg_message_mail'][$sysSession->lang]
						);
						showXHTML_input('radio', 'method', $ary, 'mail', '', '<br />');
					showXHTML_td_E();
					showXHTML_td('valign="top"', '');
				showXHTML_tr_E();
				// 夾檔
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="upload_box"');
					showXHTML_td('align="right" nowrap="nowrap" class="cssTrHead"', $MSG['th_attachement'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('file', '', '', '', 'class="cssInput" size="70"');
					showXHTML_td_E('');
					showXHTML_td('valign="top"', '');
				showXHTML_tr_E('');
				// 按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="upload_base"');
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['btn_more_attach'][$sysSession->lang], '', 'class="cssBtn" onclick="more_attachs();"');
						showXHTML_input('button', '', $MSG['btn_del_attach'][$sysSession->lang] , '', 'class="cssBtn" onclick="cut_attachs();"');
						echo '&nbsp;&nbsp;';
						showXHTML_input('button', 'btnOK', $MSG['btn_ok_send'][$sysSession->lang], '', 'onclick="chkData()" class="cssBtn"');
						showXHTML_input('button', 'btnDeny' , $MSG['btn_return_list'][$sysSession->lang] , '', 'onclick="gotoList()" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

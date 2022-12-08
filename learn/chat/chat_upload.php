<?php
	/**
	 * 聊天室上傳夾檔
	 *
	 * @since   2004/03/05
	 * @author  ShenTing Lin
	 * @version $Id: chat_upload.php,v 1.1 2010/02/24 02:39:06 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2000100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2000100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$js = <<< BOF
	var MSG_MIN_FILES = "{$MSG['msg_file_min'][$sysSession->lang]}";
	var MSG_MAX_FILES = "{$MSG['msg_file_max'][$sysSession->lang]}";

	var pcs   = null;
	var timer = null;
	var col   = "cssTrOdd";
	var files = 1;
	function chgProcess() {
		var val = 0;
		if (pcs == null) return false;
		val = parseInt(pcs.style.width);
		val++;
		if (val > 350) clearInterval(timer);
		pcs.style.width = val + "px";
	}

	function more_attachs(){
		col = col == "cssTrEvn" ? "cssTrOdd" : "cssTrEvn";
		if (files >= 10){
			alert(MSG_MAX_FILES);
			return;
		}
		var curNode = document.getElementById('upload_box');
		var nxtNode = document.getElementById('upload_base');
		var newNode = curNode.cloneNode(true);
		newNode.className = col;
		curNode.parentNode.insertBefore(newNode, nxtNode);
		newNode.getElementsByTagName("input")[0].value = "";
		nxtNode.className = col == "cssTrEvn" ? "cssTrOdd" : "cssTrEvn";
		files++;
	}

	function cut_attachs(){
		var curNode = document.getElementById('upload_base');
		var delNode = curNode.previousSibling;
		if (files <= 1){
            /*#47341 Chrome [教室/學習互動區/線上討論] 上傳檔案畫面，選擇一個檔案後按下「縮減附檔」，不會把已選取的檔案清掉。：適用ie*/
            var newNode = delNode.cloneNode(true);	// 若原本有選定檔案則清空
            delNode.parentNode.replaceChild(newNode, delNode);
			/*alert(MSG_MIN_FILES);*/
            /*#47341 Chrome [教室/學習互動區/線上討論] 上傳檔案畫面，選擇一個檔案後按下「縮減附檔」，不會把已選取的檔案清掉。適用chrome*/
            document.getElementById('uploads[]').value = '';
			return;
		}
		delNode.parentNode.removeChild(delNode);
		col = col == "cssTrEvn" ? "cssTrOdd" : "cssTrEvn";
		curNode.className = col == "cssTrEvn" ? "cssTrOdd" : "cssTrEvn";
		files--;

	}

	function showProcess() {
		var obj = document.getElementById("divFile");
		if (obj != null) obj.style.display = "none";

		obj = document.getElementById("divUpload");
		if (obj != null) obj.style.display = "";

		pcs = document.getElementById("hrPcs");
		timer = setInterval("chgProcess()", 1000);
		return true;
	}

	window.onunload = function () {
		if (pcs != null) pcs.style.width = "350px";
	};
BOF;

	showXHTML_head_B($MSG['title_file_upload'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_file_upload'][$sysSession->lang], id, action);
		showXHTML_tabFrame_B($ary, 1, 'upFm', '', 'action="/learn/chat/chat_upload1.php" onsubmit="return showProcess();" method="post" enctype="multipart/form-data" style="display: inline;"');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'chatupload' . $_COOKIE['idx']), '', '');
			echo '<div align="center" id="divFile">';
			showXHTML_table_B('width="360" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				// 檔案說明
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['th_file_note'][$sysSession->lang]);
					showXHTML_td_B();
						// showXHTML_input('textarea', 'file_note', '', '', 'rows="5" cols="40" class="cssInput"');
						showXHTML_input('textarea', 'note', '', '', 'rows="5" cols="40" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('valign="top"', $MSG['th_help_file_note'][$sysSession->lang]);
				showXHTML_tr_E();

				// 檔案
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="upload_box"');
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['th_file'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('file', '', '', '', 'size="30" class="cssInput"');
					showXHTML_td_E();
					$msgAry = array('%MIN_SIZE%'	=> 	'<span style="color: red; font-weight:bold">' . ini_get('upload_max_filesize') . '</span>',
								 	'%MAX_SIZE%'	=>	'<span style="color: red; font-weight:bold">' . ini_get('post_max_size') . '</span>'
									);
					showXHTML_td('valign="top"', strtr($MSG['th_help_file'][$sysSession->lang], $msgAry));
				showXHTML_tr_E();

				// 按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="upload_base"');
					showXHTML_td_B('align="center" colspan="3"');
						showXHTML_input('submit', 'up', $MSG['btn_upload'][$sysSession->lang]  , '', 'class="cssBtn"');
						showXHTML_input('button', 'cn', $MSG['btn_cancel'][$sysSession->lang]  , '', 'class="cssBtn" onclick="window.close();"');
						showXHTML_input('button', 'mt', $MSG['btn_more_att'][$sysSession->lang], '', 'class="cssBtn" onclick="more_attachs();"');
						showXHTML_input('button', 'ct', $MSG['btn_cut_att'][$sysSession->lang] , '', 'class="cssBtn" onclick="cut_attachs();"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			echo '</div>';
			echo '<div align="center" id="divUpload" style="display: none;">';
			showXHTML_table_B('width="360" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				// 檔案說明
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('', $MSG['msg_help_upload'][$sysSession->lang]);
				showXHTML_tr_E();
				// 狀態
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="left" id="tdPcs"');
						echo '<hr align="left" id="hrPcs" class="cssTrHead" style="width:1px; height: 20px;">';
					showXHTML_td_E();
				showXHTML_tr_E();
				// 按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="3"');
						showXHTML_input('button', 'cn', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.close();"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			echo '</div>';
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

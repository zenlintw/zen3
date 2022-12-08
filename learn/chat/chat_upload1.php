<?php
	/**
	 * 儲存上傳的檔案
	 *
	 * @since   2004/03/05
	 * @author  ShenTing Lin
	 * @version $Id: chat_upload1.php,v 1.1 2010/02/24 02:39:06 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2000100100';
	$sysSession->restore();
	if (!aclVerifyPermission(2000100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/**
	* Return human readable sizes
	*
	* @author      Aidan Lister <aidan@php.net>
	* @version     1.1.0
	* @link        http://aidanlister.com/repos/v/function.size_readable.php
	* @param       int    $size        Size
	* @param       int    $unit        The maximum unit
	* @param       int    $retstring   The return string format
	* @param       int    $si          Whether to use SI prefixes
	*/
	function genSize($size, $unit = null, $retstring = null, $si = false)
	{
		// Units
		if ($si === true) {
			$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
			$mod   = 1000;
		} else {
			// $sizes = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
			$sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
			$mod   = 1024;
		}
		$ii = count($sizes) - 1;

		// Max unit
		$unit = array_search((string) $unit, $sizes);
		if ($unit === null || $unit === false) {
			$unit = $ii;
		}

		// Return string
		if ($retstring === null) {
			$retstring = '%01.2f %s';
		}

		// Loop
		$i = 0;
		while ($unit != $i && $size >= 1024 && $i < $ii) {
			$size /= $mod;
			$i++;
		}

		return sprintf($retstring, $size, $sizes[$i]);
	}
	
	// 判斷上傳檔案是否超過限制
	if (detectUploadSizeExceed())
	{
		showXHTML_script('inline', 'alert("'.$MSG['upload_file_error'][$sysSession->lang].'");location.replace("chat_upload.php");');
		die();
	}
	
	// 檢查 Ticket
	$ticket = md5(sysTicketSeed . 'chatupload' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
 	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	// 檢查討論室編號
	$rid = $sysSession->room_id;
	if (empty($rid)) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '錯誤的討論室編號(room_id is empty)!');
		die($MSG['msg_error_id'][$sysSession->lang]);
	}

	// 儲存夾檔。如果有的話。
	$dir = getChatPath();
	if (empty($dir)) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->room_id, 3, 'auto', $_SERVER['PHP_SELF'], '檔案存放的路徑有誤!');
		die($MSG['msg_empty_dir'][$sysSession->lang]);
	}
	$ret = trim(save_upload_file($dir, 0, 0));
	if (empty($ret)) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->room_id, 4, 'auto', $_SERVER['PHP_SELF'], '檔案上傳失敗!');
		die($MSG['msg_upload_fail'][$sysSession->lang]);
	}
	$fary = explode("\t", $ret);

	/**
	 * xml 資料說明
	<files>
		<note></note><!-- 主要的說明 -->
		<file>
			<file_name></file_name><!-- 上傳時的檔名 -->
			<real_name></real_name><!-- 存在系統的檔名 -->
			<file_size></file_size><!-- 檔案大小 -->
			<file_note></file_note><!-- 相關的說明 -->
		</file>
	</files>
	 **/
	// 建立 xml 字串 (Begin)
	$xmlDocs = '<files></files>';
	if(!$dom = domxml_open_mem($xmlDocs)) {
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 5, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
		die($MSG['msg_create_xml_error'][$sysSession->lang]);
	}

	$root  = $dom->document_element();

	// 檔案說明
	$note  = trim(stripslashes($_POST['note']));
	$note  = strip_scr($note);
	$note  = ereg_replace("[\r\n]+", '<br />', $note);
	$pnode = $dom->create_element('note');
	$data  = $dom->create_text_node($note);
	$pnode->append_child($data);
	$root->append_child($pnode);

	for ($i = 0; $i < count($fary); $i += 2) {
		$pnode = $dom->create_element('file');

		$cnode = $dom->create_element('file_name');
		$data  = $dom->create_text_node($fary[$i]);
		$cnode->append_child($data);
		$pnode->append_child($cnode);

		$cnode = $dom->create_element('rawe_name');
		$data  = $dom->create_text_node(rawurlencode($fary[$i]));
		$cnode->append_child($data);
		$pnode->append_child($cnode);

		$cnode = $dom->create_element('real_name');
		$data  = $dom->create_text_node($fary[$i + 1]);
		$cnode->append_child($data);
		$pnode->append_child($cnode);

		$leng  = filesize($dir . $fary[$i + 1]);
		$cnode = $dom->create_element('file_size');
		$data  = $dom->create_text_node(genSize($leng));
		$cnode->append_child($data);
		$pnode->append_child($cnode);

		$root->append_child($pnode);
	}

	$xmlDocs = $dom->dump_mem(false);
	$xmlDocs = ereg_replace("[\r\n]+", '', $xmlDocs);
	// 建立 xml 字串 (End)

	// 將資料寫到檔案中
	$res = setChatCont($xmlDocs, 3, 0, '', '');
	$msg = ($res) ? $MSG['msg_upload_success'][$sysSession->lang] : $MSG['msg_upload_fail'][$sysSession->lang];

	$js = <<< BOF
	window.onload = function () {
		if (opener == null) return false;
		if ((typeof(opener.session) != "object") && (typeof(opener.session) != "function")) return false;
		opener.session();
		// alert($msg);
		// window.close();
	};
BOF;
	showXHTML_head_B($MSG['title_file_upload'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_file_upload'][$sysSession->lang], id, action);
		showXHTML_tabFrame_B($ary, 1);
			showXHTML_table_B('width="360" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center"', $msg);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="center"');
						showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="window.close()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

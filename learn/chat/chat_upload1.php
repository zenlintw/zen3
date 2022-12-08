<?php
	/**
	 * �x�s�W�Ǫ��ɮ�
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
	
	// �P�_�W���ɮ׬O�_�W�L����
	if (detectUploadSizeExceed())
	{
		showXHTML_script('inline', 'alert("'.$MSG['upload_file_error'][$sysSession->lang].'");location.replace("chat_upload.php");');
		die();
	}
	
	// �ˬd Ticket
	$ticket = md5(sysTicketSeed . 'chatupload' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
 	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '�ڵ��s��!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	// �ˬd�Q�׫ǽs��
	$rid = $sysSession->room_id;
	if (empty($rid)) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '���~���Q�׫ǽs��(room_id is empty)!');
		die($MSG['msg_error_id'][$sysSession->lang]);
	}

	// �x�s���ɡC�p�G�����ܡC
	$dir = getChatPath();
	if (empty($dir)) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->room_id, 3, 'auto', $_SERVER['PHP_SELF'], '�ɮצs�񪺸��|���~!');
		die($MSG['msg_empty_dir'][$sysSession->lang]);
	}
	$ret = trim(save_upload_file($dir, 0, 0));
	if (empty($ret)) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->room_id, 4, 'auto', $_SERVER['PHP_SELF'], '�ɮפW�ǥ���!');
		die($MSG['msg_upload_fail'][$sysSession->lang]);
	}
	$fary = explode("\t", $ret);

	/**
	 * xml ��ƻ���
	<files>
		<note></note><!-- �D�n������ -->
		<file>
			<file_name></file_name><!-- �W�Ǯɪ��ɦW -->
			<real_name></real_name><!-- �s�b�t�Ϊ��ɦW -->
			<file_size></file_size><!-- �ɮפj�p -->
			<file_note></file_note><!-- ���������� -->
		</file>
	</files>
	 **/
	// �إ� xml �r�� (Begin)
	$xmlDocs = '<files></files>';
	if(!$dom = domxml_open_mem($xmlDocs)) {
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 5, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
		die($MSG['msg_create_xml_error'][$sysSession->lang]);
	}

	$root  = $dom->document_element();

	// �ɮ׻���
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
	// �إ� xml �r�� (End)

	// �N��Ƽg���ɮפ�
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

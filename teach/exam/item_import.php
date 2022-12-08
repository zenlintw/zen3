<?php
	/**
	 * 【程式功能】匯入題目
	 * 建立日期：2004/08/16
	 * @author  Wiseguy Liang
	 * @version $Id: item_import.php,v 1.1 2010/02/24 02:40:26 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600100500';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700100500';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800100200';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	// 判斷 ticket 是否正確 (開始)
	$ticket = md5(trim($_SERVER['QUERY_STRING'] . sysTicketSeed . $course_id . $_COOKIE['idx']));
	if ($ticket != $_POST['ticket']) {
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
	   die('Illegal Access !');
	}
	// 判斷 ticket 是否正確 (結束)



	// 匯入 HTML
	function parseHTML(){
		global $sysConn, $sysSession, $MSG;
		echo 'Supporting in the future.';
	}

	// 匯入 CSV
	function parseCSV(){
		global $sysConn, $sysSession, $MSG, $_POST;
		$lang = ($_POST['file_format'] ? $_POST['file_format'] : $sysSession->lang);	// 設定匯入檔案所使用的語系
		include_once(sysDocumentRoot . '/teach/exam/simple_item_import.php');
	}

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array(array($MSG['import_result'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable');
			showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

	switch(strtolower(strrchr($_FILES['import_file']['name'], '.'))){
		case '.xml':
			if ($_POST['format'] == '1') {
				include_once(sysDocumentRoot . '/teach/exam/qti_xml_lib.php');
				parseXML();
			}
			else
			{
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('colspan="2" align="center"', $MSG['unknown_import_file'][$sysSession->lang]);
				showXHTML_tr_E();
			}
			break;
		case '.txt':
		case '.csv':
			if ($_POST['format'] == '2') parseCSV();
			else
			{
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('colspan="2" align="center"', $MSG['unknown_import_file'][$sysSession->lang]);
				showXHTML_tr_E();
			}
			break;
		case '.htm':
		case '.html':
			if ($_POST['format'] == '3') parseHTML();
			else
			{
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('colspan="2" align="center"', $MSG['unknown_import_file'][$sysSession->lang]);
				showXHTML_tr_E();
			}
			break;
		default:
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('colspan="2" align="center"', $MSG['unknown_import_file'][$sysSession->lang]);
			showXHTML_tr_E();
			break;
	}
    unlink($_FILES['import_file']['tmp_name']);

			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

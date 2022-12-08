<?php
	/**
	 * 【試題預覽】
	 * 建立日期：2004/10/05
	 * @author  Wiseguy Liang
	 * @version $Id: item_preview.php,v 1.1 2009-06-25 09:27:43 edi Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600100200';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700100100';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800100100';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
			
	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	// 判斷 ticket 是否正確 (開始)
	$ticket = md5($_POST['gets'] . sysTicketSeed . $course_id . $_COOKIE['idx']);
	if ($ticket != $_POST['ticket']) {
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
	   die('Illegal Access !');
	}
	// 判斷 ticket 是否正確 (結束)
	if (!ereg('^[A-Z0-9_,]+$', $_POST['lists'])) {
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'ID format error:' . $_POST['lists']);
	   die('ID format error !'); // 判斷 ident 序列格式
	}

	chkSchoolId('WM_qti_'.QTI_which.'_item');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$allitem = $sysConn->GetCol('select content from WM_qti_' . QTI_which . '_item where ident in ("' . str_replace(',', '","', $_POST['lists']) . '")');
	$allatta = $sysConn->GetAssoc('select ident,attach from WM_qti_' . QTI_which . '_item where ident in ("' . str_replace(',', '","', $_POST['lists']) . '")');

	$attachments = array(); // 這個變數要設，否則附檔不會列出
	if (is_array($allatta)) foreach($allatta as $id => $attach)
	{
		if (preg_match('/^a:[0-9]+:{/', $attach)) $attachments[$id] = unserialize($attach);
	}

	showXHTML_head_B($MSG['preview'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary[] = array($MSG['preview'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'responseForm', 'ListTable');
				showXHTML_table_B('id ="mainTable" width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
					showXHTML_tr_B('class="font01 cssTrEvn"');
						showXHTML_td_B('align="center"');
							showXHTML_input('button', '', $MSG['close_window'][$sysSession->lang], '', 'class="cssBtn" onclick="self.close();"');
						showXHTML_td_E();
					showXHTML_tr_E();

					showXHTML_tr_B('class="font01 cssTrEvn"');
						showXHTML_td_B();
						parseQuestestinterop('<questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster">' . implode('', $allitem) . '</questestinterop>');
						showXHTML_td_E();
					showXHTML_tr_E();

					showXHTML_tr_B('class="font01 cssTrEvn"');
						showXHTML_td_B('align="center"');
							showXHTML_input('button', '', $MSG['close_window'][$sysSession->lang], '', 'class="cssBtn" onclick="self.close();"');
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

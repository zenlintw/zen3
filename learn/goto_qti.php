<?php
	/**
	 * ¶i¤J QTI
	 *
	 * @since   2005/03/08
	 * @author  ShenTing Lin
	 * @version $Id: goto_qti.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/learn/path/qti_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/sysbar.php');
	
	$sysSession->cur_func = '';
	$sysSession->restore();
	if (!aclVerifyPermission(900100600, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$canDo = false;
	$qtp = trim($_GET['tp']);
	$qid = trim($_GET['v']);
	$qid = sysDecode($qid);
	$msg = '';
	$title = '';
	switch ($qtp) {
		case 'hw' :
			$title = $MSG['title_hw'][$sysSession->lang];
			$canDo = check_qti_can_do('homework', $qid);
			if ($canDo < 0) $msg = $MSG['msg_hw_error'][$sysSession->lang];
			break;
		case 'ex' :
			$title = $MSG['title_ex'][$sysSession->lang];
			$canDo = check_qti_can_do('exam', $qid);
			if ($canDo < 0) $msg = $MSG['msg_ex_error'][$sysSession->lang];
			break;
		case 'qs' :
			$title = $MSG['title_qs'][$sysSession->lang];
			$canDo = check_qti_can_do('questionnaire', $qid);
			if ($canDo < 0) $msg = $MSG['msg_qs_error'][$sysSession->lang];
			break;
		default:
			$msg = $MSG['msg_hw_ex_qs_error'][$sysSession->lang];
	}
	if (empty($msg)) {
		header(sprintf('Location: %s', $canDo));
		exit;
	}

	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($title, 'tabs1');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('', $msg);
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();

?>

<?php
	/**
	 * 寄發審核帳號不核可通知信件
	 * @version $Id: fail_mail1.php,v 1.1 2010/02/24 02:38:44 saly Exp $:
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/verify_mail.php');

	define('MAIL_TYPE', 'FAIL_MAIL');
	$sysSession->cur_func='500300300';
	$default_subject = $MSG['fail_account_subject'][$sysSession->lang];
	$default_content = $MSG['fail_account_body'][$sysSession->lang];
	$target          = sysDocumentRoot . "/base/$sysSession->school_id/fail_account_" . $sysSession->lang . ".mail";
	$save_path       = sysDocumentRoot . "/base/$sysSession->school_id/attach/fail_account";

	require_once(sysDocumentRoot . '/academic/stud/verify_mail1.php');
?>

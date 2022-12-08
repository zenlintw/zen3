<?php
	/**
	 * �H�o�f�ֱb���֥i�q���H��
	 * @version $Id: verify_mail1.php,v 1.1 2010/02/24 02:38:45 saly Exp $:
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/verify_mail.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if (!defined('MAIL_TYPE')) {
		define('MAIL_TYPE', 'VERIFY_MAIL');
		$sysSession->cur_func = '500100100';
		$default_subject = $MSG['verify_account_subject'][$sysSession->lang];
		$default_content = $MSG['verify_account_body'][$sysSession->lang];
		$target          = sysDocumentRoot . "/base/$sysSession->school_id/verify_account_" . $sysSession->lang . ".mail";
		$save_path       = sysDocumentRoot . "/base/$sysSession->school_id/attach/verify_account";
	}
	
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	
	if (detectUploadSizeExceed())
	{
		showXHTML_script('inline', 'alert("'.$MSG['upload_file_error'][$sysSession->lang].'");location.replace("auth_mail.php");');
		die();
	}

	# ===================================================================================
	#	�D�{���}�l
	#
	#	mode		=>	�M�w�ثe�ާ@���A
	#
	switch($_POST['mode']){
		case 'save':		// �s�ɮɪ������ʧ@
			// step.1 �B�z�W�Ǫ��ɮ׳���
			if (count($_FILES['uploads']['name']) > 0){
				$ans1 = save_upload_file(trim($save_path),0,0);
			}

			// step.2 ���X�����զX�����@�ʧ��㪺�H
			if (empty($_POST['title'])){
				$Subject    = trim(stripslashes($default_subject));
			}else{
				$Subject	= trim(stripslashes($_POST['title'])) . "\r\n";
			}
			if (empty($_POST['content'])){
				$Content    = trim(stripslashes($default_content));
			}else{
				$Content    = trim(stripslashes($_POST['content']));
			}
			$att_file		= '';

			// step.3 �զX�s�W���ɮ�
			$file_amount	= count($_FILES['uploads']['name']);
			for ($i=0; $i<$file_amount; $i++){
				if ($_FILES['uploads']['name'][$i] != '')
					$att_file	= ($att_file=='')?$_FILES['uploads']['name'][$i]:$att_file.','.$_FILES['uploads']['name'][$i];
			}

			$whole_latter = $Subject.$Content;

			// step.4 �x�s�ɮ�
			if (file_exists($target)){
			    unlink($target);
			}
			if ($fp = fopen($target, 'w')){
				fwrite($fp, $whole_latter);
				fclose($fp);
				wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], 'modify : '.$target);
			}
			else {
				echo "File Not Save !!";
				wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'File Not Save');
			}
			break;
		case 'delfile':		// �R���ɮ׮ɪ������ʧ@
			$del_file = trim($save_path . DIRECTORY_SEPARATOR . $_POST['file_name']);
			if (is_file($del_file)) {
				@unlink($del_file);
				wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], "Delete File : {$del_file}");
			}
			break;
	}

	echo '<script language="Javascript">';
	echo 'alert("' . $MSG['add_success'][$sysSession->lang] . '");';
	echo 'window.location.href="auth_mail.php?msgtp=' . (MAIL_TYPE == 'VERIFY_MAIL' ? 1 : 2) . '"';
	echo '</script>';
?>

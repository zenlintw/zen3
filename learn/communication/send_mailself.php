<?php
	/**
	 * �x�s�T��
	 *
	 *     �������ʧ@
	 *         1. �ഫ����̪����βŸ�
	 *         2. �o�e�즬��̪��H�c
	 *         3. �O���@����ƥ��X
	 *
	 * PS: ���ӭn�]�̦h��H���X�ӤH
	 *
	 * �إߤ���G2003/05/09
	 * @author  ShenTing Lin
	 * @version $Id: send_mailself.php,v 1.1 2010/02/24 02:39:07 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	require_once(sysDocumentRoot . '/lang/stud_account.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '500100100';
	$sysSession->restore();
	if (!aclVerifyPermission(500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	//  ���� �q�T�����ɪ����e (attach file)
	$html_file = stripslashes($_POST['mail_txt']);
	$s = array(
		'style="DISPLAY: none"',
		'/theme/' . $sysSession->theme . '/learn/female.gif',
		'/theme/' . $sysSession->theme . '/learn/male.gif',
		'/theme/' . $sysSession->theme . '/learn/communication/hide.gif'
	);
	$r = array('style=""', 'female.gif', 'male.gif', 'hide.gif');
	$html_file= str_replace($s, $r, $html_file);

	// �ҵ{�W��
	$s = array('&lt;', '&gt;', '&amp;');
	$r = array('<', '>', '&');
	$course_name = str_replace($s, $r, $sysSession->course_name);

	//  �H����D
	$subject = $course_name . $MSG['subject'][$sysSession->lang];

	// ** ���� ���Y�� begin **
	$temp_dir = sysDocumentRoot . '/base/' . $sysSession->school_id . '/course/' . $sysSession->course_id . '/';
	$temp = 'WM' . sprintf("%c%c%c%03d", mt_rand(97, 122), mt_rand(97, 122), mt_rand(97, 122), mt_rand(1, 999));
	$zip_name = $temp . '.zip';

	chdir($temp_dir);

	$male    = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/theme/default/learn/male.gif');
	$female  = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/theme/default/learn/female.gif');
	$caution = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/theme/default/learn/communication/hide.gif');

	$zip_lib = new ZipArchive_php4($zip_name, '', false, '', $temp_dir);
	$zip_lib->add_string($html_file, 'communication.htm');
	$zip_lib->add_string($male, 'male.gif');
	$zip_lib->add_string($female, 'female.gif');
	$zip_lib->add_string($caution, 'hide.gif');

	// ***************
	$send_content = file_get_contents($zip_name);

	//  �H�@���즬��X�ƥ��h(����̬����x�b��,�G�����H�H) (begin)

	$orgdir = MakeUserDir($sysSession->username);

	// �� zip �� �� �T������ ���Y�ӨϥΪ̥ؿ��U
	$zip_name = 'communication.zip';
	@touch(sysTempPath . DIRECTORY_SEPARATOR . $zip_name);

	if ($fp = fopen(sysTempPath . DIRECTORY_SEPARATOR . $zip_name, 'w')) {
		@fwrite($fp, $send_content);
	}

	// �� female.gif ������ �� �T������ ���Y�ӨϥΪ̥ؿ��U
	$zip_name1 = cpAttach($sysSession->username, sysTempPath, "{$zip_name}\t{$zip_name}");

	$ret1 .= "\t" . $zip_name1;
	fclose($fp);

	// �H�� �T������ ������
	collect('sys_inbox', $sysSession->username, $sysSession->username, '', addslashes($subject), '', 'html', '', $ret1, '', '', '');
	// �H�@���즬��X�ƥ��h(����̬����x�b��,�G�����H�H) (end)

	// $content1 = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/mail_count.php?mailid=' .  $sysSession->school_id . '_' . $InsertID . '" style="display:none">' . $content;
	$content1 = $content;
	$mail = buildMail('', $subject,$content1, 'html', '', '', '', '', false);

	$zip_name2 = 'communication.zip';
	$mail->add_attachment($send_content, $zip_name2);
	$mail->to = $sysSession->email;
	$mail->send();

	// �R�� ���ͪ����Y�� �� �Q���Y��
	$zip_lib->delete();

	// ** ���� ���Y�� end   **

	// �^�� �{�����檺�ؿ�
	chdir(sysDocumentRoot . '/learn/communication/');

	echo <<< BOF
<script>
	alert("{$MSG['send_to'][$sysSession->lang]}{$sysSession->email}");
	window.location.replace("stud_list.php");
</script>
BOF;
?>
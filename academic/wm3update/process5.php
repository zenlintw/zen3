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
	 * @version $Id: process5.php,v 1.1 2010/02/24 02:38:48 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/message/collect.php');

	/**
	 * �ഫ����̪����βŸ�
	 *     1. �ť� -> ,
	 *     2. ; -> ,
	 **/
	function mailFilterCallback($mail)
	{
		return preg_match(sysMailRule, $mail);
	}
	
	
	// �N����̤��Ω��}�C���A�åB�L�o���ƪ��H��
	$to = array_unique(array_filter(preg_split('/[^\w.@-]+/', $_POST['to'], -1, PREG_SPLIT_NO_EMPTY), 'mailFilterCallback'));

	// ���D���\�ϥ� html
	$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);

	// ���媺���A
	$type = (!$_POST['isHTML']) ? 'text' : 'html';

	// ����h���Ҧ��������n html
	$content = strip_scr($_POST['content']);

	// ���e
	$mail = buildMail('', $subject, $content, $type);
	foreach ($to as $username) {
		// �e�H
		$mail->to = $username;
		$mail->send();
	}
	
	header('Location: /academic/wm3update/list.php');
?>

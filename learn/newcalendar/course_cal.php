<?php
	/**
	 * [��T��]�ҵ{��ƾ�
	 *	�P[�ЫǺ޲z]�ҵ{��ƾ�t�� 1.��Ū,2.�ֶפJ��ƾ�\��
	 *
	 * �إߤ���G2005/06/30
	 * @author  Hubert
	 * @version $Id: course_cal.php,v 1.1 2010/02/24 02:39:04 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/*** �����ܼ� ***/
	$calEnv = 'teach';

	/*** �O�_��Ū ***/
	$calLmt = 'Y';
	require_once(sysDocumentRoot . '/learn/newcalendar/calendar.php');
?>

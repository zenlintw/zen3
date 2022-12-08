<?php
	/**
	 * 行事曆
	 *
	 * 建立日期：2003/03/13
	 * @author  ShenTing Lin
	 * @version $Id: calendar.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/*** 環境變數 ***/
	$calEnv = 'direct';

	/*** 是否唯讀 ***/
	$calLmt = 'N';
	require_once(sysDocumentRoot . '/learn/calendar/calendar.php');
?>

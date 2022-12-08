<?php
	/**
	 * 行事曆
	 *
	 * 建立日期：2003/03/13
	 * @author  ShenTing Lin
	 * @version $Id: calendar.php,v 1.1 2010/02/24 02:40:22 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/*** 環境變數 ***/
	$calEnv = 'teach';

	/*** 是否唯讀 ***/
	$calLmt = 'N';
	require_once(sysDocumentRoot . '/learn/newcalendar/calendar.php');

?>

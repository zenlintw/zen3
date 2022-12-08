<?php
	/**
	 * [校園廣場]校務行事曆
	 *	與全校行事曆差異 1.唯讀,2.少匯入行事曆功能
	 *
	 * 建立日期：2005/06/30
	 * @author  Hubert
	 * @version $Id: sch_cal.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/*** 環境變數 ***/
	$calEnv = 'academic';

	/*** 是否唯讀 ***/
	$calLmt = 'Y';
	require_once(sysDocumentRoot . '/learn/newcalendar/calendar.php');
?>

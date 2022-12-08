<?php
	/**
	 * [資訊區]課程行事曆
	 *	與[教室管理]課程行事曆差異 1.唯讀,2.少匯入行事曆功能
	 *
	 * 建立日期：2005/06/30
	 * @author  Hubert
	 * @version $Id: course_cal.php,v 1.1 2010/02/24 02:39:04 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/*** 環境變數 ***/
	$calEnv = 'teach';

	/*** 是否唯讀 ***/
	$calLmt = 'Y';
	require_once(sysDocumentRoot . '/learn/newcalendar/calendar.php');
?>

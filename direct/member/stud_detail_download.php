<?php
	/**
	 * 上站動作下載
	 *
	 * @since   2009-07-24
	 * @author  ShenTing Lin
	 * @version $Id: stud_detail_download.php,v 1.1 2010/02/24 02:38:59 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	session_write_close();
	session_start('WMPro');
	$sysSession->course_id = intval($_SESSION['Course_ID']);
	session_write_close();
	include(sysDocumentRoot . '/teach/student/stud_detail_download.php');
?>

<?php
	/**
	 * 儲存課程
	 *
	 * 建立日期：2002/09/09
	 * @author  ShenTing Lin
	 * @version $Id: course_save.php,v 1.1 2010/02/24 02:40:24 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	define("ENV_TEACHER", true);
	require_once(sysDocumentRoot . '/academic/course/course_save.php');
?>

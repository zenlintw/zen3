<?php
	/**
	 * 取得整個課程群組的 XML
	 *
	 * 建立日期：2002/12/12
	 * @author  ShenTing Lin
	 * @version $Id: course_group_get.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	define('Mail2Group', true);
	require_once(sysDocumentRoot . '/academic/course/course_group_get.php');
?>

<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	define('Course_ID', intval($_GET['course_id']));
	session_start('WMPro');
	$_SESSION['Course_ID'] = Course_ID;
	session_write_close();
	require_once(sysDocumentRoot . '/teach/student/stud_log.php');
?>

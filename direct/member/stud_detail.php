<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	
	define('Course_ID', intval($_GET['course_id']));
	require_once(sysDocumentRoot . '/teach/student/stud_detail.php');
?>

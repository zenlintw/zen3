<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	
	$course_id = $_POST['course_id'] ? intval($_POST['course_id']) : ($_GET['course_id'] ? intval($_GET['course_id']) : $sysSession->course_id);
	define('Course_ID', $course_id);
	require_once(sysDocumentRoot . '/teach/student/stud_info.php');
?>

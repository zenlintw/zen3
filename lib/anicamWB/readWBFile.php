<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	if (!isset($_GET['filepath']) || !file_exists($_GET['filepath']))
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}

	if (strpos(realpath($_GET['filepath']), sysDocumentRoot . "/base/$sysSession->school_id/board/$sysSession->board_id/") !== 0 &&
		strpos(realpath($_GET['filepath']), sysDocumentRoot . "/base/$sysSession->school_id/course/$sysSession->course_id/board/$sysSession->board_id/") !== 0)
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}


    header('pragma:no-cache');
   	header('expires:0');
	header('Content-transfer-encoding: binary');
	header('Content-Disposition: attachment; filename=' . basename($_GET['filepath']));
	header('Content-Type: application/octet-stream');
	header('Accept-Ranges: bytes');
	readfile($_GET['filepath']);
?>
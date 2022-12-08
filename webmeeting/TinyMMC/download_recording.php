<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//
	require_once('include/config.php');

	$path = DEFAULT_RECORDING_DIR . "/_user/{$_POST['ownerId']}/{$_POST['meetingId']}/{$_POST['recordingId']}.jnr";
	if (!file_exists($path)) die("file not found.{$path}");

	$export_filename = $_POST['recordingId'] . '.jnr';
	$export_content  = file_get_contents($path);

	header('Content-Type: application/zip');
	header('Content-Length: ' . strlen($export_content));

	// IE5.5 just downloads index.php if we don't do this
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.5') !== false) {
		header("Content-Disposition: filename={$export_filename}");
	}else{
		header("Content-Disposition: attachment;filename={$export_filename}");
	}
	echo $export_content;

?>
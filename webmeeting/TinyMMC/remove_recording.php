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
	
	if (strpos($_POST['ownerId'], '..') !== false){
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	
	if (strpos($_POST['meetingId'], '..') !== false){
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$dir = DEFAULT_RECORDING_DIR . "/_user/{$_POST['ownerId']}/{$_POST['meetingId']}";
	$path = "{$dir}/{$_POST['recordingId']}.jnr";
	if (!file_exists($path))
	{
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	
	// if (!rename($dir, "/tmp/{$_POST['meetingId']}")){echo "A";exit;}
	$cmd = sprintf("/bin/mv %s /tmp/%s",$dir,$_POST['meetingId']);
	exec($cmd);
?>
<html>
<script language="javascript">
window.close();
</script>
<body>
Finish to delete this meeting.
</body>
</html>
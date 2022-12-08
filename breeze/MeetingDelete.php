<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(BREEZE_PHP_DIR . '/Actions/SessionManager.php');
	require_once(BREEZE_PHP_DIR . '/Actions/ScoDelete.php');

	if (!isset($_POST['scoid'])) die('error arguments');
	$sess = getEnableSessionId();
	$action = new ScoDelete($sess, $_POST['scoid']);
	$action->run();
	
	if ($_POST['op_from'] == 'archives')
	{
		$url = '/breeze/MeetingArchives.php';
	}else{
		$url = '/breeze/MeetingManager.php';
	}
?>
<html>
<head>
<script language="javascript">
	alert("<? echo $alertmsg; ?>");
	document.location.href="<? echo $url; ?>";
</script>
</head>
</html>
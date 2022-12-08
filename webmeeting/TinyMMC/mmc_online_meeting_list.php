<?php
	require_once('include/config.php');
	require_once('include/online.php');
	require_once('include/lib.php');
	require_once('include_mcu/xml_status.php');

#======= Main ========================
	$statusOnline = _mcuStatusGetOnlineList();
	echo base64_encode(serialize($statusOnline->statusUserList));

?>
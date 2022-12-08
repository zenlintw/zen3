<?php
	header('Pragma: no-cache');
    header('Cache-Control: no-cache, no-store, private, must-revalidate, post-check=0, pre-check=0');
    header('Expires: -1');
    header('Content-type: text/html; charset=UTF-8');
	printf('<root server_time="%s" />', date('Y-m-d H:i:s'));
?>

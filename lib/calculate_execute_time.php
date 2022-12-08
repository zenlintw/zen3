<?php
	function getmicrotime(){
		list($usec, $sec) = explode(' ',microtime());
		return ((float)$usec + (float)$sec);
	}

	function calculate_execute_time(){
		if(isset($GLOBALS['sysIndent']))
		echo '<div style="position: absolute; bottom: 10px; right: 10px; font-size: 10pt; filter: Alpha(opacity=50)">', number_format(getmicrotime() - $GLOBALS['prog_start_time'], 5), ' S</div>';
	}

	$prog_start_time = getmicrotime();
	register_shutdown_function('calculate_execute_time');
?>
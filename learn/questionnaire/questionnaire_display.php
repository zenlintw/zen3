<?php
    if ($_SERVER['argv'][1] == '1')      define('forGuestQuestionnaire', true);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	
    function strip_score($data)
    {
        return preg_replace('!<b>[^<>]*\[[\d.]+\]</b>!isU', '', $data);
    }

    if ($_SERVER['argv'][0] == 'school') define('QTI_env', 'academic');
    ob_start('strip_score');
    require_once(sysDocumentRoot . '/learn/homework/homework_display.php');
	ob_end_flush();
?>

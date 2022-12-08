<?php
	/**
	 * 處理匯入行事曆
	 *
	 * 建立日期：2004/04/02
	 * @author  KuoYang Tsao
	 * @version $Id: cal_import1.php,v 1.1 2010/02/24 02:38:13 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	
	$calEnv = 'academic';
	$calLmt = 'N';
    
	require_once(sysDocumentRoot . '/learn/calendar/cal_import1.php');
?>

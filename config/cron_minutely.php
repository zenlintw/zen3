#!/usr/local/bin/php
<?php
	/**
	 *	※ WM minutely 定時執行程式
	 *
	 * @since   2004/09/15
	 * @author  Wiseguy Liang
	 * @version $Id: cron_minutely.php,v 1.1 2010/02/24 02:38:56 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 *
	 **/

	// 系統設定
	require_once(dirname(__FILE__) . '/console_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_newsfaq.php');
	
	$sids = $sysConn->GetCol(sprintf('select distinct school_id from %s.WM_school where school_host not like "[delete]%%"', sysDBname));
	if (is_array($sids) && count($sids)) foreach($sids as $sid)
	{
		$Da = getConstatnt($sid);
		$sysDBprefix = sysDBprefix;
		$sysConn->Execute("UPDATE {$sysDBprefix}{$sid}.WM_session SET chance=chance+1 WHERE touch <= SUBDATE(NOW(), INTERVAL 1 MINUTE)");
		$sysConn->Execute("DELETE FROM {$sysDBprefix}{$sid}.WM_session WHERE chance > " . (strlen($Da['systemTimeOutLimit']) > 0 ?intval($Da['systemTimeOutLimit']):DEFAULT_systemTimeOutLimit));
		$sysConn->Execute("DELETE FROM {$sysDBprefix}{$sid}.WM_session WHERE username = ''");
		$sysConn->Execute("DELETE FROM {$sysDBprefix}{$sid}.db_session WHERE idx NOT IN (select idx from WM_session)");
		createNewsXML($sid, 'news');
		createFAQXML($sid, 'faq');
	}

?>

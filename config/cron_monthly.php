#!/usr/local/bin/php
<?php
	/**
	 *	※ WM monthly 定時執行程式
	 *
	 * @since   2004/09/15
	 * @author  Wiseguy Liang
	 * @version $Id: cron_monthly.php,v 1.1 2010/02/24 02:38:56 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 *
	 **/

	// 系統設定
	require_once(dirname(__FILE__) . '/sys_config.php');
	require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');

	// 資料庫連結初始化
	$sysConn = &ADONewConnection(sysDBtype);
	if (!$sysConn->PConnect(sysDBhost, sysDBaccoount, sysDBpassword))
		die('Database Connecting failure !');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	// 所有 table 最佳化

	$sysConn->Execute('use ' . sysDBname);
	$sysConn->Execute('optimize table WM_all_account,WM_manager,WM_sch4user,WM_school');
	$sids = $sysConn->getCol('select distinct school_id from WM_school where school_host not like "[delete]%"');
	foreach($sids as $sid)
	{
		$sysConn->Execute('use ' . sysDBprefix . $sid);
		if ($sysConn->ErrorNo()) continue;
		$tbs = $sysConn->GetCol('show tables from ' . sysDBprefix . $sid);		// 取得所有學校的 DB
		unset($tbs['WM_auth_samba'], $tbs['WM_chat_session'], $tbs['WM_session']);
		$sysConn->Execute('optimize table ' . implode(',', $tbs));
	}

?>

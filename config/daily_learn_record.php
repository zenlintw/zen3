#!/usr/local/bin/php
<?php
	/**
	 *	※ WM daily4user 提供使用者手動更新每日的學習記錄
	 *
	 * @since   2005/08/18
	 * @author  Wiseguy Liang, modify by jeff
	 * @version $Id: daily_learn_record.php,v 1.1 2010/02/24 02:38:56 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 *
	 **/
#======== function ==============
	/**
		@name showArgsExample
		@abstract 顯示使用參數的範例
	*/	
	function showArgsExample()
	{
		echo "arguments error:\r\n";
		echo "command usage as the following:\r\n";
		echo "./cron_daily4user.php startdate enddate\r\n";
		echo "example:\r\n";
		echo "./cron_daily4user.php 2005-08-01 2005-08-15\r\n";
		exit;
	}
	
	/**
		@name clearDuringData
		@abstract 先清除參數1與參數2期間的資料，重新進行匯入
	*/
	function clearDuringData($st,$et)
	{
		global $sysConn;
		$sqls = "delete from WM_record_daily_personal where  unix_timestamp(thatday) between '{$st}' and '{$et}'";
		if (!$sysConn->Execute($sqls)) echo $sysConn->ErrorMsg();
		
		$sqls = "delete from WM_record_daily_course where  unix_timestamp(thatday) between '{$st}' and '{$et}'";
		if (!$sysConn->Execute($sqls)) echo $sysConn->ErrorMsg();
		
	}
	
	/**
		@name insertPersonalDailyData()
		@abstract 新增個人每日的閱讀記錄
		@param $st : 啟始時間
		@param $et : 結束時間
	*/
	function insertPersonalDailyData($st, $et)
	{
		global $sysConn;
		$sqls = 'insert into WM_record_daily_personal (username,course_id,thatday,reading_seconds) ' .
				"select username,course_id,DATE_FORMAT(begin_time,'%Y-%m-%d') as dd,sum(unix_timestamp(over_time)-unix_timestamp(begin_time)+1) " .
				'from WM_record_reading ' .
				"where unix_timestamp(begin_time) between '{$st}' and '{$et}' " .
				'group by username,course_id,dd';
		if ($sysConn->Execute($sqls)); else echo $sysConn->ErrorMsg();
	}
	
	/**
		@name insertCourseDailyData()
		@abstract 新增課程的閱讀記錄
		@param $st : 啟始時間
		@param $et : 結束時間
	*/
	function insertCourseDailyData($st, $et)
	{
		global $sysConn;
		$sqls = 'insert into WM_record_daily_course (course_id,thatday,reading_seconds) ' .
				"select course_id,DATE_FORMAT(begin_time,'%Y-%m-%d') as dd,sum(unix_timestamp(over_time)-unix_timestamp(begin_time)+1) " .
				'from WM_record_reading ' .
				"where unix_timestamp(begin_time) between '{$st}' and '{$et}' " .
				'group by course_id,dd';
		if ($sysConn->Execute($sqls)); else echo $sysConn->ErrorMsg();
	}
#======== main ===================
// 參數檢查
	//1. 參數數量檢查
	if (count($_SERVER['argv']) != 3)	showArgsExample();
	// 參數長度檢查
	if ((strlen($_SERVER['argv'][1]) != 10) || (strlen($_SERVER['argv'][2]) != 10)) showArgsExample();
	// 內容值檢查
	if (($stime = strtotime($_SERVER['argv'][1])) == -1) showArgsExample();
	if (($etime = strtotime($_SERVER['argv'][2])) == -1) showArgsExample();
	if ($stime > $etime) showArgsExample();

// 載入所需的函式庫
	require_once(dirname(__FILE__) . '/sys_config.php');
	require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');

	// 資料庫連結初始化
	$sysConn = &ADONewConnection(sysDBtype);
	if (!$sysConn->PConnect(sysDBhost, sysDBaccoount, sysDBpassword))
		die('Database Connecting failure !');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	foreach($sysConn->GetCol('show databases') as $db)
	{
		if (!preg_match('/^' . sysDBprefix . '([0-9]{5})$/', $db, $sid)) continue;

		//切換資料庫
		$sysConn->Execute('use ' . $db);
		
		//先清除這時間區間的資料
		clearDuringData($stime,$etime);

		// 將每天的個人閱讀時間記入
		insertPersonalDailyData($stime, $etime);

		// 將每天的課程閱讀時間記入
		insertCourseDailyData($stime, $etime);
	}

?>

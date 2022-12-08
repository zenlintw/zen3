#!/usr/local/bin/php
<?php
	/**
	 *	�� WM daily4user ���ѨϥΪ̤�ʧ�s�C�骺�ǲ߰O��
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
		@abstract ��ܨϥΰѼƪ��d��
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
		@abstract ���M���Ѽ�1�P�Ѽ�2��������ơA���s�i��פJ
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
		@abstract �s�W�ӤH�C�骺�\Ū�O��
		@param $st : �ҩl�ɶ�
		@param $et : �����ɶ�
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
		@abstract �s�W�ҵ{���\Ū�O��
		@param $st : �ҩl�ɶ�
		@param $et : �����ɶ�
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
// �Ѽ��ˬd
	//1. �ѼƼƶq�ˬd
	if (count($_SERVER['argv']) != 3)	showArgsExample();
	// �Ѽƪ����ˬd
	if ((strlen($_SERVER['argv'][1]) != 10) || (strlen($_SERVER['argv'][2]) != 10)) showArgsExample();
	// ���e���ˬd
	if (($stime = strtotime($_SERVER['argv'][1])) == -1) showArgsExample();
	if (($etime = strtotime($_SERVER['argv'][2])) == -1) showArgsExample();
	if ($stime > $etime) showArgsExample();

// ���J�һݪ��禡�w
	require_once(dirname(__FILE__) . '/sys_config.php');
	require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');

	// ��Ʈw�s����l��
	$sysConn = &ADONewConnection(sysDBtype);
	if (!$sysConn->PConnect(sysDBhost, sysDBaccoount, sysDBpassword))
		die('Database Connecting failure !');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	foreach($sysConn->GetCol('show databases') as $db)
	{
		if (!preg_match('/^' . sysDBprefix . '([0-9]{5})$/', $db, $sid)) continue;

		//������Ʈw
		$sysConn->Execute('use ' . $db);
		
		//���M���o�ɶ��϶������
		clearDuringData($stime,$etime);

		// �N�C�Ѫ��ӤH�\Ū�ɶ��O�J
		insertPersonalDailyData($stime, $etime);

		// �N�C�Ѫ��ҵ{�\Ū�ɶ��O�J
		insertCourseDailyData($stime, $etime);
	}

?>

<?php
	/**
		�B�z�����t��logs�������禡
		@author jeff
		@since 2005-11-30
	*/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	
	/**
		���ocron_daily�̫᪺����ɶ�
		@name getCronDailyLastExecuteTime()
		@return int : timestamp
	*/
	function getCronDailyLastExecuteTime()
	{
		$fid = cron_daily_function_id;
		$RS = dbGetStSr('WM_log_others','max(log_time) as maxlogtime',"function_id={$fid} and username='".sysRootAccount."'", ADODB_FETCH_ASSOC);
		if (isset($RS['maxlogtime'])) return $RS['maxlogtime'];
		return 0;
	}
?>

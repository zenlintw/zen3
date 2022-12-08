<?php
	/**
	 * 演講廳初始化程式
	 *
	 * @since   2012/10/16
	 * @author  sj
	 * @copyright Wisdom Master 3(C)  Copyright(R) SunNet Co. Taiwan, R.O.C
	 **/
	require_once(sysDocumentRoot . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	function mediaInitial () {
		global $sysConn;
		
		$tables = array('APP_experience_catalog', 'APP_experience_url');
		
		// 檢查資料表 APP_experience_catalog, APP_experience_url
		$sysConn->Execute('USE '.sysDBschool);
		foreach ($tables as $tableName) {
			$sql = sprintf("SHOW TABLES WHERE Tables_in_%s='%s'", sysDBschool, $tableName);
			if (!$sysConn->GetOne($sql)) {
				// 建立新表
				$sql = file_get_contents(sysDocumentRoot. '/academic/App_course/experience_' . $tableName . '.sql');
				$sysConn->Execute($sql);
			}
		}
	}
	
	mediaInitial();
	

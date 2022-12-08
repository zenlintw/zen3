<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(BREEZE_PHP_DIR . '/Actions/SessionManager.php');
	require_once(BREEZE_PHP_DIR . '/Actions/Report/ActiveMeetings.php');

	function ErrorLog($msg)
	{
        $dir = BREEZE_PHP_DIR;
        if (is_writable(BREEZE_PHP_DIR)) {
            $dir = BREEZE_PHP_DIR . '/logs';
        }else{
            $dir = sysDocumentRoot.'/base/10001/breeze_logs';
        }
		$logF = $dir . '/err_' . date('Ymd') . '.log';
		$fd = fopen($logF,"a+");
		fputs($fd, sprintf("%s\t%s\n",date("H:i:s"),$msg));
		fclose($fd);
	}
	
	function getCUID($idx)
	{
		return BREEZE_SCHOOL_ID . '_' . $idx;
	}

	//get meeting url
	function getMeetingURL($sess, $idx)
	{
		return sprintf("http://%s/%s/",BREEZE_SERVER_ADDR, getMeetingUrlPath($sess, $idx));
	}
	
	function getMeetingUrlPath($sess, $idx)
	{
		include_once(BREEZE_PHP_DIR . '/Actions/ScoInfo.php');
		$action = new ScoInfo($sess, $idx);
		$action->run();
		return parseValue("<url-path>/(.*)/</url-path>", $action->conn->HTTP_RESPONSE_BODY);
	}
	
	//查詢該門課是否已開啟Meeting
	function isBreezeOnlineMeeting($idx)
	{
		$list = getBreezeMeetingList($idx);
		if (count($list)>0) return true;
		return false;
	}
	
	//取得該門課現在正運作的Meeting List
	function &getBreezeMeetingList($idx)
	{
		$sess = getEnableSessionId();
		if (empty($sess)) die('errcode:001');
		$Meetings = getActiveMeetingList($sess, getCUID($idx));
		return $Meetings;
	}
	
	//ISO Date Time => Simple Express
	function getSimpleDateTimeExpress($str)
	{
		return substr(str_replace('T',' ',$str),0,19);
	}
	
	/**
		判斷Breeze Meeting這Sco資源是否有錄影檔
		@param $scoid : 會議資源
	*/
	function hasRecordingScos($scoid, $sess='')
	{
		include_once(BREEZE_PHP_DIR . '/Actions/ScoContents.php');
		include_once(BREEZE_PHP_DIR . '/Actions/ScoInfo.php');
		if (empty($sess)) $sess = getEnableSessionId();
		$rtn = 0;
		$action = new ScoContents($sess, $scoid);
		$action->addParameters('filter-icon','archive');
		$action->run();
		$xmlarr = explode('</sco><sco', $action->conn->HTTP_RESPONSE_BODY);
		for($j=0; $j<count($xmlarr); $j++)
		{
			$obj1 = new MeetingRecorderXML($xmlarr[$j]);
			if (empty($obj1->scoId)) continue;
			$action = new ScoInfo($sess, $obj1->scoId);
			$action->run();
			if (strpos($action->conn->HTTP_RESPONSE_BODY,'code="ok"') === false) continue;
			$rtn++;
		}
		return $rtn;
	}
	
	//刪除Sco資源
	function deleteScoResource($scoId, $sess='')
	{
		include_once(BREEZE_PHP_DIR . '/Actions/ScoDelete.php');
		if (empty($sess)) $sess = getEnableSessionId();
		$action = new ScoDelete($sess, $scoId);
		$action->run();
	}
?>
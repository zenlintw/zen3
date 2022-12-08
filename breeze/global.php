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
	
	//�d�߸Ӫ��ҬO�_�w�}��Meeting
	function isBreezeOnlineMeeting($idx)
	{
		$list = getBreezeMeetingList($idx);
		if (count($list)>0) return true;
		return false;
	}
	
	//���o�Ӫ��Ҳ{�b���B�@��Meeting List
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
		�P�_Breeze Meeting�oSco�귽�O�_�����v��
		@param $scoid : �|ĳ�귽
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
	
	//�R��Sco�귽
	function deleteScoResource($scoId, $sess='')
	{
		include_once(BREEZE_PHP_DIR . '/Actions/ScoDelete.php');
		if (empty($sess)) $sess = getEnableSessionId();
		$action = new ScoDelete($sess, $scoId);
		$action->run();
	}
?>
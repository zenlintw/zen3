<?php
require_once(BREEZE_PHP_DIR . '/global.php');
require_once(BREEZE_PHP_DIR . '/Actions/ScoContents.php');
#========= classes =============
/**
	解析sco-contents action所取得meeting資料xml
*/
class MeetingDataXML
{
	var $scoId;
	var $name = '';
	var $urlpath = '';
	var $date_begin = '';
	var $date_end = '';
	var $date_modified = '';

	function MeetingDataXML($sess, $xml)
	{
		$this->scoId = parseValue("sco-id=\"([0-9]{1,})\"",$xml);	
		$this->name = parseValue("<name>(.*)</name>",$xml);
		$this->urlpath = getMeetingURL($sess, $this->scoId);
		$this->date_begin = parseValue("<date-begin>(.*)</date-begin>",$xml);
		$this->date_end = parseValue("<date-end>(.*)</date-end>",$xml);
		$this->date_modified = parseValue("<date-modified>(.*)</date-modified>",$xml);
	}
}
#========= function =============
	/**
		取得某目錄內此門課的所有會議列表(預設是BREEZE_WM_MEETING_FOLDER_ID:臨時性會議)
		@param $sess : breeze login session
		@param $idx : 會議的前綴字串: BREEZE_SCHOOL_ID+$sysSession->course_id
		@param $foldid : 目錄id -- (預設是BREEZE_WM_MEETING_FOLDER_ID:臨時性會議)
	*/
	function getCourseMeetingsList($sess, $idx, $foldid='')
	{
		$rtnArray = array();
		if (empty($foldid))
			$action = new ScoContents($sess, BREEZE_WM_MEETING_FOLDER_ID);
		else
			$action = new ScoContents($sess, $foldid);
		$action->addParameters('filter-like-name',$idx);
		$action->addParameters('filter-type','meeting');
		$action->run();
		if (strpos($action->conn->HTTP_RESPONSE_BODY,"<scos>") !== false)
		{
			$arr = explode("</sco><sco", $action->conn->HTTP_RESPONSE_BODY);
		}
		for($i=0; $i<count($arr); $i++)
		{
			if (empty($arr[$i])) continue;
			$rtnArray[] = new MeetingDataXML($sess, $arr[$i]);
		}
		return $rtnArray;
	}
	
?>
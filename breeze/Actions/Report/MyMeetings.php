<?php
//Action : PrincipalList

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class ActiveMeetingXML
{
	var $scoId;
	var $participants = 0;
	var $length_minutes = 0;	//已進行的時間
	var $name = '';
	var $date_begin = '';
	function ActiveMeetingXML($xml)
	{
		$this->scoId = parseValue("sco-id=\"([0-9]{1,})\"",$xml);
		$this->participants = parseValue("active-participants=\"([0-9]{1,})\"",$xml);
		$this->length_minutes = parseValue("length-minutes=\"([0-9]{1,})\"",$xml);
		$this->name = parseValue("<name>(.*)</name>",$xml);
		$this->date_begin = parseValue("<date-begin>(.*)</date-begin>",$xml);
	}
}

class MyMeetings extends Action
{
	var $result = array();
	function MyMeetings($sess)
	{
		parent::action('report-my-meetings', $sess);
	}

	function run()
	{
		parent::run();
		//分解回傳的xml
		if (strpos($this->conn->HTTP_RESPONSE_BODY,'status code="ok"') !== false)
		{
			$offset = 0;
			while(1)
			{
				$str = getBetweenString($this->conn->HTTP_RESPONSE_BODY, "<sco", "</sco>", $offset);
				if (empty($str)) break;
				$this->result[] = new ActiveMeetingXML($str);
			}
		}
	}
}


//取得線上此課程的會議

function getMyMeetingList($sess, $idx)
{
	$action = new MyMeetings($sess);
	$action->run();

	$rtnArray = array();
	if (count($action->result)>0)
	{
		for($i=0, $size=count($action->result); $i<$size; $i++)
		{
			if (strpos($action->result[$i]->name, $idx) !== false)
			{
				$rtnArray[] = $action->result[$i];
			}
		}
	}
	return $rtnArray;
}?>
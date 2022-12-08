<?php

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class ActiveMeetingPresenters extends Action
{
	var $presenters;
	function ActiveMeetingPresenters($sess, $scoId)
	{
		parent::action('report-active-meeting-presenters', $sess);
		$this->addParameters('sco-id',$scoId);
	}

	function run()
	{
		parent::run();
		//分解回傳的xml
		if (strpos($this->conn->HTTP_RESPONSE_BODY,'status code="ok"') !== false)
		{
			$arr = explode('<name>',$this->conn->HTTP_RESPONSE_BODY);
			for($i=0, $size=count($arr); $i<$size; $i++)
			{
				if (($pos=strpos($arr[$i],'</name>')) !== false)
				{
					$this->presenters[] = substr($arr[$i],0, $pos);
				}
			}
		}
	}

}


?>
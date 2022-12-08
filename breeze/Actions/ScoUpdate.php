<?php
//Action : sco-update
//Updates one or more principals¡¦ permissions for one or more SCOs

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class ScoUpdate extends Action
{
	function ScoUpdate($sess, $idx, $MeetingDesc, $folder_id, $level='course')
	{
		// $idx = (strpos($idx, '_') === false)?substr($idx,-5):substr($idx,-10);
		if ($level == 'course'){
			$idx = substr($idx,-5);
		}
		
		$title = sprintf("%s-%s[%s]",$idx, $MeetingDesc, date("md H:i:s"));
		if (strlen($title) > 60)
		{
			$title = sprintf("%s-%s[%s]",$idx, substr($MeetingDesc,0,36), date("md H:i:s"));
		}
		parent::action('sco-update', $sess);
		$now = time();
		$this->addParameters('folder-id',$folder_id);
		$this->addParameters('date-begin',getISO8601_datetime($now));
		$this->addParameters('date-end',getISO8601_datetime($now+3*60*60));
		$this->addParameters('description',$MeetingDesc);
		// $this->addParameters('name',$idx.'-'.$MeetingDesc.'['.date("Ymd H:i:s").']');
		$this->addParameters('name',$title);
		$this->addParameters('type','meeting');
	}
}

?>
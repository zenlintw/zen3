<?php

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class ScoContents extends Action
{
	var $scoId;
	function &ScoContents($sess, $idx)
	{
		$this->scoId = $idx;
		parent::action('sco-contents', $sess);
		$this->addParameters('sco-id',$idx);
	}
}

class MeetingRecorderXML
{
	var $scoId = '';
	var $name = '';
	var $urlpath = '';
	var $date_begin = '';
	var $date_end = '';
	var $date_modified = '';
	var $duration = 0;

	function &MeetingRecorderXML($xml)
	{
		$offshift = 0;
		$this->scoId = getBetweenInnerString($xml,'sco-id="','"',$offshift);
		$this->name = getBetweenInnerString($xml,'<name>','</name>',$offshift);
		$this->date_begin = getBetweenInnerString($xml,'<date-begin>','</date-begin>',$offshift);
		$this->date_end = getBetweenInnerString($xml,'<date-end>','</date-end>',$offshift);
		$this->date_modified = getBetweenInnerString($xml,'<date-modified>','</date-modified>',$offshift);
		$this->duration = getBetweenInnerString($xml,'<duration>','</duration>',$offshift);
	}
}


?>
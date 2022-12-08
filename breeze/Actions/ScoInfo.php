<?php

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class ScoInfo extends Action
{
	var $scoId;
	function ScoInfo($sess, $idx)
	{
		$this->scoId = $idx;
		parent::action('sco-info', $sess);
		$this->addParameters('sco-id',$idx);
	}
	
}

?>
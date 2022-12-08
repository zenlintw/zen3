<?php

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class ScoNav extends Action
{
	var $scoId;
	function ScoNav($sess, $idx)
	{
		$this->scoId = $idx;
		parent::action('sco-nav', $sess);
		$this->addParameters('sco-id',$idx);
	}
	
}

?>
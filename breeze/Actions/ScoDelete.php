<?php

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class ScoDelete extends Action
{
	var $scoId;
	function ScoDelete($sess, $idx)
	{
		$this->scoId = $idx;
		parent::action('sco-delete', $sess);
		$this->addParameters('sco-id',$idx);
	}
	
}

?>
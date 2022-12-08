<?php

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class ScoSearch extends Action
{
	function ScoSearch($sess, $querystr)
	{
		parent::action('sco-search', $sess);
		$this->addParameters('query',$querystr);
	}
	
}

?>
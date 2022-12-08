<?php
//Action : Common Info
//Provides basic information about the current user and server.

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class CommonInfo extends Action
{
	function CommonInfo($sess)
	{
		parent::action('common-info', $sess);
	}
}


?>
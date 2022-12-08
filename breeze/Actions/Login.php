<?php

//Action : Common Info
//Provides basic information about the current user and server.

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class Login extends Action
{
	function Login()
	{
		parent::action('login');
		$this->addParameters('login',BREEZE_LOGIN);
		$this->addParameters('password',BREEZE_PASSWORD);
	}

	function run()
	{
		parent::run();
		//分解回傳的xml
		if (strpos($this->conn->HTTP_RESPONSE_BODY,'ok') !== false)
		{
			$pos1 = strpos($this->conn->HTTP_RESPONSE_HEADER,'BREEZESESSION=')+strlen('BREEZESESSION=');
			$pos2 = strpos($this->conn->HTTP_RESPONSE_HEADER,';',$pos1);
			$this->sessionId = substr($this->conn->HTTP_RESPONSE_HEADER, $pos1, $pos2-$pos1);
		}
	}
}

?>
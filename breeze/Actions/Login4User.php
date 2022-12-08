<?php

//Action : Common Info

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class Login4User extends Action
{
	function Login4User($login, $pwd)
	{
		parent::action('login');
		$this->addParameters('login',$login);
		$this->addParameters('password',$pwd);
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
		}else{
			$this->sessionId = null;
		}
	}
}

?>

<?php
//Action : permissions-update
//Updates one or more principals¡¦ permissions for one or more SCOs

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class PrincipalUpdate extends Action
{
	var $pid;		//principal Identifier
	function PrincipalUpdate($sess, $userData)
	{
		parent::action('principal-update', $sess);
		$this->addParameters('first-name',$userData['firstname']);
		$this->addParameters('last-name',$userData['lastname']);
		$this->addParameters('login',$userData['login']);
		$this->addParameters('password',$userData['password']);
		$this->addParameters('has-children',0);
		$this->addParameters('type','user');
	}
	
	function getPrincipalId()
	{
		if (ereg("principal-id=\"([0-9]{1,})\"",$this->conn->HTTP_RESPONSE_BODY, $args))
		{
			$this->pid = $args[1];
		}
		return $this->pid;
	}
}

?>
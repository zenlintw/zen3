<?php
//Action : permissions-info
//Provides information about principals and the permissions they have for a specified SCO

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class PermissionsInfo extends Action
{
	var $scoId;		//Sco Identifier
	var $statusCode = '';
	function PermissionsInfo($sess, $scoid, $userid)
	{
		parent::action('permissions-info', $sess);
		$this->scoId = $scoid;
		$this->addParameters('acl-id',$this->scoId);
		$this->addParameters('principal-id',$userid);
	}

	function run()
	{
		parent::run();
		$this->setStatusCode();
	}

	function setStatusCode()
	{
		preg_match_all('/<status code="(.*)"/',$this->conn->HTTP_RESPONSE_BODY,$matches);
		$this->statusCode = trim($matches[1][0]);
	}
}

?>

<?php
//Action : permissions-update
//Updates one or more principals¡¦ permissions for one or more SCOs

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class PermissionsUpdate extends Action
{
	var $scoId;		//Sco Identifier
	function PermissionsUpdate($sess, $scoid, $userid, $acl)
	{
		parent::action('permissions-update', $sess);
		$this->scoId = $scoid;
		$this->addParameters('acl-id',$this->scoId);
		$this->addParameters('principal-id',$userid);
		$this->addParameters('permission-id',$acl);
	}
}

?>
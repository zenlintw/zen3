<?php
//Action : permissions-update

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class GroupMember extends Action
{
	var $scoId;		//Sco Identifier
	function GroupMember($sess, $gid, $pid, $bool="true")
	{
		parent::action('group-membership-update', $sess);
		$this->addParameters('group-id',$gid);
		$this->addParameters('principal-id',$pid);
		$this->addParameters('is-member',$bool);
	}
}

?>
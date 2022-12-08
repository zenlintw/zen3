<?php

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class UpdatePwd extends Action
{
	var $userid;
	function UpdatePwd($sess, $uid, $pwd, $old_pwd='')
	{
		$this->userid = $uid;
		parent::action('user-update-pwd', $sess);
		$this->addParameters('user-id',$uid);
		$this->addParameters('password',$pwd);
		$this->addParameters('password-verify',$pwd);
		if (!empty($old_pwd)){
			$this->addParameters('password-old',$old_pwd);
		}
	}
	
}

?>
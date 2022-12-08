<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(BREEZE_PHP_DIR . '/Actions/SessionManager.php');
	require_once(BREEZE_PHP_DIR . '/Actions/UpdatePwd.php');
	
	function getOldBreezePwd($user)
	{
		list($pwd) = dbGetStSr('WM_all_account', 'password', "username='{$user}'", ADODB_FETCH_NUM);
		if (empty($pwd)) return '';
		return substr($pwd,0,10);
	}
	
	function getUserEmail($user)
	{
		global $_SERVER;
		return $user.'@'.$_SERVER['SERVER_NAME'];
	}
	
	function doUpdateBreezePwd($user, $pwd)
	{
		$old_pwd = getOldBreezePwd($user);
		if (empty($old_pwd)) return false;
		$sess = getEnableSessionId();
		if (empty($sess)) return false;
		
		$pid = 0;
		isUserExistedBreezeServer($sess, getUserEmail($user), $pid);
		if ($pid == 0) return true;		//¦bbreeze©|µL±b¸¹
		$action = new UpdatePwd($sess, $pid, $pwd, $old_pwd);
		$action->run();
	}

	function doUpdateBreezePwdByAdmin($user, $pwd)
	{
		$sess = getEnableSessionId();
		if (empty($sess)) return false;
		
		$pid = 0;
		isUserExistedBreezeServer($sess, getUserEmail($user), $pid);
		if ($pid == 0) return true;		//¿breeze¿¿¿¿
		$action = new UpdatePwd($sess, $pid, $pwd);
		$action->run();
	}
?>

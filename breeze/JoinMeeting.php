<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(BREEZE_PHP_DIR . '/Actions/SessionManager.php');
	require_once(BREEZE_PHP_DIR . '/Actions/PermissionsUpdate.php');
	require_once(BREEZE_PHP_DIR . '/Actions/UpdatePwd.php');
	
#========== function ============
	/**
		判別目前session的使用者是否有教師/助教權限
		@return bollean
	*/
	function isTeacherPermission()
	{
		global $sysSession, $sysRoles;
		list($role) = dbGetStSr('WM_term_major','role',"username='{$sysSession->username}' and course_id='{$sysSession->course_id}'", ADODB_FETCH_NUM);
		return (intval($role) >= $sysRoles['assistant']);
	}
#========== Main ============
//1. Get Admin Session
	$sess = getEnableSessionId();
	if (empty($sess)) die('errcode:001');
//2. Get Meeting Urlpath
	$urlpath = $_GET['urlpath'];
	$scoid = $_GET['scoid'];
	
	if (empty($scoid)) die("errcode: 002; errmsg: Can't get urlpath");
	if (empty($urlpath)) die("errcode: 002; errmsg: Can't get urlpath");
//3. Get User Data
	$userData = getUserData($sysSession->username);
	if (is_null($userData)) die("errcode: 003; errmsg: Can't get LMS User Data");
	$email = $sysSession->username.'@'.$_SERVER['SERVER_NAME'];
	$userData['email'] = $email;
//4. Query that is User existed in Breeze server
	$pid = 0;
	if (!isUserExistedBreezeServer($sess, $email, $pid))
	{
		$pid = AddBreezePrincipal($sess,$userData);
	}
	
	if ($pid == 0)
	{
		$pid = AddBreezePrincipal($sess,$userData);
	}
	
	if ($pid == 0) die("errcode: 003; errmsg: Can't get user Principal-id");
//5. 假如登入者是教師/助教，則給予presenter的權限
if (isTeacherPermission())
{
	$action2 = new PermissionsUpdate($sess, $scoid, $pid,'presenter');
	$action2->run();
}else{
	$action2 = new PermissionsUpdate($sess, $scoid, $pid,'view');
	$action2->run();
}
//6. 取得此位使用者login的session
	$userBreezeSess = buildUserSession($email,substr($userData['password'],0,10));
	//無法取得Breeze Session, 有可能密碼是錯誤的，重置密碼
    if (empty($userBreezeSess)) {
        $actionUpdPwd = new UpdatePwd($sess, $pid, substr($userData['password'],0,10));
		$actionUpdPwd->run();
		
		//更改密碼後再取一次Breeze Session
		$userBreezeSess = buildUserSession($email,substr($userData['password'],0,10));
    }
	
	$url = sprintf("http://%s/%s/?session=%s",BREEZE_SERVER_ADDR,$urlpath,$userBreezeSess);
	
//7. 更新討論次數
dbSet('WM_term_major','dsc_times=dsc_times+1',"username='{$sysSession->username}' and course_id='{$sysSession->course_id}'");

?>
<html>
<head>
<script language="javascript">
	function init()
	{
		var url = "<? echo $url?>";
		this.document.location.href=url;
	}
</script>
</head>
<body onload="init();">
<h3>會議進行中...</h3>
</body>
</html>

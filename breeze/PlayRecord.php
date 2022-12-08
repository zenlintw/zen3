<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(BREEZE_PHP_DIR . '/Actions/SessionManager.php');
	require_once(BREEZE_PHP_DIR . '/Actions/PermissionsInfo.php');
	require_once(BREEZE_PHP_DIR . '/Actions/PermissionsUpdate.php');
	require_once(BREEZE_PHP_DIR . '/Actions/UpdatePwd.php');

#========== function ============
#========== Main ============
//1. Get Admin Session
	$sess = getEnableSessionId();
	if (empty($sess)) die("errcode:001");
//2. Get Meeting Urlpath
	$urlpath = $_GET['urlpath'];
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
	if ($pid == 0) die("errcode: 003; errmsg: Can't get user Principal-id");

	$infoAction = new PermissionsInfo($sess, $_GET['scoId'], $pid);
	$infoAction->run();
	if (strcmp($infoAction->statusCode, 'no-data') == 0)
	{
		$updateAction = new PermissionsUpdate($sess, $_GET['scoId'], $pid,'view');
		$updateAction->run();
	}

//5. ���o����ϥΪ�login��session
	$userBreezeSess = buildUserSession($email,substr($userData['password'],0,10));

	//�L�k���oBreeze Session, ���i��K�X�O���~���A���m�K�X
    if (empty($userBreezeSess)) {
        $actionUpdPwd = new UpdatePwd($sess, $pid, substr($userData['password'],0,10));
		$actionUpdPwd->run();
		
		//���K�X��A���@��Breeze Session
		$userBreezeSess = buildUserSession($email,substr($userData['password'],0,10));
    }
	

$url = sprintf("http://%s/%s/?session=%s",BREEZE_SERVER_ADDR,$urlpath,$userBreezeSess);
?>
<html>
<head>
<script language="javascript">
	function init()
	{
		var url = "<?php echo $url?>";
		this.document.location.href=url;
	}
</script>
</head>
<body onload="init();">
<h3>���v�ɼ���...</h3>
</body>
</html>
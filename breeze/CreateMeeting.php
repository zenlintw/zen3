<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/webmeeting/global.php');
	require_once(BREEZE_PHP_DIR . '/Actions/SessionManager.php');
	require_once(BREEZE_PHP_DIR . '/Actions/ScoUpdate.php');
	require_once(BREEZE_PHP_DIR . '/Actions/PermissionsUpdate.php');
	require_once(BREEZE_PHP_DIR . '/Actions/UpdatePwd.php');
#========== function ============

#========= Main =================
//1. Get Admin Session
	$sess = getEnableSessionId();
	if (empty($sess)) die("errcode:001");
//2. Get User Data
	$userData = getUserData($_POST['CU_Teacher_ID']);
	if (count($userData) == 0) die("errcode: 002; errmsg: Can't get LMS User data");
	$email = $_POST['CU_Teacher_ID'].'@'.$_SERVER['SERVER_NAME'];
	$userData['email'] = $email;
//3. Query that is User existed in Breeze server
	$pid = 0;
	if (!isUserExistedBreezeServer($sess, $email, $pid))
	{
		$pid = AddBreezePrincipal($sess,$userData);
	}
	if ($pid == 0) die("errcode: 003; errmsg: Can't get user Principal-id");

//4. 取得此位使用者login的session
	$userBreezeSess = buildUserSession($email,substr($userData['password'],0,10));
    //無法取得Breeze Session, 有可能密碼是錯誤的，重置密碼
    if (empty($userBreezeSess)) {
        $actionUpdPwd = new UpdatePwd($sess, $pid, substr($userData['password'],0,10));
		$actionUpdPwd->run();
		
		//更改密碼後再取一次Breeze Session
		$userBreezeSess = buildUserSession($email,substr($userData['password'],0,10));
    }

$extra = '';		//記錄在WM_chat_mmc資料表的extra欄位內，說明是何種型態的breeze meeting
if ($_POST['breeze_meetype'] == '1')
{
	$action1 = new ScoUpdate($sess,$_POST['CUID'],$_POST['meetingTitle'],BREEZE_WM_MEETING_FOLDER_ID);
	$extra = 'temporary';
}else if ($_POST['breeze_meetype'] == '2'){
	$action1 = new ScoUpdate($sess,$_POST['CUID'],$_POST['meetingTitle'],BREEZE_WM_MEETING_FOLDER_ID1);
	$extra = 'eternal';
}
$action1->run();

if (ereg("sco-id=\"([0-9]{1,})\"",$action1->conn->HTTP_RESPONSE_BODY, $args))
{
	$scoId = $args[1];
	if (ereg("<url-path>/(.*)/</url-path>",$action1->conn->HTTP_RESPONSE_BODY, $args))
	{
		$urlpath = $args[1];
	}
	
}else{
	die("fail to update sco");
}

//設定為開放的會議
	$action2 = new PermissionsUpdate($sess, $scoId, 'public-access','view-hidden');
	$action2->run();


//設定使用者為presenter
	$action2 = new PermissionsUpdate($sess, $scoId, $pid,'presenter');
	$action2->run();

//echo "200 ok!\n";
//echo "scoId={$scoId}\n";
//echo "urlpath={$urlpath}\n";

if ($special == 2)
{
	$url = 'MeetingManager.php';
}else{
	buildMeetingChatroom($sysSession->course_id, $_POST['meetingTitle'], $scoId.':'.$urlpath, $sysSession->username, 'breeze', $extra);

	$url = sprintf("http://%s/%s/?session=%s",BREEZE_SERVER_ADDR,$urlpath,$userBreezeSess);	
	$msg = '會議進行中...';
}


?>
<html>
<head>
<script language="javascript">
	function init()
	{
		var url = "<? echo $url?>";
		this.document.location.href=url;
<?php
		if ($special == 2)
		{
			if ($language == 'en')
			{
				echo 'alert("Success to create breeze meeting.");'."\r\n";
			}else{
				echo 'alert("建立會議完成！");'."\r\n";
			}
		}
?>
	}
</script>
</head>
<body onload="init();">
<h3><? echo $msg; ?></h3>
</body>
</html>

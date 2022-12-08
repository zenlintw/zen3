<?php

require_once(BREEZE_PHP_DIR . '/Actions/CommonInfo.php');
require_once(BREEZE_PHP_DIR . '/Actions/Login.php');
require_once(BREEZE_PHP_DIR . '/Actions/PrincipalList.php');
require_once(BREEZE_PHP_DIR . '/Actions/PrincipalUpdate.php');
require_once(BREEZE_PHP_DIR . '/Actions/Login4User.php');
require_once(BREEZE_PHP_DIR . '/Actions/GroupMember.php');
require_once(sysDocumentRoot . '/lib/username.php');

function isBreezeServerLive()
{
	$fp = fsockopen(BREEZE_SERVER_ADDR,80,$errno, $errstr,5);
	if (!$fp) return false;
	return true;
}

//呼叫此函式取得可用的Session
function getEnableSessionId()
{
	if (!isBreezeServerLive()) echo "can't connect breeze server";
	$sess = '';
	if (!isValidedSession($sess))
	{
		updateSessionFile(BREEZE_LOGIN);
		$vals = getSessionValue(BREEZE_LOGIN);
		$sess = $vals['session'];
	}
	return $sess;
}

function isValidedSession(&$rtn)
{
	$rtn = '';
	$arr = getSessionValue(BREEZE_LOGIN);
	if ( intval(time()) - intval($arr['touch']) > 20*60)
	{
		return false;
	}
	$action = new CommonInfo($arr['session']);
	$action->run();
	$str = $action->conn->HTTP_RESPONSE_BODY;
	if (strpos($str,"<login>".BREEZE_LOGIN."</login>") === FALSE) return false;
	touchSessionFile(BREEZE_LOGIN, $arr['session']);
	$rtn = $arr['session'];
	return true;
}

function touchSessionFile($login, $sessionId)
{
	$dir = BREEZE_SESSION_DIR;
    if (!is_writable($dir)) {
        $dir = sysDocumentRoot.'/base/10001/sessionFiles';
    }
	$fname = $dir ."/". $login;
	$fp = @fopen($fname, "w+");
	if (!$fp){
		ErrorLog('30402: can\'t create session File');
		return false;
	}
	fputs($fp, sprintf("%s\t%s",time(),$sessionId));
	fclose($fp);
	
	return true;
}

function updateSessionFile($login)
{

    if (is_writable(BREEZE_PHP_DIR)) {
        $dir = BREEZE_SESSION_DIR;
    }else{
        $dir = sysDocumentRoot.'/base/10001/sessionFiles';
    }
	if (!file_exists($dir))
	{
		mkdir($dir,0755) or ErrorLog('30401:can\'t create directory');
	}
	
	$login_action = new Login();
	$login_action->run();
	if (strlen($login_action->sessionId) == 0)
	{
		echo "errmsg: fail to login, and get sessionId";
	    return false;
	}

	$fname = $dir ."/". $login;
	$fp = @fopen($fname, "w+");
	if (!$fp){
		ErrorLog('30402: can\'t create session File');
		return false;
	}
	fputs($fp, sprintf("%s\t%s",time(),$login_action->sessionId));
	fclose($fp);
	
	return true;
}

function getSessionValue($login)
{
	$rtnArray = array('touch'=>'','session'=>'');
	$dir = BREEZE_SESSION_DIR;
    if (!is_writable($dir)) {
        $dir = sysDocumentRoot.'/base/10001/sessionFiles';
    }
	$fname = $dir ."/". $login;
	if (file_exists($fname))
	{
	    $fp = @fopen($fname, "r");
    	$str = fgets($fp, 1024);
    	list($rtnArray['touch'], $rtnArray['session']) = explode("\t", $str);
	    fclose($fp);
	}
    return $rtnArray;
}

//取得使用者資料
function getUserData($user)
{
        global $_SERVER;
        $data = getUserDetailData($user);
        if (empty($data['password'])) $data['password'] = 'sun@breeze';
        return $data;
}

//判斷使用者是否已有Breeze Server Account
function isUserExistedBreezeServer($sess, $email, &$pid)
{
	$action = new PrincipalList($sess);
	$action->run();
	$pid = $action->getSomeonePid($email);
	return $action->isUserIncluded($email);
}

//加一個帳號for LMS
function AddBreezePrincipal($sess, $obj)
{
	if (empty($obj['password'])) $obj['password']= 'sun@breeze';
	$userData = 
		array(
			"firstname" => $obj['realname'],
			"lastname"=>$obj['username'],
			"login"=>$obj['email'],
	        "password"=>substr($obj['password'],0,10)
        );
    $action = new PrincipalUpdate($sess,$userData);
    $action->run();
    $pid = $action->getPrincipalId();
    //add user to group
    if (intval(BREEZE_USER_GROUP) > 0)
    {
    	$action1 = new GroupMember($sess,BREEZE_USER_GROUP,$pid,"true");
    	$action1->run();
    }
    
    return $pid;
}

//建立使用者登入的Session
function buildUserSession($login,$pwd)
{
	$action = new Login4User($login,$pwd);
	$action->run();
	return $action->sessionId;
}
?>
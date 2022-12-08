<?php 
/**
 * 驗證SessionId,順便設Cookie
 */
require_once(dirname(__FILE__) .'/initialize.php');    
require_once(dirname(__FILE__) .'/config.php');
require_once(dirname(__FILE__) .'/lib/common.php');

// main
$ticket = trim($_GET['ticket']);
if (($sessionData = getSessionData($ticket)) === FALSE) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$userData = getUserData($sessionData['username']);
header('Content-Type: text/html;charset=UTF-8');    

// 設定回傳給前端的Cookies 值
setcookie('idx', $ticket, 0, '/');
echo sprintf('hello,%s', $userData['username']);

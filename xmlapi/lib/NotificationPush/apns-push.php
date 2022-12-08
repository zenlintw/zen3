<?php

define('PATH_LIB', dirname(__FILE__)."/../");
require('push-config.php');
require('Apns.php');
$data = json_decode(urldecode($argv[1]), true);
$count = App_NotificationPush_Apns::pushMessage($data['iosDevices'], $data['alert'], $data['badge'], $data['data']);

// 這裡必須印出數字，shell script才能接收到內容
echo $count;

?>

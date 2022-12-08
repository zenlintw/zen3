#!/usr/local/bin/php
<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once('push-config.php');
require_once(dirname(__FILE__) . '/../JsonUtility.php');
require_once('Fcm.php');

if (isset($argv[1])) {
    $token[] = $argv[1];
} else {
    die('MISS TOKEN');
}

$jsonHandler = new JsonUtility();

$dataI = array (
    "title" => "DATAI: FCM TEST TITLE",
    "body" => "DATAI: This is FCM TEST"
);

$dataII = array (
    "source" => 'NEWS',
    "data" => array ("source" => "NEWS"),
    "title" => "DATAII: FCM TEST TITLE",
    "body" => "DATAII: This is FCM TEST",
    "message" => "DATAII: FCM TEST MESSAGE",
    "type" => "FCM"
);

$fcmPushHandler = new App_NotificationPush_Fcm();

return $fcmPushHandler->send_notification_tokens($token, $dataI, $dataII);

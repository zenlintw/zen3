<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/app_server_push.php');
require_once(sysDocumentRoot . '/xmlapi/config.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/push-config.php');
require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');
require_once(sysDocumentRoot . '/xmlapi/lib/lang.php');
require_once(sysDocumentRoot . '/xmlapi/lib/common.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/NotificationDatabase.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DatabaseHandler.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DoPush.php');

$needEchoResponse = false;
$_tokenLimit = 50;  // 推播token個數(分批限制)
// 預設來源為其他推播功能
$pushSource = 'COURSE';

$dbHandler = new DatabaseHandler();
$doPushHandler = new DoPush();
$jsonHandler = new JsonUtility();

if (isset($pushData)) {
    $pushObject = $jsonHandler->decode($pushData);
} else {
    // 其他外部呼叫
    $pushPostObject = $jsonHandler->decode(file_get_contents('php://input'));
    $pushObject = $pushPostObject['data'];

    $needEchoResponse = true;
}

if ($pushObject['alert'] === '' || $pushObject['channel'] === '') {
    exit();
}

$alert = $pushObject['alert'];
$content = $pushObject['content'];
$sender = $pushObject['sender'];
$channels = $pushObject['channel'];
switch ($pushObject['alertType']) {
case 'NEWS':
    $alertType = 'NEWS';
    break;
case 'LIVE':
    $alertType = 'LIVE';
    break;
default:
    $alertType = 'COURSE';
}
$alertTypeInDB = $pushObject['alertType'];
$messageID = $pushObject['messageID'];
$writeInDB = (isset($pushObject['writeInDB'])) ? $pushObject['writeInDB'] : true;

$extraData = array('source' => $alertType);

$totalDevices = $dbHandler->getDevices($channels);

if (COUNT($totalDevices) > 0) {
    foreach ($totalDevices as $type => $devices) {
        switch ($type) {
        case 'APNS': // APNS
            $loops = ceil(count($devices) / $_tokenLimit);
            for ($i = 0; $i < $loops; $i++) {
                $pushDevices = array_slice($devices, $i * $_tokenLimit, $_tokenLimit);
                $APNSResult = $doPushHandler->APNSPush($alert, $content, $devices, $extraData);
                if (COUNT($APNSResult) > 0 && $writeInDB) {
                    $dbHandler->pushMessageIntoDB($sender, $alert, $content, $alertType, $messageID, $APNSResult);
                }
            }
            break;
        case 'GCM': // GCM可透過FCM直接推播
        case 'FCM': // FCM
            $loops = ceil(count($devices) / $_tokenLimit);
            for ($i = 0; $i < $loops; $i++) {
                $pushDevices = array_slice($devices, $i * $_tokenLimit, $_tokenLimit);
                $GoogleResult = $doPushHandler->FCMPush($alert, $content, $pushDevices, $extraData);
                if (COUNT($GoogleResult) > 0 && $writeInDB) {
                    $dbHandler->pushMessageIntoDB($sender, $alert, $content, $alertTypeInDB, $messageID, $GoogleResult);
                }
            }
            break;
        case 'JPUSH':
            break;
        }
    }
}

if ($needEchoResponse) {
    $response = $jsonHandler->encode($response);
    echo $response;
}
<?php
    require_once(sysDocumentRoot . '/xmlapi/config.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/push-config.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/lang.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/common.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/NotificationDatabase.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Apns.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Gcm.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Jpush.php');

    $sysSession->cur_func = 999999004;

    $alert = $honguMSG['msg_app_news_published'][$sysSession->lang] . $_POST['subject'];
    $badge = 1;
    $data = null;

    $limit = 999;
    $iosTotalDevices = array();
    $androidTotalDevices = array();
    $jpushTotalDevices = array();

    $honguConn = NotificationDatabase::connect();
    for ($i = 0; $i < count($channels); $i++) {
        $channel = $channels[$i];
        $iosTempDevices = NotificationDatabase::getPushDevices($channel, 'IOS', 1);
        $androidTempDevices = NotificationDatabase::getPushDevices($channel, 'ANDROID', 2);
        $jpushTempDevices = NotificationDatabase::getPushDevices($channel, 'ANDROID', 3);

        $iosTotalDevices = array_merge($iosTotalDevices, $iosTempDevices);
        $androidTotalDevices = array_merge($androidTotalDevices, $androidTempDevices);
        $jpushTotalDevices = array_merge($jpushTotalDevices, $jpushTempDevices);
    }

    // 透過 APNS 推送 IOS 訊息
    $iosSuccessCount = 0;
    if (count($iosTotalDevices) > 0) {
        $loops = ceil(count($iosTotalDevices) / $limit);
        for ($i = 0; $i < $loops; $i++) {
            $iosDevices = array_slice($iosTotalDevices, $i * $limit, $limit);
            $iosSuccessCount = $iosSuccessCount + App_NotificationPush_Apns::pushMessage($iosDevices, $alert, $badge, $data);
        }
    }

    // 透過 GCM 推送 ANDROID 訊息
    $androidSuccessCount = 0;
    if (count($androidTotalDevices) > 0) {
        $loops = ceil(count($androidTotalDevices) / $limit);
        for ($i = 0; $i < $loops; $i++) {
            $androidDevices = array_slice($androidTotalDevices, $i * $limit, $limit);
            $androidSuccessCount = $androidSuccessCount + App_NotificationPush_Gcm::pushMessage($androidDevices, $alert, $badge, $data);
        }
    }

    // 透過 Jpush 推送 ANDROID 訊息
    $jpushSuccessCount = 0;
    if (count($jpushTotalDevices) > 0) {
        $loops = ceil(count($jpushTotalDevices) / $limit);
        for ($i = 0; $i < $loops; $i++) {
            $jpushDevices = array_slice($jpushTotalDevices, $i * $limit, $limit);
            $androidJpushSuccessCount = $jpushSuccessCount + App_NotificationPush_Jpush::pushMessage($jpushDevices, $alert, $badge, $data);
        }
    }

    $msg = 'Success: iOS=' . $iosSuccessCount . '/ Android=' . $androidSuccessCount . '/ jPush=' . $jpushSuccessCount;

    appSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], $msg, $sysSession->username);

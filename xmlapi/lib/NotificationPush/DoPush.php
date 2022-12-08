<?php
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/push-config.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Apns.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Gcm.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Jpush.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Fcm.php');

class DoPush {

    var $_tokenLimit = 50;  // 推播token個數(分批限制)

    /**
     * APNS PUSH
     *
     * @param string $alert 推播顯示訊息
     * @param string $content 推播內容
     * @param array $devices 欲推的token
     * @param array $data 其他欲帶資料
     **/
    function APNSPush ($alert, $content, $devices, $data) {
        $apnsPushHandler = new App_NotificationPush_Apns();

        if (COUNT($devices) <= 0) {
            return array();
        }

        $tokens = array();

        // 因為傳過來的device info為username跟token，所以要單獨取出token
        foreach($devices as $token => $userDevice) {
            $tokens[] = $userDevice['token'];
        }

        $loops = ceil(count($tokens) / $this->_tokenLimit);

        for ($i = 0; $i < $loops; $i++) {
            $iosDevices = array_slice($tokens, $i * $this->_tokenLimit, $this->_tokenLimit);
            if (file_exists(PHP5_EXEC_URL) && (!function_exists('version_compare') || version_compare( phpversion(), '5', '<' ))) {
                $ary = array(
                    'iosDevices' => $iosDevices,
                    'alert' => $alert,
                    'badge' => 1,
                    'data' => $data,
                );
                $sendData = urlencode(json_encode($ary));
                shell_exec('sh ' . APNS_PUSH_SCRIPT . " $sendData");
            } else {
                $apnsPushHandler->pushMessage($iosDevices, $alert, $content, 1, $data);
            }
        }

        return $this->APNSResultHandler($devices);
    }

    /**
     * GCM PUSH (統一由FCM推播，故此FUNCTION廢止)
     * @param string $alert 推播顯示訊息
     * @param string $content 推播內容
     * @param array $tokens 欲推的token
     * @param array $data 其他欲帶資料
     **/
    function GCMPush ($alert, $content, $tokens, $data) {
//        $gcmPushHandler = new App_NotificationPush_Gcm();
//
//        $loops = ceil(count($tokens) / $this->_tokenLimit);
//        for ($i = 0; $i < $loops; $i++) {
//            $androidDevices = array_slice($tokens, $i * $this->_tokenLimit, $this->_tokenLimit);
//            $gcmPushHandler->pushMessage($androidDevices, $alert, $content, 1, $data);
//        }
    }

    /**
     * FCM PUSH
     * @param string $alert 通知欄標題
     * @param string $content 通知欄內容
     * @param array $devices 接收者info (username, token)
     * @param array $extraData 其他資訊
     *
     * @return array 推播後回傳的message資訊
     **/
    function FCMPush ($alert, $content, $devices, $extraData) {
        if (COUNT($devices) <=0) {
            return array();
        }

        $tokens = array();

        // 因為傳過來的device info為username跟token，所以要單獨取出token
        foreach($devices as $token => $userDevice) {
            $tokens[] = $userDevice['token'];
        }

        // APP外的推播顯示
        $outsideData = array (
            "title" => $alert,
            "body" => $content,
            "sound" => "default"
        );
        // APP內的推播顯示
        $insideData = array (
            "source" => $extraData['source'],
            "data" => $extraData['source'],
            "title" => $alert,
            "body" => $content,
            "message" => $alert,
            "type" => "FCM"
        );

    	$fcmPushHandler = new App_NotificationPush_Fcm();

    	$result = $this->FCMResultHandler($fcmPushHandler->send_notification_tokens($tokens, $outsideData, $insideData), $devices);

    	return $result;
    }

    /**
     * 處理APNS推播結果
     *
     * @param array $devices user device info (username, token)
     * @return array 結果
     **/
    function APNSResultHandler ($devices) {
        $result = array();

        foreach ($devices as $token => $userDevice) {
            // APNS推播沒有message id，所以隨機產生一組
            $uniqueMessageID = 'APNS-' . $userDevice['username'] . '-' . uniqid(rand());

            $userResult = array(
                'message' => $uniqueMessageID,
                'token' => $userDevice['token'],
                'username' => $userDevice['username']
            );

            $result[] = $userResult;
        }

        return $result;
    }

    function FCMResultHandler ($FCMResult, $devices) {
        $returnResult = array();

        $result = (object) $FCMResult;

        if (COUNT($result->success)) {
            foreach ($result->success as $key => $successItem) {
                $successItemInfo = (object) $successItem;
                $successResult = array(
                    'message' => $successItemInfo->message_id,
                    'token' => $successItemInfo->token,
                    'username' => $devices[$successItemInfo->token]['username']
                );
                $returnResult[] = $successResult;
            }
        }

        if (COUNT($result->failure)) {
            $dbHandler = new DatabaseHandler();
            foreach ($result->failure as $key => $failureItem) {
                $failureItemInfo = (object) $failureItem;
		
                $failureResult = array(
                    'message' => $failureItemInfo->error,
                    'token' => $failureItemInfo->token,
                    'username' => $devices[$failureItemInfo->token]['username']
                );
                $returnResult[] = $failureResult;

                // 失敗就直接將device資訊設為捨棄
                $dbHandler->abandonDevice($failureItemInfo->token);
            }
        }

        return $returnResult;
    }

    function JPUSH () {
        // 尚未實作，有必要再來處理
        return;
    }
}

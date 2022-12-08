<?php
/**
 * 實作  Apple Push Notification Service 訊息推播
 */
class App_NotificationPush_Apns {
    /**
     * 推撥訊息到一群裝置中
     * 
     * @param array $devices 裝置資訊
     * @param string $title 要推播的訊息標題
     * @param string $content 要推播的訊息內容
     * @param integer $badge 推送圖示號碼
     * @param array $data 其他資訊
     * @return integer 推撥成功的裝置數
     */
    function pushMessage ($devices, $title, $content, $badge=1, $data = null) {
        $certificateFile = APNS_DIS_PEM;
        $pushServer = APNS_PRODUCTION_PUSH_GATEWAY;

        // 檢查憑證檔案
        if (!file_exists($certificateFile)) {
            return 0;
        }
        // create ssl socket
        $streamContext = stream_context_create();
        stream_context_set_option($streamContext, 'ssl', 'local_cert', $certificateFile);
        $fp = stream_socket_client(
            $pushServer,
            $error,
            $errorStr,
            100,
            STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
            $streamContext
        );

        // make payload
        $payloadObject = array(
            'aps' => array(
                'alert' => array(
                    'title' => $title,
                    'body' => $content
                ),
                'sound' => 'default',
                'badge' => $badge
            ),
            'data' => $data
        );
        $payload = json_encode($payloadObject);

        // push notification
        $count = 0;
        foreach ($devices as $pushDevice) {
            if ($pushDevice === 'DESKTOP_DEVICE_TOKEN') {
                // 桌面環境不推播
                continue;
            }

            //期限一個小時
            $expire = time() + 3600;
            $id = time();

            // 建立 傳送資料
            if ($expire) {
                //Enhanced notification format
                $binary  = pack('CNNnH*n', 1, $id, $expire, 32, $pushDevice, strlen($payload)).$payload;
            } else {
                //Simple notification format
                $binary  = pack('CnH*n', 0, 32, $pushDevice, strlen($payload)).$payload;
            }
            fwrite($fp, $binary);
            $count++;
        }

        // 關閉連線
        if (isset($fp) && !is_null($fp)) {
            fclose($fp);
            unset($fp);
        }

        return $count;
    }
}

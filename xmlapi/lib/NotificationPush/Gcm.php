<?php
/**
 * 實作  Google Cloud Message Service 訊息推播
 */
class App_NotificationPush_Gcm {
    /**
     * 推撥訊息到一群裝置中
     * 
     * @param array $devices 裝置資訊
     * @param string $title 要推撥的訊息標題
     * @param string $content 要推撥的訊息內容
     * @param integer $badge 推送圖示號碼
     * @param array $data 其他資訊
     * @return integer 推撥成功的裝置數
     */
    function pushMessage ($devices, $title, $content, $badge=1, $data = null) {
        // 產生要傳遞的訊息
        $payload = array(
            'registration_ids' => $devices,
            'notification' => array(
                'body' => $content,
                'title' => $title
            ),
            'data' => array(
                'environment' => 'PRODUCTION',
                'title' => $title,
                'badge' => $badge,
                'body' => $content,
                'data' => $data
            )
        );

        // 建立認證 Header 與 content-type
        $headers = array(
            'Authorization: key=' . GCM_TOKEN,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, GCM_PUSH_GATEWAY);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        // Execute post
        $result = curl_exec($ch);
        if ($result === false) {
            curl_close($ch);
            return 'fail';
        }

        // Close connection and return
        curl_close($ch);
        $obj = json_decode($result);
        return $obj->success;
    }
}

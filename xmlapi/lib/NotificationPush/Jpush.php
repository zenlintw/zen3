<?php
/**
 * 實作極光訊息推播
 */
class App_NotificationPush_Jpush {
    function pushMessage ($devices, $message, $badge=1, $data=null) {
        //最多一次1000個devicetoken
        $deviceIds = array();
        foreach ($devices as $pushDevice) {
            array_push($deviceIds, $pushDevice['devicetoken']);
        }
        
        // 沒有要推送的裝置就回傳 0
        if (count($deviceIds) === 0) {
            return 0;
        }

        $appKey = JPUSH_APPKEY;
        $masterSecret = JPUSH_MASTER_SECRET;
        $base64=base64_encode("$appKey:$masterSecret");
        $header=array("Authorization:Basic $base64","Content-Type:application/json");
        
        
        $payload = array();
        $payload['platform'] = 'all';          
        $payload['audience'] = array("registration_id" => $deviceIds);
        
        $payload['message'] = array(
                "msg_content"=>$message,
                "title"=>"",
                "content_type"=>1,
                "extras"=>array("data"=>$data)
        );
        $param = json_encode($payload);
       
        if (empty($param)) {
            return false;
        }
        $curlPost = $param;
        $ch = curl_init();                                      
        curl_setopt($ch, CURLOPT_URL, JPUSH_PUSH_GATEWAY);
        curl_setopt($ch, CURLOPT_HEADER, 0);                    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                
        curl_setopt($ch, CURLOPT_POST, 1);                      
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $res = curl_exec($ch);                                 
        curl_close($ch);
        
        if ($res) {
            return $res;
        } else {
            return false;
        }
        
    }
}

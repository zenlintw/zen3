<?php
class App_NotificationPush_Fcm {
    function __construct() {

        $this->debug = 0;
    }

    // 傳送推播訊息
    function send_notification($postData) {

        $result = $this->curl_request(
            'post',
            SEND_NOTIFICATION_URL,
            array('Authorization: key='.AUTHORIZATION_KEY, 'Content-Type: application/json'),
            $postData
        );

        return $result;
    }

    // 傳送推播訊息，可指定user token、group token、/topics/{topic name}
    function send_notification_to($to, $notification, $data) {
        $postData = array(
            'to' => $to,
            'notification' => $notification
        );
        if (!empty($data)) {
            $postData['data'] = $data;
        }
        $result = $this->send_notification($postData);

        if (array_key_exists('results', $result) == true) {
            $returnObject->multicast_id = $result->multicast_id;
            $returnObject->code = $result->code;

            $messageInfo = $result->results[0];
            $messageInfo->token = $to;
            if (array_key_exists('error', $result->results) == false) {
                $returnObject->success[] = $messageInfo;
            } else {
                $returnObject->failure[] = $messageInfo;
            }
        } else {
            $returnObject = $result;
        }
        if ($this->debug == 1) {
            echo "\n return array \n";
            print_r($returnObject);
        }
        return $returnObject;
    }

    // 傳送推播訊息給指定的topic
    function send_notification_topic($topicName, $notification, $data) {
        $postData = array(
            'to' => '/topics/'.$topicName,
            'notification' => $notification
        );
        if (!empty($data)) {
            $postData['data'] = $data;
        }
        $this->send_notification($postData);
    }

    // 傳送推播訊息，可同時指定多個token
    function send_notification_tokens($registration_ids, $notification, $data) {
        $postData = array(
            'registration_ids' => $registration_ids,
            'notification' => $notification
        );
        if (!empty($data)) {
            $postData['data'] = $data;
        }

        $result = $this->send_notification($postData);

        $returnObject->multicast_id = $result->multicast_id;
        $returnObject->code = $result->code;
        foreach ($registration_ids as $key => $ids) {
            $messageInfo = $result->results[$key];
            $messageInfo->token = $registration_ids[$key];
            if (array_key_exists('error', $result->results[$key]) == false) {
                $returnObject->success[] = $messageInfo;
            } else {
                $returnObject->failure[] = $messageInfo;
            }
        }
        if ($this->debug == 1) {
            echo "\n return array \n";
            print_r($returnObject);
        }
        return $returnObject;
    }

    // 透過HTTP V1協定傳送推播訊息
    function send_notification_v1($postData) {
        $oauthToken = $this->getOauthToken();
        $url = str_replace('%project_id%', PROJECT_ID, SEND_NOTIFICATION_V1_URL);
        $result = $this->curl_request(
            'post',
            $url,
            array('Authorization: Bearer '.$oauthToken, 'Content-Type: application/json'),
            $postData
        );
        return $result;
    }

    // 透過curl發送request
    function curl_request($requestMethod, $url, $headers, $postData = array()) {
        $curlUsePost = ($requestMethod == 'post' ? true : false);

        // 透過curl發送request
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, $curlUsePost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false );
        if (!empty($postData)) {
            curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($postData));
        }
        
        $result = curl_exec($ch);

        // get response code
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($result);
        $result->code = $responseCode;

        if ($this->debug == 1) {
            echo "[Result] result:\n";
            print_r($result);
        }

        return $result;
    }

    // 建立Group
    function createGroup($groupName, $registration_ids) {
        $postData['operation'] = 'create';
        $postData['notification_key_name'] = $groupName;
        $postData['registration_ids'] = $registration_ids;

        $result = $this->curl_request(
            'post',
            MANAGER_GROUP_URL,
            array('Authorization: key='.AUTHORIZATION_KEY, 'Content-Type: application/json', 'project_id: '.FCM_SENDER_ID),
            $postData
        );

        return $result;
    }

    // 透過Group Name查詢Notification Key
    function queryNotificationKey($groupName) {
        echo '[Function] queryNotificationKey'."\n";
        $result = $this->curl_request(
            'get',
            QUERY_NOTIFICATION_KEY.$groupName,
            array('Authorization: key='.AUTHORIZATION_KEY, 'Content-Type: application/json', 'project_id: '.FCM_SENDER_ID)
        );

        return $result;
    }

    // 將Token加入現有的Group
    function joinGroup($groupName, $registration_ids, $notification_key) {
        echo '[Function] joinGroup'."\n";
        $postData['operation'] = 'add';
        $postData['notification_key_name'] = $groupName;
        $postData['registration_ids'] = $registration_ids;
        $postData['notification_key'] = $notification_key;

        $result = $this->curl_request(
            'post',
            MANAGER_GROUP_URL,
            array('Authorization: key='.AUTHORIZATION_KEY, 'Content-Type: application/json', 'project_id: '.FCM_SENDER_ID),
            $postData
        );
        return $result;
    }

    // 將Token從指定群組中移除
    function leaveGroup($groupName, $registration_ids, $notification_key) {
        echo '[Function] leaveGroup'."\n";
        $postData['operation'] = 'remove';
        $postData['notification_key_name'] = $groupName;
        $postData['registration_ids'] = $registration_ids;
        $postData['notification_key'] = $notification_key;

        $result = $this->curl_request(
            'post',
            MANAGER_GROUP_URL,
            array('Authorization: key='.AUTHORIZATION_KEY, 'Content-Type: application/json', 'project_id: '.FCM_SENDER_ID),
            $postData
        );
        return $result;
    }

    // 查詢Token資訊
    function instanceId($token) {
        $url = str_replace('%token%', $token, QUERY_INSTANCE_ID);
        $result = $this->curl_request(
            'get',
            $url,
            array('Authorization: key='.AUTHORIZATION_KEY)
        );
        return $result;
    }

    // 提供將apns token轉換為fcm token的方法
    function apnsToFcm($tokens) {
        if (gettype($tokens) == 'array' && count($tokens) <= 100) {
            $result = $this->curl_request(
                'post',
                APNS_TO_FCM,
                array('Authorization: key='.AUTHORIZATION_KEY, 'Content-Type: application/json'),
                array(
                    'application' => 'tw.net.sunnet.hongu.general',
                    'sandbox' => false,
                    'apns_tokens' => $tokens

                )
            );
            return $result;
        } else {
            return array(
                'results' => 'tokens not array or exceed 100.'
            );
        }

    }

    // 提供oauth token，當透過HTTP V1協定推送訊息時，需要加在Header Authorization中
    function getOauthToken() {
        $nodeCmd = NODE_CMD;
        $fcmAdmin = FCM_NODEJS_ADMIN;
        exec("$nodeCmd $fcmAdmin 1", $output);
        return $output[0];
    }
}
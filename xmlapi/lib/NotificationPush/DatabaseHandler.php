<?php
class DatabaseHandler {

    var $_tokenType = array (
        'APNS' => 1,
        'GCM' => 2,
        'JPUSH' => 3,
        'FCM' => 4
    );

    var $_antiTokenType = array (
        1 => 'APNS',
        2 => 'GCM',
        3 => 'JPUSH',
        4 => 'FCM'
    );

    /**
     * 處理token：看是要add還是要update
     *
     * @param string $username 使用者帳號
     * @param array $deviceData 裝置資訊
     *
     **/
    function tokenHandler ($username, $deviceData) {
        // 避免SQL Injection
        $deviceData = $this->mysqlEscape($deviceData);
        $username = mysql_real_escape_string($username);

        $deviceUUID = $deviceData['device_uuid'];

        if ($deviceUUID !== 'DESKTOP_DEVICE_ID') {
            $checkTokenTypes = $this->whatTokenTypeNeedCheck($deviceData);

            $table = '`APP_notification_device`';
            $field = '`device_token`';

            foreach ($checkTokenTypes as $key => $value) {
                // 避免SQL Injection
                $value = intval($value);

                $where = "`device_uuid` = '{$deviceUUID}' AND `token_type` = {$value}";
                $tokenInDB = dbGetOne($table, $field, $where);

                if (!empty($tokenInDB)) {
                    // 只要這個裝置有該型態的token就做更新
                    $this->update($username, $deviceData, $value);
                } else {
                    // 沒有就新增
                    $this->add($username, $deviceData, $value);
                }
            }
        }
    }

    /**
     * 確認需要驗證哪些token (APNS、GCM、JPUSH、FCM)
     *
     * @param array $deviceData 裝置資訊
     * @return array token類別
     **/
    function whatTokenTypeNeedCheck ($deviceData) {
        $tokenTypes = array();

        $deviceOS = $deviceData['device_os'];

        if (isset($deviceData['device_fcm_token']) && $deviceData['device_fcm_token'] !== '') {
            $tokenTypes[] = $this->_tokenType['FCM'];
        }

        if (isset($deviceData['device_os_token']) && $deviceData['device_os_token'] !== '') {
            if ($deviceOS === 'IOS') {
                $tokenTypes[] = $this->_tokenType['APNS'];
            } else if ($deviceOS === 'ANDROID') {
                $tokenTypes[] = $this->_tokenType['GCM'];
            } else {
                $tokenTypes[] = $this->_tokenType['JPUSH'];
            }
        }

        return $tokenTypes;
    }

    /**
     * 新增裝置資訊與推播頻道
     * 透過LOW_PRIORITY，讓SQL丟出去後就不用理會，由MySQL排程自行處理
     *
     * @param string $username 帳號
     * @param array $deviceData 裝置資料
     * @param integer $tokenType token型態
     **/
    function add ($username, $deviceData, $tokenType) {
        $token = ($tokenType === $this->_tokenType['FCM']) ? $deviceData['device_fcm_token'] : $deviceData['device_os_token'];

        $table = '`APP_notification_device`';
        $fields = '`username`,
                   `token_type`, `device_token`, `device_os`, `device_uuid`,
                   `device_name`, `device_model`, `device_version`, `device_user_agent`,
                   `app_type`, `app_uuid`, `app_name`, `app_version`,
                   `create_date_time`, `update_date_time`';
        $values = "'{$username}',
                    {$tokenType}, '{$token}', '{$deviceData['device_os']}', '{$deviceData['device_uuid']}',
                   '{$deviceData['device_name']}', '{$deviceData['device_model']}', '{$deviceData['device_version']}', '{$deviceData['device_user_agent']}',
                   '{$deviceData['app_type']}', '{$deviceData['app_uuid']}', '{$deviceData['app_name']}', '{$deviceData['app_version']}', 
                   NOW(), NOW()";

        dbNew($table, $fields, $values);
    }

    /**
     * 更新裝置資訊與推播頻道
     * 透過LOW_PRIORITY，讓SQL丟出去後就不用理會，由MySQL排程自行處理
     *
     * @param string $username 帳號
     * @param array $deviceData 裝置資料
     * @param integer $tokenType token型態
     **/
    function update ($username, $deviceData, $tokenType) {
        $token = ($tokenType === $this->_tokenType['FCM']) ? $deviceData['device_fcm_token'] : $deviceData['device_os_token'];

        $table = '`APP_notification_device`';
        $values = "`username` = '{$username}', `device_token_abandon` = 0, `device_token` = '{$token}',
                   `device_name` = '{$deviceData['device_name']}', `device_version` = '{$deviceData['device_version']}',
                   `device_user_agent` = '{$deviceData['device_user_agent']}', `app_type` = '{$deviceData['app_type']}',
                   `app_version` = '{$deviceData['app_version']}', `update_date_time` = NOW()";
        $where = "`device_uuid` = '{$deviceData['device_uuid']}' AND `token_type` = {$tokenType}";

        dbSet($table, $values, $where);
    }

    /**
     * 刪除
     *
     * @param string $deviceToken device token
     **/
    function remove ($deviceToken) {

        $table = '`APP_notification_device`';
        $where = "`device_token` = '{$deviceToken}'";

        dbDel($table, $where);
    }

    /**
     * 取得推播頻道
     *
     * @param array $usernameList 帳號清單
     *
     * @return array 頻道資訊
     **/
    function getDevices ($usernameList) {
        define('USER_TOKEN_CONCATENATE_TAG', '##SUNNETAPP##');
        $devices = array();

        $users = implode("','", $usernameList);

        $joinTable = "SELECT `username`, max(`token_type`) AS `token_type` FROM `APP_notification_device` WHERE `device_token_abandon` = 0 GROUP BY `device_uuid`";
        $table = "`APP_notification_device` AS A
                  INNER JOIN
                  ( {$joinTable} ) AS B ON
                  A.`username` = B.`username` AND A.`token_type` = B.`token_type`";
        $field = 'A.`username`, A.`token_type`, A.`device_token`';
        $where = "A.`username` IN ('{$users}') AND A.`device_token` != 'null'";

        $RS = dbGetStMr($table, $field, $where);

        if ($RS) {
            while ($user = $RS->FetchRow()) {
                $type = $this->_antiTokenType[intval($user['token_type'])];

                $userDevice = array (
                    'username' => $user['username'],
                    'token' => $user['device_token']
                );

                if (!array_key_exists($user['device_token'], $devices[$type])) {
                    $devices[$type][$user['device_token']] = $userDevice;
                }
            }
        }

        return $devices;
    }

    function getAllUsers () {
        $users = array();

        $table = '`APP_notification_device`';
        $field = '`username`';
        $where = '`device_token_abandon` = 0 GROUP BY `username`';

        $RS = dbGetStMr($table, $field, $where);
        if ($RS) {
            while ($user = $RS->FetchRow()) {
                $users[] = $user['username'];
            }
        }

        return $users;
    }

    /**
     * 將array裡面的資料都透過mysql_real_escape_string轉換，避免SQL Injection
     *
     * @param array $deviceData 裝置資料
     * @return array 轉換後的資料
     **/
    function mysqlEscape ($deviceData) {
        $data = array();

        foreach ($deviceData as $key => $value) {
            $data[$key] = mysql_real_escape_string($value);
        }

        return $data;
    }

    function reGenerateData ($deviceData) {
        return array(
            'device_uuid' => $deviceData['deviceuid'],
            'device_name' => rawurldecode($deviceData['devicename']),
            'device_os' => $deviceData['deviceos'],                   // IOS | ANDROID
            'device_model' => $deviceData['devicemodel'],
            'device_version' => $deviceData['deviceversion'],
            'device_user_agent' => '',
            'device_os_token' => $deviceData['devicetoken'],          // 原先的APNS或GCM TOKEN
            'device_fcm_token' => '',
            'app_uuid' => $deviceData['app_uuid'],
            'app_name' => rawurldecode($deviceData['appname']),
            'app_version' => $deviceData['appversion'],
            'app_type' => $deviceData['environment']                  // PRODUCTION | SANDBOX
        );
    }

    function abandonDevice ($token) {
        if (strstr($token, USER_TOKEN_CONCATENATE_TAG)) {
            $userToken = explode(USER_TOKEN_CONCATENATE_TAG, $token);
            $token = mysql_real_escape_string($userToken[1]);
        }

        $table = '`APP_notification_device`';
        $where = "`device_token` = '{$token}' LIMIT 1";

        dbDel($table, $where);
    }

    function pushMessageIntoDB ($sender, $alert, $content, $alertType, $messageID, $result) {
        $table = '`APP_notification_message`';
        $fields = '`sender`, `receiver`, `receiver_token`,
                   `message_type`, `message_id`, `title`, `content`, 
                   `google_message`, `send_time`';

        $dbSendTime = date('Y-m-d H:i:s');
        $dbSender = mysql_real_escape_string($sender);
        $dbTitle = mysql_real_escape_string($alert);
        $dbContent = mysql_real_escape_string($content);
        $dbMessageType = mysql_real_escape_string($alertType);
        $dbMessageID = mysql_real_escape_string($messageID);

        foreach ($result as $key => $tokenResult) {
            $thisToken = $tokenResult['token'];
            $dbReceiver = dbGetOne('`APP_notification_device`', '`username`', "`device_token` = '{$thisToken}'");
            $dbToken = mysql_real_escape_string($tokenResult['token']);
            $dbGoogleMessage = mysql_real_escape_string($tokenResult['message']);

            $values = "'{$dbSender}', '{$dbReceiver}', '{$dbToken}',
                       '{$dbMessageType}', '{$dbMessageID}', '{$dbTitle}', '{$dbContent}',
                       '{$dbGoogleMessage}', '{$dbSendTime}'";

            dbNew($table, $fields, $values);
        }
    }
}

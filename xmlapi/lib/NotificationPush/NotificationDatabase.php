<?php
class NotificationDatabase {

    /**
     * 驗證是否有裝置資訊與推播頻道
     *
     * @param array $deviceData 裝置資料
     *
     * @return boolean 驗證結果 true(有) | false(無)
     **/
    function valid ($deviceData) {
        global $sysConn;

        $deviceWhere = "`app_uuid` = '{$deviceData['app_uuid']}' AND `deviceuid` = '{$deviceData['deviceuid']}' AND `devicetoken` = '{$deviceData['devicetoken']}'";
        $deviceSQL = "SELECT COUNT(*) FROM `APP_push_device_info` WHERE " . $deviceWhere;
        $deviceResult = $sysConn->GetOne($deviceSQL);

        if ($deviceResult) {
            // 有裝置資訊
            $channelSQL = "SELECT COUNT(*) FROM `APP_push_subscribe_channel` WHERE `devicetoken` = '{$deviceData['devicetoken']}'";
            $channelResult = $sysConn->GetOne($channelSQL);
            if ($channelResult) {
                // 有推播頻道
                return true;
            } else {
                // 沒有推播頻道，則刪除裝置資訊
                $deleteDevice = "DELETE FROM `APP_push_device_info` WHERE " . $deviceWhere;
                $sysConn->Execute($deleteDevice);
                return false;
            }
        } else {
            // 沒有裝置資訊
            return false;
        }
    }

    /**
     * 新增裝置資訊與推播頻道
     * 透過LOW_PRIORITY，讓SQL丟出去後就不用理會，由MySQL排程自行處理
     *
     * @param string $username 帳號
     * @param array $deviceData 裝置資料
     **/
    function add ($username, $deviceData) {
        global $sysConn;

        if ($deviceData['devicetoken'] === '') {
            return;
        }
        // 裝置資訊
        $appName = rawurldecode($deviceData['appname']);
        $deviceName = rawurldecode($deviceData['devicename']);
        if ($deviceData['deviceos'] === 'IOS') {
            $pushType = 1;
        } else if ($deviceData['deviceos'] === 'ANDROID') {
            $pushType = 2;
        } else {
            $pushType = 3;
        }
        $deviceSQL = "INSERT LOW_PRIORITY INTO `APP_push_device_info`
                          (`app_uuid`, `appname`, `appversion`,
                          `deviceos`, `deviceuid`, `devicetoken`, `devicename`, `devicemodel`, `deviceversion`,
                          `type`, `environment`, `status`, `builder`,
                          `pushbadge`, `pushalert`, `pushsound`,
                          `create_datetime`, `update_datetime`)
                       VALUES
                           ('{$deviceData['app_uuid']}', '{$appName}', '{$deviceData['appversion']}',
                            '{$deviceData['deviceos']}', '{$deviceData['deviceuid']}', '{$deviceData['devicetoken']}', '{$deviceName}', '{$deviceData['devicemodel']}', '{$deviceData['deviceversion']}',
                            {$pushType}, '{$deviceData['environment']}', 'ACTIVE', 'SYSTEM',
                            '{$deviceData['pushbadge']}', '{$deviceData['pushalert']}', '{$deviceData['pushsound']}',
                            NOW(), NOW())
                      ";

        $sysConn->Execute($deviceSQL);
        // 推播頻道
        $channel = "{$username}";
        $channelSQL = "INSERT LOW_PRIORITY INTO `APP_push_subscribe_channel`
                           (`app_uuid`, `devicetoken`, `channel`, `builder`, `create_datetime`, `update_datetime`)
                       VALUES
                           ('{$deviceData['app_uuid']}', '{$deviceData['devicetoken']}', '{$channel}', 'SYSTEM', NOW(), NOW())
                      ";
        $sysConn->Execute($channelSQL);
    }

    /**
     * 更新裝置資訊與推播頻道
     * 透過LOW_PRIORITY，讓SQL丟出去後就不用理會，由MySQL排程自行處理
     *
     * @param String $username 帳號
     * @param Object $deviceData 裝置資料
     **/
    function update ($username, $deviceData) {
        global $sysConn;

        if ($deviceData['devicetoken'] === '') {
            return;
        }
        
        $deviceName = rawurldecode($deviceData['devicename']);
        if ($deviceData['deviceos'] === 'IOS') {
            $pushType = 1;
        } else if ($deviceData['deviceos'] === 'ANDROID') {
            $pushType = 2;
        } else {
            $pushType = 3;
        }
        // 裝置資訊
        $deviceSQL = "UPDATE LOW_PRIORITY `APP_push_device_info` SET
                          `appversion` = '{$deviceData['appversion']}',
                          `deviceos` = '{$deviceData['deviceos']}', `devicename` = '{$deviceName}',
                          `devicemodel` = '{$deviceData['devicemodel']}', `deviceversion` = '{$deviceData['deviceversion']}',
                          `type` = {$pushType}, `environment` = '{$deviceData['environment']}', `update_datetime` = NOW()
                      WHERE
                          `deviceuid` = '{$deviceData['deviceuid']}' AND `devicetoken` = '{$deviceData['devicetoken']}'
                     ";
        $sysConn->Execute($deviceSQL);
        // 推播頻道
        $channel = "{$username}";
        $channelSQL = "UPDATE LOW_PRIORITY `APP_push_subscribe_channel` SET
                           `channel` = '{$channel}', `update_datetime` = NOW()
                       WHERE
                           `devicetoken` = '{$deviceData['devicetoken']}'
                      ";
        $sysConn->Execute($channelSQL);
    }

    /**
     * 更新裝置資訊與推播頻道
     * 透過LOW_PRIORITY，讓SQL丟出去後就不用理會，由MySQL排程自行處理
     *
     * @param Object $deviceData 裝置資料
     **/
    function remove ($deviceData) {
        global $sysConn;

        // 裝置資訊
        $deviceSQL = "DELETE FROM `APP_push_device_info` WHERE `app_uuid` = '{$deviceData['app_uuid']}' AND `deviceuid` = '{$deviceData['deviceuid']}' AND `devicetoken` = '{$deviceData['devicetoken']}'";
        $sysConn->Execute($deviceSQL);
        // 推播頻道
        $channelSQL = "DELETE FROM `APP_push_subscribe_channel` WHERE `app_uuid` = '{$deviceData['app_uuid']}' AND `devicetoken` = '{$deviceData['devicetoken']}'";
        $sysConn->Execute($channelSQL);
    }

    /**
     * 更新裝置資訊與推播頻道，刪除超過期限仍未曾使用的資料
     *
     * @param Number $timeLimit 期限秒數
     **/
    function refreshDevice ($timeLimit) {
        global $sysConn;

        // 裝置資訊
        $deviceSQL = "DELETE FROM `APP_push_device_info` WHERE (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`update_datetime`)) > ({$timeLimit})";
        $sysConn->Execute($deviceSQL);
        // 推播頻道
        $channelSQL = "DELETE FROM `APP_push_subscribe_channel` WHERE (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`update_datetime`)) > ({$timeLimit})";
        $sysConn->Execute($channelSQL);
    }

    /**
     * 取得推播頻道
     *
     * @param String $channel 頻道(或是帳號)
     * @param String $os 作業系統(IOS|ANDROID)
     * @param Number $type 1.APNS, 2.GCM, 3.Jpush
     *
     * @return Array 頻道資訊
     **/
    function getPushDevices ($channel, $os, $type) {
        global $sysConn;

        $device = array();
        $typeCondition = '';

        if ($channel === '#PUSHALL#') {
            $channelCondition = '1';
        } else {
            $channelCondition = "`hasc`.`channel` IN (". $channel .")";
        }

        if ($type === 1) {
            $typeCondition = ' AND (`hadi`.type = 1 OR `hadi`.`type` IS NULL)';
        } else if ($type === 2) {
            $typeCondition = ' AND (`hadi`.type = 2 OR `hadi`.`type` IS NULL)';
        } else if ($type === 3) {
            $typeCondition = ' AND `hadi`.type = 3';
        }

        $where = $channelCondition . $typeCondition;

        $channelSQL = "SELECT
                           `hadi`.`environment`, `hasc`.* 
                       FROM
                           `APP_push_device_info` as `hadi` INNER JOIN `APP_push_subscribe_channel` as `hasc`
                           ON `hadi`.`devicetoken` = `hasc`.`devicetoken` AND `hadi`.`devicetoken` != '' AND `hadi`.`deviceos` = '{$os}'
                       WHERE " . $where . " ORDER BY `hasc`.`update_datetime` DESC";

        $result = $sysConn->Execute($channelSQL);
        if ($result) {
            // 過濾重複的裝置
            while ($deviceInfo = $result->FetchRow()) {
                $device[AppUUID . '@' . $deviceInfo['devicetoken']] = $deviceInfo;
            }
        }

        return $device;
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
}

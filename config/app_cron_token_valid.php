#!/usr/local/bin/php
<?php
define('XMLAPI', true);
    /**
     * 透過排程，驗證FCM TOKEN是否仍然可用
     **/
    // 系統設定
    require_once(dirname(__FILE__) . '/console_initialize.php');
    require_once(dirname(__FILE__) . '/sys_config.php');
    require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');
    require_once(sysDocumentRoot . '/xmlapi/config.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DatabaseHandler.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DoPush.php');

    // 資料庫連結初始化
    $sysConn = &ADONewConnection(sysDBtype);
    if (!$sysConn->PConnect(sysDBhost, sysDBaccoount, sysDBpassword))
        die('Database Connecting failure !');
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

    $sysConn->Execute('use ' . sysDBname);
    $school_ids = $sysConn->GetCol('SELECT DISTINCT `school_id` FROM `WM_school`');
    if (is_array($school_ids) && count($school_ids)) {
        foreach ($school_ids as $school_id) {
            $sysConn->Execute('use ' . sysDBprefix . $school_id);
            if ($sysConn->ErrorNo() == 0) {
                // 取得FCM TOKEN
                $sql = "SELECT
                            `device_token`
                        FROM
                            `APP_notification_device`
                        WHERE
                            `token_type` = 4 AND `device_token_abandon` = 0";
                $RS = $sysConn->Execute($sql);

                if ($RS) {
                    $fcmPushHandler = new App_NotificationPush_Fcm();
                    $dbHandler = new DatabaseHandler();
                    while ($device = $RS->FetchRow()) {
                        $token = $device['device_token'];
                        // 驗證TOKEN是否失效
                        $validResult = (object) $fcmPushHandler->instanceId($token);

                        // 若失效，則將該TOKEN設為捨棄
                        if (isset($validResult->error) && $validResult->error !== '') {
                            $dbHandler->abandonDevice($token);
                        }

                    }
                }

                $deltaTime = 60 * 60 * 24 * 30;
                $where = "((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`update_date_time`)) > {$deltaTime}) OR (`app_uuid` = '') OR (`device_token` = '')";
                // 移除已經捨棄的device token
                $sql = "DELETE FROM `APP_notification_device` WHERE {$where}";
                $RS = $sysConn->Execute($sql);
            }
        }
    }

#!/usr/local/bin/php
<?php
define('XMLAPI', true);
    /**
     * �z�L�Ƶ{�A����FCM TOKEN�O�_���M�i��
     **/
    // �t�γ]�w
    require_once(dirname(__FILE__) . '/console_initialize.php');
    require_once(dirname(__FILE__) . '/sys_config.php');
    require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');
    require_once(sysDocumentRoot . '/xmlapi/config.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DatabaseHandler.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DoPush.php');

    // ��Ʈw�s����l��
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
                // ���oFCM TOKEN
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
                        // ����TOKEN�O�_����
                        $validResult = (object) $fcmPushHandler->instanceId($token);

                        // �Y���ġA�h�N��TOKEN�]���˱�
                        if (isset($validResult->error) && $validResult->error !== '') {
                            $dbHandler->abandonDevice($token);
                        }

                    }
                }

                $deltaTime = 60 * 60 * 24 * 30;
                $where = "((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`update_date_time`)) > {$deltaTime}) OR (`app_uuid` = '') OR (`device_token` = '')";
                // �����w�g�˱�device token
                $sql = "DELETE FROM `APP_notification_device` WHERE {$where}";
                $RS = $sysConn->Execute($sql);
            }
        }
    }

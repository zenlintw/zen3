<?php
/**
 * 儲存推播註冊頻道資料
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(PATH_LIB . 'NotificationPush/push-config.php');
require_once(PATH_LIB . 'JsonUtility.php');
require_once(PATH_LIB . 'NotificationPush/NotificationDatabase.php');
require_once(PATH_LIB . 'NotificationPush/DatabaseHandler.php');
/*
$deviceData = array (
    'device_uuid' => '裝置UUID',
    'device_name' => '裝置名稱',
    'device_os' => '裝置作業系統',                // IOS | ANDROID
    'device_model' => '裝置型號',
    'device_version' => '裝置版本',
    'device_user_agent' => '裝置User Agent',
    'device_os_token' => '裝置device token',    // 原先的APNS或GCM TOKEN
    'device_fcm_token' => '裝置fcm token',
    'app_uuid' => 'APP UUID',
    'app_name' => 'APP名稱',
    'app_version' => 'APP版本',
    'app_type' => 'APP建置類別'                  // PRODUCTION | SANDBOX
);
*/

class SubscribeChannelAction extends baseAction
{
    var $_mysqlUsername;

    function main()
    {
        global $sysSession;
        // 驗證 Ticket
        parent::checkTicket();

        // 從 POST 取得參數
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);
        $deviceData = $postData['deviceData'];
        $code = 0;
        $message = 'success';

        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        if ($deviceData['device_os_token'] === '') {
            $code = 2;
            $message = 'fail';
        } else {
            $handler = new DatabaseHandler();
            $handler->tokenHandler($this->_mysqlUsername, $deviceData);
        }


        $jsonObj = array(
            'code'     => $code,
            'message'  => $message,
            'data'     => array()
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
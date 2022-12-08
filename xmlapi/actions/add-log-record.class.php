<?php
/**
 * 新增APP操作Log
 */
include_once(dirname(__FILE__).'/action.class.php');

class AddLogRecordAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysConn, $sysSession;

        // 處理接收的資料 - Begin
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);

        if ($postData['idx'] === '' || $postData['log_time'] === '' || $postData['action'] === '') {
            return;
        }

        $table = '`APP_log`';
        $fields = '`idx`, `username`, `course_id`, `instance_type`, `instance`, `log_time`, `action`, `comment`, `telecom`, `network_type`, `device_type`, `device_brand`, `wifi_ssid`, `user_ip`';
        $idx = mysql_real_escape_string(trim($postData['idx']));
        $username = mysql_real_escape_string(trim($sysSession->username));
        $courseId = mysql_real_escape_string(trim($postData['course_id']));
        $instanceType = mysql_real_escape_string(trim($postData['instance_type']));
        $instance = mysql_real_escape_string(trim($postData['instance']));
        $logTime = mysql_real_escape_string(trim($postData['log_time']));
        $action = mysql_real_escape_string(trim($postData['action']));
        $comment = mysql_real_escape_string(trim($postData['comment']));
        $telecom = mysql_real_escape_string(trim($postData['telecom']));
        $networkType = mysql_real_escape_string(trim($postData['network_type']));
        $deviceType = mysql_real_escape_string(trim($postData['device_type']));
        $deviceBrand = mysql_real_escape_string(trim($postData['device_brand']));
        $wifiSSID = mysql_real_escape_string(trim($postData['wifi_ssid']));
        $userIp = wmGetUserIp();

        $values = "'{$idx}', '{$username}', {$courseId}, '{$instanceType}', '{$instance}', '{$logTime}', '{$action}', '{$comment}', '{$telecom}', '{$networkType}', '{$deviceType}', '{$deviceBrand}', '{$wifiSSID}', '{$userIp}'";
        dbNew($table, $fields, $values);

        if ($sysConn->Affected_Rows() > 0) {
            $message = 'success';
            $code = 0;
        } else {
            $message = 'fail';
            $code = 2;
        }

        // code: 0(新增成功) | 2(新增失敗)
        $responseObject = array(
            'code' => intval($code),
            'message' => $message,
            'data' => array()
        );
        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
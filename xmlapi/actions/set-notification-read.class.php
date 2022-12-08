<?php
/**
 * 設定推播訊息為已讀
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class SetNotificationReadAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysConn, $sysSession;

        // 從網址取得參數
        $msgId = mysql_real_escape_string(str_replace('*SUN*', '%', trim($_GET['msg_id'])));
        $table = '`APP_notification_message`';

        list($messageType, $messageId) = dbGetStSr($table, '`message_type`, `message_id`', "`google_message`='{$msgId}' AND `receiver` = '{$sysSession->username}'", ADODB_FETCH_NUM);
        $msgWhere = "`receiver` = '{$sysSession->username}' AND `message_type` = '{$messageType}' AND `message_id` = '{$messageId}'";
        $msgField = "`user_read_time` = NOW()";
        dbSet('`APP_notification_message`', $msgField, $msgWhere);

        if ($sysConn->Affected_Rows() > 0) {
            $code = 0;
            $message = 'success';
        } else {
            $code = 2;
            $message = 'fail';
        }

        // appSysLog(999999004, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Read Notification:' . $msgId . '-' . $message, $sysSession->username);
        
        // make json
        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => array()
        );

        $jsonEncode = JsonUtility::encode($jsonObj);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
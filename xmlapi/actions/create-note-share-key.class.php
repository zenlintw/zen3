<?php
/**
 * 製作筆記分享的Key
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class CreateNoteShareKeyAction extends baseAction
{
    /**
     * 加密的key
     * @access private
     * @var string
     **/
    var $_encodeKey = ';iSunNote-sunnet;';
    var $_defaultDueDay = 1;
    var $_mysqlUsername = '';

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession;

        $data = array();
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        // 處理接收的資料 - Begin
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);

        $folderId = mysql_real_escape_string(trim($postData['folderId']));
        $noteId = intval(trim($postData['noteId']));
        $shareKey = mysql_real_escape_string(trim($postData['shareKey']));

        $noteWhere = "`msg_serial` = {$noteId} AND `receiver` = '{$this->_mysqlUsername}'";

        $isExistNote = dbGetOne('WM_msg_message', 'count(*)', $noteWhere);

        if ($isExistNote) {
            $keyWhere = "`share_key` = '{$shareKey}' AND
                         `msg_serial` = {$noteId} AND
                         `owner` = '{$this->_mysqlUsername}'";
            $isExistKey = dbGetOne('APP_note_share', 'count(*)', $keyWhere);

            $dueTime = time() + $this->_defaultDueDay * 86400;

            if (!$isExistKey) {
                // 沒有key，因此要製作一個新的key
                $fields = '`share_key`, `folder_id`, `msg_serial`, `owner`, `due_time`';
                $values = "'{$shareKey}', '{$folderId}', {$noteId}, '{$this->_mysqlUsername}', {$dueTime}";

                dbNew('APP_note_share', $fields, $values);
            } else {
                // 有key，因此改分享的結束時間
                dbSet('APP_note_share', "`due_time` = {$dueTime}", $keyWhere);
            }

            $code = 0;
            $message = 'success';
            $data = array(
                'shareURL' => WM_SERVER_HOST .'/xmlapi/index.php?action=receive-share-note&share-key=' . $shareKey
            );
        } else {
            $code = 2;
            $message = 'fail';
        }

        appSysLog(999999011, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Create Note Share Key:' . $message, $this->_mysqlUsername);

        // make json
        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
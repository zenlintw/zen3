<?php
/**
 * 刪除筆記
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class DeleteNoteAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysConn;
        
        // 避免 SQL Injection 的 username
        $username = mysql_real_escape_string($sysSession->username);
        // 筆記ID
        $noteId = intval(trim($_GET['note_id']));
        // 筆記時間
        if (isset($_GET['note_update_datetime'])) {
            $noteUpdateDatetime = intval(trim($_GET['note_update_datetime']));
            $noteTime = date('Y-m-d H:i:s', $noteUpdateDatetime);
        } else {
            $noteUpdateDatetime = time();
            $noteTime = date('Y-m-d H:i:s', $noteUpdateDatetime);
        }

        $table = 'WM_msg_message';
        $value = "`folder_id` = 'sys_notebook_trash', `submit_time` = '{$noteTime}', `receive_time` = '{$noteTime}'";
        $where = "`msg_serial` = {$noteId} AND `receiver` = '{$username}'";
        dbSet($table, $value, $where);

        // 記錄筆記的動作 - Begin

        dbNew('APP_note_action_history',
            '`username`, `log_time`, `action`, `folder_id`, `msg_serial`, `from`',
            "'{$username}', {$noteUpdateDatetime}, 'D', '', {$noteId}, 'client'");
        // 記錄筆記的動作 - End

        if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
            $code = 2;
            $message = 'fail';
        } else {
            $code = 0;
            $message = 'success';
        }

        appSysLog(999999012, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Delete Note:' . $message, $sysSession->username);

        // make json
        $jsonObj = array(
            'code' => intval($code),
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
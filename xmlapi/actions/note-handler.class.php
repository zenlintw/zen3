<?php
/**
 * 新增筆記
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class NoteHandlerAction extends baseAction
{
    /**
     * 避免SQL Injection 用的帳號
     * @access private
     * @var string
     */
    var $_avoidSIUsername = '';

    /**
     * 避免SQL Injection 用的檔案串接字串
     * @access private
     * @var string
     */
    var $_attachmentsHandlerResult = '';

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysConn;

        // 避免 SQL Injection 的 username
        $this->_avoidSIUsername = mysql_real_escape_string($sysSession->username);
        // 原本在server的筆記附檔
        $serverAttachments = array();
        // 附檔處理的結果
        $saveAttachmentsResult = '';
        // 處理前置附檔路徑
        $attachPath = sprintf('%s/user/%1s/%1s/%s/', sysDocumentRoot, substr($sysSession->username, 0, 1), substr($sysSession->username, 1,1), $sysSession->username);
        $webAttachPath = sprintf('%s/user/%1s/%1s/%s/', WM_SERVER_HOST, substr($sysSession->username, 0, 1), substr($sysSession->username, 1,1), $sysSession->username);
        // 筆記本是否存在
        $folderExist = 0;
        // 筆記的編號
        $returnMsgSerial = 0;
        // 筆記是否存在
        $isNoteExist = 0;

        // 處理接收的資料 - Begin
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);

        // 筆記本ID
        $folderId = mysql_real_escape_string(trim($postData['folder_id']));
        // 筆記ID(WMPRO)
        $msgSerial = intval(trim($postData['msg_serial']));
        // 筆記ID(APP)
        $noteId = mysql_real_escape_string(trim($postData['note_id']));
        // 筆記標題
        $title = mysql_real_escape_string(trim($postData['title']));
        // 筆記內容
        $content = mysql_real_escape_string(trim($postData['content']));
        // 筆記時間
        if (isset($postData['note_update_datetime'])) {
            $noteUpdateDatetime = intval(trim($postData['note_update_datetime']));
            $noteTime = date('Y-m-d H:i:s', $noteUpdateDatetime);
        } else {
            $noteUpdateDatetime = time();
            $noteTime = date('Y-m-d H:i:s', $noteUpdateDatetime);
        }

        $code = 0;
        $message = 'success';
        $logMessage = 'noteHandler: operation success';
        $table = 'WM_msg_message';
        $historyTable = 'APP_note_action_history';

        // 筆記附檔
        $clientAttachments = $postData['attachments'];
        $from = $postData['from'];
        // 處理接收的資料 - End

        // 確認筆記本是否存在
        if ($folderId != '') {
            $folderExist = dbGetOne('WM_msg_folder', 'count(*)', "`username` = '{$this->_avoidSIUsername}' AND LOCATE('{$folderId}', `content`)");
        }
        if ($folderExist > 0) {
            // 筆記本存在
            if (strlen($title) > 0) {
                // 有筆記標題

                if ($msgSerial !== 0) {
                    // 確認筆記是否存在
                    $isNoteExist = dbGetOne($table, 'count(*)', "msg_serial = {$msgSerial}");
                }

                // 若是已經存在的筆記，則取出附檔資訊
                if ($isNoteExist) {
                    $where = "`msg_serial` = {$msgSerial} AND `receiver` = '{$this->_avoidSIUsername}'";
                    $noteAttachment = dbGetOne($table, 'attachment', $where);
                    
                    // 先判斷是否為陣列
                    if (is_array($noteAttachment)) {
                        $serverAttachments = explode("\t", $noteAttachment);
                    } else {
                        $serverAttachments = array();
                    }
                }

                // 有傳來附檔或是原本筆記有附檔，且目錄存在才做處理檔案的動作
                if (count($clientAttachments) > 0 || count($serverAttachments) > 0) {
                    // 呼叫/lib/common.php的函式處理檔案比對
                    $saveAttachmentsResult = attachmentsHandler($serverAttachments, $clientAttachments, $attachPath, $from);
                }
                
                if ($saveAttachmentsResult !== 'fail') {
                    // 存檔成功或不需要存檔
                    if ($isNoteExist === 0) {
                        // 寫入資料庫 - Begin
                        $fields = '`folder_id`,
                               `sender`, `receiver`,
                               `submit_time`, `receive_time`,
                               `status`,
                               `subject`, `content`, `attachment`, `content_type`';
                        /* $this->_attachmentsHandlerResult 改為 $saveAttachmentsResult 修正PC筆記本無法上傳附件*/
                        $values = "'{$folderId}',
                               '{$this->_avoidSIUsername}', '{$this->_avoidSIUsername}',
                               '{$noteTime}', '{$noteTime}',
                               'read',
                               '{$title}', '{$content}', '{$saveAttachmentsResult}', 'html'";

                        dbNew($table, $fields, $values);
                        if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                            $code = 31;
                            $message = 'fail';
                            $logMessage = 'noteHandler: insert note fail';
                        } else {
                            $msgSerial = $sysConn->Insert_ID();
                            // 紀錄筆記的動作 - Begin
                            dbNew($historyTable,
                                '`username`, `log_time`, `action`, `folder_id`, `msg_serial`, `from`',
                                "'{$this->_avoidSIUsername}', {$noteUpdateDatetime}, 'A', '{$folderId}', {$msgSerial}, 'client'");

                            if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                                $code = 32;
                                $message = 'fail';
                                $logMessage = 'noteHandler: insert note > insert history fail';
                            } else {
                                $returnMsgSerial = $msgSerial;
                            }
                            // 紀錄筆記的動作 - Begin
                        }
                        // 寫入資料庫 - End
                    } else {
                        // 寫入資料庫 - Begin
                        $values = "`folder_id` = '{$folderId}',
                               `sender` = '{$this->_avoidSIUsername}', `receiver` = '{$this->_avoidSIUsername}',
                               `submit_time` = '{$noteTime}', `receive_time` = '{$noteTime}',
                               `subject` = '{$title}', `content` = '{$content}', `attachment` = '{$saveAttachmentsResult}', `content_type` = 'html'";

                        $where = "`msg_serial` = {$msgSerial} AND `receiver` = '{$this->_avoidSIUsername}'";
                        dbSet($table, $values, $where);

                        if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                            $code = 33;
                            $message = 'fail';
                            $logMessage = 'noteHandler: update note fail';
                        } else {
                            // 記錄筆記的動作 - Begin
                            dbNew($historyTable,
                                '`username`, `log_time`, `action`, `folder_id`, `msg_serial`, `from`',
                                "'{$this->_avoidSIUsername}', {$noteUpdateDatetime}, 'M', '{$folderId}', {$msgSerial}, 'client'");
                            // 紀錄筆記的動作 - End
                            if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                                $code = 34;
                                $message = 'fail';
                                $logMessage = 'noteHandler: update note > insert history fail';
                            } else {
                                $returnMsgSerial = $msgSerial;
                            }
                        }
                        // 寫入資料庫 - End
                    }
                } else {
                    // 儲存檔案失敗
                    $code = 5;
                    $message = 'fail';
                    $logMessage = 'noteHandler: save physical file fail';
                }
            } else {
                // 沒有筆記標題
                $code = 4;
                $message = 'fail';
                $logMessage = 'noteHandler: no note title';
            }
        } else {
            // 筆記本不存在
            $code = 2;
            $message = 'fail';
            $logMessage = 'noteHandler: folder not found';
        }

        appSysLog(999999009, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], $logMessage, $sysSession->username);

        // code: 0(處理成功) | 2(筆記本不存在) | 3(寫入資料庫失敗) | 4(沒有筆記標題) | 5(儲存檔案失敗)
        $responseObject = array(
            'code' => intval($code),
            'message' => $message,
            'data' => array(
                'msg_serial' => $returnMsgSerial,
                'update_time'       => date('Y-m-d H:i:s', $noteUpdateDatetime),
                'note_id' => $noteId,
                'note_attachments' => makeAttachments($this->_attachmentsHandlerResult, $webAttachPath)
            )
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
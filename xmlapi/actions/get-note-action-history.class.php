<?php
/**
 * 撈出筆記的增刪修動作歷史紀錄，以提供app進行比對與同步用
 */
include_once(dirname(__FILE__).'/action.class.php');
include_once(dirname(__FILE__).'/my-course-history.class.php');
include_once(sysDocumentRoot . '/lib/course.php');

class GetNoteActionHistoryAction extends baseAction
{
    /**
     * 避免SQL Injection 用的帳號
     * @access private
     * @var string
     **/
    var $_avoidSIUsername = '';

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        $code = 0;
        $message = 'success';

        // 設定之後要用的username(避免SQL Injection)
        $this->_avoidSIUsername = mysql_real_escape_string($sysSession->username);
        $getTime = 0;
        $actionList = array();
        // 處理前置夾檔路徑
        $attachPath = sprintf('%s/user/%1s/%1s/%s/', WM_SERVER_HOST, substr($sysSession->username, 0, 1), substr($sysSession->username, 1,1), $sysSession->username);

        if (isset($_GET['sync_time']) && intval($_GET['sync_time']) > 0) {
            $getTime = intval(trim($_GET['sync_time']));
        }

        $RS = dbGetStMr('APP_note_action_history', '*', "`username` = '{$this->_avoidSIUsername}' AND `log_time` >= {$getTime} ORDER BY `log_time`");

        if ($RS) {
            while (!$RS->EOF) {
                $action = $RS->fields['action'];
                $msgSerial = $RS->fields['msg_serial'];
                $from = $RS->fields['from'];

                if (!in_array($msgSerial, $actionList)) {
                    // 重設參數
                    unset($note);
                    $note['action'] = $action;
                    $note['from'] = $from;

                    if ($action === 'A' || $action === 'M') {
                        $rsNotes = dbGetStSr('WM_msg_message', '`folder_id`, `subject`, `content`, `receive_time`, `attachment`', "`msg_serial` = {$msgSerial}", ADODB_FETCH_ASSOC);
                        if ($rsNotes) {
                            // 筆記本ID
                            $note['folder_id'] = $rsNotes['folder_id'];
                            // 筆記標題
                            $note['note_title'] = $rsNotes['subject'];
                            // 筆記內容
                            $note['note_content'] = $rsNotes['content'];
                            // 筆記時間
                            $note['note_time'] = datetimeToSeconds($rsNotes['receive_time']);
                            $note['leaf'] = true;

                            if (!empty($rsNotes['attachment'])) {
                                // 若有夾檔
                                $note['note_attachments'] = makeAttachments($rsNotes['attachment'], $attachPath);
                            } else {
                                // 沒有夾檔
                                $note['note_attachments'] = array();
                            }
                        }
                    }

                    $actionList[$msgSerial] = $note;
                }
                $RS->MoveNext();
            }
        } else {
            $message = 'fail';
            $code = 2;
        }

        // make json
        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'list' => $actionList
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
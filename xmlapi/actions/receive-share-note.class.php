<?php
/**
 * 獲得筆記分享的Key並作後續分享動作
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');
require_once(sysDocumentRoot . '/lang/app_note.php');
require_once(sysDocumentRoot . '/lib/login/login.inc');

class ReceiveShareNoteAction extends baseAction
{
    function main()
    {
        global $sysSession, $sysConn;

        $code = null;

        // 處理接收的資料 - Begin
        $shareKey = trim($_GET['share-key']);
        list($noteId, $owner, $dueTime) = dbGetStSr('APP_note_share', '`msg_serial`, `owner`, `due_time`', "`share_key` = '{$shareKey}'");

        // 暫時先取消接收機制：桌機由網頁接收，行動裝置由app接收
        // $isMobileDevice = $this->checkMobileDevice($_SERVER["HTTP_USER_AGENT"]);
        $isMobileDevice = false;

        if (is_null($noteId)) {
            // 筆記並未被分享
            $this->resultHandler(7, $isMobileDevice, $_GET['share-key']);
        }

        // 帳號因素導致無法分享
        if ($sysSession->username === 'guest') {
            // 尚未登入
            $this->resultHandler(5, $isMobileDevice, $_GET['share-key']);
        } else if ($sysSession->username === $owner) {
            // 與分享者為同一人
            $this->resultHandler(6, $isMobileDevice, $_GET['share-key']);
        }

        // 時間因素導致無法分享
        if (intval($dueTime) < time()) {
            // 超過分享時間
            $this->resultHandler(2, $isMobileDevice, $_GET['share-key']);
        }

        // 可以分享的情況下
        $noteWhere = "`msg_serial` = {$noteId}";
        $note = dbGetStSr('WM_msg_message', '`subject`, `content`,`content_type`, `attachment`', $noteWhere, ADODB_FETCH_ASSOC);

        if ($note) {
            // 預設附檔為空
            $attachment = '';

            if (!empty($note['attachment']) && !is_null($note['attachment'])) {
                // 如果附檔不為空，則進行檔案複製
                $attachment = $this->attachCopier($owner, $sysSession->username, $note['attachment']);
            }

            $noteSubject = addslashes($note['subject']);
            $noteContent = addslashes($note['content']);

            $nowTime = date('Y-m-d H:i:s', time());

            $table = 'WM_msg_message';
            $fields = '`folder_id`, `sender`, `receiver`, ' .
                '`submit_time`, `receive_time`, '.
                '`subject`, `content`, `attachment`, '.
                '`content_type`';
            $values = "'sys_notebook', '{$owner}', '{$sysSession->username}'," .
                "'{$nowTime}', '{$nowTime}'," .
                "'{$noteSubject}', '{$noteContent}', '{$attachment}'," .
                "'{$note['content_type']}'";

            dbNew($table, $fields, $values);

            if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0) {
                // 資料庫執行異常
                $code = 3;
            } else {
                // 成功
                $code = 0;
            }
        } else {
            // 找不到筆記
            $code = 4;
        }

        // 結果處理
        $this->resultHandler($code, $isMobileDevice, $_GET['share-key']);
    }

    /**
     * 偵測是否為行動裝置
     *
     * @param string $userAgent
     *
     * @return boolean true(是) | false(否)
     **/
    function checkMobileDevice ($userAgent) {
        $userAgent = strtolower($userAgent);
        if (strstr($userAgent, 'android') || strstr($userAgent, 'ios')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 複製筆記附檔
     *
     * @param string $fromUser
     * @param string $toUser
     * @param string $attachment
     *
     * @return string 附檔字串
     **/
    function attachCopier ($fromUser, $toUser, $attachment) {
        $arrayNewAttachments = array();

        $fromPath = sprintf(sysDocumentRoot . '/user/%1s/%1s/%s/', substr($fromUser, 0, 1), substr($fromUser, 1,1), $fromUser);
        $toPath = sprintf(sysDocumentRoot . '/user/%1s/%1s/%s/', substr($toUser, 0, 1), substr($toUser, 1,1), $toUser);
        $arrayAttachments = explode(chr(9),$attachment);
        $attachmentCount = count($arrayAttachments);

        for ($i = 1; $i < $attachmentCount; $i = $i + 2) {
            $fromPhysicalFile = $fromPath . $arrayAttachments[$i];
            $toPhysicalFile = $toPath . 'share_' . $arrayAttachments[$i];
            copy($fromPhysicalFile, $toPhysicalFile);

            $arrayNewAttachments[$i - 1] = $arrayAttachments[$i - 1];
            $arrayNewAttachments[$i] = 'share_' . $arrayAttachments[$i];
        }

        return implode(chr(9), $arrayNewAttachments);
    }

    /**
     * 根據結果處理後續動作
     *
     * @param number $code 結果編號
     * @param boolean $isMobile 是否為行動裝置
     * @param string $shareKey
     **/
    function resultHandler ($code, $isMobile, $shareKey) {
        global $sysSession, $MSG;

        // 初始值
        $mobileMessage = 'fail';
        if ($sysSession->env === 'app') {
            $env = 'learn';
        } else {
            $env = $sysSession->env;
        }
        $webUrl = WM_SERVER_HOST . '/'. $env . '/index.php';

        // 處理語系：若不是繁體或簡體，則一律設定為繁體
        if (strtolower($sysSession->lang) !== 'big5' && strtolower($sysSession->lang) !== 'gb2312') {
            $msgLang = 'Big5';
        } else {
            $msgLang = $sysSession->lang;
        }

        switch ($code) {
            case 0:
                // 成功
                $mobileMessage = 'success';
                $webMessage = $MSG['share_note_success'][$msgLang];
                break;
            case 2:
                // 超過分享時間
                $webMessage = $MSG['share_note_over_due_time'][$msgLang];
                break;
            case 3:
                // 資料庫執行異常
                $webMessage = $MSG['share_note_db_process_fail'][$msgLang];
                break;
            case 4:
                // 找不到筆記
                $webMessage = $MSG['share_note_no_such_note'][$msgLang];
                break;
            case 5:
                // 尚未登入
                $webMessage = $MSG['share_note_not_login_yet'][$msgLang];
                $webUrl = WM_SERVER_HOST . "/index.php?action=receive-share-note&share-key=" . $shareKey;
                break;
            case 6:
                // 與分享者同一人
                $webMessage = $MSG['share_note_same_one'][$msgLang];
                break;
            default:
                $webMessage = $MSG['share_note_share_error'][$msgLang];
        }

        if ($isMobile) {
            // 若是行動裝置，則用json回傳
            // make json
            $jsonObj = array(
                'code' => $code,
                'message' => $mobileMessage,
                'data' => array()
            );

            $jsonEncode = JsonUtility::encode($jsonObj);

            // output
            header('Content-Type: application/json');
            echo $jsonEncode;
            exit();
        } else {
            // 不是行動裝置，則用javascript去處理
            exit_func_with_msg($webMessage, $webUrl);
//            if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'trident')) {
//                // For IE
//                exit_func_with_msg($webMessage, $webUrl);
//            } else {
//                // For Not IE
//                exit_func_with_msg(iconv('UTF-8', 'Big5', $webMessage), $webUrl);
//            }
        }
    }
}
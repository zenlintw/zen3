<?php
/**
 * 設定最新消息為已讀
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class SetNewsReadAction extends baseAction
{
    var $username = null;
    
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysConn;

        $this->username = mysql_real_escape_string($sysSession->username);

        // 從網址取得參數
        $newsId = $_GET['newsid'];
        $arrayNewsId = explode('_', $newsId);
        $boardId = intval($arrayNewsId[0]);
        $node = $arrayNewsId[1];

        $postWhere = "board_id={$boardId} and node='{$node}'";
        $isPost = dbGetOne('WM_bbs_posts', 'count(*)', $postWhere);

        if ($isPost) {
            // 若文章存在，才可進行設定已讀
            $table = 'WM_bbs_readed';
            $fields = '`type`, `board_id`, `node`, `username`, `read_time`';
            $values = "'b', {$boardId}, '{$node}', '{$this->username}', NOW()";
            dbNew($table, $fields, $values);
            if ($sysConn->Affected_Rows() > 0) {
                $message = 'success';
            } else {
                $message = 'fail';
            }
        } else {
            $message = 'fail';
        }

        appSysLog(999999005, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Read News:' . $message, $this->username);
        
        // make json
        $jsonObj = array(
            'code' => 0,
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
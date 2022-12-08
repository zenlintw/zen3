<?php
/**
 * 設定最新消息為已讀
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');

class SetForumReadAction extends baseAction
{
    var $username = null;
    
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        $username = mysql_real_escape_string($sysSession->username);

        global $sysConn, $sysSession;

        // 從網址取得參數
        if ((isset($_GET['validWebService'])) && (intval($_GET['validWebService']) === 1)) {
            // 若是測試web service，則討論板編號要變動
            $boardId = 9999999999;
            $node = $_GET['post_id'];
        } else {
            $postId = $_GET['postid'];
            $arrayPostId = explode('_', $postId);
            $boardId = intval($arrayPostId[0]);
            $node = $arrayPostId[1];
        }

        $postWhere = "board_id={$boardId} and node='{$node}'";
        $isPost = dbGetOne('WM_bbs_posts', 'count(*)', $postWhere);

        if ($isPost) {
            // 若文章存在，才可進行設定已讀
            $table = 'WM_bbs_readed';
            $fields = '`type`, `board_id`, `node`, `username`, `read_time`';
            $values = "'b', {$boardId}, '{$node}', '{$username}', NOW()";
            dbNew($table, $fields, $values);
	    dbSet('WM_bbs_posts', "`hit` = `hit` + 1", $postWhere);
            if ($sysConn->Affected_Rows() > 0) {
                $code = 0;
                $message = 'success';
            } else {
                $code = 2;
                $message = 'fail';
            }
            
        } else {
            $code = 3;
            $message = 'fail';
        }

        appSysLog(999999008, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Read Course Post:' . $message, $username);

        // make json
        // $code: 0 成功、2 失敗(塞入資料庫失敗)、3 失敗(取不到原始文章資料)
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
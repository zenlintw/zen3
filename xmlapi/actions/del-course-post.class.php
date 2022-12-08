<?php
/**
 * 刪除討論板文章
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');
require_once(sysDocumentRoot . '/lib/quota.php');
require_once(sysDocumentRoot . '/lib/file_api.php');

class DelCoursePostAction extends baseAction
{
    var $username = null;

    /*
     * 刪除文章(一般區)
     *
     * @param integer $boardId 討論板編號
     * @param string $node 文章編號
     * @param integer $courseId 課程編號
     * @param string $poster 張貼者
     * @param string $site 站台
     *
     * @return true
     */
    function deletePost($boardId, $node, $courseId, $poster, $site) {
        
        dbDel('`WM_bbs_posts', "`board_id` = '{$boardId}' AND `node` = '{$node}' AND `site` = '{$site}'");
        dbDel('`WM_bbs_readed`', "`type` = 'b' AND `board_id` = '{$boardId}' AND `node` = '{$node}'");
        dbDel('`WM_bbs_ranking`', "`type` = 'b' AND `board_id` = '{$boardId}' AND `node` = '{$node}' AND `site` = '{$site}'");
    
        // 如果刪除主題一併刪除回覆、留言
        if (strlen($node) === 9) {
            dbDel('`WM_bbs_posts`', "`board_id` = '{$boardId}' AND substr(node, 1, 9) = '{$node}' AND `site` = '{$site}'");
            dbDel('`WM_bbs_readed`', "`type` = 'b' AND `board_id` = '{$boardId}' AND substr(`node`, 1,9) = '{$node}'");
            dbDel('`WM_bbs_ranking`', "`type`='b' AND `board_id` = '{$boardId}' AND substr(`node`, 1,9) = '{$node}' AND `site` = '{$site}'");
            dbDel('`WM_bbs_whispers`', "`board_id` = '{$boardId}' AND substr(`node`, 1, 9) = '{$node}' AND `site = '{$site}'");
        }
    
        // 如果刪除回覆一併刪除留言
        if (strlen($node) === 18) {
            dbDel('`WM_bbs_whispers`', "`board_id` = '{$boardId}' AND `node` = '{$node}' AND `site` = '{$site}'");
        }

        // 刪除夾檔
        $attach_path = get_attach_file_path('board', $courseId) . DIRECTORY_SEPARATOR . $node;
        if (is_dir($attach_path)) {
            exec('/bin/rm -rf ' . $attach_path);
        }
    
        // 更新 Quota 資訊
        getCalQuota($courseId, $quota_used, $quota_limit);
        setQuota($courseId, $quota_used);
    
        dbSet('WM_term_major', 'post_times=post_times-1', "username='{$poster}' and course_id='{$courseId}' and post_times>0");
        dbSet('WM_term_course', 'post_times=post_times-1', "course_id='{$courseId}' and post_times>0");

        return true;
    }

    
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;
        
        $username = $sysSession->username;
        $courseId = $sysSession->course_id;

        // 預設不具刪除權限
        $canDelete = false;

        // 從網址取得參數
        if ((isset($_GET['validWebService'])) && (intval($_GET['validWebService']) === 1)) {
            // 若是測試web service，則討論板編號要變動
            $boardId = 9999999999;
            $node = $_GET['post_id'];
        } else {
            $postId = $_GET['post_id'];
            $arrayPostId = explode('_', $postId);
            $boardId = intval($arrayPostId[0]);
            $node = $arrayPostId[1];
        }

        if (!preg_match('/^[\w-]+$/', $username) ||
            !preg_match('/^\d{8}$/', $courseId) ||
            !preg_match('/^\d{10}$/', $boardId)) {
            // 帳號或課程編號或討論板編號格式不合
            $code = '2';
            $message = 'fail';
        } else {
            $postWhere = "board_id={$boardId} AND node='{$node}'";
            list($poster, $site) = dbGetStSr('WM_bbs_posts', 'poster', $postWhere, ADODB_FETCH_NUM);

            if ($poster) {
                if ($poster !== $username) {
                    // 討論板管理者，可以刪除文章
                    $canDelete = checkBoardManager($username, $courseId, $boardId);
                } else {
                    // 本身就是張貼者，可以刪除文章
                    $canDelete = true;
                }

                if ($canDelete) {
                    // 具可刪除權限
                    $deletePost = $this->deletePost($boardId, $node, $courseId, $poster, $site);
                    if ($deletePost) {
                        $code = 0;
                        $message = 'success';
                    }
                } else {
                    // 不具刪除權限
                    $code = 3;
                    $message = 'fail';
                }
            } else {
                $code = 4;
                $message = 'fail';
            }
        }

        appSysLog(999999007, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], 'Delete Course Post:' . $message, $sysSession->username);
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
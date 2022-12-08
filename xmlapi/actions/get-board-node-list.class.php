<?php
/**
 * 新版取得課程議題討論板的主題文章(不含文章內容)(主要函式與WEB相同)
 */

include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
include_once(sysDocumentRoot.'/lib/lib_forum.php');

class GetBoardNodeListAction extends baseAction
{
    function reGenerateValueHandler ($data) {
        global $sysSession;

        $allNodes = array();

        foreach ($data as $nodeID => $nodeData) {
            $node['board_id'] = intval($nodeData['boardid']);
            $node['node'] = trim($nodeData['node']);
            $node['poster'] = trim($nodeData['poster']);
            $node['realname'] = trim($nodeData['realname']);
            $node['subject'] = trim($nodeData['subject']);
            $node['hit'] = intval($nodeData['hit']);
            $node['push'] = intval($nodeData['push']);
            $node['floor'] = intval($nodeData['floor']);
            $node['read'] = (intval($nodeData['readflag']) === 1) ? true : false;
            $node['reply'] = intval($nodeData['reply']);
            $node['whisper'] = intval($nodeData['whisper']);
            $node['push'] = intval($nodeData['push']);
            $node['i_pushed'] = (intval($nodeData['pushflag']) === 1) ? true : false;
            $node['can_delete'] = verifyDeleteRight($node['poster'], $node['board_id']);

            // 處理文章附檔
            $attachmentPath = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/%10d/%s/', $sysSession->school_id, $sysSession->course_id, 'board', $node['board_id'], $node['node']);
            $node['attachment'] = generatePostAttachment(trim($nodeData['attachment']), $attachmentPath);

            $allNodes[] = $node;
        }
        return $allNodes;
    }

    function main()
    {
        parent::checkTicket();
        global $sysSession;

        $code = 0;
        $message = 'success';
        $topicsData = array();

        $bid = intval($_REQUEST['bid']);
        $offset = (isset($_REQUEST['offset'])) ? intval($_REQUEST['offset']) : 0;
        $size = (isset($_REQUEST['size'])) ? intval($_REQUEST['size']) : 10;
        $keyword = (isset($_REQUEST['keyword']) && trim($_REQUEST['keyword']) !== '') ? trim($_REQUEST['keyword']) : '';
        // wmpro5 的分頁是用 page + perPage，app 是用 offset + perPage(size)，這邊須做個轉換 for pro5
        $page = ($offset / $size) + 1;

        $rsForum = new forum();
        $forumNews = $rsForum->getCourseForumNews($sysSession->course_id, $bid, array(), '1', $page, $size, $keyword);
        $topics = $forumNews['data'];
        $totalSize = intval($forumNews['total']);

        if (count($topics) > 0) {
            $topicsData = $this->reGenerateValueHandler($topics);
        }

        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'total_size' => $totalSize,
            'data' => $topicsData
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
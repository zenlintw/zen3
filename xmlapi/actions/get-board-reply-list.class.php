<?php
/**
 * 新版取得課程議題討論板的主題文章內容與回覆文章(主要函式與WEB相同)
 */

include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
include_once(sysDocumentRoot.'/lib/lib_forum.php');

class GetBoardReplyListAction extends baseAction
{
    function reGenerateValueHandler ($data) {
        global $sysSession;

        $allNodes = array();

        foreach ($data as $nodeID => $nodeData) {
            if (strlen((string)$nodeData['boardid']) === 10 && intval($nodeData['boardid']) > 1000000000) {
                $node['board_id'] = intval($nodeData['boardid']);
                $node['node'] = trim($nodeData['node']);
                $node['is_topic'] = (strlen($node['node']) === 9) ? true : false;
		$node['picture'] = urlencode(base64_encode($nodeData['poster']));
                $node['poster'] = trim($nodeData['poster']);
                $node['realname'] = trim($nodeData['realname']);
                $node['subject'] = trim($nodeData['subject']);
                $node['post_date'] = trim($nodeData['postdate']);
                $node['post_date_description'] = trim($nodeData['postdatelen']);
                $node['content'] = trim($nodeData['postcontent']);
                $node['hit'] = intval($nodeData['hit']);
                $node['push'] = intval($nodeData['push']);
                $node['i_pushed'] = (intval($nodeData['pushflag']) === 1) ? true : false;
                $node['floor'] = intval($nodeData['floor']);
                $node['can_delete'] = verifyDeleteRight($node['poster'], $node['board_id']);
                $node['whispercnt'] = intval($nodeData['whispercnt']);

                // 處理文章附檔
                $attachmentPath = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/%10d/%s/', $sysSession->school_id, $sysSession->course_id, 'board', $node['board_id'], $node['node']);
                $node['attachment'] = generatePostAttachment(trim($nodeData['attachment']), $attachmentPath, 'APP');

                $allNodes[] = $node;
            }
        }


        return $allNodes;
    }

    function main()
    {
        parent::checkTicket();

        global $sysSession, $sysConn;

        $code = 0;
        $message = 'success';
        $topics = array();
        $replyData = array();

        $bid = intval($_REQUEST['bid']);
        $nid = trim($_REQUEST['nid']);
        $offset = (isset($_REQUEST['offset'])) ? intval($_REQUEST['offset']) : 0;
        $size = (isset($_REQUEST['size'])) ? intval($_REQUEST['size']) : 10;
        $keyword = (isset($_REQUEST['keyword']) && trim($_REQUEST['keyword']) !== '') ? trim($_REQUEST['keyword']) : '';
        // wmpro5 的分頁是用 page + perPage，app 是用 offset + perPage(size)，這邊須做個轉換 for pro5
        $page = ($offset / $size) + 1;

        // 取得主題文章
        if ($offset === 0) {
            $rsForum = new forum();
            $forumNews = $rsForum->getCourseForumNews($sysSession->course_id, $bid, array($bid . '|' . $nid), '1', $page, $size, $keyword);
            $topics = $forumNews['data'];
        }

        // 取得回覆文章
        $rsReply = new forum();
        $replyResult = $rsReply->getReply($bid, array(array($bid, $nid)), array(), $page, $size, $keyword, 'pt', 'asc', true);

        $replyResultData = $replyResult[$bid . '|' . $nid]['data'];
        $total = intval($replyResult['total_rows']);

        if (count($topics) * count($replyResultData) !== 0) {
            // 主題文章與回覆文章都不為空的時候，要做merge
            $data = array_merge($topics, $replyResultData);
        } else if (count($topics) > 0) {
            // 只有主題文章時
            $data = $topics;
        } else {
            // 只有回覆文章時
            $data = $replyResultData;
        }

        if (count($data) > 0) {
            $replyData = $this->reGenerateValueHandler($data);
        }

        // 寫下閱讀紀錄
        dbNew('WM_bbs_readed','type, board_id, node, username, read_time', "'b', {$bid}, '{$nid}', '{$sysSession->username}', Now()");
        if($sysConn->Affected_Rows() === false) {
            dbSet('LOW_PRIORITY WM_bbs_readed', 'read_time = Now()', "type = 'b' and board_id = {$bid} and node = '{$nid}' and username = '{$sysSession->username}'");
        }

        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'total_size' => $total,
            'data' => $replyData
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
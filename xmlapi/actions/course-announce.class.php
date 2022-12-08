<?php
/**
 * 取得課程公告
 */
include_once(dirname(__FILE__).'/action.class.php');

class CourseAnnounceAction extends baseAction
{
    var $username = null;

    function getCount($boardId)
    {
        global $sysConn;
        $whereSql = "board_id={$boardId}";
        $ct = dbGetOne(
            "WM_bbs_posts", 
            "count(*) as ct", 
            $whereSql
        );
        return $ct;
    }
    
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;
        
        $username = $sysSession->username;

        // 取得課程公告
        global $sysSession;

        // 從網址取得參數
        $courseId = intval($_GET['cid']);
        $offset = isset($_GET['offset'])? max(0, intval($_GET['offset'])) : 0;
        $size = isset($_GET['size'])? max(1, intval($_GET['size'])) : 10;

        // 從courseId去撈公告板的查board_id，再去撈WM_bbs_posts，並比對WM_bbs_readed，檢查是否已閱讀
        $fields = 'T1.board_id, T1.node, T1.subject, T1.content, T1.pt, T1.attach, T2.username';
        $tables = '`WM_bbs_posts` as T1 '.
                  'LEFT JOIN WM_bbs_readed as T2 '.
                  "ON T1.board_id=T2.board_id AND T2.type='b' AND T1.node=T2.node AND T2.username='{$username}'";
        $where = "T1.board_id = (SELECT bulletin FROM WM_term_course WHERE course_id={$courseId}) ";
        $where .= sprintf("ORDER BY pt DESC limit %d,%d", $offset, $size);
        
        $rs = dbGetStMr($tables, $fields, $where, ADODB_FETCH_ASSOC);
        if ($rs) {
            $schoolId = $sysSession->school_id;
            while ($row = $rs->FetchRow()) {
                // 處理前置夾檔路徑
                $attachPath = sprintf('%s/base/%5d/course/%8d/board/%10d/', WM_SERVER_HOST, $schoolId, $courseId, $row['board_id']);

                $data['post_id'] = $row['board_id'] . '_' . $row['node'];
                $data['title'] = strip_tags($row['subject']);
                $data['create_datetime'] = strip_tags($row['pt']);
                $data['content'] = chgImgSrcRelative2Absolute($row['content']);

                // 在不更換APP課程公告版面之前，獨立抓取按讚數與自己是否有按讚的
                $push = dbGetOne('`WM_bbs_push`', '`push`', "`board_id` = {$row['board_id']} AND `node` = '{$row['node']}'");
                $data['push'] = intval($push);
                $i_push = dbGetOne('`WM_bbs_ranking`', 'COUNT(*)', "`board_id` = {$row['board_id']} AND `node` = '{$row['node']}' AND `username` = '{$sysSession->username}' AND `score` = 7");
                $data['i_pushed'] = (intval($i_push) > 0) ? true : false;
                
                if (is_null($row['username'])) {
                    $data['readed'] = 0;
                } else {
                    $data['readed'] = 1;
                }

                if (!empty($row['attach'])) {
                    // 有附檔
                    // 補上node目錄
                    $attachURL = $attachPath . $row['node'].'/';
                    // 文章附檔
                    $data['attaches'] = makeAttachments($row['attach'], $attachURL);
                } else {
                    // 沒有附檔
                    $data['attaches'] = array();
                }
                
                $datas[] = $data;
                $bulletinBoard = $row['board_id'];
            }
        }
        
        // 把公告討論版的ID傳去計算總篇數，若沒有回傳篇數，則預設為0
        $totalSize = $this->getCount($bulletinBoard);
        if ($totalSize === false) {
            $totalSize= 0;
        }
        
        // make json
        $jsonObj = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
            'total_size' => intval($totalSize),
            'list' => $datas,
            ),
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
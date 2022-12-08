<?php
/**
 * 取得除了課程公告外的議題討論板列表
 */

include_once(dirname(__FILE__).'/action.class.php');

class GetCourseSubjectListAction extends baseAction
{
    function main()
    {
        parent::checkTicket();
        global $sysSession;

        $code = 0;
        $message = 'success';
        $allBoards = array();
        $bulletinSQL = '';
        
        $courseId = (isset($_REQUEST['cid'])) ? $_REQUEST['cid'] : $sysSession->course_id;
        $isIncludeBulletin = (isset($_REQUEST['include']) && intval($_REQUEST['include']) === 1) ? true : false;

        list($bulletin, $discuss) = dbGetStSr('WM_term_course', '`bulletin`, `discuss`', "`course_id` = {$courseId}", ADODB_FETCH_NUM);
        $bulletin = intval($bulletin);
        $discuss = intval($discuss);

        $table = 'WM_bbs_boards';
        $fields = '`board_id`, `bname`, `title`, `open_time`, `close_time`, `share_time`';
        if (!$isIncludeBulletin) {
            // 不包含課程公告
            $bulletinSQL = " AND `board_id` != {$bulletin}";
        }
        $where = "`owner_id`={$courseId} AND `board_id` IN (SELECT `board_id` FROM `WM_term_subject` WHERE `course_id` = {$courseId}  AND `state` = 'open'" . $bulletinSQL . ")";
        
        $boards = dbGetStMr($table, $fields, $where);

        if ($boards) {
            $i = 1;
            // SQL 執行成功
            while (!$boards->EOF) {
                $thisBoardID = intval($boards->fields['board_id']);
                $board['board_id'] = $thisBoardID;
                $boardName = getCaption($boards->fields['bname']);
                $board['board_name'] = $boardName[$sysSession->lang];
                $board['title'] = $boards->fields['title'];
                $board['open_time'] = is_null($boards->fields['open_time']) ? '' : $boards->fields['open_time'];
                $board['close_time'] = is_null($boards->fields['close_time']) ? '' : $boards->fields['close_time'];
                $board['share_time'] = is_null($boards->fields['share_time']) ? '' : $boards->fields['share_time'];
                $board['status'] = boardStatus($board['open_time'], $board['close_time'], $board['share_time']);
                $board['is_manager'] = checkBoardManager($sysSession->username, $courseId, $boards->fields['board_id']);
                $board['is_bulletin'] = ($thisBoardID === $bulletin) ? true : false;
    
                if ($thisBoardID === $bulletin) {
                    // 課程公告
                    $board['permute'] = 1;
                } else if ($board['board_id'] === $discuss) {
                    // 課程討論
                    $board['permute'] = 2;
                } else {
                    // 其他議題討論板
                    $board['permute'] = $i + 1;
                }

                $allBoards[] = $board;

                $boards->MoveNext();
            }
        } else {
            // SQL 執行失敗
            $code = 2;
            $message = 'SQL Error';
        }

        
        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => $allBoards
        );

        $jsonEncode = JsonUtility::encode($responseObject);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
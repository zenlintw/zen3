<?php
/**
 * 新版取得課程議題討論板(主要函式與WEB相同)
 */

include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
include_once(sysDocumentRoot.'/lib/lib_forum.php');

class GetBoardListAction extends baseAction
{
    function boardStatus ($open, $close, $share) {
        $now = time();
        // 注意！時間格式與WM3不同(0000-00-00 00:00:00)
        $isBeginSet = ($open !== '' && $open !== '0000-00-00 00:00') ? true : false;
        $isEndSet = ($close !== '' && $close !== '0000-00-00 00:00') ? true : false;
        $isShareSet = ($share !== '' && $share !== '0000-00-00 00:00') ? true : false;

        // 有設定起訖時間
        if ($isBeginSet || $isEndSet) {
            // 目前未到開放時間
            if ($isBeginSet && $now < strtotime($open)) {
                // 目前未到開放時間
                return 'NOT-OPEN';
            } else if ($isEndSet && $now > strtotime($close)) {
                if ($isShareSet && $now > strtotime($share)) {
                    // 有開放參觀，且目前時間超過啟用參觀的時間
                    return 'SHARE';
                }
                // 目前未到開放時間
                return 'CLOSED';
            }
        }
        // 沒有設定起迄時間
        return 'OPEN';
    }

    function reGenerateValueHandler ($data, $courseId) {
        global $sysSession;

        list($bulletinID, $discussID) = dbGetStSr('WM_term_course', '`bulletin`, `discuss`', "`course_id` = {$courseId}", ADODB_FETCH_NUM);

        $allBoards = array();
        $i = 1;
        foreach ($data as $boardId => $boardData) {
            $board['board_id'] = intval($boardData['board_id']);
            if ($board['board_id'] === intval($bulletinID)) {
                // 課程公告
                $board['permute'] = 1;
            } else if ($board['board_id'] === intval($discussID)) {
                // 課程討論
                $board['permute'] = 2;
            } else {
                // 其他議題討論板
                $board['permute'] = $i + 1;
            }
            $board['board_name'] = trim($boardData['board_name']);
            $board['title'] = trim($boardData['title']);
            $board['canRead'] = ($boardData['canRead'] === 'Y') ? true : false;
            $board['open_time'] = ($boardData['open_time'] === '0000-00-00 00:00') ? '' : trim($boardData['open_time']);
            $board['close_time'] = ($boardData['close_time'] === '0000-00-00 00:00') ? '' : trim($boardData['close_time']);
            $board['share_time'] = ($boardData['share_time'] === '0000-00-00 00:00') ? '' : trim($boardData['share_time']);
            $board['update_date'] = ($boardData['update_date'] === '0000-00-00 00:00') ? '' : trim($boardData['update_date']);
            $board['update_date_description'] = trim($boardData['update_date_lengh']);
            $board['subject_cnt'] = intval($boardData['subject_cnt']);
            $board['read_flag'] = (isset($boardData['read_flag'])) ? $boardData['read_flag'] : false;
            $board['poster'] = trim($boardData['poster']);
            $board['status'] = $this->boardStatus($boardData['open_time'], $boardData['close_time'], $boardData['share_time']);
            $board['state'] = strtoupper(($boardData['state'] === '') ? 'open' : trim($boardData['state']));
            $board['owner_id'] = intval($boardData['owner_id']);
            $board['is_manager'] = checkBoardManager($sysSession->username, $courseId, $board['board_id']);
            $board['is_bulletin'] = ($board['board_id'] === intval($bulletinID)) ? true : false;
            $allBoards[] = $board;
        }
	    return $allBoards;
    }

    function main()
    {
        parent::checkTicket();
        global $sysSession;

        $code = 0;
        $message = 'success';
        $allBoards = array();

        // _REQUEST是為了透過測試而用，真正使用是透過sysSession
        $courseId = (isset($_REQUEST['cid'])) ? $_REQUEST['cid'] : $sysSession->course_id;
        $isIncludeBulletin = (isset($_REQUEST['include']) && intval($_REQUEST['include']) === 1) ? true : false;
        $sysSession->course_id = $courseId;
        $sysSession->env = 'learn';
        $sysSession->restore();

        if ($isIncludeBulletin) {
            // 把公告納入議題討論板
            $courseBoard = getCourseBoard($courseId);
        } else {
            // 取課程公告版編號
            $rsForum = new forum();
            $courseAnnId = $rsForum->getCourseAnnId($courseId);
            // 取得公告版以外的討論版
            $courseBoard = getCourseBoard($courseId, array($courseAnnId));
        }

        if (count($courseBoard) > 0) {
            $allBoards = $this->reGenerateValueHandler($courseBoard, $courseId);
        }
        
        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => $allBoards
        );

        $jsonHandler = new JsonUtility();
        $jsonEncode = $jsonHandler->encode($responseObject);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
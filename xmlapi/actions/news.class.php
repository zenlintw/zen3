<?php
/**
 * 取得最新消息
 */

include_once(dirname(__FILE__).'/action.class.php');

class NewsAction extends baseAction
{
    var $_mysqlUsername = '';

    function main()
    {
        parent::checkTicket();

        global $sysConn, $sysSession;

        $sqlKeyword = '';
        $orderWhere = 'ORDER BY T2.pt DESC';
        $sizeWhere = '';

        if (isset($_GET['offset']) && isset($_GET['size'])) {
            $sizeWhere = sprintf(" LIMIT %d,%d", $_GET['offset'], $_GET['size']);
        }

        if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
            $keyword = iconv('Big5', 'UTF-8', trim($_GET['keyword']));
            $keyword = mysql_real_escape_string($keyword);
            $sqlKeyword = "AND LOCATE('{$keyword}', T2.`subject`) ";
        }

        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);
        $schoolId = $sysSession->school_id;

        // 取得最新消息討論板ID
        $newsBoardId = dbGetOne('WM_news_subject', 'board_id', 'type="news"');

        $fields = "SQL_CALC_FOUND_ROWS T1.board_id, T1.node, T2.subject AS title, T2.content, T2.pt AS create_datetime, T2.attach, T3.username";
        $tables = "WM_news_posts AS T1 ".
                "LEFT JOIN WM_bbs_posts AS T2 ".
                "ON T1.board_id = T2.board_id AND T1.node = T2.node ".
                "LEFT JOIN (SELECT board_id, node, username FROM WM_bbs_readed WHERE board_id={$newsBoardId} AND type='b' AND username = '{$this->_mysqlUsername}') AS T3 ".
                "ON T2.board_id = T3.board_id AND T2.node = T3.node";
        $where = '(open_time IS NULL OR open_time="0000-00-00" OR open_time<=NOW()) AND ' .
                '(close_time IS NULL OR close_time="0000-00-00" OR close_time>NOW()) AND ' .
                "T1.board_id = {$newsBoardId} ";
        $where .= $sqlKeyword;
        $where .= $orderWhere;
        $where .= $sizeWhere;

        chkSchoolId("WM_news_posts");
        $rs = dbGetStMr($tables, $fields, $where, ADODB_FETCH_ASSOC);

        $datas = array();
        if ($rs) {
            $totalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

            // 處理前置夾檔路徑
            $attachPath = sprintf('%s/base/%5d/board/%10d/', WM_SERVER_HOST, $schoolId, $newsBoardId);
            $schoolName = getCurrentSchoolName();

            while ($row = $rs->FetchRow()) {
                $data['news_id'] = $row['board_id'] . '_' . $row['node'];
                $data['create_datetime'] = str_replace('-', '/', $row['create_datetime']);
                $data['unit'] = $schoolName;
                $data['title'] = trim(strip_tags($row['title']));
                $data['content'] = chgImgSrcRelative2Absolute($row['content']);

                // 在不更換APP最新消息版面之前，獨立抓取按讚數與自己是否有按讚的
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
            }
            $code = 0;
            $message = 'success';
        } else {
            $code = 2;
            $message = 'fail';
            $totalSize = 0;
        }

        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'total_size' => $totalSize,
                'list' => $datas,
            )
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
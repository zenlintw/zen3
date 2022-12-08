<?php
/**
 * 取得課程討論版文章
 */

include_once(dirname(__FILE__).'/action.class.php');

class GetCourseForumAction extends baseAction
{
    var $courseId, $offset, $size, $message, $postsCount = 0, $attachPath, $attachURL;

    /*
     * 取得討論板所有文章的篇數
     * @param integer $boardId 討論板編號
     * @return integer 文章總數
     */
    function getPostsCount($boardId)
    {
        $postsCount = dbGetOne('WM_bbs_posts','count(*)', "board_id={$boardId}");
        return intval($postsCount);
    }

    function main()
    {
        // 先檢查ticket是否正確
        parent::checkTicket();
        global $sysSession, $sysConn;

        $validBoard = true;

        if (!empty($_GET['cid'])) {
            // 如果有course id
            $courseId = intval(trim($_GET['cid']));

            if (!empty($_GET['bid'])) {
                $discussBoardId = intval($_GET['bid']);
                $checkBoard = dbGetOne('WM_term_subject', 'count(*)', "`course_id` = {$courseId} AND `board_id` = {$discussBoardId}");
                if ($checkBoard === 0) {
                    $validBoard = false;
                }
            } else {
                // 取得課程討論板編號
                $discussBoardId = dbGetOne('WM_term_course', 'discuss', "course_id={$courseId}");
            }

            // pageSize與offset的前置處理
            if (isset($_GET['pagesize'])) {
                $this->size = isset($_GET['pagesize'])? max(1, intval($_GET['pagesize'])) : 10;
                $page = isset($_GET['page'])? max(1, intval($_GET['page'])) : 1;
                $this->offset = ($page-1)*$this->size;
            } else {
                $this->offset = isset($_GET['offset'])? max(0, intval($_GET['offset'])) : 0;
                $this->size = isset($_GET['size'])? max(1, intval($_GET['size'])) : 10;
            }

            if ($validBoard) {
                $username = mysql_real_escape_string($sysSession->username);
                $schoolId = $sysSession->school_id;

                // 處理前置夾檔路徑
                $attachPath = sprintf('%s/base/%5d/course/%8d/board/%10d/', WM_SERVER_HOST, $schoolId, $courseId, $discussBoardId);

                //取得討論版文章與已讀狀態
                $fields = "T1.board_id, T1.node, T1.subject, T1.content, T1.pt, T1.poster, T1.attach, T2.username";
                $tables = "WM_bbs_posts AS T1 ".
                          "LEFT JOIN (SELECT board_id, node, username FROM WM_bbs_readed WHERE board_id={$discussBoardId} AND type='b' AND username = '{$username}') AS T2 ".
                          "ON T1.node = T2.node";
                $where = "T1.board_id = {$discussBoardId}";
                $where .= sprintf(" ORDER BY T1.pt DESC limit %d,%d", $this->offset, $this->size);

                chkSchoolId("WM_bbs_posts");
                $rs = dbGetStMr($tables, $fields, $where, ADODB_FETCH_ASSOC);

                if ($rs) {
                    while ($row = $rs->FetchRow()) {
                        // 文章編號
                        $data['post_id'] = $row['board_id'] . '_' . $row['node'];
                        // 文章標題
                        $data['subject'] = strip_tags($row['subject']);
                        // 張貼者
                        $data['poster'] = $row['poster'];
                        // 張貼時間
                        $data['post_time'] = str_replace('-', '/', $row['pt']);
                        // 張貼內容
                        $data['content'] = chgImgSrcRelative2Absolute($row['content']);

                        if ($username === $row['poster']) {
                            // 使用者是張貼者
                            $data['deletable'] = true;
                        } else {
                            // 使用者不是張貼者，則判斷是否具有管理權限
                            $data['deletable'] = checkBoardManager($username, $courseId, $discussBoardId);
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

                        // 已讀、未讀
                        if (is_null($row['username'])) {
                            // 若是null，表示未讀
                            $data['readed'] = 0;
                        } else {
                            // 若不是null，表示已讀
                            $data['readed'] = 1;
                        }
                        $datas[] = $data;
                    }
                    $code = 0;
                    $message = 'success';
                    $postsCount = $this->getPostsCount($discussBoardId);
                }
            } else {
                // 找不到討論板
                $code = 2;
                $message = 'fail';
            }

        } else {
            // 如果沒有course id
            $code = 3;
            $message = 'fail';
        }

        $header = '';
        $body = '';

        // $code: 0 => success, 2: 沒有討論板編號, 3: 沒有課程編號
        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
            'total_size' => $postsCount,
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
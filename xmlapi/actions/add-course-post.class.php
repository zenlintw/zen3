<?php
/**
 * 張貼課程討論板文章
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/lib/detect_malicious_data.php');
include_once(PATH_LIB . 'course.php');

// 避免timeout
set_time_limit(0);
// 避免連線斷線後，後端處理也中斷
ignore_user_abort(false);

class AddCoursePostAction extends baseAction
{
    var $postTable = '`WM_bbs_posts`';
    var $content = '';
    var $boardId = 0;
    var $node = '';
    var $postAttaches = NULL;
    var $serverAttach = array();
    var $clientAttach = array();

    function addPost ($discussBoardId, $postContent) {
        $this->content = trimHtml(strip_scr($postContent));

        $this->boardId = $discussBoardId;
        // 取得目前板中最大的 node
        $mnode = dbGetOne($this->postTable, 'MAX(node)', "board_id={$discussBoardId} AND length(node) = 9");
        // 產生本篇的 node
        $this->node = empty($mnode)?'000000001':sprintf("%09d", $mnode+1);
    }

    function modifyPost ($postId, $postContent) {
        $arrayPostId = explode('_', $postId);
        $this->boardId = $arrayPostId[0];
        $this->node = $arrayPostId[1];
        $this->content = trimHtml(strip_scr($postContent));
        
        $attach = dbGetOne($this->postTable, '`attach`', "`board_id` = {$this->boardId} AND `node` = '{$this->node}'");
        if ($attach !== '') {
            $this->serverAttach = explode("\t", $attach);
        }
    }

    function replyPost ($postReplyPostId, $postContent) {
        $arrayPostReplyPostId = explode('_', $postReplyPostId);
        $this->boardId = $arrayPostReplyPostId[0];
        $node = $arrayPostReplyPostId[1];

        $this->content = trimHtml(strip_scr($postContent));

        $nodeWhere = "board_id={$this->boardId} AND node like '" . substr($node, 0, 9) . "%'";
        $mnode = dbGetOne($this->postTable, 'MAX(node)', $nodeWhere);
        // 產生本篇的 node
        // 雙層架構
        $this->node = (strlen($mnode) == 9) ? ($node . '000000001') : sprintf('%s%09d', substr($mnode, 0, 9), intval(substr($mnode, -9))+1);
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysConn, $sysSession;

        $username = trim($sysSession->username);
        $realname = trim($sysSession->realname);
        $courseId = trim($sysSession->course_id);
        $schoolId = trim($sysSession->school_id);
        $email = trim($sysSession->email);
        $homepage = trim($sysSession->homepage);

        $data = array();

        $sysSiteNo = 1000100000 + $schoolId;
        $postTable = 'WM_bbs_posts';
        $postFields = 'board_id,node,site,pt,poster,realname,email,homepage,subject,content,attach';

        // 處理接收的資料 - Begin
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);

        $subject = addslashes(trim($postData['subject']));
        $postContent = trim($postData['content']);
        $postId = trim($postData['post_id']);
        $postReplyPostId = trim($postData['reply_post_id']);

        if (isset($postData['board_id'])) {
            // 若是有把board id傳來，則已傳過來的編號為主，並且先行驗證
            $courseHandler = new UserCourse();
            $discussBoardId = $courseHandler->validCourseSubject($courseId, $postData['board_id']);
        } else {
            // 取得課程討論板編號
            $discussBoardId = dbGetOne('WM_term_course', 'discuss', "course_id={$courseId}");
            $discussBoardId = intval($discussBoardId);
        }
        // 處理接收的資料 - End

        // 依據送過來的資料判斷是新增或是修改
        if ($postReplyPostId !== '') {
            // 有傳送回覆的ID，表示為回覆文章
            $postAction = 'reply';
            $this->replyPost($postReplyPostId, $postContent);
        } else if ($postId !== '' && $postReplyPostId === '') {
            // 有張貼編號，沒有回覆ID，表示為修改文章
            $postAction = 'modify';
            $this->modifyPost($postId, $postContent);
        } else {
            // 都沒有，則表示為新增文章
            $postAction = 'add';
            $this->addPost($discussBoardId, $postContent);
        }

        if ($this->boardId !== 0) {
            $content = mysql_real_escape_string(htmlspecialchars_decode(nl2br($this->content)));

            // 處理夾檔
            if ($postData['attaches'][0]['filename'] !== '') {
                $this->clientAttach = $postData['attaches'];
            }

            if (count($this->clientAttach) > 0 || count($this->serverAttach) > 0) {
                $attachPath = sysDocumentRoot . '/base/' . $schoolId . '/course/' . $courseId . '/board/' . $discussBoardId . '/' . $this->node . '/';
                if (!is_dir($attachPath)) {
                    mkdir($attachPath, 0700, true);
                }
                $this->postAttaches = attachmentsHandler($this->serverAttach, $this->clientAttach, $attachPath, 'APP');
            }

            if ($this->postAttaches !== 'fail') {
                if ($postAction === 'add' || $postAction === 'reply') {
                    // 新增或回覆都是要在資料表新增一筆資料
                    $values = "$discussBoardId, '$this->node', $sysSiteNo".
                        ", NOW(), '$username', '$realname ', ".
                        "'$email', '$homepage ', '$subject', '$content ', '$this->postAttaches'";

                    dbNew($postTable, $postFields, $values);
                } else {
                    // 修改文章只會修改內容、標題、附檔
                    $subjectString = ($subject === '') ? '' : "`subject` = '{$subject}'";
                    $values = "`content` = '{$content}', " . $subjectString . ", `attach` = '{$this->postAttaches}'";
                    $where = "`board_id` = {$this->boardId} AND `node` = '{$this->node}'";

                    dbSet($this->postTable, $values, $where);
                }

                if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                    $code = 2;
                    $message = 'fail';
                } else {
                    $code = 0;
                    $message = 'success';

                    $data = array(
                        'postNodeId' => $this->node
                    );

                    if ($postAction === 'add' || $postAction === 'reply') {
                        // 遞增自己跟課程的張貼數
                        dbSet('WM_term_major',  'post_times=post_times+1', "username='{$username}' and course_id='{$courseId}'");
                        dbSet('WM_term_course', 'post_times=post_times+1', "course_id='{$courseId}'");
                    }
                }
            } else {
                $code = 4;
                $message = 'Attach Error.';
            }
        } else {
            $code = 3;
            $message = 'Board not found.';
        }

        appSysLog(999999006, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], $postAction . ' Course Post:' . $message, $sysSession->username);

        // make json
        $jsonObj = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        $jsonEncode = JsonUtility::encode($jsonObj);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
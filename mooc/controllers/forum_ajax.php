<?php
    /*
     * 邏輯層：功能處理
     * 接收中介層參數經處理後，傳回中介層
     *
     * @since   2014/10/17
     * @author  cch
     */
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/controllers/JSON.php');
    require_once(sysDocumentRoot . '/mooc/models/forum.php');
    require_once(sysDocumentRoot . '/forum/lib_mailfollow.php');
    require_once(sysDocumentRoot . '/lang/forum.php');

    /*
     * $json->encode, $json->decode 宣告，以利後續使用
     */

    if (!function_exists('json_encode')) {

        function json_encode($val)
        {
            $json = new Services_JSON();
            return $json->encode($val);
        }

        function json_decode($val)
        {
            $json = new Services_JSON();
            return $json->decode($val);
        }
    }

    global $sysSession;

    // 檢核參數
    if (isset($_POST['tpc']) === true && !(in_array($_POST['tpc'], array(true, false)))) {
        die('parameter is incorrect.');
    }

    switch($_POST['action']) {

        /*
         * 探索課程-取得樹狀項目課程資料
         * @param string $_POST['bid']:討論版編號
         * @param int    $curPage:     當前頁數
         * @param int    $perPage:     每頁筆數
         *
         * @return array $arr:
         */
        case "getAnnouncement":
            // 分頁處理
            if (intval($_POST['selectPage']) !== 0) {
                $curPage = intval($_POST['selectPage']);
            } else {
                $curPage = intval($_POST['page']);
            }
            if ($_POST['inputIssuesPerPage'] !== null) {
                $perPage = intval($_POST['inputIssuesPerPage']);
            } else {
                $perPage = intval($_POST['limit']);
            }

            // 取單一門課程公告:
            $rsForum = new forum();
            $forum = $rsForum->getCourseAnnouncement(
                intval($_POST['bid']),
                $curPage,
                $perPage,
                trim($_POST['inputKeyword'])
            );

            $msg = json_encode($forum);
            break;

        case "getNews":
            if (isset($_POST['bid'])) {
                $bid = array($_POST['bid']);
            } else {
                $bid = array();
            }

            // 取該課程最新文章
            $rsForum = new forum();
        
            $selectPage = (int)$_POST['selectPage'];

            $inputPerPage = (int)$_POST['inputPerPage'];
            if ($inputPerPage === 0) {
                $inputPerPage = 5;
            }
            $forumNews = $rsForum->getCourseForumNews(
                $sysSession->course_id,
                $bid,
                array(),
                $_POST['tpc'],
                $selectPage,
                $inputPerPage,
                trim($_POST['inputKeyword'])
            );

            $msg = json_encode($forumNews);
            break;

        case "getHot":
            if (isset($_POST['bid'])) {
                $bid = array($_POST['bid']);
            } else {
                $bid = array();
            }

            // 取該課程最熱門文章
            $rsForum = new forum();
        
            $selectPage = (int)$_POST['selectPage'];

            $inputPerPage = (int)$_POST['inputPerPage'];
            if ($inputPerPage === 0) {
                $inputPerPage = 5;
            }
            $forumHot = $rsForum->getCourseForumHot(
                $sysSession->course_id,
                $bid,
                $_POST['tpc'],
                $selectPage,
                $inputPerPage,
                trim($_POST['inputKeyword'])
            );

            $msg = json_encode($forumHot);
            break;
          
        case "getPush":
            if (isset($_POST['bid'])) {
                $bid = array($_POST['bid']);
            } else {
                $bid = array();
            }

            // 取該課程最佳文章
            $rsForum = new forum();
            $forumPush = $rsForum->getCourseForumPush(
                $sysSession->course_id,
                $bid,
                $_POST['tpc'],
                (int)$_POST['selectPage'],
                (int)$_POST['inputPerPage'],
                trim($_POST['inputKeyword'])
            );

            $msg = json_encode($forumPush);
            break;

        case "setPush":
            // 按讚
            $rsForum = new forum();
            $forum = $rsForum->setPush(
                $_POST['bid'],
                $_POST['nid'],
                $_POST['sid'],
                $sysSession->username,
                $_POST['firstPush']
            );

            $msg = json_encode($forum);
            break;

        case "getReply":
            // 取得回覆文章
            $rsReply = new forum();
            if(intval($_POST['bid']) == 0){
                header('HTTP/1.1 403 Forbidden');
                exit;
            }
            $reply = $rsReply->getReply($_POST['bid'], array(array($_POST['bid'], $_POST['nid'])), array(),
                (int)$_POST['selectPage'], (int)$_POST['inputPerPage'], '', 'pt', 'asc', true);

            $msg = json_encode($reply);
            break;
        
        
        case "getAssign":
            $bid = htmlspecialchars($_POST['bid']);

            // 取該課程最新文章
            $rsForum = new forum();
            $forumData = $rsForum->getForumData($bid);
			
            $msg = json_encode($forumData);
            break;

        case "setWhisper":
            // 寫留言
            $bid     = $_POST['bid'];
            $nid     = $_POST['nid'];
            $content = $_POST['content'];
            
            $rsWhisper = new forum();
            $whisper = $rsWhisper->setWhisper(
                htmlspecialchars($_POST['sid']),
                htmlspecialchars($bid),
                htmlspecialchars($nid),
                $content,
                $sysSession->username,
                $sysSession->realname,
                $sysSession->email
            );

            $msg = json_encode($whisper);
			
            if ($msg != '') {

                $school_name  = $sysSession->school_name; // 學校名稱
                $school_host  = $_SERVER['HTTP_HOST'];
                $rs           = dbGetStSr('WM_bbs_boards', 'owner_id, bname', "board_id={$bid}", ADODB_FETCH_ASSOC);
                $bname        = $rs['bname'];
                $multiCaption = getCaption($bname);
                $board_name   = $multiCaption[$sysSession->lang];

                $rs       = dbGetStSr('WM_bbs_posts', 'subject', "board_id={$bid} and node={$nid}", ADODB_FETCH_ASSOC);
                $subject  = $rs['subject'];
                $username = $sysSession->username;
                $realname = $sysSession->realname;

                // Mail follow
                $MailData = Array();

                $MailData['subject'] = stripslashes($_POST['subject']); //$subject;
                $MailData['title']   = '==================' . $school_name . "\t" . $school_host . '==================' . "<br>\r\n";
                $MailData['course']  = $MSG['mail_cname'][$sysSession->lang] . $sysSession->course_name . "<br>\r\n";
                $MailData['body']    = $MSG['mail_board'][$sysSession->lang] . $board_name . "<br>\r\n" . $MSG['mail_poster'][$sysSession->lang] . $username . '(' . $realname . ')' . "<br>\r\n" . $MSG['mail_ptime'][$sysSession->lang] . Date("Y-m-d H:i:s") . "<br>\r\n" . $MSG['mail_subject'][$sysSession->lang] . $subject . "<br><br>\r\n" . $content;

                MailFollow($MailData, $bid);
            }
            break;

        case "modWhisper":
            // 編輯留言
            $rsWhisper = new forum();
            $whisper = $rsWhisper->modWhisper(
                htmlspecialchars($_POST['wid']),
                $_POST['content'],
                $sysSession->username,
                $sysSession->realname,
                $sysSession->email
            );

            $msg = json_encode($whisper);
            break;

        case "getWhisper":
            // 取留言
            $rsWhisper = new forum();
            $whisper = $rsWhisper->getWhisper(
                array(array(htmlspecialchars($_POST['bid']), htmlspecialchars($_POST['nid']))),
                array(),
                true
            );

            $msg = json_encode($whisper);
            break;

        case "delWhisper":
            // 刪除留言
            $rsWhisper = new forum();
            $whisper = $rsWhisper->delWhisper(
                htmlspecialchars($_POST['wid']),
                $sysSession->username
            );

            $msg = json_encode($whisper);
            break;
        
        case "getnews_nologin":
            // 分頁處理
            if (intval($_POST['selectPage']) !== 0) {
                $curPage = intval($_POST['selectPage']);
            } else {
                $curPage = intval($_POST['page']);
            }
            if ($_POST['inputIssuesPerPage'] !== null) {
                $perPage = intval($_POST['inputIssuesPerPage']);
            } else {
                $perPage = intval($_POST['limit']);
            }

            // 取單一門課程公告:
            $rsForum = new forum();
            $forum = $rsForum->getSchoolAnnouncement(
                intval($_POST['bid']),
                $curPage,
                $perPage,
                trim($_POST['inputKeyword'])
            );
            $msg = json_encode($forum);
            break;
        // 取得指定課程的所有討論版
        case "getCourseForumList":
            $rsforum = new forum();
            $forum   = $rsforum->getCourseForumList(htmlspecialchars($_POST['cid']));

            $msg = json_encode($forum);
            break;

        // 轉貼指定討論版文章到指定討論版
        case "setRepost":
            $rsforum = new forum();
            $repost  = $rsforum->setRepost(htmlspecialchars($_POST['sid']), htmlspecialchars($_POST['bid']), htmlspecialchars($_POST['bid'] . '|' . $_POST['nid']), htmlspecialchars($_POST['to_sid']), htmlspecialchars($_POST['to_cid']), htmlspecialchars($_POST['to_bid']));

            $msg = json_encode($repost);
            break;

        default:
            $val = "無此動作";
            $msg = json_encode($val);
            break;
    }

    if ($msg != '') {
        echo $msg;
    }
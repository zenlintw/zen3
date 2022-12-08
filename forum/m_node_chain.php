<?php
/**
 * 討論版檢視單一主題
 *
 * @version m_node_chain.php, v 1.1 2014/12/9 17:30 cch Exp $
 * @copyright Wisdom Master 5(C)  Copyright(R) SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
require_once(sysDocumentRoot . '/mooc/models/school.php');
require_once(sysDocumentRoot . '/lang/forum.php');
require_once(sysDocumentRoot . '/lang/mooc_forum.php');
require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');

$sysSession->cur_func = '900200100';
$sysSession->restore();

if (!aclVerifyPermission(
    $sysSession->cur_func,
        aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))
    ) {
}

// 課程編號
$cid = $sysSession->course_id;
if (isset($_REQUEST['cid']) === true && intval($_REQUEST['cid']) >= 0) {
    $cid = $_REQUEST['cid'];
}

// XSS, SQL Injection防護
if (!empty($_GET['bid']) && !is_numeric($_GET['bid'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
} else {
    $bid = $_GET['bid'];
}

if (!empty($_POST['bid']) && !is_numeric($_POST['bid'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// 透過URL指定討論串編號
$std = "/^([0-9]+)$/";
if (!empty($_GET['nid']) && (preg_match($std, $_GET['nid']) === 0)) {
    header('HTTP/1.1 403 Forbidden');
    exit;
} else {
    $nid = $_GET['nid'];
}

if (strlen($bid) === 10 && preg_match($std, $bid) === 1 &&
    (strlen($nid) === 9 || strlen($nid) === 18) && preg_match($std, $nid) === 1) {
    $_POST['bid'] = $bid;
    $_POST['nid'] = $nid;
}

// XSS, SQL Injection防護
if (!empty($_POST['nid']) && (preg_match($std, $_POST['nid']) === 0)) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// 取得本 POST 內容
$RS = dbGetStSr('WM_bbs_posts', '*', "board_id = {$_POST['bid']} and node={$_POST['nid']} {$where} limit 0, 1", ADODB_FETCH_ASSOC);

if (!$RS) {
    header("Location:index.php");
    exit();
}

if (ChkBoardReadRight($_POST['bid']) === false) {
    die('Access deny: no permission.');
}

// 是否具刊登權限(含張貼, 修改, 刪除)
$updRight = ChkRight($_POST['bid']);

// 增加點閱數
dbSet('LOW_PRIORITY WM_bbs_posts', 'hit = hit + 1', "board_id = {$_POST['bid']} and node = '{$_POST['nid']}' and site = {$RS['site']} limit 1");

// 寫下閱讀紀錄
dbNew('WM_bbs_readed', 'type, board_id, node, username, read_time', "'b', {$_POST['bid']}, '{$_POST['nid']}', '{$sysSession->username}', Now()");
if ($sysConn->Affected_Rows() === false) {
    dbSet('LOW_PRIORITY WM_bbs_readed', 'read_time = Now()', "type = 'b' and board_id = {$_POST['bid']} and node = '{$_POST['nid']}' and username = '{$sysSession->username}'");
}

$rsForum    = new forum();
// 取討論版名稱
$forumTopic = $rsForum->getCourseForumList($cid, array(), false, array($_POST['bid']));
if (strlen($cid) == 8) {
    // 取課程公告版
    $bulletinId = $rsForum->getCourseAnnId($cid, 1);
    // 目前是否為公告版
    if ($bulletinId == $_POST['bid'] && strlen($_POST['bid']) == 10) {
        $bulletinFlag = '1';
    } else {
        $bulletinFlag = '0';
    }
} else {
    $bulletinFlag = '1';
    if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['owner_id']) == 5) {
        $schoolNewsBoard = true;
    }
}

// 是否為討論室紀錄
dbGetRecordBoard($cid, $rsRecordBoard);
$isRecordBoard = ((int) $_POST['bid'] === (int) $rsRecordBoard['board_id']) ? '1' : '0'; // 都轉數字比較

// 頁面標題是否是麵包屑結構
$isBreadCrumb = ($bulletinFlag === '1' || $isRecordBoard === '1') ? '0' : '1';
if ($sysSession->env == 'teach') $isBreadCrumb = 0; // custom
$smarty->assign('isBreadCrumb', $isBreadCrumb);

//若討論板的owner_id的長度為16，此討論板為分組討論
if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['owner_id']) == 16) {
    $isGroupForum = true;
} else {
    $isGroupForum = false;
}

// 討論版名稱
$forumName = $rsForum->getForumNameByBid($_POST['bid']);
$forumName = $forumName['bname'];

// 取主文
$bnid  = $_POST['bid'] . '|' . substr($_POST['nid'], 0, 9);
$topic = $rsForum->getCourseForumNews($cid, array($_POST['bid']), array($bnid), '0', 1);

if (isset($topic['data'][$bnid]) === false) {
    die('Access deny: incorrect board id & node id.');
}

// 取得學校資料
$rsSchool    = new school();
$socialShare = $rsSchool->getShareSocial($sysSession->school_id);
if ($forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['state'] === 'public') {
} else {
    $socialShare = array();
}

// meta
$baseUrl = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
$baseUrl .= '://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'];

// 分享主題：主題、固定文字、網站圖片
if (strlen($_GET['nid']) === 9) {
    // 取得學校資料
    $rsSchool   = new school();
    $schoolInfo = $rsSchool->getSchoolIndexInfo($sysSession->school_id);
    $metaDesc   = $MSG['want_answer'][$sysSession->lang];
    $metaImage  = $baseUrl . '/theme/default/learn/co_fblogo.png';
} else if (strlen($_GET['nid']) === 18) {
    // 取單一回覆資訊
    $nodeInfo  = $rsForum->getReply($_GET['bid'], array(array($_GET['bid'], $_GET['nid'])), array(), '', '', '', 'pt', 'desc', true);
    $node      = $nodeInfo[$_GET['bid'] . '|' . $_GET['nid']]['data'][$_GET['bid'] . '|' . $_GET['nid']];
    $metaDesc  = $node['poster'] . ' ( ' . trim($node['realname']) . ' ) ' . $MSG['response'][$sysSession->lang] . ': ' . $node['postcontenttext'];
    $metaImage = $baseUrl . '/co_showuserpic.php?a=' . $node['cpic'];
}
$smarty->assign('metaTitle', $topic['data'][$bnid]['subject']);
$smarty->assign('metaDescription', $metaDesc);
$smarty->assign('metaSitename', $schoolInfo['banner_title1']);
$smarty->assign('metaUrl', $_SERVER['SCRIPT_URI'] . '?' . $_SERVER['QUERY_STRING']);
$smarty->assign('metaImage', $metaImage);

// 是否顯示張貼按鈕
// 是版主一定可以PO
$managerFlag = '0';
$postFlag    = '0';
if ($sysSession->username === $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['manager']) {
    $postFlag    = '1';
    $managerFlag = '1';
} else {
    // 只有課程公告限制身份，其他身份可讀取就能張貼，接下來驗證時間
    if ($bulletinFlag == '1') {
        if ($schoolNewsBoard) {
            if ((int) aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id) >= 1) {
                $postFlag    = '1';
                $managerFlag = '1';
            } else {
                $boardType = dbGetOne('WM_news_subject', '`type`', sprintf('board_id=%d', $_POST['bid']));
                // suggest 系統建議版、comment 校務意見箱
                if (($sysSession->username !== 'guest') && in_array($boardType, array('suggest', 'comment'))) {
                    $postFlag = '1';
                }
            }
        } else {
            $postFlag = aclCheckRole($sysSession->username, $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'], $cid);
        }
    } else if ($sysSession->username == 'guest') {
        $postFlag = false;
    } else {
        $postFlag = true;
    }
    
    if ($postFlag === true || $postFlag === '1') {
        // 判斷張貼時間
        $open_time  = $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['open_time'];
        $close_time = $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['close_time'];
        if ((date('Y-m-d H:i:s') >= $open_time || $open_time === '0000-00-00 00:00') &&
            (date('Y-m-d H:i:s') < $close_time || $close_time === '0000-00-00 00:00')) {
            $postFlag = '1';
        } else {
            $postFlag = '0';
        }
    } else {
        $postFlag = '0';
    }
}

$pushPermission = 1;
// 本課相關人員可以按讚
if (strlen($cid) == 8) {
    $pushPermission = aclCheckRole($sysSession->username, $sysRoles['auditor'] | $sysRoles['student'] | $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'], $cid);
}

// 取 fb留言
$fb_comment = $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['fb_comment'];

// 取 FB id
$FBPara = $rsSchool->getSchoolFBParameter($sysSession->school_id);
$smarty->assign('FB_APP_ID', $FBPara['canReg_fb_id']);
$smarty->assign('postFlag', $postFlag);
$smarty->assign('managerFlag', $managerFlag);
$smarty->assign('pushPermission', $pushPermission);

// 最新消息有設定文章起迄
// 取最新消息版號
dbGetNewsBoard($newsData);
$newBid = $newsData['board_id'];
if ($newBid === $_POST['bid']) {
    $whereNewsData .= " 
        AND concat(`WM_bbs_posts`.`board_id`, `WM_bbs_posts`.`node`) IN
               (SELECT concat(board_id, node)
                FROM WM_news_posts
                WHERE ((NOW() >= open_time
                        AND NOW() < close_time)
                       OR (NOW() >= open_time
                           AND close_time IS NULL)
                       OR (NOW() < close_time
                           AND open_time IS NULL)
                       OR (NOW() >= open_time
                           AND close_time = '0000-00-00 00:00:00')
                       OR (NOW() < close_time
                           AND open_time = '0000-00-00 00:00:00')
                       OR (open_time IS NULL
                           AND close_time IS NULL)
                       OR (open_time = '0000-00-00 00:00:00'
                           AND close_time = '0000-00-00 00:00:00')))";
}

// 取上下討論板的主題
$prevNodeId = dbGetOne('WM_bbs_posts', 'min(node)', sprintf("board_id=%d and length(node)=9 and node > '%s'" . $whereNewsData, $_POST['bid'], $_POST['nid']));
$nextNodeId = dbGetOne('WM_bbs_posts', 'max(node)', sprintf("board_id=%d and length(node)=9 and node < '%s'" . $whereNewsData, $_POST['bid'], $_POST['nid']));


//    043884 (B)
$page = intval($_POST['page']);
if (isset($_POST['page']) && $_POST['page'] == '') {
    $total     = $topic['data'][$bnid]['reply'];
    $rows_page = GetForumPostPerPage();
    $page      = ceil($total / $rows_page);
}
$smarty->assign('page', ($page) ? $page : 0);
//    043884 (E)

// 取老師有任教的課程
$rsTeacherCourses = dbGetCourses(
    'C.course_id, C.caption', 
    $sysSession->username, 
    $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], 
    'course_id` DESC', 
    FALSE, 
    FALSE
);
$firstCourseForums = '';
if ($rsTeacherCourses) {
    while (!$rsTeacherCourses->EOF) {
        if ($tcid = (int) ($rsTeacherCourses->fields['course_id'])) {
            $lang                  = getCaption($rsTeacherCourses->fields['caption']);
            $teacherCourses[$tcid] = empty($lang[$sysSession->lang]) ? '[no title]' : $lang[$sysSession->lang];
        }
        $rsTeacherCourses->MoveNext();
    }
    
    // 取出第一個課程的討論版列表
    if ($teacherCourses) {
        $firstCourseForums = $rsForum->getCourseForumList(current(array_keys($teacherCourses)), array(), true);
    }
}

// 設定前台參數
$bticket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $_POST['nid']);
$ticket  = md5(sysTicketSeed . 'read' . $_COOKIE['idx'] . $_POST['nid']);
$smarty->assign('cid', $cid);
$smarty->assign('bid', $_POST['bid']);
$smarty->assign('teacherCourses', $teacherCourses);
$smarty->assign('firstCourseForums', $firstCourseForums);
$smarty->assign('nowpage', $_POST['nowpage']);

$smarty->assign('prevNodeId', $prevNodeId);
$smarty->assign('nextNodeId', $nextNodeId);

$smarty->assign('nid', $_POST['nid']);
$smarty->assign('bTicket', $bticket);
$smarty->assign('ticket', $ticket);
$smarty->assign('main', $topic['data'][$bnid]);
$smarty->assign('editFlag', $topic['editEnable']);
$smarty->assign('email', $sysSession->email);
$smarty->assign('sysMailsRule', sysMailsRule);
$smarty->assign('forumName', $forumName);
$smarty->assign('bltBid', $bltBid);
$smarty->assign('socialShare', $socialShare);
$smarty->assign('updRight', $updRight);
$smarty->assign('fb_comment', $fb_comment);
// 登入者大頭照參數
$smarty->assign('loginCpic', base64_encode(urlencode($sysSession->username)));
$smarty->assign('msg', $MSG);
$smarty->assign('isGroupForum', $isGroupForum);
$smarty->assign('forum_css', filemtime(sysDocumentRoot . '/public/css/forum.css'));
$smarty->assign('node_chain_js', filemtime(sysDocumentRoot . '/public/js/forum/node_chain.js'));

$url = getQrcodePath($baseUrl . '/forum/m_node_chain.php?cid=' . $cid . '&bid=' . $topic['data'][$bnid]['boardid'] . '&nid=' . $topic['data'][$bnid]['n']);
$smarty->assign('url', $url);

// js時間戳記
$smarty->assign('forumJsFTime', filemtime(sysDocumentRoot . '/public/js/forum/forum.js'));

if ($profile['isPhoneDevice']) {
    $smarty->display('common/tiny_header.tpl');
    if ($schoolNewsBoard) {
        $smarty->display('common/site_header.tpl');
    } else {
        $smarty->display('common/course_header.tpl');
    }
    $smarty->display('phone/forum/node_chain.tpl');
    $smarty->display('common/tiny_footer.tpl');
} else {
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('forum/node_chain.tpl');
    $smarty->display('common/tiny_footer.tpl');
}
<?php
/**
 * 討論版檢視單一主題
 *
 * @version m_node_chain.php, v 1.1 2014/12/9 17:30 cch Exp $
 * @copyright Wisdom Master 5(C)  Copyright(R) SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/lib_acade_news.php'); //取得最新消息
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
require_once(sysDocumentRoot . '/lang/forum.php');
require_once(sysDocumentRoot . '/lang/mooc_forum.php');
require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
// require_once(sysDocumentRoot . '/lang/mooc.php');
// require_once(sysDocumentRoot . '/lib/interface.php');
// require_once(sysDocumentRoot . '/lib/file_api.php');
// require_once(sysDocumentRoot . '/lib/lib_forum.php');
// require_once(sysDocumentRoot . '/lang/forum.php');
// require_once(sysDocumentRoot . '/forum/order.inc.php');

$sysSession->cur_func = '900200100';
$sysSession->restore();

if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

//    // 取得最新消息討論版名稱
//    dbGetNewsBoard($newsresult);
//    $rsForum = new forum ();
//    $news = $rsForum->getForumNameByBid ( $newsresult['board_id'] );
//    $smarty->assign ( 'news_title', $news['title'] );

// 課程編號
$cid = $sysSession->course_id;
if (isset($_REQUEST['cid']) === true && $_REQUEST['cid'] >= '0') {
    $cid = $_REQUEST['cid'];
}

// 透過URL指定討論串編號
$std = "/^([0-9]+)$/";
$bid = $_GET['bid'];
$nid = $_GET['nid'];
if (strlen($bid) === 10 && preg_match($std, $bid) === 1 && (strlen($nid) === 9 || strlen($nid) === 18) && preg_match($std, $nid) === 1) {
    $_POST['bid'] = $bid;
    $_POST['nid'] = $nid;
}

$rsForum    = new forum();
// 取討論版名稱
$forumTopic = $rsForum->getCourseForumList($cid, array(), false, array(
    $_POST['bid']
));
$forumName  = $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['board_name'];
// 取主文
$bnid       = $_POST['bid'] . '|' . substr($_POST['nid'], 0, 9);
$topic      = $rsForum->getCourseForumNews($cid, array(
    $_POST['bid']
), array(
    $bnid
), '0', 1);


//news 連結
$postlink = str_replace('course/00000000/', '', $topic['data'][$bnid]['postfilelink']);

if (isset($topic['data'][$bnid]) === false) {
    die('Access deny: incorrect board id & node id.');
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
    $nodeInfo  = $rsForum->getReply($_GET['bid'], array(
        array(
            $_GET['bid'],
            $_GET['nid']
        )
    ), array(), '', '', '', 'pt', 'desc', true);
    $node      = $nodeInfo[$_GET['bid'] . '|' . $_GET['nid']]['data'][$_GET['bid'] . '|' . $_GET['nid']];
    $metaDesc  = $node['poster'] . ' ( ' . trim($node['realname']) . ' ) ' . $MSG['response'][$sysSession->lang] . ': ' . $node['postcontenttext'];
    $metaImage = $baseUrl . '/co_showuserpic.php?a=' . $node['cpic'];
}
$smarty->assign('metaTitle', $topic['data'][$bnid]['subject']);
$smarty->assign('metaDescription', $metaDesc);
$smarty->assign('metaSitename', $schoolInfo['banner_title1']);
$smarty->assign('metaUrl', $baseUrl);
$smarty->assign('metaImage', $metaImage);

//// 是否顯示張貼按鈕
//// 是版主一定可以PO
//$managerFlag = '0';
//$postFlag    = '0';
//if ($sysSession->username === $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['manager']) {
//    $postFlag    = '1';
//    $managerFlag = '1';
//} else {
//    $poster = explode(",", $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['poster']);
//    if (count($poster) >= 1) {
//        $roles = '';
//        foreach ($poster as $v) {
//            $roles = $roles | $sysRoles[$v];
//        }
//    }
//    $postFlag = aclCheckRole($sysSession->username, $roles, $cid);
//    
//    // 登入者
//    if ((in_array('login_persons', $poster) && $sysSession->username >= '0' && $sysSession->username !== 'guest')) {
//        $postFlag = true;
//    }
//    
//    if ($postFlag === true || $postFlag === '1') {
//        
//        // 判斷張貼時間
//        $open_time  = $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['open_time'];
//        $close_time = $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['close_time'];
//        if ((date('Y-m-d H:i:s') >= $open_time || $open_time === '0000-00-00 00:00:00') && (date('Y-m-d H:i:s') < $close_time || $close_time === '0000-00-00 00:00:00')) {
//            $postFlag = '1';
//        } else {
//            $postFlag = '0';
//        }
//    } else {
//        $postFlag = '0';
//    }
//}
//
//$smarty->assign('postFlag', $postFlag);
//$smarty->assign('managerFlag', $managerFlag);

// 設定前台參數
//$bticket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $_POST['nid']);
//$ticket  = md5(sysTicketSeed . 'read' . $_COOKIE['idx'] . $_POST['nid']);
//$smarty->assign('bTicket', $bticket);
//$smarty->assign('ticket', $ticket);
$smarty->assign('main', $topic['data'][$bnid]);
$smarty->assign('postlink', $postlink);
//$smarty->assign('editFlag', $topic['editEnable']);
$smarty->assign('email', $sysSession->email);
//$smarty->assign('sysMailsRule', sysMailsRule);
//$smarty->assign('forumName', $forumName);
$smarty->assign('news_title', $forumName);
$smarty->assign('bltBid', $bltBid);
// $smarty->assign('socialShare', $socialShare);
//$smarty->assign('updRight', $updRight);
// 登入者大頭照參數
$smarty->assign('loginCpic', base64_encode(urlencode($sysSession->username)));
$smarty->assign('msg', $MSG);

$smarty->display('common/tiny_header.tpl');
// $smarty->display('forum/node_chain.tpl');
$smarty->display('m_forum_one_new_annt.tpl');
$smarty->display('common/tiny_footer.tpl');
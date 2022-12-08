<?php

/**************************************************************************************************
 *                                                                                                 *
 *       Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
 *                                                                                                 *
 *       Programmer: Sean
 *       SA        :                                                                          *
 *       Creation  : 2015/03/17                                                                  *
 *       work for  : 最新消息公告
 *       work on   : Apache 2.2.21, MySQL 5.1.59 , PHP 5.3.28                                       *
 *                                                                                                 *
 **************************************************************************************************/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');

require_once(sysDocumentRoot . '/lang/forum.php');
require_once(sysDocumentRoot . '/lang/mooc_forum.php');

require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/mooc/models/school.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
require_once(sysDocumentRoot . '/lib/lib_layout.php'); // 首頁輸出
require_once(sysDocumentRoot . '/lib/lib_acade_news.php'); //取得最新消息


//從index forum POST過來的值 
$bid = intval($_POST['bid']);
$nid = $_POST['nid'];
//$smarty->assign('bid', $bid);
//$smarty->assign('nid', $nid);

// 課程編號
$cid = $sysSession->course_id;
if (isset($_REQUEST['cid']) === true && intval($_REQUEST['cid']) >= 0) {
    $cid = $_REQUEST['cid'];
}

// 透過URL指定討論串編號
//$std = "/^([0-9]+)$/";
//$bid = $_GET['bid'];
//$nid = $_GET['nid'];
//    if (strlen($bid) === 10 && preg_match($std, $bid) === 1 &&
//        (strlen($nid) === 9 || strlen($nid) === 18) && preg_match($std, $nid) === 1) {
//        $_POST['bid'] = $bid;
//        $_POST['nid'] = $nid;
//    }

// 是否具刊登權限(含張貼, 修改, 刪除)
//$updRight = ChkRight($_POST['bid']);

// 取得最新消息討論版名稱
dbGetNewsBoard($newsresult);
$rsForum = new forum();
$news    = $rsForum->getForumNameByBid($newsresult['board_id']);
//    $smarty->assign('news_title', $news['bname']);
//$smarty->assign('news', $news);
$smarty->assign('msg', $MSG);
//$smarty->assign('cid', $sysSession->school_id);


$anntMsg = array(
    'cid' => htmlspecialchars($cid, ENT_QUOTES),
    'bid' => htmlspecialchars($bid, ENT_QUOTES),
    // 'title'         => $sysSession->board_name,
    'nid' => htmlspecialchars($nid, ENT_QUOTES),
    'title' => htmlspecialchars($news['bname'], ENT_QUOTES),
//    'subscribe' => $MSG['subscribe'][$sysSession->lang],
//    'unsubscribe' => $MSG['unsubscribe'][$sysSession->lang],
//    'post' => $MSG['post'][$sysSession->lang],
//    'manage' => $MSG['forum_manage'][$sysSession->lang],
    'attach' => htmlspecialchars($MSG['forum_attachment'][$sysSession->lang], ENT_QUOTES),
//    'successful' => $MSG['successful'][$sysSession->lang],
//    'failed' => $MSG['failed'][$sysSession->lang]
);

$smarty->assign('anntMsg', $anntMsg);

if ($profile['isPhoneDevice']) {
    $smarty->display('phone/forum/m_forum_news_annt.tpl');
}else{
    $smarty->display('m_forum_news_annt.tpl');
}

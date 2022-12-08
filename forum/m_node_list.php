<?php
/**************************************************************************************************
 *
 *        Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 *
 *        Programmer: cch
 *       SA        : saly
 *        Creation  : 2014/12/1
 *        work for  : 單一討論區
 *        work on   : Apache 1.3.41, MySQL 5.1.59 , PHP 4.4.9
 *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
session_name('foo');
session_start();
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
require_once(sysDocumentRoot . '/lang/forum.php');
require_once(sysDocumentRoot . '/lang/mooc.php');
require_once(sysDocumentRoot . '/lang/mooc_forum.php');
require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');

$sysSession->cur_func = '900200100';
$sysSession->restore();
if (!aclVerifyPermission(900200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

// add by jeff : 2006-04-27, for agent 直接切到討論板
if ((isset($_COOKIE['go_board'])) && (!empty($_COOKIE['go_board']))) {
    setcookie('go_board', '', time() - 3600, '/');
    $gb = sysEncode($_COOKIE['go_board']);
    echo <<< BOF
<html><head><script language="javascript">
    if ((typeof(parent.s_sysbar) == "object") && (typeof(parent.s_sysbar.goBoard) == "function")) {
        parent.s_sysbar.goBoard("{$gb}");
    }
</script></head></html>
BOF;
    exit;
}
// 課程編號
$cid = $sysSession->course_id;
if (isset($_POST['cid']) === true && $_POST['cid'] >= '1') {
    $cid = $_POST['cid'];
}

// 設定kcfinder圖片上傳路徑
$username                          = trim($sysSession->username);
$one                               = substr($username, 0, 1);
$two                               = substr($username, 1, 1);
$_SESSION['KCFINDER']['uploadURL'] = '../../user/' . $one . '/' . $two . '/' . $username . '/';

$rsForum = new forum();

// 取課程公告版
$bulletinId = $rsForum->getCourseAnnId($cid, 1);

$deBid = trim(sysNewDecode($_GET['xbid'], $_COOKIE['idx'], true));
if (isset($_POST['bid']) && intval($_POST['bid']) > 1000000000) {
    $bid = $_POST['bid'];
}else if (isset($_POST['xbid'])&&(strlen($deBid = sysDecode($_POST['xbid'])) == 10)) {
    $bid = $deBid;
} else if (isset($_GET['xbid']) && (strlen($deBid = trim(sysDecode(rawurldecode($_GET['xbid'])))) == 10)) {
    $bid = $deBid;
} else if (isset($_GET['xbid']) && (strlen($deBid = trim(sysNewDecode(rawurldecode($_GET['xbid']), $_COOKIE['idx'], true))) == 10)) {
    $bid = $deBid;
} else {
    if ($sysSession->board_ownerid == 10001 && $bulletinId == '') {
        $bid = $sysSession->board_id;
    } else {
        $bid = $bulletinId;
    }
}

if (isset($_POST['selectPage']) && $_POST['selectPage'] != 0) {
    $smarty->assign('nowpage', $_POST['selectPage']);
}

if (strlen($cid) == 8) {
    // 目前是否為公告版
    if ($bulletinId == $bid && strlen($bid) == 10) {
        $bulletinFlag = '1';
    } else {
        $bulletinFlag = '0';
    }
} else {
    $bulletinFlag = '1';
    if (strlen($cid) == 5) {
        $schoolNewsBoard = true;
    }
}

// 設定討論版擁有者編號
$rs                        = dbGetStSr('WM_bbs_boards', 'owner_id, switch', "board_id={$bid}", ADODB_FETCH_ASSOC);
$sysSession->board_ownerid = $rs['owner_id'];
$sysSession->restore();
if (($sysSession->board_id == $bid) && ($sysSession->board_ownerid == $sysSession->school_id)) {
    $bulletinFlag    = '1';
    $schoolNewsBoard = true;
}

// 是否為討論室紀錄
dbGetRecordBoard($cid, $rsRecordBoard);
$isRecordBoard = ((int) $bid === (int) $rsRecordBoard['board_id']) ? '1' : '0'; // 都轉數字比較

// 頁面標題是否是麵包屑結構
$isBreadCrumb = ($bulletinFlag === '1' || $isRecordBoard === '1') ? '0' : '1';
if($sysSession->env == 'teach') $isBreadCrumb = 0; // custom
$smarty->assign('isBreadCrumb', $isBreadCrumb);

// 取指定討論版名稱
$forumTopic = $rsForum->getCourseForumList($cid, array(), false, array(
    $bid
));

//若討論板的owner_id的長度為16，此討論板為分組討論
if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['owner_id']) == 16) {
    $isGroupForum = true;
} else {
    $isGroupForum = false;
}

// 討論版名稱
$forumName = $rsForum->getForumNameByBid($bid);
$forumName = $forumName['bname'];

// 驗證是否有讀取權限
if (!ChkBoardReadRight($bid)) {
    wmSysLog($sysSession->cur_func, $sysSession->course_id, $bid, 2, 'auto', $_SERVER['PHP_SELF'], 'board_deny');
    $smarty->assign('forumName', $forumName);
    $smarty->assign('errorCode', 'board_deny');
    $smarty->assign('errorMsg', $MSG['cant_read'][$sysSession->lang]);
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('forum/error.tpl');
    $smarty->display('common/tiny_footer.tpl');
    exit;
}

// 取指定討論的最新文章
$forumNews     = $rsForum->getCourseForumNews($cid, array(
    $bid
));
$forumNewsData = $forumNews['data'];

// 教師權限功能開關
$manageEnable = (ChkRight($bid) == 1) ? true : false;
$smarty->assign('manageEnable', $manageEnable);

// 是否已訂閱
list($myorder) = dbGetStSr('WM_bbs_order', 'count(*)', "board_id={$bid} and username = '{$sysSession->username}'", ADODB_FETCH_NUM);
$smarty->assign('mySubscribe', ($myorder == 0) ? $MSG['subscribe'][$sysSession->lang] : $MSG['unsubscribe'][$sysSession->lang]);

// 訂閱功能顯示與否 ， 如果有開自動轉寄功能 ，則不顯示 ，WM_bbs_boards
// guest不應有訂閱功能
$swiEmail        = $rs['switch'];
$subscribeEnable = (strpos($swiEmail, 'mailfollow') === false) ? (($sysSession->username === 'guest') ? false : true) : false;
$smarty->assign('subscribeEnable', $subscribeEnable);

// 是否顯示 more 按鈕，目前根據管理及訂閱作判斷，日後如有新增功能需再修改
$smarty->assign('moreDisplay', $subscribeEnable || $manageEnable);

// 是否顯示張貼按鈕
// 是版主一定可以PO
$managerFlag = '0';
$postFlag    = '0';

// 板主
if ($sysSession->username === $forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['manager']) {
    $postFlag    = '1';
    $managerFlag = '1';
    // 學校等級的討論板且登入者是該校一般管理者、超級管理者、最高管理者
} else if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['owner_id']) === 5) {
    $schoolNewsBoard = true;
    if ((int) aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id) >= 1) {
        $postFlag    = '1';
        $managerFlag = '1';
    } else {
        $boardType = dbGetOne('WM_news_subject', '`type`', sprintf('board_id=%d', $bid));
        if (in_array($boardType, array(
            'suggest',
            'comment'
        ))) {
            $postFlag = '1';
        }
    }
} else {
    // 只有課程公告限制身份，其他身份可讀取就能張貼，接下來驗證時間
    if ($bulletinFlag == '1') {
        $postFlag = aclCheckRole($sysSession->username, $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'], $cid);
    } else if ($sysSession->username === 'guest') {
        $postFlag = false;
    } else {
        $postFlag = true;
    }
    
    if ($postFlag === true || $postFlag === '1') {
        // 判斷張貼時間
        $open_time  = $forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['open_time'];
        $close_time = $forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['close_time'];
        if ((date('Y-m-d H:i:s') >= $open_time || $open_time === '0000-00-00 00:00') && (date('Y-m-d H:i:s') < $close_time || $close_time === '0000-00-00 00:00')) {
            $postFlag = '1';
        } else {
            $postFlag = '0';
        }
    } else {
        $postFlag = '0';
    }
}

if ($schoolNewsBoard){
    $sysSession->news_board      = 1; // 含時間(開啟及關閉)欄位類型之討論版
    $sysSession->restore();
}

$smarty->assign('postFlag', $postFlag);
$smarty->assign('managerFlag', $managerFlag);

$js = <<< BOF
    function goBoard(val) {
        // 學生
        if ((typeof(parent.s_sysbar) == "object") && (typeof(parent.s_sysbar.goBoard) == "function")) {
            parent.s_sysbar.goBoard(val);
        }
    }

BOF;

$ticket = md5(sysTicketSeed . 'board' . $_COOKIE['idx'] . $bid);

$smarty->assign('ticket', $ticket);
$smarty->assign('cid', $cid);
$smarty->assign('bid', $bid);
$smarty->assign('forumName', $forumName);
$smarty->assign('forumNote', $forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['title']);
$smarty->assign('forumNewsData', $forumNewsData);
//    $smarty->assign('forumListData', $forumListData);
$smarty->assign('msg', $MSG);
$smarty->assign('isGroupForum', $isGroupForum);
    
// js時間戳記
$smarty->assign('forumJsFTime', filemtime(sysDocumentRoot . '/public/js/forum/forum.js'));
$smarty->assign('nodelistJsFTime', filemtime(sysDocumentRoot . '/public/js/forum/node_list.js'));

if ($profile['isPhoneDevice']) {
    $smarty->display('common/tiny_header.tpl');
    if ($schoolNewsBoard){
        $smarty->display('common/site_header.tpl');
    }else{
        $smarty->display('common/course_header.tpl');
    }
    $smarty->display('phone/forum/node_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}else{
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('forum/node_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}
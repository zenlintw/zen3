<?php
   /**************************************************************************************************
    *
    *        Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
    *
    *        Programmer: cch
    *           SA        : saly
    *        Creation  : 2014/11/18
    *        work for  : 討論區列表
    *        work on   : Apache 1.3.41, MySQL 5.1.59 , PHP 4.4.9
    *
    **************************************************************************************************/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot.'/lib/lib_forum.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/mooc/models/forum.php');
    require_once(sysDocumentRoot . '/lang/forum.php');
    require_once(sysDocumentRoot . '/lang/mooc.php');
    require_once(sysDocumentRoot . '/lang/mooc_forum.php');

    $sysSession->cur_func = '900200100';
    $sysSession->restore();
    if (!aclVerifyPermission(900200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
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

    $cid = $sysSession->course_id;
    
    // 討論版麵包屑
    if (isset($_POST['cid']) === true && strlen($_POST['cid']) >= 1) {
        $cid = (int) $_POST['cid'];
    }
    // 教室選單
    if (isset($_GET['cid']) === true && strlen($_GET['cid']) >= 1) {
        $cid = (int) $_GET['cid'];
    }
    // 驗證編碼長度
    if (!(in_array(strlen($cid), array(5, 7, 8, 16)))) {
        die('Error Course id: ' . $cid);
    }



//    // 取討論版列表資訊，排除課程公告版
//    $rsForum = new forum();
//    // 取課程公告版編號
//    $courseAnnId = $rsForum->getCourseAnnId($cid);
//    $forumList = getCourseBoard($cid, array($courseAnnId));
    // 因應個人區的未讀文章，呈現課程公告於列表中
    $forumList = getCourseBoard($cid);  // 由於WEB、APP共用撈取的程式，所以抽到/lib/lib_forum.php了 by Small 2018/01/22

    $js = <<< BOF
    function goBoard(val) {
        // 學生
        if ((typeof(parent.s_sysbar) == "object") && (typeof(parent.s_sysbar.goBoard) == "function")) {
            parent.s_sysbar.goBoard(val);
        }
    }

BOF;

    $ticket = md5(sysTicketSeed . 'board' . $_COOKIE['idx'] . $sysSession->board_id);

    $smarty->assign('ticket', $ticket);
    $smarty->assign('forumList', $forumList);
    $smarty->assign('cid', $cid);
    $smarty->assign('msg', $MSG);
    if ($profile['isPhoneDevice']) {
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/course_header.tpl');
        $smarty->display('phone/forum/board_list.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }else{
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('forum/board_list.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }
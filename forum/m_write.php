<?php
   /**************************************************************************************************
    *
    *		Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
    *
    *		Programmer: cch
    *       SA        : saly
    *		Creation  : 2014/10/22
    *		work for  : 張貼
    *		work on   : Apache 1.3.41, MySQL 5.1.59 , PHP 4.4.9
    *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/mooc/models/forum.php');
    require_once(sysDocumentRoot . '/lang/forum.php');
    require_once(sysDocumentRoot . '/lang/mooc.php');
    require_once(sysDocumentRoot . '/lang/mooc_forum.php');
    require_once(sysDocumentRoot . '/lib/editor.php');

    $sysSession->cur_func = '900200500';
    $sysSession->restore();
    if (!aclVerifyPermission(900200500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }
    
//    echo '<pre>';
//    var_dump('$_POST');
//    var_dump($_POST);
//    echo '</pre>';
//    
//    echo '<pre>';
//    var_dump('$_GET');
//    var_dump($_GET);
//    echo '</pre>';

    // 課程編號
    $cid = $sysSession->course_id;
    if (isset($_POST['cid']) === true && $_POST['cid'] >= '0') {
        $cid = $_POST['cid'];
    }

    if ($cid === null) {
        die('course id error!!');
    }
    
    // 如果是公告管理端傳來
    if (isset($_POST['env']) === true) {
        $env = ($_POST['env'] === $sysSession->env) ? $sysSession->env : 'learn';
    }
    
//    $_POST['from'] = 'review';
    // 如果是從筆記本來，表示資料要寫到課程討論版
    if (isset($_POST['from']) === true && $_POST['from'] === 'review' && strlen($cid) === 8) {
        // 取該課程的課程討論版BID
        $rsDiscuss = dbGetStMr('WM_term_course', '`discuss`', "course_id = '{$cid}'", ADODB_FETCH_ASSOC);
        if ($rsDiscuss && $rsDiscuss->RecordCount() >= 1) {
            while (!$rsDiscuss->EOF) {
                $discussBid = $rsDiscuss->fields['discuss'];
                $rsDiscuss->MoveNext();
            }
        }
        $_POST['bid'] = $discussBid;
        
//        $_POST['title'] = '56545646';
//        $_POST['imgsrc'] = 'http://wm5-trunk-spring.sun.net.tw/user/s/p/spring/note/100/6423_1.柱體錐體__snapshot_41.jpg';
        
        $subject = '';
        $postFrom = '';
        $noteId = '';
        if (isset($_POST['note_id'])) {
            $noteId = $_POST['note_id'];
        }
        if (isset($_POST['from'])) {
            $postFrom = $_POST['from'];
        }
        if (isset($_POST['title'])) {
            $subject = $_POST['title'];
        }
        if (isset($_POST['imgsrc'])) {
            $content = '<img src="' . $_POST['imgsrc'] . '">';
        }
    }

    define('EDIT_MODE', 'write');
    $bid = $_POST['bid'];
    $ticket = md5(sysTicketSeed . 'board' . $_COOKIE['idx'] . $bid);

    // 取簽名檔
    $RS1 = dbGetStMr('WM_user_tagline', '`serial`, `title`, `ctype`', "username = '{$sysSession->username}' ORDER BY `serial` LIMIT 0 , 1", ADODB_FETCH_ASSOC);
    $tagline = array();
    if ($RS1 && $RS1->RecordCount() >= 1) {
        while (!$RS1->EOF) {
            $tagline[$RS1->fields['serial']] = $MSG['use_tagline'][$sysSession->lang]; // 不使用簽名檔名稱 $RS1->fields['title'];

            $RS1->MoveNext();
        }
		$tagline[-1] = $MSG['not_use_tagline'][$sysSession->lang];
    }

    $rsForum = new forum();

    if (strlen($cid) == 8) {
        // 取課程公告版
        $bulletinId = $rsForum->getCourseAnnId($cid, 1);
        // 目前是否為公告版
        if ($bulletinId == $bid && strlen($bid) == 10) {
            $bulletinFlag = '1';
        } else {
            $bulletinFlag = '0';
        }
    }else{
        $bulletinFlag = '1';
        if (strlen($cid) == 5) {
        }
    }

    if (($sysSession->board_id == $bid)&&($sysSession->board_ownerid == $sysSession->school_id)) {
        $bulletinFlag = '1';
    }

    // 是否為討論室紀錄
    dbGetRecordBoard($cid, $rsRecordBoard);
    $isRecordBoard = ((int)$bid === (int)$rsRecordBoard['board_id']) ? '1' : '0';// 都轉數字比較

    // 頁面標題是否是麵包屑結構
    $isBreadCrumb = ($bulletinFlag === '1' || $isRecordBoard === '1') ? '0' : '1';
    $smarty->assign('isBreadCrumb', $isBreadCrumb);

    $forumTopic = $rsForum->getCourseForumList($cid, array(), false, array($bid));
    $forumName = $forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['board_name'];
    //若討論板的owner_id的長度為16，此討論板為分組討論
    if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['owner_id']) == 16) {
        $isGroupForum = true;
    } else {
        $isGroupForum = false;
    }
    if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['owner_id']) == 5) {
        $schoolNewsBoard = true;
    }

    $smarty->assign('forumName', $forumName);
    $smarty->assign('type', 'board');
    $smarty->assign('actionName', $MSG['post'][$sysSession->lang]);
    $smarty->assign('ticket', $ticket);
    $smarty->assign('tagline', $tagline);
    $smarty->assign('tmpDir', md5($_COOKIE['idx']));
    $smarty->assign('cid', $cid);
    $smarty->assign('bid', $bid);
    $enbid = rawurlencode(sysNewEncode($bid, $ticket, false));
    $smarty->assign('enbid', $enbid);
        
    $smarty->assign('env', $env);
    $smarty->assign('subject', $subject);
    $smarty->assign('postFrom', $postFrom);
    $smarty->assign('noteId', $noteId);
    $smarty->assign('isGroupForum', $isGroupForum);
    
    // 取課程公告版編號，決定清單路徑是否出現
    $courseAnnId = $rsForum->getCourseAnnId($cid);
    if ($bid === $courseAnnId) {
        $pathFlag = '0';
    } else {
        $pathFlag = '1';
    }
    $smarty->assign('pathFlag', $pathFlag);

    // FCK Editor
    $oEditor = new wmEditor;
    $oEditor->setEditor('rtnFckeditor');
    $oEditor->setValue(($content) ? $content : '&nbsp;');
    $oEditor->addContType('isHTML', 1);
    $oEditor->addUploadFun();
    $htmleditorName = 'content';// 給予要變成編輯器的元素名稱
    $bw = $oEditor->chkBrowser();
    $headers = apache_request_headers();
    $isIE11 = preg_match('/Trident\/(\d+)/', $headers['User-Agent'], $regs) && intval($regs[1])> 6;

    // 給js lib用
    $smarty->assign('htmleditorname', $htmleditorName); // 編輯器名稱
    $smarty->assign('bw0', $bw[0]); // 判斷 MSIE
    $smarty->assign('setUploadFun', $oEditor->setUploadFun);
    $smarty->assign('isIE11', $isIE11);// 判斷 IE11
    if ($profile['isPhoneDevice']) {
        $smarty->assign('ckeditorToolbar', 'PHONE');
    }
    $content = $oEditor->generate($htmleditorName, 500, 350);
    $smarty->assign('content', $content);
    
    $smarty->assign('msg', $MSG);

    if ($profile['isPhoneDevice']) {
        $smarty->display('common/tiny_header.tpl');
        if ($schoolNewsBoard){
            $smarty->display('common/site_header.tpl');
        }else{
            $smarty->display('common/course_header.tpl');
        }
        $smarty->display('phone/forum/write.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }else{
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('forum/write.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }
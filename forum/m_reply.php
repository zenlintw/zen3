<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/mooc/models/forum.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/file_api.php');
    require_once(sysDocumentRoot . '/lib/editor.php');
    require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
    require_once(sysDocumentRoot . '/lang/forum.php');
    require_once(sysDocumentRoot . '/lang/mooc.php');
    require_once(sysDocumentRoot . '/lang/mooc_forum.php');

    $sysSession->cur_func = '900200500';
    $sysSession->restore();
    if (!aclVerifyPermission(900200500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

    $bid = $_POST['bid'];

//    if ($_GET['bTicket'] != md5(sysTicketSeed . $sysSession->username . 'read' . $sysSession->ticket . $bid)) {
//        echo <<< EOB
//<script>
//	alert('Incorrect board id.');
//	location.replace('/forum/q_index.php');
//</script>
//EOB;
//        exit;
//    }
    $title     = $MSG['modify'][$sysSession->lang];
    $tabs_name = $MSG['reply'][$sysSession->lang];
    $st_id     = $sysSession->cur_func . $bid . '1';
    $referurl  = 'index.php';

    if (preg_match('/^\d{9,}$/', $_POST['node'])) {
        $_POST['page_no']       = abs($_POST['page_no']);
        $_POST['post_per_page'] = abs($_POST['post_per_page']);
        $_POST['curr_page']     = abs($_POST['curr_page']);
        $_POST['item_per_page'] = abs($_POST['item_per_page']);
        $action = "m_writing.php?threadSequence=1&page_no={$_POST['page_no']}&post_per_page={$_POST['post_per_page']}&curr_page={$_POST['curr_page']}&item_per_page={$_POST['item_per_page']}";
    } else {
        $action = 'm_writing.php';
    }

    $ticket = md5(sysTicketSeed . 'board' . $_COOKIE['idx'] . $bid);
    $bn_extra = '';
    $subject = htmlspecialchars(trim(stripslashes($_POST['subject'])), ENT_QUOTES);

    // 去掉最後面沒有用的 <p> 及 <br>
//    $ct = stripslashes(trim($_POST['content']));
//    do {
//        $content = $ct;
//        $ct      = preg_replace('!\s*(<(\w)+\b[^>]*>(\s|&nbsp;)*</\2>|<br\b[^>]*>)$!isU', '', $content);
//    } while ($ct != $content);

//    if (preg_match('/<blockquote\b[^>]*\bborder-left: gray 2px dotted\b[^>]*\bbackground-color: #([0-9A-Fa-f]{6})\b[^>]*>/iU', $content, $regs)) {
//        $bgc = (strtolower($regs[1]) == 'eeeeee') ? 'CFCFB6' : 'EEEEEE';
//    }
//    else
//        $bgc = 'EEEEEE';

//    $content = '<p>&nbsp;</p><blockquote style="margin-left: 1em; margin-right: 0; margin-bottom: 0; padding: 5px; border-left: 2px gray dotted; background-color: #' . $bgc . '">' .
//        $content .
//        '</blockquote>';

    define('EDIT_MODE', 'reply');
//    require_once(sysDocumentRoot . '/forum/lib_edit.php');
    $_POST['node'] = preg_replace('/[^\d]+/', '', $_POST['node']);

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

    // 課程編號
    $cid = $sysSession->course_id;
    if (isset($_POST['cid']) === true && $_POST['cid'] >= '0') {
        $cid = $_POST['cid'];
    }

    $rsForum = new forum();
    $forumTopic = $rsForum->getCourseForumList($cid, array(), false, array($_POST['bid']));
    $forumName = $forumTopic[sprintf("'%d'", mysql_real_escape_string($_POST['bid']))]['board_name'];
    //若討論板的owner_id的長度為16，此討論板為分組討論
    if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['owner_id']) == 16) {
        $isGroupForum = true;
    } else {
        $isGroupForum = false;
    }
    if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['owner_id']) == 5) {
        $schoolNewsBoard = true;
    }
    
    // 取主文
    $bnid = $bid . '|' . substr($_POST['mnode'], 0, 9);
    $topic = $rsForum->getCourseForumNews($cid, array($bid), array($bnid), '0', 1);

    $smarty->assign('forumName', $forumName);
    $smarty->assign('type', 'board');
    $smarty->assign('actionName', $MSG['reply'][$sysSession->lang]);
    $smarty->assign('subject', $subject);
    $smarty->assign('main', $topic['data'][$bnid]);

    $smarty->assign('tagline', $tagline);
    $smarty->assign('isreply', 1);
    $smarty->assign('tmpDir', md5(uniqid(rand())));
    $smarty->assign('cid', $_POST['cid']);
    $smarty->assign('bid', $_POST['bid']);
    $enbid = rawurlencode(sysNewEncode($bid, $ticket, false));
    $smarty->assign('enbid', $enbid);
    $smarty->assign('nid', $_POST['mnode']);
    $smarty->assign('node', $_POST['mnode']);
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
    
    $content = $oEditor->generate($htmleditorName, 500, 350);
    $smarty->assign('content', $content);
    
    $smarty->assign('msg', $MSG);

    $smarty->assign('ticket', $ticket);

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
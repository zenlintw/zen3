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

    $sysSession->cur_func = '900200600';
    $sysSession->restore();
    if (!aclVerifyPermission(900200600, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

//    if ($_GET['bTicket'] != md5(sysTicketSeed . $sysSession->username . 'read' . $sysSession->ticket . $sysSession->board_id)) {
//        echo <<< EOB
//<script>
//	alert('Incorrect board id.');
//	location.replace('/forum/');
//</script>
//EOB;
//        exit;
//    }

    // 課程編號
    $cid = $sysSession->course_id;
    if (isset($_POST['cid']) === true && $_POST['cid'] >= '0' && (intval($_POST['cid'])>10000000)) {
        $cid = intval($_POST['cid']);
    }
    if (!empty($_POST['bid']) && !is_numeric($_POST['bid'])) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
    if ($sysSession->board_ownerid==$sysSession->school_id) {
        $cid = 0;
    }
    $bid = $_POST['bid'];
    $title     = $MSG['post'][$sysSession->lang];
    $tabs_name = $MSG['title_modify'][$sysSession->lang];
    $st_id     = $sysSession->cur_func . $bid;
    $referurl  = '/forum/m_node_list.php';
    $action    = 'm_writing.php';
    $ticket    = md5(sysTicketSeed . 'board' . $_COOKIE['idx'] . $bid);
    $bn_extra  = '';

//    $subject   = trim(stripslashes($_POST['subject']));
//    $content   = trim(stripslashes($_POST['content']));

    define('EDIT_MODE', 'edit');
//    require_once(sysDocumentRoot . '/forum/lib_edit.php');
    $nid = preg_replace('/[^\d]+/', '', $_POST['mnode']);

    if (in_array(EDIT_MODE, array('edit', 'q_write', 'q_edit'))) {
        $poster = '';
        if (EDIT_MODE == 'edit') {
            // 取得本 POST 內容
            $RS = dbGetStSr('WM_bbs_posts',
                'board_id, node, site, pt, poster, realname, email, homepage, subject, content, attach, rcount, rank, hit, lang',
                "board_id = {$bid} and node = {$nid}",
                ADODB_FETCH_ASSOC
            );
            if (!$RS) {
                die('Accsess deny.');
//                header("Location:index.php");
//                exit();
            }
            $poster = $RS['poster'];
        }

        if (!ChkRight($bid) && ($poster != $sysSession->username || $poster == 'guest')) {
            header('Location:' . $referurl);
            wmSysLog($sysSession->cur_func, $sysSession->class_id , $bid , 1, 'auto', $_SERVER['PHP_SELF'], '不具刊登權限!');
            exit();
        }

        // 是否具刊登權限(含張貼, 修改, 刪除)
        $updt_right = $sysSession->q_right;
        if ($sysSession->board_readonly) {
            $post_right = $sysSession->q_right;
        } else {
            $post_right = true;
            $updt_right = $updt_right || ($RS['poster'] === $sysSession->username);
        }
 
        // 取檔案列表
        if ($sysSession->course_id === "0" || $sysSession->course_id === null || $rs->fields['owner_id'] == 10001) {
            // 公告的最新消息會沒有cid
            $filesInTable = getFileData($RS['attach'], sprintf (sysDocumentRoot . '/base/%05d/%s/%10d/%s/', $sysSession->school_id, 'board', $bid, $nid));
        } else {
            $filesInTable = getFileData($RS['attach'], sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/%10d/%s/', $sysSession->school_id, $cid, 'board', $bid, $nid));
        }
    }

    $rsForum = new forum();
    $forumTopic = $rsForum->getCourseForumList($cid, array(), false, array($bid));
    $forumName = $forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['board_name'];
    
    //若討論板的owner_id的長度為16，此討論板為分組討論
    if (strlen($forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['owner_id']) == 16) {
        $isGroupForum = true;
    } else {
        $isGroupForum = false;
    }
	
// 043884 (B)
    $smarty->assign('page', ($_GET['page'])?intval($_GET['page']):'0');
	
// 043884 (E)
	
    $smarty->assign('forumName', $forumName);
    $smarty->assign('type', 'board');
    $smarty->assign('actionName', $MSG['edit'][$sysSession->lang]);
    $smarty->assign('tmpDir', md5(uniqid(rand())));
    $smarty->assign('cid', $cid);
    $smarty->assign('bid', $bid);
    $enbid = rawurlencode(sysNewEncode($bid, $ticket, false));
    $smarty->assign('enbid', $enbid);
    $smarty->assign('nid', substr($nid, 0, 9));
    $smarty->assign('subject', $RS['subject']);
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
    $oEditor->setValue(($RS['content']) ? htmlspecialchars($RS['content']) : '&nbsp;');
    $oEditor->addContType('isHTML', 1);
    $oEditor->addUploadFun();
    $htmleditorName = 'content';// 給予要變成編輯器的元素名稱
    if ($profile['isPhoneDevice']) {
        $smarty->assign('ckeditorToolbar', 'PHONE');
    }
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

    $smarty->assign('etime', $RS['pt']);
    $smarty->assign('mnode', $nid);

    $smarty->assign('filesInTable', $filesInTable);

    $smarty->assign('ticket', $ticket);
    
    // 是否為回覆
    if (strlen($nid) === 18) {
        $smarty->assign('isreply', 1);
    }

    if ($profile['isPhoneDevice']) {
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/course_header.tpl');
        $smarty->display('phone/forum/write.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }else{
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('forum/write.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }
<?php
        require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
        require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
        require_once(sysDocumentRoot . '/lib/lib_forum.php');
        require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
        require_once(sysDocumentRoot . '/lib/acl_api.php');

        $sysSession->cur_func = '2500700100';
        $sysSession->restore();
        if (!aclVerifyPermission(2500700100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
        }
        
        if (!dbGetNewsBoard($result, 'faq')) {
            wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 1, 'manager', $_SERVER['PHP_SELF'], 'System Error!');
            die('System Error!');
        } elseif (!$result['ok']) {
            wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 1, 'manager', $_SERVER['PHP_SELF'], 'Time has not yet started or time is up.');
            require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
            require_once(sysDocumentRoot . '/lang/mooc_forum.php');
            list($forumName) = dbGetStSr('WM_bbs_boards', 'bname', 'board_id = ' . $result['board_id'], ADODB_FETCH_NUM);
            $forumName = unserialize($forumName);
            $smarty->assign('forumName', $forumName[$sysSession->lang]);
            $smarty->assign('errorCode', 'board_deny');
            $smarty->assign('errorMsg', $MSG['cant_read'][$sysSession->lang]);
            $smarty->display('common/tiny_header.tpl');
            $smarty->display('forum/error.tpl');
            $smarty->display('common/tiny_footer.tpl');
            exit;
        }
        
        // 未登入時，語系部分，判斷自網址參數
        if ($sysSession->username === 'guest' && $_GET['lang'] !== '' && isset($_GET['lang'])) {
            $sysSession->lang = $_GET['lang'];
        }
        
//        $sysSession->course_id       = 0;
        $sysSession->board_id        = $result['board_id'];
        $sysSession->news_board      = 0; // 含時間(開啟及關閉)欄位類型之討論版
        $sysSession->board_readonly  = 1;
        $sysSession->board_qonly     = 1;
        $sysSession->page_no         = '';
        $sysSession->post_no         = '';
        $sysSession->q_page_no       = 1;
        $sysSession->q_post_no       = '';
        $sysSession->board_ownerid   = $sysSession->school_id;
        $sysSession->board_ownername = $sysSession->school_name;
        // 是否具刊登權限(含修改, 刪除)
        $sysSession->q_right         = ChkRight($result['board_id']);
        $sysSession->b_right         = $sysSession->q_right; // 目前兩者一樣

        $sysSession->q_path = '/';

        list($bname, $default_order) = dbGetStSr('WM_bbs_boards', 'bname, default_order', 'board_id = ' . $sysSession->board_id, ADODB_FETCH_NUM);
        $sysSession->sortby   = $default_order;
        $sysSession->q_sortby = $default_order;
        $bname                = unserialize($bname);
        dbSet('WM_session', "board_name='{$bname[$sysSession->lang]}',q_path='/'", "idx='{$_COOKIE['idx']}'");

        // 回存 SESSION
        $sysSession->restore();

        // 清除 Cookie 所存搜尋條件
        ClearForumCookie();

        // 讀出 extras 值到 cookie 中
        loadExtras2Cookie($sysSession->board_id);
        $faqPara = '';
        if ($_SERVER['argv'] !== null && $_SERVER['argv'][0] == md5('faq')) {
            $faqPara = '?' . $_SERVER['argv'][0];
        }
        
        // 取第一層目錄名
        // academic：wmpro2.5
        // 其他：moocs風格
//        list($x, $scope) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF']);
//        if ($scope === 'academic') {
//            header('Location:/forum/index.php');
//        } else {
            header('Location: /forum/m_node_list.php'.$faqPara);
//        }
?>
<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func='2500700100';
	$sysSession->restore();
	if (!aclVerifyPermission(2500700100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	if(!dbGetNewsBoard($result, 'comment')) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'System Error!');
		die('System Error!');
	}
	elseif(!$result['ok'] && !$result['readonly'])
	{
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'Time has not yet started or time is up.');
	    // die('Time has not yet started or time is up.');
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

	$sysSession->board_id        = $result['board_id'];
	$sysSession->news_board      = 0;		// �D�t�ɶ�(�}�Ҥ�����)����������Q�ת�
	$sysSession->board_readonly  = $result['readonly'];
	$sysSession->board_qonly     = $result['readonly'];
	$sysSession->page_no         = '';
	$sysSession->post_no         = '';
	$sysSession->q_page_no       = '';
	$sysSession->q_post_no       = '';
	$sysSession->board_ownerid   = $sysSession->school_id;
	$sysSession->board_ownername = $sysSession->school_name;
	// �O�_��Z�n�v��(�t�ק�, �R��)
	$sysSession->q_right         = ChkRight($result['board_id']);
	$sysSession->b_right         = $sysSession->q_right;	// �ثe��̤@��

	list($bname, $default_order) = dbGetStSr('WM_bbs_boards', 'bname, default_order', 'board_id = ' . $sysSession->board_id, ADODB_FETCH_NUM);
	$sysSession->sortby          = $default_order;
	$sysSession->q_sortby        = $default_order;
	$bname                       = unserialize($bname);
	dbSet('WM_session', "board_name='{$bname[$sysSession->lang]}',q_path=''", "idx='{$_COOKIE['idx']}'");
    
    // �^�s SESSION
	$sysSession->restore();
	
	// �M�� Cookie �Ҧs�j�M����
	ClearForumCookie();

	// Ū�X extras �Ȩ� cookie ��
	loadExtras2Cookie($sysSession->board_id);

    // mooc �Ҳն}�Ҫ��ܱN�����ɦVindex.php
	if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
	    header('Location: /forum/m_node_list.php');
	}else{
	    header('Location:/forum/index.php');
	}
?>

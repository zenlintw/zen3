<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/acade_news.php');
	require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2500100200';
	$sysSession->restore();
	if (!aclVerifyPermission(2500100200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// �U���ƧǨ̾�
	$OB = $OrderBy['quint'];

	/*
	 * GotoPagePost()
	 *    ���o�`�����D���Ƥνg�ƤΩҦb�ؿ� ( �ɻ��� q_read.php �ݭn�� Session )
	 *    @pram string $node : �峹�s��
	 *    @return bool : ���\ true, ���� false
	 */
	function GotoPagePost($node) {
		global $sysSession, $sysConn, $OB;
		$sysSession->q_page_no = '';
		$sysSession->q_post_no = '';
		$sysSession->q_path = '/';

		if(empty($node)) return false;

		// ���o�C������
		$rows_page = GetForumPostPerPage();

		$qost_no = 0;
		$path = '/';

		// ���o�ؿ�
		$RS_p = dbGetStSr('WM_bbs_collecting', 'path', "board_id={$sysSession->board_id} and node='{$node}'", ADODB_FETCH_ASSOC);
		if($RS_p) {
			$sysSession->q_path = $RS_p['path'];
			$path = $RS_p['path'];
		} else {
			return false;
		}

		// ���o�� board �Ҧ� POST
		$RS = dbGetStMr('WM_bbs_collecting', 'node', "board_id={$sysSession->board_id} and path='{$path}' order by {$OB[$sysSession->q_sortby]}", ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			$qost_no++;
			if($RS->fields['node']==$node) {
				$sysSession->q_post_no = $qost_no;
				$sysSession->q_page_no = ceil($qost_no / $rows_page);
				return true;
			}
			$RS->MoveNext();
		}
		return false;
	}


	if(!dbGetNewsBoard($result, 'faq')) {
		echo 'System Error!';
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'System Error!');
		exit();
	}

	if( empty($_GET['node']) ) {
		header('Location:index.php');
		exit();
	}
	elseif (!ereg('^[0-9]{9,}$', $_GET['node']))
	{
		wmSysLog($sysSession->cur_func, $sysSession->course_id , '' , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Deny!');
		die('Access Deny!');
	}

	$sysSession->board_id        = $result['board_id'];
	$sysSession->news_board      = 0;		// �t�ɶ�(�}�Ҥ�����)����������Q�ת�
	$sysSession->board_readonly  = 1;
	$sysSession->board_qonly     = 1;
	$sysSession->page_no         = '';
	$sysSession->post_no         = '';
	$sysSession->sortby          = 'hit';
	$sysSession->q_sortby        = 'hit';
	// $sysSession->q_page_no    = '';
	// $sysSession->q_post_no    = '';
	$sysSession->board_ownerid   = $sysSession->school_id;
	$sysSession->board_ownername = $sysSession->school_name;
	// �O�_��Z�n�v��(�t�ק�, �R��)
	$sysSession->q_right         = ChkRight($result['board_id']);
	$sysSession->b_right         = $sysSession->q_right;	// �ثe��̤@��


	// �N�g�ƭp��X�æs�� sysSession ��( �`�N: �H�U function �ݩ�b news_nodes �� q_right (b_right)���o���� )
	GotoPagePost($_GET['node']);

	// �^�s SESSION
	$sysSession->restore();

	dbSet('WM_session', "board_name='{$MSG['faq'][$sysSession->lang]}',q_path='{$sysSession->q_path}'", "idx='{$_COOKIE['idx']}'");

	// �M�� Cookie �Ҧs�j�M����
	ClearForumCookie();

	header('Location:/forum/q_read.php');
?>

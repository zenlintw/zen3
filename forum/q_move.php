<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '900300700';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$move_err_msg = Array(-1=>$MSG['msg_move_1'][$sysSession->lang],
						  -2=>$MSG['copyfile_fail1'][$sysSession->lang].' '.
							  $MSG['db_busy'][$sysSession->lang].' '.
							  $MSG['try_later'][$sysSession->lang],
						  -3=>$MSG['msg_move_3'][$sysSession->lang],
						  -4=>$MSG['msg_move_4'][$sysSession->lang]
						);

	function js_exit() {
		global $failed_msgs, $MSG,$sysSession;
		$js_txt = '';
		if(count($failed_msgs) > 0) {	// �����~�o��
			foreach($failed_msgs as $k=>$v)
				$js_txt .= $v . "\\n";
		} else {
			$js_txt = $MSG['move'][$sysSession->lang].$MSG['success_to'][$sysSession->lang];
		}

		$js = "alert('" . $js_txt . "');\r\n";
		$js .= "location.replace('q_read.php');\r\n";

		showXHTML_script('inline',$js);
		exit();
	}

	/***
	 *	"�h��"�B�z�{��
	 *	�Ѽ�: $node : �h���峹
	 *		  $folder_id : �h���ؼи�Ƨ��`�I�s��(�Y node)
	 */
	function do_move($node, $folder_id, &$path) {
		global $sysSession, $sysConn, $sysSiteNo, $move_err_msg, $_SERVER;

		// ���X�Ӹ�Ƨ�
		if($folder_id!=='0') {
			$folder_rs = dbGetStSr('WM_bbs_collecting' , 'path, subject' ,"board_id={$sysSession->board_id} and node='{$folder_id}' and type='D'", ADODB_FETCH_ASSOC);
			if(!$folder_rs) return -3;	// ��Ƨ����s�b

			$path = ($folder_rs['path']=='/' ? '' : $folder_rs['path']) . "/{$folder_rs['subject']}";
		} else {
			$path = '/';
		}

		$RS = dbGetStSr('WM_bbs_collecting', 'picker', "board_id='{$sysSession->board_id}' and node='{$node}'", ADODB_FETCH_ASSOC);
		if(!$RS) { // ��Ʈw�d�ߥ���
		   return -2;
		}

		$picker= $RS['picker'];

		if(($picker==$sysSession->username) || $sysSession->q_right) {	// �ˬd�v��
			dbSet('WM_bbs_collecting', "path='$path'",  "board_id='{$sysSession->board_id}' and node='{$node}' and site={$sysSiteNo}");
		} else {
			return -4;	// �v������
		}
		return 0;
	}

	/****************************************************
	 *	�D�n�{��
	 *
	 ****************************************************/
	$ticket    = $_GET['ticket'];
	$node      = $_GET['node'];
	$site      = $_GET['site'];
	$folder_id = $_GET['folder_id'];

	$failed_msgs = Array();	// ���ѰT��

	// ���� ticket
	if(	$ticket != md5(sysTicketSeed . 'Borad' . $_COOKIE['idx'] . $sysSession->board_id))
	{
		$failed_msgs[] = 'Access denied';
		js_exit();
	}

	// �O�_��Z�n�v��(�t�ק�, �h��, �R��)
	$post_right = ChkRight($sysSession->board_id);
	if(!$post_right) {
		$failed_msgs[] = $move_err_msg[-4];	// "Permission denied";
		js_exit();
	}

   	if( !ereg('[0-9]{6,}',$node) || !ereg('[0-9]{10}',$site) || (!ereg('[0-9a-zA-Z]{32}',$folder_id) && $folder_id!=0) )
   	{
   		$failed_msgs[] = $MSG['move'][$sysSession->lang] . $MSG['failed'][$sysSession->lang];
		js_exit();
   	}

	// �h���峹
	$path = '/';
	if(($ret = do_move($node, $folder_id, $path)) !== 0) {
		$failed_msgs[] = $move_err_msg[$ret];
		wmSysLog($sysSession->cur_func, $sysSession->board_id , $node , $ret, 'auto', $_SERVER['PHP_SELF'], 'Move essential post to ' . $folder_id . ', path=' . $path . $move_err_msg[$ret]);
		js_exit();
	}
	
	wmSysLog($sysSession->cur_func, $sysSession->board_id , $node , 0, 'auto', $_SERVER['PHP_SELF'], 'Move essential post to ' . $folder_id . ', path=' . $path);
	
	$sysSession->q_path = $path; // $folder;
	if($sysSession->q_sortby != 'pt') {	// ���O�̱i�K�ɶ��ƧǪ�
		$where      = getSQLwhere($is_search, 'quint');	// ���o SQL �L�o����
		$total_post = getTotalPost($where, 'quint');	// ���o���O�i�K��
		$rows_page  = GetForumPostPerPage();			// ���o�@���X��
		$total_page = ceil($total_post / $rows_page); 	// �p���`�@���X��

		$sysSession->q_page_no = $total_page;
	} else {
		$sysSession->q_page_no = 1;
	}

	$sysSession->restore();

	header("Location:q_index.php");
?>

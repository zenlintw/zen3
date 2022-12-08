<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/file_api.php');

	/*************************

	�s�W��ذϸ�� db_new_collect($RS)

	@param array $RS ( dbGetStSr() �Ҩ��o�� WM_bbs_posts �}�C )
		( �һݸ��| path �w��n�b $RS['path'] ��, ���s�W���t attach ��� )
	@param int $replyto_node : �Y�O�����̬��^�Ф峹, �N�ثe���I�ন $replyto_node �U���@�I
			�h�s Node �W�h node = $replyto_node (9�X)+  $RS['node'] ��9�X ( �H�����즸��)

	@return int : ���ѶǦ^ 0
		���\�Ǧ^�`�I�s�� (node)

	 *************************/
	function db_new_collect($RS, $replyto_node=0) {
		global $sysConn,$sysSiteNo,$sysSession;

		if (!is_array($RS)) return 0;
		if ($replyto_node) { // ���^�ХD�D�`�I
			$nnode = $replyto_node . substr($RS['node'], 9,9); // �s Node �W�h node = $replyto_node (9�X)+  $RS['node'] ��9�X ( �H�����즸��)
		} else {
			// ���o�ثe��ذϤ��̤j�� node
			list($mnode) = dbGetStSr('WM_bbs_collecting', 'MAX(node)', "board_id={$RS['board_id']} and length(node) = 9", ADODB_FETCH_NUM);
			// ���ͥ��g�� node
			$nnode = empty($mnode) ? '000000001' : sprintf("%09d", $mnode+1);
		}

		// �[�J��Ʈw
		$fields = 'board_id,node,site,path,pt,poster,realname,email,homepage,subject,content,'.
				  'rcount,rank,hit,lang,ctime,picker,post_node';
		$RS['rcount'] = ($RS['rcount'] ? $RS['rcount'] : 'NULL');
		$RS['rank']   = ($RS['rank']   ? $RS['rank']   : 'NULL');

		foreach ($RS as $k => $v) {
			$RS[$k] = mysql_escape_string($v);
		}

		//MIS#23781 ���J��ذϻݫO�d�I�\���� by Small 2012/01/30
		$values = "{$RS['board_id']}, '$nnode',$sysSiteNo,'{$RS['path']}',".
			      "'{$RS['pt']}', '{$RS['poster']}', '{$RS['realname']}', ".
			      "'{$RS['email']}', '{$RS['homepage']}', '{$RS['subject']}', '{$RS['content']}',".
			      "{$RS['rcount']},{$RS['rank']},{$RS['hit']},{$RS['lang']},".
			      "Now(),'{$sysSession->username}','{$RS['node']}'";

		dbNew('WM_bbs_collecting', $fields, $values);

		if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0)
			return 0;
		else
			return $nnode;
	}

	/*****
	 *	���ɽƻs ( �q�@��Ϩ��ذϹ����Ƨ� )
	 *	�Ѽ�: $node, $q_node, $attach, $new_attach
	 *	�^�ǭ�:
	 *		true ���\
	 *		false ����
	 *****/
	function process_files($node, $q_node, $attach, &$new_attach) {

		global $sysSession;

		$board_id = $sysSession->board_id;
		$b_attach = trim($attach);
		$q_attach = '';

		if (empty($b_attach)) {
			$new_attach = '';
			return true;
		}

		$b_path = get_attach_file_path('board', $sysSession->board_ownerid);	// . "/{$node}";
		$q_path = get_attach_file_path('quint', $sysSession->board_ownerid);	// . "/{$q_node}";

		$from_path = "{$b_path}/{$node}";
		$to_path   = "{$q_path}/{$q_node}";


		if (!is_dir($q_path))  @System::mkDir("-p $q_path");
		if (!is_dir($to_path)) @System::mkDir("-p $to_path");

		// �ƻs�ɮ�
		return b_copyfiles( $from_path , $to_path , $b_attach, $new_attach);
	}

	// ���~�N�X (0 �����\)
	$err_id = Array(
		'success'     =>  0,
		'db_error'    => -1,
		'file_error'  => -2,
		'query_error' => -3
	);
	// ���~�N�X�ҥN��r��
	$err_msg = Array(
		 0 => $MSG['collect'][$sysSession->lang] . $MSG['success_to'][$sysSession->lang],
		-1 => $MSG['db_busy'][$sysSession->lang],
		-2 => $MSG['copyfile_fail'][$sysSession->lang],
		-3 => $MSG['query_post_fail'][$sysSession->lang]
	);


	/**************************************
		���榬�J��ذϰʧ@
		@param int $board_id	: �Q�תO�s��
		@param int $node		: �@��Ϥ峹�s��
		@param int $new_node	: �s���ͪ���ذϤ峹�s��
		@param int $site		: ����
		@param string $path		: �N���J�����|	( ���|�w�]�� "/" )
		@param bool $is_move	: �O�_���h��( true : �h�� 		false : �ƻs )
		@param int $replyto_node: ���^�Ф��D�D�`�I

		@return int : error code
	 **************************************/
	function do_collect($board_id, $node, $site, &$new_node, $path=DIRECTORY_SEPARATOR, $is_move=false, $replyto_node=0) {
		global $sysSession, $sysConn, $MSG, $err_id;

		$board_id = intval($board_id);
		$site     = intval($site);
		// ���o�@��Ϥ峹���e
		$RS = dbGetStSr('WM_bbs_posts','*', "board_id={$board_id} and node='{$node}' and site={$site}", ADODB_FETCH_ASSOC);
		if($RS) {

			$RS['path'] = $path;
			$attach     = $RS['attach'];

			// �s�W�J��Ʈw
			$new_node = db_new_collect($RS, $replyto_node);
			if ($new_node == 0) { // ����
				return $err_id['db_fail'];
			} else {
				$q_attach = '';
				if (process_files($node, $new_node, $attach,$q_attach)) {
					if ($q_attach != '')
						dbSet('WM_bbs_collecting', "attach='{$q_attach}'", "board_id={$board_id} and node='{$new_node}' and site={$site}");

					if ($is_move) { // �h��(1) , �ݧR����@���
						// �R���@��ϧ���
						$attach_path = get_attach_file_path('board', $sysSession->board_ownerid). DIRECTORY_SEPARATOR . "{$node}";
						if (is_dir($attach_path)) @System::rm("-rf $attach_path");
						// �R���@��ϸ��
						delete_post($board_id, $node, $site);	// �b /lib/lib_forum.php ��
					}
					return $err_id['success'];
				} else { // �h�ɮץ���, �R���s�W��ؤ��峹
					dbDel('WM_bbs_collecting', "board_id={$board_id} and node='{$new_node}' and site={$site}");
					return $err_id['file_error'];
				}
			}
		} else {
			return $err_id['query_error'];
		}
	}
?>

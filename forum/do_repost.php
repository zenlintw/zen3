<?php
	/**
	 * ��K�峹�ܨ�L�Q�ת�
	 *
	 * @since   2004/09/12
	 * @author  KuoYang Tsao
	 * @version $Id: do_repost.php,v 1.1 2010/02/24 02:38:59 saly Exp $
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/file_api.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '900200400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

//---------------------------------------------
// �禡�}�l
//---------------------------------------------

	/*************************

	�s�W�Q�װϤ峹��� db_new_post($RS)

	�Ѽ� : $RS ( dbGetStSr() �Ҩ��o�� WM_bbs_posts �}�C )
		( �һݸ��| path �w��n�b $RS['path'] ��, ���s�W���t attach ��� )

	�Ǧ^��: ���ѶǦ^ 0
		���\�Ǧ^�`�I�s�� (node)

	 *************************/
	function db_new_post($RS) {
		global $sysConn,$sysSiteNo,$sysSession, $_SERVER;

		// ���o�ثe��ذϤ��̤j�� node
		list($mnode) = dbGetStSr('WM_bbs_posts', 'MAX(node)',"board_id={$RS['board_id']} and length(node) = 9", ADODB_FETCH_NUM);
		// ���ͥ��g�� node
		$nnode = empty($mnode)?'000000001':sprintf('%09d', $mnode+1);

		// �[�J��Ʈw
		// �����i�ध�޸�
		$username = mysql_escape_string($sysSession->username);
		$realname = mysql_escape_string($sysSession->realname);
		foreach($RS as $k=>$v) {
			$RS[$k] = mysql_escape_string($v);
		}
		$fields = 'board_id,node,site,pt,poster,realname,email,homepage,subject,content,lang';
		$values = "{$RS['board_id']}, '$nnode',$sysSiteNo".
			      ", NOW(), '{$username}', '{$realname}', ".
			      "'{$sysSession->email}', '{$sysSession->homepage}', '{$RS['subject']}', '{$RS['content']}',".
			      $RS['lang'];

		dbNew('WM_bbs_posts', $fields, $values);
		if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0) {
		    wmSysLog($sysSession->cur_func, $sysSession->class_id , $RS['board_id'] , 3, 'auto', $_SERVER['PHP_SELF'], '�s�W�Q�װϤ峹���fail!');
			return 0;
		}
		else {
			wmSysLog($sysSession->cur_func, $sysSession->class_id , $RS['board_id'] , 0, 'auto', $_SERVER['PHP_SELF'], '�s�W�Q�װϤ峹���success!');
			return $nnode;
		}
	}

	/* ���ͰQ�תO���ɦs��ؿ� (���t node)
	 * input : $board_id : �Q�ת��s��
	 * return: �Ǧ^���|
	 */
	function get_board_attach_path($board_id, $owner_id=null){
		global $sysSession;

		$ret = '/base/' . $sysSession->school_id;

		switch(strlen($owner_id)) {
			case 5: //�Ǯ�
				break;
			case 7:// �Z��
			case 15:// �Z�Ÿs��
				$ret .= '/class/'.$owner_id;
				break;

			case 8:// �ҵ{
			case 16:// �ҵ{�s��
				$ret .= '/course/'.substr($owner_id, 0, 8);
				break;
			default:
			{
				if ($sysSession->course_id){
					$ret .= '/course/' . $sysSession->course_id;
				} else if($sysSession->class_id) {
					$ret .= '/class/' . $sysSession->class_id;
				}
			}
		}
		$ret .= '/board/' . $board_id ;
		return sysDocumentRoot . $ret;
	}

	/*****
	 *	���ɽƻs ( �����Ƨ� )
	 *	�Ѽ�: $node, $q_node, $attach, $new_attach
	 *	�^�ǭ�:
 *		true ���\
	 *		false ����
	 *****/
	function process_files($src_board, $src_node, $to_board, $to_node, $src_attach, &$new_attach) {

		global $sysSession, $Board_OwnerID, $Board_Owner;
		$board_id = $sysSession->board_id;

		// $b_attach  = trim($attach);
		// $q_attach  = '';
		$src_attach = trim($src_attach);
		$to_attach  = '';

		if($src_attach=='') {
			$new_attach = $to_attach;
			return true;
		}

		getBoardOwner($to_board);
		$to_ownerid = $Board_OwnerID;
		$src_path = get_board_attach_path($src_board, $sysSession->board_ownerid). DIRECTORY_SEPARATOR . $src_node;
		$to_path  = get_board_attach_path($to_board, $to_ownerid). DIRECTORY_SEPARATOR . $to_node;

		if (!is_dir($src_path)) @System::mkDir("-p $src_path");
		if (!is_dir($to_path)) @System::mkDir("-p $to_path");

		// �ƻs�ɮ�
		return b_copyfiles( $src_path , $to_path , $src_attach, $new_attach);
	}

	// ���~�N�X (0 �����\)
	$err_id = Array(
				'success'		=> 0,
				'db_error'		=>-1,
				'file_error'	=>-2,
				'query_error'	=>-3
			  );
	// ���~�N�X�ҥN��r��
	$err_msg = Array(
				0 => $MSG['repost'][$sysSession->lang] . $MSG['success_to'][$sysSession->lang],
				-1=> $MSG['db_busy'][$sysSession->lang],
				-2=> $MSG['copyfile_fail'][$sysSession->lang],
				-3=> $MSG['query_post_fail'][$sysSession->lang]
			  );


	/**************************************
		���榬�J��ذϰʧ@
		�Ѽ�:
			$src_board	: �ӷ��Q�תO�s��
			$src_node	: �ӷ��峹�s��
			$to_board	: �ؼаQ�תO
			$is_move	: �O�_���h��( true : �h�� 		false : �ƻs )(�O�d�\��)
	 **************************************/
	function do_repost($src_board, $src_node, $to_board, $is_move=false) {
		global $sysSession, $sysConn, $sysSiteNo, $MSG, $err_id;

		// ���o�ӷ��峹���e
		$RS = dbGetStSr('WM_bbs_posts','*', "board_id={$src_board} and node='{$src_node}' and site={$sysSiteNo}", ADODB_FETCH_ASSOC);
		if($RS) {
			// Bug 1051 ��K��,�[�W��i�K�ҵ{/�O�W/�i�K��/�i�K�ɶ� Begin
			list($bname, $owner) = dbGetStSr('WM_bbs_boards', 'bname, owner_id', 'board_id=' . $src_board, ADODB_FETCH_NUM);
			switch(strlen($owner)) {
				case 5: //�Ǯ�
					$owner = $sysSession->school_name;
					break;
				case 7:// �Z��
				case 15:// �Z�Ÿs��
					list($owner) = dbGetStSr('WM_class_main', 'caption', 'class_id='.substr($owner, 0, 7), ADODB_FETCH_NUM);
					$owner = $owner ? unserialize($owner) : array();
					$owner = $owner[$sysSession->lang];
					break;
				case 8:// �ҵ{
				case 16:// �ҵ{�s��
					list($owner) = dbGetStSr('WM_term_course', 'caption', 'course_id='.substr($owner, 0, 8), ADODB_FETCH_NUM);
					$owner = $owner ? unserialize($owner) : array();
					$owner = $owner[$sysSession->lang];
					break;
			}
			$bname = $bname ? unserialize($bname) : array();	// ���o�O�W
			list($fname, $lname) = dbGetStSr('WM_user_account', 'first_name, last_name', 'username="' . $RS['poster']. '"', ADODB_FETCH_NUM); // ���o��i�K��
			$RS['poster'] = $RS['poster'] . '(' . checkRealname($fname, $lname) . ')';
			$RS['subject'] = '['. $MSG['repost'][$sysSession->lang] . ']' . $RS['subject'];	// ��i�K���D�[�W[��K]
			$RS['content'] = $MSG['repost_from_board'][$sysSession->lang] . $owner . ' - ' .$bname[$sysSession->lang] . '<br />' .
							 $MSG['repost_from_user'][$sysSession->lang]  . $RS['poster']  . '<br />' .
							 $MSG['repost_from_time'][$sysSession->lang]  . $RS['pt']	   . '<br /><br />' .
							 $RS['content'];
			// Bug 1051 ��K��,�[�W��i�K�ҵ{/�O�W/�i�K��/�i�K�ɶ� End

			$attach = $RS['attach'];
			$RS['board_id'] = $to_board;	// �ܧ� board_id , �� db_new_post() �ϥ�

			// �s�W�J��Ʈw
			$to_node = db_new_post($RS);
			if($to_node==0) { // ����
				return $err_id['db_fail'];
			} else {
				$to_attach = '';
			if(process_files($src_board,$src_node, $to_board, $to_node, $attach,$to_attach)) {
					if($to_attach != '')
					dbSet('WM_bbs_posts',"attach='{$to_attach}'","board_id={$to_board} and node='{$to_node}' and site={$sysSiteNo}");

					if($is_move) {	// �h��(1) , �ݧR����@���

						// �R���@��ϧ���
						$attach_path = get_board_attach_path($src_board). DIRECTORY_SEPARATOR ."{$src_node}";
						if (is_dir($attach_path)) @System::rm("-rf $attach_path");
						// �R���@��ϸ��
						delete_post($src_board, $src_node, $sysSiteNo);	// �b /lib/lib_forum.php ��

					}
					return $err_id['success'];

				} else	{ // �h�ɮץ���, �R���s�W��ؤ��峹
					dbDel('WM_bbs_posts',"board_id={$to_board} and node='{$to_node}' and site={$sysSiteNo}");

					return $err_id['file_error'];
				}
			}
		} else {
			return $err_id['query_error'];
		}
	}
//---------------------------------------------
// �禡����
//---------------------------------------------


//---------------------------------------------
// �D�{���}�l
//---------------------------------------------

	header('Content-type: text/xml');
	echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// �ˬd Ticket
		$ticket = md5(sysTicketSeed . $sysSession->username . 'read' . $sysSession->ticket . $sysSession->board_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			echo '<manifest>Access Fail.</manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '�ڵ��s��!');
			exit;
		}

		$action = getNodeValue($dom, 'action');
		$result = '';
		switch ($action) {
			case 'repost' :   // ��K
				$src_board = $sysSession->board_id;
				$src_node  = getNodeValue($dom, 'src_node');
				$to_board  = getNodeValue($dom, 'to_board');
				$ret       = do_repost($src_board, $src_node, $to_board);
				$result    = '<manifest>' .
				             "<code>{$ret}</code>" .
				             "<message>{$err_msg[$ret]}</message>" .
				             '</manifest>';
				break;
		}

		if (!empty($result)) {
			echo str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
		} else {
			echo '<manifest></manifest>';
		}
	}
?>

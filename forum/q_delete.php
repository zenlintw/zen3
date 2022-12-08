<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lib/lib_newsfaq.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '900300600';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (ereg('^580,([0-9]{10}),([0-9]{6,}),([0-9]{10})\.php$', basename($_SERVER['PHP_SELF']), $reg) &&
	    $reg[1] == $sysSession->board_id
	   ){
	   	$board_id = $reg[1];
	   	$node	  = $reg[2];
	   	$site	  = $reg[3];

		// ���o��@��Ϥ峹�s�� (post_node)
		// �p�G�㦳 post_no , �h�O�q�@��Ϧ�����
	   	$RS = dbGetStSr('WM_bbs_collecting','post_node', "board_id='$board_id' and node='$node' and site=$site", ADODB_FETCH_ASSOC);
	   	$post_node	= '';
	   	if($RS)
	   		$post_node = $RS['post_node'];

		delete_qost($board_id, $node, $site, $post_node);
		wmSysLog($sysSession->cur_func, $board_id , $node , 0, 'auto', $_SERVER['PHP_SELF'], "Delete essential post (board_id, node, site, post_node) = ({$board_id}, {$node}, {$site}, {$post_node})");
		// ��s�ǲߤ��߱`�����D�C��
		if(IsNewsBoard('faq'))
				createFAQXML($sysSession->school_id, 'faq');
	}
	header('Location: q_read.php');
?>

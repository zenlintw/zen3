<?php
	/**
	 * 聊天室
	 *
	 * @since   2003/11/26
	 * @author  ShenTing Lin
	 * @version $Id: index.php,v 1.1 2010/02/24 02:40:22 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$rid = $sysSession->room_id;   // 聊天室編號
	if (empty($rid)) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '禁止進入(room_id is empty)!');
	   die($MSG['chat_deny'][$sysSession->lang]);
	}

	require_once(sysDocumentRoot . '/learn/chat/chat_room.php');
?>

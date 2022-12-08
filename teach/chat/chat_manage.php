<?php
	/**
	 * 教師端的聊天室管理
	 *
	 * @since   2003/12/25
	 * @author  ShenTing Lin
	 * @version $Id: chat_manage.php,v 1.1 2010/02/24 02:40:22 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$owner_id = $sysSession->course_id;
	$env      = 'teach';
	$help     = $MSG['msg_help_chat_manage'][$sysSession->lang];
	$aryTitle = array(
		array($MSG['course'][$sysSession->lang] . $MSG['tabs_chat_list'][$sysSession->lang], 'tabs1', ''),
		array($MSG['group_chat_list'][$sysSession->lang], 'tabs2', "goto_chatgroup();")
	);

	require_once(sysDocumentRoot . '/academic/chat/chat_main_manage.php');
?>

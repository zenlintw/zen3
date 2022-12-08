<?php
	/**
	 * 管理者端的聊天室管理
	 *
	 * @since   2003/12/25
	 * @author  ShenTing Lin
	 * @version $Id: chat_manage.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');

	$owner_id = $sysSession->school_id;
	$env      = 'academic';
	$help     = $MSG['msg_help_sch_chat_manage'][$sysSession->lang];
	$aryTitle = array(
		array($MSG['title_school'][$sysSession->lang] . $MSG['tabs_chat_list'][$sysSession->lang], 'tabs1', ''),
	);

	require_once(sysDocumentRoot . '/academic/chat/chat_main_manage.php');

?>

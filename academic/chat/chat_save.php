<?php
	/**
	 * 儲存討論室設定
	 *
	 * @since   2003/12/30
	 * @author  ShenTing Lin
	 * @version $Id: chat_save.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	$owner_id = $sysSession->school_id;
	$env      = 'academic';

	require_once(sysDocumentRoot . '/academic/chat/chat_main_save.php');

?>

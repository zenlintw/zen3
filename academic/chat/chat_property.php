<?php
	/**
	 * ²á¤Ñ«Ç³]©w
	 *
	 * @since   2003/12/26
	 * @author  ShenTing Lin
	 * @version $Id: chat_property.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	$owner_id = $sysSession->school_id;
	$env      = 'academic';

	require_once(sysDocumentRoot . '/academic/chat/chat_main_property.php');

?>

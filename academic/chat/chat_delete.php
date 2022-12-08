<?php
	/**
	 * §R°£²á¤Ñ«Ç
	 *
	 * @since   2003/12/31
	 * @author  ShenTing Lin
	 * @version $Id: chat_delete.php,v 1.1 2010/02/24 02:38:13 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	$owner_id = $sysSession->school_id;
	$env      = 'academic';

	require_once(sysDocumentRoot . '/academic/chat/chat_main_delete.php');

?>

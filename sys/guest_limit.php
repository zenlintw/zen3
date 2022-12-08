<?php
	/**
	 * Guest ¤H¼Æ­­¨î
	 *
	 * @since   2005/07/28
	 * @author  ShenTing Lin
	 * @version $Id: guest_limit.php,v 1.1 2010/02/24 02:40:19 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	define('GUEST_LIMIT', true);
	require_once(sysDocumentRoot . '/sys/guest_main.php');

?>

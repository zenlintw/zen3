<?php
	/**
	 * ��ܹϧδO�J����
	 *
	 * �إߤ���G2002/02/24
	 * @author  ShenTing Lin
	 * @version $Id: showpic.php,v 1.1 2010/02/24 02:39:10 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	getUserPic($sysSession->username);	
?>
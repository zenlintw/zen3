<?php
	/**
	 * ±H«H
	 *
	 * @since   2004/06/25
	 * @author  ShenTing Lin
	 * @version $Id: director_mail.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/direct_member_manage.php');
	
	$head       = $MSG['tabs_director_mail'][$sysSession->lang];
	$title      = $MSG['tabs_director_mail'][$sysSession->lang];
	$target_url = 'director_mail_send.php';
	
	require_once(sysDocumentRoot . '/direct/member/lib_mail.php');
?>

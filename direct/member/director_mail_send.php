<?php
	/**
	 * �H�o�q���H
	 *
	 * @since   2004/06/28
	 * @author  ShenTing Lin
	 * @version $Id: director_mail_send.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/direct_member_manage.php');

	$head       = $MSG['tabs_director_mail_send'][$sysSession->lang];
	$title      = $MSG['tabs_director_mail_send'][$sysSession->lang];
	$btn        = $MSG['btn_return_director_list'][$sysSession->lang];
	$target_url = 'director_list.php';
	$_POST['method'] = 'email';
	
	require_once(sysDocumentRoot . '/direct/member/lib_mail_send.php');
?>

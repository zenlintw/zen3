<?php
	/**
	 * 儲存收件者看過此mail的次數
	 *
	 *     傳進來的參數
	 *         1. mail_serial : 此封mail的序號
	 *
	 *
	 * 建立日期：2003/12/04
	 * @author  Saly Lin
	 * @version $Id: mail_count.php,v 1.1 2010/02/24 02:38:55 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

    $tmp = explode('_',$_GET['mailid']);

    if (ereg('^[0-9]{5}$', $tmp[0])) dbSet('WM_mails', "count = count+1", 'mail_serial=' . intval($tmp[1]));
?>

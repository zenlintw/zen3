<?php
	/**
	 * 顯示圖形嵌入頁面
	 *
	 * 建立日期：2002/02/24
	 * @author  ShenTing Lin
	 * @version $Id: showpic.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$enc = base64_decode(trim($_GET['a']));
	$serial = trim(@mcrypt_decrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $enc, 'ecb'));
	list($username) = dbGetStSr('WM_im_message', '`sender`', "`username`='{$sysSession->username}' AND `serial`='{$serial}'", ADODB_FETCH_NUM);
	getUserPic($username, true);
?>

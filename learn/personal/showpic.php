<?php
	/**
	 * 顯示圖形嵌入頁面
	 *
	 * 建立日期：2002/02/24
	 * @author  ShenTing Lin
	 * @version $Id: showpic.php,v 1.1 2010/02/24 02:39:10 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	getUserPic($sysSession->username);	
?>
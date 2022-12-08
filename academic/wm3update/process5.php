<?php
	/**
	 * 儲存訊息
	 *
	 *     相關的動作
	 *         1. 轉換收件者的切割符號
	 *         2. 發送到收件者的信箱
	 *         3. 記錄一份到備份匣
	 *
	 * PS: 應該要設最多能寄給幾個人
	 *
	 * 建立日期：2003/05/09
	 * @author  ShenTing Lin
	 * @version $Id: process5.php,v 1.1 2010/02/24 02:38:48 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/message/collect.php');

	/**
	 * 轉換收件者的切割符號
	 *     1. 空白 -> ,
	 *     2. ; -> ,
	 **/
	function mailFilterCallback($mail)
	{
		return preg_match(sysMailRule, $mail);
	}
	
	
	// 將收件者切割放到陣列中，並且過濾重複的人員
	$to = array_unique(array_filter(preg_split('/[^\w.@-]+/', $_POST['to'], -1, PREG_SPLIT_NO_EMPTY), 'mailFilterCallback'));

	// 標題不許使用 html
	$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);

	// 內文的型態
	$type = (!$_POST['isHTML']) ? 'text' : 'html';

	// 內文去除所有的不必要 html
	$content = strip_scr($_POST['content']);

	// 內容
	$mail = buildMail('', $subject, $content, $type);
	foreach ($to as $username) {
		// 送信
		$mail->to = $username;
		$mail->send();
	}
	
	header('Location: /academic/wm3update/list.php');
?>

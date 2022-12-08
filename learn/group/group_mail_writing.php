<?php
	/**
	 * 分組討論 - 寄信給小組 - 寄出
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: group_mail_writing.php,v 1.1 2010/02/24 02:39:07 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-05-08(新版直接套用/lib/wm_mails.php)
	 */

// {{{ 函式庫引用 begin
	 require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	 require_once(sysDocumentRoot . '/lib/wm_mails.php');
	 require_once(sysDocumentRoot . '/lang/msg_center.php');
// }}} 函式庫引用 end
	
	$reciver = array();
	if (is_array($_POST['user']) && count($_POST['user']))	// 取得勾選學員的Email
	{
		$user    = preg_split('/[^\w.-]+/', implode(',', $_POST['user']) , -1, PREG_SPLIT_NO_EMPTY);
		$reciver = dbGetCol('WM_user_account', 'email', 'username in ("'.implode('","', $user).'") and email is not null');
	}
	
	if ($_POST['to'] != '') // 額外輸入的Email
	{
		$to = preg_split('/[^\w.@-]+/', $_POST['to'], -1, PREG_SPLIT_NO_EMPTY);
		$reciver = array_merge($reciver, $to);
	}
	
	$reciver = array_unique($reciver);

	$mail = new wm5MailSender();
	$mail->reciver     = $reciver;
	$mail->priority    = intval($_POST['priority']);
	$mail->subject     = trim($_POST['subject']);
	$mail->content     = trim($_POST['content']);	// 在wm_mails.php中去除所有的不必要 html
	$mail->isHTML      = (intval($_POST['isHTML']) > 0);
	$mail->tagline     = intval($_POST['tagline']);
	$mail->title       = $MSG['tabs_send_result'][$sysSession->lang];
	$mail->send_kind   = 'split';
	$mail->uri_target  = 'group_list.php';
	$mail->memsg['btn_return'] = $MSG['goto_group_list'][$sysSession->lang];
	$mail->send();
?>

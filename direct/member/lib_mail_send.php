<?php
	/**
	 * 寄發通知信
	 *
	 * @since   2004/06/28
	 * @author  ShenTing Lin
	 * @version $Id: lib_mail_send.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_mails.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '500200200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ary    = (isset($_POST['user'])) ? $_POST['user'] : array();
	$method = trim($_POST['method']);
	$user   = array();
	switch ($method) {
		case 'email'  :
			if (is_array($ary)) {
				foreach ($ary as $username) {
					if (preg_match(sysMailRule, $username)) {
						$user[] = $username;
					} else {
						$act = getUserDetailData($username);
						$user[] = $act['email'];
					}
				}
			}
			break;
		case 'message':
			break;
		case 'both'   :
			if (is_array($ary)) {
				foreach ($ary as $username) {
					if (preg_match(sysMailRule, $username)) {
						$user[] = $username;
					} else {
						$act = getUserDetailData($username);
						$user[] = $username;
						$user[] = $act['email'];
					}
				}
			}
			break;
		default:
	}

	$reciver  = trim($_POST['to']) . ';' . implode(';', $user);
	$mail = new wmMailSender();

	$mail->reciver     = $reciver;
	$mail->priority    = intval($_POST['priority']);
	$mail->subject     = trim($_POST['subject']);
	$mail->content     = trim($_POST['content']);
	$mail->isHTML      = (intval($_POST['isHTML']) > 0);
	$mail->tagline     = intval($_POST['tagline']);

	$mail->head        = $head;
	$mail->title       = $title;
	$mail->send_kind   = 'split';
	$mail->uri_target  = $target_url;
	$mail->memsg['btn_return'] = $btn;
	$mail->send();
?>

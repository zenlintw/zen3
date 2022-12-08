<?php
	/**
	 * 寄發通知信
	 *
	 * @since   2004/06/28
	 * @author  ShenTing Lin
	 * @version $Id: stud_mailto_send.php,v 1.1 2010/02/24 02:40:31 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_mails.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php'); // #036655 Custom(B) mars 20150305*
	
	$sysSession->cur_func = '500300300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$ary    = $_POST['user'];
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
			if (is_array($ary)) {
				foreach ($ary as $username) {
					if (!preg_match(sysMailRule, $username)) {
						$user[] = $username;
					}
				}
			}
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
	/*#036655 Custom(B) mars 20150305*/
	list($caption) = dbGetStSr("WM_term_course","caption","course_id = {$sysSession->course_id}");
	$course_title = getCaption($caption);
	/*#036655 Custom(E) mars 20150305*/

	$reciver  = trim($_POST['to']);
	$reciver .= ',' . implode(',', $user);
	$mail = new wmMailSender();

	$mail->reciver     = $reciver;
	$mail->priority    = intval($_POST['priority']);
	/*#036655 Custom(B) mars 20150305*/
	$mail->subject     = '['.$course_title[$sysSession->lang].']'.trim($_POST['subject']);
	/*#036655 Custom(E) mars 20150305*/
	$mail->content     = trim($_POST['content']);
	$mail->isHTML      = (intval($_POST['isHTML']) > 0);
	$mail->tagline     = intval($_POST['tagline']);

	$mail->title       = $MSG['tabs_mail_send'][$sysSession->lang];
	$mail->send_kind   = 'split';
	$mail->uri_target  = 'stud_mailto.php';
	$mail->memsg['btn_return'] = $MSG['btn_return_mailto'][$sysSession->lang];
	$mail->send();
	
	wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'mailto:' . $reciver);
?>

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
	 * @version $Id: send_mail1.php,v 1.1 2010/02/24 02:39:07 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/class_manage.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '500100100';
	$sysSession->restore();
	if (!aclVerifyPermission(500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 判斷信件總上傳夾檔 有無超過 apache 總上傳檔案size (begin)
	// 判斷是否有夾檔
	if ((count($_POST) == 0) && (count($_FILES) == 0))
	{
		$msg = $MSG['msg_file_max'][$sysSession->lang];
		$js = <<< BOF
	window.onload = function () {
		alert("{$msg}");
		window.location.href = 'stud_list.php';
	};
BOF;
		showXHTML_script('inline', $js);
		die();
	}
	else
	{
		// 判斷信件每個夾檔 有無超過 apache 單一上傳檔案 size (begin)
		$file_amount = count($_FILES['uploads']['name']);
		$msg = array();
		for ($i = 0; $i < $file_amount; $i++)
		{
			/*
			 * 相關參考網址：http://www.php.net/manual/en/features.file-upload.php
			 * 錯誤碼參考網址：http://www.php.net/manual/en/features.file-upload.errors.php
			 */
			if ($_FILES['uploads']['error'][$i] == UPLOAD_ERR_NO_FILE)
				continue;
			else if ($_FILES['uploads']['error'][$i] != UPLOAD_ERR_OK)
				$msg[] = stripslashes($_FILES['uploads']['name'][$i]);
		}
		if (count($msg) > 0)
		{
			$show_msg = implode(', ', $msg) . ' ' . str_replace('%MIN_SIZE%', ini_get('upload_max_filesize'), $MSG['title17'][$sysSession->lang]);
			$js = <<< BOF
	window.onload = function ()
	{
		alert("{$show_msg}");
		document.backForm.submit();
	};
BOF;
			showXHTML_head_B($MSG['title'][$sysSession->lang]);
				showXHTML_script('inline', $js);
			showXHTML_head_E('');
			showXHTML_body_B('');
				showXHTML_form_B('action="send_mail.php" method="post" enctype="multipart/form-data" style="display:none"', 'backForm');
					showXHTML_input('hidden', 'subject'  , stripslashes($_POST['subject']), '', '');
					showXHTML_input('hidden', 'content'  , stripslashes($_POST['content']), '', '');
					showXHTML_input('hidden', 'send_user', $_POST['send_user'], '', '');
					showXHTML_input('hidden', 'ticket'   , $_POST['ticket']   , '', '');
				showXHTML_form_E();
			showXHTML_body_E('');
			die();
		}
		// 判斷信件每個夾檔 有無超過 apache 單一上傳檔案 size (end)
	}
	// 判斷信件總上傳夾檔 有無超過 apache 總上傳檔案size (end)

	$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);
	if (trim($_POST['ticket']) != $ticket) {
		die($MSG['msg_access_deny'][$sysSession->lang]);
	}

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');

		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array(
						array($MSG['tabs_send_result'][$sysSession->lang], 'tabs1')
					);
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" class="cssTable"');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('width="40" nowrap="nowrap" align="center"' , $MSG['number'][$sysSession->lang]);
							showXHTML_td('width="80" nowrap="nowrap" align="center"' , $MSG['to'][$sysSession->lang]);
							showXHTML_td('width="250" nowrap="nowrap" align="center"', $MSG['result'][$sysSession->lang]);
						showXHTML_tr_E('');

						// *********************************************************
						//  查詢 成員的 email
						$emails = array();
						$error_emails = array();
						if ($_POST['send_user'] != '')
						{
							$a_send_user = explode(',', $_POST['send_user']);
							$send_user = array();
							foreach ($a_send_user as $val)
							{
								if (!preg_match(Account_format, $val) ||
									strlen($val) < sysAccountMinLen ||
									strlen($val) > sysAccountMaxLen) continue;
								$send_user[] = $val;
							}
							$RS = dbGetStMr('WM_user_account', 'email', 'username in ("' . implode('","', $send_user) . '")', ADODB_FETCH_ASSOC);
							// if ($sysConn->ErrorNo()) die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
							if ($RS)
							{
								while(!$RS->EOF)
								{
									if (strlen($RS->fields['email']) > 0)
									{
										// 判斷 mail 是否合法
										$user_mail = strtolower($RS->fields['email']);
										if (preg_match(sysMailRule, $user_mail))
										{
											$emails[] = $user_mail;
										}
										else
										{
											$error_emails[] = $user_mail;
										}
									}
									$RS->MoveNext();
								}
							}
						}

						if (($_POST['to'] != '') && ($_POST['to'] != $MSG['title12'][$sysSession->lang]))
						{
							$to_array = split('[;, ]+', trim($_POST['to']));
							// 判斷額外收件者 的 email 是否合法
							foreach ($to_array as $val)
							{
								$user_mail = strtolower($val);
								if (preg_match(sysMailRule, $user_mail))
								{
									$emails[] = $user_mail;
								}
								else
								{
									$error_emails[] = $user_mail;
								}
							}
						}

						$note  = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>'; // 確保 PHP 不會辨識錯誤
						$note .= '<manifest><to>' . implode(', ', $emails) . '</to></manifest>';

						$priority = 0;	// 預設為一般優先順序
						// 標題不許使用 html
						$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
						$type = 'html';	// 型態一律都以html格式來呈現
						// 內文去除所有的不必要 html
						$content = strip_scr($_POST['content']);
						//	$content .= '<img src="xxx.php?mailid=" width="0" height="0" border="0">';

						// 取出簽名檔
						$tag_serial = intval($_POST['tagline']);
						$RS = dbGetStSr('WM_user_tagline', 'tagline', "serial={$tag_serial} AND username='{$sysSession->username}'", ADODB_FETCH_ASSOC);
						$result = array();
						$tagline = $RS['tagline'];
						ereg('\[html\](.*)\[/html\]', $tagline, $result);
						if (count($result) > 1)
							$tagline = $result[1];

						// 儲存夾檔。如果有的話。(儲存夾檔到寄件者的目錄去)
						$orgdir = MakeUserDir($sysSession->username);
						$ret = trim(save_upload_file($orgdir, 0, 0));

						// 以1000人為一單位 發信出去 (Begin)
						$tos = array_chunk($emails, 1000);
						foreach ($tos as $to)
						{
							$mail_list = implode(',', $to);
							dbNew('WM_mails', 'function_id,froms,tos,submit_time,send_status', "0,'{$sysSession->username}','{$mail_list}',NOW(),'1'");
							$InsertID = $sysConn->Insert_ID();
							// $content1 = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/mail_count.php?mailid='. $sysSession->school_id . '_' . $InsertID . '" style="display:none">' . $content;
							$content1 = $content;

							$mail = buildMail('', $subject, $content1, 'html', $tagline, $ret, $orgdir, $priority, false);
							$mail->to = $sysSession->email;	// 以寄件者為to
							$mail->headers = 'Bcc: ' . $mail_list;
							$mail->send();
							dbSet('WM_mails', "send_status='2'", "mail_serial={$InsertID}");

							// 記錄到 WM_log_manager
							$msg = $sysSession->username . 'send mail (Bcc:)' . $mail_list;
							wmSysLog('2400300500', $sysSession->course_id, 0, '0', 'manager', $_SERVER['SCRIPT_FILENAME'], $msg);
						}
						// 以1000人為一單位 發信出去 (End)

						// 寄到對方的訊息中心 (Begin)
						foreach ($send_user as $to)
						{
							$nret = cpAttach($to, $orgdir, $ret);
							collect('sys_inbox', $sysSession->username, $to, '', $subject, $content, $type, $tagline, $nret, $priority, '', $note);
						}
						// 寄到對方的訊息中心 (End)
						// 寄件者的寄件匣備份
						collect('sys_sent_backup', $sysSession->username, $sysSession->username, '', $subject, $content, $type, $tagline, $ret, $priority, 'read', $note);

					// *********************************************************
						// 正確的 email
						$i = 0;
						foreach ($emails as $to)
						{
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td(' nowrap="nowrap" align="center"', ++$i);
								showXHTML_td(' nowrap="nowrap"', $to);
								showXHTML_td('nowrap="nowrap"', $MSG['sended'][$sysSession->lang]);
							showXHTML_tr_E('');
						}

						// 錯誤的 email
						foreach ($error_emails as $to)
						{
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('nowrap="nowrap" align="center"', ++$i);
								showXHTML_td('nowrap="nowrap"', $to);
								showXHTML_td('nowrap="nowrap"', $MSG['title18'][$sysSession->lang]);
							showXHTML_tr_E('');
						}

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								showXHTML_input('button', '', $MSG['title13'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_list.php\')"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

	showXHTML_body_E('');
?>

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
	 * @version $Id: send_class_mail1.php,v 1.1 2010/02/24 02:38:15 saly Exp $
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

	$sysSession->cur_func = '2400300500';
	$sysSession->restore();
	if (!aclVerifyPermission(2400300500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{
	}

	// 偵測上傳的檔案大小是否超過限制
	if (detectUploadSizeExceed())
	{
		showXHTML_script('inline', 'alert("' . $MSG['upload_file_error'][$sysSession->lang] . '"); location.replace("people_manager.php");');
		die();
	}

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
		showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary   = array();
					$ary[] = array($MSG['tabs_send_result'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top"');
					showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('nowrap="nowrap" align="center"', $MSG['number'][$sysSession->lang]);
							showXHTML_td('nowrap="nowrap" align="center"', $MSG['to'][$sysSession->lang]);
							showXHTML_td('nowrap="nowrap" align="center"', $MSG['result'][$sysSession->lang]);
						showXHTML_tr_E('');

					// 處理 寄信的 程式 (begin)
						//  查詢 成員的 email
						$temp_user    = array();
						$emails       = array();
						$error_emails = array();
						if ($_POST['send_user'] != '')
						{
							$a_send_user = preg_split('/[^\w.-]+/', $_POST['send_user'], -1, PREG_SPLIT_NO_EMPTY);
							$RS = dbGetStMr('WM_user_account', 'email', 'username in ("' . implode('","', $a_send_user) . '")', ADODB_FETCH_ASSOC);
							if ($sysConn->ErrorNo()) die($sysConn->ErrorNo());
							while (!$RS->EOF)
							{
								// 判斷 mail 是否合法
								$user_mail = strtolower($RS->fields['email']);
								if (preg_match(sysMailRule, $user_mail))
									$emails[] = $user_mail;
								else
									$error_emails[] = $user_mail;
								$RS->MoveNext();
							}
						}

						if ($_POST['class_id'] != '')
						{
							//  WM_class_member
							$cid  = preg_replace('/[^\d,]+/', '', $_POST['class_id']);
							$sqls = str_replace(array('%TABLE%', '%CLASS_ID%'), array('WM_class_member', $cid), $Sqls['get_many_class'] . ' order by A.username asc');
							chkSchoolId('WM_class_member');
							$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
							$RS   = $sysConn->Execute($sqls);
							if ($RS && $RS->RecordCount() > 0)
							{
								while (!$RS->EOF)
								{
									$temp_user[] = $RS->fields['username'];
									$user_mail = strtolower($RS->fields['email']);
									if (preg_match(sysMailRule, $user_mail))
										$emails[] = $user_mail;
									else
										$error_emails[] = $user_mail;
									$RS->MoveNext();
								}
							}
						}


						/**
							* 轉換收件者的切割符號
							*     1. 空白 -> ,
							*     2. ; -> ,
							**/
						if (($_POST['to'] != '') && ($_POST['to'] != $MSG['title12'][$sysSession->lang]))
						{
							$to = parseTo($_POST['to']);
							// 判斷額外收件者 的 email 是否合法
							for ($i = 0, $c = count($to); $i < $c; $i++)
							{
								$user_mail = strtolower($to[$i]);
								if (preg_match(sysMailRule, $user_mail))
									$emails[] = $user_mail;
								else
									$error_emails[] = $user_mail;
							}
						}

						// 將收件者切割放到陣列中，並且過濾重複的人員
						$to    = array_unique($emails);
						$note  = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>'; // 確保 PHP 不會辨識錯誤
						$note .= '<manifest><to>' . implode(', ', $to) . '</to></manifest>';
						$priority = 0; // 預設為一般優先順序

						// 標題不許使用 html
						$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
						// 內文的型態
						$type = 'html'; // 型態一律都以html格式來呈現
						// 內文去除所有的不必要 html
						$content = strip_scr($_POST['content']);

						// 取出簽名檔
						$tag_serial = intval($_POST['tagline']);
						list($tagline) = dbGetStSr('WM_user_tagline', 'tagline', "serial={$tag_serial} AND username='{$sysSession->username}'", ADODB_FETCH_NUM);
						ereg('\[html\](.*)\[/html\]', $tagline, $result);
						if (count($result) > 1)
							$tagline = $result[1];

						// 儲存夾檔。如果有的話。(儲存夾檔到寄件者的目錄去)
						$orgdir = MakeUserDir($sysSession->username);
						$ret = trim(save_upload_file($orgdir, 0, 0));
						$list_tmp = explode("\t", $ret);

						// 建立 mime mail 並且建立複製檔案的清單 (Begin)
						$file_list = array();
						for ($i = 0; $i < count($list_tmp); $i = $i + 2)
						{
							$filename = $orgdir . DIRECTORY_SEPARATOR . $list_tmp[$i + 1];
							if (!file_exists($filename) || !is_file($filename)) continue;
							$file_list[] = $list_tmp[$i];
							$file_list[] = $list_tmp[$i + 1];
						}
						// 建立 mime mail 並且建立複製檔案的清單 (End)

						$no_user    = array();
						$mail_list  = '';
						$mail_count = 0;
						$ary = array_chunk($to, 1000);
						foreach ($ary as $v)
						{
							$mail_list = implode(',', $v);
							dbNew('WM_mails', 'function_id,froms,tos,submit_time,send_status', "0,'{$sysSession->username}','{$mail_list}',NOW(),'1'");
							$InsertID = intval($sysConn->Insert_ID());
							// $content1 = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/mail_count.php?mailid=' . $sysSession->school_id . '_' . $InsertID . '" style="display:none">'. $content;
							$content1 = $content;
							$mail = buildMail('', $subject, $content1, 'html', $tagline, $ret, $orgdir, $priority, false);
							$mail->to = $sysSession->email;	// 以寄件者為to
							$mail->headers = 'Bcc: ' . $mail_list;
							$mail->send();
							// 記錄到 WM_log_manager
							$msg = $sysSession->username . 'send mail (Bcc:)' . $mail_list;
							wmSysLog('2400300500', $sysSession->school_id, 0, '0', 'manager', $_SERVER['SCRIPT_FILENAME'], $msg);
							dbSet('WM_mails', "send_status='2'", "mail_serial={$InsertID}");
						}

						$send_sys_inbox_user = '';

						//  寄到 $_POST['username'] or $_POST['class_id'] 所屬的帳號的 收件夾
						foreach ($temp_user as $u)
						{
							collect('sys_inbox', $sysSession->username, $u, '', $subject, $content, $type, $tagline, $ret, $priority, '', $note);
							cpAttach($u, $orgdir, $file_list);
						}
						// 寄件者的寄件匣備份
						collect('sys_sent_backup', $sysSession->username, $sysSession->username, '', $subject, $content, $type, $tagline, $ret, $priority, 'read', $note);

						// 顯示寄信的結果 (begin)
						// 正確的 email
						$cnt = count($to);
						for ($i = 0; $i < $cnt; $i++)
						{
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('nowrap="nowrap" align="center"', $i + 1);
								showXHTML_td('nowrap="nowrap"', $to[$i]);
								showXHTML_td('nowrap="nowrap"', $MSG['sended'][$sysSession->lang]);
							showXHTML_tr_E('');

						}

						// 錯誤的 email
						for ($i = 0, $c = count($error_emails); $i < $c; $i++)
						{
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('nowrap="nowrap" align="center"', $i + $cnt);
								showXHTML_td('nowrap="nowrap"', $error_emails[$i]);
								showXHTML_td('nowrap="nowrap"', $MSG['title18'][$sysSession->lang]);
							showXHTML_tr_E('');
						}

						// 顯示寄信的結果 (end)
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								$target = 'people_manager.php';
								showXHTML_input('button', '', $MSG['return_people'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'' . $target . '\')"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					// 處理 寄信的 程式 (end)
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	// 判斷信件每一夾檔 有無超過 2MB (END)

	//  寄信
	showXHTML_form_B('action="send_class_mail.php" method="post" enctype="multipart/form-data" style="display:none"', 'backForm');
		showXHTML_input('hidden', 'subject', stripslashes($_POST['subject']), '', '');
		showXHTML_input('hidden', 'content', stripslashes($_POST['content']), '', '');
		showXHTML_input('hidden', 'send_user', $_POST['send_user'], '', '');
		showXHTML_input('hidden', 'class_id', $_POST['class_id'], '', '');
		showXHTML_input('hidden', 'ticket', $_POST['ticket'], '', '');
	showXHTML_form_E();

	showXHTML_body_E('');
?>

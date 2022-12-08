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
	 * @version $Id: send_class_grade_mail1.php,v 1.1 2010/02/24 02:38:15 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/class_manage.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/academic/class/send_detail_grade.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2400300500';
	$sysSession->restore();
	if (!aclVerifyPermission(2400300500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{
	}

	// 偵測上傳的檔案大小是否超過限制
	if (detectUploadSizeExceed())
	{
		showXHTML_script('inline', 'alert("' . $MSG['upload_file_error'][$sysSession->lang] . '"); location.replace("view_grade.php");');
		die();
	}

	// 標題不許使用 html
	$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);

	//  判斷勾選 那些細目 (begin)
	$ary = array();
	if ($_POST['course_name'] != '')  $ary[] = 'course_name';  // 課程名稱
	if ($_POST['teacher'] != '')      $ary[] = 'teacher';      // 授課老師
	if ($_POST['period'] != '')       $ary[] = 'period';       // 課程起訖
	if ($_POST['course_state'] != '') $ary[] = 'course_state'; // 課程狀態
	if ($_POST['fair_grade'] != '')   $ary[] = 'fair_grade';   // 及格標準
	if ($_POST['every_grade'] != '')  $ary[] = 'every_grade';  // 各科總成績
	if ($_POST['every_credit'] != '') $ary[] = 'every_credit'; // 本科學分
	if ($_POST['real_credit'] != '')  $ary[] = 'real_credit';  // 實得學分
	$detail_title = implode(',', $ary);
	//  判斷勾選 那些細目 (end)

	// 判斷信件總上傳夾檔 有無超過 apache 總上傳檔案size (begin)

	// 判斷是否有夾檔
	$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);
	if (trim($_POST['ticket']) != $ticket) {
		die($MSG['msg_access_deny'][$sysSession->lang]);
	}

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
	// 判斷信件每一夾檔 有無超過 2MB (BEGIN)
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary   = array();
					$ary[] = array($MSG['tabs_send_result'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" ');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('width="40" nowrap="nowrap" align="center"', $MSG['number'][$sysSession->lang]);
							showXHTML_td('width="80" nowrap="nowrap" align="center"', $MSG['to'][$sysSession->lang]);
							showXHTML_td('width="250" nowrap="nowrap" align="center"', $MSG['result'][$sysSession->lang]);
						showXHTML_tr_E('');
// 處理 寄信的 程式 (begin)
// **********************************************************
	$no_user      = array();
	$emails       = array();
	$error_emails = array();
	$type         = 'html'; // 型態一律都以html格式來呈現
	// 內文去除所有的不必要 html
	$content      = strip_scr($_POST['content']);
	// 取出簽名檔
	$serial  = intval($_POST['tagline']);
	$RS      = dbGetStSr('WM_user_tagline', 'tagline', "serial={$serial} AND username='{$sysSession->username}'", ADODB_FETCH_ASSOC);
	$tagline = ($RS) ? $RS['tagline'] : '';

	// 儲存夾檔。如果有的話。(儲存夾檔到寄件者的目錄去)
	$orgdir  = MakeUserDir($sysSession->username);
	$ret     = trim(save_upload_file($orgdir, 0, 0));
	// 建立 mime mail 並且建立複製檔案的清單 (End)

	// if begin
	if ($_POST['send_user'] != '')
	{
		$a_send_user  = preg_split('/[^\w.-]+/', $_POST['send_user'], -1, PREG_SPLIT_NO_EMPTY);
		// for begin
		for ($i = 0, $c = count($a_send_user);$i < $c; $i++)
		{
			list($s_email) = dbGetStSr('WM_user_account', 'email', "username='{$a_send_user[$i]}'", ADODB_FETCH_NUM);
			if (preg_match(sysMailRule, strtolower($s_email)))
				$emails[] = $s_email;
			else
				$error_emails[] = $s_email;

			$html_file = grade_html($a_send_user[$i], $detail_title);
			dbNew('WM_mails', 'function_id,froms,tos,submit_time,send_status', "0,'{$sysSession->username}','{$s_email}',NOW(),'1'");
			$InsertID = intval($sysConn->Insert_ID());
			//$content1 = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/mail_count.php?mailid=' . $sysSession->school_id . '_' . $InsertID . '" style="display:none">' . $content;
			$content1 = $content;
			$mail = buildMail('', $subject, $content1, 'html', $tagline, $ret, $orgdir, $priority, false);
			$mail->add_attachment(stripslashes($html_file), $a_send_user[$i] . '.htm');
			$mail->to = $s_email;
			$mail->send();

		// 記錄到 WM_log_manager
			$msg = $sysSession->username . ' send personal course grade mail to (' . $a_send_user[$i] . ')';
			wmSysLog('2400300500', $sysSession->school_id, 0, '0', 'manager', $_SERVER['SCRIPT_FILENAME'], $msg);

			// 檢查有沒有這個使用者存在與收件者中有沒有自己
			if (($a_send_user[$i] == $sysSession->username) || (checkUsername($a_send_user[$i]) != 2))
			{
				$no_user[] = $a_send_user[$i];
			}

			//  產生 學員 成績的 html file (begin)
			$grade_file = $a_send_user[$i] . '.htm';
			$dir_file   = sysTempPath . DIRECTORY_SEPARATOR . $grade_file;
			@touch($dir_file);
			if ($fp = fopen($dir_file, 'w'))
			{
				@fwrite($fp, $html_file);
				fclose($fp);
			}
			$ret1  = cpAttach($a_send_user[$i], sysTempPath, array($grade_file, $grade_file));
			$tmp   = cpAttach($a_send_user[$i], $orgdir, $ret);
			$ret1 .= empty($tmp) ? '' : "\t" . $tmp;
			//  產生 學員 成績的 html file (end)
			// 刪除檔案
			unlink($dir_file);

			// 寄一份到收件匣備份去(收件者為平台帳號,故內部寄信)
			collect('sys_inbox', $sysSession->username, $a_send_user[$i], '', addslashes($subject), addslashes($content), $type, $tagline, $ret1, $priority, '', $note);
		}
		// for end
	}
	// if end

	//  if $_POST['class_id'] != '' begin
	if ($_POST['class_id'] != '')
	{
		$a_class_id = preg_split('/\D+/', $_POST['class_id'], -1, PREG_SPLIT_NO_EMPTY);
		//   for  begin
		for ($i = 0, $c = count($a_class_id);$i < $c; $i++)
		{
			//  WM_class_member
			$sqls  = str_replace(array('%TABLE%', '%CLASS_ID%'), array('WM_class_member', $a_class_id[$i]), $Sqls['get_many_class']);
			$sqls .= ' order by A.username asc';
			$RS    = $sysConn->Execute($sqls);

			//  if $RS->RecordCount() > 0 begin
			if ($RS && ($RS->RecordCount() > 0))
			{
				// while begin
				while (!$RS->EOF)
				{
					if (strlen($RS->fields['email'])>0)
					{
						if (preg_match(sysMailRule, strtolower($RS->fields['email'])))
						{
							$emails[] = $RS->fields['email'];
						}
						else
						{
							$error_emails[] = $RS->fields['email'];
						}
					}

					$html_file = class_grade_html($a_class_id[$i], $RS->fields['username'], $detail_title);
					dbNew('WM_mails','function_id,froms,tos,submit_time,send_status', "0,'{$sysSession->username}','" . $RS->fields['email'] . "',NOW(),'1'");
					$InsertID = intval($sysConn->Insert_ID());
					// $content1 = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/mail_count.php?mailid=' . $sysSession->school_id . '_' . $InsertID . '" style="display:none">' . $content;
					$content1 = $content;
					$mail = buildMail('', $subject, $content1, 'html', $tagline, $ret, $orgdir, $priority, false);
					$mail->add_attachment(stripslashes($html_file), $RS->fields['username'].'.htm');
					$mail->to = $RS->fields['email'];
					$mail->send();

					// 記錄到 WM_log_manager
					$msg = $sysSession->username . ' send personal course grade mail to ' .$RS->fields['username'];
					wmSysLog('2400300500',$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$msg);

					// 檢查有沒有這個使用者存在與收件者中有沒有自己
					if (($RS->fields['username'] == $sysSession->username) || (checkUsername($RS->fields['username']) != 2))
					{
						$no_user[] = $RS->fields['username'];
					}

					//  產生 學員 成績的 html file (begin)
					$grade_file = $RS->fields['username'] . '.htm';
					$dir_file = sysTempPath . DIRECTORY_SEPARATOR . $grade_file;
					@touch($dir_file);
					if ($fp = fopen($dir_file, 'w'))
					{
						@fwrite($fp, $html_file);
						fclose($fp);
					}

					$ret1  = cpAttach($RS->fields['username'], sysTempPath, array($grade_file, $grade_file));
					$tmp   = cpAttach($RS->fields['username'], $orgdir, $ret);
					$ret1 .= empty($tmp) ?  '' : "\t" . $tmp;
					//  產生 學員 成績的 html file (end)

					// 刪除檔案
					unlink($dir_file);

				// 寄一份到收件匣備份去(收件者為平台帳號,故內部寄信)
					collect('sys_inbox', $sysSession->username, $RS->fields['username'], '', addslashes($subject), addslashes($content), $type, $tagline, $ret1, $priority, '', $note);
					$RS->MoveNext();
				}
				// while end
			}
			//  if $RS->RecordCount() > 0 end
		}
		//   for ($i = 0;$i < $send_class_num;$i++) end
	}
	//  if $_POST['class_id'] != '' end

	// 寄件者的寄件匣備份
	collect('sys_sent_backup', $sysSession->username, $sysSession->username, '', $subject, $content, $type, $tagline, $ret, $priority, 'read', $note);

// **********************************************************
// 處理 寄信的 程式 (end)

						// 顯示寄信的結果 (begin)
						// 正確的 email
						$to  = parseTo(implode(',', $emails) . ',' . $_POST['to']);
						$cnt = count($to);
						for ($i = 0; $i < $cnt; $i++)
						{
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td(' nowrap="nowrap" align="center"', $i + 1);
								showXHTML_td(' nowrap="nowrap"', $to[$i]);
								$res = in_array($to[$i], $no_user) ? $MSG['user_not_exist'][$sysSession->lang] : $MSG['sended'][$sysSession->lang];
								showXHTML_td('nowrap="nowrap"', $res);
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
						// 顯示寄信的結果 (begin)

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								$target = 'view_grade.php';
								showXHTML_input('button', '', $MSG['return_view_grade'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'' . $target . '\')"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	// 判斷信件每一夾檔 有無超過 2MB (END)

	//  寄信
    showXHTML_form_B('action="send_class_grade_mail.php" method="post" enctype="multipart/form-data" style="display:none"', 'backForm');
	   	showXHTML_input('hidden', 'subject', stripslashes($_POST['subject']), '', '');
	   	showXHTML_input('hidden', 'content', stripslashes($_POST['content']), '', '');
	   	showXHTML_input('hidden', 'send_user', $_POST['send_user'], '', '');
		showXHTML_input('hidden', 'class_id', $_POST['class_id'], '', '');
		showXHTML_input('hidden', 'ticket', $_POST['ticket'], '', '');
    showXHTML_form_E();

	showXHTML_body_E('');
?>

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
	 * @version $Id: writing.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');

	// $sysSession->cur_func = '2200200100';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	
	if (detectUploadSizeExceed())
	{
		showXHTML_script('inline', 'alert("'.$MSG['upload_file_error'][$sysSession->lang].'");location.replace("write.php");');
		die();
	}
	$isCommUse = isSet($_POST['isCommUse']) && $_POST['isCommUse'] ? true : false;

	if ($sysSession->cur_func == $msgFuncID['notebook']) {
		$title     = $MSG['tabs_notebook_title'][$sysSession->lang];
		$isNB      = true;
		$folder_id = isSet($_POST['folder']) ? $_POST['folder'] : getFolderId();
		if (!ckNBFolder($folder_id)) $folder_id = 'sys_notebook';
		$inbox     = $folder_id;
	} else {
		$title = $MSG['title'][$sysSession->lang];
		$isNB  = false;
		$inbox = 'sys_inbox';
	}

	/**
	 * 記錄是回覆或轉寄
	 **/
	$forwrad = false;
	if (isset($_POST['status'])) {
		$set    = array('read', 'reply', 'forward');
		$status = preg_split('/[^\w.-]+/', $_POST['status'], -1, PREG_SPLIT_NO_EMPTY);
		$status = implode(',', array_intersect($set, $status));
		dbSet('WM_msg_message', "`status`='{$status}'", "`msg_serial`={$sysSession->msg_serial} AND `receiver`='{$sysSession->username}'");
	}

	$forward = (isset($_POST['act']) && (trim($_POST['act']) == 'forward'));

	if ($isNB && !$forward) {
		$_POST['to'] = $sysSession->username;
	}

	// 將收件者切割放到陣列中，並且過濾重複的人員
	$to     = parseTo($_POST['to']);
	$to_tmp = implode(', ', $to);
	// 檢查收件者是不是空的，若是就回到上一頁
	if (($forward || !$isNB) && (count($to_tmp) <= 0)) {
		$msg = $MSG['need_to'][$sysSession->lang];
		$js = <<< BOF
	var MSG_TO = "{$msg}";
	window.onload = function () {
		var obj = null;
		alert(MSG_TO);
		obj = document.getElementById("mainFm");
		if (obj != null) obj.submit();
	};
BOF;
		showXHTML_head_B($title);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('inline', $js);
		showXHTML_head_E('');
		showXHTML_body_B('');
			showXHTML_form_B('action="write.php" method="post" enctype="multipart/form-data"', 'mainFm');
				showXHTML_input('hidden', 'to'      , $to_tmp , '', '');
				showXHTML_input('hidden', 'subject' , $subject, '', '');
				showXHTML_input('hidden', 'content' , $content, '', '');
				showXHTML_input('hidden', 'isHTML'  , trim($_POST['isHTML']), '', '');
				showXHTML_input('hidden', 'priority', trim($_POST['priority']), '', '');
			showXHTML_form_E('');
		showXHTML_body_E('');
		die();
	}

	$note  = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>'; // 確保 PHP 不會辨識錯誤
	$note .= '<manifest><to>' . $to_tmp . '</to></manifest>';

	$priority = intval($_POST['priority']);
	// 標題不許使用 html
	$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);

	// 內文的型態
	$type = (!$_POST['isHTML']) ? 'text' : 'html';

	// 內文去除所有的不必要 html
	$content = strip_scr($_POST['content']);

	// 取出簽名檔
	$tag_serial = intval($_POST['tagline']);
	list($tagline) = dbGetStSr('WM_user_tagline', 'tagline', "serial={$tag_serial} AND username='{$sysSession->username}'", ADODB_FETCH_NUM);

	// 儲存夾檔。如果有的話。
	$orgdir   = MakeUserDir($sysSession->username);
	$ret      = trim(save_upload_file($orgdir, 0, 0));
	$list_tmp = explode("\t", $ret);

	// 轉寄時處理夾檔 (Begin)
	if (isset($_POST['attachment'])) {
		$attachs = explode("\t", trim($_POST['attachment']));
	}

	if (isset($_POST['attach'])) {
		if ((count($list_tmp) == 1) && empty($list_tmp[0])) $list_tmp = array();
		$org_attach = array();
		for ($i = 0; $i < count($_POST['attach']); $i++) {
			$key    = array_search(trim($_POST['attach'][$i]), $attachs);
			$target = uniqid('WM') . strrchr($attachs[$key - 1], '.');
			@copy($orgdir . DIRECTORY_SEPARATOR . $attachs[$key], $orgdir . DIRECTORY_SEPARATOR . $target);
			$org_attach[] = $attachs[$key - 1];
			$org_attach[] = $target;
		}
		$list_tmp = array_merge($org_attach, $list_tmp);
		$ret      = trim(implode("\t", $org_attach) . "\t" . $ret);
	}
	// 轉寄時處理夾檔 (End)

	// 建立 mime mail 並且建立複製檔案的清單 (Begin)
	// $from = "{$sysSession->realname} <{$sysSession->email}>";
	// 內容
	$mail = buildMail('', $subject, $content, $type, $tagline, $ret, $orgdir, $priority, false);

	$file_list = array();
	for ($i = 0, $j = 0; $i < count($list_tmp); $i = $i + 2, $j++) {
		$filename = $orgdir . DIRECTORY_SEPARATOR . $list_tmp[$i + 1];
		if (!file_exists($filename) || !is_file($filename)) continue;

		$message     = implode('', file($filename));
		$file_list[] = $list_tmp[$i + 1];
	}
	// 建立 mime mail 並且建立複製檔案的清單 (End)

	$no_user = array();
	foreach ($to as $username) {
		$username = trim($username);

		if ($forward || !$isNB) {
			// 檢查是不是 email (這個檢查很簡單，看需不需要做仔細一點的檢查)
			if (preg_match(sysMailRule, $username)) {
				// 送信
				$mail->to = $username;
				$mail->send();
				continue;
			}

			// 檢查有沒有這個使用者存在與收件者中有沒有自己
			if (($username == $sysSession->username)
				|| (checkUsername($username) != 2))
			{
				$no_user[] = $username;
				continue;
			}
		}

		$serial = intval($_POST['serial']);
		if ($forward) {
				collect($inbox, $sysSession->username, $username, '', $subject, $content, $type, $tagline, $ret, $priority, '', $note);
		} else {
			if ($isNB && !empty($serial)) {
                $saveTime = date('Y-m-d H:i:s');
				// 儲存編修後的資料
				dbSet('WM_msg_message',
					  "`priority`='{$priority}', `subject`='{$subject}', `content`='{$content}', " .
					  "`attachment`='{$ret}', `content_type`='{$type}', " .
                      "`submit_time`='{$saveTime}', `receive_time`='{$saveTime}'",
					  "`msg_serial`={$serial} AND `receiver`='{$sysSession->username}'"
				);
                // 處理雲端筆記的log - begin
                $logTime = strtotime($saveTime);
                dbNew('APP_note_action_history',
                    '`username`, `log_time`, `action`, `folder_id`, `msg_serial`, `from`',
                    "'{$sysSession->username}', {$logTime}, 'M', '', {$serial}, 'server'");
                // 處理雲端筆記的log - end
			} else {
				collect($inbox, $sysSession->username, $username, '', $subject, $content, $type, $tagline, $ret, $priority, '', $note);
			}
		}

		// 存放附檔
		if (!$isNB || $forward) {
			$userdir = MakeUserDir($username);
			for ($i = 0; $i < count($file_list); $i++) {
				@copy("{$orgdir}/{$file_list[$i]}", "$userdir/{$file_list[$i]}");
			}
		}
	}

	if (!$isNB || $forward) {
		// 寄件匣備份
		collect('sys_sent_backup', $sysSession->username, $sysSession->username, '', $subject, $content, $type, $tagline, $ret, $priority, 'read', $note);
	}

	showXHTML_head_B($title);
    showXHTML_head_E();
	$msg = $sysConn->Affected_Rows() > 0 ? $MSG['msg_write_success'][$sysSession->lang] : $MSG['msg_write_fail'][$sysSession->lang];
	if (in_array($sysSession->username,$to)) {
		
		list($reserved) = dbGetStSr('WM_user_account', '`msg_reserved`', "`username`='$sysSession->username'", ADODB_FETCH_NUM);
		if ($reserved) {
			$name_ary = nowPos('sys_sent_backup');
			$index = count($name_ary) - 1;
			$folder_name = $name_ary[$index];

			$msg = $MSG['no_self1'][$sysSession->lang] . $folder_name . $MSG['no_self2'][$sysSession->lang];
		} else {
		    $msg = $res = $MSG['no_self'][$sysSession->lang];	
		}
	}
	showXHTML_script('inline', 'alert("'.$msg.'");location.replace("index.php");');
	die();

	// 介面輸出
	// 檢查收件者是不是空的，若是就回到上一頁

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$tabs = $MSG['tabs_send_result'][$sysSession->lang];
					$ary   = array();
					$ary[] = array($tabs, 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top"');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						if ($isNB && !$forward) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('colspan="3" align="center"', $msg);
							showXHTML_tr_E('');
						} else {
							showXHTML_tr_B('class="cssTrHead"');
								showXHTML_td('width="40" nowrap="nowrap" align="center"', $MSG['number'][$sysSession->lang]);
								showXHTML_td('width="80" nowrap="nowrap" align="center"', $MSG['to'][$sysSession->lang]);
								showXHTML_td('width="250" nowrap="nowrap" align="center"', $MSG['result'][$sysSession->lang]);
							showXHTML_tr_E('');

							for ($i = 0; $i < count($to); $i++) {
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('width="40" nowrap="nowrap" align="center"', $i + 1);
									showXHTML_td('width="80" nowrap="nowrap"', $to[$i]);
									$res = in_array($to[$i], $no_user) ? $MSG['user_not_exist'][$sysSession->lang] : $MSG['sended'][$sysSession->lang];
									if ($to[$i] == $sysSession->username) {
										// 取出使用者設定的信件匣備份的名稱
										list($reserved) = dbGetStSr('WM_user_account', '`msg_reserved`', "`username`='$sysSession->username'", ADODB_FETCH_NUM);
										if ($reserved) {
											$name_ary = nowPos('sys_sent_backup');
											$index = count($name_ary) - 1;
											$folder_name = $name_ary[$index];

											$res = $MSG['no_self1'][$sysSession->lang] . $folder_name . $MSG['no_self2'][$sysSession->lang];
										} else {
											$res = $MSG['no_self'][$sysSession->lang];
										}
									}
									showXHTML_td('nowrap="nowrap"', $res);
								showXHTML_tr_E('');
							}

						}

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								$msg = $isNB ? $MSG['goto_nodebook'][$sysSession->lang] : $MSG['goto_msg_center'][$sysSession->lang];
								$target = $isNB ? 'notebook.php' : 'index.php';
								showXHTML_input('button', '', $msg, '', 'class="cssBtn" onclick="window.location.replace(\'' . $target . '\')"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');
?>

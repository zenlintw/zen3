<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/18                                                                      *
	*		work for  : 寄帳號 密碼 給學員 且 寄給管理者做備份                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1
	*       @version $Id:                                                                             *
	*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/send_register_mail.php');
	require_once(sysDocumentRoot . '/message/collect.php');
   	require_once(sysDocumentRoot . '/lib/acl_api.php');
   	require_once(sysDocumentRoot . '/lib/file_api.php');

	$sysSession->cur_func = '500100100';
	$sysSession->restore();
	if (!aclVerifyPermission(500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 郵件的原始信件檔案
	$target = sysDocumentRoot . "/base/{$sysSession->school_id}/add_account.mail";

	// 郵件的原始信件檔案  的夾檔路徑
	$att_file_path = sysDocumentRoot . "/base/{$sysSession->school_id}/attach/add_account";

	$ticket = md5($sysSession->ticket . 'sendMail' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
	if (trim($_POST['ticket']) != $ticket) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['illege_access'][$sysSession->lang]);
	}

	// ========== 1.先讀取檔案中的原始信件檔案(每封信件共用資訊) ==========
	if (file_exists($target))
	{
		// 先讀取 郵件的原始信件檔案 資料
		$fd = fopen($target, "r");

		// 讀取標題
		$temp = fgets($fd, 1024);
		$tmp_subject = $temp;

		/*
			讀取內容
			$tmp_body 為尚未置換特殊符號的本文
		**/
		while (!feof ($fd)) {
			$tmp_body .= fgets($fd, 4096);
		}
		fclose($fd);
	}
	else
	{
		$tmp_subject = $MSG['add_account_subject'][$sysSession->lang];
		$tmp_body    = $MSG['add_account_body'][$sysSession->lang];
	}

	// ========== 2.從資料庫中取出必要的資訊(每封信件共用資訊) ==========
	$school_name       = $sysSession->school_name;			// 學校名稱
	$school_host       = $_SERVER['HTTP_HOST'];				// 學校網址
	list($school_mail) = dbGetStSr('WM_school','school_mail',"school_id='{$sysSession->school_id}' and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

	// ========== 3.取出信件夾檔名稱(每封信件共用資訊) ==========
	// 取得所有附加檔案名稱
	if (is_dir($att_file_path)){
		$att_files = getAllFile($att_file_path);
	}

	// ※※※※※※ 進入信件處理過程 ※※※※※※

	$user1 = explode("\t", $_POST['send_data']);

	// 1.讀取標題
	$subject = htmlspecialchars(trim($tmp_subject), ENT_QUOTES);

	// 寄給管理者標題
	$subject_for_manager = $MSG['add_account_subject_for_manager'][$sysSession->lang];

	// 寄件者
	if (empty($school_mail))
		$school_mail = 'webmaster@' . $school_host;
	$from = mailEncFrom($school_name, $school_mail);

	// 有勾選 寄信給管理者備存 (begin)
	if ($_POST['backup'] == 1)
	{
		// 寄到管理者的信箱
                $_POST['mail_txt'] = urldecode(base64_decode($_POST['mail_txt']));

		// 每次進入都必須重新宣告一個新的 mail 類別
		$mail = buildMail('', $subject_for_manager, '&nbsp;', 'html', '', '', '', '', false);

		// 2.寄件者
		$mail->from = $from;
		$mail->to   = $sysSession->email;
		$mail->add_attachment(stripslashes($_POST['mail_txt']), 'register_result.htm');
		$mail->send();

		//  產生 新增帳號結果頁面的 html file (begin)
		$userdir     = MakeUserDir($sysSession->username);
		$result_file = 'register_result.htm';
		@touch(sysTempPath . DIRECTORY_SEPARATOR . $result_file);

		if ($fp = fopen(sysTempPath . DIRECTORY_SEPARATOR . $result_file, 'w'))
		{
			@fwrite($fp, stripslashes($_POST['mail_txt']));
			fclose($fp);
		}
		$ret1 = cpAttach($sysSession->username, sysTempPath, array($result_file, $result_file));
		unlink(sysTempPath . DIRECTORY_SEPARATOR . $result_file);
		//  產生 新增帳號結果頁面的 html file (end)

		//  夾檔 (begin)
		if (is_array($att_files) && count($att_files))
		{
			// 存放附檔(將寄件者的夾檔複製一份到$ursername的目錄去)
			$files = array();
				// 將 $att_files 轉成 cpAttach() 可接受的參數
			foreach ($att_files as $val)
			{
				$files[] = $val;
				$files[] = $val;
			}
			$temp2 = cpAttach($sysSession->username, $att_file_path . DIRECTORY_SEPARATOR, $files);
			$ret1 .= "\t" . $temp2;
		}
		//  夾檔 (end)

		// 寄一份到收件匣備份去(收件者為平台帳號,故內部寄信)
		collect('sys_inbox', $sysSession->username, $sysSession->username, '', $subject, $content, $type, $tagline, $ret1, $priority, '', $note);
	}
	// 有勾選 寄信給管理者備存 (end)

	$js = <<< BOF

	function goList() {
		window.location.replace("/academic/teacher/teacher_list.php");
	}

	// Chrome
	function backStudAccount(no) {
		window.location.replace("stud_account.php?msgtp=" + no);
	}

BOF;
	// 開始呈現 HTML
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();

		showXHTML_table_B(' border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary[] = array($MSG['title'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_input('hidden', 'ticket', $ticket, '', '');
					showXHTML_input('hidden', 'username', $_POST['username'], '', '');
					showXHTML_input('hidden', 'passwd', $passwd, '', '');
					showXHTML_table_B('id ="mainTable" width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						$col = 'cssTrHead';

						showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td('', $MSG['user_account'][$sysSession->lang]);
							showXHTML_td('', $MSG['password'][$sysSession->lang]);
							showXHTML_td('', 'E-mail');
							showXHTML_td('', $MSG['status2'][$sysSession->lang]);
						showXHTML_tr_E();

						//  for begin
						for ($i = 0; $i < count($user1); $i++)
						{
							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
							showXHTML_tr_B('class="' . $col . '"');

							$user2 = explode(",", $user1[$i]);
							if ($user2 === false) continue;

							$uname   = $user2[0];
							$upasswd = $user2[1];

							showXHTML_td('', $uname);
							showXHTML_td('', $upasswd);

							// 每次進入都必須重新宣告一個新的 mail 類別
							$mail_body = str_replace(
								array('%SCHOOL_NAME%', '%SCHOOL_HOST%', '%USERNAME%', '%PASSWORD%'),
								array($school_name   , $school_host   , $uname      , $upasswd),
								$tmp_body
							);

							$mail = buildMail('', $subject, $mail_body, 'html', '', '', '', '', false);

							// 2.寄件者
							$mail->from	= $from;
							$uemail = strtolower($_POST['email'][$uname]);
							showXHTML_td('', $uemail);

							// 3.收件者
							if (empty($uemail) || !preg_match(sysMailRule, $uemail))
							{
								// 郵寄狀態
								showXHTML_td('', $MSG['title23'][$sysSession->lang]);
							}
							else
							{
								$mail->to = $uemail;
								// ========== 處理附加檔案 ==========
								$att_count = count($att_files);
								for ($j = 0; $j < $att_count; $j++)
								{
									$data = file_get_contents($att_file_path . DIRECTORY_SEPARATOR . $att_files[$j]);
									// 5.信件夾檔
									$mail->add_attachment($data, $att_files[$j]);
								}
								$mail->send();
								// 郵寄狀態
								showXHTML_td('', $MSG['title22'][$sysSession->lang]);
								//  更改 學員的email
								dbSet('WM_user_account', "email='" . $_POST['email'][$uname] . "'", "username='" . $uname . "'");
								wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], '寄帳號密碼給學員 username=' . $uname . ' and email=' . $uemail);
							}
							showXHTML_tr_E();
						}


						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
						showXHTML_tr_B('id="btn" class="' . $col . '"');
							showXHTML_td_B('colspan="4" align="center"');
								if ($_POST['msgtp'] == 1)
								{
									$go_msg = $MSG['return_register'][$sysSession->lang];
								}
								else if ($_POST['msgtp'] == 2)
								{
									$go_msg = $MSG['return_register2'][$sysSession->lang];
								}
								// Chrome
								// showXHTML_input('button', '', $go_msg, '', 'onclick="window.location.replace(\'stud_account.php?msgtp='. $_POST['msgtp'] .'\');" class="cssBtn"');
								showXHTML_input('button', '', $go_msg, '', 'onclick="backStudAccount('.$_POST['msgtp'].');" class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();

					showXHTML_table_E();

				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();

	showXHTML_body_E();
?>

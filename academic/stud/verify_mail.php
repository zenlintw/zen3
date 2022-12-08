<?php
	/**
	 * 寄發審核帳號核可通知信件
	 * @version $Id: verify_mail.php,v 1.1 2010/02/24 02:38:45 saly Exp $:
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/editor.php');

	if (!defined('MAIL_TYPE')) {
		define('MAIL_TYPE', 'VERIFY_MAIL');
		$sysSession->cur_func = '500100100';
		$default_subject = $MSG['verify_account_subject'][$sysSession->lang];
		$default_content = $MSG['verify_account_body'][$sysSession->lang];
		$target          = sysDocumentRoot . "/base/$sysSession->school_id/verify_account_" . $sysSession->lang . ".mail";
		$save_path       = sysDocumentRoot . "/base/$sysSession->school_id/attach/verify_account";
		$arry[]          = array($MSG['edit_allow'][$sysSession->lang], 'addTable1');
	}

	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0"');
		// 顯示 tab 的標記
		showXHTML_tr_B();
			showXHTML_td_B('valign="top"');
				// 先讀取檔案中的原始信件檔案
				if (file_exists($target))
				{
					$fd      = fopen($target, "r");
					$subject = fgets($fd, 1024);			// 讀取標題
				}
				else
				{
					$subject = $default_subject;
				}

				showXHTML_input('hidden', 'mode', 'edit', '', '');		// 決定目前此頁的動作模式
				showXHTML_input('hidden', 'file_name', '', '', '');		// 刪除檔案時之檔名
				showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="addTable1" style="display:block" class="cssTable"');
					// 信件標題
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td(' width="12%" nowrap align="right"', $MSG['title'][$sysSession->lang]);
						showXHTML_td_B();
							showXHTML_input('text', 'title', $subject, '', 'size="70" maxlength="100" class="cssInput"');
						showXHTML_td_E();
						showXHTML_td(' width="30%"', $MSG['message1'][$sysSession->lang]);
					showXHTML_tr_E();
					// 內容
					showXHTML_tr_B('class="cssTrOdd"');
						showXHTML_td(' align="right" nowrap',$MSG['content'][$sysSession->lang]);
						showXHTML_td_B('nowrap="nowrap"');
							if (file_exists($target))
							{
								while (!feof($fd)) {
									$content .= fgets($fd, 4096);
								}
							}
							else
							{
								$content = $default_content;
							}
							$oEditor = new wmEditor;
							$oEditor->setValue($content);
							$oEditor->generate('content');
						showXHTML_td_E();
						showXHTML_td('valign="top"',$MSG['message3'][$sysSession->lang]);
					showXHTML_tr_E();
					// 附檔
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td('align="right" align="center"',$MSG['att_file'][$sysSession->lang]);
						showXHTML_td_B(' id="att_file"');

							// 先列出已經有的附加檔案
							if (is_dir($save_path)){
								$file_array  = getAllFile($save_path);
								$file_count  = count($file_array);
								for ($i = 0; $i < $file_count; $i++) {
									echo "<span>";
										showXHTML_input('text', "exist_$i", $file_array[$i], '', 'size="29" class="cssInput" disabled');
										showXHTML_input('button', '', $MSG['del_att_file'][$sysSession->lang], '', 'class="cssBtn" onclick="delFile(document.addFm.exist_'.$i.'.value)"');
									echo "<br></span>";
								}
							}
							// 再列出需要附加的檔案
							echo "<span><br>";
							showXHTML_input('file', $att_file, 'uploads', '', 'class="cssInput"');
							echo "<br></span>";
						showXHTML_td_E();
						// 單一上傳檔案size
						$min_size = '<span style="color: red; font-weight: bold">' . ini_get('upload_max_filesize') . '</span>';
						// 總上傳檔案size
						$max_size = '<span style="color: red; font-weight: bold">' . ini_get('post_max_size') . '</span>';

						$file_msg = str_replace(array('%MIN_SIZE%', '%MAX_SIZE%'), array($min_size, $max_size), $MSG['message2'][$sysSession->lang]);
						showXHTML_td('valign="top"', $file_msg);
					showXHTML_tr_E();

					// 按鍵區
					showXHTML_tr_B('class="cssTrOdd"');
						showXHTML_td_B(' colspan="3" align="center"');
							showXHTML_input('button', '', $MSG['sure'][$sysSession->lang], '', 'class="cssBtn" align="right" valign="middle" nowrap  onclick="chk_this();"');
							//showXHTML_input('reset', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" align="right" valign="middle" nowrap ');
							// 更多附檔
							showXHTML_input('button', '', $MSG['more'][$sysSession->lang], '', 'class="cssBtn" onclick="add_att()"');
							showXHTML_input('button', '', $MSG['return_verify'][$sysSession->lang], '', 'class="cssBtn" align="right" valign="middle" nowrap onclick="window.location.replace(\'stud_authorisation.php\');"');
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
			showXHTML_td_E();
		showXHTML_tr_E();
	showXHTML_table_E();
?>
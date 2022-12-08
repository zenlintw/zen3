<?php
	/**
	 * 線上傳訊-訊息清除與備份
	 *
	 * @since   2005/02/22
	 * @author  ShenTing Lin
	 * @version $Id: msg_backup.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lang/msg_online.php');

	/**
	 * 線上傳訊備份
	 * @param boolean $delData : 是否要刪除資料
	 * @return void
	 **/
	function msg_backup($delData=TRUE) {
		global $sysSession, $MSG;

		ob_start();
			$CSS = <<< BOF
		body { scrollbar-arrow-color: #9C98BB; scrollbar-3dlight-color: #9C98BB; scrollbar-highlight-color: #FFFFFF; scrollbar-face-color: #EBEBEB; scrollbar-shadow-color: #9C98BB; scrollbar-track-color: #F0F0F0; scrollbar-darkshadow-color: #FFFFFF; }
		.cssTable { background-color: #E3E9F2; border: 1px solid #5176D2; }
		.cssTrHelp { font-size: 12px; line-height: 16px; text-decoration: none; letter-spacing: 2px; color: #000000; background-color: #C7D8FA; font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif"; }
		.cssTrHead { font-size: 12px; line-height: 16px; text-decoration: none; letter-spacing: 2px; color: #000000; background-color: #C7D8FA; font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif"; }
		.cssTrEvn { font-size: 12px; line-height: 16px; text-decoration: none; letter-spacing: 2px; color: #000000; background-color: #FFFFFF; font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif"; }
		.cssTrOdd { font-size: 12px; line-height: 16px; text-decoration: none; letter-spacing: 2px; color: #000000; background-color: #ECF1F7; font-family: "Tahoma", "PMingliu", "MingLiU", "Times New Roman", "Times", "serif"; }
BOF;

			$RS = dbGetStMr('WM_im_message', '*', "`username`='{$sysSession->username}' AND `saw`='Y' order by `send_time`, `serial`, `sorder`, `sender`", ADODB_FETCH_ASSOC);
			showXHTML_head_B($MSG['title_msg_backup'][$sysSession->lang]);
			showXHTML_css('inline', $CSS);
			showXHTML_head_E();
			showXHTML_body_B();
				showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="tabsMsgList" class="cssTable"');
					showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td('colspan="5"', $MSG['title_msg_backup'][$sysSession->lang]);
					showXHTML_tr_E();
					showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td('align="center"', $MSG['th_send_time'][$sysSession->lang]);
						showXHTML_td('align="center"', $MSG['th_status'][$sysSession->lang]);
						showXHTML_td('align="center"', $MSG['th_sender'][$sysSession->lang]);
						showXHTML_td('align="center"', $MSG['th_reciver'][$sysSession->lang]);
						showXHTML_td('align="center"', $MSG['th_message'][$sysSession->lang]);
					showXHTML_tr_E('');

					$message = '';
					$cnt = 0;
					$user = array();
					if ($RS && ($RS->RecordCount() > 0)) {
						while (!$RS->EOF) {
							if ($RS->fields['sorder'] == 0) {
								if ($cnt != 0) {
										if ($ctype == 'text') {
											$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
											$replace = array("<a href=\"\\1\" target=\"_blank\" class=\"cssAnchor\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
											$message = nl2br(preg_replace($patterns, $replace, htmlspecialchars($message, ENT_QUOTES)));
										}
										showXHTML_td_B('');
											if (strlen($message) > 254) {
												echo '<div style="width:400px; height:110px; overflow:auto">' . $message . '</div>';
											} else {
												echo $message;
											}
										showXHTML_td_E();
									showXHTML_tr_E('');
								}

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								$message = '';
								$cnt++;
								$ctype = $RS->fields['ctype'];
								$sender = $RS->fields['sender'];
								showXHTML_tr_B($col);
									// 傳訊時間
									showXHTML_td('nowrap="nowrap"', str_replace(' ', '<br />', $RS->fields['send_time']));
									// 狀態
									if ($RS->fields['talk'] != '') {
										$talk = trim($RS->fields['talk']);
										switch ($talk) {
											case 'Talk'  : $status = $MSG['msg_talk_call'][$sysSession->lang]; break;
											case 'Accept': $status = $MSG['msg_talk_accept'][$sysSession->lang]; break;
											case 'Refuse': $status = $MSG['msg_talk_refuse'][$sysSession->lang]; break;
											case 'Alert' : $status = $MSG['msg_system_alert'][$sysSession->lang]; break;
											default:
												$status = '';
										}
									} else {
										$status = ($sysSession->username == $RS->fields['sender']) ? $MSG['msg_send'][$sysSession->lang] : $MSG['msg_receive'][$sysSession->lang];
									}
									showXHTML_td('nowrap="nowrap"', $status);
									// 傳訊
									showXHTML_td('', $sender . '<br />(' . $RS->fields['sender_name'] . ')');
									// 接收
									$reciver = $RS->fields['reciver'];
									if (!isset($user[$reciver])) {
										list($firstN, $lastN) = dbGetStSr('WM_user_account', '`first_name`, `last_name`', "`username`='{$reciver}'", ADODB_FETCH_NUM);
										$user[$reciver] = checkRealname($firstN, $lastN);
									}
									showXHTML_td('', $reciver . '<br />(' . $user[$reciver] . ')');
							}
							$message .= $RS->fields['message'];
							$RS->MoveNext();
						}

						if ($ctype == 'text') {
							$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
							$replace = array("<a href=\"\\1\" target=\"_blank\" class=\"cssAnchor\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
							$message = nl2br(preg_replace($patterns, $replace, htmlspecialchars($message, ENT_QUOTES)));
						}
						showXHTML_td_B();
							if (strlen($message) > 254) {
								echo '<div style="width:400px; height:110px; overflow:auto">' . $message . '</div>';
							} else {
								echo $message;
							}
						showXHTML_td_E();
					} else {
						// 沒有任何訊息
						return '';
					}
				showXHTML_table_E();
			showXHTML_body_E();
			$content = ob_get_contents();
		ob_end_clean();
		$dir = MakeUserDir($sysSession->username);
		$name = uniqid('IM_') . '.htm';
		$filename = $dir . DIRECTORY_SEPARATOR . $name;
		touch($filename);
		$fp = fopen($filename, 'w');
		fputs($fp, $content);
		fclose($fp);
		$ret = $MSG['msg_filename'][$sysSession->lang] . "\t" . $name;
		// 儲存到訊息中心
		collect('sys_online_msg_backup', $sysSession->username, $sysSession->username, '', $MSG['msg_im_log'][$sysSession->lang], $MSG['msg_im_log_attachment'][$sysSession->lang], 'text', '', $ret, 0);
		// 清除資料
		if ($delData) dbDel('WM_im_message', "`username`='{$sysSession->username}' AND `saw`='Y'");
	}
?>

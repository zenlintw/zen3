<?php
	/**
	 * 登出聊天室
	 *
	 * @since   2003/12/02
	 * @author  ShenTing Lin
	 * @version $Id: chat_logout.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_chat_records.php');
	require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
	$sysSession->cur_func = '2000200100';
	$sysSession->restore();
	if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/**
	 * 將資料寫到檔案
	 * @param string $filename : 檔名
	 * @param string $dir      : 路徑
	 * @param string $content  : 內容
	 **/
	function dump2file($filename, $dir, $content) {
		$fullname = $dir . DIRECTORY_SEPARATOR . $filename;
		@touch($fullname);
		$fp  = @fopen($fullname, 'w');
		$len = @fwrite($fp, $content);
		@fclose($fp);
	}

	/**
	 * 分析討論內容，並轉為筆記本的夾檔
	 * @param integer $begin : 從第幾行開始
	 * @return string : 夾檔名稱
	 **/
	function parseChatCont($begin=0, $username='', $rid='') {
		global $sysSession, $MSG;

                $room_id = (!empty($rid))? $rid : $sysSession->room_id;
		if (empty($username)) $username = $sysSession->username;
		$begin = intval($begin);
		if(!empty($rid))
			$cont  = getChatCont($begin,0,$rid);
		else
			$cont  = getChatCont($begin);
		list($owner, $caption) = dbGetStSr('WM_chat_setting', '`owner`, `title`', "`rid`='{$room_id}'", ADODB_FETCH_NUM);
		$owner = explode('_', $owner);
		$lang  = getCaption($caption);
		$title = $lang[$sysSession->lang];
		if (strlen($owner[0]) == 8 && !empty($sysSession->course_name)) $title = $sysSession->course_name . '_' . $title;
		if (!empty($sysSession->school_name)) $title = $sysSession->school_name . '_' . $title;

		// 原始資料檔 (Begin)
		$path  = getRecFullPath();
		$fname = basename($path);
		$bname = basename($path, '.csv');
		$dname = dirname($path);
		//將原始資料dump => csv
		dump2file($fname, $dname, utf8_to_excel_unicode($cont));
		// 原始資料檔 (End)


		$tplMain = file_get_contents(sysDocumentRoot . '/learn/chat/tmp_main.htm');
		$tplChat = file_get_contents(sysDocumentRoot . '/learn/chat/tmp_chat.htm');
		$tplDeal = file_get_contents(sysDocumentRoot . '/learn/chat/tmp_detail.htm');
		$tplAttn = file_get_contents(sysDocumentRoot . '/learn/chat/tmp_attendance.htm');

		preg_match('/%LOOP_BEGIN%(.+)%LOOP_END%/sU', $tplChat, $result);
		$tmpChat = $result[1];
		preg_match('/%LOOP_BEGIN%(.+)%LOOP_END%/sU', $tplDeal, $result);
		$tmpDeal = $result[1];
		preg_match('/%LOOP_BEGIN%(.+)%LOOP_END%/sU', $tplAttn, $result);
		$tmpAttn = $result[1];

		$tagMain = array('%TITLE%', '%CAPTION%', '%ALT_ROOM_NAME%', '%ROOM_NAME%', '%ROWS%');
		$tagChat = array('%COL%', '%VALUE1%', '%VALUE2%', '%VALUE3%', '%VALUE4%', '%VALUE5%', '%VALUE6%', '%VALUE7%');
		$tagDeal = array('%COL%', '%VALUE1%', '%VALUE2%', '%VALUE3%', '%VALUE4%', '%VALUE5%');
		$tagAttn = array('%COL%', '%VALUE1%', '%VALUE2%', '%VALUE3%', '%VALUE4%');

		$thdChat = array('%HEAD1%', '%HEAD2%', '%HEAD3%', '%HEAD4%', '%HEAD5%', '%HEAD6%', '%HEAD7%');
		$thdDetl = array('%HEAD1%', '%HEAD2%', '%HEAD3%', '%HEAD4%', '%HEAD5%');
		$thdAttn = array('%HEAD1%', '%HEAD2%', '%HEAD3%', '%HEAD4%');

		$thdCtCt = array(
			$MSG['chat_th_sender'][$sysSession->lang],
			$MSG['chat_th_sender_name'][$sysSession->lang],
			$MSG['chat_th_reciver'][$sysSession->lang],
			$MSG['chat_th_reciver_name'][$sysSession->lang],
			$MSG['chat_th_send_date'][$sysSession->lang],
			$MSG['chat_th_send_time'][$sysSession->lang],
			$MSG['chat_th_content'][$sysSession->lang]
		);
		$thdDlCt = array(
			$MSG['chat_th_username'][$sysSession->lang],
			$MSG['chat_th_realname'][$sysSession->lang],
			$MSG['chat_th_date'][$sysSession->lang],
			$MSG['chat_th_time'][$sysSession->lang],
			$MSG['chat_th_inout_msg'][$sysSession->lang]
		);
		$thdAtCt = array(
			$MSG['chat_th_username'][$sysSession->lang],
			$MSG['chat_th_realname'][$sysSession->lang],
			$MSG['chat_th_time_in'][$sysSession->lang],
			$MSG['chat_th_time_out'][$sysSession->lang]
		);

		$attend  = array();
		$layChat = '';
		$layDeal = '';
		$layAttn = '';
		$col0    = 'cssRowOff';
		$col1    = 'cssRowOff';
		$ary     = explode("\n", $cont);
		foreach($ary as $val) {
			if (empty($val)) continue;
			$line = explode("\t", $val);
			if (empty($attend[$line[1]][0])) $attend[$line[1]][0] = $line[2];
			switch (intval($line[0])) {
				case -2 :   // 被主持人踢出去
					$col0     = ($col0 == 'cssRowOff') ? 'cssRowOn' : 'cssRowOff';
					$time     = explode(' ', $line[5]);
					$val      = array($col0, $line[1], $line[2], $time[0], $time[1], $MSG['chat_log_out_host'][$sysSession->lang]);
					$layDeal .= str_replace($tagDeal, $val, $tmpDeal);
					$attend[$line[1]][2] = $line[5];
					break;
				case -1 :   // 被系統踢出去
					$col0     = ($col0 == 'cssRowOff') ? 'cssRowOn' : 'cssRowOff';
					$time     = explode(' ', $line[5]);
					$val      = array($col0, $line[1], $line[2], $time[0], $time[1], $MSG['chat_log_out_sys'][$sysSession->lang]);
					$layDeal .= str_replace($tagDeal, $val, $tmpDeal);
					$attend[$line[1]][2] = $line[5];
					break;
				case 0  :   // 自己離開
					$col0     = ($col0 == 'cssRowOff') ? 'cssRowOn' : 'cssRowOff';
					$time     = explode(' ', $line[5]);
					$val      = array($col0, $line[1], $line[2], $time[0], $time[1], $MSG['chat_log_out_msg'][$sysSession->lang]);
					$layDeal .= str_replace($tagDeal, $val, $tmpDeal);
					$attend[$line[1]][2] = $line[5];
					break;
				case 1  :   // 登入
					$col0     = ($col0 == 'cssRowOff') ? 'cssRowOn' : 'cssRowOff';
					$time     = explode(' ', $line[5]);
					$val      = array($col0, $line[1], $line[2], $time[0], $time[1], $MSG['chat_log_in_msg'][$sysSession->lang]);
					$layDeal .= str_replace($tagDeal, $val, $tmpDeal);
					if (empty($attend[$line[1]][1])) $attend[$line[1]][1] = $line[5];
					break;
				case 2 :
					// 狀態 發話 名字 對象 名字 時間 語氣 顏色 內容
					$col1     = ($col1 == 'cssRowOff') ? 'cssRowOn' : 'cssRowOff';
					$time     = explode(' ', $line[5]);
					$val      = array($col1, $line[1], $line[2], $line[3], $line[4], $time[0], $time[1], $line[8]);
					$layChat .= str_replace($tagChat, $val, $tmpChat);
					break;
				default:
			}
		}

		$col0    = 'cssRowOff';
		foreach ($attend as $key => $value) {
			$col0     = ($col0 == 'cssRowOff') ? 'cssRowOn' : 'cssRowOff';
			$val      = array($col0, $key, $value[0], $value[1], $value[2]);
			$layAttn .= str_replace($tagAttn, $val, $tmpAttn);
		}

		$cntChat = preg_replace('/%LOOP_BEGIN%(.+)%LOOP_END%/sU', $layChat, $tplChat);   // 建立內文
		$cntDeal = preg_replace('/%LOOP_BEGIN%(.+)%LOOP_END%/sU', $layDeal, $tplDeal);   // 建立內文
		$cntAttn = preg_replace('/%LOOP_BEGIN%(.+)%LOOP_END%/sU', $layAttn, $tplAttn);   // 建立內文
		$cntChat = str_replace($thdChat, $thdCtCt, $cntChat);   // 轉換 Head
		$cntDeal = str_replace($thdDetl, $thdDlCt, $cntDeal);   // 轉換 Head
		$cntAttn = str_replace($thdAttn, $thdAtCt, $cntAttn);   // 轉換 Head


		$cntMain = array(
			$MSG['chat_log_chat'][$sysSession->lang],
			$MSG['chat_log_chat'][$sysSession->lang],
			$MSG['chat_room_name'][$sysSession->lang],
			$title,	$cntChat
		);
		$cntChat = str_replace($tagMain, $cntMain, $tplMain);

		$cntMain = array(
			$MSG['chat_log_detail'][$sysSession->lang],
			$MSG['chat_log_detail'][$sysSession->lang],
			$MSG['chat_room_name'][$sysSession->lang],
			$title,	$cntDeal
		);
		$cntDeal = str_replace($tagMain, $cntMain, $tplMain);

		$cntMain = array(
			$MSG['chat_log_attendance'][$sysSession->lang],
			$MSG['chat_log_attendance'][$sysSession->lang],
			$MSG['chat_room_name'][$sysSession->lang],
			$title,	$cntAttn
		);
		$cntAttn = str_replace($tagMain, $cntMain, $tplMain);
		// 對話記錄

		$chname = "chat_{$sysSession->room_id}_{$sysSession->username}.htm";
		$dlname = "detail_{$sysSession->room_id}_{$sysSession->username}.htm";
		$atname = "attendance_{$sysSession->room_id}_{$sysSession->username}.htm";


		dump2file($chname, $dname, $cntChat);
		dump2file($dlname, $dname, $cntDeal);
		dump2file($atname, $dname, $cntAttn);

		// 出席統計
		// 進出記錄
		$files = array(
			$MSG['chat_log_chat'][$sysSession->lang] . '.htm',
			$chname,
			$MSG['chat_log_detail'][$sysSession->lang] . '.htm',
			$dlname,
			$MSG['chat_log_attendance'][$sysSession->lang] . '.htm',
			$atname,
			$MSG['chat_log_org'][$sysSession->lang] . '.csv',
			$fname
		);
		
		$ret   = cpAttach($username, $dname, $files);
		// 移除暫存檔
		@unlink($dname . DIRECTORY_SEPARATOR . $chname);
		@unlink($dname . DIRECTORY_SEPARATOR . $dlname);
		@unlink($dname . DIRECTORY_SEPARATOR . $atname);
		return $ret;
	}

#==== main =================
	// echo $GLOBALS['HTTP_RAW_POST_DATA'];
	// die();
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
		    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// 檢查 Ticket
		/*
		$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->ticket . $sysSession->school_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest>Access Fail.</manifest>';
			exit;
		}

		// 重新建立 Ticket
		setTicket();
		$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->ticket . $sysSession->school_id);
		*/

		$exit = getNodeValue($dom, 'exit');

		// 透過討論室管理結束會議 - Begin by Small 2011/11/16
		$cancel = getNodeValue($dom, 'cancel');
		$rid = getNodeValue($dom, 'rid');
		// 透過討論室管理結束會議 - End

		// 移除聊天的 session
		if(!$cancel)
		{
			// 從討論室登出
			$rid = $sysSession->room_id;
			dbSet('WM_session', "`room_id`=''", "`idx`='{$_COOKIE['idx']}'");
			dbDel('WM_chat_session', "`rid`='{$rid}' AND `username`='{$sysSession->username}'");
			if ($sysConn->Affected_Rows() > 0) {
				// 寫入登出訊息
				setChatCont('', 0, 0);
			}

			// 處理主持人
			if ((strpos($rid, 'online') === FALSE)) {
				list($cnt) = dbGetStSr('WM_chat_session', 'count(*)', "`host`='Y'", ADODB_FETCH_NUM);
				if (intval($cnt) == 0) {
					list($user) = dbGetStSr('WM_chat_session', '`username`', "`rid`='{$rid}' order by `login`", ADODB_FETCH_NUM);
					dbSet('WM_chat_session', "`host`='Y', `voice`='allow'", "`rid`='{$rid}' AND `username`='{$user}'");
				}
			}
		}
		else
		{
			// 從討論室管理結束會議，清空session
			dbDel('WM_chat_session', "`rid`='{$rid}'");
		}

		// 處理聊天內容
			// 一般登出
		$ret = '';
		if(!$cancel)
		{
			if ($exit == 'notebook') {
				$ret = parseChatCont();
                                // 給予較完整的標題，以利筆記本選單辨識
                                $RS = dbGetStSr('WM_chat_setting', '`owner`, `title`, `host`, `exit_action`', "`rid`='{$rid}'", ADODB_FETCH_ASSOC);
                                $lang  = getCaption($RS['title']);
                                $title = $sysSession->course_name . '-' . date("Ymd") . '[' . $lang[$sysSession->lang] . ']' . $MSG['chat_log'][$sysSession->lang];
				collect('sys_notebook', $sysSession->username, $sysSession->username, '', $title, $MSG['chat_log_attachment'][$sysSession->lang], 'text', '', $ret, 0);
			}
		}

		// 最後一個登出 或 從討論室管理結束會議 都要處理討論室紀錄 by Small 2011/11/16
		list($cnt) = dbGetStSr('WM_chat_session', 'count(*)', "`rid`='{$rid}'", ADODB_FETCH_NUM);
		if ($cnt <= 0) {
			list($msg_count) = dbGetStSr('WM_chat_msg','count(*)',"`rid`='{$rid}' and msgType != 1 and msgType !=0", ADODB_FETCH_NUM);
			
			if($msg_count<=0){
				dbDel('WM_chat_msg', "rid='{$rid}'");
				return false;
			}
			$RS = dbGetStSr('WM_chat_setting', '`owner`, `title`, `host`, `exit_action`', "`rid`='{$rid}'", ADODB_FETCH_ASSOC);
			$act = explode(',', $RS['exit_action']);
			// 聊天室的 Title (Begin)
			$lang  = getCaption($RS['title']);
			$title = date("Ymd") . '[' . $lang[$sysSession->lang] . ']' . $MSG['chat_log'][$sysSession->lang];
			// 聊天室的 Title (End)
			if (is_array($act)) {
				foreach ($act as $val) {
					switch ($val) {
						case 'forum'   :
							if (empty($RS['host'])) $RS['host'] = $sysSession->username;
							if (empty($ret)) {
								// 產生報告資料
								// 如果不是來自討論室管理，則照舊處理 by Small 2011/11/16
								if(!$cancel)
									$ret = parseChatCont(0, $RS['host']);
								else
									$ret = parseChatCont(0, $RS['host'],$rid);
								$dir = MakeUserDir($RS['host']);
							} else {
								$dir = MakeUserDir($sysSession->username);
							}
							$dir = str_replace(sysDocumentRoot . DIRECTORY_SEPARATOR, '', $dir);
							$owner = explode('_', $RS['owner']);
							$owner_id = intval($owner[0]);
							if ($owner_id > 0) {
								if ((10001 <= $owner_id) && ($owner_id < 100000)) {
									// 學校
									$res = saveSchoolRecord($RS['owner'], $title, $MSG['chat_log_attachment'][$sysSession->lang], $dir, $ret);
								} else if ((1000001 <= $owner_id) && ($owner_id < 10000000)) {
									// 班級
									if (count($owner) > 2) {
										// 班級學員分組
										saveClassGrpRecord($owner_id, intval($owner[2]), intval($owner[1]), $title . $MSG['chat_log'][$sysSession->lang], $MSG['chat_log_attachment'][$sysSession->lang], $dir, $ret);
									} else {
										// 班級
										saveClassRecord($owner_id, $title, $MSG['chat_log_attachment'][$sysSession->lang], $dir, $ret);
									}
								} else if ((10000001 <= $owner_id) && ($owner_id < 100000000)) {
									// 課程
									if (count($owner) > 2) {
										// 課程學員分組
										saveCourseGrpRecord($owner_id, intval($owner[2]), intval($owner[1]), $title, $MSG['chat_log_attachment'][$sysSession->lang], $dir, $ret);
									} else {
										// 課程
										saveCourseRecord($owner_id, $title, $MSG['chat_log_attachment'][$sysSession->lang], $dir, $ret);
									}
								}
							}
							break;
						case 'notebook':
							if (!empty($RS['host'])) {
								if (empty($ret)) {
									// 產生報告資料
									// 如果不是來自討論室管理，則照舊處理 by Small 2011/11/16
									if(!$cancel)
										$ret = parseChatCont(0, $RS['host']);
									else
										$ret = parseChatCont(0, $RS['host'],$rid);
								} else {
									// 複製已經產生好的報告
									$dir = MakeUserDir($sysSession->username);
									$ret = cpAttach($RS['host'], $dir, $ret);
								}
								collect('sys_notebook', $RS['host'], $RS['host'], '', $title, $MSG['chat_log_attachment'][$sysSession->lang], 'text', '', $ret, 0);
							}
							break;
						case 'email'   :
							break;
						default:
					}
				}
			}
			// 清除聊天內容與檔案
			$path = getRecFullPath();
			// @unlink($path);
			if(!$cancel)
				clearChatMsg();
			else
				clearChatMsg($rid);
			$dir = getChatPath();
			$d = dir($dir);
			if ($d) {
				while (false !== ($entry = $d->read())) {
					if (($entry == '.') || ($entry == '..')) continue;
					@unlink($dir . $entry);
				}
				$d->close();
			}
		}
		/*
		if ((strpos($rid, 'online') === FALSE)) {
		} else {
			// 對談的聊天室
		}
		*/

		// 安全性檢查
		// 如果是討論室管理結束會議，則回傳不同訊息 by Small 2011/11/16
		if(empty($cancel))
			echo $MSG['logout_ok'][$sysSession->lang];
		else
			echo $MSG['cancel_ok'][$sysSession->lang];
	}

?>

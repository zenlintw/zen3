<?php
	/**
	 * 收錄文章
	 *
	 * @since   2003/05/19
	 * @author  ShenTing Lin
	 * @version $Id: collect.php,v 1.1 2010/02/24 02:40:17 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/mime_mail.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/mime_detection.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200200500';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/**
	 * 複製夾檔到訊息中心
	 * @param string $to  : 複製到哪個帳號
	 * @param string $dir : 原始檔案的路徑
	 * @param string or array $files : 檔案清單
	 *     string : (原始檔名\t實際檔名)\t(原始檔名\t實際檔名)  <= 括號只為了清楚表示用
	 *     array  : 0 => 原始檔名, 1 => 實際檔名, 2 => 原始檔名, 3 => 實際檔名  <= 也就是字串切割後的資料
	 * @return
	 *     string : (原始檔名\t實際檔名)\t(原始檔名\t實際檔名)  <= 括號只為了清楚表示用
	 **/
	function cpAttach($to, $dir, $files) {
		$userdir = MakeUserDir($to);    // 建立並取得該帳號的目錄
		$ary     = array();
		if (!empty($dir)) $dir .= '/';
		// 拆解檔案清單 (Begin)
		if (is_string($files))
		{
			$str = trim($files);
			if (empty($str)) $attach = array();
			else $attach = explode("\t", $str);
		}
		elseif (is_array($files))
		{
			$attach  = $files;
		}
		else
		{
			return false;
		}
		// 拆解檔案清單 (End)
		for ($i = 0; $i < count($attach); $i = $i + 2) {
			$target = uniqid('WM') . strrchr($attach[$i], '.');
			@copy($dir . $attach[$i + 1], $userdir . DIRECTORY_SEPARATOR . $target);
			$ary[] = $attach[$i];
			$ary[] = $target;
		}
		$ret = implode("\t", $ary);
		return $ret;
	}
///////////////////////////////////////////////////////////////////////////////
	/**
	 * 從訊息中心複製信件到筆記本
	 * @param integer $serial  : 訊息編號
	 * @param string $username : 帳號
	 *     要複製到哪個帳號，預設以 Session 中的 username 為主
	 * @param string $fid      : 資料夾編號
	 *     要複製到哪個資料夾，預設以 sys_notebook (筆記本) 為主
	 * @return
	 **/
	function msg2note($serial, $username='', $fid='sys_notebook') {
		global $sysSession, $sysConn;

		$serial = intval($serial);
		$RS = dbGetStSr('WM_msg_message', '*', "msg_serial={$serial}", ADODB_FETCH_ASSOC);
		if (!$RS) return false;

		// 要複製到哪個帳號，預設以 Session 中的 username 為主
		if (empty($username)) {
			$username = $sysSession->username;
			$RS['receiver'] = $sysSession->username;
		}

		$orgdir  = MakeUserDir($sysSession->username);
		$ret = cpAttach($username, $orgdir, trim($RS['attachment']));
		if ($ret === false) $ret = '';

		dbNew('WM_msg_message',
			'`folder_id`, `sender`, `receiver`, `submit_time`, `receive_time`, `priority`, ' .
			'`subject`, `content`, `attachment`, `note`, `content_type`',
			"'{$fid}','{$RS['sender']}', '{$username}', '{$RS['submit_time']}', " .
			"'{$RS['receive_time']}', '{$RS['priority']}', '{$RS['subject']}', '{$RS['content']}', " .
			"'{$ret}', '{$RS['note']}', '{$RS['type']}'"
		);
                wmSysLog('2200200101', $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Added to message center success(msg2note)：' . $RS['sender'] . '|' . $username . '|' . $RS['subject']);
		return ($sysConn->Affected_Rows() > 0);
	}
///////////////////////////////////////////////////////////////////////////////
	/**
	 * 處理收件者
	 *     將收件者切割放到陣列中，並且過濾重複的人員
	 * @param string or array $to : 收件者
	 *     可以使用 ',' ';' ' ' 這三種來分別不同的收件者
	 * @return
	 *     boolean false : 失敗
	 *     array  $rever : 收件者的陣列
	 **/
	function parseTo($to) {
		global $sysSession;

		$rever = array();
		// 將收件者切割放到陣列中，並且過濾重複的人員
		if (is_string($to)) {
			$rever = preg_split('/[^\w.@-]+/', $to, -1, PREG_SPLIT_NO_EMPTY);
		} else if (is_array($to)) {
			$rever = $to;
		} else {
			return false;
		}
		$tmp = array_unique($rever);    // 過濾重複的人員
		// 清除空白的帳號 (Begin)
		$rever = array();
		foreach ($tmp as $val) {
			$val = trim($val);
			if (empty($val)) continue;
			$rever[] = $val;
		}
		// 清除空白的帳號 (End)

		return $rever;
	}

	/**
	 * 將 mail 的標題編碼
	 * @param string $from    : 顯示的名稱
	 * @param string $email   : Email
	 * @param string $charset : 字集
	 * @return string : 編碼後的 from
	 **/
	function mailEncFrom($from='', $email='', $charset='utf-8') {
		$email = trim($email);
		$from  = trim($from);
		if (empty($email)) return false;
		if (empty($from)) return $email;

		$cset = strtolower($charset);
		$str  = '=?' . $cset . '?B?' . base64_encode($from) . '?= <' . $email . '>';
		return $str;
	}

	/**
	 * 將 mail 的標題編碼
	 * @param string $subject : 標題
	 * @param string $charset : 字集
	 * @return string : 編碼後的標題
	 **/
	function mailEncSubject($subject='', $charset='utf-8') {
		if (empty($subject)) return false;
		$cset = strtolower($charset);
		$str  = '=?' . $cset . '?B?' . base64_encode($subject) . '?=';
		return $str;
	}

	/**
	 * 建立 E-mail
	 * @param string $from         : 寄件者
	 * @param string $subject      : 標題
	 * @param string $content      : 內容
	 * @param string $ctype        : 內容格式
	 *     text : 純文字
	 *     html : HTML
	 * @param string $tagline      : 簽名檔
	 * @param string $attachment   : 夾檔
	 *     (原始檔名\t實際檔名)\t(原始檔名\t實際檔名)  <= 括號只為了清楚表示用
	 * @param string $attach_dir   : 檔案存放路徑
	 * @param string $priority     : 重要性
	 * @param string $notification : 是否須回覆 (尚無作用)
	 * @return
	 **/
	function buildMail($from='', $subject='', $content='', $ctype='html',
			   $tagline='',
			   $attachment='', $attach_dir='',
			   $priority=0, $notification=FALSE, $charset='utf-8') {
		global $sysSession, $sysConn;

		// 優先順序 (轉換成 email 的優先順序)
		$mail_priority = array(
				-2 => 5,    // 最低
				-1 => 4,
				 0 => 3,    // 一般
				 1 => 2,
				 2 => 1,    // 最高
			);

		$level = $mail_priority[intval($priority)];
		if (empty($level)) $level = 3;
		// 建立寄件者
		if (empty($from)) $from = mailEncFrom($sysSession->realname, $sysSession->email, 'utf-8');
                
                // #047461
                $sysConn->Execute('use ' . sysDBname);
                
                // school_host 請手動改為學校domain
                $RS1         = $sysConn->Execute("select school_name, school_mail from WM_school where school_id='10001' and school_host='wmpro5.sun.net.tw'");
                $school_name = addslashes($RS1->fields['school_name']);
                $school_mail = $RS1->fields['school_mail'];

                if (!empty($sysSession->email)) {
                    $from  = mailEncFrom($sysSession->realname, $sysSession->email, 'utf-8');
                    $reply = $from;
                } else {
                    $from  = mailEncFrom($school_name, $school_mail, 'utf-8');
                    $reply = $from;
                }

		// 開始建立郵件 (Begin)
		$mail = new mime_mail;
		$mail->priority  = $level;
		$mail->from      = $from;
		$mail->reply     = $reply;
		$mail->charset   = $charset;
		$mail->subject   = mailEncSubject($subject, 'utf-8');
		$ctype = ($ctype == 'html') ? 'html' : 'text';   // 避免被亂塞資料，若需要其他的格式再作修改
		$mail->body_type = ($ctype == 'html') ? 'text/html' : 'text/plain';
			// 建立夾檔 (Begin)
		$attach_dir = trim($attach_dir);
		if (!empty($attach_dir)) $attach_dir .= '/';
		if (!empty($attachment)) {
			$attach = explode("\t", $attachment);
			chdir(sysDocumentRoot);
			for ($i = 0; $i < count($attach); $i = $i + 2) {
				$filename = realpath($attach_dir . $attach[$i + 1]);
				if (!file_exists($filename) || !is_file($filename)) continue;
				$file = implode('', file($filename));
				$name = mailEncSubject($attach[$i], 'utf-8');
				if ($ctype == 'html') $IMGS[] = $name;
				$att_type = detect_mime($filename);
				if (empty($att_type)) $att_type = 'application/octet-stream';
				$mail->add_attachment($file, $name, $att_type);
			}
		}
			// 建立夾檔 (End)
		// 內文去除所有的不必要 html，並且加上簽名檔
		if ($ctype == 'html') {
			while(ereg('%(IMGS\[[0-9]+\])%', $content, $reg)){
				eval('$v = $' . $reg[1] . ';');
				$content = str_replace("%{$reg[1]}%", $v, $content);
			}
		}

		$hr = ($ctype == 'html') ? '<br />====================<br />' : "\n====================\n";
		if (!empty($tagline)) $tagline = $hr . $tagline;
		$mail->body = strip_scr(stripslashes($content)) . $tagline;
		// 開始建立郵件 (End)

		return $mail;
	}

	/**
	 * 收錄文章
	 * @param string  $fid        : 資料夾編號
	 * @param string  $from       : 張貼者
	 * @param string  $to         : 收件者
	 * @param string  $stime      : 張貼時間
	 * @param string  $subject    : 標題
	 * @param string  $content    : 內容
	 * @param string  $ctype      : 內容格式
	 * @param string  $tagline    : 簽名檔
	 * @param string  $attachment : 夾檔，請先用 cpAttach() 將夾檔複製到訊息中心
	 * @param string  $priority   : 優先順序，預設為一般
	 * @param string  $status     : 優先順序，預設為一般
	 * @param string  $note       : 附註
	 * @return
	 **/
	function collect($fid='sys_notebook', $from='', $to='', $stime='', $subject='', $content='', $ctype='html', $tagline='', $attachment='', $priority=0, $status='', $note='') {
		global $sysConn, $sysSession, $msgFuncID;

		// 張貼者
		$from  = trim($from);
		// 收件者
		if (empty($to)) $to = $sysSession->username;
		// 傳送時間
		if (empty($stime)) $stime = date('Y-m-d H:i:s');
		// 標題不許使用 html
		// $subject = htmlspecialchars($subject, ENT_QUOTES);
		// 內文的型態
		$ctype = trim($ctype);
		$ctype = ($ctype == 'html') ? 'html' : 'text';   // 避免被亂塞資料
		// 內文去除所有的不必要 html，並且加上簽名檔
		$hr = ($ctype == 'html') ? '<br />====================<br />' : "\n====================\n";
		if (!empty($tagline)) $tagline = $hr . $tagline;
		$content = strip_scr($content) . $tagline;
		$content = $sysConn->qstr($content);
		// 夾檔，請先用 cpAttach() 複製到寄件者的訊息中心去
		$attachment = trim($attachment);
		// 附註
		$note = trim($note);
		// 優先權
		$priority = intval($priority);

		// 收錄
		if ($fid == 'sys_sent_backup')
		{
			list($msg_reserved) = dbGetStSr('WM_user_account', 'msg_reserved', "username='{$sysSession->username}'", ADODB_FETCH_NUM);
			if ($msg_reserved == 1)
			{
				dbNew('WM_msg_message',
					'`folder_id`, `sender`, `receiver`, `submit_time`, `status`, `priority`, ' .
					'`subject`, `content`, `attachment`, `note`, `content_type`',
					"'{$fid}','{$from}', '{$to}', '{$stime}', '', '{$priority}', " .
					"'{$subject}', {$content}, '{$attachment}', '{$note}', '{$ctype}'"
				);
                            wmSysLog('2200200102', $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Added to message center success(collect)：sys_sent_backup|' . $from . '|' . $to . '|' . $subject);
			}
		}else{
			dbNew('WM_msg_message',
				'`folder_id`, `sender`, `receiver`, `submit_time`, `receive_time`, `status`, `priority`, ' .
				'`subject`, `content`, `attachment`, `note`, `content_type`',
				"'{$fid}','{$from}', '{$to}', '{$stime}', '{$stime}', '', '{$priority}', " .
				"'{$subject}', {$content}, '{$attachment}', '{$note}', '{$ctype}'"
			);
                        wmSysLog('2200200103', $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Added to message center success(collect)：' . $fid . '|' . $from . '|' . $to . '|' . $subject);

            if ($sysSession->cur_func == $msgFuncID['notebook']) {
                // 如果是筆記本的功能，要處理雲端筆記的log - begin
                $logTime = strtotime($stime);
                $where = "`folder_id` = '{$fid}'
                        AND `sender` = '{$from}' AND `receiver` = '{$to}'
                        AND `submit_time` = '{$stime}'
                        AND `subject` = '{$subject}'";
                list($serial) = dbGetStSr('WM_msg_message', 'msg_serial', $where, ADODB_FETCH_NUM);

                dbNew('APP_note_action_history',
                    '`username`, `log_time`, `action`, `folder_id`, `msg_serial`, `from`',
                    "'{$sysSession->username}', {$logTime}, 'A', '{$fid}', {$serial}, 'server'");
                // 如果是筆記本的功能，要處理雲端筆記的log - end
            }
		}
		return ($sysConn->Affected_Rows() > 0);
	}
?>

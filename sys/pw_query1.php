<?php
	/**
	 * 查詢帳號密碼
	 *
	 * @author  ShenTing Lin
	 * @version $Id: pw_query1.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/pw_query.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/mime_mail.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

    // mooc 模組開啟的話將網頁導向index.php
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        header('Location: /mooc/index.php');
        exit;
    }

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/**
	 * 將 mail 的標題編碼
	 * @param string $from    : 顯示的名稱
	 * @param string $email   : Email
	 * @param string $charset : 字集
	 * @return string : 編碼後的 from
	 **/
	function mailEncFrom($from='', $email='', $charset='utf-8') {
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
	
	// 設定車票
	//setTicket();
	//setcookie("Ticket", $sysSession->ticket, time()+3600);

	$content = '';

	$js = <<< BOF
	function GoHome() {
		window.location.replace("/");
	}

BOF;

	list($isUserExist) = dbGetStSr('WM_user_account', 'count(*)', "username='" . trim($_POST['query_user']) . "' AND email='" . trim($_POST['email']) . "'", ADODB_FETCH_NUM);
	if (!$isUserExist) {

	$js .= <<< BOF
	function ReQuery() {
		window.location.replace("/sys/pw_query.php");
	}

BOF;
		ob_start();
			showXHTML_script('inline', $js);
			showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
				showXHTML_tr_B('');
					showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor01"', '&nbsp;');
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					showXHTML_td('width="100%" height="200" align="center" valign="middle" nowrap style="color : #FF0000"', $MSG['username_email_fail'][$sysSession->lang]);
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					// $image = "/theme/{$sysSession->theme}/sys/button.gif";
					$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";
					$btn = showButton('button', $MSG['query_pwd'][$sysSession->lang], $image, 'class="cssBtn1" onclick="ReQuery();"') . '&nbsp;' .
					       showButton('button', $MSG['home'][$sysSession->lang]     , $image, 'class="cssBtn1" onclick="GoHome();"');
					showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor02"', $btn);

				showXHTML_tr_E('');
			showXHTML_table_E('');
			$content = ob_get_contents();
		ob_end_clean();
	} else {
		/* 變更使用者的密碼 */
		// 產生新的密碼
		mt_srand(intval(substr(microtime(),3,6)));
		$NewPwd = sprintf("%c%c%c.%03d",
				mt_rand(97,122),
				mt_rand(97,122),
				mt_rand(97,122),
				mt_rand(1,999));

		// 將新的密碼寫到資料庫
		dbSet('WM_user_account', "password=md5('$NewPwd')", "username='" . trim($_POST['query_user']) . "'");
		if ($sysConn->Affected_Rows()) {
			dbSet('WM_all_account', "password=md5('$NewPwd')", "username='" . trim($_POST['query_user']) . "'");

			// 寄出帳號與密碼的通知信
			$mail = new mime_mail;
			$body = strtr($MSG['mail_body'][$sysSession->lang],
				      array('%%SCHOOL_NAME%%' => $sysSession->school_name,
					    '%%SERVER_NAME%%' => $_SERVER['HTTP_HOST'],
					    '%%USERNAME%%'    => trim($_POST['query_user']),
					    '%%PASSWORD%%'    => $NewPwd
				           )
				     );

			$subject = str_replace('%%SCHOOL_NAME%%', $sysSession->school_name, $MSG['mail_subject'][$sysSession->lang]);

			// 查詢學校的email
			list($school_mail) = dbGetStSr('WM_school','school_mail',"school_id='{$sysSession->school_id}' and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);
			// 寄件者
			if (empty($school_mail)){
				$school_mail = 	'webmaster@'. $school_host;
			}
			$send_from = mailEncFrom($sysSession->school_name,$school_mail);

			// $mail->subject = iconv('UTF-8', $sysSession->lang, $subject);
			$mail->subject = mailEncSubject($subject, 'utf-8');
			$mail->from = $send_from;
			// $mail->body = iconv('UTF-8', $sysSession->lang, $body);
			$mail->body = $body;
			$mail->reply = $send_from;
			$mail->to = trim($_POST['email']);
			$mail->charset = 'utf-8';
			$mail->send();

			$msg = str_replace('%%EMAIL%%', trim($_POST['email']), $MSG['msg_success'][$sysSession->lang]);
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], 'passwd query=>send new passwd by Email');
		} else {
			$msg = $MSG['msg_fail'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'others', $_SERVER['PHP_SELF'], 'System Error');
		}
		// 顯示相關的訊息
		ob_start();
			showXHTML_script('inline', $js);
			showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
				showXHTML_tr_B('');
					showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor01"', '&nbsp;');
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					showXHTML_td('width="100%" height="200" align="center" valign="middle" style="color : #FF0000"', $msg);
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					// $image = "/theme/{$sysSession->theme}/sys/button.gif";
					$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";
					$btn  = showButton('button', $MSG['home'][$sysSession->lang], $image, 'class="cssBtn1" onclick="GoHome();"');
					showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor02"', $btn);
				showXHTML_tr_E('');
			showXHTML_table_E('');
			$content = ob_get_contents();
		ob_end_clean();
	}

	layout('', $content);
?>

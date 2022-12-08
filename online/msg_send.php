<?php
	/**
	 * 傳送訊息
	 *
	 * @since   2003/11/06
	 * @author  ShenTing Lin
	 * @version $Id: msg_send.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '2100100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function sys_error() {
		header("Content-type: text/xml");
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		echo '<manifest></manifest>';
		exit;
	}

	/**
	 * 切割訊息，假如傳訊的內容過長的話
	 * @param string $cont : 訊息內容
	 * @return array : 切割後的訊息
	 **/
	function split_cont($cont) {
		// 濾掉不需要的資料
		$str = strip_scr($cont);
		if (strlen($str) <= 250) {
			return array($str);
		} else {
			$str = chunk_split($str, 250, chr(8));
			return explode(chr(8), $str);
		}
	}

	/**
	 * 傳送訊息
	 * @param string $user : 收訊者
	 * @param array  $cont : 訊息內容
	 * @return integer : 成功或失敗
	 *     0 : 失敗
	 *     1 : 成功
	 *     2 : 成功，但是使用者已經離線了
	 **/
	function send_msg($user, $cont, $ctype='text', $talk='', $rid='') {
		global $sysSession, $sysConn;
		$succ = 1;

		$user = trim($user);
		if (empty($user)) return false;
		list($rv, $tk, $st) = dbGetStSr('WM_im_setting', '`recive`, `talk`, `status`', "`username`='{$user}'", ADODB_FETCH_NUM);
		if ((!empty($talk)) && ($tk == 'N')) return 5;    // 不想 Talk
		// 傳送者的資料
		$from = $sysSession->username;
		$name = addslashes($sysSession->realname);
		// 產生訊息的序號
		$msg_id = uniqid(uniqid('msg_'));
		// 傳送時間
		$date = date('Y-m-d H:i:s');
		switch (trim($talk)) {
			case 'Talk'  :
				$rid = trim($rid);
				if (empty($rid)) $rid = uniqid('online_');
				break;
			// case 'Talk'  : $rid = $msg_id; break;
			case 'Accept':
				$rid = trim($rid);
				break;
			default:
				$rid = '';
		}

		$field = '`username`, `serial`, `sorder`, `sender`, `sender_name`, `reciver`, `send_time`, `talk`, `chat_id`, `message`, `ctype`, `saw`';
		for ($i = 0; $i < count($cont); $i++) {
			$txt  = $sysConn->qstr($cont[$i]);
			// 給別人
			$vals = "'{$user}', '{$msg_id}', '{$i}', '{$from}', '{$name}', '{$user}', '{$date}', '{$talk}', '{$rid}', {$txt}, '{$ctype}', 'N'";
			dbNew('WM_im_message', $field, $vals);
			if ($sysConn->Affected_Rows() <= 0) {
				$succ = 0;
				break;
			}

			// 自己留存
			$vals = "'{$from}', '{$msg_id}', {$i}, '{$from}', '{$name}', '{$user}', '{$date}', '{$talk}', '{$rid}', {$txt}, '{$ctype}', 'Y'";
			dbNew('WM_im_message', $field, $vals);
		}
		if ($succ == 0) {
			// 若有多筆內容要記，只要其中一筆資料新增失敗，則移除之前新的的資料
			dbDel('WM_im_message', "`serial`='{$msg_id}'");
		} else {
			list($cnt) = dbGetStSr('WM_session', 'count(*)', "`username`='{$user}' AND `chance`<3", ADODB_FETCH_NUM);
			if ($cnt > 0) {
				$succ = 1;
				if (empty($talk)) {
					if ($st == 'Invisible') {
						$succ = 3;    // 隱形
					} else if ($rv == 'N') {
						$succ = 4;    // 不想接訊息
					}
				}
			} else {
				$succ = 2; // 離線
			}
		}
		return $succ;
	}

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			sys_error();
		}

		$user = getNodeValue($dom, 'user');
		if (empty($user)) sys_error();
		$list = explode(',', $user);

		$cont = getNodeValue($dom, 'content');
		$strs = split_cont($cont);

		$talk = getNodeValue($dom, 'talk');
		if (!empty($talk)) {
			switch (trim($talk)) {
				case 'talk'  :
					$talk = 'Talk';
					$rid = uniqid('online_');
					break;
				case 'reject': $talk = 'Refuse'; break;
				case 'accept':
					$talk = 'Accept';
					$rid = getNodeValue($dom, 'other');
					$pwd = md5(sysTicketSeed . $_COOKIE['idx']);
					$enc = base64_decode(trim($rid));
					$rid = trim(@mcrypt_decrypt(MCRYPT_DES, $pwd, $enc, 'ecb'));
					break;
				default:
			}
		}

		$ctype = getNodeValue($dom, 'ctype');
		if (($ctype != 'text') && ($ctype != 'html')) $ctype = 'text';

		$res = array();
		for ($i = 0; $i < count($list); $i++) {
			$res[] = $list[$i];
			$res[] = send_msg($list[$i], $strs, $ctype, $talk, $rid);
		}
		echo implode("\t", $res);
		// echo $cont;
	}
?>

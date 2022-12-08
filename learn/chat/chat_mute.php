<?php
	/**
	 * 禁止發言
	 *
	 * @since   2003/12/05
	 * @author  ShenTing Lin
	 * @version $Id: chat_mute.php,v 1.1 2010/02/24 02:39:06 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2000200200';
	$sysSession->restore();
	if (!aclVerifyPermission(2000200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 安全性檢查

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
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
		$host  = getChatHost();
		if ($sysSession->username != $host) die();   // 若不是主持人則不做任何動作

		$allow = trim(getNodeValue($dom, 'allow'));
		$deny  = trim(getNodeValue($dom, 'deny'));

		$newA  = preg_split('/[^\w.-]+/', $allow, -1, PREG_SPLIT_NO_EMPTY);
		$newD  = preg_split('/[^\w.-]+/', $deny , -1, PREG_SPLIT_NO_EMPTY);

		$users = array();
		$orgA  = array();
		$orgD  = array();
		$RS = dbGetStMr('WM_chat_session', '`username`, `realname`, `voice`', '1', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$users[$RS->fields['username']] = $RS->fields['realname'];
			if ($RS->fields['voice'] == 'allow') {
				$orgA[$RS->fields['username']] = 'allow';
			} else {
				$orgD[$RS->fields['username']] = 'deny';
			}
			$RS->MoveNext();
		}
		$rid = $sysSession->room_id;
		$sql = implode("','", $newA);
		dbSet('WM_chat_session', "`voice`='allow'", "`rid`='{$rid}' AND `username` in ('$sql')");

		$sql = implode("','", $newD);
		dbSet('WM_chat_session', "`voice`='deny'", "`rid`='{$rid}' AND `username` in ('$sql')");

		// 顯示新的允許發言的人員
		foreach ($newA as $val) {
			if (($val == $host) || empty($val)) continue;
			if ($orgA[$val] != 'allow') {
			   setChatCont($MSG['chat_voice'][$sysSession->lang], 5, 0, $val, $users[$val]);
			   wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->room_id , 0, 'auto', $_SERVER['PHP_SELF'], "允許 $val 發言");
			} 
		}

		// 顯示新的被禁止發言的人員
		foreach ($newD as $val) {
			if (($val == $host) || empty($val)) continue;
			if ($orgD[$val] != 'deny') {
			   setChatCont($MSG['chat_mute'][$sysSession->lang], 6, 0, $val, $users[$val]);
			   wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->room_id , 0, 'auto', $_SERVER['PHP_SELF'], "禁止 $val 發言");
			}
		}
		echo $MSG['chat_msg_mute'][$sysSession->lang];
	}
?>

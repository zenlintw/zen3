<?php
	/**
	 * 進聊天室
	 *
	 * @since   2003/12/25
	 * @author  ShenTing Lin
	 * @version $Id: chat_change.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func='2000200100';
	$sysSession->restore();
	if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	function goChatroom($rid) {
		global $sysSession, $_COOKIE, $MSG;

		dbSet('WM_session', "`room_id`='{$rid}'", "idx='{$_COOKIE['idx']}'");

		$username = $sysSession->username;
		$realname = addslashes($sysSession->realname);
		list($title, $sysH, $getH, $max, $state) = dbGetStSr('WM_chat_setting', '`title`, `host`, `get_host`, `maximum`, `state`', "`rid`='{$rid}'", ADODB_FETCH_NUM);
		// 主持人
		$nowH = getChatHost();   // 取得目前聊天室的主持人
		$host = (empty($nowH)) ? 'Y' : 'N';
		// 若登入的是設定的主持人，而且跟目前聊天室的主持人不是同一個人，就搶回主持權
		if (($sysSession->username == $sysH) && ($getH == 'Y') && ($nowH != $sysH)) {
			dbSet('WM_chat_session', "`host`='N'", "`rid`='{$rid}'");
			$host = 'Y';
		}
		$lang  = getCaption($title);
		$rname = $lang[$sysSession->lang];

		// 寫入登入訊息
		$sysSession->room_id = $rid;
		setChatCont('', 1, 0);

		// 建立 Session
		dbNew('WM_chat_session', '`rid`, `idx`, `username`, `realname`, `host`, `voice`, `login`', "'{$rid}', '{$_COOKIE['idx']}', '{$username}', '{$realname}', '{$host}', 'allow', NOW()");
	}

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		header("Content-type: text/xml");
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		echo '<manifest>';
		/*
		if (!empty($sysSession->room_id)) {
			echo '<msg>' . $MSG['msg_in_chat'][$sysSession->lang] . '</msg>';
			echo '<uri></uri>';
		} else {
		*/
			$chat_id = trim(getNodeValue($dom, 'chat_id'));
			goChatroom($chat_id);
		/*
			echo '<msg></msg>';
			echo '<uri>/learn/chat/index.php</uri>';
		}
		*/
		echo '</manifest>';
	}

?>

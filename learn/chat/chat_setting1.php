<?php
	/**
	 * 儲存設定
	 *
	 * @since   2003/12/10
	 * @author  ShenTing Lin
	 * @version $Id: chat_setting1.php,v 1.1 2010/02/24 02:39:06 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$isUpdate = false;
	// 儲存個人設定 (Begin)
	$user_exit = trim($_POST['user_exit']);
	if (!in_array($user_exit, array_keys($exitUser))) $user_exit = 'notebook';
	if ($user_exit == 'none') $user_exit = '';
	$in_out = isset($_POST['user_in_out']) ? 'visible' : 'hidden';
	list($cnt) = dbGetStSr('WM_chat_user_setting', 'count(*)', "`username`='{$sysSession->username}'", ADODB_FETCH_NUM);
	if ($cnt > 0) {
		dbSet('WM_chat_user_setting', "`exit_action`='{$user_exit}', `inout_msg`='{$in_out}'", "`username`='{$sysSession->username}'");
	} else {
		dbNew('WM_chat_user_setting', '`username`, `exit_action`, `inout_msg`', "'{$sysSession->username}', '{$user_exit}', '{$in_out}'");
	}
	$isUpdate = (($sysConn->Affected_Rows() > 0) || $isUpdate);
		// 儲存線上傳訊設定
	$rec = isset($_POST['user_online_msg']) ? 'Y' : 'N';
	
	dbNew('WM_im_setting', 'username, recive, talk, status', "'{$sysSession->username}', '{$rec}', 'Y', 'Online'");
	if ($sysConn->ErrorNo() == 1062)
		dbSet('WM_im_setting', "`recive`='{$rec}'", "`username`='{$sysSession->username}'");
	
	$isUpdate = (($sysConn->Affected_Rows() > 0) || $isUpdate);
	// 儲存個人設定 (End)

	// 檢查主持人 (Begin)
	$isHost  = false;
	$isAdmin = false;
	// 檢查是不是現在聊天室的主持人
	$host = getChatHost();
	if ($sysSession->username == $host) $isHost = true;
	// 檢查是不是聊天室的管理員
	$adm  = getChatAdmin();
	if ($sysSession->username == $adm) {
		$isHost  = true;
		$isAdmin = true;
	}
	// 檢查主持人 (End)

	// 切換主持人
	if ($isHost) {
		$_POST['newHost'] = trim($_POST['newHost']);
		if ($host != $_POST['newHost']) {
			dbSet('WM_chat_session', "`host`='N'", "`rid`='{$sysSession->room_id}'");
			dbSet('WM_chat_session', "`host`='Y'", "`rid`='{$sysSession->room_id}' AND `username`='{$_POST['newHost']}'");
			$isUpdate = true;
		}
	}

	// 儲存聊天室設定
	if ($isHost) {
		$lang['Big5']        = stripslashes(trim($_POST['host_room_name_big5']));
		$lang['GB2312']      = stripslashes(trim($_POST['host_room_name_gb']));
		$lang['en']          = stripslashes(trim($_POST['host_room_name_en']));
		$lang['EUC-JP']      = stripslashes(trim($_POST['host_room_name_jp']));
		$lang['user_define'] = stripslashes(trim($_POST['host_room_name_user']));
		$chat_name           = addslashes(serialize($lang));
		$chat_exit           = trim($_POST['host_exit']);
		if (!in_array($chat_exit, array_keys($exitHost))) $chat_exit = 'forum';
		$chat_limit    = intval($_POST['host_user_limit']);
		$chat_jump     = isset($_POST['host_change']) ? 'allow' : 'deny';
		$chat_media    = isset($_POST['enable_media']) ? 'enable' : 'disable';
		$chat_ip       = ''; //trim($_POST['host_media_ip']); // 目前沒有實做，直接清空
		$chat_port     = intval($_POST['host_media_port']);
		$chat_protocol = (trim($_POST['host_media_protocol']) == 'tcp') ? 'TCP' : 'UDP';
		$chat_host     = trim($_POST['host_root']);
		$chat_login    = (trim($_POST['host_login'])          == 'no') ? 'N' : 'Y';

		$res = checkUsername($chat_host);
		if (($res != 2) || ($res != 4)) $chat_host = $host;

		if ($isAdmin) {
			dbSet('WM_chat_setting',
				"`title`='{$chat_name}', `host`='{$chat_host}', `get_host`='{$chat_login}', `maximum`={$chat_limit}, `exit_action`='{$chat_exit}', `jump`='{$chat_jump}', `media`='$chat_media', `ip`='{$chat_ip}', `port`='{$chat_port}', `protocol`='{$chat_protocol}', `tone`=''",
				"`rid`='{$sysSession->room_id}'");
		} else {
			dbSet('WM_chat_setting',
				"`title`='{$chat_name}', `maximum`={$chat_limit}, `exit_action`='{$chat_exit}', `jump`='{$chat_jump}', `media`='$chat_media', `ip`='{$chat_ip}', `port`='{$chat_port}', `protocol`='{$chat_protocol}', `tone`=''",
				"`rid`='{$sysSession->room_id}'");
		}
		$isUpdate = (($sysConn->Affected_Rows() > 0) || $isUpdate);
	}
	$msg = ($isUpdate) ? $MSG['chat_set_success'][$sysSession->lang] : $MSG['chat_set_fail'][$sysSession->lang];

	showXHTML_head_B($MSG['chat_set_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['char_set_save'][$sysSession->lang], 'tabs1');
		showXHTML_tabFrame_B($ary, 1);
			showXHTML_table_B('width="400" border="0" cellspacing="1" cellpadding="3" id="tabs1" class="cssTable"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('', $msg);
				showXHTML_tr_E();
				showXHTML_tr_B();
					showXHTML_td_B('align="center" class="cssTrOdd"');
						showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="window.close();"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

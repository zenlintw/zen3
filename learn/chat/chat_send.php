<?php
	/**
	 * 紀錄聊天內容
	 *
	 * @since   2003/11/27
	 * @author  ShenTing Lin
	 * @version $Id: chat_send.php,v 1.1 2010/02/24 02:39:06 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/Hongu/Validate/Validator/XssAttack.php');
        
	$sysSession->cur_func='2000200100';
	$sysSession->restore();
	if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
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
		$hongu = new Hongu_Validate_Validator_XssAttack();
		$rid     = $sysSession->room_id;
		$cont    = getNodeValue($dom, 'content');
		
		if (!$hongu->validate($cont)) {
			die('Access Denied. Possible XSS attack');
		}
                
		$tone    = intval(getNodeValue($dom, 'tone'));
		$reciver = getNodeValue($dom, 'reciver');
		$recname = getNodeValue($dom, 'reciver_name');
		$times   = getNodeValue($dom, 'dsc_times');

		$param   = split('[ ,:]+', trim($cont));
		$adm     = getChatAdmin();
		$host    = getChatHost();
		$cnt     = 0;
		$ary     = array();

		if ($host == $sysSession->username) {
			if ((count($param) <= 1) && empty($reciver)) {
				$RS = dbGetStMr('WM_chat_session', '`username`', "`rid`='{$rid}' AND `host`='N'", ADODB_FETCH_ASSOC);
				while (!$RS->EOF) {
					$param[] = $RS->fields['username'];
					$RS->MoveNext();
				}
			} else if (count($param) <= 1) {
				$param[] = $reciver;
			}

			switch ($param[0]) {
				case '@delete':   // 主持人的踢人功能
				case '@del':
				case '@kill':
					if ($param[1] == '@all') {
						$param = array();
						$param[] = '@delete';
						$RS = dbGetStMr('WM_chat_session', '`username`', "`rid`='{$rid}' AND `host`='N'", ADODB_FETCH_ASSOC);
						while (!$RS->EOF) {
							$param[] = $RS->fields['username'];
							$RS->MoveNext();
						}
					}

					for ($i = 1; $i < count($param); $i++) {
						if (empty($param[$i]) || ($adm == $param[$i])) continue;
						dbDel('WM_chat_session', "`rid`='{$rid}' AND `username`='{$param[$i]}'");
						if ($sysConn->Affected_Rows() > 0) {
							list($name) = dbGetStSr('WM_session', '`realname`', "`username`='{$param[$i]}'", ADODB_FETCH_NUM);
							setChatCont('', -2, 0, '', '', $param[$i], $name);
							$cnt++;
						}
					}   // End for ($i = 1; $i < count($param); $i++)
					break;

				case '@allow':   // 允許發言
					if ($param[1] == '@all') {
						$param = array();
						$param[] = '@allow';
						$RS = dbGetStMr('WM_chat_session', '`username`', "`rid`='{$rid}' AND `host`='N'", ADODB_FETCH_ASSOC);
						while (!$RS->EOF) {
							$param[] = $RS->fields['username'];
							$RS->MoveNext();
						}
					}

					for ($i = 1; $i < count($param); $i++) {
						if (empty($param[$i])) continue;
						dbSet('WM_chat_session', "`voice`='allow'", "`rid`='{$rid}' AND `username`='{$param[$i]}'");
						if ($sysConn->Affected_Rows() > 0) {
							list($name) = dbGetStSr('WM_session', '`realname`', "`username`='{$param[$i]}'", ADODB_FETCH_NUM);
							setChatCont($MSG['chat_voice'][$sysSession->lang], 5, 0, $param[$i], $name);
							$ary[] = $param[$i];
							$cnt++;
						}
					}   // End for ($i = 1; $i < count($param); $i++)
					if ($cnt > 0) echo 'syncMute("allow", "' . implode(',', $ary) . '");';
					break;

				case '@deny' :   // 禁止發言
				case '@mute' :
					if ($param[1] == '@all') {
						$param = array();
						$param[] = '@deny';
						$RS = dbGetStMr('WM_chat_session', '`username`', "`rid`='{$rid}' AND `username`!='{$adm}'", ADODB_FETCH_ASSOC);
						while (!$RS->EOF) {
							$param[] = $RS->fields['username'];
							$RS->MoveNext();
						}
					}

					for ($i = 1; $i < count($param); $i++) {
						if (empty($param[$i])) continue;
						dbSet('WM_chat_session', "`voice`='deny'", "`rid`='{$rid}' AND `host`='N'");
						if ($sysConn->Affected_Rows() > 0) {
							list($name) = dbGetStSr('WM_session', '`realname`', "`username`='{$param[$i]}'", ADODB_FETCH_NUM);
							setChatCont($MSG['chat_mute'][$sysSession->lang], 6, 0, $param[$i], $name);
							$ary[] = $param[$i];
							$cnt++;
						}
					}   // End for ($i = 1; $i < count($param); $i++)
					if ($cnt > 0) echo 'syncMute("deny", "' . implode(',', $ary) . '");';
					break;
				default:
					$res = setChatCont($cont, 2, $tone, $reciver, $recname);
			}   // End switch ($param[0])
		} else {   // End if (($adm != $reciver) && ($host == $sysSession->username))
			$res = setChatCont($cont, 2, $tone, $reciver, $recname);
		}

		if ($times <= 0) {
			$isCS = false;
			list($owner) = dbGetStSr('WM_chat_setting', '`owner`', "`rid`='{$rid}'", ADODB_FETCH_NUM);
			if ($owner == $sysSession->course_id) $isCS = true;
			$ress = strpos($owner, $sysSession->course_id);
			if (($ress !== false) && ($ress == 0)) $isCS = true;
			if ($isCS) {
				dbSet('WM_term_major', 'dsc_times=dsc_times+1', "username='{$sysSession->username}' and course_id='{$sysSession->course_id}'");
				dbSet('WM_term_course', 'dsc_times=dsc_times+1', "course_id='{$sysSession->course_id}'");
			}
		}
		//header("Content-type: text/xml");
		//echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		// echo ($res) ? 'true' : 'false';
	}
?>

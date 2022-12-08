<?php
	/**
	 * 回到 學生環境
	 * $Id: learn_relogin.php,v 1.1 2010/02/24 02:38:55 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// 查詢是否 為 某門課 的正式生 或旁聽生
	$c_student = aclCheckRole($sysSession->username, $sysRoles['student'] | $sysRoles['auditor']);

	$c_teacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);

	if ($c_student){	//   我的教室
		$go_tab = 'cur_func=10010101';
	}else if ($c_teacher){		// 我的辦公室
		$go_tab = 'cur_func=10010102';
	}else{		// 全校課程
		$go_tab = 'cur_func=10010103';
	}

	$userinfo = dbGetStSr('WM_user_account', '*', "username='{$sysSession->username}'", ADODB_FETCH_ASSOC);

	// 移除舊的 sysSession
	dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
	// 建立新的 sysSession
	$idx = $sysSession->init($userinfo);
	$_COOKIE['idx'] = $idx;
	dbSet('WM_session', $go_tab, "idx='{$_COOKIE['idx']}'");

	$sysSession->restore();

	$js = <<< BOF
	top.window.location.replace("/learn/index.php");
BOF;
      showXHTML_head_B($MSG['title'][$sysSession->lang]);
   	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
   	showXHTML_script('inline', $js);
   	showXHTML_head_E('');
   	showXHTML_body_B('');
   	showXHTML_body_E('');

?>

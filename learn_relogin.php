<?php
	/**
	 * �^�� �ǥ�����
	 * $Id: learn_relogin.php,v 1.1 2010/02/24 02:38:55 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// �d�߬O�_ �� �Y���� �������� �ή�ť��
	$c_student = aclCheckRole($sysSession->username, $sysRoles['student'] | $sysRoles['auditor']);

	$c_teacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);

	if ($c_student){	//   �ڪ��Ы�
		$go_tab = 'cur_func=10010101';
	}else if ($c_teacher){		// �ڪ��줽��
		$go_tab = 'cur_func=10010102';
	}else{		// ���սҵ{
		$go_tab = 'cur_func=10010103';
	}

	$userinfo = dbGetStSr('WM_user_account', '*', "username='{$sysSession->username}'", ADODB_FETCH_ASSOC);

	// �����ª� sysSession
	dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
	// �إ߷s�� sysSession
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

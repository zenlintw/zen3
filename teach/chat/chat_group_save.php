<?php
	/**
	 * �x�s�Q�׫ǳ]�w
	 *
	 * @since   2003/12/30
	 * @author  ShenTing Lin
	 * @version $Id: chat_group_save.php,v 1.1 2010/02/24 02:40:22 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$rid = trim($_POST['chat_id']);
	if (!ereg("[0-9A-Za-z]{13}", $rid))
	{
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '�Q�׫ǽs�����ŦX�W�h�I');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	// �ˬd ticket
	$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $rid);
	if (trim($_POST['ticket']) != $ticket)
	{
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '�ڵ��s��!');
		die($MSG['access_deny'][$sysSession->lang]);
	} 
	$lang['Big5']        = stripslashes(trim($_POST['host_room_name_big5']));
	$lang['GB2312']      = stripslashes(trim($_POST['host_room_name_gb']));
	$lang['en']          = stripslashes(trim($_POST['host_room_name_en']));
	$lang['EUC-JP']      = stripslashes(trim($_POST['host_room_name_jp']));
	$lang['user_define'] = stripslashes(trim($_POST['host_room_name_user']));
	$title               = addslashes(serialize($lang));
	$chat_jump           = isset($_POST['host_change'])                   ? 'allow'  : 'deny';
	$chat_media          = isset($_POST['enable_media'])                  ? 'enable' : 'disable';
	$chat_protocol       = (trim($_POST['host_media_protocol']) == 'tcp') ? 'TCP'    : 'UDP';
	$chat_login          = (trim($_POST['host_login']) == 'no')           ? 'N'      : 'Y';
	$host_limit          = intval($_POST['host_user_limit']);
	$host_exit           = trim($_POST['host_exit']);
	if (!in_array($host_exit, array('none', 'notebook', 'forum')))
	{
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $host_exit . '�Q�׫������᪺�ʧ@�Ȥ��b�\�i���d��');
		$host_exit = 'forum';
	}
	// �ثe�S���ϥ�
	// $host_media_ip = trim($_POST['host_media_ip']);
	$host_media_ip = '';
	$host_port     = intval(trim($_POST['host_media_port']));
	$host_root     = trim($_POST['host_root']);
	if (checkUsername($host_root, true) > 0)
	{
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $host_root . '�b�����ŦX�W�w');
		$host_root = '';
	}

    chkSchoolId('WM_chat_setting');
    // �N���ǲ߸��|�`�I�� <title> (�����o�� title)
	$old_title = $sysConn->GetOne('select title from WM_chat_setting where rid="' . $rid . '"');

	dbSet('WM_chat_setting',
		  "`title`='{$title}', `host`='{$host_root}', `get_host`='{$chat_login}', " .
		  "`maximum`={$host_limit}, `exit_action`='{$host_exit}', `jump`='{$chat_jump}', " .
		  "`media`='{$chat_media}', " .
		  "`ip`='{$host_media_ip}', `port`={$host_port}, `protocol`='{$chat_protocol}'",
		  "`rid`='{$rid}'"
	);
	$msg = ($sysConn->Affected_Rows() > 0) ? $MSG['msg_update_success'][$sysSession->lang] : $MSG['msg_update_fail'][$sysSession->lang];
	wmSysLog($sysSession->cur_func, $sysSession->course_id , $rid , 0, 'auto', $_SERVER['PHP_SELF'], 'update chat setting' . $msg);
	
    // �N���ǲ߸��|�`�I�� <title> begin
	if (($new_title = serialize($lang)) != $old_title)
	{
		$manifest = new SyncImsmanifestTitle(); // �����O�w�q�� db_initialize.php
		$manifest->replaceTitleForImsmanifest(7, $rid, $manifest->convToNodeTitle($new_title));
		$manifest->restoreImsmanifest();
	}
	// �N���ǲ߸��|�`�I�� <title> end

	
	$js = <<< BOF
	/**
	 * �^��޲z�C��
	 **/
	function goManage() {
		var obj = document.getElementById("actFm");
		if (obj != null) obj.submit();
	}

	window.onload = function () {
		alert("{$msg}");
	};
BOF;

	showXHTML_head_B($MSG['chat_save_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['tabs_save_chat'][$sysSession->lang], 'tabs_host');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1);
			// �D���H�]�w (Begin)
			$col = 'class="cssTrOdd"';
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="3"', $msg);
				showXHTML_tr_E();
				// ��ѫǦW��
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['host_msg_room_name'][$sysSession->lang]);
					showXHTML_td_B();
						$multi_lang = new Multi_lang(true, $lang, $col); // �h�y�t��J��
						$multi_lang->show(false);
					showXHTML_td_E();
				showXHTML_tr_E();
				
				// �H�ƭ���
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						echo $MSG['host_msg_user_limit1'][$sysSession->lang];
						echo $host_limit;
						echo $MSG['host_msg_user_limit2'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// ������ѫ�B�z��Ѥ��e
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('colspan="2"', $MSG['host_msg_exit'][$sysSession->lang] . $MSG['exit_act_' . $host_exit][$sysSession->lang]);
				showXHTML_tr_E();
				// ���\������ѫ�
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						$chk = ($chat_jump == 'allow') ? ' checked="checked" ' : '';
						showXHTML_input('checkbox', 'host_change', '', '', $chk . 'disabled="disabled"');
						echo $MSG['host_msg_allow_chg'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// �D���H�]�w
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('colspan="2"', $MSG['host_msg_host_set'][$sysSession->lang]);
				showXHTML_tr_E();
				// ��ѫǺ޲z��
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('colspan="2" style="padding-left: 30px;"', $MSG['host_msg_root'][$sysSession->lang] . $host_root);
				showXHTML_tr_E();
				// �n�J�ɬO�_���^�D���v
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
						echo $MSG['host_msg_login'][$sysSession->lang],
						     ($chat_login == 'Y' ? $MSG['yes'][$sysSession->lang] : $MSG['no'][$sysSession->lang]);
					showXHTML_td_E();
				showXHTML_tr_E();
				// ���}���s
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// �D���H�]�w (End)
		showXHTML_tabFrame_E();
		echo '</div>';
		showXHTML_form_B('action="chat_group_manage.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
			showXHTML_input('hidden', 'page', $_POST['page'], '', '');
			showXHTML_input('hidden', 'tid', $_POST['tid'], '', '');
		showXHTML_form_E();
	showXHTML_body_E();
?>

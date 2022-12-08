<?php
	/**
	 * ��ѫǳ]�w
	 *
	 * @since   2003/12/26
	 * @author  ShenTing Lin,Modify By Saly
	 * @version $Id: chat_group_property.php,v 1.1 2010/02/24 02:40:22 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '2000100300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$ticket = md5(sysTicketSeed . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '�ڵ��s��!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$dd = array(
		'title'      => '',
		'limit'      => 0 ,
		'exitAct'    => 'forum',
		'jump'       => 'deny',
		'media'      => 'disable',
		'ip'         => '',
		'port'       => '',
		'protocol'   => 'TCP',
		'host'       => $sysSession->username,
		'login'      => 'N'
	);

	$chatid = trim($_POST['chat_id']);
	// �ˬd $chatid �O���O�ŦX�ڭ̩ҭn�����
	if (!ereg("[0-9A-Za-z]{13}", $chatid))
	{
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '�Q�׫ǽs�����ŦX�W�h�I');
		die($MSG['access_deny'][$sysSession->lang]);
	}
	if (!empty($chatid))
	{
		$RS =  dbGetStSr('WM_chat_setting', '*', "`rid`='{$chatid}'", ADODB_FETCH_ASSOC);
		if ($RS)
		{
			$dd['title']      = $RS['title'];
			$dd['limit']      = intval($RS['maximum']);
			$dd['exitAct']    = trim($RS['exit_action']);
			$dd['jump']       = trim($RS['jump']);
			$dd['media']      = trim($RS['media']);
			$dd['ip']         = trim($RS['ip']);
			$dd['port']       = intval($RS['port']);
			$dd['protocol']   = trim($RS['protocol']);
			$dd['host']       = trim($RS['host']);
			$dd['login']      = trim($RS['get_host']);
		}
		else
		{
		    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '�䤣�즹�s�����Q�׫ǡI');
			die($MSG['access_deny'][$sysSession->lang]);
		}
	}
	else
	{
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '�Q�׫ǽs�����o���ťաI');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$js = <<< BOF
	function saveSetting() {
		var obj = document.getElementById("setFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		
		// �ˬd�Q�תO�W�٬O�_��g (check subject name)
		if (!chk_multi_lang_input(1, true, "{$MSG['msg_need_name'][$sysSession->lang]}")) return false;
		
		// �ˬd�Q�׫ǥD���H (check chatroom host)
		if (obj.host_root.value == "") {
			alert("{$MSG['msg_need_host'][$sysSession->lang]}");
			obj.host_root.focus();
			return false;
		}
		obj.submit();
	}

	/**
	 * �^��޲z�C��
	 **/
	function goManage() {
		var obj = document.getElementById("actFm");
		if (obj != null) obj.submit();
	}
BOF;

	showXHTML_head_B($MSG['chat_set_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array(
			array($MSG['tabs_chat_property'][$sysSession->lang], 'tabs_host')
		);
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'setFm', '', 'action="chat_group_save.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			// ��ѫǽs��
			showXHTML_input('hidden', 'chat_id', $chatid, '', '');
			$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $chatid);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			// �D���H�]�w (Begin)
			$col = 'class="cssTrOdd"';
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
				// ��ѫǦW��
				$lang = old_getCaption($dd['title']);
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$arr_names = array('Big5'		=>	'host_room_name_big5',
							   	   'GB2312'		=>	'host_room_name_gb',
							   	   'en'			=>	'host_room_name_en',
							   	   'EUC-JP'		=>	'host_room_name_jp',
							   	   'user_define'=>	'host_room_name_user'
							   	   );
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['host_msg_room_name'][$sysSession->lang]);
					showXHTML_td_B();
						$multi_lang = new Multi_lang(false, $lang, $col); // �h�y�t��J��
						$multi_lang->show(true, $arr_names);
					showXHTML_td_E();
				showXHTML_tr_E();
				
				// �H�ƭ���
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						echo $MSG['host_msg_user_limit1'][$sysSession->lang];
						showXHTML_input('text', 'host_user_limit', $dd['limit'], '', 'maxlength="5" class="cssInput" style="width: 30px;"');
						echo $MSG['host_msg_user_limit2'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// ������ѫ�B�z��Ѥ��e
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						$exitHost = array(
							'none'     => $MSG['exit_act_none'][$sysSession->lang],
							'notebook' => $MSG['exit_act_notebook'][$sysSession->lang],
							'forum'    => $MSG['exit_act_forum'][$sysSession->lang]);
						echo $MSG['host_msg_exit'][$sysSession->lang];
						showXHTML_input('select', 'host_exit', $exitHost, $dd['exitAct'], 'class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// ���\������ѫ�
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						$chk = ($dd['jump'] == 'allow') ? ' checked="checked"' : '';
						showXHTML_input('checkbox', 'host_change', '', '', $chk);
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
					showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
						echo $MSG['host_msg_root'][$sysSession->lang];
						showXHTML_input('text', 'host_root', $dd['host'], '', 'maxlength="32" class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// �n�J�ɬO�_���^�D���v
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
						echo $MSG['host_msg_login'][$sysSession->lang];
						$ary = array(
							'yes' => $MSG['yes'][$sysSession->lang],
							'no'  => $MSG['no'][$sysSession->lang],
						);
						$chk = ($dd['login'] == 'Y') ? 'yes' : 'no';
						showXHTML_input('radio', 'host_login', $ary, $chk, '');
					showXHTML_td_E();
				showXHTML_tr_E();
				// ���}���s
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn" onclick="saveSetting()"');
						echo '&nbsp;';
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="goManage()"');
						showXHTML_input('hidden', 'page', $_POST['page'], '', '');
						showXHTML_input('hidden', 'tid', $_POST['tid'], '', '');
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

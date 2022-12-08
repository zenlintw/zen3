<?php
	/**
	 * 儲存線上傳訊偏好設定
	 *
	 * @since   2003/11/11
	 * @author  ShenTing Lin
	 * @version $Id: msg_set1.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/msg_online.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2100100300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$im_status = array(
		0 => 'Offline'  ,
		1 => 'Online'   ,
		2 => 'Away'     ,
		3 => 'DND'      ,
		4 => 'Occupied' ,
		5 => 'Chat'     ,
		6 => 'Invisible',
		7 => 'Phone'    ,
		8 => 'Lunch'
		);

	$imReciver = intval($_POST['imReciver']);
	$imChat    = intval($_POST['imChat']);
	$imStatus  = intval($_POST['imStatus']);

	$reciver = ($imReciver == 0) ? 'Y' : 'N';
	$chat    = ($imChat == 0)    ? 'Y' : 'N';
	$status  = $im_status[$imStatus];

	dbSet('WM_im_setting', "`recive`='{$reciver}', `talk`='{$chat}', `status`='{$status}'", "`username`='{$sysSession->username}'");
	if ($sysConn->Affected_Rows() <= 0) {
		dbNew('WM_im_setting', '`username`, `recive`, `talk`, `status`', "'{$sysSession->username}', '{$reciver}', '{$chat}', '{$status}'");
		if ($sysConn->Affected_Rows() <= 0) {
			$msg = $MSG['msg_set_fail'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'new im setting fail');
		} else {
			$msg = $MSG['msg_set_success'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'new im setting success');
		}
	} else {
		$msg = $MSG['msg_set_success'][$sysSession->lang];
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'update im setting success');
	}

	$js = <<< BOF
	function msgUserList() {
		window.location.replace("/online/userlist.php");
	}
BOF;

	showXHTML_head_B($MSG['title_setting'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_setting'][$sysSession->lang], 'tabs1');
		showXHTML_tabFrame_B($ary, 1, '', '', 'action="msg_set1.php" style="display:inline;"');
			showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" id="tabs1" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="2" nowrap="nowrap"', $msg);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('nowrap="nowrap"' , $MSG['th_allow_revier'][$sysSession->lang]);
					$msg = ($imReciver == 0) ? $MSG['btn_reciver_yes'][$sysSession->lang] : $MSG['btn_reciver_no'][$sysSession->lang];
					showXHTML_td('', $msg);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td('nowrap="nowrap"', $MSG['th_allow_talk'][$sysSession->lang]);
					$msg = ($imChat == 0) ? $MSG['btn_talk_yes'][$sysSession->lang] : $MSG['btn_talk_no'][$sysSession->lang];
					showXHTML_td('', $msg);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('nowrap="nowrap"', $MSG['th_online_status'][$sysSession->lang]);
					$msg = ($imStatus == 1) ? $MSG['btn_status_online'][$sysSession->lang] : $MSG['btn_status_invisible'][$sysSession->lang];
					showXHTML_td('', $msg);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B('align="center" colspan="2" nowrap="nowrap"');
						showXHTML_input('button', '', $MSG['btn_goto_list'][$sysSession->lang]    , '', 'class="cssBtn" onclick="msgUserList()"');
						showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="parent.close()"');
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

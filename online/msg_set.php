<?php
	/**
	 * 線上傳訊偏好設定
	 *
	 * @since   2003/11/11
	 * @author  ShenTing Lin
	 * @version $Id: msg_set.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/msg_online.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '2100100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	list($reciver, $talk, $status) = dbGetStSr('WM_im_setting', '`recive`, `talk`, `status`', "`username`='{$sysSession->username}'", ADODB_FETCH_NUM);

	$js = <<< BOF
	function msgUserList() {
		window.location.replace("/online/userlist.php");
	}

	window.onload = function () {
		var obj = document.getElementById("tabs1");
		var xW = 400, xH = 200;
		xW = parseInt(obj.offsetWidth)  + 40;
		xH = parseInt(obj.offsetHeight) + 100;
		if (typeof(window.dialogWidth) == "undefined") {
			parent.window.resizeTo(xW, xH);
		} else {
			window.dialogWidth  = xW + "px";
			window.dialogHeight = xH + "px";
		}
	};
BOF;

	showXHTML_head_B($MSG['title_setting'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_setting'][$sysSession->lang], 'tabs1');
		showXHTML_tabFrame_B($ary, 1, '', '', 'method="post" action="msg_set1.php" style="display:inline;"');
			showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" id="tabs1" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="2" nowrap="nowrap"', $MSG['msg_setting_help'][$sysSession->lang]);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('nowrap="nowrap"' , $MSG['th_allow_revier'][$sysSession->lang]);
					showXHTML_td_B();
						$sel = array(
							0 => $MSG['btn_reciver_yes'][$sysSession->lang],
							1 => $MSG['btn_reciver_no'][$sysSession->lang]
						);
						$ck = ($reciver == 'N') ? 1 : 0;
						showXHTML_input('radio', 'imReciver', $sel, $ck, '', '');
					showXHTML_td_E();
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td('nowrap="nowrap"', $MSG['th_allow_talk'][$sysSession->lang]);
					showXHTML_td_B();
						$sel = array(
							0 => $MSG['btn_talk_yes'][$sysSession->lang],
							1 => $MSG['btn_talk_no'][$sysSession->lang]
						);
						$ck = ($talk == 'N') ? 1 : 0;
						showXHTML_input('radio', 'imChat', $sel, $ck, '', '');
					showXHTML_td_E();
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('nowrap="nowrap"', $MSG['th_online_status'][$sysSession->lang]);
					showXHTML_td_B('nowrap="nowrap"');
						$sel = array(
							1 => $MSG['btn_status_online'][$sysSession->lang],
							6 => $MSG['btn_status_invisible'][$sysSession->lang]
						);
						$im_status = array(
							'Offline'  => 0,
							'Online'   => 1,
							'Away'     => 2,
							'DND'      => 3,
							'Occupied' => 4,
							'Chat'     => 5,
							'Invisible'=> 6,
							'Phone'    => 7,
							'Lunch'    => 8
							);
						$ck = $im_status[$status];
						if ($ck == '') $ck = 1;
						showXHTML_input('radio', 'imStatus', $sel, $ck, '', '');
					showXHTML_td_E();
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B('align="center" colspan="2" nowrap="nowrap"');
						showXHTML_input('button', '', $MSG['btn_goto_list'][$sysSession->lang]    , '', 'class="cssBtn" onclick="msgUserList()"');
						showXHTML_input('submit', '', $MSG['btn_ok'][$sysSession->lang]    , '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

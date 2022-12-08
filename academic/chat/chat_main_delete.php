<?php
	/**
	 * �R����ѫ�
	 *
	 * @since   2003/12/31
	 * @author  ShenTing Lin
	 * @version $Id: chat_main_delete.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/breeze/global.php');

	$sysSession->cur_func='20001002000';
	$sysSession->restore();
	if (!aclVerifyPermission(2000100200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}
	
	// �����]�w $env
	if (!isset($env)) die($MSG['access_deny'][$sysSession->lang]);
	$env = trim($env);
	// ���o�Q�� cookie�Bpost �� get ��k�]�w $env
	$ary = array($_COOKIE['env'], $_POST['env'], $_GET['env']);
	if (in_array($env, $ary)) die($MSG['access_deny'][$sysSession->lang]);

	// �����]�w $owner_id
	if (!isset($owner_id)) die($MSG['access_deny'][$sysSession->lang]);
	$owner_id = trim($owner_id);
	// ���o�Q�� cookie�Bpost �� get ��k�]�w $owner_id
	$ary = array($_COOKIE['owner_id'], $_POST['owner_id'], $_GET['owner_id']);
	if (in_array($owner_id, $ary)) die($MSG['access_deny'][$sysSession->lang]);

	$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . 'delete');
	if (trim($_POST['ticket']) != $ticket) {
	    wmSysLog($sysSession->cur_func, 1, 'auto', $_SERVER['PHP_SELF'], '�ڵ��s��');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$rids = preg_split('/\W+/', $_POST['chat_ids'], -1, PREG_SPLIT_NO_EMPTY);
	$res = array();
	$strs = implode("','", $rids);
	$RS = dbGetStMr('WM_chat_setting AS ST LEFT JOIN WM_chat_session AS SE ON ST.rid = SE.rid',
					'ST.`rid`, ST.`title`, SUM(IF(ISNULL(SE.rid), 0, 1)) AS cnt',
					"ST.`rid` IN ('{$strs}') GROUP BY ST.`rid`, ST.`title`", ADODB_FETCH_ASSOC);
	while (!$RS->EOF) {
		$rid = $RS->fields['rid'];
		$lang = getCaption($RS->fields['title']);
		if ($RS->fields['cnt'] > 0) {
			$msg = $MSG['msg_now_active'][$sysSession->lang];
		} else {
			dbDel('WM_chat_setting', "`rid`='{$rid}'"); // ���ӭn�[�� owner ����w���@�I
			$msg = ($sysConn->Affected_Rows() > 0) ? $MSG['msg_del_success'][$sysSession->lang] : $MSG['msg_del_fail'][$sysSession->lang];
		}
		$res[] = array($lang[$sysSession->lang], $msg);
		$RS->MoveNext();
	}

	// �R��Breeze Meeting���ä[�ʷ|ĳ
	if (breeze == 'Y')
	{
		$RS = dbGetStMr('WM_chat_mmc', '`rid`, `meetingID`', "rid in ('{$strs}') and meetingType='breeze' and extra='eternal' ", ADODB_FETCH_ASSOC);
		while (!$RS->EOF)
		{
			list($scoid, $urlpath) = explode(':',$RS->fields['meetingID']);
			deleteScoResource($scoid);
			$RS->MoveNext();
		}
		dbDel('WM_chat_mmc', "rid in ('{$strs}') and meetingType='breeze' and extra='eternal'");
	}
	
	$js = <<< BOF
	/**
	 * �^��޲z�C��
	 **/
	function goManage() {
		window.location.replace("chat_manage.php");
	}
BOF;

	showXHTML_head_B($MSG['chat_delete_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['tabs_delete_chat'][$sysSession->lang], 'tabs_host');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1);
			$col = 'class="font01 bg04"';
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="box01"');
				$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
				showXHTML_tr_B($col);
					showXHTML_td('align="center"', $MSG['th_room_name'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_result'][$sysSession->lang]);
				showXHTML_tr_E();
				foreach ($res as $val) {
					$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
					showXHTML_tr_B($col);
						showXHTML_td('', $val[0]);
						showXHTML_td('', $val[1]);
					showXHTML_tr_E();
				}
				// ���}���s
				$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="button01" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

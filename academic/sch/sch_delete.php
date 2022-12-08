<?php
	/**
	 * 刪除一所學校
	 *
	 * 建立日期：2003//
	 * @author  ShenTing Lin
	 * @version $Id: sch_delete.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_manage.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func='100300400';
	$sysSession->restore();
	if (!aclVerifyPermission(100300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	/**
	 * 刪除的步驟
	 * 1. 將學校從 WM_school 中刪除此筆資料
	 * 2. 假如是最後一個校門則將跟學校有關的資料一起刪除
	 *    這包含了討論版、課程內容
	 **/

	/**
	 * 檢查車票是否正確
	 **/
	$_POST['sname']  = Filter_Spec_char($_POST['sname'], 'title');
	$_POST['shost']  = trim($_POST['shost']);
	$_POST['ticket'] = trim($_POST['ticket']);
	$res = preg_match('/^\w+(\.\w+)+$/', $_POST['shost'], $match);
	if (count($match) == 0)
	{
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'shost 格式錯誤!');
		die($MSG['access_deny'][$sysSession->lang]);
	}
	$sid = intval($_POST['sid']);
	$ticket = md5($sysSession->ticket . 'Delete' . $sysSession->username . $sid . $_POST['shost'] . $_POST['sname']);
	if (trim($_POST['ticket']) != $ticket) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$message = '';
	list($cnt) = dbGetStSr('WM_school', 'count(*)', "school_id='{$_POST[sid]}'", ADODB_FETCH_NUM);
	if (intval($cnt) <= 1) {
		// 刪除學校的 Database
		// $sysConn->Execute('DROP DATABASE ' . sysDBprefix . $sid);
		// 刪除學校的目錄
	}

	// dbDel('WM_school', "school_id='{$sid}' AND school_host='{$_POST['shost']}' AND school_name='{$_POST['sname']}'");
	dbSet('WM_school', "`school_host`='[delete]{$_POST['shost']}'", "school_id='{$sid}' AND school_host='{$_POST['shost']}' AND school_name='{$_POST['sname']}'");
	if (!$sysConn->Affected_Rows()) {
		$message = $MSG['msg_del_sch_fail'][$sysSession->lang];
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'manager', $_SERVER['PHP_SELF'], $message . ' school_id=' . $sid);
		$mustRestart = false;
	} else {
		$message = $MSG['msg_del_sch_success'][$sysSession->lang];
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], $message . ' school_id=' . $sid);

		include_once sysDocumentRoot . '/lib/wm3_config_class.php';
		$wm3_config = new WM3config;
		$wm3_config->reGenerateVirtualHostConfig();
        $mustRestart = true;
	}

	showXHTML_head_B($MSG['html_title_delete'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B('');

		echo '<div aling="center">';
		showXHTML_table_B('width="700" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['html_title_delete'][$sysSession->lang],  'tabsTag');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" class="bg01"');
					showXHTML_table_B('width="700" border="0" cellspacing="1" cellpadding="3" id="MySet" class="box01"');
						showXHTML_tr_B('class="font01 bg02"');
							showXHTML_td('colspan="3"', $message);
						showXHTML_tr_E('');

						showXHTML_tr_B('class="font01 bg03"');
							showXHTML_td('align="center"', $MSG['school_id'][$sysSession->lang]);
							showXHTML_td('align="center"', $MSG['school_name'][$sysSession->lang]);
							showXHTML_td('align="center"', 'Domain Name');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="font01 bg04"');
							showXHTML_td('align="center"', $sid);
							showXHTML_td('align="center"', $_POST['sname']);
							showXHTML_td('align="center"', $_POST['shost']);
						showXHTML_tr_E('');

						showXHTML_tr_B('class="font01 bg03"');
							showXHTML_td_B('colspan="3" align="center"');
								showXHTML_input('button', 'listBtn', $MSG['btn_return_list'][$sysSession->lang], '', 'class="button01" onclick="window.location.replace(\'sch_list.php\')"');
								if ($mustRestart)
								showXHTML_input('button', '', $MSG['restart_web_server'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'sch_restart.php?restart\')"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		echo '</div>';
	showXHTML_body_E('');
?>

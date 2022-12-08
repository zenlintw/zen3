<?php
	/**
	 * 儲存助教
	 *
	 * @since   2004/07/06
	 * @version $Id: director_save.php,v 1.1 2010/02/24 02:38:15 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/direct_member_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/character_class.php');

	$sysSession->cur_func = '2400200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket_add = md5(sysTicketSeed . 'Director_add'     . $_COOKIE['idx'] . $sysSession->username);
	$ticket_del = md5(sysTicketSeed . 'Delete_assistant' . $_COOKIE['idx'] . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket_add)
		$act = 'add';
	else if (trim($_POST['ticket']) == $ticket_del)
		$act = 'modify';
	else {
		wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'director', $_SERVER['PHP_SELF'], $MSG['msg_access_deny'][$sysSession->lang]);
		die('<script language="javascript">
				alert("' . $MSG['msg_access_deny'][$sysSession->lang] . '");
				location.replace("director_main.php");
		     </script>');
	}
	
	$result = array();
	$cids   = array();
	for ($i = 0; $i < count($_POST['user']); $i++) {
		$tmp_user = base64_decode($_POST['user'][$i]);
		$user_ary = explode(',',$tmp_user);
		if (!in_array($user_ary[0], $cids)) $cids[] = $user_ary[0];
		$result[] = array($user_ary[0], $user_ary[1], $user_ary[2], '');	// class_id, username, role, result
	}

	$class_names = dbGetAssoc('WM_class_main', 'class_id, caption', 'class_id in('.implode(',', $cids).')', ADODB_FETCH_ASSOC);
	for($i = 0; $i < count($result); $i++) 
	{
		if (!isset($class_names[$result[$i][0]]))	// 檢查班級是否存在
		{
			$result[$i][3] = $MSG['class_not_exit'][$sysSession->lang];
		}
		else if (!in_array($result[$i][2], array('assistant','director', 'DEL')))	// 檢查導師助教身分是否正確
		{
			$result[$i][3] = $MSG['class_role_wrong'][$sysSession->lang];
		}
		else	// 檢查帳號
		{
			$res = checkUsername($result[$i][1]);
			if ($result[$i][1] == sysRootAccount) $res = 4;
			switch(intval($res)) 
			{
				case 1:
				case 4:
					$result[$i][3] = $MSG['system_reserved'][$sysSession->lang];
					break;
				case 0:
				case 3:
					$result[$i][3] = $MSG['msg_username_not_exist'][$sysSession->lang];
					break;
				case 2:
					if ($result[$i][2] == 'DEL')
						WMdirector::assign($result[$i][1], $sysRoles['student'], $result[$i][0]);
					else
						WMdirector::assign($result[$i][1], $sysRoles[$result[$i][2]], $result[$i][0]);
					$result[$i][3] = $result[$i][2] == 'DEL' ? $MSG['msg_del_success'][$sysSession->lang] : $MSG['msg_modify_success'][$sysSession->lang];
					break;
			}
		}
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'academic', $_SERVER['PHP_SELF'], "username={$result[$i][1]} class_id={$result[$i][0]} add role={$result[$i][2]} result={$result[$i][3]}");
	}
	
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['go_academic_title'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td('align="center" width="200"', $MSG['th_username'][$sysSession->lang]);
						showXHTML_td('align="center" width="200"', $MSG['class'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_add_result'][$sysSession->lang]);
				showXHTML_tr_E();
				// 列出結果
				for($i = 0; $i < count($result); $i++)
				{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						$name = isset($class_names[$result[$i][0]]) ? getCaption($class_names[$result[$i][0]]) : array();
						showXHTML_td('', $result[$i][1]);
						showXHTML_td('', $name[$sysSession->lang]);
						showXHTML_td('', $result[$i][3]);
					showXHTML_tr_E();
				}
				// 按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('id="tools" align="center" colspan="3"');
							switch ($_POST['action']) {
								case 'ADD_CSV':	// 新增 - 匯入 csv
									$continue_href = 'javascript:window.location.href=\'director_add_csv.php\';';
									break;
								case 'ADD_CHOOSE_CLASS': // 先找出特定的班級 - 再新增班級導師
									$continue_href = 'javascript:window.location.href=\'director_choose_class.php\';';
									break;
								case 'ADD_CHOOSE_DIRECTOR':  // 先找到特定導師，再指定他要帶領的班級
									$continue_href = 'javascript:window.location.href=\'director_choose_director.php\';';
									break;
								case 'DEL_CSV':	// 卸除 - 匯入 csv
									$continue_href = 'javascript:window.location.href=\'director_add_csv.php?type=remove\';';
									break;
								case 'DEL_CHOOSE_CLASS':	// 先找到一個導師(或助理)，再將他所擔任的導師(或助理)職務卸除。
									$continue_href = 'javascript:window.location.href=\'director_choose_class.php?type=remove\';';
									break;
								case 'DEL_CHOOSE_DIRECTOR':  // 先找到一個班級，再將班級中的導師(或助理)職務卸除。
									$continue_href = 'javascript:window.location.href=\'director_choose_director1.php?type=remove\';';
									break;
							}
							showXHTML_input('button', 'back_go', $_POST['type'] == 'remove' ? $MSG['go_academic_del'][$sysSession->lang] : $MSG['go_academic'][$sysSession->lang], '', 'class="cssBtn" onclick=" ' . $continue_href . '"');
							showXHTML_input('button', 'back_main', $MSG['go_academic_end'][$sysSession->lang], '', 'class="cssBtn" onclick="javascript:window.location.href=\'director_main.php\';"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>
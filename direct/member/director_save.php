<?php
	/**
	 * 儲存助教
	 *
	 * @since   2004/07/06
	 * @author  ShenTing Lin
	 * @version $Id: director_save.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/direct_member_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/character_class.php');
	
	$ticket_add = md5(sysTicketSeed . 'Director_add'     . $_COOKIE['idx'] . $sysSession->username); // 新增助理
	$ticket_del = md5(sysTicketSeed . 'Delete_assistant' . $_COOKIE['idx'] . $sysSession->username); // 刪除助理
	$isDirector = aclCheckRole($sysSession->username, $sysRoles['director']);                        // 檢查是否有導師權限
	if (trim($_POST['ticket']) == $ticket_add && $isDirector)
		$act = 'add';
	else if (trim($_POST['ticket']) == $ticket_del && $isDirector)
		$act = 'del';
	else {
		wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 6, 'director', $_SERVER['PHP_SELF'], '拒絕存取!');
		die('<script language="javascript">
				alert("' . $MSG['msg_access_deny'][$sysSession->lang] . '");
				location.replace("director_list.php");
		     </script>');
	}
	
	$sysSession->cur_func = $act == 'add' ? '2400600100' : '300100500';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$result  = array();
	$ary     = preg_split('/[^\w.-]+/', $_POST['users'], -1, PREG_SPLIT_NO_EMPTY);
	$members = dbGetCol('WM_class_member', 'username', 'class_id=' . $sysSession->class_id . ' and username in("'.implode('","', $ary).'") and role&' . ($sysRoles['assistant']|$sysRoles['director']));
	foreach ($ary as $val) {
		$res = checkUsername($val);
		if ($val == sysRootAccount) $res = 4;
		if ((intval($res) == 2)) 
		{
			if ($act == 'add')
			{
				if (in_array($val, $members)) // 判斷是否已經有導師或助理權限
				{
					$result[$val] = $MSG['is_director_or_assistant'][$sysSession->lang];
				}
				else
				{
					WMdirector::assign($val, $sysRoles['assistant'], $sysSession->class_id);
					$result[$val] = $MSG['msg_add_success'][$sysSession->lang];
				}
			}
			else
			{
				if (in_array($val, $members)) // Bug#1441-若有助教身份的話，直接將其變更為「學員」，而非整個刪除帳號 by Small 2006/11/7
					WMdirector::assign($val, $sysRoles['student'], $sysSession->class_id);
				$result[$val] = $MSG['msg_del_success'][$sysSession->lang];
			}
		} 
		else if ($res == 0)
		{
			$result[$val] = $MSG['msg_username_not_exist'][$sysSession->lang];
		}
		else if ($res == 1 || $res == 4) 
		{
			$result[$val] = $MSG['system_reserved'][$sysSession->lang];
		}
		wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , $res == 2 ? 0 : ($res+1), 'director', $_SERVER['PHP_SELF'], "add assistant {$val} result={$result[$val]}");
	}

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		echo '<div align="center">';
		$ary = $act == 'add' ? array( array($MSG['tabs_director_save'][$sysSession->lang]  , 'tabs1') ) :
		                       array( array($MSG['tabs_director_delete'][$sysSession->lang], 'tabs1') ) ;
		showXHTML_tabFrame_B($ary, 1);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center" width="200"', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_add_result'][$sysSession->lang]);
				showXHTML_tr_E();
				// 列出結果
				foreach ($result as $key => $val) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $key);
						showXHTML_td('', $val);
					showXHTML_tr_E();
				}
				// 按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('id="tools" align="center" colspan="2"');
						showXHTML_input('button', 'rb', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'director_list.php\');"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

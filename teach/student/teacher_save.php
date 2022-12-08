<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Saly Lin                                                                         *
	*		Creation  : 2004/05/7                                                                      *
	*		work for  : 儲存 或 刪除 教師  授課名稱                                                     *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: teacher_save.php,v 1.2 2010/03/12 08:03:52 small Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teacher_settutor.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/character_class.php');

	$actType = '';
	$title   = '';
	$action  = '';

	$mask_role = ($sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'] | $sysRoles['student'] | $sysRoles['auditor']|$sysRoles['senior']|$sysRoles['paterfamilias']);

   // 新增老師 教授 課程 資料
	$ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Create';
		$ticket2 = '';
		$action  = 'teacher_new_course.php';
		$title   =  $MSG['add_teacher'][$sysSession->lang];
	}

	// 修改 老師 教授 課程 資料
	$ticket = md5($sysSession->ticket . 'Edit' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Edit';
		$action  = 'teacher_modify.php';
		$ticket2 = $_POST['ticket2'];
		$title   = $MSG['title2'][$sysSession->lang];
	}

   // 刪除老師 教授 課程 資料
	$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Delete' . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Delete';
		$ticket2 = '';
		$action  = 'teacher_list.php';
		$title   = $MSG['del_teacher'][$sysSession->lang];
	}

   if (empty($actType)) die($MSG['illege_access'][$sysSession->lang]);

   if ($actType == 'Create' || $actType == 'Edit')
   	  $sysSession->cur_func = '300100400';
   else
   	  $sysSession->cur_func = '300100500';
   $sysSession->restore();
   if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
   }

	$self_level = aclCheckRole($sysSession->username, ($sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant']), $sysSession->course_id, true) &
				  ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
   if (($self_level = array_search($self_level, $sysRoles)) === false) die('no teacher permission.');

	$all_users   = $sysConn->GetCol('select username from WM_user_account');			// 本校所有帳號

	$a = array($sysRoles['teacher']    => "{$MSG['teacher'][$sysSession->lang]}",
	           $sysRoles['instructor'] => "{$MSG['instructor'][$sysSession->lang]}",
	           $sysRoles['assistant']  => "{$MSG['assistant'][$sysSession->lang]}"
		       );

	$current_tas = $sysConn->GetCol("select username from WM_term_major where course_id={$sysSession->course_id} and role&" . ($sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant']));

	if ($actType == 'Create' || $actType == 'Edit')
	{
		$title = $MSG['add_teacher'][$sysSession->lang];
		$role = (int)($_POST['state'] == 'M' ? $sysRoles[$_POST['role']] : $sysRoles[$_POST['level']]);
		if (($role & ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'])) == 0) die('<script>alert(1);</script>');							//

		$reserved 	 = @file(sysDocumentRoot . '/config/reserve_username.txt');
		for($i = 0; $i < count($reserved); $i++)
			$reserved[$i] = trim(str_replace('*', '', $reserved[$i]));
		if (!in_array(sysRootAccount, $reserved))
			$reserved[] = sysRootAccount;

		$users		= preg_split('/[; ]+/', $_POST['username'], -1, PREG_SPLIT_NO_EMPTY);   // 輸入的帳號
		$not_existed= array_diff($users, $all_users);
		$users		= array_intersect($users, $all_users);                                  // 濾掉亂打的帳號
		$reserved	= array_intersect($users, $reserved);                                   // 濾掉系統保留帳號
		$users      = array_diff($users, $reserved);
		$existed    = array_intersect($users, $current_tas);                                // 已經是教師/助教/講師
		$users      = array_diff($users, $current_tas);
		if ($_POST['state'] == 'M') {
			$not_existed = $not_existed + $users;
			$users = $existed;
			$existed = array();
		}

		if (count($users) > 0) {
			$tech_obj = new WMteacher();
			
			$tech_obj->assign($users, $role, $sysSession->course_id);	// 呼叫指定身份 API

			$msg = implode(',', $users) . "\\n" . $MSG['assign_to'][$sysSession->lang] .$a[$role];

			// 設定功能編號
			dbSet('WM_session', 'cur_func=0300100400', "idx='{$_COOKIE['idx']}'");

			// 記錄到 WM_log_manager
        	wmSysLog($sysSession->cur_func,$sysSession->school_id,0,'0','teacher',$_SERVER['SCRIPT_FILENAME'],$msg);
		}
		/*
		if (count($reserved) != 0)
			$msg = implode(',', $reserved) . $MSG['system_reserved'][$sysSession->lang];
		else if (count($users) == 0)
			$msg = $MSG['title38'][$sysSession->lang];
		else {
			WMteacher::assign($users, $role, $sysSession->course_id);	// 呼叫指定身份 API

			$msg = implode(',', $users) . "\\n" . $MSG['assign_to'][$sysSession->lang] .$a[$role];

			// 設定功能編號
			dbSet('WM_session', 'cur_func=0300100400', "idx='{$_COOKIE['idx']}'");

			// 記錄到 WM_log_manager
        	wmSysLog($sysSession->cur_func,$sysSession->school_id,0,'0','teacher',$_SERVER['SCRIPT_FILENAME'],$msg);
		}
		*/

	}
	//  刪除 老師 教授 課程 資料 ( begin )
	elseif ($actType == 'Delete')
	{
		$title = $MSG['del_teacher'][$sysSession->lang];
		if (is_array($_POST['ckUname']) && count($_POST['ckUname']))
		{
			$legalRoles = array('teacher', 'instructor', 'assistant');
			$msg = '';
			$illegal_role = array();
			$not_existed = array();
			$users = array();
			foreach($_POST['ckUname'] as $item)
			{
				list($user, $role) = split('@', $item, 2);
				if (!in_array($role, $legalRoles)) {
					$illegal_role[] = $user;
					continue;
				}
				if (!in_array($user, $all_users)) {
					$not_existed = $user;
					continue;
				}
				if (!in_array($role, $legalRoles) || !in_array($user, $all_users)) continue;
				if (!in_array($role, $legalRoles) || !in_array($user, $all_users)) continue;
				$tech_obj = new WMteacher();
				$tech_obj->remove($user, $sysRoles[$role], $sysSession->course_id);	// 呼叫移除身份 API
				$users[$user] = $MSG['revoke'][$sysSession->lang] . $a[$sysRoles[$role]] . $MSG['status'][$sysSession->lang];
				$msg .= $user . $MSG['revoke'][$sysSession->lang] . $a[$sysRoles[$role]] . $MSG['status'][$sysSession->lang] ."\\n";
        	}
			// 設定功能編號
			dbSet('WM_session', 'cur_func=0300100500', "idx='{$_COOKIE['idx']}'");

			// 記錄到 WM_log_manager
			wmSysLog($sysSession->cur_func,$sysSession->school_id,0,'0','teacher',$_SERVER['SCRIPT_FILENAME'],$msg);
		}
	}

	$js = <<< BOF
	function goList() {
		document.getElementById("actForm").submit();
	}
BOF;

   // 開始呈現 HTML
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($title, 'tabs1');
		// $colspan = 'colspan="2"';
		echo '<div align="left">';
		showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
			showXHTML_table_B('width="380" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				if ($actType == 'Create' || $actType == 'Edit') {
					if (count($reserved) > 0) {
						$col = 'class="cssTrOdd"';
						showXHTML_tr_B('class="cssTrHelp"');
							showXHTML_td('colspan="2" align="left"', $MSG['th_reserved'][$sysSession->lang]);
						showXHTML_tr_E();
						foreach ($reserved as $v) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="5%"', '&nbsp;');
								showXHTML_td('', $v);
							showXHTML_tr_E();
						}
					}
					if (count($existed) > 0) {
						$col = 'class="cssTrOdd"';
						showXHTML_tr_B('class="cssTrHelp"');
							showXHTML_td('colspan="2" align="left"', $MSG['th_existed'][$sysSession->lang]);
						showXHTML_tr_E();
						foreach ($existed as $v) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="5%"', '&nbsp;');
								showXHTML_td('', $v);
							showXHTML_tr_E();
						}
					}
					if (count($not_existed) > 0) {
						$col = 'class="cssTrOdd"';
						showXHTML_tr_B('class="cssTrHelp"');
							showXHTML_td('colspan="2" align="left"', $MSG['th_not_existed'][$sysSession->lang]);
						showXHTML_tr_E();
						foreach ($not_existed as $v) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="5%"', '&nbsp;');
								showXHTML_td('', $v);
							showXHTML_tr_E();
						}
					}
					if (count($users) > 0) {
						$col = 'class="cssTrOdd"';
						showXHTML_tr_B('class="cssTrHelp"');
							if ($_POST['state'] == 'M') {
								$th = $MSG['assign_to'][$sysSession->lang] . $a[$role];
							} else {
								$th = ($role == 64) ? $MSG['th_add_assistant_success'][$sysSession->lang] : $MSG['th_add_instructor_success'][$sysSession->lang];
							}
							showXHTML_td('colspan="2" align="left"', $th);
						showXHTML_tr_E();
						foreach ($users as $v) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="5%"', '&nbsp;');
								showXHTML_td('', $v);
							showXHTML_tr_E();
						}
					}
				} else {
					if (count($illegal_role) > 0) {
						$col = 'class="cssTrOdd"';
						showXHTML_tr_B('class="cssTrHelp"');
							showXHTML_td('colspan="2" align="left"', $MSG['th_illegal_role'][$sysSession->lang]);
						showXHTML_tr_E();
						foreach ($illegal_role as $v) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="5%"', '&nbsp;');
								showXHTML_td('', $v);
							showXHTML_tr_E();
						}
					}
					if (count($not_existed) > 0) {
						$col = 'class="cssTrOdd"';
						showXHTML_tr_B('class="cssTrHelp"');
							showXHTML_td('colspan="2" align="left"', $MSG['th_not_existed'][$sysSession->lang]);
						showXHTML_tr_E();
						foreach ($not_existed as $v) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="5%"', '&nbsp;');
								showXHTML_td('', $v);
							showXHTML_tr_E();
						}
					}
					if (count($users) > 0) {
						$col = 'class="cssTrOdd"';
						showXHTML_tr_B('class="cssTrHelp"');
							showXHTML_td('colspan="2" align="left"', $MSG['del_teacher'][$sysSession->lang]);
						showXHTML_tr_E();
						foreach ($users as $k => $v) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="20%"', $k);
								showXHTML_td('', $v);
							showXHTML_tr_E();
						}
					}
				}
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['title6'][$sysSession->lang], '', 'onclick="goList();" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';

	showXHTML_form_B('action="teacher_list.php" method="post"', 'actForm');
		$page = intval($_POST['page_no']);
		if (empty($page)) $page = 1;
		showXHTML_input('hidden', 'page_no' , $page);
	showXHTML_form_E();
   showXHTML_body_E();
?>

<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2004/06/15                                                            *
	 *		work for  :                                             *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/academic_access.php');

	$sysSession->cur_func = '100400100';
	$sysSession->restore();
	if (!aclVerifyPermission(100400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!ereg('^[0-9]{9,}$', $_POST['function_id']))
		die("<script>alert('{$MSG['msg_errFuncId'][$sysSession->lang]}');history.back();</script>");

	$_POST['department_id'] = intval($_POST['department_id']);
	$_POST['element_id'] = intval($_POST['element_id']);

	if (implode('', $_POST['acl_caption']) == '')
		die("<script>alert('{$MSG['msg_captOneLang'][$sysSession->lang]}');history.back();</script>");
	else{
		if (get_magic_quotes_gpc())
			foreach($_POST['acl_caption'] as $k => $v) $_POST['acl_caption'][$k] = stripslashes($v);
		$title = addslashes(serialize($_POST['acl_caption']));
	}

	$permission = array_sum($_POST['permission']);

	if (!preg_match('/^\s*([\w-]+\s*)*$/U', $_POST['extra_member']))
		die("<script>alert('{$MSG['msg_numbContNotCorr'][$sysSession->lang]}');history.back();</script>");
	else
		$extra_member = preg_split('/\s+/', $_POST['extra_member'], -1, PREG_SPLIT_NO_EMPTY);

	if (($role = array_sum($_POST['role'])) == 0 && count($extra_member))
		die("<script>alert('{$MSG['msg_oneRoleOrNumb'][$sysSession->lang]}');history.back();</script>");
	$roles = explode(',', aclBitmap2Roles($role));

	if (get_magic_quotes_gpc())
		foreach($_POST['acl_caption'] as $k => $v)
			$_POST['acl_caption'][$k] = stripslashes($v);
	$caption = $sysConn->qstr(serialize($_POST['acl_caption']));

	// ========================================== 開始處理 ==============================================
	if (isset($_POST['acl_id'])){	// 修改 ACL
		$ticket = md5(sysTicketSeed . $_POST['acl_id'] . $_POST['function_id'] . $_COOKIE['idx']);
		if ($ticket != $_GET['ticket']) {
		   wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'manager', $_SERVER['PHP_SELF'], 'Fake ACL_ID!');
		   die('Fake ACL_ID.');
		}

		dbSet('WM_acl_list',
			  sprintf('permission=%d,caption=%s,function_id=%d,unit_id=%d,instance=%d',
  			  		  $permission,
			  		  $caption,
			  		  $_POST['function_id'],
			  		  $_POST['department_id'],
			  		  $_POST['element_id']),
			  'acl_id=' . intval($_POST['acl_id'])
			 );
		if ($sysConn->ErrorNo() == 0){
			$rs = dbGetStMr('WM_acl_member', 'member', 'acl_id=' . intval($_POST['acl_id']), ADODB_FETCH_ASSOC);
			if ($rs){
				$old_members = array(array(),array());
				while($fields = $rs->FetchRow())
					if (strpos($member, '#') !== 0)
						$old_members[0][] = $fields['member']; // 一般帳號
					else
						$old_members[1][] = substr($fields['member'], 1); // 系統群組 (以 # 為開頭)
			}
			foreach(array_diff($extra_member, $old_members[0]) as $member) // 新成員
				dbNew('WM_acl_member', 'acl_id,member', $_POST['acl_id'] . ",'$member'");
			foreach(array_diff($old_members[0], $extra_member) as $member) // 刪除的成員
				dbDel('WM_acl_member', sprintf('acl_id=%d and member="%s"', $_POST['acl_id'], $member));
			foreach(array_diff($roles, $old_members[1]) as $member)
				dbNew('WM_acl_member', 'acl_id,member', $_POST['acl_id'] . ",'#$member'");
			foreach(array_diff($old_members[1], $roles) as $member)
				dbDel('WM_acl_member', sprintf('acl_id=%d and member="#%s"', $_POST['acl_id'], $member));

			header('Location: access_maintain.php?' . $_POST['attribute']);
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], $MSG['mod_btn'][$sysSession->lang].' ACL sucess acl_id = '. $_POST['acl_id']);
		}
		else {
			$errMsg = $sysConn->ErrorMsg();
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 2, 'manager',$_SERVER['PHP_SELF'], $MSG['mod_btn'][$sysSession->lang].' ACL fail acl_id = '. $_POST['acl_id'] . $errMsg);
			die("<script>alert('{$MSG['mod_btn'][$sysSession->lang]} ACL {$MSG['msg_err'][$sysSession->lang]}" . $errMsg . "');history.back();</script>");
		}
	}
	else{			// 新增 ACL
		dbNew('WM_acl_list', 'permission,caption,function_id,unit_id,instance',
			  sprintf('%d, %s, %d, %d, %d',
			  		  $permission,
			  		  $caption,
			  		  $_POST['function_id'],
			  		  $_POST['department_id'],
			  		  $_POST['element_id'])
			 );
		if ($sysConn->ErrorNo() == 0){
			$newAcl_id = $sysConn->Insert_ID();
			foreach($extra_member as $member)
				dbNew('WM_acl_member', 'acl_id,member', $newAcl_id . ",'$member'");
			foreach($roles as $member)
				dbNew('WM_acl_member', 'acl_id,member', sprintf('%d,"#%s"', $newAcl_id, $member));
			header('Location: access_maintain.php?' . $_POST['attribute']);
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager',$_SERVER['PHP_SELF'], $MSG['msg_add'][$sysSession->lang].' ACL success acl_id = '. $newAcl_id);
		}
		else {
			$errMsg = $sysConn->ErrorMsg();
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 3, 'manager',$_SERVER['PHP_SELF'], $MSG['msg_add'][$sysSession->lang].' ACL fail '. $errMsg);
			die("<script>alert('{$MSG['msg_add'][$sysSession->lang]} ACL {$MSG['msg_err'][$sysSession->lang]}" . $errMsg . "');history.back();</script>");
		}
	}
?>

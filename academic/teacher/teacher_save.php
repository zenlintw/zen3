<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/16                                                                      *
	*		work for  : �x�s �� �R�� �Юv  �½ҦW��                                                     *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: teacher_save.php,v 1.1 2010/02/24 02:38:48 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teacher_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/character_class.php');

	$sysSession->cur_func = '700400100';
	$sysSession->restore();
	if (!aclVerifyPermission(700400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$actType     = '';
	$title       = '';
	$action      = '';

	$add_count   = 0;
	$exist_count = 0;

	$mask_role = ($sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'] | $sysRoles['student'] | $sysRoles['auditor']|$sysRoles['senior']|$sysRoles['paterfamilias']);

   // �s�W�Ѯv �б� �ҵ{ ���
	$ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);

	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Create';
		$ticket2 = '';
		$action  = 'teacher_new_course.php';
		$title   =  $MSG['add_teacher'][$sysSession->lang];
	}

	// �ק� �Ѯv �б� �ҵ{ ���
	$ticket = md5($sysSession->ticket . 'Edit' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);

	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Edit';
		$action  = 'teacher_modify.php';
		$ticket2 = $_POST['ticket2'];
		$title   = $MSG['title2'][$sysSession->lang];
	}

   if (empty($actType)) {
   	   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '�ڵ��s��!');
   	   die($MSG['illege_access'][$sysSession->lang]);
   }


	// �s�@�k begin    by Wiseguy
	chkSchoolId('WM_user_account');

	$_POST['level'] = trim($_POST['level']);
	if (ereg('^[0-9]+$', $_POST['level']) && in_array(intval($_POST['level']), $sysRoles))
		$role = intval($_POST['level']);
	elseif(isset($sysRoles[$_POST['level']]))
		$role = $sysRoles[$_POST['level']];
	if (($role & ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'])) == 0) die('<script>alert("' . $MSG['incorrect_role'][$sysSession->lang] . '"); history.back();</script>');

	$users       = preg_split('/[; ]+/', $_POST['username'], -1, PREG_SPLIT_NO_EMPTY);	// ��J���b��
	$all_users   = $sysConn->GetCol('select username from WM_user_account');			// ���թҦ��b��
	$users	     = array_intersect($users, $all_users);									// �o���å����b��
	if (count($users) == 0) die('<script>alert("' . $MSG['title38'][$sysSession->lang] . '"); history.back();</script>');

	if (empty($_POST['sel']) || !is_array($_POST['sel'])) die('<script>alert("' . $MSG['title13'][$sysSession->lang] . '"); history.back();</script>');
	$all_courses = $sysConn->GetCol('select course_id from WM_term_course');			// ���թҦ��ҵ{
	$courses     = array_intersect($_POST['sel'], $all_courses);						// �o���å��� ID
	if (count($courses) == 0) die('<script>alert("' . $MSG['title13'][$sysSession->lang] . '"); history.back();</script>');

	$locale_titles = WMteacher::getLocaleCaption($courses);								// ���o�ҵ{���W��

	if ($actType == 'Create' || $_POST['state'] == 'M')
	{
		if ($_POST['state'] == 'M') $action = 'teacher_modify.php';

		foreach($courses as $course_id)
			WMteacher::assign($users, $role, $course_id);	// �I�s���w���� API

		$a = array($sysRoles['teacher']    => $MSG['teacher'][$sysSession->lang],
				   $sysRoles['instructor'] => $MSG['instructor'][$sysSession->lang],
				   $sysRoles['assistant']  => $MSG['assistant'][$sysSession->lang]
		          );
		$msg = implode(',', $users) . $MSG['assign_to'][$sysSession->lang] . implode($MSG['assign_to1'][$sysSession->lang], $locale_titles) . $MSG['assign_to2'][$sysSession->lang] . $a[$role];

		// �]�w�\��s��
		if ($actType == 'Edit' && $_POST['state'] == 'M')
		{
			$function_id = '0300100300';
		}
		else if ($role == $sysRoles['assistant'])
		{
      		$function_id = '0300100400';
		}
		else
		{
      		$function_id = '0300100100';
		}
		dbSet('WM_session', 'cur_func=' . $function_id, "idx='{$_COOKIE['idx']}'");

		// �O���� WM_log_manager
        wmSysLog($function_id,$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$msg);

	}
	//  �R�� �Ѯv �б� �ҵ{ ��� ( begin )
	elseif (($actType == 'Edit') && ($_POST['state'] == 'D'))
	{
		foreach($courses as $course_id)
			WMteacher::remove($users, $role, $course_id);	// �I�s�������� API

		$msg = implode(',', $users) . $MSG['revoke'][$sysSession->lang] . implode($MSG['assign_to1'][$sysSession->lang], $locale_titles) . $MSG['assign_to2'][$sysSession->lang] . $a[$role];

		// �]�w�\��s��
		if ($role == $sysRoles['assistant'])
		{
			$function_id = '0300100500';
		}
		else
		{
			$function_id = '0300100200';
		}
		dbSet('WM_session', 'cur_func=' . $function_id, "idx='{$_COOKIE['idx']}'");

		// �O���� WM_log_manager
		wmSysLog($function_id,$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$msg);

	}
	// �s�@�k end


	//  �ק� �Ѯv �б� �ҵ{ ��� ( end )

	$js = <<< BOF
	window.onload = function () {
	   var obj = document.getElementById("actForm");
		alert('{$msg}');
		obj.submit();
	};

BOF;

   // �}�l�e�{ HTML
	showXHTML_head_B($title);
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
      showXHTML_form_B('action="' . $action . '" method="post"', 'actForm');
		   showXHTML_input('hidden', 'ticket', $ticket2, '', '');
		   showXHTML_input('hidden', 'username', $_POST['username'], '', '');
		   showXHTML_input('hidden', 'level', $_POST['level'], '', '');
		   showXHTML_input('hidden', 'page_no', $_POST['page_no'], '', '');
   		   showXHTML_input('hidden', 'cond_type', $_POST['cond_type'], '', '');
   		   showXHTML_input('hidden', 'queryTxt', htmlspecialchars(stripslashes(trim($_POST['queryTxt']))), '', '');
	   showXHTML_form_E('');
   showXHTML_body_E('');
?>

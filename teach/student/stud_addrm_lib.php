<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/18                                                            *
	 *		work for  : 新增/刪除 本課學員(刪除匯入帳號)                                                    *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *      $Id: stud_addrm_lib.php,v 1.1 2010/02/24 02:40:30 saly Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	
	// 判斷是否有教師或者助教權限
	$isTeacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['assistant'], $sysSession->course_id);
	if (!$isTeacher) {
		die('<script language="javascript">
		         alert("' . $MSG['illege_access'][$sysSession->lang] . '");
		         location.replace("stud_addrm.php");
		    </script>');
	}
	
	$log_msg   = '';	// 用來記log
	function displayItem($user, $proc, $state){
		static $classValue;
		global $sysSession, $_SERVER, $log_msg;
		$classValue = $classValue == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;

		    showXHTML_tr_B($classValue);
		      showXHTML_td('align="center" nowrap', $user);
		      showXHTML_td('align="center" nowrap', $proc);
		      showXHTML_td('align="center" nowrap', $state);
		    showXHTML_tr_E();
		
		$log_msg .= $user . $proc . $state . ',';
	}

	$messages = array($MSG['add_student'][$sysSession->lang],
					  $MSG['add_auditor'][$sysSession->lang],
					  $MSG['and_student'][$sysSession->lang],
					  $MSG['and_auditor'][$sysSession->lang],
					  $MSG['aud2stu'][$sysSession->lang],
					  $MSG['stu2aud'][$sysSession->lang],
					  $MSG['already_is_student'][$sysSession->lang],
					  $MSG['already_is_auditor'][$sysSession->lang],
					  $MSG['complete'][$sysSession->lang],
					  $MSG['failure'][$sysSession->lang],
					  $MSG['unknown_user'][$sysSession->lang],
					  $MSG['format_incorrect'][$sysSession->lang],
					  $MSG['reserve_used'][$sysSession->lang],
					  $MSG['reserve'][$sysSession->lang],
					  $MSG['system_reserved'][$sysSession->lang],
					  $MSG['not_student_of_curr_course'][$sysSession->lang],
					  $MSG['not_student'][$sysSession->lang],
					  $MSG['not_auditor'][$sysSession->lang]
					 );

	showXHTML_head_B($MSG['addrm'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
	  showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
	    showXHTML_tr_B();
	      showXHTML_td_B();
	        $ary[] = array($MSG['addrm'][$sysSession->lang], 'tabsSet',  '');
	        showXHTML_tabs($ary, 1);
	      showXHTML_td_E();
	    showXHTML_tr_E();
	    showXHTML_tr_B();
	      showXHTML_td_B('valign="top" class="bg01"');

		  showXHTML_table_B('width="380" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');
		    showXHTML_tr_B('class="bg02 font01"');
		      showXHTML_td('align="center" nowrap', $MSG['account'][$sysSession->lang]);
		      showXHTML_td('align="center" nowrap', $MSG['process'][$sysSession->lang]);
		      showXHTML_td('align="center" nowrap', $MSG['result'][$sysSession->lang]);
		    showXHTML_tr_E();

	switch($_POST['op']){
		case '1': // 加入正式生
			$mask = $sysRoles['all'] ^ $sysRoles['auditor'];
			for($i=0; $i<$length; $i++){
				$result = checkUsername($users[$i]);
				if ($users[$i] == sysRootAccount) $result = 4;
				if ($result == 2){
					dbNew('WM_term_major', 'username,course_id,role,add_time', "'{$users[$i]}',{$sysSession->course_id},{$sysRoles['student']},NOW()");
					if ($sysConn->ErrorNo() == 1062){ // 如果已經有本課某種身份
						dbSet('WM_term_major', "role=role & $mask | {$sysRoles['student']},add_time=NOW()", "username='{$users[$i]}' and course_id={$sysSession->course_id}");
						if ($sysConn->ErrorNo() == 0)
							displayItem($users[$i], $messages[2], $messages[8]);
						else
							displayItem($users[$i], $messages[2], $messages[9]);
					}
					else{
						displayItem($users[$i], $messages[0], $messages[8]);
					}
				}else if ($result == 0){
					displayItem($users[$i], $messages[0], $messages[10]);
				}else if ($result == 1){
					displayItem($users[$i], $messages[0], $messages[14]);
				}else if ($result == 3){
					displayItem($users[$i], $messages[0], $messages[11]);
				}else if ($result == 4){
					displayItem($users[$i], $messages[0], $messages[14]);
				}
			}
			break;
		case '2': // 加入旁聽生
			$mask = $sysRoles['all'] ^ $sysRoles['student'];
			for($i=0; $i<$length; $i++){
				$result = checkUsername($users[$i]);
				if ($users[$i] == sysRootAccount) $result = 4;
				if ($result == 2){
					dbNew('WM_term_major', 'username,course_id,role,add_time', "'{$users[$i]}',{$sysSession->course_id},{$sysRoles['auditor']},NOW()");
					if ($sysConn->ErrorNo() == 1062){ // 如果已經有本課某種身份
						dbSet('WM_term_major', "role=role & $mask | {$sysRoles['auditor']},add_time=NOW()", "username='{$users[$i]}' and course_id={$sysSession->course_id}");
						if ($sysConn->ErrorNo() == 0)
							displayItem($users[$i], $messages[3], $messages[8]);
						else
							displayItem($users[$i], $messages[3], $messages[9]);
					}
					else{
						displayItem($users[$i], $messages[1], $messages[8]);
					}
				}else if ($result == 0){
					displayItem($users[$i], $messages[1], $messages[10]);
				}else if ($result == 1){
					displayItem($users[$i], $messages[1], $messages[14]);
				}else if ($result == 3){
					displayItem($users[$i], $messages[1], $messages[11]);
				}else if ($result == 4){
					displayItem($users[$i], $messages[1], $messages[14]);
				}
			}
			break;
		case '3': // 旁聽生轉為正式生
			$mask = $sysRoles['all'] ^ $sysRoles['auditor'];
			for($i=0; $i<$length; $i++){
				$result = checkUsername($users[$i]);
				if ($users[$i] == sysRootAccount) $result = 4;
				if ($result == 2){
					if (aclCheckRole($users[$i], $sysRoles['auditor'], $sysSession->course_id))
					{
						dbSet('WM_term_major', "role=role & $mask | {$sysRoles['student']},add_time=NOW()", "username='{$users[$i]}' and course_id={$sysSession->course_id} and role&{$sysRoles['auditor']} limit 1");
						displayItem($users[$i], $messages[4], $messages[8]);
					}
					else
						displayItem($users[$i], $messages[4], $messages[17]);
				}else if ($result == 0){
					displayItem($users[$i], $messages[4], $messages[10]);
				}else if ($result == 1){
					displayItem($users[$i], $messages[4], $messages[14]);
				}else if ($result == 3){
					displayItem($users[$i], $messages[4], $messages[11]);
				}else if ($result == 4){
					displayItem($users[$i], $messages[4], $messages[14]);
				}
			}
			break;
		case '4': // 正式生轉為旁聽生
			$mask = $sysRoles['all'] ^ $sysRoles['student'];
			for($i=0; $i<$length; $i++){
				$result = checkUsername($users[$i]);
				if ($users[$i] == sysRootAccount) $result = 4;
				if ($result == 2){
					if (aclCheckRole($users[$i], $sysRoles['student'], $sysSession->course_id))
					{
						dbSet('WM_term_major', "role=role & $mask | {$sysRoles['auditor']},add_time=NOW()", "username='{$users[$i]}' and course_id={$sysSession->course_id} and role&{$sysRoles['student']} limit 1");
						displayItem($users[$i], $messages[5], $messages[8]);
					}
					else
						displayItem($users[$i], $messages[5], $messages[16]);
				}else if ($result == 0){
					displayItem($users[$i], $messages[5], $messages[10]);
				}else if ($result == 1){
					displayItem($users[$i], $messages[5], $messages[14]);
				}else if ($result == 3){
					displayItem($users[$i], $messages[5], $messages[11]);
				}else if ($result == 4){
					displayItem($users[$i], $messages[5], $messages[14]);
				}
			}
			break;
		case '5': // 移除
			include_once(sysDocumentRoot . '/lib/lib_stud_rm.php');
			$last = $length-1;
			for($i=0; $i<$length; $i++){
				
				$result = DelStudentAll($sysSession->course_id,$users[$i],$i==$last);
				switch ($result)
				{
					case 0:		
						displayItem($users[$i], $MSG['remove'][$sysSession->lang], $messages[8]);		
						break;
					case 1:
						displayItem($users[$i], $MSG['remove'][$sysSession->lang], $messages[15]);
						break;
					case 2:
						displayItem($users[$i], $MSG['remove'][$sysSession->lang], $messages[10]);
						break;
					case 3:
						displayItem($users[$i], $MSG['remove'][$sysSession->lang], $messages[14]);
						break;
					case 4:
						displayItem($users[$i], $MSG['remove'][$sysSession->lang], $messages[11]);
						break;
					case 5:
						displayItem($users[$i], $MSG['remove'][$sysSession->lang], $messages[14]);
						break;
				}
			}
			dbDel('WM_term_major', 'role=0');
			break;
	}
			    showXHTML_tr_B('');
			      showXHTML_td_B('colspan="3" align="center"');
					if (strpos($_SERVER['PHP_SELF'], '_chk_') !== FALSE)
						showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'stud_addrm.php?4'.((isset($_POST['cIdx']) && $_POST['cIdx']!='')?('&cIdx='.$_POST['cIdx']):'').'\');"');
					else
			        	showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'stud_addrm.php\');"');
			      showXHTML_td_E();
			    showXHTML_tr_E();

	          showXHTML_table_E();

	      showXHTML_td_E();
	    showXHTML_tr_E();
	  showXHTML_table_E();
	showXHTML_body_E();
	
	if ($log_msg != '')
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], $log_msg);
?>

<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/30                                                            *
	 *		work for  :                                                                       *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *      $Id: stud_groups_addtimes.php,v 1.1 2010/02/24 02:40:31 saly Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '1000100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$post_lang_str = '';
	$alert_msg = '';
	switch ($sysSession->lang){
		case 'Big5':
			$post_lang_str = 'team_name_big5';
			$alert_msg = $MSG['please_input_Big5'][$sysSession->lang];
			break;
		case 'en':
			$post_lang_str = 'team_name_en';
			$alert_msg = $MSG['please_input_en'][$sysSession->lang];
			break;
		case 'GB2312':
			$post_lang_str = 'team_name_gb';
			$alert_msg = $MSG['please_input_GB2312'][$sysSession->lang];
			break;
		case 'EUC-JP':
			$post_lang_str = 'team_name_jp';
			$alert_msg = $MSG['please_input_EUC_JP'][$sysSession->lang];
			break;
		case 'user_define':
			$post_lang_str = 'team_name_ud';
			$alert_msg = $MSG['please_input_user_define'][$sysSession->lang];
			break;
	}

	if (strlen(trim($_POST[$post_lang_str])) == 0){
		echo '<script type="text/javascript" language="javascript">',
		     "alert('$alert_msg');\n",
		     "window.location.replace('stud_groups.php');\n",
		     '</script>';
	}else{
		$tean_name = array('Big5'        => stripslashes(trim($_POST['team_name_big5'])),
		                   'GB2312'      => stripslashes(trim($_POST['team_name_gb']  )),
		                   'en'          => stripslashes(trim($_POST['team_name_en']  )),
		                   'EUC-JP'      => stripslashes(trim($_POST['team_name_jp']  )),
		                   'user_define' => stripslashes(trim($_POST['team_name_ud']  ))
		                   );
		$tean_name = addslashes(serialize($tean_name));
		list($maxTeamId) = dbGetStSr('WM_student_separate', 'MAX(team_id)', "course_id={$sysSession->course_id}", ADODB_FETCH_NUM);
		$nextTeamId = intval($maxTeamId) + 1;
		dbNew('WM_student_separate',
		      'course_id,team_id, team_name',
		      "{$sysSession->course_id},$nextTeamId,'" . $tean_name . "'"
		     );
		if ($sysConn->ErrorNo() > 0) die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());

		wmSysLog($sysSession->cur_func, $sysSession->course_id , $nextTeamId , 0, 'auto', $_SERVER['PHP_SELF'], 'Student groups addtimes:'.$nextTeamId);
		
		echo '<script type="text/javascript" language="javascript">',
		     "alert('{$MSG['save_ok'][$sysSession->lang]}');\n",
		     "window.location.replace('stud_groups.php?tid=$nextTeamId&order=3');\n",
		     '</script>';
	}

?>

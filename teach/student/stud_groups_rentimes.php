<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *      Programmer: Wiseguy Liang                                                                 *
	 *      Creation  : 2003/08/10                                                                    *
	 *      work for  :                                                                               *
	 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1000100200';
	$sysSession->restore();
	if (!aclVerifyPermission(1000100200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

/*
	$course_id = $sysSession->course_id; //10000000;
	if (!isset($_SERVER['argv'][0])) die('Access denied.');					// 檢查 ticket 是否存在
	$ticket_head = sysTicketSeed . $course_id . $_SERVER['argv'][1] . $_SERVER['argv'][2];
	if (md5($ticket_head) != $_SERVER['argv'][0]) die('Fake ticket.');			// 檢查 ticket
*/

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
		echo '<script type="text/javascript" language="javascript">';
		echo "alert(\"{$alert_msg}\");\n";
		echo "window.location.replace(\"stud_groups.php\");\n";
		echo '</script>';
	}else if ((strlen($_POST[$post_lang_str]) >= 0) && ereg('^[0-9]+$', $_POST['team_id'])){
		$tean_name = array('Big5'        => stripslashes(trim($_POST['team_name_big5'])),
						   'GB2312'      => stripslashes(trim($_POST['team_name_gb'])),
						   'en'          => stripslashes(trim($_POST['team_name_en'])),
						   'EUC-JP'      => stripslashes(trim($_POST['team_name_jp'])),
						   'user_define' => stripslashes(trim($_POST['team_name_ud']))
						  );

		dbSet('WM_student_separate',
		      "team_name='" . addslashes(serialize($tean_name)) . "'",
		      "course_id={$sysSession->course_id} and team_id={$_POST['team_id']}");
		if ($sysConn->ErrorNo() > 0) die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['team_id'] , 0, 'auto', $_SERVER['PHP_SELF'], 'Student groups rename times:'.$_POST['team_id']);
	}

	header('Location: stud_groups.php?tid=' . $_POST['team_id'] . '&order=3');
?>

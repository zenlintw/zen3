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
	require_once(PEAR_INSTALL_DIR . '/System.php');

	$sysSession->cur_func = '1000100300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	function destroyBoard($bid)
	{
		global $sysSession;
		// 刪除夾檔 (Begin)
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/board/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/quint/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/board/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		$path = sysDocumentRoot . "/base/{$sysSession->school_id}/quint/{$bid}";
		if (is_dir($path)) @System::rm("-rf {$path}");
		// 刪除夾檔 (End)
		// 刪除張貼
		dbDel('WM_bbs_posts', "`board_id`={$bid}");
		dbDel('WM_bbs_order', "`board_id`={$bid}");
		dbDel('WM_bbs_collecting', "`board_id`={$bid}");
		dbDel('WM_bbs_ranking', "`board_id`={$bid}");
		dbDel('WM_bbs_readed', "`board_id`={$bid}");
	}

if (count($_POST['tlist'])>0) {
	foreach($_POST['tlist'] as $team_id){
		if (preg_match('/^\d+$/', $team_id)){
			// 刪除討論版及討論室
			$RS = dbGetStMr('WM_student_group', 'group_id,board_id', "course_id={$sysSession->course_id} and team_id={$team_id}", ADODB_FETCH_ASSOC);
			if ($sysConn->ErrorNo() > 0) {
			   $errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
			   wmSysLog($sysSession->cur_func, $sysSession->course_id , $team_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Student groups del times result:' . $errMsg);
			   die($errMsg);
			}
			while(!$RS->EOF){
				dbDel('WM_bbs_boards', "board_id={$RS->fields['board_id']}");
				destroyBoard($RS->fields['board_id']);
				$owners = $sysSession->course_id.'_'.$team_id.'_'.$RS->fields['group_id'];
				list($rids) = dbGetStSr('WM_chat_setting', 'rid', "owner='{$owners}'", ADODB_FETCH_NUM);
				if(isset($rids)){
					dbDel('WM_chat_setting', "rid='{$rids}' and owner='{$owners}'");
					dbDel('WM_chat_session', "rid='{$rids}'");
				}
				$RS->MoveNext();
			}
			$condition = "course_id={$sysSession->course_id} and team_id={$team_id}";
			dbDel('WM_student_separate', $condition); // 刪除分組次
			dbDel('WM_student_group',    $condition); // 刪除群組
			dbDel('WM_student_div',      $condition); // 刪除群組成員
		}
	}
	wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'Student groups del times:'. implode(',', $_POST['tlist']));
}
	header('Location: stud_groups.php?order=3');
?>

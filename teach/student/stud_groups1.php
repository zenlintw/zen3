<?php
	/********************************************************************************************
	 *                                                                                          *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C    						*
	 *																							*
	 *		Programmer: Wiseguy Liang															*
	 *		Creation  : 2003/06/22																*
	 *		work for  :																			*
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1									*
	 *		$Id: stud_groups1.php,v 1.2 2009-07-09 10:02:15 edi Exp $																					*
	 ********************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(PEAR_INSTALL_DIR . '/System.php');

	$course_id = $sysSession->course_id; //10000000;
	$acl_function = 1000400300;
	$sysSession->cur_func = '1000400300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	function cutRealname(&$item){
		list($item) = explode(chr(9), $item, 2);
	}

	function genCaption($str){
		$title = explode(chr(12), $str, 5);
		$titles = array('Big5'        => stripslashes(trim($title[0])),
				'GB2312'      => stripslashes(trim($title[1])),
				'en' 	      => stripslashes(trim($title[2])),
				'EUC-JP'      => stripslashes(trim($title[3])),
				'user_define' => stripslashes(trim($title[4])));

		return addslashes(serialize($titles));
	}

    $_POST['team_id'] = intval($_POST['team_id']);
	if (!isset($_POST['results'])) {
		header('Location: stud_groups.php?tid=' . $_POST['team_id']);
		die();
	}


	$curGroups = explode("\n", $_POST['results']);
	$acl_lists = explode(Chr(12), get_magic_quotes_gpc()?stripslashes($_POST['acl_lists']):$_POST['acl_lists']);

	foreach($curGroups as $item) $newGroups[] = explode('&loz;', $item);
	unset($curGroups);

	for($i=0; $i<count($newGroups); $i++) $newGroups[$i][3] = explode(chr(8), trim($newGroups[$i][3]));

	$old_groups = array(); $max_gid = 0;
	$RS = dbGetStMr('WM_student_group', 'group_id', "course_id={$sysSession->course_id} and team_id={$_POST['team_id']}", ADODB_FETCH_ASSOC);
	if ($RS) while(!$RS->EOF) {
		$old_groups[$RS->fields['group_id']] = 1;
		if (intval($RS->fields['group_id']) > $max_gid) $max_gid = intval($RS->fields['group_id']);
		$RS->MoveNext();
	}

	dbDel('WM_student_div', "course_id={$sysSession->course_id} and team_id={$_POST['team_id']}");
	$int = 0;
	$all_groups = array();
	$log_add_groups = '';
	$log_modify_groups = '';
	// foreach($newGroups as $group){
	for($i=0;$i<count($newGroups);$i++) {
		$group = $newGroups[$i];
		$int++;
		$group[1] = genCaption($group[1]);
		if ( intval($group[0]) < 9001){ // 舊有組別
			if (strlen(trim($group[3][0]))>0){	// 代表此群組內有學員
				$thisGroup = "course_id={$sysSession->course_id} and group_id={$group[0]} and team_id={$_POST['team_id']}";
				$chat_owner = $sysSession->course_id.'_'.$_POST['team_id'].'_'.$group[0];
				// 變更組名及組長
				if ($group[1]) {
					dbSet('WM_student_group', "caption='{$group[1]}',captain='{$group[2]}',permute={$int}", $thisGroup);
					if ($sysConn->Affected_Rows() > 0) 
						$log_modify_groups .= $log_modify_groups == '' ? $group[0] : (', ' . $group[0]);
					// 變更討論室主持人,若聊天室沒有主持人的話,則順便更新主持人上去,若有值,則不用動作
					list($host_name) = dbGetStSr('WM_chat_setting', 'host', "owner='$chat_owner'", ADODB_FETCH_NUM);
					if(isset($host_name) && (strlen($host_name)==0))
						dbSet('WM_chat_setting', "host='{$group[2]}'", "owner='$chat_owner'");
					// 變更討論板及討論室名稱
					// dbSet('WM_student_group,WM_bbs_boards',"WM_bbs_boards.bname='{$group[1]}'", "WM_student_group.course_id={$sysSession->course_id} and WM_student_group.group_id={$group[0]} and WM_student_group.team_id={$_POST['team_id']} and WM_student_group.board_id=WM_bbs_boards.board_id");
					// dbSet('WM_chat_setting', "title='{$group[1]}'", "owner='$chat_owner'");
				}
				unset($old_groups[$group[0]]);
				$all_groups[] = $group[0];
			}
		}
		else{
			if (strlen(trim($group[3][0]))>0){	// 代表此群組內有學員
				$max_gid++;
				// 新增組別
				dbNew('WM_student_group', 'course_id,group_id,team_id,caption,captain,permute',
				      "{$sysSession->course_id},$max_gid,{$_POST['team_id']},'{$group[1]}', '{$group[2]}', {$int}");
				// 新增群組討論板
				dbNew('WM_bbs_boards','bname, owner_id',sprintf("'%s', %08d%04d%04d", $group[1], $sysSession->course_id, $_POST['team_id'], $max_gid));
				if ($sysConn->Affected_Rows() > 0) {
					$bid = $sysConn->Insert_ID();
					dbSet('WM_student_group', "board_id={$bid}", "course_id={$sysSession->course_id} and group_id={$max_gid} and team_id={$_POST['team_id']}");
				}
				// 新增群組討論室
				$owners = $sysSession->course_id.'_'.$_POST['team_id'].'_'.$max_gid;
				$newrid = uniqid('');
				dbNew('WM_chat_setting','rid,owner,title,host', "'$newrid','$owners','$group[1]','$group[2]'");
				$group[0] = $max_gid;
				$all_groups[] = $group[0];
				$log_add_groups .= $log_add_groups == '' ? $group[0] : (', ' . $group[0]);
			} else {
				unset($acl_lists[$i]);
			}
		}
		$new_member = $group[3]; array_walk($new_member, 'cutRealname');
		$users = '';
		foreach($new_member as $user) {
			if (empty($user)) continue;
			$users .= "{$sysSession->course_id},{$group[0]},{$_POST['team_id']},'$user'),(";
		}
		if ($user != '')
			dbNew('WM_student_div', 'course_id,group_id,team_id,username', substr($users, 0, -3));
	}

	if ($log_add_groups != '') wmSysLog('1000300100', $sysSession->course_id , $_POST['team_id'] , 0, 'auto', $_SERVER['PHP_SELF'], iconv('Big5', 'UTF-8', '新增學員分組：') . $log_add_groups);
	if ($log_modify_groups != '') wmSysLog('1000300200', $sysSession->course_id , $_POST['team_id'] , 0, 'auto', $_SERVER['PHP_SELF'], iconv('Big5', 'UTF-8', '修改學員分組：') . $log_modify_groups);

	// 刪掉已移除的 group,及相關的討論板/討論室
	if (count($old_groups)){
		dbDel('WM_student_div', "course_id={$sysSession->course_id} and group_id in (" .
				      			implode(',', array_keys($old_groups)) .
				      			") and team_id={$_POST['team_id']}");
		$olds = implode(',', array_keys($old_groups));
		$olds = explode(',',$olds);
		for ($i=0;$i<count($olds);$i++){
			$owners = $sysSession->course_id.'_'.$_POST['team_id'].'_'.$olds[$i];
			list($bid) = dbGetStSr('WM_student_group,WM_bbs_boards', 'WM_bbs_boards.board_id', "WM_student_group.board_id=WM_bbs_boards.board_id and WM_student_group.course_id={$sysSession->course_id} and WM_student_group.team_id={$_POST['team_id']} and WM_student_group.group_id={$olds[$i]}", ADODB_FETCH_NUM);
			if($bid)
			{
				dbDel('WM_bbs_boards', "board_id={$bid}");
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
			list($rids) = dbGetStSr('WM_chat_setting', 'rid', "owner='{$owners}'", ADODB_FETCH_NUM);
			if(isset($rids)){
				dbDel('WM_chat_setting', "rid='{$rids}' and owner='{$owners}'");
				dbDel('WM_chat_session', "rid='{$rids}'");
			}

			// 刪除相關 ACL
			$instance = sprintf("%4u%04u",$_POST['team_id'], $olds[$i]);
			$ACL_IDS  = Array();
			$ACL_IDS  = aclGetAclIdByInstance($acl_function, $course_id, $instance);

			if(count($ACL_IDS)>0) {
				dbDel('WM_acl_member','acl_id in ('.implode(",", $ACL_IDS).')');
				dbDel('WM_acl_list','acl_id in ('.implode(",", $ACL_IDS).')');
			}
		}
		dbDel('WM_student_group', "course_id={$sysSession->course_id} and group_id in (" .
				      			implode(',', array_keys($old_groups)) .
				      			") and team_id={$_POST['team_id']}");
		
		wmSysLog('1000300300', $sysSession->course_id , $_POST['team_id'] , 0, 'auto', $_SERVER['PHP_SELF'], iconv('Big5', 'UTF-8', '刪除學員分組：') . implode(',', array_keys($old_groups)));
	}
	
	// 處理異動的組長 begin
	$sqls = 'select S.team_id,G.group_id,G.board_id ' .
			'from WM_student_separate as S ' .
			'inner join WM_student_group as G ' .
			'on S.course_id=G.course_id and S.team_id=G.team_id ' .
			'left join WM_student_div as D ' .
			'on G.course_id=D.course_id and G.group_id=D.group_id ' .
			'and G.team_id=D.team_id and G.captain=D.username ' .
			'where S.course_id=' . $sysSession->course_id .
			' and G.captain != "" and isnull(D.username)';
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if ($rs = $sysConn->Execute($sqls))
	{
	    while ($fields = $rs->FetchRow())
	    {
	        dbSet('WM_student_group', 'captain=NULL', "course_id={$sysSession->course_id} and group_id={$fields['group_id']} and team_id={$fields['team_id']}");
	        dbSet('WM_bbs_boards'   , 'manager=NULL', "board_id={$fields['board_id']}");
		}
	}
	// 處理異動的組長 end

	// 處理 ACL
	/***************
	 * aclSaveList()
	 *    儲存 $acl_functions 所指定之 acl $function_id
	 * @param $instance : 此處需指定課程小組 sprintf("%4u%04u",$team_id, $group_id);
	 * @param $acl_list_item : acl list 組合字串
	 * @param $acl_id : 識別是修改或新增
	 *
	 ***************/
	function aclSaveList($acl_function,$instance, $acl_list_item, $acl_id=0) {
		global $sysConn, $course_id;
		$elements = explode(chr(8), $acl_list_item);

		$titles = explode(chr(9), $elements[0]);

		for($i=0;$i<count($titles);$i++){
			$title[$i] = stripslashes($title[$i]);
		}

		if($acl_id) {
			dbSet('WM_acl_list',
			      sprintf("permission=%d,caption='%s'",
			      		  $elements[1],
			      		  addslashes(serialize($titles))
						 ),
			      sprintf("acl_id=%d", $acl_id)
			     );
		}else {
			dbNew('WM_acl_list', 'permission,caption,function_id,unit_id,instance',
			      sprintf("%d,'%s',%d,%d,%d",
			      		$elements[1],
			      		addslashes($titles),
			      		$acl_function,
		      		  	$course_id,
			      		$instance
						 )
			     );
		      $acl_id = $sysConn->Insert_ID();
		}

		if ($sysConn->ErrorNo() == 0){
			// 刪掉舊的 acl_member
			dbDel('WM_acl_member',"acl_id=$acl_id");

			// 再新增進去
			$roles = explode(',', aclBitmap2Roles($elements[2]));
			for($ir=0; $ir<count($roles); $ir++)  if(!empty( $roles[$ir] ) &&  $roles[$ir] !== '' ) $roles[$ir] = '#' . $roles[$ir];
			$users = array_merge($roles, preg_split('/\D+/', $elements[3], -1, PREG_SPLIT_NO_EMPTY));
			foreach($users as $user) {
				if(!empty($user) && $user !== '' )
				dbNew('WM_acl_member', 'acl_id,member', $acl_id . ',"' . $user . '"');
			}
		}
		else
			die(sprintf("Creating ACL Error: No=%d, Msg=%s", $sysConn->ErrorNo(), $sysConn->ErrorMsg()));

	}

	for($acl_i=0;$acl_i<count($acl_lists);$acl_i++) {
		$instance = sprintf("%4u%04u",$_POST['team_id'], $all_groups[$acl_i]);
		$acl_parts = explode("\n", $acl_lists[$acl_i]);

		// 取出 user 送過來的 acl
		$cur_lists = array();
		$upd_lists = array();	// 需修改者
		$new_lists = array();
		$old_lists = array();	// 原始資料(未修改, 刪除前)
		foreach($acl_parts as $item){
			$x = explode(chr(8), $item, 2);
			if (preg_match('/^[0-9]+$/', $x[0])) {
				$cur_lists[] = intval($x[0]);
				$upd_lists[intval($x[0])] = $x[1];
			} elseif($x[0] == '*new*') $new_lists[] = $x[1];
		}

		// 取出資料庫的舊 acl
		$old_lists = aclGetAclIdByInstance($acl_function, $course_id, $instance);

		// 處理要修改的
		foreach($upd_lists as $acl_id=>$item){
			aclSaveList($acl_function, $instance, $item, $acl_id);
		}
		// 處理要刪掉的 ACL
		if(!empty($old_lists)) {
			$will_rm_ar = array_diff($old_lists,$cur_lists);
			$will_rm = implode(',', $will_rm_ar);

			if ($will_rm != ''){
				dbDel('WM_acl_member', sprintf('acl_id in (%s)', $will_rm));
				dbDel('WM_acl_list', sprintf('acl_id in (%s)', $will_rm));
			}
		}

		// 處理要新增的
		foreach($new_lists as $item)
			aclSaveList($acl_function, $instance, $item, 0);
	}

	// Chrome 開始 output HTML
	showXHTML_head_B($MSG['student_grouping'][$sysSession->lang]);
	echo <<< EOB
	<script language="javascript">
		alert("{$MSG['save_msg'][$sysSession->lang]}");
		location.replace("stud_groups.php?tid={$_POST['team_id']}");
	</script>
EOB;
	showXHTML_head_E();
	showXHTML_body_B();
	showXHTML_body_E();
?>

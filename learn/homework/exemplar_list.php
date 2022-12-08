<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

	$sysSession->cur_func='1700400300';
	$sysSession->restore();
	if (!aclVerifyPermission(1700400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if ($_SERVER['argv'][2] != md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']))
	{
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
		die('Fake ticket !');
	}
	
	function genTicket($username)
	{
		return md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $username . $_COOKIE['idx']);
	}
	
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline',"
function viewExemplar(examinee, ticket) {
	location.href = 'view_exemplar.php?{$_SERVER['argv'][0]}+{$_SERVER['argv'][1]}+' + ticket + '+' + examinee + '+exemplar';
}
	");
        echo '<style>';
        echo 'td {font-size: 1.2em;}';
        echo '.cssBtn {font-size: 1em; height: 1.9em;}';
        echo '.box01 td {padding: 0.4em;}';
        echo '.cssTabs {font-size: 1em;}';
        echo '</style>';
	showXHTML_head_E();
	showXHTML_body_B();


	$rs = dbGetStMr('WM_qti_homework_result', 'examinee', sprintf('exam_id=%u and time_id=%d and status="publish" order by score DESC', $_SERVER['argv'][0], $_SERVER['argv'][1]), ADODB_FETCH_ASSOC);
	if (!$rs) {
	   $errMsg = $sysConn->ErrorMsg();
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 2, 'auto', $_SERVER['PHP_SELF'], $errMsg);
	   die($errMsg);
	}

	if ($rs->RecordCount() == 0)
		echo '<h2 align="center">', $MSG['non_exemplary'][$sysSession->lang], '</h2>';

	showXHTML_tabFrame_B(array(array($MSG['exemplary_list'][$sysSession->lang])));
		showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="260" style="border-collapse: collapse" class="box01"');
		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td('', $MSG['exemplary_user'][$sysSession->lang]);
			showXHTML_td('', $MSG['publish_exemplary'][$sysSession->lang]);
		showXHTML_tr_E();
		$css = 'cssTrOdd';

		if (isAssignmentForGroup($_SERVER['argv'][0]))  // 如果是群組作業
		{
		    $alreadySubmittedAssignmentForGroup = getAlreadySubmittedAssignmentForGroup();
			list($team_id, $group_ids) = each($alreadySubmittedAssignmentForGroup[$_SERVER['argv'][0]]);
			$sqls = 'select distinct D.group_id,G.caption ' .
					'from WM_student_div as D ' .
					'left join WM_qti_homework_result as R ' .
					'on D.username=R.examinee and R.exam_id=' . $_SERVER['argv'][0] .
					' inner join WM_student_group as G ' .
					'on G.course_id = D.course_id and G.group_id = D.group_id and G.team_id = D.team_id ' .
					'where D.course_id=' . $sysSession->course_id .
					' and D.team_id=' . $team_id .
					' and G.course_id=' . $sysSession->course_id .
					' and G.team_id=' . $team_id .
					' order by G.group_id,R.status';
		    $GWRS = $sysConn->GetAssoc($sqls);
			if (is_array($GWRS) && count($GWRS))
			    foreach ($GWRS as $gid => $caption)
			    {
				    if (($titles = unserialize($caption)) !== false)
				        $GWRS[$gid] = $titles[$sysSession->lang];
			    }

			while ($row = $rs->FetchRow()) {
				showXHTML_tr_B('class="' . $css = ($css =='cssTrOdd' ? 'cssTrEvn' : 'cssTrOdd') . '"');
					$gs = getMyGroups($row['examinee'], $sysSession->course_id);
					showXHTML_td('width="70%"', $GWRS[$gs[$team_id]]);
					showXHTML_td_B('width="30%"');
						showXHTML_input('button', '', $MSG['publish'][$sysSession->lang], '', 'class="cssBtn" onclick="viewExemplar(\'' . $row['examinee'] . '\', \'' . genTicket($row['examinee']) . '\')"');
					showXHTML_td_E();
				showXHTML_tr_E();
			}
		}
		else    // 個人作業
			while ($row = $rs->FetchRow()) {
				showXHTML_tr_B('class="' . $css = ($css =='cssTrOdd' ? 'cssTrEvn' : 'cssTrOdd') . '"');
					showXHTML_td('width="70%"', $row['examinee']);
					showXHTML_td_B('width="30%"');
						showXHTML_input('button', '', $MSG['publish'][$sysSession->lang], '', 'class="cssBtn" onclick="viewExemplar(\'' . $row['examinee'] . '\', \'' . genTicket($row['examinee']) . '\')"');
					showXHTML_td_E();
				showXHTML_tr_E();
			}

		showXHTML_table_E();
	showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

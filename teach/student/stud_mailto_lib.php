<?php
	/**
	 * �H�H�I�W�@�Ϊ��禡
	 *
	 * @since   2004/06/17
	 * @author  ShenTing Lin
	 * @version $Id: stud_mailto_lib.php,v 1.1 2010/02/24 02:40:31 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');	
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$mt_type = array(
		'login'         => $MSG['which1_1'][$sysSession->lang],
		'lesson'        => $MSG['which1_2'][$sysSession->lang],
		'progress'      => $MSG['which1_3'][$sysSession->lang],
		'exam'          => $MSG['which1_4'][$sysSession->lang],
		'homework'      => $MSG['which1_5'][$sysSession->lang],
		'questionnaire' => $MSG['which1_6'][$sysSession->lang],
		'chat'          => $MSG['which1_7'][$sysSession->lang],
		'post'          => $MSG['which1_8'][$sysSession->lang],
	);

	$mt_roles = array(
		'all'     => $MSG['all'][$sysSession->lang],
		'auditor' => $MSG['auditor'][$sysSession->lang],
		'student' => $MSG['student'][$sysSession->lang],
	);
		
	$mt_operator = array(
		'equal'         => ' = ',
		'greater'       => ' > ',
		'smaller'       => ' < ',
		'greater_equal' => ' >= ',
		'smaller_equal' => ' <= ',
		'differ'        => ' != '
	);
	
	$mt_days = array(
		'Monday'	=>	$MSG['Monday'][$sysSession->lang],
		'Tuesday'	=>	$MSG['Tuesday'][$sysSession->lang],
		'Wednesday'	=>	$MSG['Wednesday'][$sysSession->lang],
		'Thursday'	=>	$MSG['Thursday'][$sysSession->lang],
		'Friday'	=>	$MSG['Friday'][$sysSession->lang],
		'Saturday'	=>	$MSG['Saturday'][$sysSession->lang],
		'Sunday'	=>	$MSG['Sunday'][$sysSession->lang]
	);

	function getQTIPaperList($val) {
		global $sysSession, $sysConn;

		$ary = array();
		$course_id = $sysSession->course_id;
		$RS = dbGetStMr('WM_qti_' . $val . '_test',
						'`exam_id`, `title`, `type`, `publish`',
						"course_id={$course_id} and publish != 'prepare' order by sort, exam_id",
						ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$lang = getCaption($RS->fields['title']);
				$title = empty($lang[$sysSession->lang]) ? '[No Title]' : htmlspecialchars_decode($lang[$sysSession->lang]);
				$ary[$RS->fields['exam_id']] = array($title, $RS->fields['type'], $RS->fields['publish']);
				$RS->MoveNext();
			}
		}
		return $ary;
	}
	
	
	/**
	 * �n�J
	 * @param
	 * @return
	 **/
	function func_login($ary, $sid='', $csid='') {
		global $sysSession, $sysConn, $sysRoles, $mt_operator, $roles;

		if (empty($csid)) $csid = intval($sysSession->course_id);
		if (empty($sid)) $sid = intval($sysSession->school_id);
		$role = ($roles[0] != 'all') ? " AND `role` & {$sysRoles[$roles[0]]} " : '';
		
        chkSchoolId('WM_term_major');
        $sqls = 'select U.username, U.login_times, U.post_times, U.dsc_times,U.last_login 
        	     from WM_term_major as U join ' . sysDBname . '.WM_sch4user as S on U.username=S.username and S.school_id=' . $sid .'
        	     where U.course_id=' . $csid . $role;
		
		switch ($ary[1]) {
			case 'total':   // �n�J�`����
				$sqls .= ' AND S.login_times ' . $mt_operator[$ary[2]] . ' "' . $ary[3] . '"';
				break;
			case 'off'  :   // �X�ѥ��n�J
				if ($ary[2] == 'greater' || $ary[2] == 'greater_equal' || $ary[2] == 'differ')
					$sqls .= ' AND ((DATE_SUB(NOW(), INTERVAL ' . intval($ary[3]) . ' DAY) ' . $mt_operator[$ary[2]] . ' S.last_login) || S.last_login is NULL)';
				else
					$sqls .= ' AND DATE_SUB(NOW(), INTERVAL ' . intval($ary[3]) . ' DAY) ' . $mt_operator[$ary[2]] . ' S.last_login';
				break;
			case 'last' :   // �̫�@���n�J
				if ($ary[2] == 'equal')
					$sqls .= sprintf(' and S.last_login between "%s" and DATE_ADD("%s", INTERVAL 1 DAY)', $ary[3], $ary[3]);
				else if ($ary[2] == 'greater')
					$sqls .= sprintf(' and S.last_login > DATE_ADD("%s", INTERVAL 1 DAY)', $ary[3]);
				else if ($ary[2] == 'greater_equal')
					$sqls .= sprintf(' and S.last_login > "%s"', $ary[3]);
				else if ($ary[2] == 'smaller')
					$sqls .= sprintf(' and (S.last_login < "%s" or S.last_login is NULL)', $ary[3]);
				else if ($ary[2] == 'smaller_equal')
					$sqls .= sprintf(' and (S.last_login <= DATE_ADD("%s", INTERVAL 1 DAY) or S.last_login is NULL)', $ary[3]);
				else if ($ary[2] == 'differ')
					$sqls .= sprintf(' and ((S.last_login not between "%s" and DATE_ADD("%s", INTERVAL 1 DAY)) or S.last_login is NULL)', $ary[3], $ary[3]);
				break;
			default:
		}
		
		$sqls .= ' order by U.username';
        $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
		return $sysConn->GetArray($sqls);
	}

	/**
	 * �W�Ҭd��
	 * @param
	 * @return
	 **/
	function func_lesson($ary, $csid='') {
		global $sysSession, $sysConn, $sysRoles, $mt_operator, $roles;
		if (empty($csid)) $csid = intval($sysSession->course_id);
		$role = ($roles[0] != 'all') ? "AND `role` & {$sysRoles[$roles[0]]} " : '';
		$result = array();
		switch ($ary[1]) {
			case 'total':   // �W���`����
				$RS = dbGetStMr('WM_term_major', '*', "`course_id`={$csid} {$role} AND `login_times` {$mt_operator[$ary[2]]} '{$ary[3]}' order by `username`", ADODB_FETCH_ASSOC);
				break;
			case 'off'  :   // �X�ѥ��W��
				if ($ary[2] == 'greater' || $ary[2] == 'greater_equal' || $ary[2] == 'differ')
					$RS = dbGetStMr('WM_term_major', '*', "`course_id`={$csid} {$role} AND ((DATE_SUB(NOW(), INTERVAL " . intval($ary[3]) . ' DAY) ' . $mt_operator[$ary[2]] . ' last_login) || last_login is NULL) order by `username`', ADODB_FETCH_ASSOC);
				else
					$RS = dbGetStMr('WM_term_major', '*', "`course_id`={$csid} {$role} AND DATE_SUB(NOW(), INTERVAL " . intval($ary[3]) . ' DAY) ' . $mt_operator[$ary[2]] . ' last_login order by `username`', ADODB_FETCH_ASSOC);
				break;
			case 'last' :   // �̫�@���W��
				
				if ($ary[2] == 'equal')
					$where = sprintf('last_login between "%s" and DATE_ADD("%s", INTERVAL 1 DAY)', $ary[3], $ary[3]);
				else if ($ary[2] == 'greater')
					$where .= sprintf('last_login > DATE_ADD("%s", INTERVAL 1 DAY)', $ary[3]);
				else if ($ary[2] == 'greater_equal')
					$where .= sprintf('last_login > "%s"', $ary[3]);
				else if ($ary[2] == 'smaller')
					$where .= sprintf('(last_login < "%s" or last_login is NULL)', $ary[3]);
				else if ($ary[2] == 'smaller_equal')
					$where .= sprintf('(last_login <= DATE_ADD("%s", INTERVAL 1 DAY) or last_login is NULL)', $ary[3]);
				else if ($ary[2] == 'differ')
					$where .= sprintf('((last_login not between "%s" and DATE_ADD("%s", INTERVAL 1 DAY)) or last_login is NULL)', $ary[3], $ary[3]);
				
				$RS = dbGetStMr('WM_term_major', '*', "`course_id`={$csid} {$role} AND {$where} order by username", ADODB_FETCH_ASSOC);
				
				break;
			default:
		}

		if ($RS) {
			while (!$RS->EOF) {
				$result[] = $RS->fields;
				$RS->MoveNext();
			}
		}
		return $result;
	}

	/**
	 * �Q��
	 * @param
	 * @return
	 **/
	function func_chat($ary, $csid='') {
		global $sysSession, $sysConn, $sysRoles, $mt_operator, $roles;
		if (empty($csid)) $csid = intval($sysSession->course_id);
		$role = ($roles[0] != 'all') ? "AND `role` & {$sysRoles[$roles[0]]} " : '';
		$result = array();
		switch ($ary[1]) {
			case 'total':   // �Q���`����
				$RS = dbGetStMr('WM_term_major', '*', "`course_id`={$csid} {$role} AND `dsc_times` {$mt_operator[$ary[2]]} '{$ary[3]}' order by `username`", ADODB_FETCH_ASSOC);
				break;
			default:
		}

		if ($RS) {
			while (!$RS->EOF) {
				$result[] = $RS->fields;
				$RS->MoveNext();
			}
		}
		return $result;
	}

	/**
	 * �i�K
	 * @param
	 * @return
	 **/
	function func_post($ary, $csid='') {
		global $sysSession, $sysConn, $sysRoles, $mt_operator, $roles;

		if (empty($csid)) $csid = intval($sysSession->course_id);
		$role = ($roles[0] != 'all') ? "AND `role` & {$sysRoles[$roles[0]]} " : '';
		$result = array();
		switch ($ary[1]) {
			case 'total':   // �Q���`����
				$RS = dbGetStMr('WM_term_major', '*', "`course_id`={$csid} {$role} AND `post_times` {$mt_operator[$ary[2]]} '{$ary[3]}' order by `username`", ADODB_FETCH_ASSOC);
				break;
			default:
		}

		if ($RS) {
			while (!$RS->EOF) {
				$result[] = $RS->fields;
				$RS->MoveNext();
			}
		}
		return $result;
	}

	function func_progress($ary, $csid='') {
		global $sysSession, $sysConn, $sysRoles, $mt_operator, $roles;

		if (empty($csid)) $csid = intval($sysSession->course_id);
		$role = ($roles[0] != 'all') ? "AND WTM.`role` & {$sysRoles[$roles[0]]} " : '';
		$result = array();
		$mt_operator['equal'] = ' == ';
		switch ($ary[1]) {
			case 'total':   // �ǲ��`�ɼ�
				$RS = dbGetStMr('WM_term_major AS WTM LEFT JOIN WM_record_daily_personal AS WRDP ON WTM.username=WRDP.username AND WTM.course_id=WRDP.course_id',
								'WTM.*, sum(WRDP.reading_seconds) AS reading_seconds',
								"WTM.`course_id`={$csid} {$role} GROUP BY WTM.`username` ORDER BY WTM.`username`",
								ADODB_FETCH_ASSOC);
				if ($RS) {
					while (!$RS->EOF) {
						$utime = intval($RS->fields['reading_seconds']);
						$time = intval($ary[3]) * 60;
						eval("\$bol = ({$utime} {$mt_operator[$ary[2]]} {$time}) ? true : false;");
						if ($bol) $result[] = $RS->fields;
						$RS->MoveNext();
					}
				}
				break;
			case 'page':   // �\Ū����
				$RS = dbGetStMr('WM_record_reading', '`username`, count(*) AS reading_pages', "`course_id`={$csid} GROUP BY `course_id`, `username` ORDER BY `username`", ADODB_FETCH_ASSOC);
				$users = array();
				if ($RS) {
					while (!$RS->EOF) {
						$user[$RS->fields['username']] = $RS->fields['reading_pages'];
						$RS->MoveNext();
					}
				}
				$RS = dbGetStMr('WM_term_major AS WTM', '*', "`course_id`={$csid} {$role} ORDER BY `username`", ADODB_FETCH_ASSOC);
				if ($RS) {
					while (!$RS->EOF) {
						$upage = intval($user[$RS->fields['username']]);
						$page = intval($ary[3]);
						eval("\$bol = ({$upage} {$mt_operator[$ary[2]]} {$page}) ? true : false;");
						if ($bol) {
							$RS->fields['reading_pages'] = intval($upage);
							$result[] = $RS->fields;
						}
						$RS->MoveNext();
					}
				}
				break;
			default:
		}
		$mt_operator['equal'] = ' = ';
		return $result;
	}

	if (!function_exists('array_intersect_key'))
	{
   		function array_intersect_key ($isec, $arr2)
   		{
       		$argc = func_num_args();
         
       		for ($i = 1; !empty($isec) && $i < $argc; $i++)
       		{
             	$arr = func_get_arg($i);
             
             	foreach ($isec as $k => $v)
                	if (!isset($arr[$k]))
                     	unset($isec[$k]);
       		}
       
       		return $isec;
   		}
	}
	
	// �T�X�@
	function func_QTI($ary, $sid='', $csid='') {
		if ($ary[0] == 'homework') include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
		
		global $sysSession, $roles, $sysRoles, $sysConn, $mt_operator;
		
		$qti_fun_id = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200);
		$role = ($roles[0] != 'all') ? " AND `role` & {$sysRoles[$roles[0]]} " : '';
		$mt_operator['equal'] = ' == ';
		if (empty($csid)) $csid = intval($sysSession->course_id);
		if (empty($sid))  $sid  = intval($sysSession->school_id);
		
		chkSchoolId('WM_student_div');
		
		if ($ary[1] == 'some') { // �Y������ �@�~ �ݨ�
			if ($ary[0] == 'homework' && isAssignmentForGroup($ary[3], $csid)) { // �s�է@�~
				$have_done = array(); $allMem = array(); $selected = array();
				
				$submitted = getAlreadySubmittedAssignmentForGroup($csid);	// �����o�Ҧ��w�����W��
				if ($submitted[$ary[3]] && is_array($submitted[$ary[3]])) {
					foreach ($submitted[$ary[3]] as $team_id => $group_ids) {
						$cols = $sysConn->GetCol('select username from WM_student_div where course_id = "' . $csid . '" and group_id in(' . implode(',', $group_ids) .') and team_id=' .$team_id);
						if ($cols && count($cols)) $have_done = array_merge($have_done, $cols);
					}
				}
				if ($ary[2] == 'yes') { // �w��
					$selected = array_unique($have_done);
				}
				else {	// ����
					$grpHW = getAssignmentsForGroup($csid);
					if ($grpHW[$ary[3]] && is_array($grpHW[$ary[3]]))
					foreach ($grpHW[$ary[3]] as $team_id => $group_ids) {
						$cols = $sysConn->GetCol('select username from WM_student_div where course_id = "' . $csid . '" and group_id in(' . implode(',', $group_ids) .') and team_id=' .$team_id);
						if ($cols && count($cols)) $allMem = array_merge($allMem, $cols);
					}
					$selected = array_diff($allMem, $have_done);
				}
			}
			else if ($ary[2] == 'yes') { // �䥦�@�~ ���� �ݨ� �w��
				$selected = $sysConn->GetCol('select distinct examinee from WM_qti_' . $ary[0] . '_result where exam_id=' . $ary[3]);
			}
			else {	// �䥦�@�~ ���� �ݨ� ���� (���P�_ACL, �Y�L�]�w�h�w�]�O������)
				$acl_ids = $sysConn->GetCol('select acl_id from WM_acl_list where function_id="' . $qti_fun_id[$ary[0]] . '" and unit_id="' . $csid . '" and instance="' . $ary[3] . '"');
				if (is_array($acl_ids) && count($acl_ids)) {
					$can_do = array();
					foreach ($acl_ids as $acl_id)
						$can_do = array_merge($can_do, aclGetMembersByAcl($acl_id, $csid));
					$can_do = array_unique($can_do);
				}
				else {
					if ($roles[0] == 'auditor') return;
					$can_do = $sysConn->GetCol('select username from WM_term_major where course_id=' . $csid . ' and role & ' . $sysRoles['student']);
				}
				$have_done = $sysConn->GetCol('select distinct examinee from WM_qti_' . $ary[0] . '_result where exam_id=' . $ary[3]);
				$selected = array_diff($can_do, $have_done);
			}
			
            $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
			$people = $sysConn->GetAssoc('select username as idx, username, login_times, post_times, dsc_times,last_login from WM_term_major where course_id=' . $csid . $role);
			return array_intersect_key($people, array_flip($selected));
		}
		else if ($ary[1] == 'yes') { // �w������ �@�~ �ݨ�
			/* �p�G�O�@�~
			 * 1.���o�s�է@�~id
			 * 2.�p��ǭ��w���@��@�~���Ӽ�
			 * 3.�p��ǭ��w���s�է@�~���Ӽ�
			 */
			if ($ary[0] == 'homework') {
				$grpHW = getAssignmentsForGroup($csid);						// ���o�s�է@�~
				$submitted = getAlreadySubmittedAssignmentForGroup($csid);	// �����o�Ҧ��w�����W��
			}
            $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
			$have_done = $sysConn->GetAssoc('select B.examinee, count(distinct B.exam_id) 
											 from WM_qti_' . $ary[0] . '_test AS A left join WM_qti_' . $ary[0] . '_result AS B on A.exam_id = B.exam_id 
			                                 where A.course_id = '. $csid .' and A.publish != "prepare" and '.
			    							($ary[0] == 'homework' ? (' A.exam_id not in('.implode(',', array_keys($grpHW)).') and ') : '') .
			    							 'B.examinee is not null group by B.examinee');
			
			if ($ary[0] == 'homework') {
				foreach ($submitted as $exam_id => $exams) {
					foreach ($exams as $team_id => $group_ids) {	// �z�פW�@�Ӹs�է@�~�u�|���t��@�Ӳզ������p��, �w���U�@�٬O�ϥ�foreach�]�@��
						$tmp = $sysConn->GetCol('select username from WM_student_div where course_id = "' . $csid . '" and group_id in(' . implode(',', $group_ids) .') and team_id=' .$team_id);
						foreach ($tmp as $u)
							$have_done[$u]++;
					}
				}
			}
			
			foreach ($have_done as $user => $times) {
				eval('$res = (' . $times . ' ' . $mt_operator[$ary[2]] . ' ' . $ary[3] . ');');
				if (!$res) unset($have_done[$user]);
			}
            $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
			$people = $sysConn->GetAssoc('select username as idx, username, login_times, post_times, dsc_times,last_login from WM_term_major where course_id=' . $csid . $role);
			return array_intersect_key($people, $have_done);
		}
		else if ($ary[1] == 'no') { // �������� �@�~ �ݨ�
			// step1 : ���o�C�ӤH�i������� (acl�P�_, ���窬�A�P�_)
            $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
			$can_do = $sysConn->GetAssoc('select username, 0 as tests, role from WM_term_major where course_id=' . $csid . $role);
			if ($roles[0] != 'auditor') { // �����o�S�]�wACL������, �N�Ҧ������������禸�ƥ[�W
				list($cnt) = dbGetStSr('WM_qti_' . $ary[0] . '_test AS A left join WM_acl_list AS B on A.course_id = B.unit_id and A.exam_id = B.instance',  
									   'count(A.exam_id)', 
									   'A.course_id = '. $csid .' and A.publish != "prepare" and B.acl_id is NULL',
									   ADODB_FETCH_NUM);
				foreach ($can_do as $k => $v)
					if ($v['role'] & $sysRoles['student']) $can_do[$k]['tests'] += $cnt;
			}

			$rs = dbGetStMr('WM_qti_' . $ary[0] . '_test AS A left join WM_acl_list AS B on A.course_id = B.unit_id and A.exam_id = B.instance',
					  		'A.exam_id, B.acl_id',
				      		'A.course_id = '. $csid .' and A.publish != "prepare" and B.acl_id is not NULL',
							ADODB_FETCH_ASSOC);
			if ($rs) {
				while ($row = $rs->FetchRow())	// ���X�Ҧ����窺�Ҧ�ACL (��@����i�঳�h��acl)
					$tmp[$row['exam_id']][] = $row['acl_id'];
				if ($tmp) foreach($tmp as $tmp_cid => $acl_ids) {
					$member = array();
					foreach($acl_ids as $acl_id)	// ���X��@���礤�Ҧ�acl���������X(�i�঳����)
						$member = array_merge($member, aclGetMembersByAcl($acl_id, $csid));
					$member = array_unique($member);	// �h�����ƪ�����
					foreach ($member as $u) 			// �N�Ҧ��������i���禸��+1
						if ($can_do[$u]) $can_do[$u]['tests']++;
					unset($member);
				}
			}
			
			// step2 : ���o�C�ӤH�w�������
			if ($ary[0] == 'homework') {
				$grpHW = getAssignmentsForGroup($csid);						// ���o�s�է@�~
				$submitted = getAlreadySubmittedAssignmentForGroup($csid);	// �����o�Ҧ��w�����W��
			}
			$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
			$have_done = $sysConn->GetAssoc('select B.examinee, count(distinct B.exam_id) 
											 from WM_qti_' . $ary[0] . '_test AS A left join WM_qti_' . $ary[0] . '_result AS B on A.exam_id = B.exam_id 
			                                 where A.course_id = '. $csid .' and A.publish != "prepare" and '.
			    							 ($ary[0] == 'homework' ? (' A.exam_id not in('.implode(',', array_keys($grpHW)).') and ') : '') .
			    							 'B.examinee is not null group by B.examinee');
			if ($ary[0] == 'homework') {
				foreach ($submitted as $exam_id => $exams) {
					foreach ($exams as $team_id => $group_ids) {	// �z�פW�@�Ӹs�է@�~�u�|���t��@�Ӳզ������p��, �w���U�@�٬O�ϥ�foreach�]�@��
						$tmp = $sysConn->GetCol('select username from WM_student_div where course_id = "' . $csid . '" and group_id in(' . implode(',', $group_ids) .') and team_id=' .$team_id);
						foreach ($tmp as $u)
							$have_done[$u]++;
					}
				}
			}
			// step3 : ���o��������ƨçR�h���ŦX�����
			foreach ($can_do as $u => $val) {
				$times = $have_done[$u] ? ($can_do[$u]['tests'] - $have_done[$u]) : $can_do[$u]['tests'];
				eval('$res = (' . $times . ' ' . $mt_operator[$ary[2]] . ' ' . $ary[3] . ');');
				if (!$res) unset($can_do[$u]);
			}
			
			$mt_operator['equal'] = ' = ';
			// step4 : ���o�H�����
			$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
			$people = $sysConn->GetAssoc('select username as idx, username, login_times, post_times, dsc_times,last_login from WM_term_major where course_id=' . $csid . $role, false, false, false);
			return array_intersect_key($people, $can_do);
		}
		else return;
	}
    
    	/**
	 * �ഫ��Ƭ��ѡB�ɡB���P��
	 * @param integer $sec : ���
	 * @param boolean $show_day : �O�_�n��ܤѼ�
	 * @param string  $str : �ۭq��ܪ��榡 (�w�]�G'%d days, %2$02d:%3$02d:%4$02d')
	 * @return �榡�ƫ᪺�r��
	 **/
	function sec2time($sec, $show_day=true, $str='') {
		global $sysSession, $MSG;

		$tmp = intval($sec);
		$sec = $tmp % 60;
		$tmp = floor($tmp / 60);
		$min = $tmp % 60;
		$tmp = floor($tmp / 60);
		if ($show_day) {
			$hou = $tmp % 24;
			$day = floor($tmp / 24);
			if (empty($str)) $str = $MSG['days'][$sysSession->lang] . $MSG['time_str'][$sysSession->lang];
		} else {
			$hou = $tmp;
			$day = 0;
			if (empty($str)) $str = $MSG['time_str'][$sysSession->lang];
		}
		return sprintf($str, $day, $hou, $min, $sec);
	}

?>

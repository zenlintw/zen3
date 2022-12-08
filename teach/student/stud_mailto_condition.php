<?php
	/**
	 * �z����
	 *
	 * @since   2004/06/18
	 * @author  ShenTing Lin
	 * @version $Id: stud_mailto_condition.php,v 1.1 2010/02/24 02:40:31 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/teach/student/stud_mailto_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
    
	$sysSession->cur_func = '1000200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function memberFilter($element)
	{
	    global $group;
	    
	    return in_array($element['username'], $group);
	}

	/**
	 * �N�d�ߩұo������ন XML �榡
	 * @param array $ary : ��ư}�C
	 * @return string : xml �榡���r��
	 **/
	function rs2xml($ary) {
		global $sysSession, $sysConn, $groups, $group;

		if (!is_array($ary)) {
			return '<manifest />';
		}

		$csid = intval($sysSession->course_id);
		// �L�o�s��
		$group = array();
		if ($groups[0][0] != 'all') {
			$tm = intval($groups[0][0]);
			if ($groups[0][1] == 'all') {
				$RS = dbGetStMr('WM_student_div', '`username`', "`course_id`={$csid} AND `team_id`={$tm}", ADODB_FETCH_ASSOC);
			} else {
				$gp = intval($groups[0][1]);
				$RS = dbGetStMr('WM_student_div', '`username`', "`course_id`={$csid} AND `group_id`={$gp} AND `team_id`={$tm}", ADODB_FETCH_ASSOC);
			}

			if ($RS) {
				while (!$RS->EOF) {
					$group[] = $RS->fields['username'];
					$RS->MoveNext();
				}
			}
			
			$ary = array_filter($ary, 'memberFilter'); // �L�o���D�s�ժ��H
		}
		
		// ����o�m�W�εn�J���ơB�̫�n�J�ɶ��� SQL�A�q�j�鴣�X�ӡA�@���d�w begin
        $usernames = array(); $userinfos = array();
		foreach ($ary as $val) $usernames[] = $val['username'];
		if (count($usernames) > 0)
		{
			$sqls = 'select U.username,first_name,last_name,login_times,last_login ' .
					'from WM_user_account as U,' . sysDBname . '.WM_sch4user as S where U.username in ("' .
					implode('","', $usernames) . '") and S.school_id=' . $sysSession->school_id .
					' and U.username=S.username';
            chkSchoolId('WM_user_account');
		    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		    $userinfos = $sysConn->GetAssoc($sqls);
	    }
	    // ����o�m�W�εn�J���ơB�̫�n�J�ɶ��� SQL�A�q�j�鴣�X�ӡA�@���d�w end

		$xmlStrs = '';
		foreach ($ary as $val) {
			$realname = htmlspecialchars(checkRealname($userinfos[$val['username']]['first_name'],$userinfos[$val['username']]['last_name']));
			$reading_seconds = isset($val['reading_seconds']) ? sec2time($val['reading_seconds'], false, '%2$02d:%3$02d:%4$02d') : '';
			$reading_pages = isset($val['reading_pages']) ? intval($val['reading_pages']) : '';
			$xmlStrs .= <<< BOF
	<user>
		<username>{$val['username']}</username>
		<realname>{$realname}</realname>
		<lesson_times>{$val['login_times']}</lesson_times>
		<post_times>{$val['post_times']}</post_times>
		<dsc_times>{$val['dsc_times']}</dsc_times>
		<last_lesson>{$val['last_login']}</last_lesson>
		<login_times>{$userinfos[$val['username']]['login_times']}</login_times>
		<last_login>{$userinfos[$val['username']]['last_login']}</last_login>
		<reading_seconds>{$reading_seconds}</reading_seconds>
		<reading_pages>{$reading_pages}</reading_pages>
	</user>
BOF;
		}
		return '<manifest><ticket></ticket>' . $xmlStrs . '</manifest>';
	}

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}

		// �ˬd Ticket
		/*
		$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->ticket . $sysSession->school_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest>Access Fail.</manifest>';
			exit;
		}
		*/

/*
	<manifest>
		<roles>
			<role><role>
		</roles>
		<groups>
			<group team="" group=""></group>
		</groups>
		<filters>
			<filter type="����" filter="�l����" op="�j�p��">�ƭ�</filter>
		</filters>
	</manifest>
*/

		$roles = array();
		$nodes = $dom->get_elements_by_tagname('role');
		for ($i = 0; $i < count($nodes); $i++) {
			if ($nodes[$i]->has_child_nodes()) {
				$node = $nodes[$i]->first_child();
				$roles[] = $node->node_value();
			} else {
				$roles[] = '';
			}
		}
		//print_r($roles);
		$groups = array();
		$nodes = $dom->get_elements_by_tagname('group');
		for ($i = 0; $i < count($nodes); $i++) {
			$tm = $nodes[$i]->get_attribute('team');
			$gp = $nodes[$i]->get_attribute('group');
			// $groups = $nodes[$i]->attributes();
			$groups[] = array($tm, $gp);
		}
		if (count($groups) <= 0) {
			$groups[] = array('all', 'all');
		}
		// print_r($groups);
		$filters = array();
		$nodes = $dom->get_elements_by_tagname('filter');
		for ($i = 0; $i < count($nodes); $i++) {
			$tp = $nodes[$i]->get_attribute('type');
			$ft = $nodes[$i]->get_attribute('filter');
			$op = $nodes[$i]->get_attribute('op');
			// $groups = $nodes[$i]->attributes();
			$vl = '';
			if ($nodes[$i]->has_child_nodes()) {
				$node = $nodes[$i]->first_child();
				$vl = $node->node_value();
			}
			$filters[] = array($tp, $ft, $op, $vl);
		}
	}

	/**
	 * ���X����
	 *     all     : ����
	 *     auditor : ��ť��
	 *     student : ������
	 **/
		// print_r($roles);

	/**
	 * �s��
	 *
	 **/
	/**
	 * �L�o����
	 *     login         : �n�J
	 *     lesson        : �W��
	 *     progress      : �ǲ߶i��
	 *     exam          : ����
	 *     homework      : �@�~
	 *     questionnaire : �ݨ�
	 *     chat          : �Q��
	 *     post          : �i�K
	 **/
	$students = array();
	switch ($filters[0][0]) {
		case 'login'         :   // �n�J
		case 'lesson'        :   // �W��
		case 'chat'          :   // �Q��
		case 'post'          :   // �i�K
			$students = call_user_func_array('func_' . $filters[0][0], array($filters[0]));
			break;
		case 'progress'      :   // �ǲ߶i��
			$students = call_user_func_array('func_' . $filters[0][0], array($filters[0]));
			break;
		case 'exam'          :   // ����
		case 'homework'      :   // �@�~
		case 'questionnaire' :   // �ݨ�
			$students = func_QTI($filters[0]);
			break;
		default:
			$csid = intval($sysSession->course_id);
			$role = ($roles[0] != 'all') ? "AND `role` & {$sysRoles[$roles[0]]} " : '';
			$RS = dbGetStMr('WM_term_major', '*', "`course_id`={$csid} {$role} order by `username`", ADODB_FETCH_ASSOC);
			if ($RS) {
				while (!$RS->EOF) {
					$students[] = $RS->fields;
					$RS->MoveNext();
				}
			}
	}
	// ��X���G
	echo rs2xml($students);
	// print_r($students);

?>

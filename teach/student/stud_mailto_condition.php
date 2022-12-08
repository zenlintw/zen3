<?php
	/**
	 * 篩選資料
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
	 * 將查詢所得的資料轉成 XML 格式
	 * @param array $ary : 資料陣列
	 * @return string : xml 格式的字串
	 **/
	function rs2xml($ary) {
		global $sysSession, $sysConn, $groups, $group;

		if (!is_array($ary)) {
			return '<manifest />';
		}

		$csid = intval($sysSession->course_id);
		// 過濾群組
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
			
			$ary = array_filter($ary, 'memberFilter'); // 過濾掉非群組的人
		}
		
		// 把取得姓名及登入次數、最後登入時間的 SQL，從迴圈提出來，一次搞定 begin
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
	    // 把取得姓名及登入次數、最後登入時間的 SQL，從迴圈提出來，一次搞定 end

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

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}

		// 檢查 Ticket
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
			<filter type="類型" filter="子項目" op="大小於">數值</filter>
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
	 * 取出身份
	 *     all     : 全部
	 *     auditor : 旁聽生
	 *     student : 正式生
	 **/
		// print_r($roles);

	/**
	 * 群組
	 *
	 **/
	/**
	 * 過濾條件
	 *     login         : 登入
	 *     lesson        : 上課
	 *     progress      : 學習進度
	 *     exam          : 測驗
	 *     homework      : 作業
	 *     questionnaire : 問卷
	 *     chat          : 討論
	 *     post          : 張貼
	 **/
	$students = array();
	switch ($filters[0][0]) {
		case 'login'         :   // 登入
		case 'lesson'        :   // 上課
		case 'chat'          :   // 討論
		case 'post'          :   // 張貼
			$students = call_user_func_array('func_' . $filters[0][0], array($filters[0]));
			break;
		case 'progress'      :   // 學習進度
			$students = call_user_func_array('func_' . $filters[0][0], array($filters[0]));
			break;
		case 'exam'          :   // 測驗
		case 'homework'      :   // 作業
		case 'questionnaire' :   // 問卷
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
	// 輸出結果
	echo rs2xml($students);
	// print_r($students);

?>

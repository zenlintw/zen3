<?php
	/**
	 * 課程群組的功能
	 *
	 * @since   2004/11/23
	 * @author  ShenTing Lin
	 * @version $Id: course_group_func.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');

	// 安全性檢查

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
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

		// 重新建立 Ticket
		setTicket();
		$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->ticket . $sysSession->school_id);
		*/

		$act = getNodeValue($dom, 'act');

		$tmp  = explode(',', getNodeValue($dom, 'course'));
		$gpid = explode(',', getNodeValue($dom, 'group'));
		$idx  = intval(sysDecode(getNodeValue($dom, 'idx')));

		$csid = array();
		foreach ($tmp as $key => $val) {
			$cs = getCourseData(sysDecode($val));
			if ((count($cs) == 0) || (intval($cs['status']) == 9)) continue;
			$csid[$key] = intval($cs['course_id']);
		}

		// 移除要刪除的課程編號
		$ary = array('move', 'remove');
		if (in_array($act, $ary)) {
			if ($idx <= 10000000) {
				die('GorupID_Error');
			}
			$tmp = array();
			$RS = dbGetStMr('WM_term_group', 'child', "`parent`={$idx} order by `permute`", ADODB_FETCH_ASSOC);
			if ($RS) {
				while (!$RS->EOF) {
					if (!in_array($RS->fields['child'], $csid)) $tmp[] = $RS->fields['child'];
					$RS->MoveNext();
				}
			}
			dbDel('WM_term_group', "`parent`={$idx}");
			$max = 0;
			foreach ($tmp as $key => $val) {
				$max++;
				dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$idx}, {$val}, {$max}");
			}
		}
		// 搬移或附屬到其他群組中
		$ary = array('move', 'append');
		if (in_array($act, $ary)) {
			foreach ($gpid as $key => $val) {
				$log_msg = '';
				$gid = intval(sysDecode($val));
				if ($gid == $idx) continue;
				list($max) = dbGetStSr('WM_term_group', 'max(`permute`)', "`parent`={$gid}", ADODB_FETCH_NUM);
				$max = intval($max);
				foreach ($csid as $key => $cid) {
					list($cnt) = dbGetStSr('WM_term_group', 'count(*)', "`parent`={$gid} AND `child`={$cid}", ADODB_FETCH_NUM);
					if (intval($cnt) > 0) continue;
					$max++;
					dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$gid}, {$cid}, {$max}");
					$log_msg = $log_msg == '' ? $cid : (',' . $cid);
				}
				if ($log_msg != '')
					wmSysLog('0700400400', $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'set course group relation: gid=' . $gid . ', cids=' . $log_msg);
			}
		}
		// 課程上下移
		$ary = array('up', 'down');
		if (in_array($act, $ary)) {
			if ($idx <= 10000000) {
				die('GorupID_Error');
			}
			$gp = array();
			$cs = array();
			$RS = dbGetStMr('WM_term_group', 'child', "`parent`={$idx} order by `permute`", ADODB_FETCH_ASSOC);
			if ($RS) {
				while (!$RS->EOF) {
					$cid = intval($RS->fields['child']);
					$ary = getCourseData($cid);
					if ($ary['kind'] == 'course') $cs[] = $cid;
					if ($ary['kind'] == 'group')  $gp[] = $cid;
					$RS->MoveNext();
				}
			}
			if ($act == 'down') $csid = array_reverse($csid);
			foreach ($csid as $val) {
				$key = array_search($val, $cs);
				if ($key === false) {
					$csid[] = $val;
					continue;
				}
				if ($act == 'up') {
					if (empty($key)) die('NOT_UP');
					$cs[$key] = $cs[$key - 1];
					$cs[$key - 1] = $val;
				} else {
					if (intval($key) == (count($cs) - 1)) die('NOT_DOWN');
					$cs[$key] = $cs[$key + 1];
					$cs[$key + 1] = $val;
				}
			}
			if ($act == 'down') $csid = array_reverse($csid);
			dbDel('WM_term_group', "`parent`={$idx}");
			$max = 0;
			foreach ($gp as $val) {
				$max++;
				dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$idx}, {$val}, {$max}");
			}
			foreach ($cs as $val) {
				$max++;
				dbNew('WM_term_group', '`parent`, `child`, `permute`', "{$idx}, {$val}, {$max}");
			}
		}
	}

?>

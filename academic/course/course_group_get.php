<?php
	/**
	 * 取得課程群組
	 *
	 * @since   2004//
	 * @author  ShenTing Lin
	 * @version $Id: course_group_get.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}

		// 重新建立 Ticket
		$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->school_id . $_COOKIE['idx']);
		// 取得設定的動作
		$act    = getNodeValue($dom, 'action');
		if (!empty($act)) {
			$res = allGroup2XML(false, true, 'group');
		} else {
			// 取得編碼後的 group_id
			$enc    = getNodeValue($dom, 'group_id');
			// 解碼
			$csid   = trim(sysDecode($enc));
			$csid   = intval($csid);
			$group  = getCoursesList($csid, 'group');
			foreach ($group as $key => $val) {
				// 將 course_id 編碼
				$group[$key][0] = sysEncode($key);
			}
			$res = Group2XML($group);
		}

		header("Content-type: text/xml");
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		echo '<manifest>';
		echo '<ticket>' . $ticket . '</ticket>';
		echo $res;
		echo '</manifest>';
	}

?>

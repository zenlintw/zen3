<?php
	/**
	 * 取得課程群組
	 *
	 * @since   2004/07/21
	 * @author  ShenTing Lin
	 * @version $Id: course_group_get.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/direct/enroll/course_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700300400';
	$sysSession->restore();
	if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	header("Content-type: text/xml");
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'director', $_SERVER['PHP_SELF'], 'domxml open fail!');
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

		// 重新建立 Ticket
		$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->school_id . $_COOKIE['idx']);
		// 取得編碼後的 group_id
		$enc    = getNodeValue($dom, 'group_id');
		// 解碼
		$csid   = intval(sysDecode($enc));
		$group  = getCoursesList($csid, 'group');
		foreach ($group as $key => $val) {
			// 將 course_id 編碼
			$group[$key][0] = sysEncode($key);
		}

		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		echo '<manifest>';
		echo '<ticket>' . $ticket . '</ticket>';
		echo Group2XML($group);
		echo '</manifest>';
	}
?>

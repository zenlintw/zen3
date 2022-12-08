<?php
	/**
	 * 管理者環境的進入教室或辦公室
	 *
	 * @since   2004/10/12
	 * @author  ShenTing Lin
	 * @version $Id: goto_course.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 1, 'academic', $_SERVER['PHP_SELF'], 'DataError');
			die('DataError');
		}

		$envRead = trim(getNodeValue($dom, 'env'));
		switch ($envRead) {
			case 'envStudent': $envRead = 'learn'; break;
			case 'envTeacher': $envRead = 'teach'; break;
			default:
				$envRead = '';
		}
		$envWork = $envRead;
	}
	
	$getSysbar = false;
	require_once(sysDocumentRoot . '/academic/goto.php');

?>

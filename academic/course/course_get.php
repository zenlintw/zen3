<?php
	/**
	 * 課程群組管理
	 *
	 * 建立日期：2002/10/07
	 * @author  ShenTing Lin
	 * @version $Id: course_get.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	header("Content-type: text/xml");
	echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if ($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$csid = sysDecode(getNodeValue($dom, 'course_id'));
			$result = getXMLCourseData($csid);

			if (!empty($result)) {
				die($result);
			} 
		}
	}
	
	die('<manifest />');
?>

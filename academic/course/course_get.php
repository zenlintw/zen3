<?php
	/**
	 * �ҵ{�s�պ޲z
	 *
	 * �إߤ���G2002/10/07
	 * @author  ShenTing Lin
	 * @version $Id: course_get.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
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

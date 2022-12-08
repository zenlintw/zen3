<?php
	/**
	 * 建立或修改課程
	 *
	 * @todo 建立多語系的複合欄位
	 * 建立日期：2002/08/23
	 * @author  ShenTing Lin
	 * @version $Id: course_property.php,v 1.1 2010/02/24 02:40:24 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	define("ENV_TEACHER", true);
	$_POST['ticket'] = md5($sysSession->school_id . $sysSession->school_name . 'Edit' . $sysSession->username);
	// require_once(sysDocumentRoot . '/academic/course/course_property.php');
	require_once(sysDocumentRoot . '/teach/course/m_course_property.php');
?>

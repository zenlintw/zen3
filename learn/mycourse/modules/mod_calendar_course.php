<?php
	/**
	 * 學校行事曆
	 *
	 * @since   2004/09/02
	 * @author  ShenTing Lin
	 * @version $Id: mod_calendar_course.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	if (!defined('MYCOURSE_MODULE') || MYCOURSE_MODULE === false) {
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
		include_once(sysDocumentRoot . '/lib/acl_api.php');
	}
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '2300200400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ary = array('learn', 'teach');
	$res = checkCourseID($sysSession->course_id);
	if (in_array($sysSession->env, $ary) && ($res !== false))
	{
		$id            = 'CalendarCourse';
		$title_caption = $sysSession->course_name . ' ' . $MSG['tabs_calendar_course'][$sysSession->lang];
		$get_who_memo  = $sysSession->course_id;
		$get_type      = 'course';

		require(sysDocumentRoot . '/learn/mycourse/modules/mod_calendar_lib.php');
	}

?>

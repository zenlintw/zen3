<?php
	/**
	 * 學校行事曆
	 *
	 * @since   2004/09/02
	 * @author  ShenTing Lin
	 * @version $Id: mod_calendar_school.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	if (!defined('MYCOURSE_MODULE') || MYCOURSE_MODULE === false) {
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
		include_once(sysDocumentRoot . '/lib/acl_api.php');
	}

	$sysSession->cur_func='2300300400';
	$sysSession->restore();
	if (!aclVerifyPermission(2300300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$id            = 'CalendarSchool';
	$title_caption = $MSG['tabs_calendar_school'][$sysSession->lang];
	$get_who_memo  = $sysSession->school_id;
	$get_type      = 'school';

	require(sysDocumentRoot . '/learn/mycourse/modules/mod_calendar_lib.php');
?>

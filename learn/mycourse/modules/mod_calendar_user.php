<?php
	/**
	 * 個人行事曆
	 *
	 * @since   2004/09/01
	 * @author  ShenTing Lin
	 * @version $Id: mod_calendar_user.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	if (!defined('MYCOURSE_MODULE') || MYCOURSE_MODULE === false) {
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
		include_once(sysDocumentRoot . '/lib/acl_api.php');
	}

	$sysSession->cur_func='2300100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2300100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$id            = 'CalendarUser';
	$title_caption = $MSG['tabs_calendar_user'][$sysSession->lang];
	$get_who_memo  = $sysSession->username;
	$get_type      = 'person';

	require(sysDocumentRoot . '/learn/mycourse/modules/mod_calendar_lib.php');
?>

<?php
	/**
	 * 取得行事曆
	 *
	 * 建立日期：2003//
	 * @author  ShenTing Lin
	 * @version $Id: cale_memo.php,v 1.1 2010/02/24 02:39:04 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	//$sysSession->cur_func='2300200400';
	//$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}

		// 檢查 Ticket
		$ticket = md5($sysSession->username . 'Calendar' . $sysSession->ticket . $sysSession->school_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			header("Content-type: text/xml");
			echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
			echo '<manifest>Access Fail.</manifest>';
			echo '<oticket>'.getNodeValue($dom, 'ticket').'</oticket>';
			echo '<ticket>'.$ticket.'</ticket>';
			exit;
		}

		// 重新建立 Ticket
		setTicket();
		$ticket = md5($sysSession->username . 'Calendar' . $sysSession->ticket . $sysSession->school_id);

		$action = getNodeValue($dom, 'action');
		$calEnv = getNodeValue($dom, 'calEnv');
		switch($calEnv)
		{
			case 'academic': $interface = 'school'; break;
			case 'teach':  	 $interface = 'course'; break;
			case 'direct':	 $interface = 'class';  break;
			case 'learn':	 $interface = 'person';  break;
		}

		$result = '';
		switch ($action) {
			case 'month' : $result = getMonthMemo($dom, $interface); break;
			case 'day'   : $result = getDayMemo($dom, $interface); break;
			case 'save'  : $result = saveMemo($dom, $interface); break;
			case 'delete': $result = delMemo($dom); break;
			case 'set_load': $result= getCalendarSetting(); break;
			case 'set_save': $result= setCalendarSetting($dom); break;
		}
		if (!empty($result)) {
			header("Content-type: text/xml");
			echo $result;
		}
	}
?>

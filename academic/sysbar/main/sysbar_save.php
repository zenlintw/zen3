<?php
	/**
	 * 儲存 sysbar 的設定值
	 *
	 * @since   2004/04/07
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_save.php,v 1.1 2010/02/24 02:38:46 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_lib.php');
	require_once(sysDocumentRoot . '/lang/sysbar_config.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if (!aclVerifyPermission(1300300200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		header("Content-type: text/xml");

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		$ticket = getNodeValue($dom, 'ticket');
		$menu   = genMenuTicket();
		if (in_array($ticket, $menu)) {
			$key = array_search($ticket, $menu);
			$SYSBAR_MENU = $key;
		} else {
			$SYSBAR_MENU = '';
		}

		$res = saveSysbar($dom);
		$msg = $res ? $MSG['save_success'][$sysSession->lang] : $MSG['save_fail'][$sysSession->lang];

		echo '<', '?xml version="1.0" encoding="UTF-8" ?', '>' . "\n",
			 '<manifest><ticket>', $ticket, '</ticket><result>', $msg, '</result></manifest>';
	}

?>

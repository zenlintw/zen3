<?php
	/**
	 * 訊息中心後端處理程式
	 *
	 * 建立日期：2003/04/30
	 * @author  ShenTing Lin
	 * @version $Id: msg_function.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200100100';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	//echo $GLOBALS['HTTP_RAW_POST_DATA'];
	//die();
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	header("Content-type: text/xml");
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'others', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// 檢查 Ticket
		$ticket = md5($sysSession->username . 'Message' . $sysSession->ticket . $sysSession->school_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest>Access Fail.</manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'others', $_SERVER['PHP_SELF'], 'Access Fail!');
			exit;
		}

		// 重新建立 Ticket
		//setTicket();
		//$ticket = md5($sysSession->username . 'Message' . $sysSession->ticket . $sysSession->school_id);

		$action = getNodeValue($dom, 'action');

		$result = '';
		switch ($action) {
			case 'list_folder'   : $result = getFolder(); break;
			case 'manage_folder' : $result = getFolder(); break;
			case 'man_nb_folder' : $result = getFolder(); break;
			case 'save'          : $result = saveFolder($dom); break;
			case 'folder'        :
				$folder_id = getNodeValue($dom, 'folder_id');
				$result    = saveSetting('page_no', '0', '');
				$result    = saveSetting('folder_id', $folder_id, '');
				break;
		}

		if (!empty($result)) {
			echo str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
		} else {
			echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
			echo '<manifest></manifest>';
		}
	}

?>

<?php
	/**
	 * 收入精華區顯示精華區目錄之取 XML 程式
	 *
	 * 建立日期：2004/09/03
	 * @author  KuoYang
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '900300300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/**
	 * 取得精華區資料夾的XML結構
	 * @param object $node : 欲取資料夾之 node id
	 * @param object $full_path : 欲取之完整路徑下(含本身)的結構
	 * @param object $folder_name : 本層名稱
	 * @return string : 資料夾的XML結構語法
	 **/
	function generateQFolderXML($node, $full_path, $folder_name) {
		global $sysSession, $sysConn;

		$RS     = dbGetStMr('WM_bbs_collecting', 'node, subject', "board_id={$sysSession->board_id} and path='{$full_path}' and type='D'", ADODB_FETCH_ASSOC);
		$xmlStr = "<folder id='{$node}'>\n<title>{$folder_name}</title>";
		if ($RS)
		{
			while (!$RS->EOF)
			{
				$new_path = ($full_path == '/' ? '' : $full_path) . '/' . $RS->fields['subject'];
				$xmlStr  .= generateQFolderXML($RS->fields['node'], $new_path, $RS->fields['subject']);
				$RS->MoveNext();
			}
		}
		$xmlStr .= "</folder>\n";
		return $xmlStr;
	}

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	header("Content-type: text/xml");
	echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA']))
		{
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		$action = getNodeValue($dom, 'action');
		$result = '<manifest></manifest>';
		if ($action == 'list_folder') $result = generateQFolderXML('root', '/', '/');
		echo $result;
	}

?>

<?php
	/**
	 * ���J��ذ���ܺ�ذϥؿ����� XML �{��
	 *
	 * �إߤ���G2004/09/03
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
	 * ���o��ذϸ�Ƨ���XML���c
	 * @param object $node : ������Ƨ��� node id
	 * @param object $full_path : ������������|�U(�t����)�����c
	 * @param object $folder_name : ���h�W��
	 * @return string : ��Ƨ���XML���c�y�k
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

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
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

<?php
	/**
	 * ��ذϦ��J���O����ܵ��O���ؿ�
	 *
	 * �إߤ���G2004/08/13
	 * @author  KuoYang
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/forum/lib_nb_dir.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900300900';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	header("Content-type: text/xml");
	echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		$action = getNodeValue($dom, 'action');

		$result = '<manifest></manifest>';
		if ($action == 'list_folder') $result = nb_getFolder();
		echo $result;
	}

?>

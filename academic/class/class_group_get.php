<?php
	/**
	 * ���o��ӽҵ{�s�ժ� XML
	 *
	 * �إߤ���G2002/12/12
	 * @author  ShenTing Lin
	 * @version $Id: class_group_get.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
 	require_once(sysDocumentRoot . '/academic/class/class_tree.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{
	}

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA']))
	{
		$result = csGroup2XML(1000000, false);

		header("Content-type: text/xml");
		if (!empty($result))
		{
			echo $result;
		}
		else
		{
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest></manifest>';
		}
	}
?>

<?php
	/**
	 * 【程式功能】
	 * 建立日期：2004/09/16
	 * @author  Wiseguy Liang
	 * @version $Id: cour_path_import.php,v 1.1 2010/02/24 02:40:23 saly Exp $
	 * @copyright 2004.09 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/teach/course/import_imsmanifest.lib.php');

	$sysSession->cur_func = '1900100200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	if (is_uploaded_file($_FILES['importXmlFile']['tmp_name']))
	{
		processNewImsmanifest($_FILES['importXmlFile']['tmp_name'], $_POST['importMode'] == 'replace');
	}

	@unlink($_FILES['importXmlFile']['tmp_name']);
	header('Location: cour_path.php');
?>

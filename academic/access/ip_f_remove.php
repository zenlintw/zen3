<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C			                      *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                                 *
	 *		Creation  : 2004/07/27                                       		                      *
	 *		work for  : ip filter                                            						  *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                		  *
	 *		identifier: $Id: ip_f_remove.php,v 1.1 2010/02/24 02:38:13 saly Exp $					  *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '600300100';
	$sysSession->restore();
	if (!aclVerifyPermission(600300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!empty($_POST['lists']))
	{
		foreach(explode("\n", $_POST['lists']) as $element)
		{
			if (empty($element)) continue;
			$fields = explode(chr(9), gzuncompress(base64_decode($element)));
			if (count($fields) < 3) continue;
			dbDel('WM_ipfilter', sprintf('username="%s" and host="%s" and mode="%s"', $fields[0], $fields[1], $fields[2]));
		}
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'remove ip filter rules');
	}
	header('Location: ip_filter.php');
?>

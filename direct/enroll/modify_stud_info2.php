<?php
	/**
	 *
	 *
	 * @since   2004/07/01
	 * @author  ShenTing Lin
	 * @version $Id: modify_stud_info2.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/direct_member_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400500';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$DIRECT_MEMBER = true;
	$uri_target = 'modify_stud_info1.php';
	$uri_parent = 'member_detail.php';
	$enc = base64_decode(trim($_POST['username']));
	$username = trim(@mcrypt_decrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $enc, 'ecb'));
	require_once(sysDocumentRoot . '/learn/personal/info1.php');
?>

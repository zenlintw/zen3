<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C			                      *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                                 *
	 *		Creation  : 2004/07/27                                       		                      *
	 *		work for  : ip filter                                            						  *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                		  *
	 *		identifier: $Id: ip_f_save.php,v 1.1 2010/02/24 02:38:13 saly Exp $					  *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '600300100';
	$sysSession->restore();
	if (!aclVerifyPermission(600300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!empty($_POST['username']))
	{
		list($c) = dbGetStSr('WM_user_account', 'count(*)', "username='{$_POST['username']}'", ADODB_FETCH_NUM);
		if ($c == 0)
		{
			echo <<< EOB
<script>
	alert('User "{$_POST['username']}" not found.');
	location.replace('ip_filter.php');
</script>
EOB;
			die();
		}
	}

	if (empty($_POST['rule_id']))
	{
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'new ip filter rule!');
		dbNew('WM_ipfilter', 'username,host,mode', "'{$_POST['username']}','{$_POST['host']}','{$_POST['mode']}'");
	}
	else
	{
		$fields = explode(chr(9), gzuncompress(base64_decode($_POST['rule_id'])));
		if (count($fields) > 3){
			dbSet('WM_ipfilter',
				  sprintf('username="%s", host="%s", mode="%s"', $_POST['username'], $_POST['host'], $_POST['mode']),
				  sprintf('username="%s" and host="%s" and mode="%s"', $fields[0], $fields[1], $fields[2])
				 );
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], 'modify ip filter rule!');
		}
	}

	header('Location: ip_filter.php');
?>

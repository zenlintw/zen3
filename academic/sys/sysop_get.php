<?php
	/**
	 * 取得管理者的資料
	 *
	 *     權限大小：最高 > 高級 > 一般
	 *     權限低的看不到權限高的
	 *     一般管理者不可查詢別校的
	 *
	 * @since   2003/10/14
	 * @author  ShenTing Lin
	 * @version $Id: sysop_get.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/sys/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '100200100';
	$sysSession->restore();
	if (!aclVerifyPermission(100200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 安全性檢查

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			sysFail();
		}

		/**
		 * 1. 是否具備管理者的身份
		 * 2. 是，但是是一般管理者，則檢查是否具備該校管理者的身份
		 * 3. 否，踢掉
		 **/
		$permit = array(
			$sysRoles['manager']       => 0,
			$sysRoles['administrator'] => 1,
			$sysRoles['root']          => 2
		);
		$level = intval(getAllSchoolTopAdminLevel($sysSession->username));
		if ($level < $sysRoles['administrator']) $level = intval(getAdminLevel($sysSession->username));
		if ($level <= 0) sysFail();

		// 一般管理者不可以跨校
		$sid      = intval(getNodeValue($dom, 'sid'));
		if (($level < $sysRoles['administrator']) && ($sid != $sysSession->school_id)) sysFail();

		$sname    = getAllSchoolName();
		$sname    = htmlspecialchars($sname[$sid]);
		$username = getNodeValue($dom, 'username');
		$res      = checkUsername($username);
		if (($res != 2) && ($res != 4)) sysFail();
		$RS       = dbGetStSr('WM_all_account', 'first_name, last_name', "username='{$username}'", ADODB_FETCH_ASSOC);
		$uname    = checkRealname($RS['first_name'], $RS['last_name']);
		$uname    = htmlspecialchars($uname);
		$RS       = dbGetStSr('WM_manager', '`level`, `allow_ip`', "`username`='{$username}' AND `school_id`={$sid} AND `level`<={$level}", ADODB_FETCH_ASSOC);
		$xmlStrs  = <<< BOF
	<sysop>
		<uname>{$username}</uname>
		<name>{$uname}</name>
		<sid>{$sid}</sid>
		<sname>{$sname}</sname>
		<permit>{$permit[$RS['level']]}</permit>
		<limit_ip>{$RS['allow_ip']}</limit_ip>
	</sysop>
BOF;
		header("Content-type: text/xml");
		echo '<manifest>', $xmlStrs, '</manifest>';
	} else {
		sysFail();
	}
?>

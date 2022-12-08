<?php
	/**
	 * ���o�޲z�̪����
	 *
	 *     �v���j�p�G�̰� > ���� > �@��
	 *     �v���C���ݤ����v������
	 *     �@��޲z�̤��i�d�ߧO�ժ�
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

	// �w�����ˬd

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			sysFail();
		}

		/**
		 * 1. �O�_��ƺ޲z�̪�����
		 * 2. �O�A���O�O�@��޲z�̡A�h�ˬd�O�_��ƸӮպ޲z�̪�����
		 * 3. �_�A��
		 **/
		$permit = array(
			$sysRoles['manager']       => 0,
			$sysRoles['administrator'] => 1,
			$sysRoles['root']          => 2
		);
		$level = intval(getAllSchoolTopAdminLevel($sysSession->username));
		if ($level < $sysRoles['administrator']) $level = intval(getAdminLevel($sysSession->username));
		if ($level <= 0) sysFail();

		// �@��޲z�̤��i�H���
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

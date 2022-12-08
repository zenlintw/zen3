<?php
	/**
	 * 公用函式
	 * $Id: lib.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/sys_sysop.php');

	$lines = sysPostPerPage;

	$sopLevel = array(
			$sysRoles['manager']       => $MSG['permit_manager'][$sysSession->lang],       // 一般管理者
			$sysRoles['administrator'] => $MSG['permit_administrator'][$sysSession->lang], // 進階管理者
			$sysRoles['root']          => $MSG['permit_root'][$sysSession->lang]           // 最高管理者
		);

	/**
	 * 未通過檢查，回傳空白訊息
	 **/
	function sysFail() {
		global $sysSession, $_SERVER;
		header("Content-type: text/xml");
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		echo '<manifest></manifest>';
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '未通過檢查');
		exit;
	}
	
	/**
	 * 取得管理者的權限
	 * @return level
	 *        0: 不具備管理者的權限
	 *     2048: 一般管理者
	 *     4096: 進階管理者
	 *     8192: 最高管理者 (一機只有一人)
	 **/
	function getAllSchoolTopAdminLevel($username) {
		return dbGetOne('WM_manager', 'level', "username='{$username}' order by level DESC");
	}

	/**
	 * 取得所有學校的名稱
	 * @return array $res : array(id => name);
	 **/
	function getAllSchoolName() {
		return dbGetAssoc('WM_school', 'school_id, school_name', '1');
	}
?>

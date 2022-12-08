<?php
	/**
	 * ���Ψ禡
	 * $Id: lib.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/sys_sysop.php');

	$lines = sysPostPerPage;

	$sopLevel = array(
			$sysRoles['manager']       => $MSG['permit_manager'][$sysSession->lang],       // �@��޲z��
			$sysRoles['administrator'] => $MSG['permit_administrator'][$sysSession->lang], // �i���޲z��
			$sysRoles['root']          => $MSG['permit_root'][$sysSession->lang]           // �̰��޲z��
		);

	/**
	 * ���q�L�ˬd�A�^�ǪťհT��
	 **/
	function sysFail() {
		global $sysSession, $_SERVER;
		header("Content-type: text/xml");
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		echo '<manifest></manifest>';
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '���q�L�ˬd');
		exit;
	}
	
	/**
	 * ���o�޲z�̪��v��
	 * @return level
	 *        0: ����ƺ޲z�̪��v��
	 *     2048: �@��޲z��
	 *     4096: �i���޲z��
	 *     8192: �̰��޲z�� (�@���u���@�H)
	 **/
	function getAllSchoolTopAdminLevel($username) {
		return dbGetOne('WM_manager', 'level', "username='{$username}' order by level DESC");
	}

	/**
	 * ���o�Ҧ��Ǯժ��W��
	 * @return array $res : array(id => name);
	 **/
	function getAllSchoolName() {
		return dbGetAssoc('WM_school', 'school_id, school_name', '1');
	}
?>

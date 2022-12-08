<?php
	/**
     * 目  的 : 自動點名規則啟用,停用,刪除
     *
     * @since   2005/09/28
     * @author  Edi Chen
     * @version $Id: stud_mailto_chgstats.php,v 1.1 2010/02/24 02:40:31 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
     
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	
	$ticket = md5(sysTicketSeed . 'chgStatus' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang]);
	}
	
	// nids 點名規則編號, func 功能代碼
	$nids = trim($_POST['nids']);
	$func = trim($_POST['func']);
	if (empty($nids) || empty($func)) {
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang] . '_');
	}

	if (!preg_match('/^\d+(,\d+)*$/', $nids))
	{
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang] . '...');
	}

	$msg = '';
	if ($func == 'rm') {	// 刪除
		dbDel('WM_roll_call', 'serial_id in(' . $nids . ')');
		$msg = ($MSG['roll_call_rm'][$sysSession->lang]) . 
			   ($sysConn->Affected_Rows() > 0 ? $MSG['roll_call_set_success'][$sysSession->lang] : $MSG['roll_call_set_fail'][$sysSession->lang]);
	}
	else if ($func == 'enable' || $func == 'disable') {		// 啟用,停用
		dbSet('WM_roll_call', 'enable=\''.$func.'\'', 'serial_id in(' . $nids . ')');				
		$msg = ($func == 'enable' ? $MSG['roll_call_enable'][$sysSession->lang] : $MSG['roll_call_disable'][$sysSession->lang]) . 
			   ($sysConn->Affected_Rows() > 0 ? $MSG['roll_call_set_success'][$sysSession->lang] : $MSG['roll_call_set_fail'][$sysSession->lang]);
	}
	wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], $msg . ', nids = ' . $nids);
	
	echo <<< BOF
	<script language="javascript">
		alert('{$msg}');
		location.href = 'stud_mailto.php?tabs=2';
	</script>
BOF;
?>

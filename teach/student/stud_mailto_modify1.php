<?php
	/**
     * 目  的 : 自動點名規則新增修改(儲存設定)
     *
     * @since   2005/09/30
     * @author  Edi Chen
     * @version $Id: stud_mailto_modify1.php,v 1.1 2010/02/24 02:40:31 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/file_api.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/lang/teach_student.php');

	$ticket = md5(sysTicketSeed . 'modify' . $_COOKIE['idx'] . $_POST['nid']);
	if (trim($_POST['ticket']) != $ticket) {
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$fldsArr = array('course_id'    => "'{$sysSession->course_id}'",
	                 'team_id'      => "'{$_POST['mtTeam']}'",
	                 'group_id'     => "'{$_POST['mtGroup']}'",
	                 'enable'       => "'{$_POST['enable']}'", 
	                 'role'         => "'{$_POST['role']}'",
	                 'mtType'       => "'{$_POST['mtType']}'",
	                 'mtFilter'     => "'{$_POST['mtFilter']}'",
	                 'mtOP'         => "'{$_POST['mtOP']}'",
	                 'mtVal'        => "'{$_POST['mtVal']}'",
	                 'frequence'    => "'{$_POST['frequence']}'",
	                 'freq_extra'   => "'{$_POST['freq_'.$_POST['frequence'].'_day']}'",
	                 'begin_time'   => empty($_POST['begin_time']) ? 'NULL' : "'{$_POST['begin_time']}'",
	                 'end_time'     => empty($_POST['end_time'])   ? 'NULL' : "'{$_POST['end_time']}'",
	                 'mail_subject' =>	"'{$_POST['mail_subject']}'",
	                 'mail_content' =>	"'{$_POST['mail_content']}'",
	                 'mail_cc'      =>	isSet($_POST['mail_cc']) ? 1 : 0);
	
	if ($_POST['nid']) {
		dbSet('WM_roll_call', 
			  substr(vsprintf( vsprintf( str_repeat('%s=%%s,', count($fldsArr)) , array_keys($fldsArr)), $fldsArr), 0, -1),
			  'serial_id='. intval($_POST['nid']));
	
		$update_row = $sysConn->Affected_Rows();
		if($update_row > 0)
			$msg = $MSG['roll_call_rull_modify'][$sysSession->lang] . $MSG['roll_call_set_success'][$sysSession->lang];
		else
			$msg = $MSG['roll_call_not_modify'][$sysSession->lang];

		$sid = $_POST['nid'];
	}
	else {
		dbNew('WM_roll_call', implode(',', array_keys($fldsArr)), implode(',', array_values($fldsArr)) );
		$msg = $MSG['roll_call_rull_add'][$sysSession->lang] . ($sysConn->Affected_Rows() > 0 ? $MSG['roll_call_set_success'][$sysSession->lang] : $MSG['roll_call_set_fail'][$sysSession->lang]);
		$sid = $sysConn->Insert_ID();
	}

	$save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/rollcall/%d/',
		  				 $sysSession->school_id,
		  				 $sysSession->course_id,
		  				 $sid
		  				);

	$attach = array();
	// 處理刪除的附檔 Start
	if ($_POST['nid']) {
		list($temp_attach) = dbGetStSr('WM_roll_call', 'mail_attach', 'serial_id=' . intval($_POST['nid']), ADODB_FETCH_NUM);
		$temp_attach = ereg('^a:[0-9]+:{s:', $temp_attach) ? unserialize($temp_attach) : array();
		$rm_file = is_array($_POST['rm_files']) ? $_POST['rm_files'] : array();

		foreach($temp_attach as $k => $v)
		{
			if (!in_array($v, $rm_file))
			{
				$attach[$k]	= $v;
			}
			else
			{
				unlink($save_path . '/' . $v);
			}
		}

	}
	// 處理刪除的附檔 End

	// 處理新增的附檔 Start
	$isFirst = true;
	if (is_array($_FILES['mail_attach_files']['tmp_name'])) {
		foreach($_FILES['mail_attach_files']['tmp_name'] as $i => $file) {
			if ($isFirst && !is_dir($save_path)) exec("mkdir -p '$save_path'");
			if (is_uploaded_file($file)) {

				$virtualname = @tempnam($save_path, 'WM');
				if ($virtualname !== false){
					@unlink($virtualname);
					$virtualname .= strrchr($_FILES['mail_attach_files']['name'][$i], '.');

					if (move_uploaded_file($file, $virtualname)){

						$attach[htmlspecialchars($_FILES['mail_attach_files']['name'][$i])] = basename($virtualname);
					}
				}
			}
		}
	}
	dbSet('WM_roll_call','mail_attach=' . (empty($attach) ? '' : $sysConn->qstr(serialize($attach))) ,'serial_id=' . $sid);

	if ($_POST['nid']) {	// 修改
		if(array_sum($_FILES['mail_attach_files']['size']) > 0) {
			$update_row = $sysConn->Affected_Rows();

			if($update_row > 0)
				$msg = $MSG['roll_call_rull_modify'][$sysSession->lang] . $MSG['roll_call_set_success'][$sysSession->lang];
			else
				$msg = $MSG['roll_call_not_modify'][$sysSession->lang];

		}
	}
	// 處理新增的附檔 End

	echo '<script language="javascript">alert("'.$msg.'");location.href="stud_mailto.php?tabs=2"</script>';
?>

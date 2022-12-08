<?php
	/**
	 * 刪除議題
	 *
	 * @since   2004/01/16
	 * @author  ShenTing Lin
	 * @version $Id: board_delete.php,v 1.1 2010/02/24 02:38:13 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/board_manage.php');
	require_once(PEAR_INSTALL_DIR . '/System.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = 900100200;
	
	$ticket = md5(sysTicketSeed . 'delBoard' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$tmp = trim($_POST['nids']);
	if (empty($tmp)) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang] . '_');
	}

	// 取出相關的討論版編號
	// 備份資料
	// 刪除相關的夾檔
	// 刪除討論版
	// 刪除議題

	$result = array();
	$nid = explode(',', $tmp);
	foreach ($nid as $val) {
		//list($bid) = dbGetStSr('WM_bbs_boards', '`board_id`', );
		if (list($bid) = dbGetStSr('WM_term_subject', '`board_id`', '`node_id`=' . intval($val), ADODB_FETCH_NUM))
		{
			list($bnm) = dbGetStSr('WM_bbs_boards', '`bname`', '`board_id`=' . $bid, ADODB_FETCH_NUM);
			// 刪除討論版
			dbDel('WM_bbs_boards', '`board_id`=' . $bid);
			if ($sysConn->Affected_Rows() > 0) {
				// 刪除夾檔 (Begin)
				$path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/board/{$bid}";
				if (is_dir($path)) @System::rm("-rf {$path}");
				$path = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/quint/{$bid}";
				if (is_dir($path)) @System::rm("-rf {$path}");
				$path = sysDocumentRoot . "/base/{$sysSession->school_id}/board/{$bid}";
				if (is_dir($path)) @System::rm("-rf {$path}");
				$path = sysDocumentRoot . "/base/{$sysSession->school_id}/quint/{$bid}";
				if (is_dir($path)) @System::rm("-rf {$path}");
				// 刪除夾檔 (End)
				// 刪除張貼
				dbDel('WM_bbs_posts',		'`board_id`=' . $bid);
				dbDel('WM_bbs_order',		'`board_id`=' . $bid);
				dbDel('WM_bbs_collecting',	'`board_id`=' . $bid);
				dbDel('WM_bbs_ranking',		'`board_id`=' . $bid);
				dbDel('WM_bbs_readed',		'`board_id`=' . $bid);
			}
		}

		// 刪除議題
		if (dbDel('WM_term_subject', '`node_id`=' . intval($val)))
		{
			$msg = ($sysConn->Affected_Rows() > 0) ? $MSG['msg_del_success'][$sysSession->lang] : $MSG['msg_del_fail'][$sysSession->lang];
		} else {
			$msg = $MSG['msg_del_fail'][$sysSession->lang] . '_1';
		}

		$lang = getCaption($bnm);
		$result[] = array($lang[$sysSession->lang], $msg);
	}
	wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'delete course subject:' . $tmp);

	$js = <<< BOF
	/**
	 * 回到管理列表
	 **/
	function goManage() {
		window.location.replace("board_manage.php");
	}
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_subject_delete'][$sysSession->lang], 'tabs1');
		showXHTML_tabFrame_B($ary, 1);
			$col = 'class="font01 bg04"';
			showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" class="box01"');
				foreach ($result as $val) {
					$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
					showXHTML_tr_B($col);
						showXHTML_td('', $val[0]);
						showXHTML_td('', $val[1]);
					showXHTML_tr_E();
				}
				// 離開按鈕
				$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="button01" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

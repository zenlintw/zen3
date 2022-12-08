<?php
	/* * ************************************************************************************************
	*                                                                                                *
	* 		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	*                                                                                                *
	* 		Programmer: Wiseguy Liang                                                         *
	* 		Creation  : 2002/10/28                                                            *
	* 		work for  : delete Item                                                           *
	* 		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	*                                                                                                *
	* ************************************************************************************************ */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func = '1600100400';
	} else if (QTI_which == 'homework') {
		$sysSession->cur_func = '1700100400';
	} else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func = '1800100400';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
	// 判斷 ticket 是否正確 (開始)
	$ticket = md5($_POST['gets'] . sysTicketSeed . $course_id . $_COOKIE['idx']);
	if ($ticket != $_POST['ticket']) {
		wmSysLog($sysSession->cur_func, $course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
		die('Illegal Access !');
	}
	// 判斷 ticket 是否正確 (結束)
	if (!ereg('^[A-Z0-9_,]+$', $_POST['lists'])) { // 判斷 ident 序列格式
		wmSysLog($sysSession->cur_func, $course_id, 0, 2, 'auto', $_SERVER['PHP_SELF'], 'ID format error:' . $_POST['lists']);
		die('ID format error !');
	}

	// 如果刪除完成
	$source = sprintf(sysDocumentRoot . '/base/%05d/QTI_share/%s/', $sysSession->school_id, QTI_which);

	// 取得所有題目名稱
	$titleArr = array();
	$lists = str_replace(',', '\',\'', $_POST['lists']);
	$RS = dbGetStMr(
		'WM_qti_share_item',
		'ident, title',
		"category='" . QTI_which . "'  and ident in ('$lists')",
		ADODB_FETCH_ASSOC
	);
	if ($RS) {
		while ($row = $RS->FetchRow()) {
			$titleArr[$row['ident']] = $row['title'];
		}
	}

	$resultArr = array();
	foreach (explode(',', $_POST['lists']) as $id) {
		if (!preg_match('/^[0-9A-Za-z_.-]+$/', $id))
			continue;

		dbDel('WM_qti_share_item', "ident='$id' and course_id=$course_id");
		if ($sysConn->Affected_Rows() > 0) {
			if (!empty($id) && is_dir($source . $id) && chdir($source)) {
				exec("rm -rf '{$id}'");
			}
			$resultArr[$id] = 'remove_success';
		} else { // 刪除失敗的話
			$resultArr[$id] = 'remove_fail';
			$errMsg = $sysConn->ErrorNo() . ' : ' . $sysConn->ErrorMsg();
			wmSysLog($sysSession->cur_func, $course_id, 0, 3, 'auto', $_SERVER['PHP_SELF'], $errMsg);
		}
	}

	$msg = 'remove WM_qti_' . QTI_which . '_item:' . ereg_replace(',WM_ITEM1_[0-9]+_', ',', $_POST['lists']);
	wmSysLog($sysSession->cur_func, $course_id, 0, 0, 'auto', $_SERVER['PHP_SELF'], $msg);

	// 開始 output HTML
	showXHTML_head_B($MSG['item_remove'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		showXHTML_tabFrame_B(array(array($MSG['item_remove'][$sysSession->lang])), 1, null, null, 'style="display: inline" action="item_maintain.php?"' . $_POST['gets'], false, false);
			showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="460" style="border-collapse: collapse" class="box01"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('', $MSG['item_desc'][$sysSession->lang]);
					showXHTML_td('', $MSG['remove_result'][$sysSession->lang]);
				showXHTML_tr_E('', 'width="60%"');
				$css = 'cssTrOdd';
				foreach ($titleArr as $k => $v) {
					showXHTML_tr_B('class="' . ($css = ($css == 'cssTrOdd' ? 'cssTrEvn' : 'cssTrOdd')) . '"');
						showXHTML_td('width="40%"', $v);
						showXHTML_td_B();
						if (is_Array($resultArr[$k])) {
							echo $MSG['remove_fail_ref'][$sysSession->lang];
							foreach ($resultArr[$k] as $exam_title) {
								echo '<br>', $exam_title;
							}
						} else {
							echo $MSG[$resultArr[$k]][$sysSession->lang];
						}
						showXHTML_td_E();
					showXHTML_tr_E();
				}
				showXHTML_tr_B('class="' . ($css = ($css == 'cssTrOdd' ? 'cssTrEvn' : 'cssTrOdd')) . '"');
					showXHTML_td_B('colspan=2 align=center');
						showXHTML_input('submit', '', $MSG['return_item_maintain'][$sysSession->lang], '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

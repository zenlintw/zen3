<?php
	/**
	 * 刪除審核規則
	 *
	 * @since   2004/02/27
	 * @author  ShenTing Lin
	 * @version $Id: review_delete.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '400300700';
	$sysSession->restore();
	if (!aclVerifyPermission(400300700, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket = md5(sysTicketSeed . 'delRule' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$tmp = trim($_POST['nids']);
	if (empty($tmp)) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$result = array();
	$nid = preg_split('/\D+/', $tmp, -1, PREG_SPLIT_NO_EMPTY);
	$rnames = dbGetAssoc('WM_review_syscont', '`flow_serial`,`title`', "`flow_serial` in (" . implode(',', $nid) . ")");
	foreach ($nid as $val) {
		// 刪除討論版
		dbDel('WM_review_syscont', "`flow_serial`={$val}");
		if ($sysConn->Affected_Rows() > 0) {
			// 刪除關聯
			dbDel('WM_review_sysidx', "`flow_serial`='{$val}'");
			$msg = $MSG['msg_del_success'][$sysSession->lang];
		} else {
			$msg = $MSG['msg_del_fail'][$sysSession->lang];
		}
		$lang = getCaption($rnames[$val]);
		$result[] = array($lang[$sysSession->lang], $msg);
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], "刪除討論版{$rname}與關連{$msg}");
	}

	$js = <<< BOF
	/**
	 * 回到管理列表
	 **/
	function gotoList() {
		window.location.replace("review_list.php");
	}
BOF;
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_review_delete'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1);
			$col = 'class="font01 bg04"';
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				foreach ($result as $val) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $val[0]);
						showXHTML_td('', $val[1]);
					showXHTML_tr_E();
				}
				// 離開按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="gotoList()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();

?>

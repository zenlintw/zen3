<?php
	/**
	 * 管理者設定
	 *
	 * @since   2002/11/08
	 * @author  ShenTing Lin
	 * @version $Id: sysop.php,v 1.1 2009-06-25 09:26:05 edi Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/academic/sys/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '100200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/**
	 * 安全性檢查
	 *     1. 身份的檢查
	 *     2. 權限的檢查
	 *     3. .....
	 **/

	// 安全與權限檢查

	// 設定車票
	setTicket();

	// 各項排序依據
	$OB = array(
		'uname' => '`username`',   // 帳號
		'sid'   => '`school_id`',  // 學校編號
		'level' => '`level`'       // 等級
		   );

	// 取出操作此功能的管理者的等級
	$level = intval(getAllSchoolTopAdminLevel($sysSession->username));

	if ($level < $sysRoles['administrator']) $level = intval(getAdminLevel($sysSession->username, $sysSession->school_id));
	if ($level <= 0) {
		die($MSG['not_sysop'][$sysSession->lang]);
	}

	$sqls = '';
	// 設定取出管理者的條件
	switch ($level) {
		case $sysRoles['root']:    // 最高管理者
			$sqls = ' 1 ';
			break;

		case $sysRoles['administrator']:    // 進階管理者
			$sqls = ' username != "' . sysRootAccount . '" AND level <= ' . $level ;
			break;

		default:      // case 2048: 一般管理者
			$sqls = ' username != "' . sysRootAccount . '" AND school_id = ' . $sysSession->school_id . ' AND level <= ' . $level;
	}

	// 計算總共有幾筆資料
	list($total_msg) = dbGetStSr('WM_manager', 'count(*) AS total', $sqls, ADODB_FETCH_NUM);

	// 計算總共分幾頁
	$total_page = max(1, ceil($total_msg / $lines));

	// 產生下拉換頁選單
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['page_all'][$sysSession->lang];

	// 設定下拉換頁選單顯示第幾頁
	$page_no = isset($_POST['page']) ?  max(0, min($_POST['page'], $total_page)) : 1;

	// 取得排序的欄位
	$sb = '';
	$sortby = trim($_POST['sortby']);
	$sb = $OB[$sortby];
	if (empty($sb)) {
		$sortby = 'uname';
		$sb = '`username`';
	}

	// 取得排序的順序是遞增或遞減
	$order = trim($_POST['order']);
	if (empty($order)) $order = 'desc';
	$od = ($order == 'asc') ? 'DESC' : 'ASC';

	// 產生執行的 SQL 指令
	$sqls .= " order by {$sb} {$od} ";
    // 已沒頁數限制
    /*
	if (!empty($page_no)) {
		$limit = intval($page_no - 1) * $lines;
		$sqls .= " limit {$limit}, {$lines} ";
	}
     *
     */

	$sysop = array();
	$user = array();
    
	$RS = dbGetStMr('WM_manager', '*', $sqls, ADODB_FETCH_ASSOC);
	if ($RS) {
		while (!$RS->EOF) {
			$sysop[] = $RS->fields;
            $sysop_r[$RS->fields['school_id']][] = $RS->fields;
            if ($RS->fields['level'] == $sysRoles['root']) {
                $sysop_m[$RS->fields['username']]= $RS->fields;
            }
			$user[$RS->fields['username']] = $RS->fields['username'];
            $user[$RS->fields['creator']] = $RS->fields['creator'];
			$RS->MoveNext();
		}
	}

	$userlist = '\'' . implode('\', \'', $user) . '\'';
	$RS = dbGetStMr('WM_all_account', 'username, first_name, last_name', "username IN ({$userlist})", ADODB_FETCH_ASSOC);
	if ($RS) {
		while (!$RS->EOF) {
            // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
            $realname = checkRealname($RS->fields['first_name'],$RS->fields['last_name']);
            $user[$RS->fields['username']] = $realname;
			$RS->MoveNext();
		}
	}
    
    // 取得目前學校 (不含刪除的)
    if ($level != $sysRoles['root']) {
        $sqlplus = ' AND school_id = '.$sysSession->school_id;
    }
    $RS = dbGetStMr('WM_school', '*', 'school_host not like "[delete]%"'.$sqlplus, ADODB_FETCH_ASSOC);
    if ($RS) {
		while (!$RS->EOF) {
            $school[$RS->fields['school_id']] = $RS->fields;
			$RS->MoveNext();
        }
    }
    foreach($school as $k=>$v) {
        $Da = getConstatnt($k);
        $school[$k]['setTea'] = ($Da['openerDefaultTea']) ? true : false;
    }

	$sysRootAccount = sysRootAccount;
    $curUser = $sysSession->username;
    $smarty->assign('appTitle', $MSG['title'][$sysSession->lang]);
    $smarty->display('common/tiny_header.tpl');
    
    
    $smarty->assign('total_page', $total_page);
    $smarty->assign('cur_theme', $sysSession->theme);
    
    $smarty->assign('sysRootAccount', $sysRootAccount);
    
    $smarty->assign('sysop', $sysop);
    $smarty->assign('sysop_r', $sysop_r);
    $smarty->assign('sysop_m', $sysop_m);

    $smarty->assign('user', $user);
    $smarty->assign('schoolData', $school);
    $smarty->assign('curLevel', $level);
    $smarty->assign('curUser', $curUser);
    $smarty->assign('curSchool', $sysSession->school_id);
    $smarty->assign('sysRoles', $sysRoles);

    $smarty->assign('sopLevel', $sopLevel);

    // 刪除訊息
    $smarty->assign('delMsg', array(
                                1=>$MSG['msg_root'][$sysSession->lang],
                                2=>$MSG['msg_del_deny'][$sysSession->lang],
                                3=>$MSG['msg_del_success'][$sysSession->lang],
                                4=>$MSG['msg_del_fail'][$sysSession->lang]
    ));
	// 畫面輸出
//
//	showXHTML_body_B();
//		$ary = array();
//		$ary[] = array($MSG['tabs_title'][$sysSession->lang], 'tabs1');
//		echo '<div align="center">';
//		showXHTML_tabFrame_B($ary, 1, 'adminModify', null,'action="sysop_del.php" method="post" onsubmit="return delAdmin();" style="display: inline;"', false);
//			$colspan = ($level == $sysRoles['manager']) ? 7 : 9;
//			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabAction"');
//				showXHTML_tr_B('class="cssTrEvn"');
//					showXHTML_td_B('nowrap="nowrap" colspan="' . $colspan . '" id="tb1"');
//						showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
//							showXHTML_tr_B('class="cssTrEvn"');
//								showXHTML_td_B();
//									showXHTML_input('button', 'btnSel1', $MSG['select_all'][$sysSession->lang], '', 'id="btnSel1" onclick="selfunc()" class="cssBtn"');
//									echo '&nbsp;' . $MSG['page'][$sysSession->lang];
//									showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
//									showXHTML_input('button', 'fp', $MSG['btn_page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
//									showXHTML_input('button', 'pp', $MSG['btn_page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
//									showXHTML_input('button', 'np', $MSG['btn_page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
//									showXHTML_input('button', 'lp', $MSG['btn_page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
//								showXHTML_td_E();
//								showXHTML_td_B('align="right"');
//									showXHTML_input('button', 'bd', $MSG['btn_add_admin'][$sysSession->lang], '', 'class="cssBtn" onclick="addAdmin()"');
//									showXHTML_input('submit', 'bd', $MSG['btn_delete'][$sysSession->lang], '', 'id="btn_del" class="cssBtn" disabled');
//								showXHTML_td_E();
//							showXHTML_tr_E();
//						showXHTML_table_E();
//					showXHTML_td_E('');
//				showXHTML_tr_E('');
//
//				$icon_up = '&nbsp;<img border="0" align="asbmiddle" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/dude07232001up.gif">';
//				$icon_dw = '&nbsp;<img border="0" align="asbmiddle" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/dude07232001down.gif">';
//				showXHTML_tr_B('class="cssTrHead"');
//					showXHTML_td_B('nowrap="nowrap" align="center" title="' . $MSG['select_all_msg'][$sysSession->lang] . '"');
//						showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc();"');
//					showXHTML_td_E('');
//					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_number'][$sysSession->lang]);
//					showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(1);" title="' . $MSG['th_alt_username'][$sysSession->lang] . '"');
//						echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
//						echo $MSG['th_username'][$sysSession->lang];
//						echo '</a>';
//						echo ($sortby == 'uname') ? ($order == 'desc' ? $icon_up : $icon_dw) : '';
//					showXHTML_td_E('');
//					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_name'][$sysSession->lang]);
//					if ($level >= $sysRoles['administrator']) {
//						showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(2);" title="' . $MSG['th_alt_school_id'][$sysSession->lang] . '"');
//							echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
//							echo $MSG['th_school_id'][$sysSession->lang];
//							echo '</a>';
//							echo ($sortby == 'sid') ? ($order == 'desc' ? $icon_up : $icon_dw) : '';
//						showXHTML_td_E('');
//						showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_school_name'][$sysSession->lang]);
//					}
//					showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(3);" title="' . $MSG['th_alt_permit'][$sysSession->lang] . '"');
//						echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
//						echo $MSG['th_permit'][$sysSession->lang];
//						echo '</a>';
//						echo ($sortby == 'level') ? ($order == 'desc' ? $icon_up : $icon_dw) : '';
//					showXHTML_td_E('');
//					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_limit_ip'][$sysSession->lang]);
//					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_action'][$sysSession->lang]);
//				showXHTML_tr_E('');
//
//				$sname = getAllSchoolName();
//				$idx = ($page_no == 0) ? 0 : intval($page_no - 1) * $lines;
//				foreach ($sysop as $val) {
//					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//					showXHTML_tr_B($col);
//						showXHTML_td_B('nowrap="nowrap" align="center"');
//
//							if ($val['username'] == sysRootAccount) {
//								echo '&nbsp;';
//							} else if ($level > intval($val['level'])){
//								showXHTML_input('checkbox', 'ckUname[]', $val['username'] . ',' . $val['school_id'], '', 'onclick="selUser(this.checked)"');
//							}
//						showXHTML_td_E('');
//						showXHTML_td('nowrap="nowrap" align="center"', ++$idx);
//						showXHTML_td_B('nowrap="nowrap"');
//							echo $val['username'];
//						showXHTML_td_E('');
//						showXHTML_td('nowrap="nowrap"', $user[$val['username']]);
//						if ($level >= $sysRoles['administrator']) {
//							showXHTML_td('nowrap="nowrap" align="center"', $val['school_id']);
//							showXHTML_td('nowrap="nowrap"', $sname[$val['school_id']]);
//						}
//						showXHTML_td('nowrap="nowrap"', $sopLevel[$val['level']]);
//						showXHTML_td('', nl2br($val['allow_ip']));
//						showXHTML_td_B('align="center"');
//
//							if ($val['username'] == sysRootAccount) {
//								echo '&nbsp;';
//							} else if ($level > intval($val['level'])){
//								showXHTML_input('button', '', $MSG['btn_modify'][$sysSession->lang], '', 'class="cssBtn" onclick="editAdmin(\'' . $val['username'] . '\', ' . $val['school_id'] . ')"');
//							}
//
//						showXHTML_td_E();
//					showXHTML_tr_E('');
//				}
//
//				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//				showXHTML_tr_B($col);
//					showXHTML_td_B('nowrap="nowrap" colspan="' . $colspan . '" id="tb2"');
//					showXHTML_td_E('');
//				showXHTML_tr_E('');
//
//		showXHTML_table_E('');
//		showXHTML_tabFrame_E();
//		echo '</div>';
//		$ary = array();
//		$ary[] = array($MSG['tabs_modify'][$sysSession->lang], 'tb2');
//		showXHTML_tabFrame_B($ary, 1, 'fmProperty', 'tabProperty', 'style="display:inline;"', true);
//			showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');
//				$col = 'class="cssTrOdd"';
//				showXHTML_tr_B($col);
//					showXHTML_td('', $MSG['th_username'][$sysSession->lang]);
//					showXHTML_td_B();
//						showXHTML_input('text', 'opnName', '', '', 'class="cssInput" maxlength="32"');
//						echo '<span id="lstName"></span>';
//					showXHTML_td_E();
//					showXHTML_td('', $MSG['th_help_username'][$sysSession->lang]);
//				showXHTML_tr_E();
//				if ($level >= $sysRoles['administrator']) {
//					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//					showXHTML_tr_B($col);
//						showXHTML_td('', $MSG['th_school'][$sysSession->lang]);
//						showXHTML_td_B();
//							showXHTML_input('select', 'opnSName', $sname, '', 'class="cssInput"');
//						showXHTML_td_E();
//					showXHTML_td('', $MSG['th_help_school'][$sysSession->lang]);
//					showXHTML_tr_E();
//				}
//				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//				showXHTML_tr_B($col);
//					showXHTML_td('', $MSG['th_permit'][$sysSession->lang]);
//					showXHTML_td_B();
//						$permit = array($sopLevel[$sysRoles['manager']]);
//						if ($level == $sysRoles['administrator']) $permit[] = $sopLevel[$sysRoles['administrator']];
//						if ($level == $sysRoles['root']) {
//							$permit[] = $sopLevel[$sysRoles['administrator']];
//							$permit[] = $sopLevel[$sysRoles['root']];
//						}
//						showXHTML_input('radio', 'opnPermit', $permit, '', '', '<br />');
//					showXHTML_td_E();
//					showXHTML_td('', $MSG['th_help_permit'][$sysSession->lang]);
//				showXHTML_tr_E();
//				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//				showXHTML_tr_B($col);
//					showXHTML_td('', $MSG['th_limit_ip'][$sysSession->lang]);
//					showXHTML_td_B();
//						showXHTML_input('textarea', 'opnIP', '', '', 'class="cssInput" cols="25" rows="4"');
//					showXHTML_td_E();
//					showXHTML_td('', $MSG['th_help_limit_ip'][$sysSession->lang]);
//				showXHTML_tr_E();
//				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
//				showXHTML_tr_B($col);
//					showXHTML_td_B('colspan="3" align="center"');
//						showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang]    , '', 'class="cssBtn" onclick="saveAdmin()"');
//						showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="layProperty(false)"');
//					showXHTML_td_E();
//				showXHTML_tr_E();
//			showXHTML_table_E();
//		showXHTML_tabFrame_E();
//
//		showXHTML_form_B('action="sysop.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
//			showXHTML_input('hidden', 'sortby', $sortby, '', '');
//			showXHTML_input('hidden', 'order', $order, '', '');
//			showXHTML_input('hidden', 'page', $page_no, '', '');
//		showXHTML_form_E('');
//
//		showXHTML_form_B('action="sysop_del.php" method="post"', 'adminDelete');
//			showXHTML_input('hidden', 'username', '', '', '');
//			$ticket = md5($sysSession->ticket . $sysSession->school_id . 'delete');
//			showXHTML_input('hidden', 'ticket', $ticket, '', '');
//		showXHTML_form_E('');
//	showXHTML_body_E();
    
    
    
    $smarty->display('academic/m_sysop.tpl');
?>

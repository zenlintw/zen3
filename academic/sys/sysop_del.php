<?php
	/**
	 * 刪除管理者
	 *
	 * @since   2003/10/14
	 * @author  ShenTing Lin
	 * @version $Id: sysop_del.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/academic/sys/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '100400300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// $sysConn->debug = true;
	/**
	 * 安全性檢查
	 *     1. 身份的檢查
	 *     2. 權限的檢查
	 *     3. .....
	 **/

	// 安全與權限檢查

	// 設定車票
	setTicket();

	// 取出操作此功能的管理者的等級
	$level = intval(getAllSchoolTopAdminLevel($sysSession->username));
	if ($level < $sysRoles['administrator']) $level = intval(getAdminLevel($sysSession->username));
	if ($level <= 0) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '不具備管理者的權限!');
		die($MSG['not_sysop'][$sysSession->lang]);
	}

	$sqls  = '';
	$sysop = array();
	$user  = array();

	// 建立刪除人員列表 (Begin)
	foreach ($_POST['ckUname'] as $val) {
		if (!preg_match('/^([\w-]+),(\d+)$/', $val, $ulist)) continue;
		array_shift($ulist);
		// 設定取出管理者的條件
		switch ($level) {
			case $sysRoles['root']:    // 最高管理者
				if ($ulist[0] == sysRootAccount) continue;
				break;

			case $sysRoles['administrator']:    // 進階管理者
				if ($ulist[0] == sysRootAccount) continue;
				break;

			default:      // case 2048: 一般管理者
				if (($ulist[0] == sysRootAccount) || ($ulist[1] != intval($sysSession->school_id))) continue;
		}
		$sqls = "`username`='{$ulist[0]}' AND `school_id`={$ulist[1]}";
		$RS = dbGetStSr('WM_manager', '*', $sqls, ADODB_FETCH_ASSOC);
		$sysop[] = $RS;
		$user[$RS['username']] = $RS['username'];
	}
	// 建立刪除人員列表 (End)

	$userlist = '"' . implode('", "', $user) . '"';
	$RS = dbGetStMr('WM_all_account', 'username, first_name, last_name', "username IN ({$userlist})", ADODB_FETCH_ASSOC);
	if ($RS) {
		while (!$RS->EOF) {
			$user[$RS->fields['username']] = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
			$RS->MoveNext();
		}
	}

	$js = <<< BOF
	var MSG_SELECT_ADMIN   = "{$MSG['msg_need_select'][$sysSession->lang]}";
	var MSG_DEL_HELP       = "{$MSG['msg_del_help'][$sysSession->lang]}";

	function delAdmin() {
		var obj = null, nodes = null, attr = null;
		var cnt = 0;
		obj = document.getElementById("tabAction");
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type == "checkbox") && nodes[i].checked) {
				attr = nodes[i].getAttribute("explode");
				if (attr != null) continue;
				cnt++;
			}
		}
		if (cnt == 0) {
			alert(MSG_SELECT_ADMIN);
			return false;
		}
		return true;
	}

	function DelMag(val){
		var obj = null,nodes = null;
		var total_num = 0,cnt = 0,attr = null;

		obj = document.getElementById("tabAction");
		nodes = obj.getElementsByTagName("input");

		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("exclude");

			if ((nodes[i].type == "checkbox") && (attr == null)) {
				total_num++;
				if (nodes[i].checked) cnt++;
			}

		}

		var top_ck = document.getElementById('ck');
		if (total_num == cnt){
			top_ck.checked = true;
		}else{
			top_ck.checked = false;
		}

	}

	window.onload = function () {
		select_func('', this.checked);
		document.getElementById("tb2").innerHTML = document.getElementById("tb1").innerHTML;
	}
BOF;
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_del_admin'][$sysSession->lang], 'tabs1');
		showXHTML_tabFrame_B($ary, 1, 'adminModify', null,'action="sysop_del1.php" method="post" onsubmit="return delAdmin();" style="display: inline;"', false);
			$colspan = ($level == $sysRoles['manager']) ? 6 : 8;
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabAction"');
				showXHTML_tr_B();
					showXHTML_td('colspan="' . $colspan . '" class="cssTrHead font01"', $MSG['msg_del_help'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="center" nowrap="nowrap" colspan="' . $colspan . '" id="tb1" class="cssInput"');
						showXHTML_input('submit', 'bd', $MSG['btn_ok_del'][$sysSession->lang], '', 'class="button01"');
						showXHTML_input('button', 'bd', $MSG['btn_return'][$sysSession->lang], '', 'class="button01" onclick="location.replace(\'./sysop.php\')"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

				showXHTML_tr_B('class="cssTrHead font01"');
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_number'][$sysSession->lang]);
					showXHTML_td_B('nowrap="nowrap" align="center" title="' . $MSG['select_all_msg'][$sysSession->lang] . '"');
						showXHTML_input('checkbox', 'ck', '', '', 'exclude="true" checked="checked" onclick="select_func(\'\', this.checked);"');
					showXHTML_td_E('');
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_name'][$sysSession->lang]);
					if ($level >= $sysRoles['administrator']) {
						showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_school_id'][$sysSession->lang]);
						showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_school_name'][$sysSession->lang]);
					}
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_permit'][$sysSession->lang]);
					showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_limit_ip'][$sysSession->lang]);
				showXHTML_tr_E('');

				$sname = getAllSchoolName();
				$idx = 0;
				foreach ($sysop as $val) {
					$col = ($col == 'class="cssTrEvn font01"') ? 'class="cssTrOdd font01"' : 'class="cssTrEvn font01"';
					showXHTML_tr_B($col);
						showXHTML_td('nowrap="nowrap" align="center"', ++$idx);
						showXHTML_td_B('nowrap="nowrap" align="center"');
							showXHTML_input('checkbox', 'ckUname[]', $val['username'] . ',' . $val['school_id'], '', 'onclick="DelMag(this.checked)"');
						showXHTML_td_E('');
						showXHTML_td_B('nowrap="nowrap"');
							echo $val['username'];
						showXHTML_td_E('');
						showXHTML_td('nowrap="nowrap"', $user[$val['username']]);
						if ($level >= $sysRoles['administrator']) {
							showXHTML_td('nowrap="nowrap" align="center"', $val['school_id']);
							showXHTML_td('nowrap="nowrap"', $sname[$val['school_id']]);
						}
						showXHTML_td('nowrap="nowrap"', $sopLevel[$val['level']]);
						showXHTML_td('', nl2br($val['allow_ip']));
					showXHTML_tr_E('');
				}

				$col = ($col == 'class="cssTrEvn font01"') ? 'class="cssTrOdd font01"' : 'class="cssTrEvn font01"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" nowrap="nowrap" colspan="' . $colspan . '" id="tb2" class="cssInput"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

			showXHTML_table_E('');
		showXHTML_tabFrame_E();

	showXHTML_body_E();
?>

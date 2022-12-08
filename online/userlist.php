<?php
	/**
	 * 線上傳訊
	 *
	 * @since   2003/11/05
	 * @author  ShenTing Lin
	 * @version $Id: userlist.php,v 1.1 2009-06-25 09:27:37 edi Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/msg_online.php');
	require_once(sysDocumentRoot . '/online/msg_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2100100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 預設每頁幾筆資料
	$lines = sysPostPerPage;

	// 取得本身具不具備管理者與老師身分
	$canSeeOther = false ||
				   aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) ||
				   aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager']) ||
				   aclCheckRole($sysSession->username, $sysRoles['director']);

	// 取得 Session 中所有的課程編號
	$cslst = array(
			   0=>$MSG['all'][$sysSession->lang],
		10000000=>$MSG['in_school'][$sysSession->lang]
	);
	$total = 0;
	$incs  = 0;
	// $RS = dbGetStMr('WM_session', 'DISTINCT course_id, course_name', '`chance`=0');
	$RS = dbGetStMr('WM_session', 'course_id, course_name, count(*) AS cnt', '`chance`<3 group by `course_id`', ADODB_FETCH_ASSOC);
	while (!$RS->EOF) {
		if (!empty($RS->fields['course_id']) && ($RS->fields['course_id'] != 10000000)) {
			$cslst[$RS->fields['course_id']] = sprintf('(%d) %s', intval($RS->fields['cnt']), $RS->fields['course_name']);
			$incs += intval($RS->fields['cnt']);
		}
		$total += intval($RS->fields['cnt']);
		$RS->MoveNext();
	}
	$cslst[0]        = sprintf('(%d) %s', $total, $MSG['all'][$sysSession->lang]);
	$cslst[10000000] = sprintf('(%d) %s', max($total - $incs, 0), $MSG['in_school'][$sysSession->lang]);

	// 取得目前身處的或切換的課程編號
	if (isset($_POST['course'])) {
		$course = intval(trim($_POST['course']));
	} else {
		$course = empty($sysSession->course_id) ? 10000000 : intval($sysSession->course_id);
	}
	if (empty($course)) {
		$sqls = 'S.`chance`<3 group by S.username order by S.username';
	} else if ($course == 10000000) {
		$sqls = 'S.`course_id`<10000001 AND S.`chance`<3 group by S.username order by S.username';
	} else {
		$sqls = "S.`course_id`={$course} AND S.`chance`<3 group by S.username order by S.username";
	}

	// 計算總共有幾筆資料
	// $sysConn->debug = true;
	$field = 'S.username, S.realname, S.course_id, S.cur_func, count(*) AS cnt, S.ip, I.status, I.recive, I.talk';
	$RS = dbGetStMr('WM_session as S left join WM_im_setting as I on S.username=I.username',
					$field,
					$sqls,
					ADODB_FETCH_ASSOC);
	$total_user = $RS->RecordCount();

	// 計算總共分幾頁
	$total_page = ceil($total_user / $lines);

	// 產生下拉換頁選單
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['all'][$sysSession->lang];

	// 設定下拉換頁選單顯示第幾頁
	$page_no = isset($_POST['page']) ? min($total_page, max($_POST['page'], 0)) : 1;

	if ($page_no) {
		$limit = ($page_no - 1) * $lines;
		$sqls .= " limit {$limit}, {$lines} ";
		$RS = dbGetStMr('WM_session as S left join WM_im_setting as I on S.username=I.username',
						$field,
						$sqls,
						ADODB_FETCH_ASSOC);
	}

    $doResize = $_SERVER['REQUEST_METHOD'] == 'GET' ? 'true' : 'false';
	$js  = $sendJS;
	$js .= <<< BOF

	var theme = "{$sysSession->theme}";
	var lang  = "{$lang}";
	var total_page = "{$total_page}";

	/**
	 * 切換所在位置
	 * @param integer val : 課程編號
	 **/
	function chgPos(val) {
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.course.value = val;
		obj.submit();
	}

	/**
	 * 換頁
	 * @param integer n : 換頁的動作或頁數
	 **/
	function go_page(n){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		switch(n){
			case -1:	// 第一頁 (First)
				obj.page.value = 1;
				break;
			case -2:	// 前一頁 (Prev)
				obj.page.value = parseInt(obj.page.value) - 1;
				if (parseInt(obj.page.value) == 0) obj.page.value = 1;
				break;
			case -3:	// 後一頁 (Next)
				obj.page.value = parseInt(obj.page.value) + 1;
				break;
			case -4:	// 最末頁 (Last)
				obj.page.value = parseInt(total_page);
				break;
			default:	// 指定某頁 (Special)
				obj.page.value = parseInt(n);
				break;
		}
		obj.submit();
	}
// ////////////////////////////////////////////////////////////////////////////
	function ckSync() {
		var nodes = null, attr = null;
		var obj = document.getElementById("tabs1");

		if ((typeof(obj) != "object") || (obj == null)) return false;
		nodes = obj.getElementsByTagName("input");
		obj = document.getElementById("selCk");
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type == "checkbox") {
				attr = nodes[i].getAttribute("exclude");
				if ((attr != null) && (attr == "true")) continue;
				if (!nodes[i].checked) {
					obj.checked = false;
					return true;
				}
			}
		}
		obj.checked = true;
		return true;
	}
// ////////////////////////////////////////////////////////////////////////////
	function msgSetting() {
		window.location.replace("/online/msg_set.php");
	}

	function msgHistory() {
		window.location.replace("/online/msg_history.php");
	}
// ////////////////////////////////////////////////////////////////////////////
	window.onload = function () {
		var obj1 = null, obj2 = null;
		obj1 = document.getElementById("tb11");
		obj2 = document.getElementById("tb12");
		if ((obj1 != null) && (obj2 != null)) {
			obj2.innerHTML = obj1.innerHTML;
		}
		obj1 = document.getElementById("tb21");
		obj2 = document.getElementById("tb22");
		if ((obj1 != null) && (obj2 != null)) {
			obj2.innerHTML = obj1.innerHTML;
		}

		buildUserList();

		if ({$doResize}) // 翻頁時不需要再調整大小
		{
			var obj = document.getElementById("tabs1");
			var xW = 400, xH = 200;
			xW = parseInt(obj.offsetWidth)  + 40;
			// xH = parseInt(obj.offsetHeight) + 100;
			xH = 400;
			if (typeof(window.dialogWidth) == "undefined") {
				parent.window.resizeTo(xW, xH);
			} else {
				window.dialogWidth  = xW + "px";
				window.dialogHeight = xH + "px";
			}
		}
	};
BOF;
	$isAdmin = aclCheckRole($sysSession->username, $sysRoles['manager']|$sysRoles['administrator']|$sysRoles['root']);
	showXHTML_head_B($MSG['tabs_user_list'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', 'hotkey.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('leftmargin="15" topmargin="10"');
// showXHTML_tabFrame_B($ary, default, form_id, table_id, form_extra, isDragable);
		$ary = array();
		$ary[] = array($MSG['tabs_user_list'][$sysSession->lang], 'tabs1');
		showXHTML_tabFrame_B($ary, 1, '', 'tabsMsgView');
			$cols = (!$isAdmin)?'7':'8';
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs1" class="cssTable"');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="' . $cols . '" nowrap="nowrap" id="tb11"');
						echo $MSG['msg_position'][$sysSession->lang];
						showXHTML_input('select', '', $cslst, $course, 'class="cssInput" onchange="chgPos(this.value)"');
						echo '&nbsp;';
						showXHTML_input('button', '', $MSG['btn_setting'][$sysSession->lang] , '', 'class="cssBtn" onclick="msgSetting()"');
					showXHTML_td_E();
				showXHTML_tr_E('');
				// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="' . $cols . '" nowrap="nowrap" id="tb21"');
						showXHTML_input('button', '', $MSG['btn_send_mutil_msg'][$sysSession->lang], '', 'class="cssBtn" onclick="msgWriteMutil()"');
						showXHTML_input('button', '', $MSG['btn_send_mutil_call'][$sysSession->lang], '', 'class="cssBtn" onclick="callWriteMutil()"');
						echo '&nbsp;&nbsp;';
						showXHTML_input('button', 'fp', $MSG['page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
						showXHTML_input('button', 'pp', $MSG['page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
						showXHTML_input('button', 'np', $MSG['page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
						showXHTML_input('button', 'lp', $MSG['page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
						echo $MSG['page_no_1'][$sysSession->lang];
								showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
						echo $MSG['page_no_2'][$sysSession->lang];
						echo '&nbsp;&nbsp;';
						showXHTML_input('button', '', $MSG['btn_view_msg'][$sysSession->lang], '', 'class="cssBtn" onclick="msgHistory()"');
					showXHTML_td_E();
				showXHTML_tr_E('');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" title="' . $MSG['alt_select_help'][$sysSession->lang] . '"');
						showXHTML_input('checkbox', '', '', '', 'id="selCk" exclude="true" onclick="select_func(\'tabs1\', this.checked)"');
					showXHTML_td_E();
					showXHTML_td('align="center"', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_realname'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_number'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_behavior'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_send_msg'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th_chat'][$sysSession->lang]);
					if ($isAdmin)
					{
						showXHTML_td('align="center"', $MSG['th_ip'][$sysSession->lang]);
					}
				showXHTML_tr_E('');
				// 開始顯示資料 (Begin)
				if ($RS) {
					while (!$RS->EOF) {
						$username = $RS->fields['username'];
						// list($status,$recive, $talk) = dbGetStSr('WM_im_setting', '`status`,`recive`,`talk`', "`username`='{$username}'");
						if ($username == $sysSession->username) $RS->fields['status'] = 'online';
						// 當帳號還未是否可以傳訊時 預設 可以 [ 傳訊 ]  & [ 對談 ]
						if(($RS->fields['recive'] == '') && ($RS->fields['talk'] == '')){
							$RS->fields['recive'] = 'Y';
							$RS->fields['talk'] = 'Y';
						}
						if ($canSeeOther || ($RS->fields['status'] != 'Invisible')) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								if ($RS->fields['username'] == $sysSession->username) {
									showXHTML_td('align="center"', '&nbsp;');
								} else {
									showXHTML_td_B('align="center"');
										showXHTML_input('checkbox', 'username', $username, '', 'onclick="ckSync()"');
									showXHTML_td_E();
								}
								showXHTML_td('', $username);
								showXHTML_td('', $RS->fields['realname']);
								showXHTML_td('align="center"', $RS->fields['cnt']);
								showXHTML_td('align="center"', getACLFunctionCaption($RS->fields['cur_func']));
								if ($RS->fields['username'] == $sysSession->username) {
									showXHTML_td('align="center"', '<img src="/theme/default/sys/stop.gif">');
									showXHTML_td('align="center"', '<img src="/theme/default/sys/stop.gif">');
								} else {

										if ($RS->fields['recive'] == 'Y')
										{
											showXHTML_td_B('align="center"');
											showXHTML_input('button', '', $MSG['btn_send_msg'][$sysSession->lang], '', 'class="cssBtn" onclick="msgWrite(\'' . $username . '\');"');
											showXHTML_td_E();
										}else{
											showXHTML_td('align="center"', '<img src="/theme/default/sys/stop.gif">');
										}

										if ($RS->fields['talk'] == 'Y')
										{
											showXHTML_td_B('align="center"');
											showXHTML_input('button', '', $MSG['btn_call'][$sysSession->lang], '', 'class="cssBtn" onclick="callWrite(\''. $username . '\');"');
											showXHTML_td_E();
										}else{
											showXHTML_td('align="center"', '<img src="/theme/default/sys/stop.gif">');
										}

								}
							if ($isAdmin) 	showXHTML_td('align="center"', $RS->fields['ip']);
							showXHTML_tr_E('');
						}
						$RS->MoveNext();
					}
				}

				// 開始顯示資料 (End)
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="' . $cols . '" nowrap="nowrap" id="tb22"');
					showXHTML_td_E();
				showXHTML_tr_E('');
				// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="' . $cols . '" nowrap="nowrap" id="tb12"');
					showXHTML_td_E();
				showXHTML_tr_E('');
				// 關閉按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="' . $cols . '" nowrap="nowrap"');
						showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang] , '', 'class="cssBtn" onclick="parent.window.close()"');
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E();
		showXHTML_tabFrame_E();

		showXHTML_form_B('action="userlist.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
			showXHTML_input('hidden', 'page'   , $page_no, '', '');
			showXHTML_input('hidden', 'course' , $course , '', '');
		showXHTML_form_E('');

		$btns = array(
			array($MSG['btn_goto_list'][$sysSession->lang], 'msgLayer(false)')
		);
		msgSendWin('', $btns);
	showXHTML_body_E();
?>

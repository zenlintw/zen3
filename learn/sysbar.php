<?php
	/**
	 * 教室環境的 sysbar
	 * @todo
	 *     1. 自動執行第一項主選單
	 *     2. 點選主選單後，自動執行第一項子選單
	 *     3. 顯示學員的帳號
	 *     4. 顯示系統時間
	 *     5. 登出
	 *     6. 顯示線上人數
	 *     7. 切換課程
	 *     8. 切換教室跟辦公室
	 *     9. 是否要 pop up 顯示個人行事曆
	 * $Id: sysbar.php,v 1.1 2010-02-24 02:39:05 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	$sysSession->env = 'learn';
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_cal_alert.php');
	require_once(sysDocumentRoot . '/lang/sysbar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='1300400100';
	//$sysSession->restore();
	if (!aclVerifyPermission(1300400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$Theme = "/theme/{$sysSession->theme}/learn";
	$lang = strtolower($sysSession->lang);

	//  判斷 行事曆 是否有無 當天 要提醒的事 (begin)
	list($login_alert,$alert_num,$alert_date) = dbGetStSr('WM_cal_setting','login_alert,alert_num,alert_date',"username='{$sysSession->username}'", ADODB_FETCH_NUM);

	$sys_date = date('Y-m-d');

	if ($login_alert == 'N'){
		if (empty($alert_num) || empty($alert_date)){
			dbSet('WM_cal_setting',"alert_num=1,alert_date=NOW()", "username='{$sysSession->username}'");
		} else if ($sys_date != $alert_date){
			dbSet('WM_cal_setting',"alert_date=NOW()", "username='{$sysSession->username}'");

			if ($alert_num > 0){
				dbSet('WM_cal_setting',"alert_num=0", "username='{$sysSession->username}'");

				list($login_alert,$alert_num,$alert_date) = dbGetStSr('WM_cal_setting','login_alert,alert_num,alert_date',"username='{$sysSession->username}'", ADODB_FETCH_NUM);
			}
		}
	}

	// calendar kind
	$cal_type = array('personal','course','class','school');
	$cal_num = count($cal_type);

	// compute total calender number
    $total_cal = 0;

	$cal_msg = GetCalendarAlert();

	if ($doc = @domxml_open_mem(preg_replace('/\sxmlns\s*=\s*"[^"]*"\s/U', ' ', $cal_msg))) {

		// for begin
		for ($i = 0;$i < $cal_num;$i++){
			// init xpath
	   	 	$xpath = @xpath_new_context($doc);

	   	 	// 取得 個人、課程、班級、學校的 總數(num)  的值
	   	 	$obj          = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '[@num > 0]/memo');
	   	 	$page_nodeset = $obj->nodeset;
	   	 	$total_count  = count($page_nodeset);

	   	 	// 取得 行事曆 的 每篇 有無登入要顯示
			$type_obj   = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '/memo/alert_type/text()');
	   	 	$type_obj1  = $type_obj->nodeset[0];
			$login_type = $type_obj1->content;

	   	 	// 判斷 有無 num 的值
	   	 	if ($total_count > 0){
				if  (strpos($login_type,"login")!==false) {
	 			$cal_count[] = $total_count;
	   	 		}
	   	 	}
		}
   }

	if ($cal_count && count($cal_count) > 0){
		$cal_count = array_sum($cal_count);
	}else{
		$cal_count = 0;
	}

	// 要切換到哪個選單項目 (Begin)
	$label = $sysSession->goto_label;
	$sysSession->goto_label = '';    // 用過就清除
	$sysSession->restore();
	// 要切換到哪個選單項目 (End)
	//  判斷 行事曆 是否有無 當天 要提醒的事 (end)

	$isPopCal = popCal(5);
    $isMobile = isMobileBrowser() ? 'true' : 'false';

$js = <<< BOF
    var session_hash = 'reload';
    var isMobile = {$isMobile};
	var username = "{$sysSession->username}";
	var lang = '{$lang}';
	var MSG_SysError          = "{$MSG['system_error'][$sysSession->lang]}";
	var MSG_NotSupportBrowser = "{$MSG['not_support_browser'][$sysSession->lang]}";
	var MSG_CantLoadLib       = "{$MSG['need_lib'][$sysSession->lang]}";
	var MSG_NoTitle           = "{$MSG['no_title'][$sysSession->lang]}";
	var MSG_NEED_VARS         = "{$MSG['msg_need_vars'][$sysSession->lang]}";
	var MSG_DATA_ERROR        = "{$MSG['msg_data_error'][$sysSession->lang]}";
	var MSG_IP_DENY           = "{$MSG['msg_ip_deny'][$sysSession->lang]}";
	var MSG_ADMIN_ROLE        = "{$MSG['msg_admin_role'][$sysSession->lang]}";
	var MSG_DIRECTOR_ROLE     = "{$MSG['msg_director_role'][$sysSession->lang]}";
	var MSG_TEACHER_ROLE      = "{$MSG['msg_teacher_role'][$sysSession->lang]}";
	var MSG_STUEDNT_ROLE      = "{$MSG['msg_student_role'][$sysSession->lang]}";
	var MSG_SLID_ERROR        = "{$MSG['msg_sid_error'][$sysSession->lang]}";
	var MSG_CAID_ERROR        = "{$MSG['msg_caid_error'][$sysSession->lang]}";
	var MSG_CSID_ERROR        = "{$MSG['msg_csid_error'][$sysSession->lang]}";
	var MSG_CS_DELTET         = "{$MSG['msg_course_delete'][$sysSession->lang]}";
	var MSG_CS_NOT_OPEN       = "{$MSG['msg_course_close'][$sysSession->lang]}";
	var MSG_BAD_BOARD_ID      = "{$MSG['msg_bad_board_id'][$sysSession->lang]}";
	var MSG_BAD_BOARD_RANGE   = "{$MSG['msg_bad_board_range'][$sysSession->lang]}";
	var MSG_BOARD_NOTOPEN     = "{$MSG['msg_board_notopen'][$sysSession->lang]}";
	var MSG_BOARD_CLOSE       = "{$MSG['msg_board_closed'][$sysSession->lang]}";
	var MSG_BOARD_DISABLE     = "{$MSG['msg_board_disable'][$sysSession->lang]}";
	var MSG_BOARD_TAONLY      = "{$MSG['msg_board_taonly'][$sysSession->lang]}";
	var MSG_IN_CHAT_ROOM      = "{$MSG['msg_in_chat'][$sysSession->lang]}";

	var fmDefault    = "s_main";
	var sysGotoLabel = "{$label}";

	// compute that show the numbers of calender
	var cal_count    = {$cal_count};

	// 是否 login alert calendar
	var login_alert  = "{$login_alert}";
	var alert_num    = "{$alert_num}";
	var alert_date   = "{$alert_date}";
	var sys_date     = "{$sys_date}";

	var CalWin       = null;
	var isPopCal     = "{$isPopCal}";

	function showCalList() {
		if ((CalWin != null) && !CalWin.closed) {
			CalWin.focus();
		} else {
			CalWin = showDialog("calender_alert.php", false , "", true, "200px", "300px", "500px", "400px", "status=0, resizable=1, scrollbars=1");
		}
	}

	function go_evn(val) {
		if (typeof(parent.s_main.notSave) == 'boolean' && parent.s_main.notSave) {
			if (!confirm(parent.s_main.MSG_EXIT)) return;
			else parent.s_main.notSave = false;
		}

		parent.window.onbeforeunload = null;
		switch (val) {
			case 1:
				parent.location.replace("/academic/index.php");
				break;
			case 2:
				parent.location.replace("/direct/index.php");
				break;
		}

	}

	window.onload = function () {
		showSysTime('PM 00:00:00');
		initSysbar("goto_course.php");
		if ((typeof(parent.session) == "object") && (typeof(parent.session.touchSession) == "function")) {
			parent.session.touchSession();
		}
		if (isPopCal == 'Y') {
			if (login_alert == 'Y') {
				if (cal_count > 0){
					showCalList();
				}
			} else if ((alert_num == 0) || (sys_date != alert_date)) {
				if (cal_count > 0) {
					showCalList();
				}
			}
		}
		rePosition();
	};

	// 判斷是否按下F5
	window.document.onkeydown = function (evnt) {
		if (typeof evnt == "object") event = evnt;
		parent.reloadKey = (event.keyCode == 116) ? true : false;
	};

BOF;

	showXHTML_head_B($MSG['student_sysbar'][$sysSession->lang]);
	showXHTML_css('include', "{$Theme}/sysbar.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/sysbar.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0"');
	echo '<div style="height: 90px; overflow: hidden;">';
		// 背景的部分
		showXHTML_table_B('width="796" border="0" cellspacing="0" cellpadding="0" id="SysLayout"');
			showXHTML_tr_B('class="cssBg01"');
				// Logo (使用介面函式會使版面走樣，所以自己輸出)
				echo '<td id="logoTd"><div id="logoDiv" style="width: 200px; height: 66px; overflow: hidden;">';
				$logo = getThemeFile('logo.gif');
				if (empty($logo)) $logo = $Theme . '/logo.gif';
				echo '<img src="' . $logo . '" border="0" id="logoImg">';
				echo '</div></td>';
				// 學員帳號
				showXHTML_td_B('valign="top" id="mainTd"');
					// 目前使用者正在那個環境的string
					$env_str = $MSG['msg_learn'][$sysSession->lang];

					printf("<div class=\"sysUsername\">{$MSG['regards'][$sysSession->lang]}</div>", $sysSession->username,$env_str);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			// 切換課程的部分 (Begin)
			showXHTML_tr_B('class="cssBg02"');
				showXHTML_td_B('width="200" height="24" align="center" valign="middle" class="SMenuItemOut"');
				if ($sysSession->username == 'guest')
				{
					if (!isset($sysSession->course_id))
					{
						echo '&nbsp;';
					}else{
						list($c_caption) = dbGetStSr('WM_term_course','caption',"course_id='{$sysSession->course_id}'", ADODB_FETCH_NUM);
						if ($c_caption) echo fetchTitle($c_caption);
					}
				}else{
					$selary[10000000] = $MSG['my_courses'][$sysSession->lang];

					$selcs = array(); $teach = array();
					if ($rs = dbGetCourses('M.course_id,C.caption', $sysSession->username, $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']))
					    while($fields = $rs->FetchRow())
					        $teach[$fields['course_id']] = $fields['caption'];

					if ($rs = dbGetCourses('M.course_id,C.caption', $sysSession->username, $sysRoles['auditor']|$sysRoles['student']))
					    while($fields = $rs->FetchRow())
					        $selcs[$fields['course_id']] = $fields['caption'];

					$opts = '<option value="' . 10000000 . '">' . $MSG['my_courses'][$sysSession->lang] . '</option>';
					if (is_array($teach) && count($teach))
					{
						$opts .= '<optgroup label="' . $MSG['msg_list_teacher'][$sysSession->lang] . '">';
						foreach($teach as $tid => $title)
						{
							$opts .= '<option value="' . $tid . '"' . ($tid==$sysSession->course_id ? ' selected' : '') . '>' . fetchTitle($title) . '</option>';
						}
						$opts .= '</optgroup>';
						$opts .= '<optgroup label="' . $MSG['msg_list_student'][$sysSession->lang] . '">';
					}

					foreach($selcs as $tid => $title)
					{
						if (isset($teach[$tid])) continue;
						$opts .= '<option value="' . $tid . '"' . ($tid==$sysSession->course_id ? ' selected' : '') . '>' . fetchTitle($title) . '</option>';
					}

					if (is_array($teach) && count($teach)) $opts .= '</optgroup>';

					echo '<select size="1" name="selcourse" id="selcourse" class="cssInput" style="width:188px;" onchange="parent.chgCourse(this.value, 1, 1)" onclick="event.cancelBubble = true;">',
						 $opts,
						 '</select>';
				}
				showXHTML_td_E('');
				showXHTML_td('', '&nbsp;');
			showXHTML_tr_E('');
			// 切換課程的部分 (End)
		showXHTML_table_E('');

		// 主選單
		echo '<div class="MContainer" id="MContainer"><div class="MMenu" id="MMenu"></div></div>';
		// 子選單
		echo '<div class="SContainer" id="SContainer"><div class="SMenu" id="SMenu"></div></div>';

		// 主選單的左右移動按鈕
		echo '<div class="MScroll" id="MScroll">' . "\n";
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('nowrap');
					$title = $MSG['move_left'][$sysSession->lang];
					echo "<span id=\"MLeft\" style=\"visibility: hidden;\"><a href=\"javascript:;\" onclick=\"ScrollMenu(1)\" title=\"{$title}\"><img src=\"{$Theme}/mleft.gif\" alt=\"{$title}\" width=\"16\" height=\"16\" border=\"0\" align=\"absmiddle\"></a></span>\n";
				showXHTML_td_E('');
				showXHTML_td_B('nowrap');
					$title = $MSG['move_right'][$sysSession->lang];
					echo "<span id=\"MRight\" style=\"visibility: hidden;\"><a href=\"javascript:;\" onclick=\"ScrollMenu(2)\" title=\"{$title}\"><img src=\"{$Theme}/mright.gif\" alt=\"{$title}\" width=\"16\" height=\"16\" border=\"0\" align=\"absmiddle\"></a></span>\n";
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		echo '</div>' . "\n";

		// 子選單的左右移動按鈕
		echo '<div class="SScroll" id="SScroll">' . "\n";
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('nowrap');
					$title = $MSG['move_left'][$sysSession->lang];
					echo "<span id=\"SLeft\" style=\"visibility: hidden;\"><a href=\"javascript:;\" onclick=\"ScrollMenu(3)\" title=\"{$title}\"><img src=\"{$Theme}/sleft.gif\" alt=\"{$title}\" width=\"16\" height=\"16\" border=\"0\" align=\"absmiddle\"></a></span>\n";
				showXHTML_td_E('');
				showXHTML_td_B('nowrap');
					$title = $MSG['move_right'][$sysSession->lang];
					echo "<span id=\"SRight\" style=\"visibility: hidden;\"><a href=\"javascript:;\" onclick=\"ScrollMenu(4)\" title=\"{$title}\"><img src=\"{$Theme}/sright.gif\" alt=\"{$title}\" width=\"16\" height=\"16\" border=\"0\" align=\"absmiddle\"></a></span>\n";
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		echo '</div>' . "\n";

		// 線上人數
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" class="sysOnline" id="SysOnline"');
			showXHTML_tr_B('');
				showXHTML_td_B('nowrap class="sysOnlineFont"');
					echo '<a href="javascript:;" onclick="showUserList(); return false;" class="sysOnlineFont">',
					     $MSG['num_school'][$sysSession->lang], '<span id="spanSchool">000</span>', $MSG['people'][$sysSession->lang],
					     ' | ',
					     $MSG['num_course'][$sysSession->lang], '<span id="spanCourse">000</span>', $MSG['people'][$sysSession->lang],
					     '</a>', // 說明 登出
					     ' | ';
					// 假如是參觀者，則顯示登入字樣
					if ($sysSession->username == 'guest') {
						$logout = $MSG['login'][$sysSession->lang];
						echo "<a href=\"javascript:;\" class=\"sysHelpFont\" onclick=\"login();\">{$logout}</a>";
					} else {
						$logout = $MSG['logout'][$sysSession->lang];
						echo "<a href=\"javascript:;\" class=\"sysHelpFont\" onclick=\"parent.logout();\">{$logout}</a>";
					}
				showXHTML_td_E();
			showXHTML_tr_E('');
		showXHTML_table_E('');

		// 切換環境
		if (!isMobileBrowser()) {
			$split = '<pre class="sysHelpSplit">&nbsp;</pre>';
			showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" class="sysHelp" id="SysHelp"');
				showXHTML_tr_B('');
					// 管理者
					if (aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id)) {
						echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap><div><a href=\"javascript:go_evn(1);\" class=\"sysEnvFont\" title=\"{$MSG['manager'][$sysSession->lang]}\">{$MSG['manager_short'][$sysSession->lang]}</a></div></td>\n";
					}

					// 導師
					if (aclCheckRole($sysSession->username, $sysRoles['director'] | $sysRoles['assistant'])) {
						echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap><div><a href=\"javascript:go_evn(2);\" class=\"sysEnvFont\" title=\"{$MSG['director'][$sysSession->lang]}\">{$MSG['director_short'][$sysSession->lang]}</a></div></td>\n";
					}

					// 老師
					// 對不具教師身份的學員，隱藏切換到辦公室的連結 (Begin)
					$display = 'none';
					// 辨別此人有沒有這一門課的老師權限 (Begin)
					$csid = $sysSession->course_id;
					if (($csid != 10000000) || ($sysSession->username != 'guest')) {
						if (empty($csid) || ($csid <= 10000000) || ($csid >= 100000000)) {
							$csid = 10000000;
						}
						if (aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $csid)) $display = 'block';
					}
					// 辨別此人有沒有這一門課的老師權限 (End)
					echo "\t\t<td nowrap id=\"admOffice1\" style=\"display: {$display};\">{$split}</td>\t\t<td nowrap id=\"admOffice\" style=\"display: {$display};\"><div><a href=\"javascript:;\" class=\"sysEnvFont\" onclick=\"parent.chgCourse(parent.csid, 1, 2);\" title=\"{$MSG['office'][$sysSession->lang]}\">{$MSG['office_short'][$sysSession->lang]}</a></div></td>\n";
					// 對不具教師身份的學員，隱藏切換到辦公室的連結 (End)
				showXHTML_tr_E('');
			showXHTML_table_E('');
		}

		// 系統時間
		showXHTML_table_B("border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"sysTime\" id=\"SysTime\" title=\"{$MSG['system_time'][$sysSession->lang]}\"");
			showXHTML_tr_B('');
				showXHTML_td('nowrap class="sysTimeFont" id="tdSysTime"', '&nbsp;');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	echo '</div>';
	showXHTML_body_E('');
?>

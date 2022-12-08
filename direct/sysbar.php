<?php
	/**
	 * 導師環境的 sysbar
	 *
	 * @since   2003/09/30
	 * @author  ShenTing Lin
	 * @version $Id: sysbar.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sysbar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '1300400100';
	$sysSession->restore();
	if (!aclVerifyPermission(1300400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 切換班級的部分 (Begin)

	/**
	 * 將陣列值只取目前語系的標題出來
	 *
	 * @param   serialize_string    $value
	 */
	function fetchLocaleCaption(&$value)
	{
	    global $sysSession;
	
	    $lang = getCaption($value);
	    $value = $lang[$sysSession->lang];
	}

	$class = dbGetAssoc('WM_class_member as M,WM_class_main as C',
	                    'M.class_id,C.caption',
	                    "M.`username`='{$sysSession->username}' AND (M.`role` & " . ($sysRoles['director']|$sysRoles['assistant']) . ') AND M.class_id=C.class_id');
    array_walk($class, 'fetchLocaleCaption');

	if (count($class) <= 0) {
		$js = <<< BOF

	window.onload = function () {
		parent.location.replace("/learn/");
	};

	// 判斷是否按下F5
	window.document.onkeydown = function (evnt) {
		if (typeof evnt == "object") event = evnt;
		parent.reloadKey = (event.keyCode == 116) ? true : false;
	};

BOF;
		showXHTML_script('inline', $js);
		die();
	} else {
		// 設定進入的課程編號
		$caid = intval($sysSession->class_id);
		if (($caid <= 1000000) || ($caid >= 10000000)) {
			reset($class);
			$caid = key($class);
		}
	}
	// 切換班級的部分 (End)

	// 要切換到哪個選單項目 (Begin)
	$label = $sysSession->goto_label;
	$sysSession->goto_label = '';    // 用過就清除
	$sysSession->restore();
	// 要切換到哪個選單項目 (End)


	$Theme = "/theme/{$sysSession->theme}/direct";
	$lang = strtolower($sysSession->lang);
	$isMobile = isMobileBrowser() ? 'true' : 'false';
	
$js = <<< BOF
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
	var MSG_IN_CHAT_ROOM      = "{$MSG['msg_in_chat'][$sysSession->lang]}";
	
	var fmDefault = "main";
	var sysGotoLabel = "{$label}" == '' ? "SYS_06_01_001" : "{$label}";

	function go_evn(){
		if (typeof(parent.main.notSave) == 'boolean' && parent.main.notSave) {
			if (!confirm(parent.main.MSG_EXIT)) return;
			else parent.main.notSave = false;
		}
		parent.window.onbeforeunload = null;
		parent.location.href = '/learn_relogin.php';

	}

	function go_academic(){
		if (typeof(parent.main.notSave) == 'boolean' && parent.main.notSave) {
			if (!confirm(parent.main.MSG_EXIT)) return;
			else parent.main.notSave = false;
		}
		parent.window.onbeforeunload = null;
		parent.location.replace("/academic/index.php");
	}

	window.onload = function () {
		showSysTime('PM 00:00:00');
		// loadSysbar("goto_class.php", "");
		goClass("{$caid}");
		if ((typeof(parent.session) == "object") && (typeof(parent.session.touchSession) == "function")) {
			parent.session.touchSession();
		}
	};

	// 判斷是否按下F5
	window.document.onkeydown = function (evnt) {
		if (typeof evnt == "object") event = evnt;
		parent.reloadKey = (event.keyCode == 116) ? true : false;
	};
BOF;

	showXHTML_head_B($MSG['teacher_sysbar'][$sysSession->lang]);
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
				showXHTML_td_B('valign="top"');
					// 目前使用者正在那個環境的string
					$env_str = $MSG['msg_direct'][$sysSession->lang];

					printf("<div class=\"sysUsername\">{$MSG['regards'][$sysSession->lang]}</div>", $sysSession->username,$env_str);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssBg02"');
				showXHTML_td_B('width="200" height="24" align="center" valign="middle"');
				// 切換班級的部分 (Begin)
					showXHTML_input('select', 'selcourse', $class, $caid, 'id="selcourse" class="cssInput" style="width:188px;" onchange="goClass(this.value)" onclick="event.cancelBubble = true;"');
				// 切換班級的部分 (End)
				showXHTML_td_E('');
				showXHTML_td('', '&nbsp;');
			showXHTML_tr_E('');
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
					     // $MSG['num_online'][$sysSession->lang], '<span id="spanOnline">000</span>', $MSG['people'][$sysSession->lang],
					     // ' | ',
					     $MSG['num_course'][$sysSession->lang], '<span id="spanCourse">000</span>', $MSG['people'][$sysSession->lang],
					     '</a>', // 說明 登出
					     ' | ';
					// $help = $MSG['help'][$sysSession->lang];
					// echo "<a href=\"javascript:;\" class=\"sysHelpFont\" onclick=\"alert('come soon!')\">{$help}</a>";
					// echo ' | ';
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
		$split = '<pre class="sysHelpSplit">&nbsp;</pre>';
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" class="sysHelp" id="SysHelp"');
			showXHTML_tr_B('class="cssBg01"');
				// 管理者
				if (aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id)) {
					echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap><div><a href=\"javascript:go_academic();\" class=\"sysEnvFont\" title=\"{$MSG['manager'][$sysSession->lang]}\">{$MSG['manager_short'][$sysSession->lang]}</a></div></td>\n";
				}

				// 學生環境
				$classroom = $MSG['classroom'][$sysSession->lang];
				echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap><div><a href=\"javascript:go_evn();\" class=\"sysEnvFont\" title=\"{$MSG['classroom'][$sysSession->lang]}\">{$MSG['classroom_short'][$sysSession->lang]}</a></div></td>\n";
			showXHTML_tr_E('');
		showXHTML_table_E('');

		// 系統時間
		showXHTML_table_B("border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"sysTime\" id=\"SysTime\" title=\"{$MSG['system_time'][$sysSession->lang]}\"");
			showXHTML_tr_B('');
				showXHTML_td('nowrap class="sysTimeFont" id="tdSysTime"', '&nbsp;');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	echo '</div>';
	showXHTML_body_E('');
?>

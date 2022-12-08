<?php
	/**
	 * 管理處環境的 sysbar
	 * @todo
	 *     1. 自動執行第一項主選單
	 *     2. 點選主選單後，自動執行第一項子選單
	 *     3. 顯示學員的帳號
	 *     4. 顯示系統時間
	 *     5. 登出
	 *     6. 顯示線上人數
	 *     7. 切換課程
	 *     8. 切換教室跟辦公室
	 * $Id: sysbar.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sysbar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '1300300100';
	$sysSession->restore();
	if (!aclVerifyPermission(1300300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$Theme = "/theme/{$sysSession->theme}/academic";
	$lang = strtolower($sysSession->lang);

	// 要切換到哪個選單項目 (Begin)
	$label = $sysSession->goto_label;
	$sysSession->goto_label = '';    // 用過就清除
	$sysSession->restore();
	// 要切換到哪個選單項目 (End)
	
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
	var sysGotoLabel = "{$label}" == '' ? "SYS_01_01_001" : "{$label}";
	function go_evn(val) {
		if (typeof(parent.main.notSave) == 'boolean' && parent.main.notSave) {
			if (!confirm(parent.main.MSG_EXIT)) return;
			else parent.main.notSave = false;
		}

		parent.window.onbeforeunload = null;
		switch (val){
			case 1:
				parent.location.replace("/learn/index.php");
				break;
			case 2:
				parent.location.replace("/direct/index.php");
				break;
		}
	}

	window.onload = function () {
		showSysTime('PM 00:00:00');
		initSysbar("goto_school.php");
		if ((typeof(parent.session) == "object") && (typeof(parent.session.touchSession) == "function")) {
			parent.session.touchSession();
		}
	};

	window.onunload = function()
	{
		if (navigator.userAgent.search(/ MSIE [789]\./) > -1)
	    	document.cookie = 'idx=; path=/{$sysSession->school_id}_door; expires=Thu, 01-Jan-70 00:00:01 GMT';
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
					$env_str = $MSG['msg_env'][$sysSession->lang];

					printf("<div class=\"sysUsername\">{$MSG['regards'][$sysSession->lang]}</div>", $sysSession->username,$env_str);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssBg02"');
				showXHTML_td('width="200" height="24"', '&nbsp;');
				showXHTML_td('height="24"', '&nbsp;');
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
					echo '<a href="javascript:;" onclick="showUserList(); return false;" class="sysOnlineFont">';
					echo $MSG['num_school'][$sysSession->lang] . '<span id="spanSchool">000</span>' . $MSG['people'][$sysSession->lang];
					echo ' | ';
					// echo $MSG['num_online'][$sysSession->lang] . '<span id="spanOnline">000</span>' . $MSG['people'][$sysSession->lang];
					// echo ' | ';
					echo $MSG['num_course'][$sysSession->lang] . '<span id="spanCourse">000</span>' . $MSG['people'][$sysSession->lang];
					echo '</a>';
					// 說明 登出
					echo ' | ';
					// $help = $MSG['help'][$sysSession->lang];
					// echo "<a href=\"javascript:;\" class=\"sysHelpFont\" onclick=\"alert('come soon!')\">{$help}</a>";
					// echo ' | ';
					// 假如是參觀者，則顯示登入字樣
					if ($sysSession->username == 'guest') {
						$logout = $MSG['login'][$sysSession->lang];
						echo "<a href=\"javascript:;\" class=\"sysHelpFont\" onclick=\"login();\">{$logout}</a>";
					} else {
                        if (!empty($_COOKIE["persist_idx"]) && ($sysSession->username != 'guest')){
                            $logout = $MSG['gohome'][$sysSession->lang];
                            echo "<a href=\"/mooc/index.php\" class=\"sysHelpFont\" target=\"_top\">{$logout}</a>";
                        }else{
                            $logout = $MSG['logout'][$sysSession->lang];
                            echo "<a href=\"javascript:;\" class=\"sysHelpFont\" onclick=\"parent.logout();\">{$logout}</a>";
                        }
					}
				showXHTML_td_E();
			showXHTML_tr_E('');
		showXHTML_table_E('');

		// 切換環境
		$split = '<pre class="sysHelpSplit">&nbsp;</pre>';
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" class="sysHelp" id="SysHelp"');
			showXHTML_tr_B('');
				// 導師
				$cd = aclCheckRole($sysSession->username, $sysRoles['director'] | $sysRoles['assistant']);
				if ($cd) {
					echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap><div><a href=\"javascript:go_evn(2);\" class=\"sysEnvFont\" title=\"{$MSG['director'][$sysSession->lang]}\">{$MSG['director_short'][$sysSession->lang]}</a></div></td>\n";
				}

				// 學生環境
				echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap id=\"stud_evn\"><div><a href=\"javascript:go_evn(1);\" class=\"sysEnvFont\" title=\"{$MSG['classroom'][$sysSession->lang]}\">{$MSG['classroom_short'][$sysSession->lang]}</a></div></td>\n";
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

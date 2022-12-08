<?php
	/**
	 * 辦公室環境的 sysbar
	 * @todo
	 *     1. 自動執行第一項主選單
	 *     2. 點選主選單後，自動執行第一項子選單
	 *     3. 顯示學員的帳號
	 *     4. 顯示系統時間
	 *     5. 登出
	 *     6. 顯示線上人數
	 *     7. 切換課程
	 *     8. 切換教室跟辦公室
	 * $Id: sysbar.php,v 1.1 2010/02/24 02:40:26 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	$sysSession->env = 'teach';
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/sysbar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php'); // 計算教師使用容量

	if (!aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id)) exit;

    function format_size($size) {
        $mod = 1024;
        $units = explode(' ','KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

	$Theme = "/theme/{$sysSession->theme}/teach";
	$lang = strtolower($sysSession->lang);

	// 要切換到哪個選單項目 (Begin)
	$label = $sysSession->goto_label;
	$sysSession->goto_label = '';    // 用過就清除
	$sysSession->restore();
	// 要切換到哪個選單項目 (End)
	// 是否有啟用LCMS
    $lcmsEnable = sysLcmsEnable ? 'true' : 'false';

	$isMobile = isMobileBrowser() ? 'true' : 'false';

$js = <<< BOF
	var sysGotoLabel = "{$label}";
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
	
	var fmDefault = "c_main";

	function go_evn(n){
		if (typeof(parent.c_main.notSave) == 'boolean' && parent.c_main.notSave) {
			if (!confirm(parent.c_main.MSG_EXIT)) return;
			else parent.c_main.notSave = false;
		}

		parent.window.onbeforeunload = null;
		switch (n) {
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
	};

	window.onunload = function()
	{
		if (navigator.userAgent.search(/ MSIE [789]\./) > -1)
	    	document.cookie = 'idx=; path=/{$sysSession->school_id}_{$sysSession->course_id}; expires=Thu, 01-Jan-70 00:00:01 GMT';
	};

	// 判斷是否按下F5
	window.document.onkeydown = function (evnt) {
		if (typeof evnt == "object") event = evnt;
		parent.reloadKey = (event.keyCode == 116) ? true : false;
	};

    /* 代登入 LCMS */
    var cid =           '{$sysSession->course_id}';
    var lcmsEnable =    {$lcmsEnable};
    $(function() {
        if (!lcmsEnable || cid === null || cid === undefined) {
            return;
        }
        $("#bgFrame").attr('src', '/teach/course/lcms.php?action=login&nodir=1&cid=' + cid);
        
        var w = $(window).width()-850;
        $(".CNameDiv .CNameContent > div").css({'max-width':w});
    });
    
    $(window).resize(function() {
        var w = $(window).width()-850;
        $(".CNameDiv .CNameContent > div").css({'max-width':w});
    });

    /* 設定的下拉選單 */
    function dropDown(obj) {
        $(obj).parent().find(".drop-item").slideToggle();
    }
    function hideSettings() {
         $("#settings").find(".drop-item").hide();
    }
    $("#settings").on({
        mouseenter: function() {
            
        },
        mouseleave: function() {
            $(".drop-item", this).hide();
        }
    });
    
    /* 變更標題 */
    function updateCourseName(isUpdate) {
        var courseName = $("#selcourse :selected").text();
        $("#course-name").attr("title", courseName).find(".show-name").text(courseName);
        
    
        // 取課程容量資訊
        // 需要經過計算空間後，來決定上傳按鈕是否開啟，故開啟同步
        $.ajax({
            url: '/mooc/controllers/course_ajax.php',
            dataType: 'json',
            type: "POST",
            async: false,
            data: 'action=getCourseQuota&isUpdate=' + isUpdate,
            success: function(response) {
                $(".quota").html(response.data.sysbar_quota_str);
    
                /* 判斷有無超量，如果有增加class overload */

                if (parseInt(response.data.used) > parseInt(response.data.limit)) {
                    $(".quota").addClass('overload');
                } else {
                    $(".quota").removeClass('overload');
                }
            }
        });    
    }
BOF;
$jsvar =  <<< BOF
        /* 修正教師環境新套版的位移 1. MContainer.top  2.MSclLeft & SSclLeft 3.SysOnline shift */
        var replace_position = [118, 510, 0];
BOF;
	showXHTML_head_B($MSG['teacher_sysbar'][$sysSession->lang]);
	showXHTML_css('include', "{$Theme}/sysbar.css");
	showXHTML_script('inline', $jsvar);
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/sysbar.js');
	showXHTML_script('include', '/lib/jquery/jquery.min.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0"');
	echo '<div style="height: 140px; overflow: hidden;">';
		// 背景的部分
		showXHTML_table_B('width="796" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;" id="SysLayout"');
            showXHTML_tr_B('style="overflow: hidden; padding:0;" class=""');
                // 大頭貼
                $enc = @mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $sysSession->username, 'ecb');
                $ids = base64_encode($enc);
                // 登出
                // 假如是參觀者，則顯示登入字樣
                if ($sysSession->username == 'guest') {
                    $logout = $MSG['login'][$sysSession->lang];
                    $logoutEvent = 'login();';
                } else {
                    $logout = $MSG['logout'][$sysSession->lang];
                    $logoutEvent =  'parent.logout();';
                }
                // 個人資訊區
                echo '<td colspan="2" height="38" style="text-align: right; padding:0; border-collapse:collapse;"><div class="cssBg01" style="height: 38px;"></div></td>';
            showXHTML_tr_E('');
            showXHTML_tr_B('class="" style="background-image: none;"');
                echo '<td colspan="2" align="right" valign="middle" height="33">
                    <div class="more-course cssBg02">';
                        // 切換課程的部分 (Begin)
                        // $selary[10000000] = iconv('Big5', 'UTF-8', '我的課程');
                        $RS = dbGetCourses('C.course_id, C.caption',
                                           $sysSession->username,
                                           $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
                        if ($RS) {
                            while (!$RS->EOF) {
                                if ($csid = intval($RS->fields['course_id'])){
                                    $lang = getCaption($RS->fields['caption']);
                                    $selary[$csid] = empty($lang[$sysSession->lang]) ? '[no title]' : $lang[$sysSession->lang];
                                }
                                $RS->MoveNext();
                            }
                        }
                        showXHTML_input('select', 'selcourse', $selary, $sysSession->course_id, 'id="selcourse" class="cssInput" style="width:188px;font-size:1em;" onchange="parent.chgCourse(this.value, 2, 2); updateCourseName();" onclick="event.cancelBubble = true;"');
                        // 切換課程的部分 (End)
                    echo '</div></td>';
            showXHTML_tr_E('');
            showXHTML_tr_B('class="cssBg03"');
                echo '<td colspan="2"><div style="height: 5px;"></div></td>';
            showXHTML_tr_E('');
            showXHTML_tr_B('class="cssBg04"');
                echo '<td colspan="2"><div style="height: 39px;"></div></td>';
            showXHTML_tr_E('');
            showXHTML_tr_B('class="cssBg05"');
                echo '<td colspan="2" align="right" valign="middle" height="25">
                    <div class="quota" id="quota">';
                    // 課程剩餘容量
                    getQuota($sysSession->course_id, $real_used, $quota_limit);
                    echo str_replace('%quota%', format_size($real_used) . '/' . format_size($quota_limit), $MSG['msg_course_quota'][$sysSession->lang]);
                    echo '</div>
                </td>';
            showXHTML_tr_E('');
            /* logo 及 課程名稱另外秀
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
					$env_str = $MSG['msg_teach'][$sysSession->lang];

					printf("<div class=\"sysUsername\">{$MSG['regards'][$sysSession->lang]}</div>", $sysSession->username,$env_str);
				showXHTML_td_E('');
			showXHTML_tr_E('');
             * 
             */
            /*
			showXHTML_tr_B('class="cssBg02"');
				showXHTML_td_B('width="200" height="24" align="center" valign="middle"');
				// 切換課程的部分 (Begin)
					// $selary[10000000] = iconv('Big5', 'UTF-8', '我的課程');
					$RS = dbGetCourses('C.course_id, C.caption',
									   $sysSession->username,
									   $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
					if ($RS) {
						while (!$RS->EOF) {
							if ($csid = intval($RS->fields['course_id'])){
								$lang = getCaption($RS->fields['caption']);
								$selary[$csid] = empty($lang[$sysSession->lang]) ? '[no title]' : $lang[$sysSession->lang];
							}
							$RS->MoveNext();
						}
					}
					showXHTML_input('select', 'selcourse', $selary, $sysSession->course_id, 'id="selcourse" class="cssInput" style="width:188px;" onchange="parent.chgCourse(this.value, 2, 2)" onclick="event.cancelBubble = true;"');
				// 切換課程的部分 (End)
				showXHTML_td_E('');
				showXHTML_td('', '&nbsp;');
			showXHTML_tr_E('');
             * 
             */
		showXHTML_table_E('');
        // 個人資訊(利用原 sysOnline 的 js 調整位置)
        showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" class="sysOnline" id="SysOnline"');
			showXHTML_tr_B('');
				showXHTML_td_B('nowrap ');
                    echo '<div class="personal-info">
                        <div class="pic"><img src="/learn/personal/showpic.php?a=' . $ids . '&' . uniqid('') . '" type="image/jpeg" borer="0" height="38" align="absmiddle" loop="0"></div>
                        <div class="user">' . $sysSession->realname . '</div>
                        <div><pre class="SMenuItemSplit SMenuItemSplit-gray SMenuItemSplit-lg"></pre></div>
                        <div class="class-link" onclick="parent.chgCourse(parent.csid, 2, 1);">
                            <div class="icon-simulation-class"></div><div>'.$MSG['msg_into_class'][$sysSession->lang].'</div>
                        </div>
                        <div><pre class="SMenuItemSplit SMenuItemSplit-gray SMenuItemSplit-lg"></pre></div>
                        <div class="class-link" onclick="top.location.replace(\'/mooc/index.php\');">
                            <div class="icon-home"></div>
                        </div>
                        <div id="settings" class="settings">
                            <div class="drop-btn" onclick="dropDown(this);">
                                <div class="icon-gear" style="margin: auto 15px;"></div>
                            </div>
                            <ul class="drop-item">
                                <li onclick="parent.chgCourse(parent.csid, 2, 2, \'SYS_06_01_003\'); hideSettings();" title="'.$MSG['personal_settings'][$sysSession->lang].'"><a><div class="icon-set-up"></div></a></li>
                                <li onclick="'.$logoutEvent.' hideSettings();" title="'.$logout.'"><a><div class="icon-sign-out"></div></a></li>
                            </ul>
                        </div>
                    </div>';
                showXHTML_td_E();
			showXHTML_tr_E('');
		showXHTML_table_E('');
        // 課程名稱
        echo '<div class="CNameDiv">
            <div></div><!--
            --><div id="course-name" class="CNameContent" title="' . $sysSession->course_name . '">' .
                '<div class="show-name">' . $sysSession->course_name . '</div>' .
            '</div><!--
            --><div class="triangle"><div class="shape"></div></div><!--
            --><div style="min-width: 400px; background-color: transparent;"></div>
        </div>';

        // LOGO
        echo '<div class="logo"></div>';

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
        /*
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
						$logout = $MSG['logout'][$sysSession->lang];
						echo "<a href=\"javascript:;\" class=\"sysHelpFont\" onclick=\"parent.logout();\">{$logout}</a>";
					}
				showXHTML_td_E();
			showXHTML_tr_E('');
		showXHTML_table_E('');
         * 
         */

		// 切換環境
        /*
		$split = '<pre class="sysHelpSplit" style=\"display: inline;\">&nbsp;</pre>';
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" class="sysHelp" id="SysHelp"');
			showXHTML_tr_B('class="cssBg01"');
				// 管理者
				if (aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id)) {
					echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap><div><a href=\"javascript:go_evn(1);\" class=\"sysEnvFont\" title=\"{$MSG['manager'][$sysSession->lang]}\">{$MSG['manager_short'][$sysSession->lang]}</a></div></td>\n";
				}

				// 導師
				if (aclCheckRole($sysSession->username, $sysRoles['director'] | $sysRoles['assistant'])) {
					echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap><div><a href=\"javascript:go_evn(2);\" class=\"sysEnvFont\" title=\"{$MSG['director'][$sysSession->lang]}\">{$MSG['director_short'][$sysSession->lang]}</a></div></td>\n";
				}

				// 學生環境
				echo "\t\t<td nowrap>{$split}</td>\t\t<td nowrap><div><a href=\"javascript:;\" class=\"sysHelpFont\" onclick=\"parent.chgCourse(parent.csid, 2, 1);\" title=\"{$MSG['classroom'][$sysSession->lang]}\">{$MSG['classroom_short'][$sysSession->lang]}</a></div></td>\n";
			showXHTML_tr_E('');
		showXHTML_table_E('');
         * 
         */

		// 系統時間
        /*
		showXHTML_table_B("border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"sysTime\" id=\"SysTime\" title=\"{$MSG['system_time'][$sysSession->lang]}\"");
			showXHTML_tr_B('');
				showXHTML_td('nowrap class="sysTimeFont" id="tdSysTime"', '&nbsp;');
			showXHTML_tr_E('');
		showXHTML_table_E('');
         * 
         */

        // LCMS 代登入 iframe
        echo '<iframe id="bgFrame" name="bgFrame" style="display: none;"></iframe>';
	echo '</div>';
	showXHTML_body_E('');
?>

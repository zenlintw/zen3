<?php
	/**
	 * 討論版 - 編輯
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      ShenTin Lin <lst@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: lib_edit.php,v 1.2 2009-08-03 02:37:04 edi Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-05-18
	 *
	 * Requirements: PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 * Usage :
	 *
	 **/

	if (!defined('EDIT_MODE')) die('Access Deny!');

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/editor.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lib/common.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin
// }}} 常數定義 end

// {{{ 變數宣告 begin
// }}} 變數宣告 end

// {{{ 函數宣告 begin
// }}} 函數宣告 end

// {{{ 主程式 begin
	// 判斷權限
	$_POST['mnode'] = preg_replace('/[^\d]+/', '', $_POST['mnode']);
	if (in_array(EDIT_MODE, array('edit', 'q_write', 'q_edit')))
	{
		$poster = '';
		if (EDIT_MODE == 'edit')
		{
			$poster = dbGetOne('WM_bbs_posts', 'poster', 'board_id=' . $sysSession->board_id . ' and node=' . $_POST['mnode']);
		}
		if (!ChkRight($sysSession->board_id) && ($poster != $sysSession->username || $poster == 'guest'))
		{
			header('Location:' . $referurl);
			wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], '不具刊登權限!');
			exit();
		}
	}
    
    // 是否為常見問題版
    if (dbGetNewsBoard($result, 'faq')) {
        $isFaq = ($result['board_id'] == $sysSession->board_id) ? true : false;
    }

	// 檢查刊登時間
	$js_close_time = '0'; // 討論版關閉時間
	$js_time_warn  = 600; // seconds ; 10 分鐘前警告
	$js_time_left  = $js_time_warn + 1; // 可刊登之剩餘時間
	$js_interval   = 1; // 計時器迴圈間隔 (1 sec)

	$freeQuota = getRemainQuota($sysSession->board_ownerid);
	$type      = getQuotaType($sysSession->board_ownerid);
	$msgQuota  = str_replace(array('%TYPE%', '%OWNER%'),
	                         array($MSG[$type][$sysSession->lang], $MSG[$type . '_owner'][$sysSession->lang]),
	                         $MSG['quota_full'][$sysSession->lang]);


	$js = 'var IsUseAudio = false;';
	$RS = dbGetStSr('WM_bbs_boards', 'open_time,close_time,share_time,switch,with_attach,vpost', "board_id={$sysSession->board_id}", ADODB_FETCH_ASSOC);
	if ($RS)
	{
		if (in_array(EDIT_MODE, array('edit', 'reply', 'write')))
		{
			$cD = $sysConn->UnixTimeStamp($RS['close_time']);
			if (!empty($cD))
			{
				if ($cD <= time())
				{
					$js = <<< BOF
	window.onload = function ()
	{
		alert("{$MSG['time_out'][$sysSession->lang]}");
		location.replace("{$referurl}");
	};
BOF;
					showXHTML_script('inline', $js);
					wmSysLog($sysSession->cur_func, $sysSession->course_id, $sysSession->board_id, 2, 'auto', $_SERVER['PHP_SELF'], '時間已到' . $cD);
					exit();
				}

				// 產生供 javascript  用的字串
				$ct            = getdate($cD);
				$js_time_left  = $cD - time();
				$js_close_time = sprintf('new Date(%d,%d,%d,%d,%d);', $ct['year'], $ct['mon'] - 1, $ct['mday'], $ct['hours'], $ct['minutes']);
			}
		}

		if ($RS['vpost'] & 1 && Voice_Board == 'Y')
		{
			$js = 'var IsUseAudio = true;';
		}
	}

	$onload_js = '';
	if (in_array(EDIT_MODE, array('edit', 'reply', 'write')))
	{
		$IsNews = $sysSession->news_board ? 1 : 0;
		if ($IsNews)
		{
			include_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
			// 請寫在 HTML 的 Head 區 (Begin)
			$onload_js .= 'Calendar_setup("open_time", "%Y-%m-%d %H:%M", "open_time", true);' . "\n" .
				'Calendar_setup("close_time", "%Y-%m-%d %H:%M", "close_time", true);' . "\n";

			$js .= <<< BOF

	// 秀日曆的函數(checkbox)
	function showDateInput(objName, state)
	{
		var obj = document.getElementById(objName);
		if (obj != null)
			obj.style.display = state ? "" : "none";
	}

	// 秀日曆的函數
	function Calendar_setup(ifd, fmt, btn, shtime)
	{
		Calendar.setup({
			inputField  : ifd,
			ifFormat    : fmt,
			showsTime   : shtime,
			time24      : true,
			button      : btn,
			singleClick : true,
			weekNumbers : false,
			step        : 1
		});
	}

BOF;
		}
	}
	elseif  (in_array(EDIT_MODE, array('q_edit', 'q_write')))
	{
		$IsNews = '0';
	}
	if ($IsNews == '') $IsNews = '0';

	$onload_js .= "checkTime();\n";
	if ($_POST['writeError'])
		$onload_js .= "\nalert('{$_POST['writeError']}');\n";

	$js .= <<< JSS
	var files            = 1;
	var col              = "cssTrOdd";

	var MsgPlsFill       = "{$MSG['please_fill'][$sysSession->lang]}";
	var MsgField         = "{$MSG['field'][$sysSession->lang]}";
	var MsgPlsDontExceed = "{$MSG['pls_dont_exceed'][$sysSession->lang]}";
	var MsgCharacters    = "{$MSG['characters'][$sysSession->lang]}";
	var MsgSubject       = "{$MSG['subj'][$sysSession->lang]}";
	var MsgContent       = "{$MSG['content'][$sysSession->lang]}";

	var MsgTimeLeft      = "{$MSG['time_left'][$sysSession->lang]}";
	var MsgYourTime      = "{$MSG['your_time'][$sysSession->lang]}";
	var MsgTimeOut       = "{$MSG['time_out'][$sysSession->lang]}";
	var MsgNewsTime      = "{$MSG['msg_newstime'][$sysSession->lang]}";
	var MsgDateError     = "{$MSG['msg_date_error'][$sysSession->lang]}";
	var MsgQuota         = "{$msgQuota}";
	var MSG_MIN_FILES    = "{$MSG['msg_file_min'][$sysSession->lang]}";
	var MSG_MAX_FILES    = "{$MSG['msg_file_max'][$sysSession->lang]}";

	var CloseTime        = {$js_close_time};
	var TimeToWarn       = {$js_time_warn};
	var TimeWarned       = false;
	var timerID          = 0;
	var timerInterval    = {$js_interval};
	var TimeLeft         = {$js_time_left};

	var IsNews           = {$IsNews};
	var freeQuota        = {$freeQuota};

	function focusSubject()
	{
		document.getElementById("subject").focus();
	}

	window.onload = function()
	{
		{$onload_js}
		if (freeQuota <= 0)
			alert(MsgQuota);

		if (typeof Touch !== "undefined" && navigator.userAgent.search('Chrome') < 0) {
			document.getElementById('btnMoreAtt').style.display = "none";
			document.getElementById('btnCutAtt').style.display = "none";

			var node = document.getElementById('upload_box');
			while (node.nextSibling !== null) {
				if (node.nodeType === 1) {
					if (node.id !== 'upload_base' && navigator.userAgent.search('Chrome') < 0) {
						node.style.display = "none";
					} else {
						node.className = (node.className == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
					}
				}
				node = node.nextSibling;
			}
		}
	};

JSS;

	// 語音白板 (Begin)
	$edit_mode = EDIT_MODE;
	$js .= <<< BOF
	var WM_window = null;
	function loadwb(urlval)
	{
		var edit_mode = "{$edit_mode}";
		var paraObj = {
			WM_BoardID     : "{$sysSession->board_id}",
			WM_CourseID    : "{$sysSession->course_id}",
			openerdocument : document,
			preloaddata    : '',
			blocked        : false,
			isEdit         : 0
		};
		if (edit_mode == 'reply') urlval = "{$_POST['awppathre']}";	// reply時, 白板以原先檔案為base
		if (typeof(urlval) != 'undefined' && urlval != '')
		{
			paraObj.WB_File = escape(urlval);
			paraObj.preloaddata = "http://" + window.location.host + "/lib/anicamWB/readWBFile.php?filepath=" + paraObj.WB_File;
			if (edit_mode == 'edit' || edit_mode == 'q_edit')
			{
				paraObj.isEdit = 1;
				paraObj.EditFile = paraObj.WB_File;
			}
		}

		WB_Window = window.showModalDialog("whiteboard.html", paraObj, "status:no; dialogWidth:635px; dialogHeight:545px; edge:raised; center:yes; unadorned:no; help:no; scroll:no; status:no; resizable:no;");
	}
BOF;
	// 語音白板 (End)

	// 開始呈現 HTML
	showXHTML_head_B($title);
	if ($IsNews)
	{
		$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
		$calendar->load_files();
	}
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('inline' , $js);
	showXHTML_script('include', 'write.js?'.time());
	showXHTML_script('include', '/lib/anicamWB/WMRecorder.js');
	$xajax_save_temp->printJavascript('/lib/xajax/');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();

    showXHTML_body_B();

    $ary = array();
    $ary[] = array($tabs_name, 'tabs1');

    echo '<div align="center">';
    showXHTML_tabFrame_B($ary, 1, 'post1', 'tb1', 'method="post" action="' . $action . '" enctype="multipart/form-data" onsubmit="return chkform();" style="display: inline;"');
        showXHTML_input('hidden', 'ticket', $ticket);
    showXHTML_input('hidden', 'postFrom', 'forum');
        if ($RS['vpost'] & 1 && Voice_Board == 'Y') showXHTML_input('hidden', 'mp3path'); // for sound recoder
        if ($RS['vpost'] & 2 && White_Board == 'Y') showXHTML_input('hidden', 'wbpath');  // for white board

        showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="mainTable"');
            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td('width="130" align="right"', $MSG['bname'][$sysSession->lang]);
                showXHTML_td('width="630" align="left" colspan=2', $sysSession->board_name . $bn_extra);
            showXHTML_tr_E();

            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td('align="right" nowrap="nowrap"', $MSG['posters'][$sysSession->lang]);
                showXHTML_td('', "$sysSession->username ($sysSession->realname )");
                showXHTML_td('', '<span id="warnTimer"></span>');
            showXHTML_tr_E();

            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td('align="right" nowrap="nowrap"', $MSG['subjs'][$sysSession->lang]);
                showXHTML_td_B('nowrap="nowrap"');
                    showXHTML_input('text', 'subject', $subject, '', 'class="cssInput" size="87" maxlength="255"');
                showXHTML_td_E();
                showXHTML_td('align="left" nowrap="nowrap"', $MSG['required'][$sysSession->lang].$MSG['max_255chars'][$sysSession->lang]);
            showXHTML_tr_E();

            if ($IsNews)
            {
                $date = getdate();
                $ary  = array(
                    'start_time' => 'open_time',
                    'end_time'   => 'close_time'
                );
                foreach ($ary as $k => $v)
                {
                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                    showXHTML_tr_B($col);
                        showXHTML_td('align="right"', $MSG[$k][$sysSession->lang]);
                        showXHTML_td_B('');
                            $tmp = $sysConn->UnixTimeStamp($_POST[$v]);
                            if (empty($tmp))
                            {
                                $val = sprintf('%04d-%02d-%02d %02d:%02d', $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
                                $ck  = '';
                                $ds  = ' style="display: none;"';
                            }
                            else
                            {
                                $val = substr($_POST[$v], 0, 16);
                                $ck  = ' checked';
                                $ds  = '';
                            }
                            showXHTML_input('checkbox', 'ck_' . $v, '', '', 'id="ck_' . $v . '" onclick="showDateInput(\'span_' . $v . '\', this.checked)"'. $ck);
                            echo $MSG['btn_enable'][$sysSession->lang] .
                                '<span id="span_' . $v . '"' . $ds . '>' . $MSG['msg_enable_date'][$sysSession->lang];
                            showXHTML_input('text', $v, $val, '', 'id="' . $v . '" readonly="readonly" class="cssInput"');
                            echo '</span>';
                        showXHTML_td_E();
                        showXHTML_td('&nbsp;');
                    showXHTML_tr_E();
                }
            }

            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td('align="right" nowrap="nowrap"', $MSG['contents'][$sysSession->lang]);
                showXHTML_td_B('nowrap="nowrap"');
                    $oEditor = new wmEditor;
                    $oEditor->setValue(($content)?$content:'&nbsp;');
                    $oEditor->addContType('isHTML', 1);
                    $oEditor->generate('content', 500, 350);
                showXHTML_td_E('');
                showXHTML_td('align="left"', $MSG['required'][$sysSession->lang]);
            showXHTML_tr_E('');

            if (in_array(EDIT_MODE, array('edit', 'q_edit')))
            {
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col . ' id="attList"');
                    showXHTML_td('align="right" nowrap="nowrap"', $MSG['delattach'][$sysSession->lang]);
                    showXHTML_td_B('nowrap="nowrap" style="padding-top: 0; padding-bottom: 0"');
                        if (in_array(EDIT_MODE, array('edit')))
                        {
                            echo generate_attach_del(get_attach_file_path('board', $sysSession->board_ownerid) . DIRECTORY_SEPARATOR . trim($_POST['mnode']), trim($_POST['o_att']), 'board', $MSG['del'][$sysSession->lang]);
                        }
                        elseif (in_array(EDIT_MODE, array('q_edit')))
                        {
                            echo generate_attach_del(get_attach_file_path('quint', $sysSession->board_ownerid) . DIRECTORY_SEPARATOR . trim($_POST['mnode']), trim($_POST['o_att']), 'quint', $MSG['del'][$sysSession->lang]);
                        }
                    showXHTML_td_E('');
                    showXHTML_td('&nbsp;');
                showXHTML_tr_E('');
            }

            if (in_array(EDIT_MODE, array('write', 'reply', 'q_write')))
            {
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="right" nowrap="nowrap"', $MSG['tagline'][$sysSession->lang]);
                    showXHTML_td_B('colspan="2" nowrap="nowrap"');
                        $RS1         = dbGetStMr('WM_user_tagline', '`serial`, `title`, `ctype`', "username='{$sysSession->username}' LIMIT 0,1", ADODB_FETCH_ASSOC);
                        $tagline     = array();
                        $tagline[-1] = $MSG['not_use_tagline'][$sysSession->lang];
                        if ($RS1)
                        {
                            while (!$RS1->EOF)
                            {
                                $tagline[$RS1->fields['serial']] = $MSG['use_tagline'][$sysSession->lang]; // $RS1->fields['title'];
                                $RS1->MoveNext();
                            }
                        }
                        showXHTML_input('select', 'tagline', $tagline, $_POST['tagline'], 'class="cssInput"');
                    showXHTML_td_E();
                showXHTML_tr_E();

                if (in_array(EDIT_MODE, array('write', 'q_write')))
                {
                    if (strpos($RS['switch'], 'mailfollow') !== FALSE)
                    {
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        showXHTML_tr_B($col);
                            showXHTML_td('align="right" valign="center"', $MSG['title_mailfollow'][$sysSession->lang]);
                            showXHTML_td_B('colspan="2"');
                                $ary = array(
                                    'mailfollow' => $MSG['title_yes'][$sysSession->lang],
                                    'nix'        => $MSG['title_no'][$sysSession->lang]
                                );
                            showXHTML_input('radio', 'switch', $ary, $RS['switch'], 'onclick="this.form.with_attach.disabled=(this.value==\'nix\');"');
                            showXHTML_input('checkbox', 'with_attach', 'yes', '', ($RS['with_attach']=='yes' ? 'checked' : ''));
                            echo $MSG['with_attach'][$sysSession->lang];
                            showXHTML_td_E();
                        showXHTML_tr_E();
                    }
                }
            }

            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col . ' id="upload_box"');
                showXHTML_td('align="right" nowrap="nowrap"', $MSG['attach'][$sysSession->lang]);
                showXHTML_td_B('nowrap="nowrap"');
                    showXHTML_input('file', '', '', '', 'class="cssInput" size="76"' . ($freeQuota > 0 ? '' : 'disabled=true'));
                showXHTML_td_E('');
                showXHTML_td(' align="left"', str_replace('%MIN_SIZE%', ini_get('upload_max_filesize'), $MSG['attach_note'][$sysSession->lang]));
            showXHTML_tr_E('');

            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col . ' id="upload_base"');
                showXHTML_td_B('nowrap="nowrap" colspan="3"');
                    $sbbtn = 'ok';
                    if (EDIT_MODE == 'edit' || EDIT_MODE == 'q_edit')
                    {
                        showXHTML_input('hidden', 'mnode', trim($_POST['mnode']), '', '');
                        showXHTML_input('hidden', 'etime', trim($_POST['etime']), '', '');
                        showXHTML_input('hidden', 'o_att', trim($_POST['o_att']), '', '');
                    }
                    if (EDIT_MODE == 'write' || EDIT_MODE == 'q_write')
                    {
                        $sbbtn = 'post';
                    }
                    if (EDIT_MODE == 'reply')
                    {
                        showXHTML_input('hidden', 'isReply', '1');
                        showXHTML_input('hidden', 'node'   , trim($_POST['node']));
                    }
                    showXHTML_input('hidden', 'whoami' , EDIT_MODE . '.php');
                    showXHTML_input('submit', 'btnPost', $MSG[$sbbtn][$sysSession->lang]    , '', 'name="btnPost" id="btnPost" class="cssBtn"'); echo '&nbsp;&nbsp;';
                    if (in_array(EDIT_MODE, array('edit', 'reply', 'write')))
                    {
                        showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang]  , '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\''.($sysSession->post_no?'read':'index').'.php\');"'); echo '&nbsp;&nbsp;';
                    }
                    elseif (in_array(EDIT_MODE, array('q_edit', 'q_write')))
                    {
                        showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang],   '', 'class="cssBtn" onclick="xajax_clean_temp(st_id); location.replace(\''.($sysSession->q_post_no?'q_read':'q_index').'.php\');"'); echo '&nbsp;&nbsp;';
                    }
                    showXHTML_input('button', ''       , $MSG['more_att'][$sysSession->lang], '', 'id="btnMoreAtt" class="cssBtn" onclick="more_attachs();"'); echo '&nbsp;&nbsp;';
                    showXHTML_input('button', ''       , $MSG['cut_att'][$sysSession->lang] , '', 'id="btnCutAtt" class="cssBtn" onclick="cut_attachs();"');
                showXHTML_td_E('');
            showXHTML_tr_E('');
        showXHTML_table_E();
    showXHTML_tabFrame_E();
    echo '</div>';

    if (EDIT_MODE == 'write')
    {
        $ary = array();
        $ary[] = array($MSG['time'][$sysSession->lang], 'timeout_ui');
        echo '<div align="center">';
        showXHTML_tabFrame_B($ary, 1, '', 'timeout_ui', 'style="display: inline"', true);
            showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td('id="timeout_note"', '');
                showXHTML_tr_E('');
                showXHTML_tr_B('class="cssTrOdd"');
                    showXHTML_td_B();
                        showXHTML_input('button','btnImpOK',$MSG['ok'][$sysSession->lang],'','onclick="displayTimeoutUI(\'\',false);"');
                    showXHTML_td_E();
                showXHTML_tr_E();
            showXHTML_table_E('');
        showXHTML_tabFrame_E();
        echo '</div>';
    }

    echo '<form style="display: none"><input type="hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
    showXHTML_script('inline', "
        var st_id = '{$st_id}';
        xajax_check_temp(st_id, 'FCK.editor');
        window.setInterval(function(){xajax_save_temp(st_id, editor.getHTML());}, 100000);
    ");
    showXHTML_body_E();

// }}} 主程式 end
?>

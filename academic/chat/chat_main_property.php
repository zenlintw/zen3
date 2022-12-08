<?php
	/**
	 * 聊天室設定
	 *
	 * @since   2003/12/26
	 * @author  ShenTing Lin
	 * @version $Id: chat_main_property.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	//$sysSession->cur_func = '2000100300';
	//$sysSession->restore();
	if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 必須設定 $env
	if (!isset($env)) die($MSG['access_deny'][$sysSession->lang]);
	$env = trim($env);
	// 不得利用 cookie、post 或 get 方法設定 $env
	$ary = array($_COOKIE['env'], $_POST['env'], $_GET['env']);
	if (in_array($env, $ary)) die($MSG['access_deny'][$sysSession->lang]);

	// 必須設定 $owner_id
	if (!isset($owner_id)) die($MSG['access_deny'][$sysSession->lang]);
	$owner_id = trim($owner_id);
	// 不得利用 cookie、post 或 get 方法設定 $owner_id
	$ary = array($_COOKIE['owner_id'], $_POST['owner_id'], $_GET['owner_id']);
	if (in_array($owner_id, $ary)) die($MSG['access_deny'][$sysSession->lang]);

	$chatStatus = array(
		'disable' => $MSG['status_disable'][$sysSession->lang],
		'open'    => $MSG['status_open'][$sysSession->lang]
	);

	if ($env == 'teach')
		$chatStatus['taonly'] = $MSG['status_taonly'][$sysSession->lang];


	$ticket = md5(sysTicketSeed . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
		wmSysLog($sysSession->cur_func, 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$date = getdate();
	$dd = array(
		'title'      => '',
		'limit'      => 0 ,
		'exitAct'    => 'forum',
		'jump'       => 'deny',
		'openY'      => $date['year'],
		'openM'      => $date['mon'],
		'openD'      => $date['mday'],
		'openH'      => 0,
		'openMin'    => 0,
		'closeY'     => $date['year'],
		'closeM'     => $date['mon'],
		'closeD'     => $date['mday'],
		'closeH'     => 0,
		'closeMin'   => 0,
		'status'     => 'open',
		'visibility' => 'visible',
		'media'      => 'disable',
		'ip'         => '',
		'port'       => '',
		'protocol'   => 'TCP',
		'host'       => $sysSession->username,
		'login'      => 'N'
	);

	$chatid = preg_replace('/\W+/', '', $_POST['chat_id']);
	// $chatid = 10001;
	if (!empty($chatid)) {
		$RS =  dbGetStSr('WM_chat_setting', '*', "`rid`='{$chatid}'", ADODB_FETCH_ASSOC);
		$ot = $sysConn->UnixTimeStamp($RS['open_time']);
		$ct = $sysConn->UnixTimeStamp($RS['close_time']);
		$oD = getdate($ot);
		$cD = getdate($ct);
		$dd['title']      = $RS['title'];
		$dd['limit']      = intval($RS['maximum']);
		$dd['exitAct']    = trim($RS['exit_action']);
		$dd['jump']       = trim($RS['jump']);
		$dd['status']     = trim($RS['state']);
		$dd['visibility'] = trim($RS['visibility']);
		$dd['media']      = trim($RS['media']);
		$dd['ip']         = trim($RS['ip']);
		$dd['port']       = intval($RS['port']);
		$dd['protocol']   = trim($RS['protocol']);
		$dd['host']       = trim($RS['host']);
		$dd['login']      = trim($RS['get_host']);
		if (!empty($ot)) {
			$dd['openY']      = $oD['year'];
			$dd['openM']      = $oD['mon'];
			$dd['openD']      = $oD['mday'];
			$dd['openH']      = $oD['hours'];
			$dd['openMin']    = $oD['minutes'];
		}
		if (!empty($ct)) {
			$dd['closeY']     = $cD['year'];
			$dd['closeM']     = $cD['mon'];
			$dd['closeD']     = $cD['mday'];
			$dd['closeH']     = $cD['hours'];
			$dd['closeMin']   = $cD['minutes'];
		}
	}

	$js = <<< BOF
	var MSG_DATE_ERROR = "{$MSG['msg_date_error'][$sysSession->lang]}";
	function chgStatus(val, n) {
		var obj = null;
		if (val == 0) {
			obj = document.getElementById("month" + n);
			if (obj != null) obj.disabled = true;
			obj = document.getElementById("day" + n);
			if (obj != null) obj.disabled = true;
			obj = document.getElementById("hour" + n);
			if (obj != null) obj.disabled = true;
			obj = document.getElementById("minute" + n);
			if (obj != null) obj.disabled = true;
		} else {
			obj = document.getElementById("month" + n);
			if (obj != null) obj.disabled = false;
			obj = document.getElementById("day" + n);
			if (obj != null) obj.disabled = false;
			obj = document.getElementById("hour" + n);
			if (obj != null) obj.disabled = false;
			obj = document.getElementById("minute" + n);
			if (obj != null) obj.disabled = false;
		}
	}

	function genDay(n){
		var year  = parseInt(document.getElementById("year" + n).value);
		var month = parseInt(document.getElementById("month" + n).value);
		var days  = 30;

		var IH = '<select name="day' + n + '" id="day' + n + '" class="cssInput">';
		var i = 0;

		chgStatus(year, n);
		if (year == 0) return false;

		if (month == 2) {
			days = 28 + ((year % 4) ? 0 : ((year % 100) ? 1 : ((year % 400) ? 0 : 1)));
		} else {
			days = 30 + ((month > 7 ? month + 1 : month) % 2);
		}

		for (i = 1; i <= days; i++) {
			IH += '<option value="' + i + '">' + i + '</option>\\n';
		}

		document.getElementById('daySel' + n).innerHTML = IH + '</select>';
	}

	function saveSetting() {
		var val1 = 0; val2 = 0;
		var obj = document.getElementById("setFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		// 檢查討論室名稱是否填寫 (check chatroom name)
		if (!chk_multi_lang_input(1, true, "{$MSG['msg_need_name'][$sysSession->lang]}")) return false;


		// 檢查討論室主持人 (check chatroom host)
		if (obj.host_root.value == "") {
			alert("{$MSG['msg_need_host'][$sysSession->lang]}");
			obj.host_root.focus();
			return false;
		}
		// 檢查開放予關閉日期 (check open date and close date)
		if (!(obj.visibility[1].checked || obj.status[0].checked)) {
			if (obj.ckopen.checked && obj.ckclose.checked) {
				val1 = obj.timeopen.value.replace(/[\D]/ig, '');
				val2 = obj.timeclose.value.replace(/[\D]/ig, '');
				if (parseInt(val1) >= parseInt(val2)) {
					alert(MSG_DATE_ERROR);
					obj.timeopen.focus();
					return false;
				}
			}
		}
		obj.submit();
	}

	/**
	 * 切換在列表上顯示或隱藏
	 * @param string val : visable 或 hidden
	 * @return void
	 **/
	function statListShow(val) {
		var obj = null;
		var v = (val == "visible");

		obj = document.getElementById("trStatus");
		if (obj != null) obj.style.display = v ? "" : "none";
		if (!v) {
			obj = document.getElementById("trOpen");
			if (obj != null) obj.style.display = "none";
			obj = document.getElementById("trClose");
			if (obj != null) obj.style.display = "none";
		} else {
			obj = document.getElementById("setFm");
			v = (obj.status[0].checked) ? "disable" : "";
			statListDateShow(v);
		}
	}

	/**
	 * 切換在列表上顯示或隱藏
	 * @param string val : visable 或 hidden
	 * @return void
	 **/
	function statListDateShow(val) {
		var obj = null;
		var v = (val != "disable");

		obj = document.getElementById("trOpen");
		if (obj != null) obj.style.display = v ? "" : "none";
		obj = document.getElementById("trClose");
		if (obj != null) obj.style.display = v ? "" : "none";
	}

	/**
	 * 切換是否啟用影音互動
	 * @param boolean val : true 或 false
	 * @return void
	 **/
	function statMediaShow(val) {
		var obj = null;
		obj = document.getElementById("trIP");
		if (obj != null) obj.style.display = val ? "" : "none";
		obj = document.getElementById("trProtocol");
		if (obj != null) obj.style.display = val ? "" : "none";
	}

	/**
	 * 切換是否啟用開放或關閉時間
	 * @param boolean val : true 或 false
	 * @param string  objname: 物件名稱
	 * @return void
	 **/
	function statDateShow(val, objName) {
		var obj = document.getElementById(objName);
		if (obj != null) obj.style.visibility = (val) ? "visible" : "hidden";
	}

	// 秀日曆的函數
	function Calendar_setup(ifd, fmt, btn, shtime) {
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

	/**
	 * 回到管理列表
	 **/
	function goManage() {
		window.location.replace("chat_manage.php");
	}

	window.onload = function () {
		Calendar_setup("timeopen" , "%Y-%m-%d %H:%M", "timeopen" , true);
		Calendar_setup("timeclose", "%Y-%m-%d %H:%M", "timeclose", true);
	};
BOF;

	showXHTML_head_B($MSG['chat_set_title'][$sysSession->lang]);
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1){
        echo '<style>';
        echo '#outerTable{margin:0 auto;width:96%;margin-top:15px;}';
        echo 'textarea {width:90%}';
        echo '.cssBtn {height:unset}';
        echo '</style>';
    }
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array();
		if (empty($_POST['chat_id'])) {
			$ary[] = array($MSG['tabs_chat_new'][$sysSession->lang], 'tabs_host');
		} else {
			$ary[] = array($MSG['tabs_chat_property'][$sysSession->lang], 'tabs_host');
		}
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'setFm', 'outerTable', 'action="chat_save.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			// 聊天室編號
			showXHTML_input('hidden', 'chat_id', $chatid, '', '');
			$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $chatid);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			// 主持人設定 (Begin)
			if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1){
			    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
			} else {
			    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
			}
				// 聊天室名稱
				$lang = old_getCaption($dd['title']);
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$arr_names = array('Big5'		=>	'host_room_name_big5',
								   'GB2312'		=>	'host_room_name_gb',
								   'en'			=>	'host_room_name_en',
								   'EUC-JP'		=>	'host_room_name_jp',
								   'user_define'=>	'host_room_name_user'
								   );
				showXHTML_tr_B($col);
					showXHTML_td('width="180" style="min-width:120px"', $MSG['host_msg_room_name'][$sysSession->lang]);
					showXHTML_td_B('width="570"');
                        if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1){
                          $multi_lang = new Multi_lang(true, $lang, $col); // 多語系輸入框
                        }else{
                          $multi_lang = new Multi_lang(false, $lang, $col); // 多語系輸入框
                        }
						$multi_lang->show(true, $arr_names);
					showXHTML_td_E();
				showXHTML_tr_E();

				// 人數限制
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						echo $MSG['host_msg_user_limit1'][$sysSession->lang];
						showXHTML_input('text', 'host_user_limit', $dd['limit'], '', 'maxlength="5" class="cssInput" style="width: 30px;"');
						echo $MSG['host_msg_user_limit2'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 結束聊天後處理聊天內容
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						echo $MSG['host_msg_exit'][$sysSession->lang];
						$exitHost = array('none'     => $MSG['exit_act_none'][$sysSession->lang],
							              'notebook' => $MSG['exit_act_notebook'][$sysSession->lang],
							              'forum'    => $MSG['exit_act_forum'][$sysSession->lang]);
						showXHTML_input('select', 'host_exit', $exitHost, $dd['exitAct'], 'class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 允許切換聊天室
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						$chk = ($dd['jump'] == 'allow') ? ' checked="checked"' : '';
						showXHTML_input('checkbox', 'host_change', '', '', $chk);
						echo $MSG['host_msg_allow_chg'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 顯示或隱藏
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['chat_visibility'][$sysSession->lang]);
					showXHTML_td_B('');
						$ary = array(
							'visible' => $MSG['chat_visible'][$sysSession->lang],
							'hidden'  => $MSG['chat_hidden'][$sysSession->lang]
						);
						showXHTML_input('radio', 'visibility', $ary, $dd['visibility'], 'onclick="statListShow(this.value);"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 狀態
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$dis = ($dd['visibility'] == 'hidden') ? ' style="display: none;"' : '';
				showXHTML_tr_B('id="trStatus" ' . $col . $dis);
					showXHTML_td('', $MSG['chat_status'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'status', $chatStatus, $dd['status'], 'onclick="statListDateShow(this.value)"', '<br />');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 開放日期
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$dis = '';
				if (($dd['visibility'] == 'hidden') || ($dd['status'] == 'disable')) {
					$dis = ' style="display: none;"';
				}
				showXHTML_tr_B('id="trOpen" ' . $col . $dis);
					showXHTML_td('', $MSG['chat_open_time'][$sysSession->lang]);
					showXHTML_td_B('');
						$val = sprintf('%04d-%02d-%02d %02d:%02d', $dd['openY'], $dd['openM'], $dd['openD'], $dd['openH'], $dd['openMin']);
						$ck = empty($ot) ? '' : ' checked="checked"';
						showXHTML_input('checkbox', 'ckopen', '', '', 'id="ckopen" onclick="statDateShow(this.checked, \'spanopen\');"' . $ck);
						echo '<label for="ckopen">' . $MSG['date_enable'][$sysSession->lang] . '</label>';
						$dis = empty($ot) ? ' style="visibility: hidden;"' : '';
						echo '<span id="spanopen"' . $dis . '>' . $MSG['msg_datetime'][$sysSession->lang];
						showXHTML_input('text', 'timeopen', $val, '', 'id="timeopen" readonly="readonly" class="cssInput"');
						echo '</span>';
					showXHTML_td_E();
				showXHTML_tr_E();
				// 關閉日期
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$dis = '';
				if (($dd['visibility'] == 'hidden') || ($dd['status'] == 'disable')) {
					$dis = ' style="display: none;"';
				}
				showXHTML_tr_B('id="trClose" ' . $col . $dis);
					showXHTML_td('', $MSG['chat_close_time'][$sysSession->lang]);
					showXHTML_td_B('');
						$val = sprintf('%04d-%02d-%02d %02d:%02d', $dd['closeY'], $dd['closeM'], $dd['closeD'], $dd['closeH'], $dd['closeMin']);
						$ck = empty($ct) ? '' : ' checked="checked"';
						showXHTML_input('checkbox', 'ckclose', '', '', 'id="ckclose" onclick="statDateShow(this.checked, \'spanclose\');"' . $ck);
						echo '<label for="ckclose">' . $MSG['date_enable'][$sysSession->lang] . '</label>';
						$dis = empty($ct) ? ' style="visibility: hidden;"' : '';
						echo '<span id="spanclose"' . $dis . '>' . $MSG['msg_datetime'][$sysSession->lang];
						showXHTML_input('text', 'timeclose', $val, '', 'id="timeclose" readonly="readonly" class="cssInput"');
						echo '</span>';
					showXHTML_td_E();
				showXHTML_tr_E();
				// 主持人設定
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						echo $MSG['host_msg_host_set'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 聊天室管理員
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
						echo $MSG['host_msg_root'][$sysSession->lang];
						showXHTML_input('text', 'host_root', $dd['host'], '', 'maxlength="32" class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 登入時是否取回主持權
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
						echo $MSG['host_msg_login'][$sysSession->lang];
						$ary = array(
							'yes' => $MSG['yes'][$sysSession->lang],
							'no'  => $MSG['no'][$sysSession->lang],
						);
						$chk = ($dd['login'] == 'Y') ? 'yes' : 'no';
						showXHTML_input('radio', 'host_login', $ary, $chk, '');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 離開按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn" onclick="saveSetting()"');
						echo '&nbsp;';
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// 主持人設定 (End)
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

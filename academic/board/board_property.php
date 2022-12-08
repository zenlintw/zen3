<?php
	/**
	 * 議題設定
	 *
	 * @since   2004/01/08
	 * @author  ShenTing Lin
	 * @version $Id: board_property.php,v 1.1 2010/02/24 02:38:13 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/board_manage.php');
	
	// 議題狀態
	$titleStatus = array(
		'disable' => $MSG['type_disable'][$sysSession->lang],
		'open'    => $MSG['type_open'][$sysSession->lang],
		'taonly'  => $MSG['type_taonly'][$sysSession->lang]
	);

	// 預設排序的欄位
	$titleSort = array(
		'pt'      => $MSG['field_pt'][$sysSession->lang],
		'subject' => $MSG['field_subject'][$sysSession->lang],
		'poster'  => $MSG['field_poster'][$sysSession->lang],
		'rank'    => $MSG['field_rank'][$sysSession->lang],
		'hit'     => $MSG['field_hit'][$sysSession->lang]
	);
	
	$ticket = md5(sysTicketSeed . 'setBoard' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$date = getdate();
	$dd = array(
		'title'      => '',
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
		'shareY'     => $date['year'],
		'shareM'     => $date['mon'],
		'shareD'     => $date['mday'],
		'shareH'     => 0,
		'shareMin'   => 0,
		'status'     => 'open',
		'visibility' => 'visible',
		'help'       => '',
		'mailfollow' => 'no',
		'withattach' => 'no',
		'vpost' => '0',
		'sort'       => 'pt',
		'type'		 =>''
	);

	$nid = intval(trim($_POST['nid']));
	if (!empty($nid)) {
		$RS =  dbGetStSr('WM_term_subject', '`board_id`, `state`, `visibility`', "`node_id`='{$nid}'", ADODB_FETCH_ASSOC);
		$bid              = intval(trim($RS['board_id']));
		$dd['status']     = trim($RS['state']);
		$dd['visibility'] = trim($RS['visibility']);

		$RS = dbGetStSr('WM_bbs_boards', '*', "`board_id`={$bid}", ADODB_FETCH_ASSOC);
		$ot = $sysConn->UnixTimeStamp($RS['open_time']);
		$ct = $sysConn->UnixTimeStamp($RS['close_time']);
		$st = $sysConn->UnixTimeStamp($RS['share_time']);
		$oD = getdate($ot);
		$cD = getdate($ct);
		$sD = getdate($st);
		$ps = strpos($RS['switch'], 'mailfollow');
		$dd['title']      = $RS['bname'];
		$dd['help']       = $RS['title'];
		$dd['jump']       = trim($RS['jump']);
		$dd['mailfollow'] = ($ps === false) ? 'no' : 'yes';
		$dd['withattach'] = ($dd['mailfollow'] == 'yes') ? $RS['with_attach'] : 'no';
		$dd['vpost'] = $RS['vpost'];
		$dd['sort']       = trim($RS['default_order']);
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
		if (!empty($st)) {
			$dd['shareY']     = $sD['year'];
			$dd['shareM']     = $sD['mon'];
			$dd['shareD']     = $sD['mday'];
			$dd['shareH']     = $sD['hours'];
			$dd['shareMin']   = $sD['minutes'];
		}
		$type = trim($_POST['type']);
		if (!empty($type)) $dd['type'] = $type;
	}

	$js = <<< BOF
	var msgNote      = "{$MSG['note'][$sysSession->lang]}";
	var msgTitleHelp = "{$MSG['title_help'][$sysSession->lang]}";
	var msgCurLength = "{$MSG['current_length'][$sysSession->lang]}";
	var msgDontExceed= "{$MSG['dont_exceed'][$sysSession->lang]}";
	var MSG_DATE_ERROR = "{$MSG['msg_date_error'][$sysSession->lang]}";

	function saveSetting() {
		var obj = document.getElementById("setFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		
		if (!chk_multi_lang_input(1, true, "{$MSG['msg_need_name'][$sysSession->lang]}")) return false;
		
		// 主旨不能超出 255 bytes
		if(getTxtLength(obj.help)>255) {
			alert(msgNote + msgTitleHelp + "\n" + msgDontExceed);
			obj.help.focus();
			return false;
		}
		// 檢查開放予關閉時間 (check open date and close date)
		if (obj.ckopen.checked && obj.ckclose.checked) {
			val1 = obj.timeopen.value.replace(/[\D]/ig, '');
			val2 = obj.timeclose.value.replace(/[\D]/ig, '');
			if (parseInt(val1) >= parseInt(val2)) {
				if ((obj.visibility[0].checked) && (!(obj.status[0].checked))) {
					alert(MSG_DATE_ERROR);
					obj.timeopen.focus();
					return false;
				} else {
					obj.timeclose.value = obj.timeopen.value;
				}
			}
		}
		// 語音設定，白板設定
		obj.vpost.value = 0;
		if (obj.vpost1[0])
		{
			if (obj.vpost1[0].checked)	obj.vpost.value = parseInt(obj.vpost.value)+1;
		}
		if (obj.vpost2[0])
		{
			if (obj.vpost2[0].checked)	obj.vpost.value = parseInt(obj.vpost.value)+2;
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
			obj = document.getElementById("trLook");
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
		obj = document.getElementById("trLook");
		if (obj != null) obj.style.display = v ? "" : "none";
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
		window.location.replace("board_manage.php");
	}

	function getTxtLength(n) {
		v = n.value;
		j =0;
		for(i=0;i<v.length;i++) {
			c = v.charCodeAt(i);
			j+=(c>127?3:1);
		}
		return j;
	}
	function chgTitle(n) {
		var tl = document.getElementById('TxtLen');
		l = getTxtLength(n);
		if(l<200) {
			color='blue';
			msg = "";
		} else {
			color = l<255?'#BBBB00':'red';
			msg = msgNote + msgDontExceed;
		}

		tl.innerHTML = msgCurLength + "<font color=" + color + ">" + l + "</font>&nbsp;&nbsp;" + msg;
	}

	function chgWithAttach(wa) {
		if(wa.checked) { wa.form.mailfollow[0].checked = true; }
	}
	function chgMailFollow(mf) {
		if( mf.value == 'no') {	mf.form.withattach.checked = false; }
	}

	timerID = 0;
	window.onload = function () {
		Calendar_setup("timeopen" , "%Y-%m-%d %H:%M", "timeopen" , true);
		Calendar_setup("timeclose", "%Y-%m-%d %H:%M", "timeclose", true);
		Calendar_setup("timelook", "%Y-%m-%d %H:%M", "timelook", true);
		timerID = setInterval("chgTitle(document.setFm.help)",500);
	};
BOF;

	showXHTML_head_B($MSG['chat_set_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array();
		if (empty($_POST['nid'])) {
			$ary[] = array($MSG['subject_title_new'][$sysSession->lang], 'tabs_host');
		} else {
			$ary[] = array($MSG['subject_title_property'][$sysSession->lang], 'tabs_host');
		}
		showXHTML_tabFrame_B($ary, 1, 'setFm', '', 'action="board_save.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			// 議題編號
			showXHTML_input('hidden', 'nid', $nid, '', '');
			$ticket = md5(sysTicketSeed . 'setBorad' . $_COOKIE['idx'] . $nid);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_input('hidden', 'vpost', '0');
			$col = 'class="cssTrOdd"';
			showXHTML_table_B('width="600" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
				// 議題名稱
				$lang = unserialize(stripslashes($dd['title']));
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$arr_names = array('Big5'		=>	'subject_name_big5',
								   'GB2312'		=>	'subject_name_gb',
								   'en'			=>	'subject_name_en',
								   'EUC-JP'		=>	'subject_name_jp',
								   'user_define'=>	'subject_name_user'
								   );
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_subject'][$sysSession->lang]);
					showXHTML_td_B('');
						$multi_lang = new Multi_lang(false, $lang, $col); // 多語系輸入框
						$multi_lang->show(true, $arr_names);
					showXHTML_td_E();
				showXHTML_tr_E();
				
				// 說明
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_help'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('textarea', 'help', $dd['help'], '', 'cols="45" rows="6" class="cssInput"');
						echo "<div id='TxtLen'>{$MSG['current_length'][$sysSession->lang]}</div> </font>";
					showXHTML_td_E();
				showXHTML_tr_E();

				// 顯示或隱藏
				// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' style="display: none;"');
					showXHTML_td('', $MSG['visibility'][$sysSession->lang]);
					showXHTML_td_B('');
						$ary = array(
							'visible' => $MSG['title_visible'][$sysSession->lang],
							'hidden'  => $MSG['title_hidden'][$sysSession->lang]
						);
						showXHTML_input('radio', 'visibility', $ary, $dd['visibility'], 'onclick="statListShow(this.value);"');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 狀態
				// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$dis = ($dd['visibility'] == 'hidden') ? ' style="display: none;"' : '';
				showXHTML_tr_B('id="trStatus" ' . $col . ' style="display: none;"');
					showXHTML_td('', $MSG['title_status'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'status', $titleStatus, $dd['status'], 'onclick="statListDateShow(this.value)"', '<br />');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 啟用時間
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$dis = '';
				if (($dd['visibility'] == 'hidden') || ($dd['status'] == 'disable')) {
					$dis = ' style="display: none;"';
				}
				showXHTML_tr_B('id="trOpen" ' . $col . $dis);
					showXHTML_td('', $MSG['title_open_time'][$sysSession->lang]);
					showXHTML_td_B('');
						$val = sprintf('%04d-%02d-%02d %02d:%02d', $dd['openY'], $dd['openM'], $dd['openD'], $dd['openH'], $dd['openMin']);
						$ck = empty($ot) ? '' : ' checked="checked"';
						showXHTML_input('checkbox', 'ckopen', '', '', 'id="ckopen" onclick="statDateShow(this.checked, \'spanopen\');"' . $ck);
						echo '<label for="ckopen">' . $MSG['type_open'][$sysSession->lang] . '</label>';
						$dis = empty($ot) ? ' style="visibility: hidden;"' : '';
						echo '<span id="spanopen"' . $dis . '>' . $MSG['msg_datetime'][$sysSession->lang];
						showXHTML_input('text', 'timeopen', $val, '', 'id="timeopen" readonly="readonly" class="cssInput"');
						echo '</span>';
					showXHTML_td_E();
				showXHTML_tr_E();
				// 關閉時間
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$dis = '';
				if (($dd['visibility'] == 'hidden') || ($dd['status'] == 'disable')) {
					$dis = ' style="display: none;"';
				}
				showXHTML_tr_B('id="trClose" ' . $col . $dis);
					showXHTML_td('', $MSG['title_close_time'][$sysSession->lang]);
					showXHTML_td_B('');
						$val = sprintf('%04d-%02d-%02d %02d:%02d', $dd['closeY'], $dd['closeM'], $dd['closeD'], $dd['closeH'], $dd['closeMin']);
						$ck = empty($ct) ? '' : ' checked="checked"';
						showXHTML_input('checkbox', 'ckclose', '', '', 'id="ckclose" onclick="statDateShow(this.checked, \'spanclose\');"' . $ck);
						echo '<label for="ckclose">' . $MSG['type_open'][$sysSession->lang] . '</label>';
						$dis = empty($ct) ? ' style="visibility: hidden;"' : '';
						echo '<span id="spanclose"' . $dis . '>' . $MSG['msg_datetime'][$sysSession->lang];
						showXHTML_input('text', 'timeclose', $val, '', 'id="timeclose" readonly="readonly" class="cssInput"');
						echo '</span>';
					showXHTML_td_E();
				showXHTML_tr_E();
				// 開放參觀
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$dis = '';
				if (($dd['visibility'] == 'hidden') || ($dd['status'] == 'disable')) {
					$dis = ' style="display: none;"';
				}
				showXHTML_tr_B('id="trLook" ' . $col . $dis);
					showXHTML_td('', $MSG['title_share_time'][$sysSession->lang]);
					showXHTML_td_B('');
						$val = sprintf('%04d-%02d-%02d %02d:%02d', $dd['shareY'], $dd['shareM'], $dd['shareD'], $dd['shareH'], $dd['shareMin']);
						$ck = empty($st) ? '' : ' checked="checked"';
						showXHTML_input('checkbox', 'cklook', '', '', 'id="cklook" onclick="statDateShow(this.checked, \'spanlook\');"' . $ck);
						echo '<label for="cklook">' . $MSG['type_open'][$sysSession->lang] . '</label>';
						$dis = empty($st) ? ' style="visibility: hidden;"' : '';
						echo '<span id="spanlook"' . $dis . '>' . $MSG['msg_datetime'][$sysSession->lang];
						showXHTML_input('text', 'timelook', $val, '', 'id="timelook" readonly="readonly" class="cssInput"');
						echo '</span>';
					showXHTML_td_E();
				showXHTML_tr_E();

				// 自動轉寄
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_mailfollow'][$sysSession->lang]);
					showXHTML_td_B('');
						$ary = array(
							'yes' => $MSG['title_yes'][$sysSession->lang],
							'no'  => $MSG['title_no'][$sysSession->lang]
						);
						showXHTML_input('radio', 'mailfollow', $ary, $dd['mailfollow'], 'onclick="chgMailFollow(this)"', '');
						showXHTML_input('checkbox', 'withattach','yes',($dd['withattach']=='yes') , 'id="withattach" onclick="chgWithAttach(this)"');
						echo $MSG['with_attach'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				
				// 語音討論板
				if (Voice_Board == 'Y')
				{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['vpost'][$sysSession->lang]);
						showXHTML_td_B('');
						$ary = array(
							'1' => $MSG['title_yes'][$sysSession->lang],
							'0'  => $MSG['title_no'][$sysSession->lang]
						);
						showXHTML_input('radio', 'vpost1', $ary,(intval($dd['vpost'])&1), '', '');
						showXHTML_td_E();
					showXHTML_tr_E();
				}else{
					showXHTML_input('hidden', 'vpost1', '0');
				}
				
				// 電子白板
				if (White_Board == 'Y')
				{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['whiteboard'][$sysSession->lang]);
						showXHTML_td_B('');
						$ary = array(
							'2' => $MSG['title_yes'][$sysSession->lang],
							'0'  => $MSG['title_no'][$sysSession->lang]
						);
						showXHTML_input('radio', 'vpost2', $ary, (intval($dd['vpost'])&2), '', '');
						showXHTML_td_E();
					showXHTML_tr_E();
				}else{
					showXHTML_input('hidden', 'vpost2', '2');
				}
				
				// 預設排序的欄位
				// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B('style="display:none"');
					showXHTML_td('', $MSG['title_sort'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('radio', 'defsort', $titleSort, $dd['sort'], '', '<br />');
					showXHTML_td_E();
				showXHTML_tr_E();
				// 離開按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn" onclick="saveSetting()"');
						echo '&nbsp;';
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="goManage()"');
						showXHTML_input('hidden', 'type', $dd['type']);
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// 主持人設定 (End)
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

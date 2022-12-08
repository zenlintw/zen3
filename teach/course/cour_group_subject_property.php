<?php
	/**
	 * 議題設定
	 *
	 * @since   2004/01/08
	 * @author  ShenTing Lin
	 * @version $Id: cour_group_subject_property.php,v 1.1 2010/02/24 02:40:23 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/teach/course/cour_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '2000100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$ticket = md5(sysTicketSeed . 'setBoard' . $_COOKIE['idx']);
	if (trim($_POST['ticket']) != $ticket) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
		die($MSG['access_deny'][$sysSession->lang]);
	}

	/**
	 * 終止執行並且顯示訊息
	 * @param string $msg : 要顯示的訊息
	 * @return none
	 **/
	function errMSG($msg='')
	{
		global $sysSession, $MSG;

		if (empty($msg)) $msg = $MSG['msg_error_bid'][$sysSession->lang];
		$js = <<< BOF
	window.onload = function ()
	{
		alert("{$msg}");
		window.location.replace("/teach/course/cour_group_subject.php");
	}
BOF;
		showXHTML_script('inline', $js);
		die();
	}

	$dd = array(
		'title'      => '',
		'help'       => '',
		'mailfollow' => 'no',
		'withattach' => 'no',
		'vpost'      => '0',
		'sort'       => 'pt'
	);

	$bid = intval(trim($_POST['bid']));
	if (!empty($bid)) {
		$RS = dbGetStSr('WM_bbs_boards', '*', "`board_id`={$bid}", ADODB_FETCH_ASSOC);
		// 檢查是不是屬於該門課程的討論版
		if (strpos($RS['owner_id'], $sysSession->course_id) !== 0)
		{
			errMSG();
		}

		$ps = strpos($RS['switch'], 'mailfollow');
		$dd['title']      = $RS['bname'];
		$dd['help']       = $RS['title'];
		$dd['mailfollow'] = ($ps === false) ? 'no' : 'yes';
		$dd['withattach'] = ($dd['mailfollow'] == 'yes') ? $RS['with_attach'] : 'no';
		$dd['vpost']      = $RS['vpost'];
		$dd['sort']       = trim($RS['default_order']);
	}
	else
	{
		errMSG();
	}

	$js = <<< BOF
	var msgNote      = "{$MSG['note'][$sysSession->lang]}";
	var msgTitleHelp = "{$MSG['title_help'][$sysSession->lang]}";
	var msgCurLength = "{$MSG['current_length'][$sysSession->lang]}";
	var msgDontExceed= "{$MSG['dont_exceed'][$sysSession->lang]}";

	function saveSetting() {
		var obj = document.getElementById("setFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		// 檢查討論板名稱是否填寫 (check subject name)
		if (!chk_multi_lang_input(1, true, "{$MSG['msg_need_name'][$sysSession->lang]}")) return false;

		// 主旨不能超出 255 bytes
		if(getTxtLength(obj.help)>255) {
			alert(msgNote + msgTitleHelp + "\n" + msgDontExceed);
			obj.help.focus();
			return false;
		}
		
		obj.submit();
	}

	// 檢查字元長度
	/**
	 * 回到管理列表
	 **/
	function goManage() {
		window.location.replace("cour_group_subject.php");
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
	function TaskWatchLength() {
		timerID = setInterval("chgTitle(document.setFm.help)",500);
	}

BOF;

	showXHTML_head_B($MSG['chat_set_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('onLoad=TaskWatchLength();');

		$ary[] = array($MSG['group_title_property'][$sysSession->lang], 'tabs_host');
		showXHTML_tabFrame_B($ary, 1, 'setFm', '', 'action="cour_group_subject_save.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			// 議題編號
			showXHTML_input('hidden', 'bid', $bid, '', '');
			$ticket = md5(sysTicketSeed . 'setBorad' . $_COOKIE['idx'] . $bid);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_input('hidden', 'vpost', '0');
			$col = 'class="font01 bg04"';
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="box01"');
				// 議題名稱
				$lang = old_getCaption($dd['title']);
				$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
				$arr_names = array('Big5'		=>	'subject_name_big5',
							   	   'GB2312'		=>	'subject_name_gb',
							   	   'en'			=>	'subject_name_en',
							   	   'EUC-JP'		=>	'subject_name_jp',
							   	   'user_define'=>	'subject_name_user'
							   	   );
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_subject'][$sysSession->lang]);
					showXHTML_td_B();
						$multi_lang = new Multi_lang(false, $lang, $col); // 多語系輸入框
						$multi_lang->show(true, $arr_names);
					showXHTML_td_E();
				showXHTML_tr_E();

				// 說明
				$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_help'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('textarea', 'help', $dd['help'], '', 'cols="45" rows="6" class="box02"');
						echo "<div id='TxtLen'>{$MSG['current_length'][$sysSession->lang]}</div> </font>";
					showXHTML_td_E();
				showXHTML_tr_E();

				// 自動轉寄
				$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_mailfollow'][$sysSession->lang]);
					showXHTML_td_B();
						$ary = array(
							'yes' => $MSG['title_yes'][$sysSession->lang],
							'no'  => $MSG['title_no'][$sysSession->lang]
						);
						showXHTML_input('radio', 'mailfollow', $ary, $dd['mailfollow'], 'onclick="chgMailFollow(this)"', '');
						showXHTML_input('checkbox', 'withattach','yes',($dd['withattach']=='yes') , 'id="withattach" onclick="chgWithAttach(this)"');
						echo $MSG['with_attach'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				
				// 預設排序的欄位
				showXHTML_input('hidden', 'defsort', 'pt');
				/*$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_sort'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('radio', 'defsort', $titleSort, $dd['sort'], '', '<br />');
					showXHTML_td_E();
				showXHTML_tr_E();*/
				
				// 離開按鈕
				$col = ($col == 'class="font01 bg03"') ? 'class="font01 bg04"' : 'class="font01 bg03"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_save'][$sysSession->lang], '', 'class="button01" onclick="saveSetting()"');
						echo '&nbsp;';
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="button01" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// 主持人設定 (End)
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

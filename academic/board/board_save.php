<?php
	/**
     * 目  的 : 儲存討論室設定
     *
     * @since   2003/12/30
     * @author  ShenTing Lin
     * @version $Id: board_save.php,v 1.1 2010/02/24 02:38:13 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
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

	$nid = intval($_POST['nid']);
	$type = preg_replace('/\W/', '', $_POST['type']);
	$sysSession->cur_func = 900100100;
	// 檢查 ticket
	$ticket = md5(sysTicketSeed . 'setBorad' . $_COOKIE['idx'] . $nid);
	if (trim($_POST['ticket']) != $ticket) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
	   die($MSG['access_deny'][$sysSession->lang]);
	}

	$isError = false;
	// 檢查日期
	if (($_POST['visibility'] == 'visible') && ($_POST['status'] != 'disable') && isset($_POST['ckopen']) && isset($_POST['ckclose'])) {
		// 為了避免組合起來的數值大於 PHP 限定的範圍，所以先比對日期，在比對時間
		$vO = trim($_POST['timeopen']);
		$vO = explode(' ' , $vO);
		$vC = trim($_POST['timeclose']);
		$vC = explode(' ' , $vC);

		$d1 = ereg_replace("[^0-9]", '', $vO[0]);
		$d1 = intval($d1);
		$d2 = ereg_replace("[^0-9]", '', $vC[0]);
		$d2 = intval($d2);

		$t1 = ereg_replace("[^0-9]", '', $vO[1]);
		$t1 = intval($t1);
		$t2 = ereg_replace("[^0-9]", '', $vC[1]);
		$t2 = intval($t2);

		// 比對日期
		if (($d1 > $d2) || (($d1 == $d2) && ($t1 >= $t2))) {
			$msg = $MSG['msg_date_error'][$sysSession->lang];
			$isError = true;
			wmSysLog($sysSession->cur_func, 2, 'auto', $_SERVER['PHP_SELF'], $msg);
		}
	}

	if (($_POST['visibility'] == 'hidden') || ($_POST['status'] == 'disable')) {
		if (isset($_POST['ckopen'])) unset($_POST['ckopen']);
		if (isset($_POST['ckclose'])) unset($_POST['ckclose']);
		if (isset($_POST['cklook'])) unset($_POST['cklook']);
	}

	$open  = trim($_POST['timeopen'])  . ':00';
	$close = trim($_POST['timeclose']) . ':00';
	$share = trim($_POST['timelook']) . ':00';

	$subject_open  = !isset($_POST['ckopen'])  ? '0000-00-00 00:00:00' : $open;
	$subject_close = !isset($_POST['ckclose']) ? '0000-00-00 00:00:00' : $close;
	$subject_share = !isset($_POST['cklook']) ? '0000-00-00 00:00:00' : $share;

	$lang['Big5']   = stripslashes(trim($_POST['subject_name_big5']));
	$lang['GB2312'] = stripslashes(trim($_POST['subject_name_gb']));
	$lang['en']     = stripslashes(trim($_POST['subject_name_en']));
	$lang['EUC-JP'] = stripslashes(trim($_POST['subject_name_jp']));
	$lang['user_define'] = stripslashes(trim($_POST['subject_name_user']));

	$sw[] = (trim($_POST['mailfollow']) == 'yes') ? 'mailfollow' : '';
	$switch = implode(',' , $sw);
	$dd = array(
		'title'      => addslashes(serialize($lang)),
		'status'     => (in_array($_POST['status'], array('disable', 'open', 'taonly')) ? $_POST['status'] : 'open'),
		'visibility' => (in_array($_POST['visibility'], array('visible', 'hidden')) ? $_POST['visibility'] : 'visable'),
		'help'       => addslashes(strip_scr(stripslashes($_POST['help']))),
		'mailfollow' => trim($_POST['mailfollow']),
		'withattach' => ($_POST['withattach'] == 'yes' ? 'yes' : 'no'),
		'sort'       => (preg_match('/^\w+$/', $_POST['defsort']) ? $_POST['defsort'] : 'pt')
	);

	$owner = $sysSession->school_id;
	if (empty($nid)) {
		// 先儲存到 WM_bbs_boards
		// 再儲存到 WM_term_subject

		dbNew('WM_bbs_boards',
			'`bname`, `manager`, `title` , `owner_id`, ' .
			'`open_time`, `close_time`, `share_time`, ' .
			'`switch`, `with_attach`, `vpost`,`default_order`',
			"'{$dd['title']}', '', '{$dd['help']}', '{$owner}', " .
			"'{$subject_open}', '{$subject_close}', '{$subject_share}', " .
			"'{$switch}','{$dd['withattach']}', '{$_POST['vpost']}', '{$dd['sort']}'"
		);
		if ($sysConn->Affected_Rows() > 0) {
			$bid = $sysConn->Insert_ID();
			dbNew('WM_term_subject',
				'`course_id`, `board_id`, `state`, `visibility`',
				"'{$owner}', {$bid}, '{$dd['status']}', '{$dd['visibility']}'"
			);
			$nid = $sysConn->Insert_ID();
			dbSet('WM_term_subject', "`permute`={$nid}", "node_id={$nid}");
			$msg = ($sysConn->Affected_Rows() > 0) ? $MSG['msg_add_success'][$sysSession->lang] : $MSG['msg_add_fail'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 0, 'auto', $_SERVER['PHP_SELF'], 'new bbs boards:' . $msg);
		} else {
			$msg = $MSG['msg_add_fail'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'new bbs boards:' . $msg);
		}
	} else {
		list($bid) = dbGetStSr('WM_term_subject', '`board_id`', "`node_id`={$nid}", ADODB_FETCH_NUM);
		dbSet('WM_term_subject',
			"`state`='{$dd['status']}', `visibility`='{$dd['visibility']}'",
			"`node_id`={$nid}"
		);
		$suc1 = $sysConn->Affected_Rows();
		$update = "`bname`='{$dd['title']}', `title`='{$dd['help']}', `owner_id`='{$owner}', " .
			"`open_time`='{$subject_open}', `close_time`='{$subject_close}', `share_time`='{$subject_share}', " .
			"`switch`='{$switch}', `with_attach`='{$dd['withattach']}', `vpost`='{$_POST['vpost']}', `default_order`='{$dd['sort']}'";
		dbSet('WM_bbs_boards', $update,	"`board_id`={$bid}");
		$suc2 = $sysConn->Affected_Rows();
		$msg = (($suc1 > 0) || ($suc2 > 0)) ? $MSG['msg_update_success'][$sysSession->lang] : $MSG['msg_update_fail'][$sysSession->lang];
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 0, 'auto', $_SERVER['PHP_SELF'], 'update bbs boards:'.$msg);
	}

	// 系統預設的討論版 (最新消息,常見問題,校務意見箱,系統建議,教師交流,討論室記錄)
	// if ($type == 'news' || $type == 'faq' || $type == 'comment' || $type == 'suggest' || $type == 'teacher' || $type == 'school')
	//	chgSysBoard($type);

	/**
	 * 修改系統預設討論版的文字
	 * @since 2005/08/05
	 * @author Edi
	 * @param string $type 討論版類別(最新消息,常見問題,校務意見箱,系統建議,教師交流,討論室記錄)
	 */
	function chgSysBoard($type) {
		$sysBoard = array('news' 	=> 'SYS_07_01_001',
						  'faq'	 	=> 'SYS_07_01_002',
						  'comment' => 'SYS_07_01_007',
						  'suggest' => 'SYS_07_01_009',
						  'teacher' => 'SYS_07_01_011',
						  'school'  => 'SYS_07_01_010'
						);
		global $sysSession, $lang;
		$dir = sysDocumentRoot . "/base/{$sysSession->school_id}/system";
		if (!@is_dir($dir)) @mkdir($dir);
		$filename = sysDocumentRoot . "/base/{$sysSession->school_id}/system/system.xml";
		if (!empty($filename) && @is_file($filename)) {
			@$xmldoc = domxml_open_file($filename);
			if (!$xmldoc) return;
			$ctx = xpath_new_context($xmldoc);
			$nodes = $ctx->xpath_eval('//item[@id="'.$sysBoard[$type].'"]/title');
			if (count($nodes->nodeset)) {
				$oldnode = $nodes->nodeset[0];

				$title = "<title><big5>{$lang['Big5']}</big5><gb2312>{$lang['GB2312']}</gb2312><en>{$lang['en']}</en><euc-jp>{$lang['EUC-JP']}</euc-jp><user-define>{$lang['user_define']}</user-define></title>";
				$doc = domxml_open_mem($title);
				$newnode = $doc->document_element();

				$oldnode->replace_node($newnode);
			}
			$xmldoc->dump_file($filename);
		}
	}

	$js = <<< BOF
	/**
	 * 回到管理列表
	 **/
	function goManage() {
		window.location.replace("board_manage.php");
	}

	window.onload = function () {
		alert("{$msg}");
	};
BOF;

	showXHTML_head_B($MSG['subject_save_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['subject_save_title'][$sysSession->lang], 'tabs_host');
		showXHTML_tabFrame_B($ary, 1);
			// 主持人設定 (Begin)
			$col = 'class="cssTrOdd"';
			showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="3"', $msg);
				showXHTML_tr_E();
				// 聊天室名稱
				$lang = unserialize(stripslashes($dd['title']));
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_subject'][$sysSession->lang]);
					showXHTML_td_B('');
						$multi_lang = new Multi_lang(true, $lang, $col); // 多語系輸入框
						$multi_lang->show(false);
					showXHTML_td_E();
				showXHTML_tr_E();

				// 說明
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', "<pre>{$MSG['title_help'][$sysSession->lang]}</pre>");
					showXHTML_td('', nl2br($dd['help']));
				showXHTML_tr_E();
				// 顯示或隱藏
				// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' style="display :none;"');
					showXHTML_td('', $MSG['visibility'][$sysSession->lang]);
					showXHTML_td_B('');
						$ary = array(
							'visible' => $MSG['title_visible'][$sysSession->lang],
							'hidden'  => $MSG['title_hidden'][$sysSession->lang]
						);
						echo $ary[$dd['visibility']];
					showXHTML_td_E();
				showXHTML_tr_E();
				if ($dd['visibility'] == 'visible') {
					// 狀態
					// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col . ' style="display :none;"');
						showXHTML_td('', $MSG['title_status'][$sysSession->lang]);
						showXHTML_td('', $titleStatus[$dd['status']]);
					showXHTML_tr_E();
					if ($dd['status'] != 'disable') {
						// 啟用時間
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['title_open_time'][$sysSession->lang]);
							showXHTML_td_B('');
								echo !isset($_POST['ckopen']) ? $MSG['unlimit'][$sysSession->lang] : $open;
							showXHTML_td_E();
						showXHTML_tr_E();
						// 關閉時間
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['title_close_time'][$sysSession->lang]);
							showXHTML_td_B('');
								echo !isset($_POST['ckclose']) ? $MSG['unlimit'][$sysSession->lang] : $close;
							showXHTML_td_E();
						showXHTML_tr_E();
						// 開放參觀
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['title_share_time'][$sysSession->lang]);
							showXHTML_td_B('');
								echo !isset($_POST['cklook']) ? $MSG['unlimit'][$sysSession->lang] : $share;
							showXHTML_td_E();
						showXHTML_tr_E();
					}
				}
				// 自動轉寄
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_mailfollow'][$sysSession->lang]);
					showXHTML_td_B('');
						$ary = array(
							'yes' => $MSG['title_yes'][$sysSession->lang],
							'no'  => $MSG['title_no'][$sysSession->lang]
						);
						echo $ary[$dd['mailfollow']];
						echo ($dd['withattach']=='yes'?"&nbsp;({$MSG['with_attach'][$sysSession->lang]})":"");
					showXHTML_td_E();
				showXHTML_tr_E();

				// 語音討論板
				if (Voice_Board == 'Y')
				{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['vpost'][$sysSession->lang]);
						$tmpdesc = ( (intval($_POST['vpost'])&1) == 1) ? $MSG['title_yes'][$sysSession->lang]:$MSG['title_no'][$sysSession->lang];
						showXHTML_td('',$tmpdesc);
					showXHTML_tr_E();
				}

				// 白板討論
				if (White_Board == 'Y')
				{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['whiteboard'][$sysSession->lang]);
						$tmpdesc = ( (intval($_POST['vpost'])&2) == 2) ? $MSG['title_yes'][$sysSession->lang]:$MSG['title_no'][$sysSession->lang];
						showXHTML_td('',$tmpdesc);
					showXHTML_tr_E();
				}
				// 預設排序的欄位
				// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B('style="display:none"');
					showXHTML_td('', $MSG['title_sort'][$sysSession->lang]);
					showXHTML_td('', $titleSort[$dd['sort']]);
				showXHTML_tr_E();
				// 離開按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// 主持人設定 (End)
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

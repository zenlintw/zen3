<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/door.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/lib_acade_news.php');

	if(!dbGetNewsBoard($result, 'news')) {
		echo 'System Error!';
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'System Error!');
		exit();
	}

	if( empty($_GET['node']) ) {
		header('Location:index.php');
		exit();
	}
	elseif (!ereg('^[0-9]{9,}$', $_GET['node']))
	{
		wmSysLog($sysSession->cur_func, $sysSession->course_id , '' , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Deny!');
		die('Access Deny!');
	}

	$sysSession->board_id        = $result['board_id'];
	$sysSession->news_board      = 1;	// 含時間(開啟及關閉)欄位類型之討論版
	$sysSession->board_ownerid   = $sysSession->school_id;
	$sysSession->board_ownername = $sysSession->school_name;
	$sysSession->post_no         = 1;
	$IsNews                      = 1;
	// 取得本 POST 內容
	$RS = dbGetStSr('WM_bbs_posts', '*', "board_id={$sysSession->board_id} and node='{$_GET['node']}' ", ADODB_FETCH_ASSOC);
	if(!$RS) {
		header("HTTP/1.1 404 Not Found");
		exit();
	}

	// 增加點閱數
	dbSet('WM_bbs_posts', 'hit=hit+1', "board_id={$sysSession->board_id} and node='{$RS['node']}' and site={$RS['site']} limit 1");

	// 開始呈現 HTML
	showXHTML_head_B($MSG['msg_news_detail'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B('');
	showXHTML_form_B('action=""', 'toolbar1');
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary = array();
					$ary[] = array($MSG['msg_news_detail'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" class="cssTrOdd"');
					showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['subjs'][$sysSession->lang]);
							showXHTML_td('id="o_subject"',$RS['subject']);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['times'][$sysSession->lang]);
							showXHTML_td('id="pt"', $RS['pt']);
						showXHTML_tr_E();
						if($IsNews) {
							$NEWS  = dbGetStSr('WM_news_posts','open_time,close_time',"board_id={$sysSession->board_id} and node='{$RS['node']}'", ADODB_FETCH_ASSOC);
							$ot    = $sysConn->UnixTimeStamp($NEWS['open_time']);
							$ct    = $sysConn->UnixTimeStamp($NEWS['close_time']);
							$openT = empty($ot)?$MSG['unlimit'][$sysSession->lang]:substr($NEWS['open_time'], 0, 16);
							$closeT= empty($ct)?$MSG['unlimit'][$sysSession->lang]:substr($NEWS['close_time'], 0, 16);

							showXHTML_tr_B('class="cssTrOdd"');
								showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['start_time'][$sysSession->lang]);
								showXHTML_td('id="o_open_time"',$openT);
							showXHTML_tr_E();
							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['end_time'][$sysSession->lang]);
								showXHTML_td('id="o_close_time"',$closeT);
							showXHTML_tr_E();
						}
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['posters'][$sysSession->lang]);
							showXHTML_td('id="poster"', "<a href=\"mailto:{$RS['email']}\" class=\"cssAnchor\">{$RS['poster']}</a> ".($RS['homepage']?("({$RS['realname']} )"):"({$RS['realname']} )"));
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('colspan="2"','<table><tr><td id="o_content"><br />'.$RS['content'].'<p /></td></tr></table>');
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right"', $MSG['attach'][$sysSession->lang]);
							showXHTML_td('style="padding-top: 0; padding-bottom: 0"', generate_attach_link(get_attach_file_path('board', $sysSession->board_ownerid) .DIRECTORY_SEPARATOR. $RS['node'], $RS['attach']));
						showXHTML_tr_E();

						$css = 'class="cssTrEvn"';
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('align="center" nowrap="nowrap" colspan="2"');
							showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang],   '', 'onclick="window.close();" class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();
						$css = 'class="cssTrOdd"';

					showXHTML_table_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	showXHTML_form_E();
	showXHTML_body_E();
?>


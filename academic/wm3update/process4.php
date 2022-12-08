<?php

/**
 * 進行線上更新的程序步驟四
 **/

	set_time_limit(3000);
	ignore_user_abort(true);
		
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	
	//此線上更新只提供給root這帳號使用
	if ($sysSession->username != sysRootAccount)
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}

	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/wm3update.php');
	require_once(sysDocumentRoot . '/academic/wm3update/lib.php');
#========functions =================
	
#========main=======================
	//驗證update_id
	// $oUpdSess = new WM3UpdateSession();
	// if (!$oUpdSess->validSystemUpdateId($_GET['update_id'])) die("error update_id value");
	
	// $log = new WM3UpdateLog();
	// 線上更新的log, 在cron排程進行記錄
    // $log->AppendLog($_GET['rawfname'],$_GET['update_id'], $_SERVER['REMOTE_ADDR']);
	// $oUpdSess->removeLockFile();
	
#========Html output ===============
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B();
	$arry[] = array($MSG['tab_step1'][$sysSession->lang], 'TabStep1', 'tabsSelect(4);');
	$arry[] = array($MSG['tab_step2'][$sysSession->lang], 'TabStep2', 'tabsSelect(4);');
	$arry[] = array($MSG['tab_step3'][$sysSession->lang], 'TabStep3', 'tabsSelect(4);');
	$arry[] = array($MSG['tab_step4'][$sysSession->lang], 'TabStep4');
	showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
	  showXHTML_tr_B();
	    showXHTML_td_B();
	      showXHTML_tabs($arry, 4, false, false);
	    showXHTML_td_E();
	  showXHTML_tr_E();
	  showXHTML_tr_B('');
	  	showXHTML_td_B('valign="top" id="CGroup"');
	  	showXHTML_form_B('action="/academic/wm3update/process5.php" method="post" enctype="multipart/form-data" style="display:inline;"', 'setForm');
	  		showXHTML_input('hidden', 'op', '', '', '');
			showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('colspan="4"',$MSG['step3_desc'][$sysSession->lang]);
				showXHTML_tr_E('');
				
				//寄件者
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_from'][$sysSession->lang]);
					showXHTML_td('', "$sysSession->username ($sysSession->realname )");
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E('');
				
				//收件者
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_to'][$sysSession->lang]);
					showXHTML_td_B('nowrap="nowrap"');
						showXHTML_input('text', 'to', $to, '', 'class="cssInput" size="64"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['write_to_msg'][$sysSession->lang]);
				showXHTML_tr_E('');
				
				//信件主旨
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_subject'][$sysSession->lang]);
					showXHTML_td_B('nowrap="nowrap"');
						showXHTML_input('text', 'subject', $subject, '', 'class="cssInput" size="64" maxlength="200"');
					showXHTML_td_E('');
					showXHTML_td('', $MSG['write_subject_msg'][$sysSession->lang]);
				showXHTML_tr_E('');
				
				//信件內容
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_content'][$sysSession->lang]);
					showXHTML_td_B('nowrap="nowrap"');
						showXHTML_input('textarea', 'content', '', '', 'class="cssInput" cols="60" rows="5"');
					showXHTML_td_E('');
					showXHTML_td('', '&nbsp;');
				showXHTML_tr_E('');
						
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td_B('colspan="4" align="right"');
						echo '<input type="button" name="btnNext" value="'.$MSG['btn_do_final'][$sysSession->lang].'" onClick="this.form.submit();" class="cssBtn">';
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_form_E('');
			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E();
	showXHTML_body_E('');
?>

<?php

/**
 * 進行線上更新的程序步驟三
 * $Id: process3.php,v 1.1 2010/02/24 02:38:48 saly Exp $
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
	$oUpdSess = new WM3UpdateSession();
	if (!$oUpdSess->validSystemUpdateId($_GET['update_id'])) die("error update_id value");
	$oUpdSess->doBackup($_POST['patchFiles']);
    // 由於wmpro功能目錄會設定為唯讀，因此更新的動作不進行。
	// $proc = $oUpdSess->doUpdate($_POST['patchFiles']);
    // 寫入指令檔，等待常駐程式來更新
    if (!$oUpdSess->createInstructionFile("update", $_GET['rawfname'], $_POST['patchFiles'])) {
        die("Fail to Create Instruction.");
    }
    $oUpdSess->removeLockFile();
#========Html output ===============

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B();
	$arry[] = array($MSG['tab_step1'][$sysSession->lang], 'TabStep1', 'tabsSelect(3);');
	$arry[] = array($MSG['tab_step2'][$sysSession->lang], 'TabStep2', 'tabsSelect(3);');
	$arry[] = array($MSG['tab_step3'][$sysSession->lang], 'TabStep3');
	$arry[] = array($MSG['tab_step4'][$sysSession->lang], 'TabStep4', 'tabsSelect(3);');
	showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
	  showXHTML_tr_B();
	    showXHTML_td_B();
	      showXHTML_tabs($arry, 3, false, false);
	    showXHTML_td_E();
	  showXHTML_tr_E();
	  showXHTML_tr_B('');
	  	showXHTML_td_B('valign="top" id="CGroup"');
	  	showXHTML_form_B('action="/academic/wm3update/process4.php?update_id='.$_GET['update_id'].'&rawfname='.$_GET['rawfname'].'" method="post" enctype="multipart/form-data" style="display:inline;"', 'setForm');
	  		showXHTML_input('hidden', 'op', '', '', '');
			showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('colspan="4"',$MSG['step3_desc'][$sysSession->lang]);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('',$MSG['wait_cron_update'][$sysSession->lang]);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td_B('colspan="4" align="right"');
						echo '<input type="button" name="btnNext" value="'.$MSG['btn_do_step4'][$sysSession->lang].'" onClick="this.form.submit();" class="cssBtn">';
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_form_E('');
			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E();
	showXHTML_body_E('');
?>

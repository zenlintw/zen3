<?php

/**
 * 進行線上更新回覆步驟一
 * $Id: rollback.php,v 1.1 2010/02/24 02:38:48 saly Exp $
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
	$trcss = ($trcss == 'cssTrEvn' ) ? 'cssTrOdd' : 'cssTrEvn';
	function showfilehtml($act, $fname)
	{
		global $trcss;
		$trcss = ($trcss == 'cssTrEvn' ) ? 'cssTrOdd' : 'cssTrEvn';
		return '<tr class="'.$trcss.'">'.
		       sprintf('<td>%s %s</td>',$act,$fname) .
		       '</tr>';
	}

	

#========main=======================
	$o_rollback = new WM3Rollback($_POST['rollback_id']);
	
#========Html output ===============
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B();
	$arry[] = array($MSG['tab_rollback'][$sysSession->lang], 'TabStep1', '');
	showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
	  showXHTML_tr_B();
	    showXHTML_td_B();
	      showXHTML_tabs($arry, 2, false, false);
	    showXHTML_td_E();
	  showXHTML_tr_E();
	  showXHTML_tr_B('');
	  	showXHTML_td_B('valign="top" id="CGroup"');
	  	showXHTML_form_B('action="/academic/wm3update/rollback1.php" method="post" enctype="multipart/form-data" style="display:inline;"', 'setForm');
	  		showXHTML_input('hidden', 'rollback_id', $_POST['rollback_id'], '', '');
			showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('colspan="4"',$MSG['step_rollback_desc'][$sysSession->lang]);
				showXHTML_tr_E('');
				
				$arr = getDirctoryArchitecture($o_rollback->Patch_dir);
				for($i=0, $size=count($arr); $i<$size; $i++)
				{
					$filename = str_replace($o_rollback->Patch_dir,sysDocumentRoot,$arr[$i][1]);
					if ($arr[$i][0] == 'F') echo showfilehtml('<font color="red">remove</font>', $filename);
				}
				
				$arr = getDirctoryArchitecture($o_rollback->Backup_dir);
				for($i=0, $size=count($arr); $i<$size; $i++)
				{
					$filename = str_replace($o_rollback->Backup_dir,sysDocumentRoot,$arr[$i][1]);
					if ($arr[$i][0] == 'F') echo showfilehtml('<font color="blue">copy</font>', $arr[$i][1]." to ". $filename);
				}
				
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td_B('colspan="4" align="right"');
						echo '<input type="button" name="btnNext" value="'.$MSG['btn_go_rollback'][$sysSession->lang].'" onClick="this.form.submit();" class="cssBtn">';
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_form_E('');
			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E();
	showXHTML_body_E('');
?>

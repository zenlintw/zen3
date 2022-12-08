<?php
	/**
	 * 查詢線上更新的列表
	 * $Id: version.php,v 1.1 2010/02/24 02:38:55 saly Exp $
	 **/
	 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	$sysSession->lang="Big5";
	require_once(sysDocumentRoot . '/lang/wm3update.php');
	require_once(sysDocumentRoot . '/academic/wm3update/lib.php');
#========functions =================
	function showUpdateListHtml()
	{
		global $MSG, $sysSession;
		$obj = new WM3UpdateLog();
		$arr = $obj->getLogList();
		if (count($arr) == 0)
		{
			
		}else{
			for($i=0; $i<count($arr); $i++)
			{
				$trcss = ($trcss == "cssTrEvn" ) ? "cssTrOdd" : "cssTrEvn";
				showXHTML_tr_B('class="'.$trcss.'"');
				showXHTML_td('align="center"',$arr[$i][0]);
				showXHTML_td('align="center"',$arr[$i][1]);
				showXHTML_td('align="center"',$arr[$i][2]);
				showXHTML_td('align="center"',$arr[$i][4]);
				$oInfo = new WM3UpdateInfo($arr[$i][2]);
				showXHTML_td('align="center"',$oInfo->getUpdateUserInfo());
				showXHTML_td('align="center"','<input type="button" name="btn1" value="'.$MSG['btn_more'][$sysSession->lang].'" class="cssBtn" onClick="viewReadme(\''.$arr[$i][2].'\');">');
				showXHTML_td('align="center"','<input type="button" name="btn2" value="'.$MSG['btn_more'][$sysSession->lang].'" class="cssBtn" onClick="viewFiles(\''.$arr[$i][2].'\');">');
				showXHTML_tr_E('');
			}
		}
	}
#========Html output ===============
	require_once(sysDocumentRoot . '/lib/interface.php');

	$js = <<< BOF
	var msg = '{$MSG['cofirm_rollback'][$sysSession->lang]}';
	function Rollback(val)
	{
		if (confirm(msg))
		{
			document.FormRollback.rollback_id.value = val;
			document.FormRollback.submit();
		}
	}
	
	
	function viewReadme(dirname)
	{
		window.open('/academic/wm3update/viewUpdateInfo.php?content=readme&which='+dirname);
	}
	
	function viewFiles(dirname)
	{
		window.open('/academic/wm3update/viewUpdateInfo.php?content=filelist&which='+dirname);
	}
	
BOF;
	showXHTML_head_B("");
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B();
	showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
	  showXHTML_tr_B('');
	  	showXHTML_td_B('valign="top" id="CGroup"');
			showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
				showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td('align="center"',$MSG['list_th1'][$sysSession->lang]);
				showXHTML_td('align="center"',$MSG['list_th2'][$sysSession->lang]);
				showXHTML_td('align="center"',$MSG['list_th3_2'][$sysSession->lang]);
				showXHTML_td('align="center"',$MSG['list_th4'][$sysSession->lang]);
				showXHTML_td('align="center"',$MSG['list_th4_1'][$sysSession->lang]);
				showXHTML_td('align="center"',$MSG['list_th6'][$sysSession->lang]);
				showXHTML_td('align="center"',$MSG['list_th7'][$sysSession->lang]);
				showXHTML_tr_E('');
				echo showUpdateListHtml();
			showXHTML_table_E('');
			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E();
	showXHTML_body_E('');
?>

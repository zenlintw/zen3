<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/04/11                                                            *
	 *		work for  : 取得某考生對某次測驗的答案卷                                          *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lang/files_manager.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600300100';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700300100';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800300100';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if (!isset($_SERVER['argv'][0])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket_head = sysTicketSeed . $course_id . $_SERVER['argv'][1];
	if (md5($ticket_head) != $_SERVER['argv'][0]) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');
	}

	// 開始 output HTML
	showXHTML_head_B('Answer detail');
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");

	  $msgQuota = str_replace('%TYPE%', $MSG[$topDir == 'academic' ? 'school' : 'course'][$sysSession->lang], $MSG['quota_exceed'][$sysSession->lang]);
	  $scr = <<< EOB
function scoreAdd(num1, num2)
{
	var r1, r2, m;
	try{ r1 = num1.toString().split(".")[1].length; } catch(e){ r1 = 0; }
	try{ r2 = num2.toString().split(".")[1].length; } catch(e){ r2 = 0; }
	m = Math.pow(10,Math.max(r1,r2));
	return (num1 * m + num2 * m) / m;
}
function send_save(){
	var forms = document.getElementsByTagName('form');

	forms[0].submit();
}
function reload_score(){
	var forms = document.getElementsByTagName('form');
	var scores = forms[0].getElementsByTagName('input');
	var total_score = 0.0;

	for(var i=0; i< scores.length; i++){
		if (scores[i].name.indexOf('item_scores[') === 0) total_score = scoreAdd(total_score, parseFloat(scores[i].value));
	}
	forms[0].total_score.value = total_score;
}

window.onload=function()
{
	var score_input = document.getElementsByTagName('form')[0];
};

EOB;
	  showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B();
	  showXHTML_form_B('method="POST" action="exam_correct_import1.php" enctype="multipart/form-data"');
	    //showXHTML_table_B('border="0" cellpadding="3" cellspacing="0" width="800" style="border-collapse: collapse; border-style: solid; border-width: 1px; border-color: red"');
	    showXHTML_tabFrame_B(array(
		        array(
		            $MSG['hw_import'][$sysSession->lang]
		        )
		    ));
	    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable" style="width:1000;"');

	      showXHTML_tr_B('class="cssTrEvn"');
	        showXHTML_td('align="right"', $MSG['upload_file'][$sysSession->lang]);
	        showXHTML_td_B();
	          showXHTML_input('file', 'uploadz', '', '', 'size="30" class="cssInput"' . ($isQuotaExceed ? ' disabled' : ''));
	        showXHTML_td_E();
	        showXHTML_td_B();
	          echo $MSG['upload_file_tip'][$sysSession->lang];
	        showXHTML_td_E();
	      showXHTML_tr_E();

			$css = QTI_which == 'homework' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
	      showXHTML_tr_B($css . (QTI_which=='homework' ? '' : ' style="display:none"'));
	        showXHTML_td('align="right"', $MSG['function_tip_title'][$sysSession->lang]);
        showXHTML_td_B("colspan=2");
	          echo $MSG['function_tip'][$sysSession->lang];
	        showXHTML_td_E();
	      showXHTML_tr_E();

			$css = $css == 'class="cssTrOdd"' ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
	      showXHTML_tr_B($css);
	        showXHTML_td_B('colspan="3" align="center"');
	         showXHTML_input('button', '', $MSG['btn_import'][$sysSession->lang], '', ' class="cssBtn" onclick="send_save();"');
	          showXHTML_input('hidden', 'op',   'uz');
	          showXHTML_input('hidden', 'ticket',  $_SERVER['argv'][0]);
	          showXHTML_input('hidden', 'exam_id',  $_SERVER['argv'][1]);
	        showXHTML_td_E();
	      showXHTML_tr_E();

	    showXHTML_table_E();
	    showXHTML_tabFrame_E();
		
	  showXHTML_form_E();
	showXHTML_body_E();
?>

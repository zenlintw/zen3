<?php
	/**
	 * �ǲ߲έp
	 * $Id: last10.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/learn_stat.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	
	$sysSession->cur_func='1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$RS = dbGetStMr('WM_record_reading',
	                '*,SEC_TO_TIME(sum(UNIX_TIMESTAMP(over_time)-UNIX_TIMESTAMP(begin_time)+1)) as bt',
	                'course_id=' . intval($_GET['class_id']) . ' and username="' . $sysSession->username .
					'" group by activity_id order by activity_id',
					ADODB_FETCH_ASSOC);
	$datalist = array();
	if ($RS->RecordCount() >0){
	    while ($RS1 = $RS->FetchRow()){
	        $datalist[] = $RS1;
	    }
	}
	// assign
	$smarty->assign('datalist', $datalist);
	// output
	
	$smarty->display('common/tiny_header.tpl');
	$smarty->display('learn/last10.tpl');
	$smarty->display('common/tiny_footer.tpl');
	exit;
#======== html output ================
	showXHTML_head_B($MSG['student_sysbar'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
	  $ary[] = array($MSG['learn_list'][$sysSession->lang], 'divSettings');
	  echo "<center>\n";
	  showXHTML_tabFrame_B($ary, 1, '', 'ListTable');
	    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="100%" style="border-collapse: collapse" class="cssTable"');
	      showXHTML_tr_B('class="cssTrHead font01"');
			showXHTML_td('valign="top" align="center"', $MSG['learn_node'][$sysSession->lang]);
			showXHTML_td('valign="top" align="center"', $MSG['title6'][$sysSession->lang]);
	      showXHTML_tr_E('');
	      if ($RS->RecordCount() >0){
	      	while ($RS1 = $RS->FetchRow()){
	      		$cln = $cln == 'class="cssTrEvn font01"' ? 'class="cssTrOdd font01"' : 'class="cssTrEvn font01"';
	      		showXHTML_tr_B($cln);
	      		showXHTML_td('align="left" nowrap', $RS1['title']);
	      		showXHTML_td('align="left" nowrap', $RS1['bt']);
	      		showXHTML_tr_E();
	      	}
	      }else{
	      	showXHTML_tr_B('class="cssTrEvn font01"');
	      	showXHTML_td('colspan="2" align="center"', $MSG['no_data'][$sysSession->lang]);
	      	showXHTML_tr_E();
	      }
	      showXHTML_tr_B('class="cssTrHead font01"');
			showXHTML_td('valign="top" align="center"  colspan="2"', '<input type="button" name="btnClose" value="'.$MSG['btnClose'][$sysSession->lang].'" class="cssBtn" onclick="window.close();"');
	      showXHTML_tr_E('');
		  showXHTML_table_E();
	  showXHTML_tabFrame_E();
	showXHTML_body_E();
?>

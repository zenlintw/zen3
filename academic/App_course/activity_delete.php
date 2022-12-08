<?php
	/**
	 * 實際圖片預覽
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/app_course_manage.php');

	$activityIds = base64_decode(trim($_POST['activityIds']));
	
	$aryActivity = explode(',',$activityIds);
	
	showXHTML_head_B($MSG['title_manage'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');

	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['tab_delete_activity'][$sysSession->lang], 'tabs');

		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'mainFm', 'ListTable', '" style="display: inline;"');
			showXHTML_table_B('width="900" border="0" cellspacing="1" cellpadding="3" id="dataTb" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center"',$MSG['item_caption'][$sysSession->lang]);
					showXHTML_td('align="center"',$MSG['item_delete_result'][$sysSession->lang]);
				showXHTML_tr_E();
				for($i=0;$i<count($aryActivity);$i++) {
					$activityId = $aryActivity[$i];
					list($caption) = dbGetStSr('CO_activities','caption',"act_id={$activityId}",ADODB_FETCH_NUM);
					dbDel('CO_activities',"act_id={$activityId} limit 1");
					if($sysConn->Affected_Rows()>0) {
						$deleteMsg = $MSG['msg_delete_success'][$sysSession->lang];
					} else {
						$deleteMsg = $MSG['msg_delete_fail'][$sysSession->lang];
					}
					$cssTR = ($cssTR == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($cssTR);
						showXHTML_td('wrap',$caption);
						showXHTML_td('align="center"',$deleteMsg);
					showXHTML_tr_E();
				}
				// 活動重新排序 - Begin
				$rsActivities = dbGetStMr('CO_activities','act_id,permute','1 order by permute asc');
				if($rsActivities) {
					$i=1;
					while(!$rsActivities->EOF) {
						$actId = $rsActivities->fields['act_id'];
						$permute = $rsActivities->fields['permute'];
						$newPermute = min($i,$permute);
						dbSet('CO_activities',"permute={$newPermute}","act_id={$actId}");
						$i++;
						$rsActivities->MoveNext();
					}
					
				}
				// 活動重新排序 - End
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td_B('align="center" colspan="2"');
						showXHTML_input('button', '', $MSG['btn_back_list'][$sysSession->lang], '', 'class="button01" onclick="location.replace(\'activity_list.php\');"');
					showXHTML_td_E('');
				showXHTML_tr_E();
			showXHTML_table_E('');
		showXHTML_tabFrame_E();
		echo '</div>';

	showXHTML_body_E('');
?>

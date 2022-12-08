<?php
	/**
	 * 檢視教材
	 *
	 * 建立日期：2005/05/10
	 * @author  Jeff Wang
	 * @version $Id: content_property_view.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/content_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	
	$sysSession->cur_func='700500500';
	$sysSession->restore();

	if (!aclVerifyPermission(700500500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	// 修改教材
	$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket) {
		$title = $MSG['title_view'][$sysSession->lang];
		$RS = dbGetStSr('WM_content', '*', 'content_id=' . intval($_POST['content_id']), ADODB_FETCH_ASSOC);
		$lang = old_getCaption($RS['caption']);
		$usage = $RS['quota_used'];
		$limit = $RS['quota_limit'];
		$status = $RS['status'];
	}

	$js = <<< BOF

	function goList() {
		window.location.replace("content_package_manager.php");
	}
BOF;
	// 開始呈現 HTML
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');

		$ary[] = array($title, 'tabs');

		showXHTML_tabFrame_B($ary, 1, 'actForm', 'ListTable', 'style="display:inline;"');
			$ticket = md5($sysSession->ticket . $actType . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['td_title_no'][$sysSession->lang]);
					showXHTML_td('colspan="2"',$RS['content_sn']);
				showXHTML_tr_E('');
				
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['td_title_caption'][$sysSession->lang]);
					showXHTML_td_B('colspan="2"');
						$multi_lang = new Multi_lang(true, $lang);
						$multi_lang->show(false);
					showXHTML_td_E();
				showXHTML_tr_E();

				if($RS['content_type'] == 'digitization'){
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('align="right" valign="top"', $MSG['td_title_quota_mb'][$sysSession->lang]);
						showXHTML_td('colspan="2"', $limit.' KB');
					showXHTML_tr_E('');

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('align="right" valign="top"', $MSG['td_title_usage_mb'][$sysSession->lang]);
						showXHTML_td('colspan="2"', $usage . ' KB');
					showXHTML_tr_E('');
				}

				$type_list = ($RS['content_type'] == 'traditional')? $MSG['state_traditional'][$sysSession->lang] : $MSG['state_digital'][$sysSession->lang];
				$type_list1 = $RS['content_form'];

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['td_title_state'][$sysSession->lang]);
					showXHTML_td('',$type_list);
					showXHTML_td('',$type_list1);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['td_title_note'][$sysSession->lang]);
					showXHTML_td('colspan="2"',$RS['content_note']);
				showXHTML_tr_E('');

				if($RS['content_type'] == 'digitization'){
					$status_list = array(
						'disable' => $MSG['state_close'][$sysSession->lang],
						'readonly' => $MSG['state_read_only'][$sysSession->lang],
						'modifiable' => $MSG['state_read_write'][$sysSession->lang]
					);

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('align="right" valign="top"', $MSG['td_title_state0'][$sysSession->lang]);
						showXHTML_td('colspan="2"',$status_list[$status]);
					showXHTML_tr_E('');
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['btn_reutrn'][$sysSession->lang], '', 'onclick="goList();" class="cssBtn"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

			showXHTML_table_E('');
		showXHTML_tabFrame_E();
	showXHTML_body_E('');
?>

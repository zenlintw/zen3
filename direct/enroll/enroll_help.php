<?php
	/**
	 * 修課指派
	 *
	 * @since   2004/07/27
	 * @author  ShenTing Lin
	 * @version $Id: enroll_help.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/direct/enroll/enroll_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1100300100';
	$sysSession->restore();
	if (!aclVerifyPermission(1100300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 初始化資料記錄的物件
	initAssoc();
	$objAssoc->store();

	$js = <<< BOF
	function go() {
		window.location.replace("member_list.php");
	}
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_enroll_help'][$sysSession->lang], 'tabs1');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1); // , form_id, table_id, form_extra, isDragable);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B();
						$ary = array(
							array($MSG['msg_step_11'][$sysSession->lang], 'help'  , 1),
							array($MSG['msg_step_2'][$sysSession->lang] , 'member', 1, 'go();'),
							array($MSG['msg_step_3'][$sysSession->lang] , 'course', 0),
							array($MSG['msg_step_4'][$sysSession->lang] , 'review', 0),
							array($MSG['msg_step_5'][$sysSession->lang] , 'result', 0)
						);
						showStep($ary, 'help');
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('', $MSG['enroll_help'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="center"');
						showXHTML_input('button', '', $MSG['btn_begin_assign'][$sysSession->lang], '', 'class="cssBtn" onclick="go();"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

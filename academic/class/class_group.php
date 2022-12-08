<?php
	/**
	 * 班級群組管理
	 *
	 * 建立日期：2002/10/07
	 * @author  ShenTing Lin
	 * @version $Id: class_group.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/class_group.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');

	$sysSession->cur_func = '2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{
	}

	$lang   = strtolower($sysSession->lang);
	$create = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Create' . $sysSession->username);
	$edit   = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);

	$js     = <<< EOF

	var create = "{$create}";
	var edit = "{$edit}";

	var theme = "{$sysSession->theme}";
	var school_name      = "{$MSG['school'][$sysSession->lang]}{$sysSession->school_name}";
	var MSG_HELP         = "{$MSG['msg_help'][$sysSession->lang]}";
	var MSG_NEW_GROUP    = "{$MSG['msg_new_group'][$sysSession->lang]}";
	var MSG_NOT_EDIT     = "{$MSG['msg_not_modify'][$sysSession->lang]}";
	var MSG_SEL_MODIFY   = "{$MSG['msg_select_modify'][$sysSession->lang]}";
	var MSG_SYS_ERROR    = "{$MSG['msg_system_error'][$sysSession->lang]}";
	var MSG_NOT_DELETE   = "{$MSG['msg_not_delete'][$sysSession->lang]}";
	var MSG_SEL_DELETE   = "{$MSG['msg_select_delete'][$sysSession->lang]}";
	var MSG_INCLUDE_B    = "{$MSG['msg_include_b'][$sysSession->lang]}"; // msg08
	var MSG_INCLUDE_E    = "{$MSG['msg_include_e'][$sysSession->lang]}"; // msg09
	var MSG_NOT_MOVE     = "{$MSG['msg_not_move'][$sysSession->lang]}";
	var MSG_SEL_COPY_MOVE     = "{$MSG['msg_select_copy_move'][$sysSession->lang]}";
	var MSG_SEL_CUT_MOVE     = "{$MSG['msg_select_cut_move'][$sysSession->lang]}";
	var MSG_SEL_REMOVE_MOVE     = "{$MSG['msg_select_remove_move'][$sysSession->lang]}";
	var MSG_MOVE_LAST    = "{$MSG['msg_move_last'][$sysSession->lang]}";
	var MSG_MOVE_FIRST   = "{$MSG['msg_move_first'][$sysSession->lang]}";
	var MSG_NO_MOVE      = "{$MSG['msg_no_move'][$sysSession->lang]}";
	var MSG_SAME_SOURCE  = "{$MSG['msg_same_source'][$sysSession->lang]}";
	var MSG_TARGET_CHILD = "{$MSG['msg_target_child'][$sysSession->lang]}";
	var MSG_NEED_LIB     = "{$MSG['msg_need_lib'][$sysSession->lang]}";
	var MSG_FILL_TITLE   = "{$MSG['msg_fill_title'][$sysSession->lang]}";
	var MSG_MV_UP_B      = "{$MSG['msg_move_up_b'][$sysSession->lang]}";
	var MSG_MV_UP_E      = "{$MSG['msg_move_up_e'][$sysSession->lang]}";
	var MSG_MV_DOWN_B    = "{$MSG['msg_move_down_b'][$sysSession->lang]}";
	var MSG_MV_DOWN_E    = "{$MSG['msg_move_down_e'][$sysSession->lang]}";
	var MSG_MV_LEFT_B    = "{$MSG['msg_move_left_b'][$sysSession->lang]}";
	var MSG_MV_LEFT_E    = "{$MSG['msg_move_left_e'][$sysSession->lang]}";
	var MSG_MV_RIGHT_B   = "{$MSG['msg_move_right_b'][$sysSession->lang]}";
	var MSG_MV_RIGHT_E   = "{$MSG['msg_move_right_e'][$sysSession->lang]}";
	var MSG_CONFIRM_DEL  = "{$MSG['msg_confirm_delete'][$sysSession->lang]}";
	var MSG_SAVE_SUCCESS = "{$MSG['msg_save_success'][$sysSession->lang]}";
	var MSG_SAVE_FAIL    = "{$MSG['msg_save_fail'][$sysSession->lang]}";
	var MSG_EXIT    	 = "{$MSG['msg_need_save'][$sysSession->lang]}";
	var MSG_DEL_FAIL     = "{$MSG['title80'][$sysSession->lang]}";
	var MSG_SEL_MOVE     = "{$MSG['msg_select_move'][$sysSession->lang]}";

	var MSG_title   = "{$MSG['title'][$sysSession->lang]}";
	var MSG_title2  = "{$MSG['title2'][$sysSession->lang]}";
	var MSG_title3  = "{$MSG['title3'][$sysSession->lang]}";
	var MSG_title4  = "{$MSG['title4'][$sysSession->lang]}";
	var MSG_title5  = "{$MSG['title5'][$sysSession->lang]}";
	var MSG_title6  = "{$MSG['title6'][$sysSession->lang]}";
	var MSG_title7  = "{$MSG['title7'][$sysSession->lang]}";
	var MSG_title8  = "{$MSG['title8'][$sysSession->lang]}";
	var MSG_title81 = "{$MSG['title81'][$sysSession->lang]}";
	var MSG_title84 = "{$MSG['title84'][$sysSession->lang]}";

	var lang = "{$lang}";

	var MSG_class_error = "{$MSG['msg_class_error'][$sysSession->lang]}";
	var MSG_cur_error 	= "{$MSG['error_cut_msg'][$sysSession->lang]}";
	var MSG_cur_error2 	= "{$MSG['error_cut_msg2'][$sysSession->lang]}";
	var cut_classes = '';
	var st_id = '{$sysSession->cur_func}';

EOF;
	showXHTML_head_B($MSG['title_cs_group'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/filter_spec_char.js');
	showXHTML_script('inline', $js);
	showXHTML_script('include', 'class_group.js');
	$xajax_save_temp->printJavascript('/lib/xajax/');
	showXHTML_head_E('');

	showXHTML_body_B('');
		// 顯示課程群組
		$ary = array();
		$ary[] = array($MSG['tabs_group_set'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'CGroup', null, 'style="display:inline;"');
			showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center" class="font01"', $MSG['loading'][$sysSession->lang]);
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_tabFrame_E();

		// 修改班級名稱  begin
		$ary = array();
		$ary[] = array($MSG['tabs_set_group_name'][$sysSession->lang], 'divSettings');

		showXHTML_tabFrame_B($ary, 1, 'fmSetting', 'divSettings', 'method="post" action="class_group_class_save.php" style="display:inline;"', true);
			showXHTML_input('hidden', 'class_id', '', '', 'id="class_id" class="cssInput"');

			showXHTML_input('hidden', 'ticket', '', '', '');
			showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" class="box01"');
				$arr_names = array('Big5'		=>	'GPName_big5',
									'GB2312'		=>	'GPName_gb2312',
									'en'			=>	'GPName_en',
									'EUC-JP'		=>	'GPName_euc-jp',
									'user_define'=>	'GPName_user-define'
									);
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('nowrap class="font01"', $MSG['title_group_name'][$sysSession->lang]);
					showXHTML_td_B('nowrap colspan="2"');
						$multi_lang = new Multi_lang(false, '', 'class="cssTrEvn"'); // 多語系輸入框
						$multi_lang->show(true, $arr_names);
					showXHTML_td_E();
				showXHTML_tr_E();

				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td('nowrap class="font01"', $MSG['title60'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('text', 'dep_id', '', '', 'id="dep_id" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('nowrap class="font01"', $MSG['title9'][$sysSession->lang]);
				showXHTML_tr_E();

				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('nowrap class="font01"', $MSG['title8'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('text', 'director', '', '', 'id="director" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('nowrap class="font01"', $MSG['title9'][$sysSession->lang]);
				showXHTML_tr_E();

				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td('nowrap class="font01"', $MSG['people_limit'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('text', 'people_limit', '0', '', 'id="people_limit" size="20" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('nowrap class="font01"', $MSG['title85'][$sysSession->lang]);
				showXHTML_tr_E();

				$limit = getDefaultQuota();

				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('nowrap class="font01"', $MSG['quota_limit'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('text', 'quota_limit', $limit, '', 'id="quota_limit" size="20" class="cssInput"');
						echo '&nbsp;KB';
					showXHTML_td_E();
					showXHTML_td('');
				showXHTML_tr_E();

				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B('colspan="3" align="center"');
						showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="checkData();"');
						showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="actionLayer(\'divSettings\', false)"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

			showXHTML_table_E('');
		showXHTML_tabFrame_E();
		// 修改班級名稱  end

	echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
	showXHTML_body_E('');
?>

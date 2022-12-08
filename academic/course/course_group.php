<?php
	/**
	 * 課程群組管理
	 *
	 * 建立日期：2002/10/07
	 * @author  ShenTing Lin
	 * @version $Id: course_group.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/course_group.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func = '700300400';
	$sysSession->restore();

	if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$lang = strtolower($sysSession->lang);
	$js = <<< EOF
	var theme = "/theme/{$sysSession->theme}/{$sysSession->env}/";

	var school_name      = "{$MSG['school'][$sysSession->lang]}{$sysSession->school_name}";
	var MSG_HELP         = "{$MSG['msg_help'][$sysSession->lang]}";
	var MSG_NEW_GROUP    = "{$MSG['msg_new_group'][$sysSession->lang]}";
	var MSG_NOT_EDIT     = "{$MSG['msg_not_modify'][$sysSession->lang]}";
	var MSG_SEL_MODIFY   = "{$MSG['msg_select_modify'][$sysSession->lang]}";
	var MSG_SYS_ERROR    = "{$MSG['msg_system_error'][$sysSession->lang]}";
	var MSG_NOT_DELETE   = "{$MSG['msg_not_delete'][$sysSession->lang]}";
	var MSG_SEL_DELETE   = "{$MSG['msg_select_delete'][$sysSession->lang]}";
	var MSG_INCLUDE_B    = "{$MSG['msg_include_b'][$sysSession->lang]}";
	var MSG_INCLUDE_E    = "{$MSG['msg_include_e'][$sysSession->lang]}";
	var MSG_NOT_MOVE     = "{$MSG['msg_not_move'][$sysSession->lang]}";
	var MSG_SEL_MOVE     = "{$MSG['msg_select_move'][$sysSession->lang]}";
	var MSG_SEL_ACTION   = "{$MSG['msg_select_action'][$sysSession->lang]}";
	var MSG_NEED_ACTION  = "{$MSG['msg_need_action'][$sysSession->lang]}";
	var MSG_SEL_PASTE    = "{$MSG['msg_select_paste'][$sysSession->lang]}";
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

	var MSG_TITLE_ERROR  = "{$MSG['msg_title_style_error'][$sysSession->lang]}";
	var lang = "{$lang}";
	var st_id = '{$sysSession->cur_func}';
EOF;
	showXHTML_head_B($MSG['title_cs_group'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/filter_spec_char.js');
	showXHTML_script('inline', $js);
	showXHTML_script('include', 'course_group.js');
	$xajax_save_temp->printJavascript('/lib/xajax/');
	showXHTML_head_E('');

	showXHTML_body_B('');
		// 顯示課程群組
		$ary = array();
		$ary[] = array($MSG['tabs_group_set'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'CGroup', null, 'style="display:inline;"');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center"', $MSG['loading'][$sysSession->lang]);
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_tabFrame_E();

		// 設定課程群組名稱
		$ary = array();
		$ary[] = array($MSG['tabs_set_group_name'][$sysSession->lang], 'divSettings');
		showXHTML_tabFrame_B($ary, 1, 'fmSetting', 'divSettings', 'style="display:inline;"', true);
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				// 語系 (Begin)
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$arr_names = array( 'Big5'		    =>	'GPName_big5',
				                    'GB2312'		=>	'GPName_gb2312',
				                    'en'			=>	'GPName_en',
				                    'EUC-JP'		=>	'GPName_euc-jp',
				                    'user_define'   =>	'GPName_user-define'
				        );
				showXHTML_tr_B('class="cssTrEvn"');
				    showXHTML_td('align="right" valign="center"', $MSG['title_group_name'][$sysSession->lang]);
				    showXHTML_td_B('');
				        $multi_lang = new Multi_lang(false, '', 'class="cssTrEvn"'); // 多語系輸入框
				        $multi_lang->show(true, $arr_names);
				    showXHTML_td_E();
				showXHTML_tr_E();

				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B('colspan="2" align="center" nowrap');
						showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="editNode();"');
						showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="actionLayer(\'divSettings\', false)"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_tabFrame_E();

    echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
	showXHTML_body_E('');
?>

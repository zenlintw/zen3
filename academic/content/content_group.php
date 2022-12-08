<?php
	/**
	 * 教材類別維護
	 *
	 * 建立日期：2005/04/01
	 * @author  Jeff wang
	 * @version $Id: content_group.php,v 1.1 2010/02/24 02:38:16 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/content_lang.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{
	}

	$create = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Create' . $sysSession->username);
	$edit   = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit'   . $sysSession->username);
	$lang   = strtolower($sysSession->lang);

	$js = <<< EOF

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
	var MSG_SEL_COPY_MOVE    = "{$MSG['msg_select_copy_move'][$sysSession->lang]}";
	var MSG_SEL_CUT_MOVE     = "{$MSG['msg_select_cut_move'][$sysSession->lang]}";
	var MSG_SEL_REMOVE_MOVE  = "{$MSG['msg_select_remove_move'][$sysSession->lang]}";
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

	var st_id = '{$sysSession->cur_func}';
EOF;
	showXHTML_head_B($MSG['title_cs_group'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/filter_spec_char.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline' , $js);
	showXHTML_script('include', 'content_group.js');
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

		// 修改教材類別名稱  begin
		$ary = array();
		$ary[] = array($MSG['tabs_set_group_name'][$sysSession->lang], 'divSettings');

	    showXHTML_tabFrame_B($ary, 1, 'fmSetting', 'divSettings', 'method="post" action="content_group_save.php" style="display:inline;" onSubmit="return checkData();" ', true,'',true);
	        showXHTML_input('hidden', 'content_id', '', '', 'id="content_id" class="cssInput"');

			showXHTML_input('hidden', 'ticket', '', '', '');
			showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" class="box01"');

				$arr_names = array( 'Big5'		    =>	'GPName_big5',
				                    'GB2312'		=>	'GPName_gb2312',
				                    'en'			=>	'GPName_en',
				                    'EUC-JP'		=>	'GPName_euc-jp',
				                    'user_define'   =>	'GPName_user-define'
				                );
				showXHTML_tr_B('class="cssTrEvn"');
				    showXHTML_td('align="right" valign="center"', $MSG['title_group_name'][$sysSession->lang]);
                    showXHTML_td_B('');
				        $multi_lang1 = new Multi_lang(false, '', 'class="cssTrEvn"'); // 多語系輸入框
				        $multi_lang1->show(true, $arr_names);
				    showXHTML_td_E();
				showXHTML_tr_E();

				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B('colspan="2" align="center"');
					    showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="editNode();"');
						showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="actionLayer(\'divSettings\', false)"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_tabFrame_E();
        // 修改教材類別名稱  end

    echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
	showXHTML_body_E('');
?>

<?php
	/**
	 * 管理訊息中心的目錄
	 *
	 * 建立日期：2003//
	 * @author  ShenTing Lin
	 * @version $Id: manage_folder.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/mycourse.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if (!aclVerifyPermission(2200200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->ticket . $sysSession->school_id);
	$js = <<< EOF
	var theme = "{$sysSession->theme}";
	var lang = "{$sysSession->lang}";
	var ticket = "{$ticket}";

	var MSG_TITLE         = "{$MSG['title_favorite'][$sysSession->lang]}";
	var MSG_HELP          = "{$MSG['msg_help_manage'][$sysSession->lang]}";
	var MSG_SYS_ERROR     = "{$MSG['msg_system_error'][$sysSession->lang]}";
	var MSG_NEW_FOLDER    = "{$MSG['msg_new_folder'][$sysSession->lang]}";
	var MSG_CANT_MOVE     = "{$MSG['msg_cant_move'][$sysSession->lang]}";
	var MSG_SEL_MOVE_NODE = "{$MSG['msg_sel_move'][$sysSession->lang]}";
	var MSG_CANT_DEL      = "{$MSG['msg_cant_del'][$sysSession->lang]}";
	var MSG_SYS_CANT_DEL  = "{$MSG['msg_sys_not_del'][$sysSession->lang]}";
	var MSG_SEL_DEL_NODE  = "{$MSG['msg_sel_del'][$sysSession->lang]}";
	var MSG_SEL_CP_NODE   = "{$MSG['msg_sel_copy'][$sysSession->lang]}";
	var MSG_SEL_CUT_NODE  = "{$MSG['msg_sel_cut'][$sysSession->lang]}";
	var MSG_SEL_PSE_NODE  = "{$MSG['msg_sel_paste'][$sysSession->lang]}";
	var MSG_CLIP_EMPTY    = "{$MSG['msg_need_cut_copy'][$sysSession->lang]}";
	var MSG_CANT_EDIT     = "{$MSG['msg_cant_edit'][$sysSession->lang]}";
	var MSG_SEL_EDIT_NODE = "{$MSG['msg_del_edit'][$sysSession->lang]}";
	var MSG_CONFIRM_DEL   = "{$MSG['msg_sure_del'][$sysSession->lang]}";
	var MSG_CONFIRM_SAVE  = "{$MSG['msg_need_save'][$sysSession->lang]}";
	var MSG_SAVE_SUCCESS  = "{$MSG['msg_save_success'][$sysSession->lang]}";
	var MSG_SAVE_FAIL     = "{$MSG['msg_save_fail'][$sysSession->lang]}";
	var MSG_EXPAND        = "{$MSG['msg_expend'][$sysSession->lang]}";
	var MSG_COLLECT       = "{$MSG['msg_collect'][$sysSession->lang]}";
	var MSG_ALREADY_CUT   = "{$MSG['msg_already_cut'][$sysSession->lang]}";

	var MSG_FOLDER        = "{$MSG['msg_folder'][$sysSession->lang]}";
	var MSG_CANT_INDENT   = "{$MSG['msg_cant_right'][$sysSession->lang]}";
	var MSG_CANT_DEINDENT = "{$MSG['msg_cant_left'][$sysSession->lang]}";
	var MSG_CANT_BACKWARD = "{$MSG['msg_cant_down'][$sysSession->lang]}";
	var MSG_CANT_FORWARD  = "{$MSG['msg_cant_up'][$sysSession->lang]}";
	var MSG_FILL_TITLE	  = "{$MSG['msg_fill_title'][$sysSession->lang]}";

	window.onload = function () {
		var obj = null;
		xmlHttp = XmlHttp.create();
		xmlVars = XmlDocument.create();
		obj = getTarget();
		if (obj != null) obj.location.replace("manage_tools.php");
		do_func("manage_folder", "");
	};

	window.onunload = function () {
		var obj = null;
		if (isEdit && confirm(MSG_CONFIRM_SAVE)) {
			do_func("save", "");
		}
		isEdit = false;
		obj = getTarget();
		if (obj != null) obj.location.replace("about:blank");
	};
EOF;
	showXHTML_head_B($MSG['title_manage_favorite'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', './manage_folder.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');
		// 顯示課程群組
		showXHTML_table_B('width="760" align="center" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['tabs_manage_favorite'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="Folder"');
					showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('align="center"', $MSG['msg_loading'][$sysSession->lang]);
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		// 設定資料夾名稱
		$style = 'style="position: absolute; left: 200px; top: 20px; visibility: hidden;" onMouseDown="dragLayer(\'divSettings\', 0, 0, 200, 30)"';
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="divSettings" ' . $style);
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['tabs_set_folder'][$sysSession->lang], 'divSettings');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup"');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                        // 語系 (Begin)
				        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				        $arr_names = array('Big5'		=>	'GPName_big5',
								           'GB2312'		=>	'GPName_gb2312',
								           'en'			=>	'GPName_en',
								           'EUC-JP'		=>	'GPName_euc-jp',
								           'user_define'=>	'GPName_user-define'
								            );
				        showXHTML_tr_B($col);
					       showXHTML_td('align="right" valign="center"', $MSG['td_folder_name'][$sysSession->lang]);
					       showXHTML_td_B('');
						      $multi_lang = new Multi_lang(false, '', $col); // 多語系輸入框
						      $multi_lang->show(true, $arr_names);
					       showXHTML_td_E();
				        showXHTML_tr_E();

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B('colspan="2" align="center" nowrap');
								showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="editNode();actionLayer(\'divSettings\', false);"');
								showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="actionLayer(\'divSettings\', false)"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

	showXHTML_form_B('action="course_group_save.php" method="post"', 'saveFm');
		showXHTML_input('hidden', 'xmldata', '', '', '');
	showXHTML_form_E('');

	showXHTML_body_E('');
?>
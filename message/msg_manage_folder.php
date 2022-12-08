<?php
	/**
	 * 管理訊息中心的目錄
	 *
	 * 建立日期：2003//
	 * @author  ShenTing Lin
	 * @version $Id: msg_manage_folder.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200100200';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200100200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($sysSession->cur_func == $msgFuncID['notebook']) {
		$title  = $MSG['tabs_notebook_title'][$sysSession->lang];
		$tabs   = $MSG['tabs_set_nb_folder'][$sysSession->lang];
		$target = 'notebook.php';
		$func   = 'man_nb_folder';
		$isNB   = 'true';
	} else {
		$title  = $MSG['title'][$sysSession->lang];
		$tabs   = $MSG['tabs_set_folder'][$sysSession->lang];
		$target = 'index.php';
		$func   = 'manage_folder';
		$isNB   = 'false';
	}

	$ticket = md5($sysSession->username . 'Message' . $sysSession->ticket . $sysSession->school_id);
	$lang = strtolower($sysSession->lang);
	$js = <<< EOF
	var theme   = "{$sysSession->theme}/{$sysSession->env}";
	var lang    = "{$lang}";
	var ticket  = "{$ticket}";
	var targetf = "{$target}";
	var isNB    = {$isNB};

	var MSG_TITLE         = "{$MSG['title'][$sysSession->lang]}";
	var MSG_HELP          = "{$MSG['mage_help'][$sysSession->lang]}";
	var MSG_SYS_ERROR     = "{$MSG['mage_sys_error'][$sysSession->lang]}";
	var MSG_NEW_FOLDER    = "{$MSG['mage_new_folder'][$sysSession->lang]}";
	var MSG_CANT_MOVE     = "{$MSG['mage_not_move'][$sysSession->lang]}";
	var MSG_SEL_MOVE_NODE = "{$MSG['mage_sel_move'][$sysSession->lang]}";
	var MSG_CANT_DEL      = "{$MSG['mage_not_del'][$sysSession->lang]}";
	var MSG_SYS_CANT_DEL  = "{$MSG['mage_sys_folder'][$sysSession->lang]}";
	var MSG_SEL_DEL_NODE  = "{$MSG['mage_sel_del'][$sysSession->lang]}";
	var MSG_SEL_CP_NODE   = "{$MSG['mage_sel_copy'][$sysSession->lang]}";
	var MSG_SEL_CUT_NODE  = "{$MSG['mage_sel_cut'][$sysSession->lang]}";
	var MSG_SEL_PSE_NODE  = "{$MSG['mage_sel_post'][$sysSession->lang]}";
	var MSG_CLIP_EMPTY    = "{$MSG['mage_clip_empty'][$sysSession->lang]}";
	var MSG_CANT_EDIT     = "{$MSG['mage_not_edit'][$sysSession->lang]}";
	var MSG_SEL_EDIT_NODE = "{$MSG['mage_sel_edit'][$sysSession->lang]}";
	var MSG_CONFIRM_DEL   = "{$MSG['mage_confirm_del'][$sysSession->lang]}";
	var MSG_EXIT  		  = "{$MSG['mage_confirm_save'][$sysSession->lang]}";
	var MSG_SAVE_SUCCESS  = "{$MSG['mage_save_succ'][$sysSession->lang]}";
	var MSG_SAVE_FAIL     = "{$MSG['mage_save_fail'][$sysSession->lang]}";
	var MSG_EXPAND        = "{$MSG['mage_expand'][$sysSession->lang]}";
	var MSG_COLLECT       = "{$MSG['mage_collect'][$sysSession->lang]}";

	var MSG_FOLDER        = "{$MSG['mage_folder'][$sysSession->lang]}";
	var MSG_CANT_INDENT   = "{$MSG['mage_not_right'][$sysSession->lang]}";
	var MSG_CANT_DEINDENT = "{$MSG['mage_not_left'][$sysSession->lang]}";
	var MSG_CANT_BACKWARD = "{$MSG['mage_not_down'][$sysSession->lang]}";
	var MSG_CANT_FORWARD  = "{$MSG['mage_not_up'][$sysSession->lang]}";
	var MSG_FILL_TITLE	  = "{$MSG['msg_fill_title'][$sysSession->lang]}";
	
	window.onload = function () {
		var obj = null;
		xmlHttp = XmlHttp.create();
		xmlVars = XmlDocument.create();
		obj = getTarget();
		if (obj != null) obj.location.replace("msg_manage_tools.php");
		do_func("{$func}", "");
	};

	window.onunload = function () {
		obj = getTarget();
		if (obj != null) obj.location.replace("about:blank");
	};

	window.onbeforeunload=function() {
		if (notSave) return MSG_EXIT;
	};
EOF;
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('inline', $js);
	showXHTML_script('include', 'lib.js');
	showXHTML_head_E('');

	showXHTML_body_B('');
		// 顯示課程群組
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($tabs, 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="Folder"');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrHelp"');
							showXHTML_td('align="center"', $MSG['loading'][$sysSession->lang]);
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
					$ary[] = array($MSG['mage_set_folder_name'][$sysSession->lang], 'divSettings');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup"');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						$arr_names = array('Big5'		=>	'GPName_big5',
								   		   'GB2312'		=>	'GPName_gb2312',
								   		   'en'			=>	'GPName_en',
								   		   'EUC-JP'		=>	'GPName_euc-jp',
								   		   'user_define'=>	'GPName_user-define'
								   );
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('nowrap', $MSG['mage_folder_name'][$sysSession->lang]);
							showXHTML_td_B('');
								$multi_lang = new Multi_lang(false, '', 'class="cssTrEvn"'); // 多語系輸入框
								$multi_lang->show(true, $arr_names);
							showXHTML_td_E();
						showXHTML_tr_E();

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B('colspan="2" align="center" nowrap');
								showXHTML_input('button', '', $MSG['ok'][$sysSession->lang], '', 'class="cssBtn" onclick="editNode();actionLayer(\'divSettings\', false);"');
								showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="actionLayer(\'divSettings\', false)"');
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
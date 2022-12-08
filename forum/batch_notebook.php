<?php
	/**
	 * 收入筆記本
	 *
	 * 建立日期：2004/7/5
	 * @author  KuoYang Tsao
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/forum/lib_nb_dir.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '900201000';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$lang      = strtolower($sysSession->lang);
	$folder_id = nb_getFolderId();
	$title     = $MSG['tabs_notebook_title'][$sysSession->lang];

	$js = <<< EOF
	var theme     = "{$sysSession->theme}";
	var lang      = "{$lang}";
	var folder_id = "{$folder_id}";
	var targetf   = "";
	var isNB      = true;
	var bodyHeight = 0, bodyWidth = 0;
	var obj = window.scrollbars;
	if ((typeof(obj) == "object") && (obj.visible == true)) {
		obj.visible = false;
	}

	var MSG_TITLE     = "{$MSG['title'][$sysSession->lang]}";
	var MSG_HELP      = "{$MSG['mage_help'][$sysSession->lang]}";
	var MSG_SYS_ERROR = "{$MSG['mage_sys_error'][$sysSession->lang]}";
	var MSG_EXPAND    = "{$MSG['mage_expand'][$sysSession->lang]}";
	var MSG_COLLECT   = "{$MSG['mage_collect'][$sysSession->lang]}";
	var MSG_CHOOSE    = "{$MSG['select'][$sysSession->lang]}";
	var MSG_FOLDER    = "{$MSG['tabs_notebook_title'][$sysSession->lang]} ";
 // /////////////////////////////////////////////////////////////////////////////
	function getSelFolder() {
		var nodes = null;
		nodes = document.getElementsByTagName("input");
		if ((typeof(nodes) != "object") || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type == "radio") && nodes[i].checked) {
				return nodes[i].value;
			}
		}
		return '';
	}

	window.onload = function () {
		/* document.body.scroll = "no"; */
		chkBrowser();
		xmlHttp = XmlHttp.create();
		xmlVars = XmlDocument.create();
		do_func("list_folder", "");
	};

EOF;
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('inline', $js);
	showXHTML_script('include', 'lib_notebook.js');
	showXHTML_head_E();
	showXHTML_body_B('class="cssTbBodyBg" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0"');

	showXHTML_table_B('width="106%" border="0" cellspacing="0" cellpadding="0" class="cssTable"');
		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td_B('nowrap class="cssTbTd"');
				echo $MSG['note1'][$sysSession->lang] , "<BR>\r\n",
				     $MSG['note2'][$sysSession->lang] , "<BR>\r\n";
			showXHTML_td_E();
		showXHTML_tr_E();
		showXHTML_tr_B();
			showXHTML_td_B();
				showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" class="cssTable"');
					showXHTML_tr_B('class="cssTrEvn"');
						// 版面問題，所以自己輸出
						echo '<td width="3" valign="top" nowrap><img src="/theme/'     , $sysSession->theme , '/academic/cl2.gif" width="3" height="3" border="0"></td>',
						     '<td align="right" valign="top" nowrap><img src="/theme/' , $sysSession->theme , '/academic/cl3.gif" width="3" height="3" border="0"></td>';
					showXHTML_tr_E();

					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td_B('colspan="2" nowrap="nowrap"');
							echo '&nbsp;<img src="/theme/' , $sysSession->theme , '/academic/icon_book.gif" width="22" height="12" border="0" align="absmiddle">',
							     '<a href="javascript:void(null);" onClick="chooseFolder(\'sys_notebook\',\'', $title, '\'); return false;" MyAttr="sys_notebook" class="cssTbHead">',
							     $title,
							     '</a>';
							showXHTML_input('button','',$MSG['close_window'][$sysSession->lang],'',"id='btnClose' onClick='window.close();' class='cssBtn'");
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
			showXHTML_td_E();
		showXHTML_tr_E();
		showXHTML_tr_B();
			showXHTML_td_B();
				showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" class="cssTable"');
					showXHTML_tr_B('style="cursor: default;" class="cssTrEvn"');
						showXHTML_td_B('colspan="2" class="cssTbTd" nowrap id="Folder"');
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
			showXHTML_td_E();
		showXHTML_tr_E();
	showXHTML_table_E();

	showXHTML_body_E();
?>

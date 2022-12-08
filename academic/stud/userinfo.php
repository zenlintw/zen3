<?php
	/**
	 * 顯示使用者資料
	 * @version:$Id: userinfo.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/people_manager.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission(400400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ACADEMIC_AUTH_MEMBER = true;

	$js = <<< EOF
	function picReSize() {
		var orgW = 0, orgH = 0;
		var demagnify = 0;
		var node = document.getElementById("MyPic");

		if ((typeof(node) != "object") || (node == null)) return false;
		orgW = parseInt(node.width);
		orgH = parseInt(node.height);
		if ((orgW > 110) || (orgH > 120)) {
			demagnify = (((orgW / 110) > (orgH / 120)) ? parseInt(orgW / 110) : parseInt(orgH / 120)) + 1;
			node.width  = parseInt(orgW / demagnify);
		node.height = parseInt(orgH / demagnify);
		}
		node.parentNode.style.height = node.height + 3;
	}

	function go_list() {
		window.location.replace('/academic/stud/stud_authorisation.php');
	}
EOF;

		showXHTML_head_B($MSG['profile'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_script('inline', $js);
		showXHTML_head_E();
		showXHTML_body_B();
		$arry[] = array($MSG['title81'][$sysSession->lang], 'addTable1');

		showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0" align="left"');
			// ========== 顯示 tab 的標記 ==========
			showXHTML_tr_B();
				showXHTML_td_B();
					showXHTML_tabs($arry, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			// ========== 顯示 tab 的標記 ==========

			showXHTML_tr_B();
				showXHTML_td_B('valign="top" ');
					showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0" align="left" id="addTable1" style="display:block"');
						showXHTML_tr_B();
							showXHTML_td_B();
								require_once(sysDocumentRoot . '/academic/stud/modify_stud_info_auth.php');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();

?>
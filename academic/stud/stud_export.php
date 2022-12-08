<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: amm lee                                                         *
	 *		Creation  : 2004/01/08                                                            *
	 *		work for  : 匯出人員資料 (第一步驟)                                                                      *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.4                                *
	 *      $Id: stud_export.php,v 1.1 2010/02/24 02:38:44 saly Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/stud_export.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400200';
	$sysSession->restore();
	if (!aclVerifyPermission(400400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$js = <<< BOF

	function go_where()
	{
		var action_flow = 0;
		var obj = document.getElementsByTagName('input');
		for(var i = 0; i < obj.length; i++)
		{
			if (obj[i].type.toLowerCase() == 'radio')
			{
				if ((obj[i].name == 'action_flow') && (obj[i].checked))
				{
					action_flow = parseInt(obj[i].value);
				}
			}
		}
		if (action_flow)
		{
			window.location.href = '/academic/stud/class_group.php';
		}
		else
		{
			window.location.href = '/academic/stud/course_set.php';
		}
	}
BOF;

	// 開始呈現 HTML
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();

	showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
		showXHTML_tr_B();
			showXHTML_td_B();
				$ary[] = array($MSG['title'][$sysSession->lang], 'tabs');
				showXHTML_tabs($ary, 1);
			showXHTML_td_E();
		showXHTML_tr_E();

		showXHTML_tr_B();
			showXHTML_td_B('valign="top" id="CGroup" ');
				showXHTML_table_B('id ="mainTable" width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

					$col = 'cssTrEvn';
					showXHTML_tr_B('class=" ' . $col . '"');
						showXHTML_td('', $MSG['title2'][$sysSession->lang]);
					showXHTML_tr_E();

					$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
					showXHTML_tr_B('class=" ' . $col . '"');
						showXHTML_td_B();
							$array_role[0]  = $MSG['title3'][$sysSession->lang];
							$array_role[1]  = $MSG['title4'][$sysSession->lang];
							showXHTML_input('radio', 'action_flow', $array_role, '', '','<p>');
						showXHTML_td_E();
					showXHTML_tr_E();

					$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
					showXHTML_tr_B('class=" ' . $col . '"');
						showXHTML_td_B('align="center"');
							showXHTML_input('button', '', $MSG['title5'][$sysSession->lang], '', 'onclick="go_where();" class="cssBtn"');
						showXHTML_td_E();
					showXHTML_tr_E();

				showXHTML_table_E();
			showXHTML_td_E();
		showXHTML_tr_E();
	showXHTML_table_E();

	showXHTML_body_E();
?>

<?php
	/**
	 * 學校統計資料 - 教材閱讀統計 (course)
	 *
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: cour_material_statistics.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket = md5(sysTicketSeed . $sysSession->username . 'cour_material_stat' . $sysSession->ticket);

	$js = <<< EOF
	var theme = "{$sysSession->theme}";
	var ticket = "{$ticket}";
	var lang = "{$lang}";

	function check_data(){
		var obj = document.getElementById('queryFm');
		obj.action = 'cour_material_statistics1.php';
		window.onunload = function () {};
		obj.submit();
	}

EOF;

	showXHTML_head_B($MSG['title4'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('width="500" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title101'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
				showXHTML_form_B('action="" method="post" enctype="multipart/form-data" target="_self" style="display:inline"', 'queryFm');
					showXHTML_table_B('id ="mainTable" width="500" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_input('hidden', 'ticket', $ticket, '', '');
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('width="120"', $MSG['title103'][$sysSession->lang]);
							showXHTML_td_B('width="480"');
								showXHTML_input('text', 'tea_query', $MSG['title105'][$sysSession->lang], '', 'id="tea_query" class="cssInput" onclick="this.value=\'\'"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('width="120"', $MSG['title104'][$sysSession->lang]);
							showXHTML_td_B('width="480"');
								showXHTML_input('text', 'cour_query', $MSG['title105'][$sysSession->lang], '', 'id="cour_query" class="cssInput" onclick="this.value=\'\'"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('width="120"', $MSG['title106'][$sysSession->lang]);
							showXHTML_td_B('width="480"');
								$page_array = array(10 => $MSG['title156'][$sysSession->lang],20 => 20,30 => 30,40 => 40,50 => 50,100 => 100);
								showXHTML_input('select', 'page_num', $page_array,10, 'class="cssInput" id="page_num" ');
								echo $MSG['title157'][$sysSession->lang];
							showXHTML_td_E('');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="2" align="center"');
								showXHTML_input('button','btnImp',$MSG['title11'][$sysSession->lang],'','onclick="check_data()"');
								showXHTML_input('reset','btnImp',$MSG['title22'][$sysSession->lang],'','');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
					showXHTML_form_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');

?>
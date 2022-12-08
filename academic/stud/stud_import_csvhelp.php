<?php
	/**
	 * 帳號管理 - 新增帳號 - 匯入帳號 - CVS 範本
	 *
	 * @since   2005/05/20
	 * @author  Amm Lee
	 * @version $Id: stud_import_csvhelp.php,v 1.1 2010/02/24 02:38:45 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/stud_import_csvhelp.php');

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');

	echo '<div align="center">';

	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs');

		showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0" id="ListTable" ');

			showXHTML_tr_B();
				showXHTML_td_B();
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();

			showXHTML_tr_B();
				showXHTML_td_B('valign="top"');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable" style="display:inline"','');

						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('colspan="3"', $MSG['td_head'][$sysSession->lang]);
						showXHTML_tr_E();

						// 範例
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('nowrap="nowrap"', $MSG['td_example'][$sysSession->lang]);
							showXHTML_td('', $MSG['td_example2'][$sysSession->lang]);
						showXHTML_tr_E();

						// 說明
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('nowrap="nowrap"', $MSG['td_conviction'][$sysSession->lang]);
							showXHTML_td('', $MSG['td_conviction2'][$sysSession->lang]);
						showXHTML_tr_E();

						// 備註
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('nowrap="nowrap"', $MSG['td_comment'][$sysSession->lang]);
							showXHTML_td('', $MSG['td_comment2'][$sysSession->lang]);
						showXHTML_tr_E();

						// 關閉BUTTON
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'id="btn_close" class="cssBtn" onclick="window.close();"');
							showXHTML_td_E();
						showXHTML_tr_E();

					showXHTML_table_E();
				showXHTML_td_E();
			showXHTML_tr_E();

		showXHTML_table_E();
	showXHTML_body_E();
	echo '</div>';

?>
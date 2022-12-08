<?php
	/**
	 *
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Amm Lee <amm@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: director_add_csvhelp.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2005-09-22
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/academic_director.php');

	// 變數宣告 begin
	// 變數宣告 end

	// 函數宣告 begin
	// 函數宣告 end

	// 主程式 begin
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');

	echo '<div align="center">';

	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['title26'][$sysSession->lang], 'tabs');

			showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0" id="ListTable" ');

				showXHTML_tr_B('');
					showXHTML_td_B('');
						showXHTML_tabs($ary, 1,'','','',false);
					showXHTML_td_E('');
				showXHTML_tr_E('');

				showXHTML_tr_B('');
					showXHTML_td_B('valign="top"');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable" style="display:inline"','');

							showXHTML_tr_B('class="cssTrHead"');
								showXHTML_td_B('colspan="3"');
									echo $MSG['title27'][$sysSession->lang];
								showXHTML_td_E('');
							showXHTML_tr_E('');

							// 範例
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('nowrap="nowrap"');
									echo $MSG['title28'][$sysSession->lang];
								showXHTML_td_E('');
								showXHTML_td_B('');
									echo '900101,teacher001,64<br>',
						                 '900102,teacher002,1024<br>',
						                 '900103,teacher003,1024';
								showXHTML_td_E('');
							showXHTML_tr_E('');

							// 說明
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('nowrap="nowrap"');
									echo $MSG['title30'][$sysSession->lang];
								showXHTML_td_E('');
								showXHTML_td_B('');
									echo $MSG['title31'][$sysSession->lang];
								showXHTML_td_E('');
							showXHTML_tr_E('');

							// 關閉BUTTON
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('colspan="3" align="center"');
									showXHTML_input('button', '', $MSG['title32'][$sysSession->lang], '', 'id="btn_close" class="cssBtn" onclick="window.close();"');
								showXHTML_td_E('');
							showXHTML_tr_E('');

						showXHTML_table_E('');
					showXHTML_td_E('');
				showXHTML_tr_E('');

			showXHTML_table_E('');
	showXHTML_body_E('');
	echo '</div>';
	// 主程式 end
?>
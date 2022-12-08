<?php
	/**
	 * 選擇範本
	 *
	 * @since   2005/06/07
	 * @author  ShenTing Lin
	 * @version $Id: sch_theme.php,v 1.1 2010/02/24 02:38:42 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_ini.php');
	require_once(sysDocumentRoot . '/lang/sch_theme.php');
	
	$theme_total = 7;
	
	$theme = intval($_POST['theme']);
	if (empty($theme)) {
		$filename = sysDocumentRoot . "/base/{$sysSession->school_id}/theme/theme.ini";
		@touch($filename);
		$objAssoc = new assoc_data();
		$objAssoc->has_sections = false;
		$objAssoc->setStorePath($filename);
		$objAssoc->restore();   // 恢復原本的資料
		$theme = $objAssoc->getValues('', 'learn');
		if (empty($theme)) $theme = 1;
	}
	showXHTML_head_B($MSG['title_theme'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['theme_suit'][$sysSession->lang], 'tabs1'); //, action);
		// $ary[] = array($MSG['theme_detail'][$sysSession->lang], 'tabs2'); //, action);
		$colspan = 'colspan="2"';
		$width  = 350;
		$height = 330;
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actFm', '', 'action="sch_theme_logo.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td($colspan, $MSG['suit_step1'][$sysSession->lang]);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
				for ($i = 1; $i <= $theme_total; $i++) {
					if (($i != 1) && (($i + 1) % 2 == 0)) {
						showXHTML_tr_E();
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
					}

					showXHTML_td_B('align="center"');
						echo sprintf('<iframe src="/academic/sch/sch_layout.php?a=%d" frameBorder="0" width="%s" height="%s" class="cssTable cssTrEvn">&nbsp;</iframe>', $i, $width, $height);
						echo '<div align="left" style="margin: 2px 0px 0px 6px">';
						$ary = array($i => $MSG['theme_kind_' . $i][$sysSession->lang]);
						showXHTML_input('radio', 'theme', $ary, $theme, '');
						echo '</div>';
					showXHTML_td_E();
				}
				showXHTML_tr_E();

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B($colspan . 'align="center"');
						showXHTML_input('submit', '', $MSG['btn_next'][$sysSession->lang]  , '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

<?php
	/**
	 * 選擇要配置的 Logo
	 *
	 * @since   2005/06/07
	 * @author  ShenTing Lin
	 * @version $Id: sch_theme_logo.php,v 1.1 2010/02/24 02:38:42 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/academic/sch/sch_theme_lib.php');
	require_once(sysDocumentRoot . '/lang/sch_theme.php');

	$js = <<< BOF
	function previewImg(val) {
		if (val == "") val = orgSrc;
		var obj = document.getElementById("logo");
		if (obj) {
			obj.src = (window.XMLHttpRequest) ? "file:///" + val : val;
		}
		obj = document.getElementById("layout");
		if (obj) {
			if (typeof(obj.contentWindow.previewImg) != "undefined") {
				obj.contentWindow.previewImg(val);
			}
		}
	}

	function goList(bol) {
		var obj = document.getElementById("actFm");
		if (obj == null) return false;
		obj.action = "sch_theme.php";
		if (bol) obj.theme.value = 1;
		obj.submit();
	}

	var orgSrc = "";
	window.onload = function () {
		var obj = document.getElementById("logo");
		if (obj) orgSrc = obj.src;
	};
BOF;

	showXHTML_head_B($MSG['title_theme'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['theme_suit'][$sysSession->lang], 'tabs1'); //, action);
		// $ary[] = array($MSG['theme_detail'][$sysSession->lang], 'tabs2'); //, action);
		$colspan = 'colspan="2"';
		$width  = 350;
		$height = 330;
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actFm', '', 'action="sch_theme_save.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td($colspan, $MSG['suit_step2'][$sysSession->lang]);
				showXHTML_tr_E();

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B(' width="320" align="center"');
						echo sprintf('<iframe src="/academic/sch/sch_layout.php?a=%d&p=1" frameBorder="0" width="%s" height="%s" id="layout" class="cssTable cssTrEvn">&nbsp;</iframe>', intval($_POST['theme']), $width, $height);
						showXHTML_input('hidden', 'theme', intval($_POST['theme']), '', '');
					showXHTML_td_E();
					showXHTML_td_B('valign="top"');
						$env             = $sysSession->env;
						$sysSession->env = themeMap($_POST['theme']);
						$pic             = getThemeFile('logo.gif');
						$sysSession->env = $env;
						echo '<div style="width: 200px; height: 66px; overflow: hidden; margin: 0px 0px 5px 0px;">',
						     '<img src="', $pic, '?', time(), '" border="0" id="logo" />',
						     '</div>';
						showXHTML_input('file', 'uploads[]', '', '', 'class="cssInput" onchange="previewImg(this.value);"');
						echo '<div style="margin: 10px 0px 0px 0px;">',
						     $MSG['msg_logo_note1'][$sysSession->lang], '<br />',
						     sprintf($MSG['msg_logo_note2'][$sysSession->lang], ini_get('upload_max_filesize')), '<br />',
						     $MSG['msg_logo_note3'][$sysSession->lang], '<br />',
						     '</div>';
					showXHTML_td_E();
				showXHTML_tr_E();

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B($colspan . 'align="center"');
						showXHTML_input('button', '', $MSG['btn_prv'][$sysSession->lang]   , '', 'class="cssBtn" onclick="goList(false);"');
						showXHTML_input('submit', '', $MSG['btn_ok'][$sysSession->lang]    , '', 'class="cssBtn"');
						showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="goList(true);"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>

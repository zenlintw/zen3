<?php
	/**
	 * 樣版
	 *
	 * @since   2005/06/07
	 * @author  ShenTing Lin
	 * @version $Id: sch_layout.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/sch/sch_theme_lib.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_theme.php');
	
	$sysSession->env = themeMap($_GET['a']);
	define('DEMO', true);

	$p = (isset($_GET['p']) && (intval($_GET['p']) == 1)) ? 'true': 'false';

	$js = <<< BOF
	var hexChars = "0123456789ABCDEF";
	function Dec2Hex(Dec) {
		var a = Dec % 16;
		var b = (Dec - a) / 16;
		var hex = "" + hexChars.charAt(b) + hexChars.charAt(a);
		return hex;
	}

	function RGB2Hex(txt) {
		txt = txt.toUpperCase();
		// IE 與 Mozilla 捕捉符合的子項目的開始值不一樣
		txt = txt.replace(/^RGB\((.+)\)$/, function ($0, $1) {
			if (typeof($1) == "undefined") return $0;
			return $1;
		});
		var c = txt.split(", ");
		txt = "#";
		for (i = 0; i < c.length; i++) {
			txt += Dec2Hex(c[i]);
		}
		return txt;
	}

	function previewImg(val) {
		if (val == "") val = orgSrc;
		var obj = document.getElementById("logo");
		if (obj) {
			obj.src = (window.XMLHttpRequest) ? "file:///" + val : val;
		}
	}

	function rePos(bol) {
		var obj = null;
		var sz = (bol) ? 120 : -120;
		var val = 80;
		obj = document.getElementById("sysbar_logo");
		if (obj) {
			val = parseInt(obj.style.width);
			if (!bol && (val <= 80)) return false;
			val += sz;
			obj.style.width = val + "px";
		}
		obj = document.getElementById("sysbar_course");
		if (obj) obj.style.width = val + "px";
	}

	var orgSrc = "";
	window.onload = function getColor() {
		var obj = null, sty = null;
		var txt = "";
		for (var k = 0; k < document.styleSheets.length; k++) {
			// MIS#23533 管理者環境→學校管理→學生環境布置 狀態列出現js error by Small 2011/12/29
			// obj = (window.XMLHttpRequest) ? document.styleSheets[k].cssRules : document.styleSheets[k].rules;
			obj = document.styleSheets[k].rules;
			for (var i = 0; i < obj.length; i++) {
				switch (obj[i].selectorText) {
					case ".cssBg01"  :
					case ".cssBg02"  :
					case ".cssTable" :
					case ".cssTrHead":
					case ".cssTrEvn" :
					case ".cssTrOdd" :
						sty = document.getElementById("color" + obj[i].selectorText);
						if (sty) {
							txt = obj[i].style.backgroundColor.toUpperCase();
							sty.style.backgroundColor = txt;
							if (window.XMLHttpRequest) txt = RGB2Hex(txt);
							sty.innerHTML = txt;
						}
						break;
					default:
				}
			}
		}

		obj = document.getElementById("logo");
		if (obj) orgSrc = obj.src;

		rePos({$p});
	};
BOF;

	showXHTML_head_B($MSG['title_layout'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/sysbar.css");
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0"');
		// 選單 (Begin)
		echo '<div style="width: 350px; height: 90px; overflow: hidden;">';
		showXHTML_table_B('width="350" height="90" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('class="cssBg01"');
				showXHTML_td_B('width="50"');
					$logo = "/theme/{$sysSession->theme}/{$sysSession->env}/logo.gif";
					echo '<div id="sysbar_logo" style="width: 80px; height: 66px; overflow: hidden;">';
					echo '<img src="' . $logo . '" border="0" id="logo" /></div>';
				showXHTML_td_E();
				showXHTML_td_B('width="300" valign="top" nowrap="nowrap"');
					echo '<div class="sysUsername">' . $sysSession->username . $MSG['user_info'][$sysSession->lang] . '</div>';
					// 主選單
					echo '<div id="MContainer" class="MContainer" style="position: relative; width: 200px; left: 6px; top: 24px;"><div class="MMenu" id="MMenu">';
					echo '<table><tr>';
					for ($i = 0; $i < 2; $i++) {
						echo sprintf('<td class="MMenuItemOut" nowrap="nowrap" onmouseover="this.className=\'MMenuItemOver\'" onmouseout="this.className=\'MMenuItemOut\'"><a class="MMenuItemFont" href="javascript:;" title="%s">%s</a></td>', $MSG['main_menu'][$sysSession->lang], $MSG['main_menu'][$sysSession->lang]);
					}
					echo '</tr></table>';
					echo '</div></div>';
				showXHTML_td_E();
			showXHTML_tr_E();

			showXHTML_tr_B('class="cssBg02"');
				showXHTML_td_B('width="50" height="24" align="center" valign="middle"');
					$ary = array(
						0 => $MSG['course_name_teach'][$sysSession->lang],
						1 => $MSG['course_name'][$sysSession->lang]
					);
					showXHTML_input('select', 'cs', $ary, 0, 'id="sysbar_course" class="cssInput" style="width: 80px;"');
				showXHTML_td_E();

				showXHTML_td_B('width="300" height="24" nowrap="nowrap"');
					// 子選單
					echo '<div id="SContainer" class="SContainer" style="position: relative; width: 200px; height: 24px; left: 15px; top: 5px;"><div class="SMenu" id="SMenu">';
					for ($i = 0; $i < 2; $i++) {
						echo '<pre class="SMenuItemSplit">&nbsp;</pre>';
						echo sprintf('<a class="SMenuItemOut" href="javascript:;" title="%s" onmouseover="this.className=\'SMenuItemOver\'" onmouseout="this.className=\'SMenuItemOut\'">%s</a>', $MSG['sub_menu'][$sysSession->lang], $MSG['sub_menu'][$sysSession->lang]);
					}
					echo '</div></div>';
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
		echo '</div>';
		// 選單 (End)
		// 分頁表格 (Begin)
		echo '<div align="center" style="width: 350px;">';
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs1');
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs2');
		$colspan = 'colspan="2"';
		showXHTML_tabFrame_B($ary, 1, '', '', 'onsubmit="return false;" style="display: inline;"', false);
			showXHTML_table_B('width="330" align="center" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td($colspan, $MSG['help'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B($colspan . ' nowrap="nowrap"');
						echo $MSG['page1'][$sysSession->lang];
						showXHTML_input('select', 'ap', array(1=>1, 2=>2, 3=>3), 1, 'class="cssInput" style="width: 40px"');
						showXHTML_input('button', '', $MSG['button'][$sysSession->lang], '', 'disabled="disabled" class="cssBtn"');
						showXHTML_input('button', '', $MSG['button'][$sysSession->lang], '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('id="myHead" class="cssTrHead"');
					showXHTML_td('align="center"', $MSG['th'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['th'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B('id="myData1" class="cssTrEvn"');
					showXHTML_td('', $MSG['content'][$sysSession->lang]);
					showXHTML_td('', $MSG['data'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B('id="myData2" class="cssTrOdd"');
					showXHTML_td('', $MSG['content'][$sysSession->lang]);
					showXHTML_td('', $MSG['data'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B($colspan . ' align="center"');
						showXHTML_input('button', '', $MSG['button'][$sysSession->lang], '', 'disabled="disabled" class="cssBtn"');
						showXHTML_input('button', '', $MSG['button'][$sysSession->lang], '', 'class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		// 分頁表格 (End)

		// 色票 (Begin)
		showXHTML_table_B('width="330" border="0" cellspacing="1" cellpadding="3" class="cssTable" style="margin: 6px 0px 0px 0px;"');
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('width="20%" colspan="2" id="color.cssBg01"'  , '');
				showXHTML_td('width="20%" colspan="2" id="color.cssBg02"'  , '');
			showXHTML_tr_E();
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('width="20%" id="color.cssTrHead"', '');
				showXHTML_td('width="20%" id="color.cssTable"' , '');
				showXHTML_td('width="20%" id="color.cssTrOdd"' , '');
				showXHTML_td('width="20%" id="color.cssTrEvn"' , '');
			showXHTML_tr_E();
		showXHTML_table_E();
		echo '</div>';
		// 色票 (End)
	showXHTML_body_E();
?>

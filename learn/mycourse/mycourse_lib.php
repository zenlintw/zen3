<?php
	/**
	 * 我的課程共用函數
	 *
	 * @since   2004/08/27
	 * @author  ShenTing Lin
	 * @version $Id: mycourse_lib.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/lib_ini.php');

	/**
	 * 模組編號與程式名稱的對應表
	 **/
	$modTable = array(
		'LoginInfo'       => 'mod_login_info.php',
		'ShortLink'       => 'mod_short_link.php',
		'MyCourse'        => 'mod_mycourse.php',
		'MyOffice'        => 'mod_myoffice.php',
		'CalendarUser'    => 'mod_calendar_user.php',
		'CalendarCourse'  => 'mod_calendar_course.php',
		'CalendarSchool'  => 'mod_calendar_school.php',
		'MyMessageCenter' => 'mod_message.php',
		'MyNews' 		  => 'mod_news.php',
		'MyFAQ' 		  => 'mod_faq.php'
	);
	// 設定模組要顯示的位置 (Begin)
	$MyCfg_Head = array('LoginInfo');
	$MyCfg_Col1 = array('ShortLink', 'MyCourse', 'MyOffice', 'CalendarUser', 'CalendarCourse', 'CalendarSchool');
	$MyCfg_Col2 = array('MyMessageCenter','MyNews', 'MyFAQ');
	// 設定模組要顯示的位置 (End)

	$defWidth = 750;
	$defLSize = 200;
	$defRSize = 525;

	function getMyConfigFile($username='') {
		global $sysSession;

		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;
		$userDir = MakeUserDir($username);
		$filename = $userDir . '/my_configure_' . $sysSession->school_id . '.ini';
		@touch($filename);
		return $filename;
	}

	/**
	 * 處理資料，過長的部份隱藏
	 * @param integer $width   : 要顯示的寬度
	 * @param string  $caption : 顯示的文字
	 * @param string  $title   : 浮動的提示文字，若沒有設定，則跟 $caption 依樣
	 * @return string : 處理後的文字
	 **/
	if (!function_exists('divMsg')) {
		function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
			if (empty($title)) $title = $caption;
			$wd = is_numeric($width) ? intval($width) . 'px' : $width;
			return $without_title ? ('<div style="width: ' . $wd . '; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $wd . '; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
		}
	}

	$aryMyTitleID = array();
	function showXHTML_mytitle_B($id='', $title='', $width=200, $showCloseBtn=true) {
		global $sysSession, $MSG, $aryMyTitleID;

		$id = trim($id);
		if (empty($id)) $id = uniqid('my_');
		if (in_array($id, $aryMyTitleID)) $id = uniqid('my_');
		else $aryMyTitleID[] = $id;
		$theme = (empty($sysSession->theme)) ? '/theme/default/learn/' : "/theme/{$sysSession->theme}/{$sysSession->env}/";

		showXHTML_table_B('width="' . $width . '" border="0" cellspacing="0" cellpadding="0" id="' . $id . '"');
			// 標題 (Begin)
			showXHTML_tr_B('myAttr="drag"');
				$pic = getThemeFile('my_title1.gif');
				if (empty($pic)) $pic = $theme . 'my_title1.gif';
				$img = '<img src="' . $pic . '" width="24" height="24" border="0" align="absbottom">';
				echo '<td width="24">' . $img . '</td>';
				// showXHTML_td('width="24"', $img);
				$pic = getThemeFile('my_title2.gif');
				if (empty($pic)) $pic = $theme . 'my_title2.gif';
				if (is_numeric($width)) {
					$wd = intval($width) - 40;
					echo '<td valign="bottom" nowrap class="cssTabs" style="background-image: url(' . $pic . ');">' . divMsg($wd, $title, htmlspecialchars($title)) . '</td>';
					// showXHTML_td('valign="bottom" nowrap class="cssTabs" style="background-image: url(' . $theme . 'my_title2.gif);"', divMsg($wd, $title, htmlspecialchars($title)));
				} else {
					echo '<td width="100%" valign="bottom" nowrap class="cssTabs" style="background-image: url(' . $pic . ');">' . divMsg('100%', $title, htmlspecialchars($title)) . '</td>';
					// showXHTML_td('width="100%" valign="bottom" nowrap class="cssTabs" style="background-image: url(' . $theme . 'my_title2.gif);"', divMsg('100%', $title, htmlspecialchars($title)));
				}

				if ($showCloseBtn) {
					$pic = getThemeFile('my_close.gif');
					if (empty($pic)) $pic = $theme . 'my_close.gif';
					$img = '<img src="' . $pic . '" width="16" height="16" border="0" align="absmiddle">';
					$img = '<a href="javascript:;" onmousedown="event.cancelBubble=true;" onclick="closeMyTitle(\'' . $id . '\'); return false;" title="' . $MSG['btn_close'][$sysSession->lang] . '">' . $img . '</a>';
					$pic = getThemeFile('my_title2.gif');
					if (empty($pic)) $pic = $theme . 'my_title2.gif';
					echo '<td width="16" nowrap valign="bottom" class="cssTabs" style="background-image: url(' . $pic . ');">' . $img . '</td>';
					// showXHTML_td('width="16" nowrap valign="bottom" class="cssTabs" style="background-image: url(' . $theme . 'my_title2.gif);"', $img);
				} else {
					$pic = getThemeFile('my_title3.gif');
					if (empty($pic)) $pic = $theme . 'my_title3.gif';
					$img = '<img src="' . $pic . '" width="16" height="24" border="0" align="absbottom">';
					echo '<td width="16" nowrap>' . $img . '</td>';
					// showXHTML_td('width="16" nowrap', $img);
				}
				$pic = getThemeFile('my_title4.gif');
				if (empty($pic)) $pic = $theme . 'my_title4.gif';
				$img = '<img src="' . $pic . '" width="9" height="24" border="0" align="absbottom">';
				echo '<td width="9">' . $img . '</td>';
				// showXHTML_td('width="9"', $img);
			showXHTML_tr_E();
			// 標題 (End)
			// 資料 (Begin)
			showXHTML_tr_B();
				showXHTML_td_B('colspan="4"');
		return $id;
	}

	function showXHTML_mytitle_E($extra='') {
				echo $extra;
				showXHTML_td_E();
			showXHTML_tr_E();
			// 資料 (End)
		showXHTML_table_E();
	}

	function showXHTML_mytitle_more($extra='') {
		global $sysSession, $MSG;

		$theme = (empty($sysSession->theme)) ? '/theme/default/learn/' : "/theme/{$sysSession->theme}/{$sysSession->env}/";
		$pic = getThemeFile('more.gif');
		if (empty($pic)) $pic = $theme . 'more.gif';
		$img = '<img src="' . $pic . '" width="33" height="8" border="0" align="absbottom">';

		echo '<div align="right" style="height: 12px; line-height: 12px;">&nbsp;<a href="javascript:;" ' . $extra . '>' . $img . '</a></div>';
	}

	function showXHTML_mytitle_postit($id='', $title='&nbsp;', $extra=' width: 100%;') {
		$id = trim($id);
		if (empty($id)) $id = uniqid('pst_');
		echo '<div align="center" myAttr="pst" modID="' . $id . '" id="pst_' . $id . '" style="height: 11px; line-height: 11px; font-size: 10px; background-color: #FF0000; color: #FFFFFF; z-index: 100; visibility: hidden; ' . $extra . '">&nbsp;' . $title . '&nbsp;</div>';
	}

	// 讀取設定檔
	$filename = getMyConfigFile($sysSession->username);
	$myConfig = new assoc_data();
	$myConfig->has_sections = true;
	$myConfig->setStorePath($filename);
	$myConfig->restore();
	// 設定預設值 (Begin)
	if (!isset($myConfig->assoc_ary['MyConfig_Head'])) {
		$myConfig->setValues('MyConfig_Head', '', $MyCfg_Head, true);
		foreach ($MyCfg_Head as $key => $val) {
			$myConfig->setValues($val, 'visibility', 'visible');
		}
	}
	if (!isset($myConfig->assoc_ary['MyConfig_Col1'])) {
		$myConfig->setValues('MyConfig_Col1', '', $MyCfg_Col1, true);
		foreach ($MyCfg_Col1 as $key => $val) {
			$myConfig->setValues($val, 'visibility', 'visible');
		}
	}
	if (!isset($myConfig->assoc_ary['MyConfig_Col2'])) {
		$myConfig->setValues('MyConfig_Col2', '', $MyCfg_Col2, true);
		foreach ($MyCfg_Col2 as $key => $val) {
			$myConfig->setValues($val, 'visibility', 'visible');
		}
	}
	foreach ($modTable as $key => $val) {
		// 檢查有沒有將模組放到要顯示的位置 (Begin)
		$posH = in_array($key, $myConfig->assoc_ary['MyConfig_Head']);
		$pos1 = in_array($key, $myConfig->assoc_ary['MyConfig_Col1']);
		$pos2 = in_array($key, $myConfig->assoc_ary['MyConfig_Col2']);
		if (!($posH || $pos1 || $pos2)) {
			if (in_array($key, $MyCfg_Head)) $myConfig->setValues('MyConfig_Head', '', $key);
			if (in_array($key, $MyCfg_Col1)) $myConfig->setValues('MyConfig_Col1', '', $key);
			if (in_array($key, $MyCfg_Col2)) $myConfig->setValues('MyConfig_Col2', '', $key);
		}
		// 檢查有沒有將模組放到要顯示的位置 (End)
	}
	$myConfig->store();
	// 設定預設值 (End)
?>

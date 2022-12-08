<?php
	/**
	 * 首頁相關的 function
	 * @todo
	 *     1. 選單的部份
	 *     2. 精靈
	 *     3. 整理相關的 function 使其更簡潔
	 *
	 * @author  ShenTing Lin
	 * @version $Id: syslib.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wise_template.php');	
	require_once(sysDocumentRoot . '/lang/sys_tools.php');		
	
	$lang = $sysSession->lang;
	$theme_lang = strtolower($lang);
	$tplSysPath = sysDocumentRoot . '/sys/tpl/';
	$tplSchPath = sysDocumentRoot . "/base/{$sysSession->school_id}/door/tpl/";
	$path_theme = "/theme/{$sysSession->theme}/sys/";
	$path_door  = "/base/{$sysSession->school_id}/door/";
	/**
	 * 取得樣板的路徑
	 * @param string $filename : 檔案名稱，不含路徑
	 * @return string $file : 包含路徑與檔名的字串
	 **/
	function getTemplate($filename) {
		global $sysSession, $tplSysPath, $tplSchPath, $lang;

		$tpl_lang = strtolower($lang);
		$file = $tplSchPath . $tpl_lang . '/' . $filename;
		if (file_exists($file)) return $file;
		$file = $tplSchPath . $filename;
		if (file_exists($file)) return $file;
		$file = $tplSysPath . $filename;
		if (file_exists($file)) return $file;
	}
	
	/**
	 * 所有樣板預設會轉換的文字
	 * @param object $obj : 樣板物件
	 * @return void
	 **/
	function genDefaultTrans(&$obj) {
		global $sysSession, $path_door, $path_theme, $lang, $theme_lang;
		$obj->add_replacement('<%DOOR_PATH%>'  , $path_door);
		$obj->add_replacement('<%THEME_PATH%>' , $path_theme);
		$obj->add_replacement('<%LANGUAGE%>'   , $theme_lang);
		$obj->add_replacement('<%USERNAME%>'   , $sysSession->username);
		$obj->add_replacement('<%SCHOOL_NAME%>', $sysSession->school_name);
		$obj->add_replacement('<!---->'        , '');
	}
	
	/**
	 * cleanSpace()
	 *     清除 <TAG> 與 </TAG> 內多餘的空白
	 * @param $buffer 所需處理的資料
	 * @return 處理後的結果
	 **/
	function cleanSpace($buffer) {
		return (preg_replace('/>\s+</', '><', $buffer));
	}	

	/**
	 * showButton()
	 *     顯示一個背景有圖的按鈕
	 * @param $caption 按鈕上面的文字
	 * @param $image 按鈕的圖片
	 * @param $href 所要連結的網址
	 * @param $event 所要執行的動作
	 * @return 一個按鈕
	 **/
	function showButton($type, $caption, $image, $extra) {
		if (empty($type))
			$type = 'button';
		if (empty($caption))
			$caption = '&nbsp;&nbsp;';
		if (!empty($image)) {
			if (!file_exists(sysDocumentRoot . $image)) {
				$image = "/theme/{$sysSession->theme}/sys/button.gif";
			}
			$image = " style=\"background-image: url($image);\"";
		}

		//$result = "<button type=\"$type\" class=\"button\" $image $extra >$caption</button>\n";
		$result = "<input type=\"$type\" value=\"$caption\" $image $extra />\n";
		return $result;
	}		

	function layout($title, $content) {
		global $sysSession, $MSG, $lang, $sysConn;
		// 頁首 -----------------------------------------------------------------------
		include_once(sysDocumentRoot . '/sys/door/mod_head.php');
		$cont_head = mod_head();				
	
		// 頁尾 -----------------------------------------------------------------------
		include_once(sysDocumentRoot . '/sys/door/mod_foot.php');
		$cont_foot = mod_foot();

		$tpl = getTemplate('reg_index.htm');
		$myTemplate = new Wise_Template($tpl);
		genDefaultTrans($myTemplate);
		
		$css = getTemplate('door.css');
		if (file_exists($css)) {
			$css = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $path_door . 'tpl/door.css');
		} else {
			$css = '';
		}

		$myTemplate->add_replacement('<%USER_THEME%>'	, $css);
		$myTemplate->add_replacement('<%TITLE%>'     	, $title);	
		$myTemplate->add_replacement('<%MOD_HEAD%>'		, $cont_head);		
		$myTemplate->add_replacement('<%MOD_CONTENT%>'	, $content);
		$myTemplate->add_replacement('<%MOD_FOOT%>'     , $cont_foot);		
		$myTemplate->print_result(false);					
		return;		
	}

	/**
	 * getTheme()
	 *     取得系統所有的佈景
	 * @return array 佈景
	 **/
	function getTheme() {
		$theme = '';
		$dp = opendir(sysDocumentRoot . '/theme/');
		while ( $entry = readdir($dp) ) {
			if ( strpos($entry, '.') !== 0 ) {
				if (is_dir(sysDocumentRoot . '/theme/' . $entry)) $theme[$entry] = $entry;
			}
		}
		closedir($dp);
		return $theme;
	}
?>

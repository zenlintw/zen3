<?php
	/**
	 * 選單同步工具
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      ShenTing Lin <lst@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: sync_menu.php,v 1.1 2010/02/24 02:38:46 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2005/
	 */

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	define('SYSBAR_LEVEL', 'root');
	define('SYSBAR_MENU' , 'personal');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_lib.php');

/**
 * 1. 讀取系統選單
 *     管理者
 *     教師 -> 學生
 *     導師 -> 學生
 *     學校
 *     個人
 * 2. 讀取自訂的選單
 * 3. 套用系統選單
 * 4. 回存
 **/

// {{{ 變數宣告 begin
	// $sysMenu = [];
// }}} 變數宣告 end


// {{{ 函數宣告 begin
	function getSysbarXMLDocs($filename)
	{
		$xmlDocs = null;
		if (!empty($filename) && @is_file($filename))
		{
			$xmlDocs = domxml_open_file($filename);
		}
		return $xmlDocs;
	}

// }}} 函數宣告 end


// {{{ 主程式 begin

	$menu = array('academic', 'school', 'personal', 'teach', 'learn', 'direct');
	// 學校預設值
	for ($i = 0; $i < count($menu); $i++)
	{
		$SYSBAR_MENU  = $menu[$i];
		$SYSBAR_LEVEL = 'administrator';
		$filename     = getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, false);
		$xmlDocs      = getSysbarXMLDocs($filename);
		if (!is_null($xmlDocs))
		{
			$res = saveSysbar($xmlDocs, true);
		}

		echo 'default: [' . $menu[$i] . '] sync OK.<br />';
	}

	reset($menu);
	// 學校設定值
	for ($i = 0; $i < count($menu); $i++)
	{
		$SYSBAR_MENU  = $menu[$i];
		$SYSBAR_LEVEL = 'manager';
		$filename     = getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, false);
		$xmlDocs      = getSysbarXMLDocs($filename);
		if (!is_null($xmlDocs))
		{
			$res = saveSysbar($xmlDocs, true);
		}

		echo 'school: [' . $menu[$i] . '] sync OK.<br />';
	}

	// 教師環境 學生環境 (Begin)
	$SYSBAR_LEVEL = 'manager_course';
	$RS = dbGetStMr('WM_term_course', '`course_id`', '`course_id`!=10000000', ADODB_FETCH_ASSOC);
	while (!$RS->EOF) {
		$sysSession->course_id = $RS->fields['course_id'];
		$SYSBAR_MENU           = 'teach';
		$filename              = getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, false);
		$xmlDocs               = getSysbarXMLDocs($filename);
		if (!is_null($xmlDocs))
		{
			$res = saveSysbar($xmlDocs, true);
		}
		echo 'teach: [adm_course] (' . $sysSession->course_id . ') sync OK.<br />';

		$SYSBAR_MENU  = 'learn';
		$filename     = getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, false);
		$xmlDocs      = getSysbarXMLDocs($filename);
		if (!is_null($xmlDocs))
		{
			$res = saveSysbar($xmlDocs, true);
		}
		echo 'learn: [course] (' . $sysSession->course_id . ') sync OK.<br />';

		$RS->MoveNext();
	}
	// 教師環境 學生環境 (End)

	// 導師環境 (Begin)
	$RS = dbGetStMr('WM_class_main', '`class_id`', '`class_id`!=1000000', ADODB_FETCH_ASSOC);
	while (!$RS->EOF) {
		$sysSession->class_id = $RS->fields['class_id'];
		$SYSBAR_MENU          = 'direct';
		$filename             = getSysbarSetFile($SYSBAR_MENU, $SYSBAR_LEVEL, false);
		$xmlDocs              = getSysbarXMLDocs($filename);
		if (!is_null($xmlDocs))
		{
			$res = saveSysbar($xmlDocs, true);
		}
		echo 'direct: [adm_class] (' . $sysSession->class_id . ') sync OK.<br />';

		$RS->MoveNext();
	}
	// 導師環境 (End)
// }}} 主程式 end

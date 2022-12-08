<?php
	/**
     * 學習路徑備份還原 -- 預覽學習路徑
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     *          則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     *          照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Edi Chen <edi@sun.net.tw>
     * @copyright   2000-2007 SunNet Tech. INC.
     * @version     CVS: $Id: cour_path_preview.php,v 1.1 2010/02/24 02:40:23 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2007-01-05
     */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');

// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end

// {{{ 變數宣告 begin
	$lang = $_GET['lang'] && in_array($_GET['lang'], $sysAvailableChars) ? $_GET['lang'] : $sysSession->lang;
	$sysLangs = array('Big5'        => $MSG['big5'][$sysSession->lang],
	                  'GB2312'      => $MSG['gb2312'][$sysSession->lang],
	                  'en'          => $MSG['en'][$sysSession->lang],
	                  'EUC-JP'      => $MSG['euc-jp'][$sysSession->lang],
	                  'user_define' => $MSG['user_define'][$sysSession->lang]);
	removeUnAvailableChars($sysLangs);

	// #47318 Chrome [教師/課程管理/學習路徑管理/備份還原] 點選「預覽」，畫面出現亂碼。-->增加編碼片段
    showXHTML_head_B($MSG['expandAll'][$sysSession->lang]);
	showXHTML_head_E();

	ob_start();
	echo '<input id="expandBtn" type=button value="'.$MSG['collapseAll'][$sysSession->lang].'" onclick="expandingAll();this.value=expandingFlag==\'none\'?\''.$MSG['collapseAll'][$sysSession->lang].'\':\''.$MSG['expandAll'][$sysSession->lang].'\';" class="cssBtn">&nbsp;&nbsp;'; // 全展開/收攏
	echo $MSG['other_lang'][$sysSession->lang];	// 其它語系
	showXHTML_input('select', '', $sysLangs, $lang, 'class="cssInput" onchange="location.replace(\'/teach/course/cour_path_preview.php?sid=' . str_replace('+', '%252B', $_GET["sid"]) . '&lang=\'+this.value);"');
	$switchLangs = ob_get_contents();
	ob_end_clean();

    $_GET['sid'] = sysNewDecode($_GET['sid']);
    $pathSerial  = $_GET['sid'] ? ('?' . trim($_GET['sid'])) : '';
    $sLang       = '?lang=' . $lang;
    $justPreview = 1;
// }}} 變數宣告 end

// }}} 主程式 begin
	ob_start();
	require_once(sysDocumentRoot . '/learn/path/pathtree.php');
	$buffers = ob_get_contents();
	ob_end_clean();

    $baseUrl = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
    $baseUrl .= '://'. $_SERVER['HTTP_HOST'];
    if (($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443)){
        $baseUrl .= ':'.$_SERVER['SERVER_PORT'];
    }
    $baseUrl .= '/learn/path/manifest.php';

	$base = '<head><base href="'.$baseUrl.'">';
	$buffers = str_replace('<head>', $base, $buffers);
	echo preg_replace('/(<body\b[^>]*>\s*)/i', '\1' . $switchLangs, $buffers);

// }}} 主程式 end
?>

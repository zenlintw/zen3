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
	 * @author      ShenTing Lin <lst@sun.net.tw>
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: msg_wysiwyg.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       
	 **/
	define('NO_TEMPLATE', true);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/editor.php');

	// 常數定義 begin
	// 常數定義 end

	// 變數宣告 begin
	$js = <<< BOF
	function myclose(bol)
	{
		if (bol && window.opener)
		{
			var obj = window.opener.document.getElementById("msg_content");
			if (obj)
				obj.value = editor.getHTML();
		}
		window.close();
	}

	window.onload = function ()
	{
		if (window.opener)
		{
			var obj1 = document.getElementById("content");
			var obj2 = window.opener.document.getElementById("msg_content");
			if (obj1 && obj2)
				obj1.value = obj2.value;
		}
	};
BOF;
	// 變數宣告 end

	// 函數宣告 begin
	// 函數宣告 end

	// 主程式 begin
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0"');
		$ary = array();
		$ary[] = array('Test', 'Tabs1');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_form_B('method="post" onsubmit="return false;" enctype="multipart/form-data" style="display: inline;"', "MyWin");
			$oEditor = new wmEditor;
			$oEditor->setValue(stripslashes($content));
			$oEditor->addContType('isHTML', 1);
			$oEditor->generate('content');
		showXHTML_form_E();
		showXHTML_input('button', 'btnOK'    , 'OK'    , '', 'onclick="myclose(true);" class="cssBtn"');
		showXHTML_input('button', 'btnCancel', 'Cancel', '', 'onclick="myclose(false);" class="cssBtn"');
		echo '</div>';
	showXHTML_body_E();
	// 主程式 end
?>
<?php
   /**
    * 撰寫筆記(以新視窗開啟撰寫筆記的頁面，供任何功能所使用)
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
    * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
    * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: write_notebook.php,v 1.1 2010/02/24 02:40:18 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2006-06-22
    */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/message/lib.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end

// {{{ 變數宣告 begin

// }}} 變數宣告 end

// {{{ 函數宣告 begin

// }}} 函數宣告 end

// {{{ 主程式 begin
	/*
	 * 在這又open一次是為了控制視窗不要有toolbar,menu等，因為如果掛在sysbar上，新開視窗會有menu
	 */
	setNotebookID('sys_notebook');
	echo <<< BOF
    <form action="write.php?commonuse=true" name="a" method="get"></form>
	<script language="javascript">
		document.a.submit();
	</script>
BOF;

// }}} 主程式 end

?>
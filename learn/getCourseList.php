<?php
	/**
	 * 檔案說明
	 *	取得課程清單
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: getCourseList.php,v 1.1 2010-02-24 02:39:05 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-07-18
	 */
	 
	 	 
	ob_start();
	require_once('mooc_sysbar.php');
	$content = ob_get_contents();
	ob_end_clean();
	
	if (preg_match('/<select.*id="selcourse".*<\/select>/', $content, $arg))
		echo $arg[0];
	else
		echo '';
?>
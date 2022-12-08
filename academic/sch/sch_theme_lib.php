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
	 * @copyright   2000-2006 SunNet Tech. INC.
	 * @version     CVS: $Id: sch_theme_lib.php,v 1.1 2010/02/24 02:38:42 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       
	 **/

	// 常數定義 begin
	// 常數定義 end

	// 變數宣告 begin

	// 變數宣告 end

	// 函數宣告 begin
	function themeMap($val)
	{
		$path = 'learn';
		switch (intval($val))
		{
			case 2: // 教師環境
				$path = 'teach';
				break;
			case 3: // 導師環境
				$path = 'direct';
				break;
			case 4: // 管理者環境
				$path = 'academic';
				break;
			case 5: // 學生環境
				$path = 'learn_1';
				break;
			case 6: // 學生環境
				$path = 'learn_2';
				break;
			case 7: // mooc環境
				$path = 'learn_mooc';
				break;	
			default: // 學生環境
				$path = 'learn';
		}
		return $path;
	}
	// 函數宣告 end

	// 主程式 begin

	// 主程式 end
?>
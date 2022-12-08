<?php
    /**
     * 產生 Captcha 識別圖
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2005 SunNet Tech. INC.
     * @version     CVS: $Id: captcha.php,v 1.1 2010/02/24 02:40:20 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2006-10-12
     */
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    session_start();
	header("Content-type: image/jpeg");
    $im = imagecreate(200,70);
    $clr_white = imagecolorallocate($im, 255, 255, 255); // 背景色
	$clr_black = imagecolorallocate($im, 120, 120, 120); // 前景色
    $imagebg = imagecreatefromjpeg('./bg/Image000' . sprintf('%02u', rand(1,33)) . '.jpg'); // 底圖
    imagesettile($im, $imagebg); // 設底圖
	imagefill($im, 0, 0,IMG_COLOR_TILED); // 填滿底圖
	$str = '';
    for($i=4; $i>0; $i--) // 填四個字
    {
		imagettftext(
			$im, 48, rand(-30,30), (4-$i)*40+16, rand(52,59),
			$clr_black, './57930___.TTF', ($c = chr(rand(48,57))) //78640
		);
		$str .= $c;
	}
	$_SESSION['captcha'] = $str;
    imagejpeg($im);
    imagedestroy($im);
?>

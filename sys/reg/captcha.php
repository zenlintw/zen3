<?php
    /**
     * ���� Captcha �ѧO��
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
     * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
     * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
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
    $clr_white = imagecolorallocate($im, 255, 255, 255); // �I����
	$clr_black = imagecolorallocate($im, 120, 120, 120); // �e����
    $imagebg = imagecreatefromjpeg('./bg/Image000' . sprintf('%02u', rand(1,33)) . '.jpg'); // ����
    imagesettile($im, $imagebg); // �]����
	imagefill($im, 0, 0,IMG_COLOR_TILED); // �񺡩���
	$str = '';
    for($i=4; $i>0; $i--) // ��|�Ӧr
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

<?php
	/**
	 * �ɮ׻���
	 *	���o�ҵ{�M��
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
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
<?php
	/**
	 * 
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      ShenTing Lin <lst@sun.net.tw>
	 * @copyright   2000-2006 SunNet Tech. INC.
	 * @version     CVS: $Id: sch_theme_lib.php,v 1.1 2010/02/24 02:38:42 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       
	 **/

	// �`�Ʃw�q begin
	// �`�Ʃw�q end

	// �ܼƫŧi begin

	// �ܼƫŧi end

	// ��ƫŧi begin
	function themeMap($val)
	{
		$path = 'learn';
		switch (intval($val))
		{
			case 2: // �Юv����
				$path = 'teach';
				break;
			case 3: // �ɮv����
				$path = 'direct';
				break;
			case 4: // �޲z������
				$path = 'academic';
				break;
			case 5: // �ǥ�����
				$path = 'learn_1';
				break;
			case 6: // �ǥ�����
				$path = 'learn_2';
				break;
			case 7: // mooc����
				$path = 'learn_mooc';
				break;	
			default: // �ǥ�����
				$path = 'learn';
		}
		return $path;
	}
	// ��ƫŧi end

	// �D�{�� begin

	// �D�{�� end
?>
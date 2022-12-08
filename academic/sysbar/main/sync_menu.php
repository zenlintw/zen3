<?php
	/**
	 * ���P�B�u��
	 *
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
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
 * 1. Ū���t�ο��
 *     �޲z��
 *     �Юv -> �ǥ�
 *     �ɮv -> �ǥ�
 *     �Ǯ�
 *     �ӤH
 * 2. Ū���ۭq�����
 * 3. �M�Ψt�ο��
 * 4. �^�s
 **/

// {{{ �ܼƫŧi begin
	// $sysMenu = [];
// }}} �ܼƫŧi end


// {{{ ��ƫŧi begin
	function getSysbarXMLDocs($filename)
	{
		$xmlDocs = null;
		if (!empty($filename) && @is_file($filename))
		{
			$xmlDocs = domxml_open_file($filename);
		}
		return $xmlDocs;
	}

// }}} ��ƫŧi end


// {{{ �D�{�� begin

	$menu = array('academic', 'school', 'personal', 'teach', 'learn', 'direct');
	// �Ǯչw�]��
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
	// �Ǯճ]�w��
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

	// �Юv���� �ǥ����� (Begin)
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
	// �Юv���� �ǥ����� (End)

	// �ɮv���� (Begin)
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
	// �ɮv���� (End)
// }}} �D�{�� end

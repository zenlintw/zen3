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
	 * @copyright   2000-2005 SunNet Tech. INC.
	 * @version     CVS: $Id: msg_wysiwyg.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       
	 **/
	define('NO_TEMPLATE', true);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/editor.php');

	// �`�Ʃw�q begin
	// �`�Ʃw�q end

	// �ܼƫŧi begin
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
	// �ܼƫŧi end

	// ��ƫŧi begin
	// ��ƫŧi end

	// �D�{�� begin
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
	// �D�{�� end
?>
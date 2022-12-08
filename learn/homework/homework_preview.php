<?php
	/**
     * �ɮ׻��� 
     *	�@�~�w��
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
     * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
     * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
     *
     * @package     WM3
     * @subpackege  
     * @author      Edi Chen <edi@sun.net.tw>
     * @copyright   2000-2005 SunNet Tech. INC.
     * @version     CVS: $Id: homework_preview.php,v 1.1 2010/02/24 02:39:07 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2008-04-10
     */
// {{{ �禡�w�ޥ� begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/homework_learn.php');
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
// {{{ �禡�w�ޥ� end

// {{{ �D�{�� begin
	$content = dbGetOne('WM_qti_homework_test', 'content', 'exam_id=' . intval($_SERVER['argv'][0]));
	if (!$content) die('homework not exist!');
	
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	
	$js = <<< EOF
	var examDetail = XmlDocument.create();
	var xmlHttp    = XmlHttp.create();
	
	examDetail.loadXML('{$content}');
	xmlHttp.open('POST', 'homework_display.php?preview=true', false);
	
	var ret  = xmlHttp.send(examDetail);
	if (ret == false)
	{
		document.write('System Error!');
	}
	else
	{
		document.write(xmlHttp.responseText);
	}
EOF;
	showXHTML_script('inline', $js);
// {{{ �D�{�� end
?>
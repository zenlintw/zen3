<?php
   /**
    * /�줽��/�ҵ{�޲z/�ҵ{²��/�M�κ����˪O
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
    * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
    * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: cour_intro_template.php,v 1.1 2010/02/24 02:40:29 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-13
    */

// {{{ �禡�w�ޥ� begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/cour_introduce.php');
	require_once(sysDocumentRoot . '/lib/editor.php');
	require_once(sysDocumentRoot . '/teach/introduce/cour_intro_lib.php');
// }}} �禡�w�ޥ� end

// {{{ �`�Ʃw�q begin

// }}} �`�Ʃw�q end
    
// {{{ �ܼƫŧi begin
    $cour_intro = array('cour_intro', 'cour_arrange', 'teach_intro');
// }}} �ܼƫŧi end

// {{{ ��ƫŧi begin
	
// }}} ��ƫŧi end

// {{{ �D�{�� begin
	if (empty($_POST['func']) || !in_array($_POST['func'], $cour_intro))
		die('Illegal Access!');
	
	switch ($_POST['func']) {
		case 'cour_intro' :
			$intro_type = 'C';
			$sysSession->cur_func = 800100100;
			break;
		case 'cour_arrange' :
			$intro_type = 'R';
			$sysSession->cur_func = 800200100;
			break;
		case 'teach_intro' :
			$intro_type = 'T';
			$sysSession->cur_func = 800300100;
			break;
	}
	$sysSession->restore();
	
	list($content) = dbGetStSr('WM_term_introduce', 'content', 'course_id=' . $sysSession->course_id . ' and intro_type="'.$intro_type.'"', ADODB_FETCH_NUM);
	
	if (!empty($content))
		$content = getContent($content, 'template');
	if (empty($content))	// �p�G�������ƩΪ̨��쪺���content�]����,�h���w�]����
		$content = $MSG[$_POST['func'].'_content'][$sysSession->lang];
		
	
	showXHTML_head_B('');
	    $xajax_save_temp->printJavascript('/lib/xajax/');
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_head_E();
	showXHTML_body_B();
		$ary = array(array($MSG[$_POST['func']][$sysSession->lang] . '_' . $MSG['intro_template'][$sysSession->lang]));
		echo "<center>\n";
		showXHTML_tabFrame_B($ary, 1, 'mainForm', 'table1', 'action="cour_intro_save.php" method="POST" style="display: inline" onsubmit="xajax_clean_temp(st_id);"');
			showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="700" style="border-collapse: collapse" class="cssTable"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('');
						$oEditor = new wmEditor;
						$oEditor->setValue(stripslashes($content));
						$oEditor->addContType('isHTML', 1);
						$oEditor->generate('content', 700, 450);
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('');
						showXHTML_input('hidden', 'func', $_POST['func'], '', '');	// �Ұ��檺�O���ӥ\��
						showXHTML_input('hidden', 'type', 'template', '', '');
						showXHTML_input('submit', '', $MSG['save'][$sysSession->lang], '', 'class="cssBtn"');
						showXHTML_input('button', '', $MSG['back'][$sysSession->lang], '', 'onclick="xajax_clean_temp(st_id); location.replace(\'cour_introduce.php\');" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo "</center>\n";
		echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
	showXHTML_script('inline', "
	var st_id = '{$sysSession->cur_func}{$sysSession->course_id}';
    xajax_check_temp(st_id, 'FCK.editor');
	window.setInterval(function(){xajax_save_temp(st_id, editor.getHTML());}, 100000);
");
	showXHTML_body_E();
// }}} �D�{�� end

?>

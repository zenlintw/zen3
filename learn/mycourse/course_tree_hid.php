<?php
	/**
	 * 我的課程 - 課程群組
	 *
	 * @since   2003/06/05
	 * @author  ShenTing Lin
	 * @version $Id: course_tree_hid.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/mycourse.php');

	showXHTML_head_B('');
		showXHTML_script('inline', "
	var cs_status = new Array(
		'{$MSG['cs_state_close'][$sysSession->lang]}',
		'{$MSG['cs_state_open_a'][$sysSession->lang]}',
		'{$MSG['cs_state_open_a_date'][$sysSession->lang]}',
		'{$MSG['cs_state_open_n'][$sysSession->lang]}',
		'{$MSG['cs_state_open_n_date'][$sysSession->lang]}',
		'{$MSG['cs_state_prepare'][$sysSession->lang]}');

	var theme = '{$sysSession->theme}/{$sysSession->env}';
	var ticket = '{$ticket}';
");
		showXHTML_script('include', '/lib/xmlextras.js');
		showXHTML_script('include', 'course_tree.js');
	showXHTML_head_E();
?>

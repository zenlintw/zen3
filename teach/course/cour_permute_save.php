<?php
	/**
	 * Àx¦s¶¶§Ç
	 *
	 * @since   2004/01/30
	 * @author  ShenTing Lin
	 * @version $Id: cour_permute_save.php,v 1.1 2010/02/24 02:40:23 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700500200';
	$sysSession->restore();
	if (!aclVerifyPermission(700500200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$nids = explode(',', $_POST['nodeids']);
	$min  = min($nids);

	foreach ($nids as $val) {
		$nid = intval($val);
		dbSet('WM_term_subject', "permute={$min}", "node_id={$nid}");
		$min++;
	}

	showXHTML_head_B($MSG['msg_permute_save'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B('');
	showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
		showXHTML_tr_B('');
			showXHTML_td_B('');
				showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
					showXHTML_tr_B('');
						showXHTML_td_B('');
			echo "<img onselectstart=\"return false;\" id=\"ImgL2\" MyAttr=\"2\" tabsIdx=\"2\" ".
				"src=\"/theme/{$sysSession->theme}/{$sysSession->env}/title_on_01.gif\" width=\"25\" height=\"30\" border=\"0\" ".
				"align=\"absbottom\">";
						showXHTML_td_E('');
						showXHTML_td_B("align='center' valign='bottom' nowrap='NoWrap' id='TitleID2' MyAttr='2' tabsIdx='2' style='cursor: default; background-image: url(\"/theme/{$sysSession->theme}/{$sysSession->env}/title_on_02.gif\");' class='cssTabs' onselectstart='return false;'");
						  echo "<span id=\"tab_send\">{$MSG['msg_permute_save'][$sysSession->lang]}</span>";
						showXHTML_td_E('');
						showXHTML_td_B('');
						  echo "<img onselectstart='return false;' id='ImgR2' MyAttr='2' tabsIdx='2' src='/theme/{$sysSession->theme}/{$sysSession->env}/title_on_03.gif' width='28' height='30' border='0' align='absbottom'>";
						showXHTML_td_E('');
					showXHTML_tr_E('');
				showXHTML_table_E('');
			showXHTML_td_E('');
		showXHTML_tr_E('');

		showXHTML_tr_B('');
			showXHTML_td_B('class="bg01"');
			showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="box01"');
				showXHTML_tr_B('class="bg02 font01"');
				showXHTML_tr_E('');
				showXHTML_tr_B('class="bg04 font01"');
				    showXHTML_td_B('nowrap="nowrap"');
					echo $MSG['msg_permute_save'][$sysSession->lang]. "<br><br>";
					showXHTML_input('button','btnOK',$MSG['msg_ok'][$sysSession->lang],'','onclick="window.close()"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');

		showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E('');

	showXHTML_body_E('');

?>

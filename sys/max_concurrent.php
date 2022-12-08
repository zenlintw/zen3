<?php
	/**
	 * Guest ннию
	 *
	 * @author  ShenTing Lin
	 * @version $Id: max_concurrent.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/login.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission(400400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$js = <<< BOF
	function GoHome() {
		window.location.replace("/");
	}
BOF;

	setTicket();
	setcookie('Ticket', $sysSession->ticket, time()+3600);

	$content = '';
	ob_start();
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/sys/sys.css");
		showXHTML_script('inline', $js);

		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('class="bgColor01"');
				showXHTML_td('width="100%" colspan="2" align="left" valign="middle" nowrap class="font01"', '&nbsp;&nbsp;&nbsp;&nbsp;' . $MSG['label_sysmsg'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="bgColor03"');
				showXHTML_td('', '&nbsp;&nbsp;&nbsp;&nbsp;');
				showXHTML_td_B('width="90%" class="bgColor04"');
					showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
						showXHTML_tr_B('class="bgColor03"');
						    $msg = str_replace('%max_limit%',sysMaxConcurrentUser, $MSG['msg_max_concurrent'][$sysSession->lang]);
							showXHTML_td('align="left" class="font06" colspan="2"', $msg);
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('colspan="2" align="center" valign="middle" nowrap class="bgColor02"');
					$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";
					echo showButton('button', $MSG['btn_return'][$sysSession->lang], $image, 'class="cssBtn1" onclick="GoHome();"');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		$content = ob_get_contents();
	ob_end_clean();

	$title = (defined('GUEST_LIMIT')) ? $MSG['title_limit'][$sysSession->lang] : $MSG['title_allow'][$sysSession->lang];
	layout($title, $content);
?>
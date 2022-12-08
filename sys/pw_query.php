<?php
	/**
	 * 查詢帳號密碼 
	 *
	 * @author  ShenTing Lin
	 * @version $Id: pw_query.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/pw_query.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');


    // mooc 模組開啟的話將網頁導向index.php
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        header('Location: /mooc/index.php');
        exit;
    }

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$js = <<< BOF
	function GoHome() {
		window.location.replace("/");
	}

	function checkData() {
		var node = document.getElementById("actForm");
		if (node == null) return false;
		if (node.query_user.value == "") {
			alert("{$MSG['msg_username'][$sysSession->lang]}");
			node.query_user.focus();
			return false;
		}

		if (node.email.value == "") {
			alert("{$MSG['msg_email'][$sysSession->lang]}");
			node.email.focus();
			return false;
		}

		return true;
	}
BOF;

	setTicket();
	setcookie('Ticket', $sysSession->ticket, time()+3600);

	$content = '';
	ob_start();
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/sys/sys.css");
		showXHTML_script('inline', $js);
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_form_B('method="post" action="pw_query1.php" onsubmit="return checkData();"', 'actForm');
			showXHTML_tr_B('class="bgColor01"');
				showXHTML_td('width="100%" colspan="2" align="left" valign="middle" nowrap class="font01"', '&nbsp;&nbsp;&nbsp;&nbsp;' . $MSG['query_pwd'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="bgColor03"');
				showXHTML_td('', '&nbsp;&nbsp;&nbsp;&nbsp;');
				showXHTML_td_B('width="90%" class="bgColor04"');
					showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
							showXHTML_tr_B('class="bgColor05"');
								showXHTML_td('align="left" class="font06"', $MSG['username'][$sysSession->lang]);
								showXHTML_td_B('width="80%"');
									showXHTML_input('text', 'query_user', '',   '', 'class="box03" size="20"');
								showXHTML_td_E('');
							showXHTML_tr_E('');
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td('align="left" class="font06"', 'E-mail：');
								showXHTML_td_B('width="80%"');
									showXHTML_input('text', 'email', '',   '', 'class="box03" size="60"');
								showXHTML_td_E('');
							showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('colspan="2" align="center" valign="middle" nowrap class="bgColor02"');
					$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";
					echo showButton('submit', $MSG['query'][$sysSession->lang], $image, 'class="cssBtn1"'), '&nbsp;',
					     showButton('button', $MSG['home'][$sysSession->lang] , $image, 'class="cssBtn1" onclick="GoHome();"');
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_form_E('');
		showXHTML_table_E('');
		$content = ob_get_contents();
	ob_end_clean();

	layout($MSG['title_01'][$sysSession->lang], $content);
?>

<?php
	/**
	 * 查詢帳號密碼
	 *
	 * @author  ShenTing Lin
	 * @version $Id: login_fault.php,v 1.1 2010/02/24 02:40:19 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/pw_query.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');

	$xajax_rgk = new xajax('/sys/door/re_gen_loginkey.php');
	$xajax_rgk->registerFunction('reGenLoginKey');

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
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

	window.onload = function () {
		var node = document.getElementById("loginForm");
		if (node) node.password.focus();
	};
BOF;

	setTicket();
	setcookie('Ticket', $sysSession->ticket, time()+3600);

	$_SERVER['argv'][0] = preg_replace('/[^\w.-]+/', '', $_SERVER['argv'][0]);
	$content = '';
	$login_js = str_replace(sysDocumentRoot, '', getTemplate('login.js'));
	ob_start();
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/sys/sys.css");
		showXHTML_script('include', '/lib/base64.js');
		showXHTML_script('include', '/lib/des.js');
		showXHTML_script('include', '/lib/md5.js');
		showXHTML_script('inline', $js);
		showXHTML_script('include', $login_js);

		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('class="bgColor01"');
				showXHTML_td('width="100%" colspan="2" align="left" valign="middle" nowrap class="font04"', '&nbsp;&nbsp;&nbsp;&nbsp;<font color="#FFFFFF">' . $MSG['login_fail'][$sysSession->lang] . '</font>');
			showXHTML_tr_E('');
			// *************************************
			showXHTML_tr_B('class="bgColor03"');
				showXHTML_td('', '&nbsp;&nbsp;&nbsp;&nbsp;');
					showXHTML_td_B('width="90%" class="bgColor04"');
						showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
							showXHTML_form_B('method="post" action="' . (defined('WM_SSL') ? ('https://' . $_SERVER['HTTP_HOST']) : '') . '/login.php" onsubmit="return checkLogin();"', 'loginForm');
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td('colspan="2" class="font06"', '<b>' . $MSG['relogin'][$sysSession->lang] . '</b>');
							showXHTML_tr_E('');
							showXHTML_tr_B('class="bgColor05"');
								showXHTML_td('align="left" class="font06"', $MSG['username'][$sysSession->lang]);
								showXHTML_td_B('width="80%"');
									showXHTML_input('text', 'username', $_SERVER['argv'][0],   '', 'class="box03" size="20"');
								showXHTML_td_E('');
							showXHTML_tr_E('');
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td('align="left" class="font03"', $MSG['passwd'][$sysSession->lang]);
								showXHTML_td_B('width="80%"');
									showXHTML_input('password', 'password', '', '', 'class="box03"');
								showXHTML_td_E('');
							showXHTML_tr_E('');

							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td_B('colspan="2" align="left" valign="middle" nowrap class="bgColor02"');
									$image = "/theme/{$sysSession->theme}/sys/button.gif";

									showXHTML_input('submit', '', $MSG['login'][$sysSession->lang], '', 'class="cssBtn" ');
									echo '&nbsp;';
									showXHTML_input('reset', '', $MSG['reset'][$sysSession->lang], '', 'class="cssBtn" ');
									echo '&nbsp;';
									showXHTML_input('button', '', $MSG['home'][$sysSession->lang], '', 'class="cssBtn" onclick="GoHome();"');

									$uid = md5(uniqid(rand(),1));
									$login_key = md5(sysSiteUID . sysTicketSeed . $uid);
									dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
									dbNew('WM_prelogin', 'login_seed,uid,log_time', "'$login_key','$uid',NOW()");
									showXHTML_input('hidden', 'login_key', $login_key, '', '');
									showXHTML_input('hidden', 'encrypt_pwd', '', '', '');
								showXHTML_td_E('');
							showXHTML_tr_E('');

						showXHTML_form_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
			// **************************************
			showXHTML_tr_B('class="bgColor03"');
				showXHTML_td('', '&nbsp;&nbsp;&nbsp;&nbsp;');
					showXHTML_td_B('width="90%" class="bgColor04"');
						showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
							showXHTML_form_B('method="post" action="pw_query1.php" onsubmit="return checkData();"', 'actForm');
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td('colspan="2" class="font06"', '<b>' . $MSG['query_pwd'][$sysSession->lang] . '</b>');
							showXHTML_tr_E('');
							showXHTML_tr_B('class="bgColor05"');
								showXHTML_td('align="left" class="font06"', $MSG['username'][$sysSession->lang]);
								showXHTML_td_B('width="80%"');
									showXHTML_input('text', 'query_user', $_SERVER['argv'][0],   '', 'class="box03" size="20"');
								showXHTML_td_E('');
							showXHTML_tr_E('');
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td('align="left" class="font06"', 'E-mail：');
								showXHTML_td_B('width="80%"');
									showXHTML_input('text', 'email', '',   '', 'class="box03" size="60"');
								showXHTML_td_E('');
							showXHTML_tr_E('');

							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td_B('colspan="2" align="left" valign="middle" nowrap class="bgColor02"');
									$image = "/theme/{$sysSession->theme}/sys/button.gif";
									showXHTML_input('submit', '', $MSG['query'][$sysSession->lang], '', 'class="cssBtn" ');
									echo '&nbsp;';
									showXHTML_input('button', '', $MSG['home'][$sysSession->lang], '', 'class="cssBtn" onclick="GoHome();"');
									$ticket = md5('PWD_Query' . $sysSession->ticket . $sysSession->username . $sysSession->school_host . $sysSession->school_id);
									showXHTML_input('hidden', 'ticket', $ticket, '', '');
							showXHTML_td_E('');
							showXHTML_tr_E('');

						showXHTML_form_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');

		showXHTML_table_E('');

		echo $xajax_rgk->getJavascript('/lib/xajax/') . '<script>function init_rlk(){window.setInterval("xajax_reGenLoginKey()", 1200000);} if (document.attachEvent) window.attachEvent("onload", init_rlk); else window.addEventListener("load", init_rlk, false);</script>';
		$content = ob_get_contents();
	ob_end_clean();

	layout($MSG['title_01'][$sysSession->lang], $content);
?>

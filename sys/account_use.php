<?php
	/**
	 * ±b¸¹¬O§_±Ò¥Î
	 *
	 * @author  Amm Lee
	 * @version $Id: account_use.php,v 1.1 2010/02/24 02:40:19 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/login.php');
	require_once(sysDocumentRoot . '/lang/pw_query.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');

	$xajax_rgk = new xajax('/sys/door/re_gen_loginkey.php');
	$xajax_rgk->registerFunction('reGenLoginKey');

	$sysSession->cur_func = '400300700';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$js = <<< BOF
	var MSG_NEED_USERNAME = "{$MSG['msg_fill_username'][$sysSession->lang]}";
	var MSG_NEED_PASSWORD = "{$MSG['msg_fill_password'][$sysSession->lang]}";

	function GoHome() {
		window.location.replace("/mooc/index.php");
	}

BOF;

	setTicket();
	setcookie("Ticket", $sysSession->ticket, time()+3600);

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
				showXHTML_td('width="100%" colspan="2" align="left" valign="middle" nowrap class="font04"', '&nbsp;&nbsp;&nbsp;&nbsp;' . $MSG['account_enable'][$sysSession->lang]);
			showXHTML_tr_E('');
			// *************************************
			showXHTML_tr_B('class="bgColor03"');
				showXHTML_td('', '&nbsp;&nbsp;&nbsp;&nbsp;');
				showXHTML_td_B('width="90%" class="bgColor04"');
					showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
						showXHTML_form_B('method="post" action="' . (defined('WM_SSL') ? ('https://' . $_SERVER['HTTP_HOST']) : '') . '/login.php" onsubmit="return checkLogin();"', 'loginForm');
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td('colspan="2" class="font06"', $MSG['relogin'][$sysSession->lang]);
							showXHTML_tr_E();
							showXHTML_tr_B('class="bgColor05"');
								showXHTML_td('align="left" class="font06"', $MSG['username'][$sysSession->lang]);
								showXHTML_td_B('width="80%"');
									showXHTML_input('text', 'username', '',   '', 'class="box03" size="20"');
								showXHTML_td_E();
							showXHTML_tr_E();
							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td('align="left" class="font03"', $MSG['passwd'][$sysSession->lang]);
								showXHTML_td_B('width="80%"');
									showXHTML_input('password', 'password', '', '', 'class="box03"');
								showXHTML_td_E();
							showXHTML_tr_E();

							showXHTML_tr_B('class="bgColor03"');
								showXHTML_td_B('colspan="2" align="left" valign="middle" nowrap class="bgColor02"');
									$image = "/theme/{$sysSession->theme}/sys/button.gif";
									showXHTML_input('submit', '', $MSG['login'][$sysSession->lang], '', 'class="cssBtn" ');
									echo '&nbsp;';
									showXHTML_input('reset' , '', $MSG['reset'][$sysSession->lang], '', 'class="cssBtn" ');
									echo '&nbsp;';
									showXHTML_input('button', '', $MSG['home'][$sysSession->lang], '', 'class="cssBtn" onclick="GoHome();"');

									$uid = md5(uniqid(rand(),1));
									$login_key = md5(sysSiteUID . sysTicketSeed . $uid);
									dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
									dbNew('WM_prelogin', 'login_seed,uid,log_time', "'$login_key','$uid',NOW()");
									showXHTML_input('hidden', 'login_key', $login_key, '', '');
									showXHTML_input('hidden', 'encrypt_pwd', '', '', '');
								showXHTML_td_E();
							showXHTML_tr_E();

						showXHTML_form_E();
					showXHTML_table_E();
				showXHTML_td_E();
			showXHTML_tr_E();

		showXHTML_table_E();

		echo $xajax_rgk->getJavascript('/lib/xajax/') . '<script>function init_rlk(){window.setInterval("xajax_reGenLoginKey()", 1200000);} if (document.attachEvent) window.attachEvent("onload", init_rlk); else window.addEventListener("load", init_rlk, false);</script>';
		$content = ob_get_contents();
	ob_end_clean();

	layout($MSG['account_enable'][$sysSession->lang], $content);
?>

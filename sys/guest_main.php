<?php
	/**
	 * Guest ннию
	 *
	 * @author  ShenTing Lin
	 * @version $Id: guest_main.php,v 1.1 2010/02/24 02:40:19 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/guest.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');

	$xajax_rgk = new xajax('/sys/door/re_gen_loginkey.php');
	$xajax_rgk->registerFunction('reGenLoginKey');

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission(400400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!defined('GUEST_LIMIT') && !defined('GUEST_DENY')) {
		header('Location: /');
		die();
	}

	$js = <<< BOF
	var MSG_NEED_USERNAME = "{$MSG['msg_fill_username'][$sysSession->lang]}";
	var MSG_NEED_PASSWORD = "{$MSG['msg_fill_password'][$sysSession->lang]}";

	function GoHome() {
		window.location.replace("/");
	}

	function GoLearn() {
		window.location.replace("/learn/");
	}

	window.onload = function () {
		var obj = document.getElementById("username");
		if (obj != null) obj.focus();
	};
BOF;

	setTicket();
	setcookie('Ticket', $sysSession->ticket, time()+3600);

	$content = '';
	$login_js = str_replace(sysDocumentRoot, '', getTemplate('login.js'));
	ob_start();
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/sys/sys.css");
		showXHTML_script('include', '/lib/base64.js');
		showXHTML_script('include', '/lib/des.js');
		showXHTML_script('include', '/lib/md5.js');
		showXHTML_script('inline', $js);
		showXHTML_script('include', $login_js);

		showXHTML_form_B('method="post" action="' . (defined('WM_SSL') ? ('https://' . $_SERVER['HTTP_HOST']) : '') . '/login.php" onsubmit="return checkLogin();" style="display: inline;"', 'loginForm');
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('class="bgColor01"');
				$msg = (defined('GUEST_LIMIT')) ? $MSG['msg_over_limit'][$sysSession->lang] : $MSG['msg_not_allow'][$sysSession->lang];
				showXHTML_td('width="100%" colspan="2" align="left" valign="middle" nowrap class="font01"', '&nbsp;&nbsp;&nbsp;&nbsp;' . $msg);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="bgColor03"');
				showXHTML_td('', '&nbsp;&nbsp;&nbsp;&nbsp;');
				showXHTML_td_B('width="90%" class="bgColor04"');
					showXHTML_table_B('width="100%" cellpadding="5" border="0" cellspacing="1"');
						if (defined('GUEST_LIMIT')) {
							showXHTML_tr_B('class="bgColor05"');
								showXHTML_td_B('align="left" class="font06" colspan="2"');
									showXHTML_input('button', 'btn_learn', $MSG['btn_try'][$sysSession->lang],   '', 'class="cssBtn" onclick="GoLearn();"');
									echo $MSG['msg_use_guest'][$sysSession->lang];
								showXHTML_td_E();
							showXHTML_tr_E('');
						}
						showXHTML_tr_B('class="bgColor03"');
							showXHTML_td('align="left" class="font06" colspan="2"', $MSG['msg_relogin'][$sysSession->lang]);
						showXHTML_tr_E('');
						showXHTML_tr_B('class="bgColor05"');
							showXHTML_td('align="left" class="font06"', $MSG['th_username'][$sysSession->lang]);
							showXHTML_td_B('width="80%"');
								showXHTML_input('text', 'username', '',   '', 'id="username" class="box03" size="20"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
						showXHTML_tr_B('class="bgColor03"');
							showXHTML_td('align="left" class="font06"', $MSG['th_password'][$sysSession->lang]);
							showXHTML_td_B('width="80%"');
								showXHTML_input('password', 'password', '',   '', 'id="password" class="box03" size="20"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('colspan="2" align="center" valign="middle" nowrap class="bgColor02"');
					$uid = md5(uniqid(rand(),1));
					$login_key = md5(sysSiteUID . sysTicketSeed . $uid);
					dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
					dbNew('WM_prelogin', 'login_seed,uid,log_time', "'$login_key','$uid',NOW()");

					showXHTML_input('hidden', 'login_key'  , $login_key, '', '');
					showXHTML_input('hidden', 'encrypt_pwd', ''        , '', '');
					$image = "/theme/{$sysSession->theme}/sys/button_02_b.gif";
					echo showButton('submit', $MSG['btn_login'][$sysSession->lang], $image, 'class="cssBtn1"');
					echo '&nbsp;';
					echo showButton('reset' , $MSG['btn_reset'][$sysSession->lang], $image, 'class="cssBtn1"');
					echo '&nbsp;';
					echo showButton('button', $MSG['btn_return'][$sysSession->lang], $image, 'class="cssBtn1" onclick="GoHome();"');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		showXHTML_form_E('');

		echo $xajax_rgk->getJavascript('/lib/xajax/') . '<script>function init_rlk(){window.setInterval("xajax_reGenLoginKey()", 1200000);} if (document.attachEvent) window.attachEvent("onload", init_rlk); else window.addEventListener("load", init_rlk, false);</script>';
		$content = ob_get_contents();
	ob_end_clean();

	$title = (defined('GUEST_LIMIT')) ? $MSG['title_limit'][$sysSession->lang] : $MSG['title_allow'][$sysSession->lang];
	layout($title, $content);
?>

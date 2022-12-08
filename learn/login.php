<?php
	/**
	 * 從學生環境登入
	 *
	 * @since   2004/06/01
	 * @author  ShenTing Lin
	 * @version $Id: login.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/login.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');

	$xajax_rgk = new xajax('/sys/door/re_gen_loginkey.php');
	$xajax_rgk->registerFunction('reGenLoginKey');

	$sysSession->cur_func = '600200100';
	$sysSession->restore();
	if (!aclVerifyPermission(600200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($sysSession->username != 'guest') {
		// 跳出你已經登入的訊息
		$js = <<< BOF
	window.onload = function () {
		alert("{$MSG['msg_is_login'][$sysSession->lang]}");
		window.close();
	};
BOF;
	showXHTML_head_B($MSG['login'][$sysSession->lang]);
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
	showXHTML_body_E();
		die();
	}

	$uid = md5(uniqid(rand(),1));
	$login_key = md5(sysSiteUID . sysTicketSeed . $uid);
	dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
	dbNew('WM_prelogin', 'login_seed,uid,log_time', "'$login_key','$uid',NOW()");

	$js = <<< BOF
	var MSG_NEED_USERNAME = "{$MSG['msg_fill_username'][$sysSession->lang]}";
	var MSG_NEED_PASSWORD = "{$MSG['msg_fill_password'][$sysSession->lang]}";

	function checkData() {
		var node = document.getElementById("loginForm");
		if (node == null) return false;

		if (typeof(window.opener) != 'undefined') {
			if (typeof(window.opener.parent.s_main.getCheckedCourse) != "undefined") {
				node.course_ids.value = window.opener.parent.s_main.getCheckedCourse();
			} else {
				node.course_ids.value = "";
			}
		}

		return checkLogin();
	}

	window.onload = function () {
		var obj = document.getElementById("username");
		if (obj != null) obj.focus();
	};

BOF;

	$login_js = str_replace(sysDocumentRoot, '', getTemplate('login.js'));

	showXHTML_head_B($MSG['login'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/base64.js');
	showXHTML_script('include', '/lib/des.js');
	showXHTML_script('include', '/lib/md5.js');
	showXHTML_script('inline', $js);
	showXHTML_script('include', $login_js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['login'][$sysSession->lang], 'tabs1');
		showXHTML_tabFrame_B($ary, 1, 'loginForm', 'logTab', 'autocomplete="off" method="post" action="' . (defined('WM_SSL') ? ('https://' . $_SERVER['HTTP_HOST']) : '') . '/login.php" onsubmit="return checkData();" style="display: inline;"');
			showXHTML_table_B('width="250" border="0" cellspacing="1" cellpadding="3" id="tabs1" class="box01"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('align="right" valign="top" nowrap="nowrap"', $MSG['username'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('text', 'username', '', '', 'id="username" class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td('align="right" valign="top" nowrap="nowrap"', $MSG['passwd'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('password', 'password', '', '', 'id="password" class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="center" colspan="2" nowrap="nowrap"');
						showXHTML_input('hidden', 'referer_source'        , 'pass_from_learn'     , '', '');
						showXHTML_input('hidden', 'course_ids', ''        , '', '');
						showXHTML_input('hidden', 'guest_login_act', 'major_add'        , '', '');
						showXHTML_input('hidden', 'login_key'  , $login_key, '', '');
						showXHTML_input('hidden', 'encrypt_pwd', ''        , '', '');
						showXHTML_input('submit', '', $MSG['login'][$sysSession->lang], '', 'id="btnLogin" class="cssBtn" onclick=""');
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E();
		showXHTML_tabFrame_E();

		echo $xajax_rgk->getJavascript('/lib/xajax/') . '<script>function init_rlk(){window.setInterval("xajax_reGenLoginKey()", 1200000);} if (document.attachEvent) window.attachEvent("onload", init_rlk); else window.addEventListener("load", init_rlk, false);</script>';
	showXHTML_body_E();
?>

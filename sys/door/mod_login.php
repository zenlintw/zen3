<?php
	/**
	 * 登入模組
	 *
	 *     所需樣板名稱：login.htm
	 *
	 * @since   2004/10/27
	 * @author  ShenTing Lin
	 * @version $Id: mod_login.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');

	$xajax_rgk = new xajax('/sys/door/re_gen_loginkey.php');
	$xajax_rgk->registerFunction('reGenLoginKey');

	$sysSession->cur_func = '1300100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function mod_login() {
		global $_SERVER, $sysSession, $MSG, $lang, $xajax_rgk;
		
		// 產生登入用的 key (Begin)
		$uid = md5(uniqid(rand(),1));
		$login_key = md5(sysSiteUID . sysTicketSeed . $uid);
		dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
		dbNew('WM_prelogin', 'login_seed,uid,log_time', "'{$login_key}','{$uid}',NOW()");
		// 產生登入用的 key (End)
		// 取得學校設定 (Begin)
		list($reg, $guest) = dbGetStSr('WM_school', 'canReg, guest', "school_id={$sysSession->school_id} AND school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);
		// 取得學校設定 (End)

		$tpl = getTemplate('login.htm');
		$myTemplate = new Wise_Template($tpl);

	// 切換登入狀態 (Begin)			
		if ($sysSession->username == 'guest') {
			// 尚未登入
			$myTemplate->add_replacement('<%DIV_LOGOUT_BEGIN%>+<%DIV_LOGOUT_END%>', '', true);
			$myTemplate->add_replacement('<%DIV_LEARN_BEGIN%>+<%DIV_LEARN_END%>'  , '', true);
			$myTemplate->add_replacement('<%DIV_LOGIN_BEGIN%>', '');
			$myTemplate->add_replacement('<%DIV_LOGIN_END%>'  , '');
			$myTemplate->add_replacement('<%DIV_QUERY_BEGIN%>', '');
			$myTemplate->add_replacement('<%DIV_QUERY_END%>'  , '');
		} else {
			// 已經登入
			$myTemplate->add_replacement('<%DIV_GUEST_BEGIN%>+<%DIV_GUEST_END%>'  , '', true);
			$myTemplate->add_replacement('<%DIV_LOGIN_BEGIN%>+<%DIV_LOGIN_END%>'  , '', true);
			$myTemplate->add_replacement('<%DIV_LOGOUT_BEGIN%>'    , '');
			$myTemplate->add_replacement('<%DIV_LOGOUT_END%>'      , '');
			$myTemplate->add_replacement('<%DIV_LEARN_BEGIN%>'     , '');
			$myTemplate->add_replacement('<%DIV_LEARN_END%>'       , '');
		}
		// 切換登入狀態 (End)
		// 允不允許註冊
		if ($reg != 'N') {
			$myTemplate->add_replacement('<%DIV_REGISTER_BEGIN%>', '');
			$myTemplate->add_replacement('<%DIV_REGISTER_END%>'  , '');
		} else {
			$myTemplate->add_replacement('<%DIV_REGISTER_BEGIN%>+<%DIV_REGISTER_END%>', '', true);
		}
		// 目前平台不允許 guest 可參考 bug report NO.519
		// 允不允許參觀者登入
		if ($guest == 'Y') {
			$myTemplate->add_replacement('<%DIV_GUEST_LOGIN_BEGIN%>', '');
			$myTemplate->add_replacement('<%DIV_GUEST_LOGIN_END%>'  , '');
		} else {
			$myTemplate->add_replacement('<%DIV_GUEST_LOGIN_BEGIN%>+<%DIV_GUEST_LOGIN_END%>', '', true);
		}

		$myTemplate->add_replacement('<%LOGIN_KEY%>'         , $login_key);

		$myTemplate->add_replacement('<%MSG_USERNAME%>'      , $MSG['th_username'][$lang]);
		$myTemplate->add_replacement('<%MSG_PASSWORD%>'      , $MSG['th_password'][$lang]);
		$myTemplate->add_replacement('<%MSG_WELCOME%>'       , $MSG['msg_welcome'][$lang]);
		$myTemplate->add_replacement('<%BTN_QUERY_PASSWORD%>', $MSG['btn_query_password'][$lang]);
		$myTemplate->add_replacement('<%BTN_REGISTER%>'      , $MSG['btn_register'][$lang]);
		$myTemplate->add_replacement('<%BTN_GUEST%>'         , $MSG['btn_guest'][$lang]);		
		$myTemplate->add_replacement('<%BTN_LOGOUT%>'        , $MSG['btn_logout'][$lang]);

		$myTemplate->add_replacement('<%MSG_FILL_USERNAME%>', $MSG['msg_fill_username'][$lang]);
		$myTemplate->add_replacement('<%MSG_FILL_PASSWORD%>', $MSG['msg_fill_password'][$lang]);
		
		// 判斷 WM_sch4user 的 login_times 是否為 0 且是否為 第一次登入
		if ($sysSession->username == 'guest') {
			$myTemplate->add_replacement('<%GO_WHERE%>', "location.replace('/learn/index.php');");
			$myTemplate->add_replacement('<%BTN_GO_LEARN%>'      , $MSG['btn_go_learn'][$lang]);
		}else{
			list($login_times) = dbGetStSr('WM_sch4user','login_times','school_id=' . $sysSession->school_id . ' and username="' . $sysSession->username . '"', ADODB_FETCH_NUM);
			if($login_times == 0) { // 個人基本資為料未填寫完成
				$myTemplate->add_replacement('<%GO_WHERE%>', "location.replace('/sys/reg/step3.php');");	
				$myTemplate->add_replacement('<%BTN_GO_LEARN%>'      , $MSG['go_fill_info'][$lang]);
			}else{
				$myTemplate->add_replacement('<%GO_WHERE%>', "location.replace('/learn/index.php');");
				$myTemplate->add_replacement('<%BTN_GO_LEARN%>'      , $MSG['btn_go_learn'][$lang]);
			}			
		}
		
		if (defined('WM_SSL'))
		{
		    $myTemplate->add_replacement('action="login.php"', 'action="https://' . $_SERVER['HTTP_HOST'] . '/login.php"');
		}

		genDefaultTrans($myTemplate);
		return $xajax_rgk->getJavascript('/lib/xajax/') . $myTemplate->get_result(false) .
		'<script>function init_rlk(){window.setInterval("xajax_reGenLoginKey()", 1200000);} if (document.attachEvent) window.attachEvent("onload", init_rlk); else window.addEventListener("load", init_rlk, false);</script>';
	}

?>

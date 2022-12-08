<?php
	/**
	 * �n�J�Ҳ�
	 *
	 *     �һݼ˪O�W�١Glogin.htm
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
		
		// ���͵n�J�Ϊ� key (Begin)
		$uid = md5(uniqid(rand(),1));
		$login_key = md5(sysSiteUID . sysTicketSeed . $uid);
		dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
		dbNew('WM_prelogin', 'login_seed,uid,log_time', "'{$login_key}','{$uid}',NOW()");
		// ���͵n�J�Ϊ� key (End)
		// ���o�Ǯճ]�w (Begin)
		list($reg, $guest) = dbGetStSr('WM_school', 'canReg, guest', "school_id={$sysSession->school_id} AND school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);
		// ���o�Ǯճ]�w (End)

		$tpl = getTemplate('login.htm');
		$myTemplate = new Wise_Template($tpl);

	// �����n�J���A (Begin)			
		if ($sysSession->username == 'guest') {
			// �|���n�J
			$myTemplate->add_replacement('<%DIV_LOGOUT_BEGIN%>+<%DIV_LOGOUT_END%>', '', true);
			$myTemplate->add_replacement('<%DIV_LEARN_BEGIN%>+<%DIV_LEARN_END%>'  , '', true);
			$myTemplate->add_replacement('<%DIV_LOGIN_BEGIN%>', '');
			$myTemplate->add_replacement('<%DIV_LOGIN_END%>'  , '');
			$myTemplate->add_replacement('<%DIV_QUERY_BEGIN%>', '');
			$myTemplate->add_replacement('<%DIV_QUERY_END%>'  , '');
		} else {
			// �w�g�n�J
			$myTemplate->add_replacement('<%DIV_GUEST_BEGIN%>+<%DIV_GUEST_END%>'  , '', true);
			$myTemplate->add_replacement('<%DIV_LOGIN_BEGIN%>+<%DIV_LOGIN_END%>'  , '', true);
			$myTemplate->add_replacement('<%DIV_LOGOUT_BEGIN%>'    , '');
			$myTemplate->add_replacement('<%DIV_LOGOUT_END%>'      , '');
			$myTemplate->add_replacement('<%DIV_LEARN_BEGIN%>'     , '');
			$myTemplate->add_replacement('<%DIV_LEARN_END%>'       , '');
		}
		// �����n�J���A (End)
		// �������\���U
		if ($reg != 'N') {
			$myTemplate->add_replacement('<%DIV_REGISTER_BEGIN%>', '');
			$myTemplate->add_replacement('<%DIV_REGISTER_END%>'  , '');
		} else {
			$myTemplate->add_replacement('<%DIV_REGISTER_BEGIN%>+<%DIV_REGISTER_END%>', '', true);
		}
		// �ثe���x�����\ guest �i�Ѧ� bug report NO.519
		// �������\���[�̵n�J
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
		
		// �P�_ WM_sch4user �� login_times �O�_�� 0 �B�O�_�� �Ĥ@���n�J
		if ($sysSession->username == 'guest') {
			$myTemplate->add_replacement('<%GO_WHERE%>', "location.replace('/learn/index.php');");
			$myTemplate->add_replacement('<%BTN_GO_LEARN%>'      , $MSG['btn_go_learn'][$lang]);
		}else{
			list($login_times) = dbGetStSr('WM_sch4user','login_times','school_id=' . $sysSession->school_id . ' and username="' . $sysSession->username . '"', ADODB_FETCH_NUM);
			if($login_times == 0) { // �ӤH�򥻸ꬰ�ƥ���g����
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

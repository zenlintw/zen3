<?php
	/**
	 *  登入的 IP 是否有被擋除
	 *
	 * @author  Amm Lee
	 * @version $Id: ip_deny.php,v 1.1 2010/02/24 02:40:19 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/pw_query.php');
	require_once(sysDocumentRoot . '/sys/syslib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '600300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	ob_start();
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/sys/sys.css");
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" align="center"');
				showXHTML_tr_B();
					showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor01"', '&nbsp;');
				showXHTML_tr_E();
				showXHTML_tr_B();
					showXHTML_td('width="100%" height="200" align="center" valign="middle" nowrap style="color : #FF0000"', $MSG['permit_check'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B();
					$image = "/theme/{$sysSession->theme}/sys/button.gif";
					$btn = showButton('button', $MSG['home'][$sysSession->lang], $image, 'onclick="location.replace(\'/\');"');
					showXHTML_td('width="100%" align="center" valign="middle" nowrap class="bgColor02"', $btn);
				showXHTML_tr_E();
			showXHTML_table_E();
		$content = ob_get_contents();
	ob_end_clean();

	layout($MSG['permit_check'][$sysSession->lang], $content);
?>

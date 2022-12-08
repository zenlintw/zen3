<?php
	/**
	 * ¡° ­×§ï¯Ü®z±K½X
	 *
	 * @since   2004/09/21
	 * @author  Wiseguy Liang
	 * @version $Id: weak_passwd1.php,v 1.1 2010/02/24 02:38:55 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/weak_passwd.php');

	if (strlen($_POST['ticket']) != 32 ||
	    $_POST['ticket'] != md5($_POST['username'] . sysTicketSeed)
	   ){
	   	wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'others', $_SERVER['PHP_SELF'], 'Illegal Access (Fake ticket)!');
		die('Illegal Access (Fake ticket).');
	}

	if (check_pwd($_POST['username'], $_POST['passwd1']))
	{
		// header(sprintf('Location: /weak_passwd.php?%s+%s', $_POST['username'], md5(sysTicketSeed . $_POST['username'])));
		echo <<< EOB
<script>
alert('{$MSG['loginAgain'][$sysSession->lang]}');
history.back();
</script>
EOB;
		exit;
	}
	else
	{
		if ($_POST['username'] == sysRootAccount) die(sysRootAccount . ' is not allowed to change password here.');
		$RS2 = dbSet('WM_user_account', sprintf('password="%s"', md5($_POST['passwd1'])), "username='{$_POST['username']}'");
		if ($RS2) $RS = dbSet('WM_all_account', sprintf('password="%s"', md5($_POST['passwd1'])), "username='{$_POST['username']}'");
		header('Location: /');
		exit;
	}
?>

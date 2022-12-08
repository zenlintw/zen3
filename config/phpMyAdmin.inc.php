<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
if (!$sysConn->GetOne('select count(*) from ' . sysDBname . '.WM_manager where username="' . $sysSession->username . '" and level=' . $sysRoles['root']))
    die('Root permission only.');
ob_end_clean();
unset($MSG, $Sqls, $sysConn, $sysRoles);

$_SESSION['PMA_Config']->settings['PmaAbsoluteUri'] = 'http://' . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] == '80' ? '' : ":{$_SERVER['SERVER_PORT']}") . '/academic/dbcs/';

$_SESSION['PMA_Config']->settings['Servers'][1]['host']     = sysDBhost;
$_SESSION['PMA_Config']->settings['Servers'][1]['user']     = sysDBaccoount;
$_SESSION['PMA_Config']->settings['Servers'][1]['password'] = sysDBpassword;
$_SESSION['PMA_Config']->settings['Servers'][1]['only_db']  = array(sysDBname, sysDBschool, sysDBprefix . '10001', sysDBprefix . '10002');

switch(strtolower($sysSession->lang)){
	case 'big5':	$_SESSION['PMA_Config']->settings['DefaultLang'] = 'zhtw-utf-8'; // 'zh-tw-utf-8';
			break;
	case 'gb2312':	$_SESSION['PMA_Config']->settings['DefaultLang'] = 'zh-utf-8'; // 'zh-utf-8';
			break;
	case 'en':		$_SESSION['PMA_Config']->settings['DefaultLang'] = 'en-utf-8'; // 'en-utf-8';
			break;
	case 'euc-jp': 	$_SESSION['PMA_Config']->settings['DefaultLang'] = 'ja-utf-8';
			break;
	default:		$_SESSION['PMA_Config']->settings['DefaultLang'] = 'zhtw-utf-8'; // 'zh-tw-utf-8';
			break;
}
$_SESSION['PMA_Config']->settings['Lang'] = $_SESSION['PMA_Config']->settings['DefaultLang'];
$_SESSION['PMA_Config']->settings['DefaultCharset'] = 'utf-8';

if ($GLOBALS['db'] == '') $GLOBALS['db'] = sysDBschool;
?>

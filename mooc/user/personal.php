<?php
/**
 * 個人資料設定
 * $Id: personal.php,v 1.1 2010/02/24 02:39:05 saly Exp $
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lang/personal.php');

if ($sysSession->username == 'guest') {
	header('Location: /mooc/index.php');
	exit;
}
$userDetailData = getUserDetailData($sysSession->username);

setTicket();
$ticket = md5($sysSession->username . $sysSession->school_id . $sysSession->ticket);
$smarty->assign('ticket',$ticket);

$smarty->assign('userDetailData', $userDetailData);
$realname = checkRealname($userDetailData['first_name'], $userDetailData['last_name']);
$smarty->assign('realname', $realname);
$smarty->assign('user_lang', $sysSession->lang);

// output
$smarty->display('user/personal.tpl');
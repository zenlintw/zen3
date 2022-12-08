<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/user.php');
require_once(sysDocumentRoot . '/lib/login/login.inc');
require_once(sysDocumentRoot . '/lib/username.php');

$idx = $_GET['idx'];

$rsUser = new user();
$active = $rsUser->isEmailExists($idx);

// 設定訊息
switch ($active['code']) {
    case '1':
        // 驗證成功並直接登入
        // 建立個人ini
        setUserIni($active['username']);
        // 移除原cookie中idx舊的session資料
        removeExpiredSessionIdx($_COOKIE['idx']);
        // 移除之前同一位使用者的ftp認證設定資料
        removeExpiredFtpAuth();
        // 重設新的idx資料
        $userinfo = $rsUser->getProfileByUsername($active['username']);
        $idx = $sysSession->init($userinfo);
        $_COOKIE['idx'] = $idx;
        $sysSession->restore();
        setcookie('school_hash',$_COOKIE['school_hash']);

        $type = 2;
        break;

    case '2':
        $type = 3;
        break;

    case '3':
        $type = 4;
        break;

    case '5':
        $type = 12;
        break;

    case '4':
    case '0':
    default:
        $type = 5;
        break;
}

noCacheRedirect($baseUrl . '/mooc/message.php?type=' . $type);

// DEBUG時可用
$smarty->assign('message', $active);
$smarty->display('active.tpl');


//驗證帳號密碼是否正確, 並取得使用者的資訊
function &getUserInfo($user)
{
	global $UsersValidedByWM3;

	//檢查使用者是否不經帳號中心驗證
	if (in_array($user,$UsersValidedByWM3))
	{
		return getUserInfoFromWM3($user);
	}

	//依據設定的帳號中心取得使用者資料
	switch(AccountCenter)
	{
		case "WM3":
			return getUserInfoFromWM3($user);
			break;
		case "LDAP":
			include_once(sysDocumentRoot . '/lib/login/AccountCenter_LDAP.class');
			$obj = new AccountCenter_LDAP();
			return $obj->getUserInfo($user);
			break;
		case "OTHDB":
			include_once(sysDocumentRoot . '/lib/login/AccountCenter_OTHDB.class');
			$obj = new AccountCenter_OTHDB();
			return $obj->getUserInfo($user);
			break;
		case "WEBSERVICES":
			include_once(sysDocumentRoot . '/lib/login/AccountCenter_WS.class');
			$obj = new AccountCenter_WS();
			return $obj->getUserInfo($user);
		default:
			return getUserInfoFromWM3($user);
			break;
	}
}
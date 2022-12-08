<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/lib/fb_login_lib.php');
require_once(sysDocumentRoot . '/lang/register.php');

// 在https連線時，由session_start()建立的cookie也要設定secure
if ($_SERVER['HTTPS']){
    ini_set("session.cookie_secure","1");
}

session_start();
if($_SERVER['REMOTE_ADDR'] !== $_SESSION['LAST_REMOTE_ADDR'] || $_SERVER['HTTP_USER_AGENT'] !== $_SESSION['LAST_USER_AGENT']) {
   session_destroy();
}

session_regenerate_id();

$_SESSION['LAST_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['LAST_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
unset($_SESSION['facebook_user']);

/**
 * 回首頁
 */
function gotoHome() {
    if (empty($_COOKIE['fb_reurl']) === TRUE) {
        header('Location: /mooc/index.php');
    } else {
        $href = $_COOKIE['fb_reurl'];
        unset($_COOKIE['fb_reurl']);
        setcookie('fb_reurl', '', 0, '/', '');
        header('Location:'.$href);
    }
}

/**
 * 回登入頁面
 */
function gotoLogin() {
    header('Location: /mooc/login.php');
    exit();
}

// 已經登入，重導回 MOOC 首頁
if ($sysSession->username !== 'guest') {
    gotoHome();
}

// 使用者拒絕，重導回登入頁面
if (isset($_GET['error']) && ($_GET['error'] === 'access_denied')) {
    gotoLogin();
}
// 沒有 code
if (!isset($_GET['code'])) {
    gotoLogin();
}

// 取得 Access Token
$code = $_GET['code'];
$data = array(
    'client_id'     => FB_APP_ID,
    'redirect_uri'  => $baseUrl . '/mooc/fb_login.php',
    'client_secret' => FB_APP_SECRET,
    'code'          => $code
);

$uri = 'https://graph.facebook.com/oauth/access_token';
$content = getRemoteData($uri, $data, 'GET', $status);
preg_match('/"access_token":"(\w+)"/', $content, $match);
$decContent = json_decode($content);

if (empty($_COOKIE['show_me_info']) === false) {
    echo '<pre>';
    var_dump($decContent);
    var_dump($decContent->error);
    var_dump($decContent->access_token);
    echo '</pre>';
}

if ($decContent->error) {
    gotoLogin();
}
$token = $decContent->access_token;

// 取得使用者資料
$data = array(
    'access_token' => $token
);
$uri = 'https://graph.facebook.com/me';
$content = getRemoteData($uri, $data, 'GET', $status);
$user = json_decode($content);

// 檢查使用者是否已存在 WMPro 內
$username = fbid2Username($user->id);
if ($username !== false) {
    setLoginInfo($username);
    gotoHome();
}

// 進入設定使用者基本畫面
session_start();
$_SESSION['facebook_user'] = $user;
$user_limit3 = str_replace(
    '%FIRSTCHR%',
    $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang],
    $MSG['msg_js_03'][$sysSession->lang]
);

if (is_null($user->username)) {
    $ary = explode('@', $user->email);
    $username = $ary[0];
} else {
    $username = $user->username;
}
$username = str_replace('.', '-', $username);

// 產生 login key
$uid      = md5(uniqid(rand(), 1));
$loginKey = md5(sysSiteUID . sysTicketSeed . $uid);
dbDel('WM_prelogin', 'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) > 1200');
dbNew('WM_prelogin', 'login_seed, uid, log_time', "'$loginKey', '$uid', NOW()");

$smarty->assign(array(
    'user_limit'  => $user_limit3,
    'fbRealname'  => $user->name,
    'fbUsername'  => $username,
    'loginKey'    => $loginKey
));
$smarty->display('fb_login.tpl');
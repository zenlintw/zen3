<?php
/**
 * 登入判斷的流程
 *     1. 帳號密碼是否正確 (WM_[school_id].WM_user_account)
 *     2. 帳號是否啟用 (WM_[school_id].WM_user_account)
 *     3. 是否有重複登入 (WM_[school_id].WM_session)
 *     4. 登入的 IP 是否有被擋除 (WM_[school_id].???????)
 *     5. 帳號是否超過使用期限 (WM_MASTER.WM_sch4user)
 *     6. 是否為第一次登入 (檢查 WM_MASTER.WM_sch4user 的 login_times 若為零則是第一次登入，管理者變身的不列入登入次數)
 *
 * 登入後的動作
 *     1. 將登入的次數加一
 *
 * @todo
 *     1. 紀錄 Log
 *     2. 版面
 *
 * @author  ShenTing Lin
 * @version $Id: login.php,v 1.1 2010/02/24 02:38:55 saly Exp $
 **/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/config/login.config');
require_once(sysDocumentRoot . '/lib/login/login.inc');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/co_mooc.php'); // mooc專用函數


if (strlen($_POST['password']) > 50) {
	header('HTTP/1.1 403 Forbidden');
    exit;
}

// $sysConn->debug = true;
#====================== functio ==============================
//驗證帳號密碼是否正確, 並取得使用者的資訊
function &getUserInfo($user)
{
    global $UsersValidedByWM3;

    //檢查使用者是否不經帳號中心驗證
    if (in_array($user, $UsersValidedByWM3)) {
        return getUserInfoFromWM3($user);
    }

    //依據設定的帳號中心取得使用者資料
    switch (AccountCenter) {
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

// 是否要與帳號中心同步資料, 需要userinfo資料，此資料為全域變數-命名式陣列
function syncUserInfo()
{
    global $userinfo, $syncFields;
    if (!is_array($userinfo)) return false;
    $upArray = array(); //更新字串的陣列
    foreach ($userinfo as $field => $val) {
        if (in_array($field, $syncFields)) {
            $upArray[] = sprintf("%s='%s'", $field, mysql_escape_string($val));
        }
    }
    if (count($upArray) == 0) return false;

    $upstr = implode(',', $upArray);

    dbSet('WM_user_account', $upstr, "username='{$userinfo['username']}'");
    dbSet('WM_all_account', $upstr, "username='{$userinfo['username']}'");
}

#====================== 登入主程序 ============================
/***** 0.檢查上線人數是否已達上限 *******/
if (sysMaxConcurrentUser != 0) // 0表示無限制
    {
    list($now_courrent_num) = dbGetStSr('WM_session', 'count(*)', '`chance`=0', ADODB_FETCH_NUM);
    if ($now_courrent_num >= sysMaxConcurrentUser) {
        exit_func('/sys/max_concurrent.php');
    }
}
/***** 1.啟始程序 **********/
// 移除舊的 sysSession
// dbDel('WM_session', "idx='{$_COOKIE['idx']}'");

//啟始參數
$_POST['username']  = trim($_POST['username']);
$_POST['login_key'] = trim($_POST['login_key']);

// 手機掃未登入者的Qrcode，使其登入
if (isset($_GET['spotlight'])&&!empty($_GET['spotlight'])){
    $qrcodeParams = sysNewDecode($_GET['spotlight'],'SunWm51');
    if (($qrcodeParams === FALSE) || (empty($qrcodeParams))){
        header('LOCATION: /mooc/index.php');
        exit;
    }

    $qrcodeParams = unserialize($qrcodeParams);
    if (time()-intval($qrcodeParams[2]) > 180){
        header('LOCATION: /mooc/message.php?type=206');
        exit;
    }

    //掃描手機的session是未登入
    if ($sysSession->username == 'guest'){
        header('LOCATION: /mooc/message.php?type=201');
        exit;
    }

    // 被掃描的session已是登入身份
    $targetUsername = dbGetOne('WM_session','username',sprintf("idx='%s'",mysql_escape_string($qrcodeParams[0])));
    // 找不到被掃描的session
    if (empty($targetUsername)) {
        header('LOCATION: /mooc/message.php?type=202');
        exit;
    }

    // 目標的Qrcode已是登入身份
    if ($targetUsername != 'guest'){
        header('LOCATION: /mooc/message.php?type=203');
        exit;
    }

    dbSet(
        'WM_session',
        sprintf("`username`='%s',`realname`='%s',`email`='%s'",$sysSession->username,$sysSession->realname,$sysSession->email),
        sprintf("idx='%s'",mysql_escape_string($qrcodeParams[0]))
    );

    header('LOCATION: /mooc/message.php?type=223');
    exit;
}

// 手機掃已登入者的Qrcode, 使手機本身登入
if (isset($_GET['movelight'])&&!empty($_GET['movelight'])){
    $qrcodeParams = sysNewDecode($_GET['movelight'],'SunWm51');
    if (($qrcodeParams === FALSE) || (empty($qrcodeParams))){
        header('LOCATION: /mooc/index.php');
        exit;
    }

    $qrcodeParams = unserialize($qrcodeParams);

    if (time()-intval($qrcodeParams[2]) > 180){
        header('LOCATION: /mooc/message.php?type=206');
        exit;
    }

    // 被掃描的session是未登入身份
    $targetUsername = dbGetOne('WM_session','username',sprintf("idx='%s'",mysql_escape_string($qrcodeParams[0])));
    // 找不到被掃描的session
    if (empty($targetUsername)) {
        header('LOCATION: /mooc/message.php?type=202');
        exit;
    }

    // 提供被掃描登入的Qrcode其目前身份是guest
    if ($targetUsername == 'guest'){
        header('LOCATION: /mooc/message.php?type=204');
        exit;
    }

    //掃描手機的session是已登入身份，提示訊息
    if ($sysSession->username != 'guest'){
        if ($sysSession->username == $targetUsername){
            header('LOCATION: /mooc/message.php?type=221');
            exit;
        }else{
            header('LOCATION: /mooc/message.php?type=205');
            exit;
        }
    }

    // 參數內的username與WM_session所存的不相符
    if ($targetUsername != $qrcodeParams[1]) {
        header('LOCATION: /mooc/index.php');
        exit;
    }

    $targetUserData = dbGetRow('WM_session','username,realname,email',sprintf("idx='%s'",mysql_escape_string($qrcodeParams[0])));
    dbSet(
        'WM_session',
        sprintf("`username`='%s',`realname`='%s',`email`='%s'",$targetUserData['username'],$targetUserData['realname'],$targetUserData['email']),
        sprintf("idx='%s'",mysql_escape_string($_COOKIE['idx']))
    );

    header('LOCATION: /mooc/message.php?type=222');
    exit;
}

// 保持登入 - 登入程序
$isPersistLoginSuccessfully = false;    //是否由persist_idx登入的
if (($sysSession->username == 'guest') &&
    (!empty($_COOKIE["persist_idx"])) &&
    (empty($_POST['username'])) &&
    (empty($_POST['login_key']))
)
{
    if (validPersistIdx($_COOKIE["persist_idx"])){
        $persistRow = dbGetRow('WM_persist_login','*',sprintf("persist_idx='%s' and expire_time>NOW()",mysql_escape_string($_COOKIE["persist_idx"])));
        $_POST['username'] = $persistRow['username'];
        $_POST['login_key'] = md5($_POST['username']);
        $userinfo = dbGetStSr('WM_user_account', '*', 'username="' . $_POST['username'] . '"', ADODB_FETCH_ASSOC);
        // 確認WM_user_account有此帳號
        if ($userinfo['username'] == $_POST['username']){
            $isPersistLoginSuccessfully = true;
        }
    }

    // 無法順利登入成功，則清除persist_idx的cookie值，導向首頁
    if (!$isPersistLoginSuccessfully){
        // 清除 persist_idx
        setcookie('persist_idx', '', time()-3600, '/', '', $http_secure);
        // 清除 WM_persist_login
        dbDel('WM_persist_login', sprintf("persist_idx='%s'",mysql_escape_string($_COOKIE["persist_idx"])));
        header('LOCATION: /index.php');
        exit;
    }

}else{
    /***** 2.wmpro5 標準登入驗證程序 **********/
    // 2.1 檢查此request是否由首頁登入

    if (!checkLoginKey()) exit_func('/index.php');

    // 2.2 驗證帳號密碼是否正確, 並取得使用者的資訊
    $userinfo = getUserInfo($_POST['username']);

    // 2.3 檢查脆弱密碼
    /*
    判斷邏輯：假如是用 WM3 認證，且沒有定義 isCheckWeakPassword 或定義值不是 false 就檢查脆弱密碼，
    換句話說，如果是用 WM3 認證，要停用檢查脆弱密碼 的話，就要有 define('isCheckWeakPassword', false);
    */
    if (AccountCenter == "WM3" && (!defined('isCheckWeakPassword') || !in_array(strtolower(isCheckWeakPassword), array('false', false, 'n', 0, ''), true))) {
        if (check_pwd($_POST['username'], $_POST['password'])) {
            // MOOC 重設密碼，先使用 forget 的流程運作，未來有需求再拆開
            $activeCode = md5(uniqid(rand()));
            if ($activeCode !== '') {
                $res_no = setForgetValidCode($userinfo['username'], $userinfo['email'], $activeCode);
            }
            if ($res_no == 1) {
                exit_func(sprintf($baseUrl . '/mooc/resetpwd.php?idx=%s&usr=%s', $activeCode, urlencode(base64_encode($userinfo['username']))));
            } else {
                // 設定重設密碼驗證碼失敗的話，跑 PRO 重設密碼頁
                setLoginProcLog(9, 'incorrect password');
                exit_func(sprintf('/weak_passwd.php?%s+%s', $_POST['username'], md5(sysTicketSeed . $_POST['username'])));
            }
        }
    }
}



// 2.4 帳號是否啟用
if ($userinfo['enable'] == 'N') {
    setLoginProcLog(5, 'user not yet be enabled');
    exit_func('/sys/account_use.php');
}

// 2.5 登入的 IP 是否有被擋除
if (isDenyFromThat($_POST['username'])) {
    setLoginProcLog(6, 'access denied');
    exit_func('/sys/ip_deny.php');
}

// 2.6 是否有重複登入
$RS = dbGetStSr('WM_school', 'multi_login', "school_id={$sysSession->school_id} AND school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_ASSOC);
if ($RS['multi_login'] == 'N') {
    list($times) = dbGetStSr('WM_session', 'count(*) AS times', "username='{$_POST['username']}'", ADODB_FETCH_NUM);
    if ($times > 0) {
        // 若有重複登入，則將前一個登入者登出
        dbDel('WM_session', "username='{$_POST['username']}'");
        dbDel('WM_auth_samba', "username='{$_POST['username']}'");
        dbDel('WM_auth_ftp', "userid='{$_POST['username']}'");
    }
}


// 2.7 驗證帳號使用期限與登入次數
isUserAccountExpired();

// 2.8 是否要與帳號中心進行資料同步
if (AccountCenter != "WM3" &&
    isSyncUserData == 'Y' &&
    !in_array($_POST['username'], $UsersValidedByWM3))
    syncUserInfo();

// 2.9 登入成功
setLoginProcLog(0, 'login success');

/***** 3.驗證成功之後準備程序 **********/
//3.1 建立個人ini
setUserIni($_POST['username']);
//3.2 移除原cookie中idx舊的session資料
removeExpiredSessionIdx($_COOKIE['idx']);
//3.3 移除之前同一位使用者的ftp認證設定資料
removeExpiredFtpAuth();
//3.4 重設新的idx資料
$idx            = $sysSession->init($userinfo);
$_COOKIE['idx'] = $idx;
$sysSession->restore();

if ($_SERVER['HTTPS']) {
    $http_secure = true;
} else {
    $http_secure = false;
}

//3.5 記住我，保持登入 => 寫入WM_MASTER.WM_persist_login資料表
if ($isPersistLoginSuccessfully){
    //持續登入，自動登入進來的
    // 刪除舊的
    dbDel('WM_persist_login',sprintf("persist_idx='%s'",mysql_escape_string($_COOKIE["persist_idx"])));

    // 建立新的
    $newPidx = createPersistData($idx, $userinfo['username']);
    setcookie('persist_idx', $newPidx, time() + 2592000, '/', '', $http_secure);

    // 保持登入成功，留在首頁
    exit_func('/mooc/index.php');
}else{
    // 正常登入畫面進來的
    if ((intval($_POST['persist_login']) === 1) && ($_POST['username'] != sysRootAccount)){
        // 建立新的
        $newPidx = createPersistData($idx, $userinfo['username']);
        setcookie('persist_idx', $newPidx, time() + 2592000, '/', '', $http_secure);
    }else{
        // 重新登入時，且不想保持登入的情況下，卻發現有persist_idx的cookie值，且要清除之前的
        if (isset($_COOKIE["persist_idx"])&&!empty($_COOKIE["persist_idx"])) {
            // 清除 persist_idx
            setcookie('persist_idx', '', time()-3600, '/', '', $http_secure);
            // 清除 WM_persist_login
            dbDel('WM_persist_login', sprintf("persist_idx='%s'",mysql_escape_string($_COOKIE["persist_idx"])));
        }
    }
}

setcookie('wm_lang', $userinfo['language'], time() + 86400, '/', '', $http_secure);
setcookie('school_hash', $_COOKIE['school_hash'], time() + 86400, '/', '', $http_secure);
/* [MOOC](B) #57892 另外儲存 idx 便於 SSO 到入口網校及內容商  2014/12/19 By Spring */
// 目前是截取第一個點以後的domain，當遇到不規則 Domain 時可能會需要修正
$fdm = explode('.', $_SERVER['SERVER_NAME']);
$dm  = str_replace($fdm[0] . '.', '', $_SERVER['SERVER_NAME']);
// 如果是此學校為入口網校或是內容商，取入口網校ID
setcookie('sIdx', $sysSession->school_id . $_COOKIE['idx'], time() + 60 * 60 * 24 * 3, '/', $dm, $http_secure);
/* [MOOC](E) #57892 */

// 若是因為操作筆記分享而登入的，則重新導到接收筆記分享的網址去
if (isset($_COOKIE['noteAction']) && $_COOKIE['noteAction'] === 'receive-share-note' && isset($_COOKIE['shareNoteKey'])) {
    exit_func('/xmlapi/index.php?action=receive-share-note&share-key=' . $_COOKIE['shareNoteKey']);
}

// irs學生登入成功
if (isset($_POST['irsGoto'])){
    exit_func('/mooc/irs/check.php?action=start&goto='.$_POST['irsGoto']);
}
// Qrcode掃描點名報到，學生登入成功
if (isset($_POST['rollcallGoto'])){
    exit_func('/mooc/teach/rollcall/start.php?goto='.$_POST['rollcallGoto']);
}

/******4. 依據使用者的角色導入學習環境，若是首次登入者，則要求填個人資料 ****/

// #80752 管理者在登入時 也要加入WM_auth_ftp
if (aclCheckRole($_POST['username'], $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] ) | aclCheckRole($_POST['username'], $sysRoles['manager'] | $sysRoles['administrator'] | $sysRoles['root'])) {
    $cnt = $sysConn->GetOne("select count(*) from WM_auth_ftp where userid = '{$_POST['username']}'");
    if ($cnt==0) {
        dbNew('WM_auth_ftp', 'userid,passwd,home', "'{$_POST['username']}',ENCRYPT('{$_POST['password']}'),'/nonexistent'");
    }
}

// // 6. 是否為第一次登入 (檢查 WM_MASTER.WM_sch4user 的 login_times 若為零則是第一次登入，管理者變身的不列入登入次數)
// if ((isFirstLogin($_POST['username']) || isUserDataIncomplete($_POST['username'])) && $_POST['username'] != sysRootAccount) {
// // 導到填寫個人資料
// exit_func('/sys/reg/step3.php');
// exit;
// } else {
// 這邊還要加上，是要導到管理者、導師或教師跟學生

// mooc 模組未開啟的話將網頁導向learn/index.php
if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
    // 從課程報名處登入會將簡介頁網址放在 reurl 後 POST 過來，登入成功轉回原課程網頁
    if (isset($_POST['reurl']) && $_POST['reurl'] !== '') {
        if (strpos($_POST['reurl'], $_SERVER['HTTP_HOST'])) {
            $http           = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $_POST['reurl'] = str_replace($http . '://' . $_SERVER['HTTP_HOST'] . '/', '', $_POST['reurl']);
            exit_func($_POST['reurl']);
        } else {
            exit_func('/mooc/index.php');
        }
    }

    // 使用手機登入
    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
    $detect = new Mobile_Detect;
    if ($detect->isMobile() && !$detect->isTablet()) {
        exit_func('/mooc/index.php');
    }

    // PC或平台登入
    exit_func('/learn/index.php');
}

//  計算此使用者總共有幾個特殊身分
$temp_env = 0;

// 教師
if (aclCheckRole($_POST['username'], $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'])) {
    $temp_env++;
}
// 導師
if (aclCheckRole($_POST['username'], $sysRoles['director'])) {
    $temp_env++;
}

// 管理者
chkSchoolId('WM_manager');
$cm = $sysConn->GetOne("select count(*) from WM_manager where username = '{$_POST['username']}' and (school_id = {$sysSession->school_id} or level & {$sysRoles['root']})");
if ($cm > 0) {
    $temp_env++;
}
/*
 * 流程：首頁 -> 搜尋& 選課 -> 允許 guest -> 進入 [個人區 - 我的課程 - 全校課程]
 *       選完課之後 -> 幫 user login -> 送出選課清單
 */
$pass_from_learn = (isset($_POST['referer_source']) && (trim($_POST['referer_source']) == 'pass_from_learn'));

if (intval($temp_env) >= 2) { // 俱有 2 個以上的特殊身分
    if ($pass_from_learn) {
        login_func($_POST['guest_login_act'], $_POST['course_ids'], '/mooc/index.php');
    } else {
        exit_func('/mooc/index.php');
    }
} else {
    if ($ct > 0) {
        $sysSession->env      = 'teach';
        $sysSession->cur_func = 10010102;
        $sysSession->restore();
        if ($pass_from_learn) {
            login_func($_POST['guest_login_act'], $_POST['course_ids'], '/mooc/index.php');
        } else {
            exit_func('/mooc/index.php');
        }
    } elseif ($cd > 0) {
        if ($pass_from_learn) {
            login_func($_POST['guest_login_act'], $_POST['course_ids'], '/direct/index.php');
        } else {
            exit_func('/direct/index.php');
        }

    } elseif ($cm > 0) {
        if ($pass_from_learn) {
            login_func($_POST['guest_login_act'], $_POST['course_ids'], '/academic/index.php');
        } else {
            exit_func('/academic/index.php');
        }

    } else {
        if ($pass_from_learn) {
            login_func($_POST['guest_login_act'], $_POST['course_ids'], '/mooc/index.php');
        } else {
            exit_func('/mooc/index.php');
        }
    }
}
// }

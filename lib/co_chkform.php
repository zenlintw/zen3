<?php
/**
 * @author  $Author: cch
 * @version $Id: co_chkform.php,v 1.2 2014-02-24 cch Exp $
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/login/login.inc');

/**
 * checkUserEmail()
 *     檢查這個電子信箱是否可以使用
 *     檢查的動作：
 *         2. 是否已經有人使用了
 *         3. 檢查帳號的格式是否符合我們的要求
 * @param string $email 要檢查的帳號
 * @param boolean $onlyFormat 僅檢查電子信箱格式
 *
 * @return
 *     0 : 可以使用
 *     2 : 電子信箱使用中
 *     3 : 帳號格式不符合
 **/
function checkUserEmail($email, $onlyFormat = false, $username = '') {

    if ($email === '') {
        return 4;
    }

    // 電子信箱格式不符合
    $v = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
    if (!(preg_match($v, $email))) {
        return 3;
    }

    if ($onlyFormat) return 0;

//    if ($username !== '') {
//        $strUsername = "and username != '" . $username . "'";
//    }
//
//    // 電子信箱使用中
//    list($count) = dbGetStSr('WM_all_account', 'count(username)', "email='{$email}' ". $strUsername, ADODB_FETCH_NUM);
//    list($mooc_count) = dbGetStSr('CO_mooc_account', 'count(username)', "email='{$email}' " . $strUsername, ADODB_FETCH_NUM);
//
//    if (($count + $mooc_count) > 0) {
//        return 2;
//    }

    return 0;
}

/**
 * checkTmpAccount()
 *     檢查這個帳號是否存在CO_user_verify
 *     檢查的動作：
 *         1. 是否存在並尚未驗證
 * @param string $username 要檢查的帳號
 *
 * @return
 *     1 : 此帳號尚在驗證階段
 *     2 : 無此帳號
 **/
function checkTmpAccount($username = '') {
    $rtn = getUserInfoFromMooc($username);
    return $rtn;
}

// 由 Mooc 取得使用者資訊
function getUserInfoFromMooc($user)
{
    global $_POST;
    
    // 檢查圖形驗證碼
    if (defined('sysEnableCaptcha') && sysEnableCaptcha) {
        session_start();
        if (empty($_POST['captcha']) || ($_SESSION['captcha'] != $_POST['captcha'])) {            
            if (session_id()) {
                session_destroy();
            }
            $userInfo[0] = array('code' => -1);
            return $userInfo;
        }
    }
    if (session_id()) {
        session_destroy();
    }

    $userInfo[0] = array('code' => 2);
    
    // 取得 Mooc 暫存的密碼及 email
    list($password, $email) = dbGetStSr('CO_mooc_account', 'password, email', "username='" . $user ."' AND enable='N' ", ADODB_FETCH_NUM);
    if ($password === false){
        setLoginProcLog(3,"unknown account");
        return $userInfo;
    }
    $userpwd = $password;
    // 還原前端加密的密碼
    $key = substr($userpwd, 0, 4) . substr($_POST['login_key'], 0, 4);
    $pwd = @mcrypt_decrypt(MCRYPT_DES, $key, base64_decode($_POST['encrypt_pwd']), MCRYPT_MODE_ECB);
    // dbDel('WM_prelogin', "login_seed='{$_POST['login_key']}'");
    $_POST['password'] = trim($pwd);    
    if (md5($_POST['password']) === $userpwd) {
        $regStatus = getSchoolRegStatus();
        if ($regStatus === 'C') {
        	$userInfo[0] = array('code' => 3);
    	    return $userInfo;
        }
        $userInfo[0] = array('code' => 1,
                             'username' => urlencode(base64_encode($user)),
                             'email' => urlencode(base64_encode($email)),
                             'encemail' => urlencode(md5('xliame'.$email))
                            );
        return $userInfo;
    } 
    return $userInfo;
}
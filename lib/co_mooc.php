<?php
/**
 * @author  $Author: cch
 * @version $Id: co_mooc.php,v 1.2 2014-02-24 cch Exp $
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

/**
 * 設定電子信箱驗證碼
 *
 * @param string $username : 要新增的帳號
 * @param array  $code     : 驗證碼
 *
 * @return
 **/
function setEmailValidCode($username, $email, $code) {
    $username = trim($username);
    $email   = trim($email);

    dbDel('CO_user_verify', sprintf('`username`="%s" and `type` = "email"', $username));
    $r = dbNew(
        'CO_user_verify',
        '`type`, `username`, `email`, `reg_time`, `verify_code`, `verify_flag`',
        "'email', '{$username}', '{$email}', NOW(), '{$code}', 'N'"
    );

    if ($r !== false) {
        return 1;
    } else {
        return 0;
    }
}

/**
 * 設定忘記密碼通知信驗證碼
 *
 * @param string $username : 要新增的帳號
 * @param array  $code     : 驗證碼
 *
 * @return
 **/
function setForgetValidCode($username, $email, $code) {
    $username = trim($username);
    $email   = trim($email);

    dbDel('CO_user_verify', sprintf('`username`="%s" and `type` = "forget"', $username));
    $r = dbNew(
        'CO_user_verify',
        '`type`, `username`, `email`, `reg_time`, `verify_code`, `verify_flag`',
        "'forget', '{$username}', '{$email}', NOW(), '{$code}', 'N'"
    );

    if ($r !== false) {
        return 1;
    } else {
        return 0;
    }
}
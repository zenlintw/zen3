<?php
if (empty($_COOKIE['sys_lang']) === TRUE) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    $DefaultLang = sysDefaultLang;
    if (empty($DefaultLang)) $DefaultLang="Big5";
    setcookie('sys_lang', $DefaultLang, time() + 86400, '/');
} else {
    $DefaultLang = $_COOKIE['sys_lang'];
}
echo $DefaultLang;
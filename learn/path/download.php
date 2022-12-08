<?php

/* * ************************************************************************************************
 *
 * 		Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 *
 * 		Programmer: cch
 *              SA        : 914
 * 		Creation  : 2016/12/2
 * 		work for  : 下載教材
 * 		work on   : Apache 2.2.21, MySQL 5.1.59 , PHP 5.3.28
 *
 * ************************************************************************************************ */

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lang/learn_path.php');

// 網頁權限控制
$sysSession->cur_func = '1900200100';
$sysSession->restore();
if (!aclVerifyPermission(1900200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    
}

function sendHeaders($file, $type, $name = NULL) {
    if (empty($name)) {
        $name = preg_replace('/^(.+[\\/])|(\\/)/', '', $file);
    }
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Content-Transfer-Encoding: binary');
    header('Content-Disposition: attachment; filename="' . rawurlencode(preg_replace('/^(.+[\\/])|(\\/)/', '', $file)) . '";');
    header('Content-Type: ' . $type);
    header('Content-Length: ' . filesize($file));
}

// 下載區
$key = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
$realpath = trim(sysNewDecode(htmlspecialchars(rawurldecode($_GET['path'])), $key, true));
if (preg_match('/^\/base\/\d+\/course\/\d{8}\/content\/.+\.[a-zA-Z0-9]+$/', $realpath)) {
    if (file_exists(sysDocumentRoot . $realpath) === true) {
        $file = sysDocumentRoot . $realpath;
        sendHeaders($file, mime_content_type($file), rawurlencode(preg_replace('/^(.+[\\/])|(\\/)/', '', $realpath)));
        $chunkSize = 1024 * 1024;
        $handle = fopen($file, 'rb');
        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            ob_flush();
            flush();
        }
        fclose($handle);
        
        exit;
    } else {
        die(sprintf($MSG['msg_file_not_exist'][$sysSession->lang], preg_replace('/^(.+[\\/])|(\\/)/', '', $realpath)));
    }
} else {
    die('Access denied.');
}
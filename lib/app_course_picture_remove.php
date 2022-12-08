<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])){
    if ($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])){
        //
        $nodeFilename = $dom->get_elements_by_tagname('filename');
        $filename = $nodeFilename[0]->get_content();
    }

    $userBasePath = $userBasePath = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/app/', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
    if (!is_dir($userBasePath)) {
        // 沒有user 目錄
        die('noDir');
    }
    $file = $userBasePath . $filename;
    if (!file_exists($file)) {
        // 沒有檔案
        die('noFile');
    }

    // 刪除檔案
    unlink($file);
    if (!file_exists($file)) {
        // 確認刪除完畢
        die('success');
    }

    // 前面成功的部份皆未成功，最後回傳失敗
    die('fail');
}
?>

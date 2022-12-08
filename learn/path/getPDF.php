<?php
/**
 * ebook content
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
require_once(sysDocumentRoot . '/lib/course.php');
//require_once(sysDocumentRoot . '/lib/wda_common.php'); //WDA 客製共用函式庫

// pdf防直連
if (strpos($_SERVER['HTTP_REFERER'], 'id') === FALSE && preg_match('#/learn/path/viewPDF.php?id=#', $_SERVER['HTTP_REFERER']) === 0) {
    die('no permission');
}

$bookid = $_GET['id'];

$ticket = md5($sysSession->username . $sysSession->school_id . $sysSession->course_id . $_GET['id']);
if ($_GET['ticket'] != $ticket) {
    header('HTTP/1.1 403 Forbidden');
    die();
}

if (preg_match("|^/base/[\d]+/content/[\d]+/|", $bookid)) {
    $file = '../..' . $bookid;
} else {
    $localpath = sprintf('../../base/%05d/course/%08d/content/', $sysSession->school_id, $sysSession->course_id);
    $file      = $localpath . $bookid;
}


header("Content-type:application/pdf");
header("Content-Disposition:inline;filename='ebook.pdf'");
@readfile($file);
exit;
<?php
/*
 * 邏輯層：功能處理
 * 接收中介層參數經處理後，傳回中介層
 *
 * @since   2014/10/17
 * @author  cch
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

$ticket = md5(sysTicketSeed . QTI_which . $_COOKIE['idx'] . $_POST['exam_id']);

if (trim($_POST['ticket']) != $ticket) {
    die('access_deny');
}

$school_q = $sysConn->GetOne("select course_id={$sysSession->school_id} from WM_qti_".QTI_which."_test where exam_id={$_POST['exam_id']}") ? true : false;

if ($school_q) {
    $path = sprintf('%s/base/%05d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, QTI_which, $_POST['exam_id'], $sysSession->username);
} else {
    $path = sprintf('%s/base/%05d/course/%08d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id, QTI_which, $_POST['exam_id'], $sysSession->username);
}

$file_path = $path . $_POST['name'];
$file_path = stripslashes($file_path);
$success = is_file($file_path) && unlink($file_path);

wmSysLog(1700400202, $sysSession->course_id, $_POST['exam_id'], 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' delete file('.$_POST['name'].')');

echo $success;
<?php
/**
 * 下載所有作業的附檔
 *
 * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Jeff Wang <jeff@sun.net.tw>
 * @copyright   2000-2013 SunNet Tech. INC.
 * @version     CVS: $Id$
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2013-03-22
 * 
 * 備註：          
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

// 避免下載被中斷
set_time_limit(0);

include_once($_SERVER['DOCUMENT_ROOT'] . '/lib/archive_api.php');
if (empty($_POST['examinee'])) {
    $fname     = 'hw' . $_POST['exam_id'] . '.zip';
    $file_path = sysDocumentRoot . sprintf('/base/%5u/course/%8u/homework/A/%09u/', $sysSession->school_id, $sysSession->course_id, $_POST['exam_id']);
} else {
    $fname     = 'hw' . $_POST['examinee'] . '.zip';
    $file_path = sysDocumentRoot . sprintf('/base/%5u/course/%8u/homework/A/%09u/%s/', $sysSession->school_id, $sysSession->course_id, $_POST['exam_id'], $_POST['examinee']);
}
    
// 判斷檔案是否存在，以避免產生錯誤訊息塞爆主機的error_log檔
if (file_exists($file_path . 'Coursetemp.zip')) {

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path . 'Coursetemp.zip'));

    //ob_clean();
    //ob_end_flush();
    //readfile($file_path.'Coursetemp.zip');

    $chunkSize = 1024 * 1024;
    $handle    = fopen($file_path . 'Coursetemp.zip', 'rb');
    while (!feof($handle)) {
        $buffer = fread($handle, $chunkSize);
        echo $buffer;
        ob_flush();
        flush();
    }
    fclose($handle);

    //    $myzip = new CO_ZipArchive('', sysDocumentRoot . sprintf('/base/%5u/course/%8u/homework/A/%09u/%s', $sysSession->school_id, $sysSession->course_id, $_POST['exam_id'], $_POST['examinee']), true);
    if (file_exists($file_path)) {
        exec("/bin/rm {$file_path}/Coursetemp.zip");
    }
} else {
    echo "<script>alert('{$MSG['msg_path_error'][$sysSession->lang]}'); location.href='/teach/" . QTI_which . "/exam_correct_user.php?" . md5(sysTicketSeed . $sysSession->course_id . $_POST['exam_id']) . "+{$_POST['exam_id']}'</script>";
}
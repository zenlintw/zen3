<?php
/**
 * 匯出學員統計
 *
 * 建立日期：2004/05/04
 * @author  KuoYang Tsao
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/teach_student.php');
require_once(sysDocumentRoot . '/lib/archive_api.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

$sysSession->cur_func = '400500200';
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$export_filename = 'stud_export.' . preg_replace('/\W+/', '', $_POST['export_file']);
$export_content  = stripslashes(urldecode(base64_decode($_POST['export_data'])));
$fname           = 'stud_export.zip';
$temp_name       = md5(uniqid(rand(), true)) . '.zip';

if ($_POST['export_file'] == 'xml') {
    $export_content = preg_replace(array(
        '!<a\b[^>]*>([^<]*)</a>!isU',
        '!>\s+<!'
    ), array(
        '\1',
        '><'
    ), $export_content);
} else {
    $export_content = preg_replace(array(
        '!<a\b[^>]*>([^<]*)</a>!isU',
        '!>\s+<!',
        '!<a\b[^>]*>(<font\b[^>]*>[^<]*</font>[^<]*)</a>!isU'
    ), array(
        '\1',
        '><',
        '\1'
    ), $export_content);
    $export_content = str_replace('#EBF3DA', '#ECECEC', $export_content);
    $export_content = str_replace('#7EAB4E', '#0db9bb', $export_content);
}

// ** 產生 壓縮檔 begin **
header('Content-Disposition:attachment;filename="' . $fname . '"');
header('Content-Transfer-Encoding:binary');
header('Content-Type:application/zip;name="' . $fname . '"');

while (@ob_end_clean()); // 抑制防右鍵
$zip_lib = new ZipArchive_php4($temp_name, '', false, '');
$zip_lib->add_string($export_content, $export_filename);
$zip_lib->readfile();
$zip_lib->delete();
// ** 產生 壓縮檔 end **
<?php
/**
 * 將原先客製專用的名稱 CO_qti_exam_app 改為 APP專用的名稱 APP_qti_support_app
 * 並將原先的資料轉入新資料表去
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

$permitUser = array('sunnet', 'root');

if (!in_array($sysSession->username, $permitUser)) {
    die('Permission Denied');
}

// 建立資料表 - Begin
$sysConn->Execute('USE '.sysDBschool);
$sql = sprintf("SHOW TABLES WHERE Tables_in_%s='%s'", sysDBschool, 'APP_qti_support_app');
if (!$sysConn->GetOne($sql)) {
    // 建立新表
    if (is_file(sysDocumentRoot. '/app_install/APP_qti_support_app.sql')) {
        $sql = file_get_contents(sysDocumentRoot. '/app_install/APP_qti_support_app.sql');
        $sysConn->Execute($sql);

        echo '<font color="green">' . APP_qti_support_app . ' is created successfully.<br></font>';
    } else {
        echo '<font color="red">' . APP_qti_support_app . ' SQL file is not found.<br>';
    }
}
echo '<font color="blue">' . 'QTI tables are finished.<br>' . '</font>';
// 建立資料表 - End

// 塞入舊資料 - Begin
$sql = sprintf("SHOW TABLES WHERE Tables_in_%s='%s'", sysDBschool, 'CO_qti_exam_app');
if ($sysConn->GetOne($sql)) {
    $sql = "INSERT INTO APP_qti_support_app SELECT exam_id, 'exam', course_id, support FROM `CO_qti_exam_app` WHERE 1 ";
    $sysConn->Execute($sql);

    $sysConn->Execute('DROP TABLE `CO_qti_exam_app`');
}
echo '<font color="blue">' . 'QTI data transformation are finished.<br>' . '</font>';
// 塞入舊資料 - End

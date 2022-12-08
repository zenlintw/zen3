<?php
/**
 * 將原先演講廳專用的名稱 WM_experience_catalog, WM_experience_url 改為 APP專用的名稱 APP_experience_catalog, APP_experience_url
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

$permitUser = array('sunnet', 'root');

if (!in_array($sysSession->username, $permitUser)) {
    die('Permission Denied');
}

// 變更資料表 - Begin
$tables = array('APP_experience_catalog' => 'WM_experience_catalog', 'APP_experience_url' => 'WM_experience_url');
$sysConn->Execute('USE '.sysDBschool);

foreach ($tables as $newTableName => $oldTableName) {
    $sql = sprintf("SHOW TABLES WHERE Tables_in_%s='%s'", sysDBschool, $newTableName);
    if (!$sysConn->GetOne($sql)) {
        $renameSQL = 'RENAME TABLE  `' . sysDBschool . '`.`' . $oldTableName . '` TO  `' . sysDBschool . '`.`' . $newTableName . '` ;';
        $sysConn->Execute($renameSQL);
    }
}
echo '<font color="blue">' . 'Experience Tables are finished.<br>' . '</font>';
// 變更資料表 - End
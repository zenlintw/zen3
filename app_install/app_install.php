<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

$permitUser = array('sunnet', 'root');

if (!in_array($sysSession->username, $permitUser)) {
    die('Permission Denied');
}

// 更新選單 - Begin
$xmlFilePathArray = array('/config/xml/',
                          '/base/'.$sysSession->school_id.'/system/default/');
for ($i = 0; $i < count($xmlFilePathArray); $i++) {
    $xmlFilePath = sysDocumentRoot . $xmlFilePathArray[$i] .'academic.xml';
    if (is_readable($xmlFilePath)) {
        $academicXML = file_get_contents($xmlFilePath);

        if ($xmlDoc = domxml_open_mem($academicXML)) {
            $xpathBase = '/manifest/items';
            $xmlXpathContext = xpath_new_context($xmlDoc);

            // 確認是否已有建立
            $checkExist = $xmlXpathContext->xpath_eval("//manifest/items/item[@id='APP_01_01']");
            if (count($checkExist->nodeset) > 0) {
                // 已有建立
                echo '<font color="red">' . 'Installed already. Modify in manage mode.<br>' . '</font>';
            } else {
                // 未建立
                $appListFilePath = sysDocumentRoot . '/app_install/app_list.xml';
                if (is_file($appListFilePath)) {
                    $appXML = domxml_open_mem(file_get_contents($appListFilePath));
                    $appXmlRoot = $appXML->document_element();

                    $xmlRoot = $xmlXpathContext->xpath_eval("//manifest/items");
                    $xmlRoot = $xmlRoot->nodeset[0];
                    $xmlRoot->append_child($appXmlRoot->clone_node(true));

                    $xml = $xmlDoc->dump_mem();

                    $fp = fopen($xmlFilePath, 'w');
                    if ($fp) {
                        fwrite($fp, $xml);
                        fclose($fp);

                        echo '<font color="green">' . $xmlFilePathArray[$i]. ': Sysbar updated.<br></font>';
                    }
                } else {
                    echo '<font color="red">' . 'app_list.xml is not found.<br>' . '</font>';
                }
            }
        } else {
            echo '<font color="red">'. 'XML Error.<br>' . '</font>';
        }
    }
}
// 更新選單 - End

// 先確定有沒有演講廳 - Begin
require_once('experience_patch.php');
// 先確定有沒有演講廳 - End

// 建立資料表 - Begin
$tables = array(
    'APP_experience_catalog', 'APP_experience_url',
    'CO_course_picture',
    'APP_note_share', 'APP_note_action_history',
    'APP_rollcall_base', 'APP_rollcall_record',
    'APP_notification_device', 'APP_notification_message',
    'APP_log',
    'APP_live_activity'
);
$sysConn->Execute('USE '.sysDBschool);
foreach ($tables as $tableName) {
    $sql = sprintf("SHOW TABLES WHERE Tables_in_%s='%s'", sysDBschool, $tableName);
    if (!$sysConn->GetOne($sql)) {
        // 建立新表
        if (is_file(sysDocumentRoot. '/app_install/' . $tableName . '.sql')) {
            $sql = file_get_contents(sysDocumentRoot. '/app_install/' . $tableName . '.sql');
            $sysConn->Execute($sql);

            echo '<font color="green">' . $tableName . ' is created successfully.</font><br>';
        } else {
            echo '<font color="red">' . $tableName . ' SQL file is not found.</font><br>';
        }
    }
}
echo '<font color="blue">' . 'Tables are finished.</font><br>';
// 建立資料表 - End

// 資料表欄位更新 - Begin
$tables = array(
    'APP_rollcall_base', 'APP_rollcall_record'
);
$sysConn->Execute('USE '.sysDBschool);
foreach ($tables as $tableName) {
    $sql = sprintf("SHOW TABLES WHERE Tables_in_%s='%s'", sysDBschool, $tableName);
    // 確認資料表存在
    if ($sysConn->GetOne($sql)) {
        // 確認有無更新檔
        $fileName = sysDocumentRoot. '/app_install/' . $tableName . '_patch.sql';
        if (is_file($fileName)) {
            $sql = file_get_contents($fileName);

            // SQL 拆開執行，避免已存在欄位的 error 影響到後續 SQL 的執行
            $sqlArray = explode(";", $sql);
            foreach ($sqlArray AS $sqlString) {
                if ($sqlString !== '') {
                    $sysConn->Execute($sqlString);
                }
            }

            echo '<font color="green">' . $tableName . ' is updated successfully.</font><br>';
        } else {
            echo '<font color="red">' . $tableName . ' SQL patch file is not found.</font><br>';
        }
    }
}
// 資料表欄位更新 - End

// 建立預設目錄 - Begin
$defaultAppDir = sysDocumentRoot. '/app_install/APP';
$doorAppDir = sysDocumentRoot. '/base/' . $sysSession->school_id . '/door/APP/';
if (is_dir($defaultAppDir)) {
    if (!is_dir($doorAppDir)) {
        $cmd = 'cp -ra ' . $defaultAppDir . ' ' . $doorAppDir;
        exec($cmd);
        if (is_dir($doorAppDir)) {
            echo '<font color="green">' . 'APP directory is created.</font><br>';
        } else {
            echo '<font color="red">' . 'APP directory is not created.</font><br>';
        }
    } else {
        echo '<font color="red">' . 'APP directory is existed.</font><br>';
    }
}
// 建立預設目錄 - End

// 更新圖片 - Begin
$dir = $defaultAppDir .'/course_repos';
if (is_dir($dir) && $dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
        $sourceFile = $dir . '/' . $file;
        $destFile = $doorAppDir . '/course_repos/' . $file;
        if (is_file($sourceFile) && !file_exists($destFile)) {
            if (copy($sourceFile, $destFile)) {
                echo '<font color="green">' . 'Update Course Cover: ' . $destFile . '<br>' . '</font>';
            } else {
                echo '<font color="red">' . 'Update Course Cover Error!: ' . $destFile . '<br>' . '</font>';
            }
        }
    }
    closedir($dh);
}
// 更新圖片 - End

// 支援行動QTI - Begin
require_once('qti_patch.php');
// 支援行動QTI - End

echo "如果有問卷模組，請確認複選的格式若沒有" . htmlspecialchars("<varequal respident='ANS01'>1</varequal>") . "<br>記得還要變更複選題的content，詳情請看app_install.php下方的註解<br><br>";
echo "承上，亦須修改wm3.conf，將問卷附檔使用dl.php轉送的動作拿掉，務必重啟apache。";

// 使用範例：將下方程式區塊複製到sunnet.php去執行；執行完畢記得恢復原sunnet.php程式
/*
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
$RS = dbGetStMr('WM_qti_questionnaire_item', '*', "`type` = 3 AND LOCATE('<conditionvar></conditionvar>', `content`)");

if ($RS) {
    while (!$RS->EOF) {
        echo $RS->fields['ident'] . '<br>';
        $newContent = str_replace('<conditionvar></conditionvar>', '<conditionvar><varequal respident="ANS01">1</varequal></conditionvar>', $RS->fields['content']);
        dbSet('WM_qti_questionnaire_item', "`content` = '{$newContent}'", "`ident` = '{$RS->fields['ident']}'");
        $RS->MoveNext();
    }
}
*/
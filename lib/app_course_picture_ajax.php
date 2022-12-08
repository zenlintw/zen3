<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/app_course_picture.php');

if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])){
    if ($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])){
        // 課程編號
        $nodeCourseId = $dom->get_elements_by_tagname('cid');
        $courseId = intval(base64_decode(trim($nodeCourseId[0]->get_content())));

        // 圖片處理動作：remove 或是 setup
        $nodeAction = $dom->get_elements_by_tagname('action');
        $action = trim($nodeAction[0]->get_content());

        // 圖片檔案名稱
        $nodeFilename = $dom->get_elements_by_tagname('file');
        $filename = trim($nodeFilename[0]->get_content());

        // 將取到的檔名做".."或"斜線"的字串轉換
        $filename = preg_replace(
            array('/\.\.+/', '/[\/\\\\]{2,}/'),
            array('', '/'),
            $filename
        );

        // 圖片檔案名稱
        $nodeClassify = $dom->get_elements_by_tagname('classify');
        $classify = trim($nodeClassify[0]->get_content());
    }

    $actionArray = array('remove', 'setup');
    $appPictureInfoFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/appCoursePictureData.txt', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);

    if (isset($courseId) && in_array($action, $actionArray)) {
        $table = 'CO_course_picture';
        $where = "course_id = {$courseId}";
        list($isExist) = dbGetStSr($table, 'count(*)', $where, ADODB_FETCH_NUM);

        if ($action === 'remove') {
            if ($isExist > 0) {
                // 資料庫有資料(情境：編輯課程設定) => 刪除資料
                dbDel($table, $where);
                if ($sysConn->Affected_Rows() > 0) {
                    die('success');
                }
            } else {
                // 資料庫沒有資料(情境：新增課程設定) => 刪除暫存檔
                if (is_file($appPictureInfoFile)) {
                    unlink($appPictureInfoFile);
                    die('success');
                }
            }
        } else {
            if ($classify === 'public') {
                $pictureFile = sysDocumentRoot . sprintf('/base/%5d/door/APP/course_repos/%s', $sysSession->school_id, $filename);
            } else if ($classify === 'private'){
                $pictureFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/app/%s', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username, $filename);
            }
            echo $pictureFile;
            if (is_file($pictureFile)) {
                $mimeFileType = getFileMimeType($pictureFile);
                $pictureContent = addslashes(base64_encode(file_get_contents($pictureFile)));
                if ($pictureContent != '') {
                    if (intval($isExist) === 0) {
                        if (intval($courseId) !== 99999999) {
                            // 在編輯課程的時候 => 在資料庫新增資料
                            $fields = '`course_id`, `picture`, `mime_type`';
                            $values = "{$courseId}, '{$pictureContent}', '{$mimeFileType}'";
                            dbNew($table, $fields, $values);
                        } else {
                            // 在新增課程的時候 => 在暫存檔新增資料
                            $appPictureFileInfoContent = implode('#', array($pictureFile, $mimeFileType));
                            $fp = fopen($appPictureInfoFile,'w+');
                            fwrite($fp, $appPictureFileInfoContent);
                            fclose($fp);
                            die('success');
                        }
                    } else {
                        // 在編輯課程的時候，變更圖片
                        $setValues = "`picture` = '{$pictureContent}'";
                        dbSet($table, $setValues, $where);
                    }

                    if ($sysConn->Affected_Rows() > 0) {
                        die('success');
                    }
                }
            }
        }
    }

    // 前面成功的部份皆未成功，最後回傳失敗
    die('fail');
}
?>

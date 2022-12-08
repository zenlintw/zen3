<?php

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

    /**
     * 取得課程代表圖
     * @param string  $courseId : 課程編號
     * @return string 圖片檔案
     **/
    function getCoursePic($courseId, $schoolId = null) {
        global $sysSession;
        
        $table = '`CO_course_picture`';
        $csid = intval($courseId);
        if (strlen($csid) != 8) {
            // 如果課程編號不是8碼，則終止動作
            return false;
        }
        if (isset($schoolId)) {
            $sid = intval($schoolId);
            if (strlen($sid) != 5) {
                // 如果學校編號不是5碼，則終止動作
                return false;
            }
            $table = sysDBprefix.$sid.'.`CO_course_picture`';
        }

        if ($csid !== 99999999) {
            list($picture, $mimeType) = dbGetStSr($table, '`picture`, `mime_type`', "course_id='{$csid}'", ADODB_FETCH_NUM);
        }

        if (!isset($picture)){
            $appPictureInfoFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/appCoursePictureData.txt', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);

            if (!is_file($appPictureInfoFile) || file_get_contents($appPictureInfoFile) === '') {
                // 資料庫沒有課程圖片，也不是新增課程時設定圖片，回傳預設圖片
                $filename = sysDocumentRoot . "/theme/{$sysSession->theme}/app/default-course-picture.jpg";
                $picture = file_get_contents($filename);
                $mimeType = 'image/jpeg';
                $fileType = 'jpeg';
            } else {
                $appCoursePictureInfo = explode('#', file_get_contents($appPictureInfoFile));
                $picture = file_get_contents($appCoursePictureInfo[0]);
                $mimeType = $appCoursePictureInfo[1];
                $fileType = substr($mimeType, 6, strlen($mimeType) - 1);
            }
        } else {
            // 資料庫有課程圖片，回傳資料庫圖片
            $picture = base64_decode($picture);
            $fileType = substr($mimeType, 6, strlen($mimeType) - 1);
            $finfo = new finfo(FILEINFO_MIME);
            $mimeType  = $finfo->buffer($picture);
            if (strpos($mimeType,'bmp')!==false) {
                $mimeType  = 'image/bmp'; 
            }
        }

        $length = strlen($picture);
        header('Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Expires: ' . gmdate('r', time()+259200)); // 3天時效
        header('Content-type: ' . $mimeType);
        header('Content-transfer-encoding: binary');
        header('Content-Disposition: filename=picture.' . $fileType);
        header('Accept-Ranges: bytes');
        header("Content-Length: {$length}");
        echo $picture;
    }

    /**
     * 取得圖檔的MIME TYPE
     *
     * @param string $filePath 圖檔的實際路徑
     * @return string 圖檔的MIME TYPE
     */
    function getFileMimeType ($filePath) {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filePath, $fileSuffix);
        switch (strtolower($fileSuffix[1])) {
            case 'jpg' :
            case 'jpeg' :
            case 'jpe' :
                return 'image/jpeg';
            case 'png' :
            case 'gif' :
            case 'bmp' :
                return 'image/'.strtolower($fileSuffix[1]);
        }
    }
?>

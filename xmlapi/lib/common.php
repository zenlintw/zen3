<?php
    // 設定 Debug Mode
    if (DEBUG_MODE) {
        error_reporting(E_ALL ^ E_NOTICE);
        ini_set('display_errors', 'On');
        $sysConn->debug = true;
    } else {
        ini_set('display_errors', 'Off');
        $sysConn->debug = false;
    }

    function isSessionIdExists($idx)
    {
        $row = dbGetStSr('WM_session', 'count(*) as ct', sprintf("idx='%s'", mysql_real_escape_string($idx)));
        if (intval($row['ct']) == 0) return false;
        return true;
    }

    function getSessionData($idx)
    {
        $row = dbGetStSr("WM_session","*",sprintf("idx='%s'",mysql_real_escape_string($idx)));
        if (!isset($row['idx'])) return false;
        return $row;
    }

    function getUserData($user)
    {
        $row = dbGetStSr("WM_user_account","*",sprintf("username='%s'",mysql_real_escape_string($user)));
        if (!isset($row['username'])) return false;
        return $row;
    }

    function getCurrentSchoolName()
    {
        global $sysSession;
        $sname = dbGetOne("WM_school", "school_name", sprintf("school_id=%d", $sysSession->school_id));
        return $sname;
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

    /**
     * 取得圖檔名稱
     *
     * @param string $kind iphone、ipad或是logo
     * @param string $schoolId 學校代號
     * @return string 圖檔在os下的完整路徑
     */
    function getImageFilename ($kind, $schoolId) {
        $image = glob(sprintf(sysDocumentRoot . '/base/%5d/door/APP/home/' . $kind . '.*', $schoolId));
        return $image[0];
    }

    /**
     * 判斷管理者
     *
     * @param string $username 使用者帳號
     * @param integer $courseId 課程編號
     * @param integer $boardId 討論板編號
     * @return boolean true：是管理者，false：非管理者
     **/
    function checkBoardManager ($username, $courseId, $boardId) {
        // 帳號或課程編號或討論板編號格式不合
        if (!preg_match('/^[\w-]+$/', $username) ||
            !preg_match('/^\d{8}$/', $courseId) ||
            !preg_match('/^\d{10}$/', $boardId)) {
            return false;
        }

        // 討論板的管理者
        $where = "board_id={$boardId} and manager='{$username}'";
        list($isManager) = dbGetStSr('WM_bbs_boards', 'count(*)', $where, ADODB_FETCH_NUM);
        if ($isManager) {
            return true;
        }

        // 課程的管理者
        $where = "username='{$username}' and course_id={$courseId} and role&704";
        list($isManager) = dbGetStSr('WM_term_major', 'count(*)', $where, ADODB_FETCH_NUM);
        if ($isManager) {
            return true;
        }

        // 不是討論板也不是課程管理者
        return false;
    }

    /**
     * 濾掉特殊html tag
     *
     * @param string $title 節點名稱
     * @return string 濾過的名稱
     **/
    function nodeTitleStrip ($title) {
        $pattern = array(
            '/<font[^>]+>/',
            '/<\/font>/'
        );
        $replace = array(
            '',
            ''
        );

        return preg_replace($pattern, $replace, $title);

    }

    /**
     * 處理文章附檔
     * @param string $attachments 文章附檔
     * @param string $hrefPath 附檔路徑
     * @return array 處理後附檔，檔名與下載連結
     **/
    function makeAttachments($attachments, $hrefPath) {
        $returnAttachments = array();

        if ($attachments !== '') {
            $arrayAttachments = explode(chr(9),$attachments);
            $attachmentCount = count($arrayAttachments);
            for ($i = 0; $i < $attachmentCount; $i += 2) {
                $attachment['filename'] = $arrayAttachments[$i];
                $attachment['href'] = $hrefPath . $arrayAttachments[$i + 1];
                $returnAttachments[] = $attachment;
                unset($attachment);
            }
        }

        return $returnAttachments;
    }

    /**
     * 處理文章附檔 (給新版議題討論板使用)
     * @param string $attachments 文章附檔
     * @param string $hrefPath 附檔路徑
     * @return array 處理後附檔，檔名與下載連結
     **/
    function generatePostAttachment ($attachments, $hrefPath) {
        $returnAttachments = array();

        if ($attachments !== '') {
            //設定單位
            $size_unit = array('Bytes','KB','MB','GB','TB','PB','EB','ZB','YB');

            $uri = substr($hrefPath, strlen(sysDocumentRoot));
            $a = explode(chr(9), trim($attachments));

            for ($i=0; $i < count($a); $i+=2) {
                $flag = 0;
                /* 計算檔案大小 */
                $size = @filesize(sysDocumentRoot . $uri . $a[$i+1]);
                while ($size >= 1024) {
                    $size = $size / 1024;
                    $flag++;
                }
                /* 修正檔名過長? */
                if (mb_strlen($a[$i], 'utf-8') > 20) {
                    $a1 = mb_substr($a[$i], 0, 20, 'utf-8') . '...' . mb_substr($a[$i], -7, 7, 'utf-8');
                } else {
                    $a1 = htmlspecialchars($a[$i]);
                }
                $attachment['filename'] = $a1;
                $attachment['view_size'] = number_format($size, 0) . $size_unit[$flag];
                $attachment['href'] = WM_SERVER_HOST . $uri . $a[$i+1];
                $returnAttachments[] = $attachment;
                unset($attachment);
            }
        }

        return $returnAttachments;
    }

    /**
     * 轉換檔名(將取到的檔名做".."或"斜線"的字串轉換)
     *
     * @param string $filename 檔案名稱
     * @return string 新檔案名稱
     **/
    function correctFilename ($filename) {
        $newFilename = preg_replace(
            array('/\.\.+/', '/[\/\\\\]{2,}/'),
            array('', '/'),
            $filename
        );
        return $newFilename;
    }

    /**
     * 確認實體目錄是否存在，若不存在是否要建立
     *
     * @param string $attachPath 儲存路徑
     * @param boolean $needCreate 是否需要主動建立目錄
     *
     * @return boolean true(存在)|false(不存在)
     **/
    function isAttachDir ($attachPath, $needCreate) {
        if (is_dir($attachPath)) {
            return true;
        } else {
            if ($needCreate) {
                mkdir($attachPath, 0700);
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 判斷附檔是要保留或刪除既有的，還是有要新增
     *
     * @param array $serverAttachments 編輯前的檔案
     * @param array $clientAttachments 編輯後的檔案
     * @param string $attachPath 檔案路徑
     * @param string $from 從何處建立
     *
     * @return boolean true(儲存成功) | false(儲存失敗)
     **/
    function attachmentsHandler ($serverAttachments, $clientAttachments, $attachPath, $from = 'APP') {
        // 如果目錄不存在或是傳入的$serverAttachments或$clientAttachments不是array，則直接return false，不需要再往下處理附檔
        if (!isAttachDir($attachPath, false) ||
            !is_array($serverAttachments) ||
            !is_array($clientAttachments)) {
            return false;
        }

        // 原先的附檔
        $serverAttachmentsCount = count($serverAttachments);
        // 後來的附檔
        $clientAttachmentsCount = count($clientAttachments);
        // filename的陣列
        $clientAttachmentFilename = array();
        // 欲保留的附檔
        $reserveAttachments = array();
        // 欲刪除的附檔
        $deleteAttachments = array();
        $deleteAttachmentResult = true;
        // 欲新增的附檔
        $addAttachments = array();
        // 存檔後所回傳的結果
        $saveAttachments = array();
        $saveAttachmentsCount = 0;

        for ($i = 0; $i < $clientAttachmentsCount; $i++) {
            if (!is_null($clientAttachments[$i]['filename'])) {
                // 取出有filename的資訊，做與原本附檔的檔名比對
                $clientAttachmentFilename[] = $clientAttachments[$i]['filename'];
            }

            if (isset($clientAttachments[$i]['base64']) && $clientAttachments[$i]['base64'] !== '' && !strstr('data:image/jpeg;base64', $clientAttachments[$i]['base64'])) {
                // 有base64表示要新增
                $addAttachments[] = $clientAttachments[$i];
            }
        }

        $addAttachmentsCount = count($addAttachments);

        if ($serverAttachmentsCount > 0) {
            if (count($clientAttachmentFilename) > 0) {
                // 原資料有附檔，後來也有傳來附檔filename資訊，則要做資料比對
                for ($i = 0; $i < $serverAttachmentsCount; $i += 2) {
                    if (in_array($serverAttachments[$i], $clientAttachmentFilename)) {
                        // 既有檔案不刪除
                        $reserveAttachments[] = $serverAttachments[$i];
                        $reserveAttachments[] = $serverAttachments[$i + 1];
                    } else {
                        // 沒發現相同檔名，則表示既有檔案要刪除
                        $deleteAttachments[] = $serverAttachments[$i];
                        $deleteAttachments[] = $serverAttachments[$i + 1];
                    }
                }
            } else {
                // 原資料有附檔，但後來裡面沒有filename的資訊，表示全數刪除
                $deleteAttachments = $serverAttachments;
            }
        }

        // 刪除檔案
        if (count($deleteAttachments) > 0) {
            $deleteAttachmentResult = deleteAttachments($attachPath, $deleteAttachments);
        }

        // 新增檔案
        if ($addAttachmentsCount > 0) {
            if ($from === 'WM') {
                $saveAttachments = saveWMAttachments($addAttachments);
            } else {
                $saveAttachments = saveAttachments($attachPath, $addAttachments);
            }
            $saveAttachmentsCount = intval(count($saveAttachments) / 2);
        }

        // 處理結果
        if ($saveAttachmentsCount === $addAttachmentsCount && $deleteAttachmentResult === true) {
            // 存檔筆數符合且刪除結果要為true
            if (count($reserveAttachments) > 0 || $saveAttachmentsCount > 0) {
                return mysql_real_escape_string(implode("\t", array_merge($reserveAttachments, $saveAttachments)));
            }
            return 'success';
        } else {
            // 存檔筆數不符合
            return 'fail';
        }
    }

    /**
     * 儲存附檔
     *
     * @param string $attachPath 儲存路徑
     * @param array $attachment 檔案
     *
     * @return array 檔案名稱串接(用以儲存在資料庫的格式)
     **/
    function saveAttachments($attachPath, $attachment) {
        $aryAttach = array();
        $attachmentCount = count($attachment);

        for ($i = 0; $i < $attachmentCount; $i++) {
            $newFilename = uniqid('APP') . '.jpg';
            $newPhysicalName = uniqid('WM') . '.jpg';

            $base64Content = str_replace('data:image/jpeg;base64,', '', $attachment[$i]['base64']);
            $base64DecodeContent = base64_decode($base64Content);

            // 寫入圖檔
            $fopen = fopen($attachPath . $newPhysicalName, 'w');
            if ($fopen && $base64DecodeContent !== '') {
                $fputs = fputs($fopen, $base64DecodeContent);
                $fclose = fclose($fopen);

                if ($fputs > 0 && $fclose) {
                    // 用來之後串接資料庫附檔名稱的陣列
                    $aryAttach[] = $newFilename;
                    $aryAttach[] = $newPhysicalName;
                } else {
                    // 失敗 => 回傳空陣列
                    return array();
                }
            } else {
                // 失敗 => 回傳空陣列
                return array();
            }
            // 清除資料
            unset($fopen);
            unset($base64Content);
            unset($base64DecodeContent);
        }

        return $aryAttach;
    }

    /**
     * 儲存附檔
     *
     * @param array $attachment 檔案
     *
     * @return array 檔案名稱串接(用以儲存在資料庫的格式)
     **/
    function saveWMAttachments($attachment) {
        $aryAttach = array();
        $attachmentCount = count($attachment);

        for ($i = 0; $i < $attachmentCount; $i++) {
            $realFilename = $attachment[$i]['filename'];
            $viewFilename = $attachment[$i]['viewfilename'];
            
            // 用來之後串接資料庫附檔名稱的陣列
            $aryAttach[] = $viewFilename;
            $aryAttach[] = $realFilename;
        }

        return $aryAttach;
    }

    /**
     * 刪除附檔
     *
     * @param string $attachPath 檔案路徑
     * @param array $attachment 檔案
     *
     * @return boolean 處理完畢與否
     **/
    function deleteAttachments ($attachPath, $attachment) {
        $attachmentCount = count($attachment);

        for ($i = 1; $i < $attachmentCount; $i+=2) {
            $file = $attachPath . '/' . $attachment[$i];
            if (is_file($file)) {
                @unlink($file);
            }
        }
        // for迴圈完整跑完，表示處理完畢，回傳true
        return true;
    }

    /**
     * 筆記本-建立多語系的名稱
     *
     * @param object $xmlDoc User的folder xml
     * @param string $type 是title或是help
     * @param string $folderName 筆記本名稱
     *
     * @return object xml節點
     **/
    function makeFolderTitle ($xmlDoc, $type, $folderName) {

        $node = $xmlDoc->create_element($type);

        $titleLanguage = array('big5', 'gb2312', 'en', 'euc-jp', 'user-define');
        $titleCount = count($titleLanguage);

        for ($i = 0; $i < $titleCount; $i++) {
            $language = $xmlDoc->create_element($titleLanguage[$i]);
            $language_text = $xmlDoc->create_text_node(stripslashes($folderName));
            $language->append_child($language_text);
            $node->append_child($language);
        }

        return $node;
    }

    /**
     * 將時間格式轉成秒數格式 YYYY-MM-DD HH:ii:ss => ssssssss
     *
     * @param string $datetime 時間
     *
     * @return integer 秒數
     **/
    function datetimeToSeconds ($datetime) {
        $datetimeSplit = explode(' ', $datetime);
        $datetimeSplitPrefix = explode('-', $datetimeSplit[0]);
        $datetimeSplitPostfix = explode(':', $datetimeSplit[1]);

        return mktime($datetimeSplitPostfix[0], $datetimeSplitPostfix[1], $datetimeSplitPostfix[2], $datetimeSplitPrefix[1], $datetimeSplitPrefix[2], $datetimeSplitPrefix[0]);
    }

    /**
     *  轉換文字為UTF-8
     * @param string $str 轉換字串
     *
     * @return string UTF-8 編碼字串
     */
    function charset2Utf8 ($str) {
        $encodingList = 'ASCII, UTF-8, BIG-5, EUC-CN';
        return iconv(mb_detect_encoding($str, $encodingList),"UTF-8", $str);
    }

    /**
     *  更換內容的 img source 為絕對路徑
     * @param $content
     */
    function chgImgSrcRelative2Absolute ($content) {
        // loadHTML php 5 以上才支援
        if ( !function_exists('version_compare') || version_compare( phpversion(), '5', '<' ) ) {
            return $content;
        }

        $dom = new DOMDocument;
        // <?xml encoding="UTF-8"> 的方式在部分站台(中原)會亂碼、改使用 mb_convert_encoding 先轉碼一次
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
        $dom->loadHTML($content);
        
        // 取得 IMG tag
        $imgItems = $dom->getElementsByTagName('img');

        for ($i = 0; $i < $imgItems->length; $i++) {
            $originalPath = $imgItems->item($i)->getAttribute('src');

            // 先判斷URL中是否包含協議，如果包含說明是絕對地址
            if(strpos($originalPath, '://') !== FALSE){
                continue;
            }
            if (strpos($originalPath, "/") === 0) {
                // 相對路徑改為絕對路徑
                $imgItems->item($i)->setAttribute("src", WM_SERVER_HOST . $originalPath);
            }
        }
        // 目前版本 loadHTML 不支援 LIBXML_HTML_NODEFDTD，利用preg 去除saveHTML 自動產生的 doctype、html、body tag
        $transContent = preg_replace('~<(?:!DOCTYPE|/?(?:html|body|\?xml))[^>]*>\s*~i', '', $dom->saveHTML());

        return mb_convert_encoding($transContent, "UTF-8", 'HTML-ENTITIES');
    }

    /**
     * 取得 Apache 版本
     *
     * @return String
     */
    function getApacheVersion () {
        $apache = exec('/usr/local/apache/bin/apachectl -v | grep version 2>&1');
        preg_match('/apache\/([\d.]+)/', strtolower($apache), $match);

        return $match[1];
    }

    /**
     * 直接解密：透過平台網址做的二次加密後要解密
     *
     * @param String $data 欲解密的文字
     * @return String 解密後的文字
     **/
    function decryptImmediately ($data) {
        if ($data === '') {
            return '';
        }

        // 預設最後解密的資料為空字串
        $phraseII = '';

        $iCode = strlen(AES_APP_STRING);

        // 做第一次解密，直接使用$_SERVER['HTTP_HOST']的長度作aesCode
        $encryptHandler = new APPEncrypt();
        $phraseI = $encryptHandler->decryptNew($data, $iCode);
        // 分離當初用@!!@串接的資料
        $phraseISplitData = explode('@!!@', $phraseI);

        $data = $phraseISplitData[0];
        $iiCode = intval($phraseISplitData[1]);

        if ($iiCode > 0) {
            $phraseII = $encryptHandler->decryptNew($data, $iiCode);
        }

        return $phraseII;
    }

    /**
     * 直接加密：透過平台做二次加密
     *
     * @param String $data 欲加密的文字
     * @return String 加密後的文字
     **/
    function encryptImmediately ($data) {
        if ($data === '' || is_null($data)) {
            return '';
        }

        $encryptHandler = new APPEncrypt();

        $iCode = $encryptHandler->makeAesCode();
        $iiCode = strlen(AES_APP_STRING);

        // 做第一次加密
        $phraseI = $encryptHandler->encryptNew($data, $iCode);
        // 用特殊符號把aesCode串起來
        $phraseIData = $phraseI . '@!!@' . $iCode;

        // 做第二次加密，直接使用$_SERVER['HTTP_HOST']的長度作aesCode
        $phraseII = $encryptHandler->encryptNew($phraseIData, $iiCode);

        return $phraseII;
    }

    function boardStatus ($open, $close, $share) {
        $now = time();
        $isBeginSet = ($open !== '' && $open !== '0000-00-00 00:00:00') ? true : false;
        $isEndSet = ($close !== '' && $close !== '0000-00-00 00:00:00') ? true : false;
        $isShareSet = ($share !== '' && $share !== '0000-00-00 00:00:00') ? true : false;

        // 有設定起訖時間
        if ($isBeginSet || $isEndSet) {
            // 目前未到開放時間
            if ($isBeginSet && $now < strtotime($open)) {
                // 目前未到開放時間
                return 'NOT-OPEN';
            } else if ($isEndSet && $now > strtotime($close)) {
                if ($isShareSet && $now > strtotime($share)) {
                    // 有開放參觀，且目前時間超過啟用參觀的時間
                    return 'SHARE';
                }
                // 目前未到開放時間
                return 'CLOSED';
            }
        }
        // 沒有設定起迄時間
        return 'OPEN';
    }
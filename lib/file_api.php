<?php
    /*
    *	討論板系統之夾檔處理 API
    *	Write by Wiseguy Liang	2002/4/20
    *	使用 PHP 之 PEAR 處理 "mkdir -p" 和 "rm -rf" 功能
    */
    require_once('System.php');
    require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');

    function genEmbedAudioHtml($filename)
    {
        static $audio_types = array('.wax','.asx','.wma','.wav','.mp3','.asf','.mid'), $count;
        if (!isset($count))
            $count = 1;
        else
            $count++;

        if (in_array(strtolower(strrchr($filename, '.')), $audio_types)) {
            $ret = <<< EOB
<span style="width: 48px; height: 26px; overflow: hidden; vertical-align: middle">
<OBJECT ID="MediaPlayer{$count}" width="66" height="26"
   classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
   CODEBASE="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,5,715"
        standby="Loading Microsoft Windows Media Player components..."
        type="application/x-oleobject" style="position: relative; left: -18">
  <PARAM NAME="AutoStart" VALUE="False">
  <PARAM NAME="FileName" VALUE="{$filename}">
  <PARAM NAME="ShowControls" VALUE="True">
  <PARAM NAME="ShowStatusBar" VALUE="False">
  <EMBED type="application/x-mplayer2"
   pluginspage="http://www.microsoft.com/Windows/MediaPlayer/"
   SRC="{$filename}"
   name="MediaPlayer{$count}"
   width="66" height="26"
   autostart="0"
   showcontrols="1">
  </EMBED>
</OBJECT>
</span>
EOB;
            return $ret;
        } else {
            return '';
        }
    }

    /* 儲存討論板附檔
     * input :	$save_path		= 存檔目的目錄
     *			$quota_limit	= 可使用儲存空間(若無使用限制請填入 0 )
     *			$quota_used		= 目前已使用大小
     * return: 檔名\t存檔名\t檔名\t存檔名 ... 如果沒有夾檔則傳回空字串
     */
    function save_upload_file($save_path,$quota_limit,$quota_used){
        global $_FILES, $sysSession, $_SERVER, $content;
        $file_size		= 0	;										// 取出上傳的檔案大小
        $file_amount	= count($_FILES['uploads']['name']);		// 取出上傳檔案個數
        $files = '';

        // 計算上傳檔案的大小
        for ($i=0; $i<$file_amount; $i++)
            $file_size += $_FILES['uploads']['size'][$i];
        $file_size /= 1024;	// 換算為 KB

        if (!is_dir($save_path)) @System::mkDir("-p $save_path");
        if ($quota_limit==0 or ($quota_limit > $quota_used + $file_size)) {
            for($i=0; $i<$file_amount; $i++){
                if ($_SERVER[PHP_SELF] == '/academic/explorer/index.php') {
                    $target = un_adjust_char($_FILES['uploads']['name'][$i]);
                    if (move_uploaded_file($_FILES['uploads']['tmp_name'][$i], $save_path . DIRECTORY_SEPARATOR . $target)){
                        $files .= $_FILES['uploads']['name'][$i];
                    }
                } elseif ($_SERVER[PHP_SELF] == '/academic/stud/verify_mail.php') {
                    $target = uniqid('WM') . strrchr($_FILES['uploads']['name'][$i], '.');
                    if (move_uploaded_file($_FILES['uploads']['tmp_name'][$i], $save_path . DIRECTORY_SEPARATOR . $target)){
                        $files .= ($_FILES['uploads']['name'][$i] . chr(9) . $target . chr(9));
                    }
                } else {
                    $target = uniqid('WM') . strrchr(mb_convert_encoding($_FILES['uploads']['name'][$i], 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win'), '.');
                    if (move_uploaded_file($_FILES['uploads']['tmp_name'][$i], $save_path . DIRECTORY_SEPARATOR . $target)){
                        $files .= ($_FILES['uploads']['name'][$i] . chr(9) . $target . chr(9));
                        if (dirname($_SERVER['PHP_SELF']) == '/forum' &&
                            $content &&
                            strpos($_FILES['uploads']['type'][$i], 'image/') === 0 &&
                            preg_match('/<img\b[^>]*\bsrc=\\\\"([A-Z]:\\\\\\\\[^">]*\\\\\\\\' . preg_quote($_FILES['uploads']['name'][$i], '/') . ')\\\\"/isU', $content, $regs)) {
                            $content = str_replace($regs[1], substr($save_path . DIRECTORY_SEPARATOR . $target, strlen(sysDocumentRoot)), $content);
                        }
                    }
                }
            }
            return chop($files);
        }
        return false;
    }

    /* 產生討論板夾檔存放目錄 (不含 node)
     * input : $where = 為 'board' 或 'quint' 兩者之一
     * input : $owner_id = 課程編號或班級編號
     * return: 傳回路徑
     */
    function get_attach_file_path($where, $owner_id=null, $board_id='') {
        global $sysSession;
        if ($board_id == '') $board_id = $sysSession->board_id;
        if ($where != 'board' && $where != 'quint') return null;

        $ret = '/base/' . $sysSession->school_id;

        switch(strlen($owner_id)) {
            case 7:// 班級
            case 15:// 班級群組
                $ret .= '/class/'. substr($owner_id, 0, 7);
                break;

            case 8:// 課程
            case 16:// 課程群組
                $ret .= '/course/'. substr($owner_id, 0, 8);
                break;
            case 5: // 校級看板
                break;
            default:
            {
                if ($sysSession->course_id){
                    $ret .= '/course/' . $sysSession->course_id;
                } else if($sysSession->class_id) {
                    $ret .= '/class/' . $sysSession->class_id;
                }
            }
        }
        $ret .= "/$where/" . $board_id ;
        return sysDocumentRoot . $ret;
    }

    /* 將夾檔字串轉為 Link
     * input : $pre = 夾檔的 URI；$attach 以 Tab 隔開的夾檔字串
     * return: 一串 Link (在列表時不分行，在單一POST裡會分行)
     */
    function generate_attach_link($pre, $attach, $b_type='board') {
        return generate_attach_del($pre, $attach, $b_type, '', false);
    }

    /* 產生刪除夾檔的列表。
     * input : $pre = 夾檔的 URI；$attach 以 Tab 隔開的夾檔字串
     * return: From 字串。有 checkbox
     */
    function generate_attach_del($pre, $attach, $b_type='board', $msg='', $forDel=true) {
        global $sysSession;
        if (empty($attach)) return null;
        $type = array('avi','bmp','doc','gif','htm','html','jpg','mp3','pdf','ppt','txt','wav','xls','zip');
        switch($b_type) {
            case 'board':
                $post_no = $sysSession->post_no;
                break;
            case 'quint':
                $post_no = $sysSession->q_post_no;
                break;
            default:
                $post_no = 0;
        }

        $uri = substr($pre, strlen(sysDocumentRoot));
        $a = explode(chr(9), trim($attach));
        $r = '';
        for($i=0; $i<count($a); $i+=2){
            if ($forDel) $r .= $msg . '<input type="checkbox" name="delAttach[]" value="' . $a[$i+1] . '" />&nbsp;';
            $icon = '<img border="0" align="absmiddle" src="/theme/' . $sysSession->theme . '/filetype/' .
                    ((($ext = strtolower(substr(strrchr($a[$i+1], '.'), 1))) && in_array($ext, $type))?
                $ext : 'default') . '.gif"'.($post_no?(' /> '.$a[$i]):(' alt="'.$a[$i].'" />'));
            if (strrchr($a[$i+1], '.') != '.awp') {
                $filename = $uri . DIRECTORY_SEPARATOR . $a[$i+1];
                // $r .= '<a href="' . $filename .'" target="_blank" class="cssAnchor"'.($post_no?('>'. $icon .'</a> <span class="font01">(' . number_format(@filesize(sysDocumentRoot . $filename), 0, '.', ',') . ' <span style="font-size: 8pt; font-family: Arial Narrow; color: gray">bytes</span>)</span>' . genEmbedAudioHtml($filename) . '<br />'):(' onclick="event.cancelBubble=true;">'. $icon .'</a>'));
                $r .= '<a href="' . $filename .'" target="_blank" class="cssAnchor"'.($post_no?('>'. $icon .'</a> <span class="font01">(' . number_format(@filesize(sysDocumentRoot . $filename), 0, '.', ',') . ' <span style="font-size: 8pt; font-family: Arial Narrow; color: gray">bytes</span>)</span>' . genEmbedAudioHtml($filename) . '<br />'):(' onclick="event.cancelBubble=true;">'. $icon .'</a>'));
            } else {
                $r .= '<a href="javascript:;" onClick="loadwb(\''.$pre . DIRECTORY_SEPARATOR . $a[$i+1] . '\'); return false;" class="cssAnchor">'.$icon.'</a>'.
                      '<input type="hidden" id="awppath" name="awppath" value="'.$pre . DIRECTORY_SEPARATOR . $a[$i+1].'">' . ($post_no?'<br />':'');
            }
        }
        return $r;
    }

    /* 刪除夾檔
     * input : $pre = 夾檔的 URI；$attach 以 Tab 隔開的夾檔字串
     * return: 刪除後剩下的字串
     */
    function remove_previous_uploaded($pre, $attach) {
        global $sysSession, $_POST;
        if (empty($_POST['delAttach'])) return $attach;
        $old = explode(chr(9), $attach);
        for($i=0; $i<count($_POST['delAttach']); $i++){
            $x = array_search($_POST['delAttach'][$i], $old);
            // if (($x = array_search($_POST['delAttach'][$i], $old)) && ($x % 2)){
            if (($x = array_search($_POST['delAttach'][$i], $old)) !== false){
                $pre . DIRECTORY_SEPARATOR . $old[$x];
                @unlink($pre . DIRECTORY_SEPARATOR . $old[$x]);
                if ($x % 2 == 0)
                {
                    array_splice($old, $x, 2);
                }else{
                    array_splice($old, $x-1, 2);
                }
            }
        }
        return trim(implode(chr(9), $old));
    }

    /**
     * 取得某一個目錄下所有檔案
     * @param string $path : 給訂的目錄路徑
     * @param string $charset : 字元集
     * @return array 檔案陣列
     */
    function getAllFile($path, $charset='') {
        $files = array();
        if ($dp = @opendir($path . DIRECTORY_SEPARATOR)) {
            while (($fn = @readdir($dp)) !== FALSE)
            {
                if ((strpos($fn, '.') === 0) || is_dir($path . $fn)) continue;
                $files[] = adjust_char($fn);
            }
            closedir($dp);
        }
        return $files;
    }

    /**
     * 建立實體目錄( 遞迴 )
     * @param string $path : 完整路徑名稱(實體檔案系統)
     * @return boolean : 成功 true , 失敗 false
     **/
    function mkdirs($path) {
        if (is_dir($path))
            return true;

        $parent_path = dirname($path);
        if(!is_dir($parent_path)) {
            if(!mkdirs($parent_path))
                return false;
        }

        mkdir($path);
        return is_dir($path);
    }

    /**
     * 判斷上傳檔案是否超過系統限制
     * @return boolean true : 超過限制; false : 未超過限制
     */
    function detectUploadSizeExceed()
    {
        global $_POST, $_FILES;

        $POST_MAX_SIZE = ini_get('post_max_size');
        $mul = substr($POST_MAX_SIZE, -1);
        $mul = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));

        if ($_SERVER['CONTENT_LENGTH'] > $mul*(int)$POST_MAX_SIZE && $POST_MAX_SIZE && count($_FILES) === 0 && count($_POST) === 0) {
            return true;
        }

        return false;
    }

    /**
     * 取得夾檔相關資訊
     *
     * @param string $attach 資料庫附件檔案上傳的檔名與實際儲存檔名
     * @param string $pre 實際儲存的資料夾
     */
    function getFileData($attach, $pre)
    {
        if (empty($attach)) return null;

        //設定單位
        $size_unit = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB','ZB', 'YB');

        $uri = substr($pre, strlen(sysDocumentRoot));
        $a = explode(chr(9), trim($attach));
        $data = array();
        for ($i = 0; $i < count($a); $i+=2) {
            $flag = 0;
            // 計算檔案大小
            $size = @filesize(sysDocumentRoot . $uri . $a[$i+1]);
            while ($size >= 1024) {
                $size = $size / 1024;
                $flag++;
            }
            $data[] = array(
                'path' => $uri . $a[$i+1],// 實際路徑
                'filename' => htmlspecialchars($a[$i]),// 上傳時檔名
                'disk_filename' => $a[$i+1],// 實際儲存檔名
                'file_size' => number_format($size, 0) . $size_unit[$flag],// 檔案大小
                'file_type' => get_mime_type(sysDocumentRoot . $uri . $a[$i+1])
            );
        }
        return $data;
    }

    /**
     * 取得檔案類型
     * @param string $filepath 實際儲存路徑
     */
    function get_mime_type($filepath) {
        return mime_content_type($filepath);
    }

    /**
     * 取登入者專屬資料夾的實體路徑
     * 
     * @param string $subFolder 子資料夾
     */
    function getUserBasePath($subFolder = null) {
        global $sysSession;
        $username = $sysSession->username;
        
        if (isset($subFolder) === true) {
            $subFolder = $subFolder .'/';
        }
        $userBasePath = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/' . $subFolder, substr($username, 0, 1), substr($username, 1, 1), $username);
        if (!is_dir($userBasePath)) {
            mkdirs($userBasePath, 0700);
        }
        
        return $userBasePath;
    }

    /**
     * 取登入者專屬資料夾的網頁路徑（含DOMAIN NAME）
     * 
     * @param string $subFolder 子資料夾
     */
    function getUserViewPath($subFolder = null) {
        global $sysSession;
        $username = $sysSession->username;
        
        if (isset($subFolder) === true) {
            $subFolder = $subFolder .'/';
        }
        $userDisplayPath = sprintf($_SERVER['HTTP_ORIGIN'] . '/user/%1s/%1s/%s/' . $subFolder, substr($username, 0, 1), substr($username, 1, 1), $username);
        
        return $userDisplayPath;
    }

    /**
     * 取登入者專屬資料夾的路徑（不含DOMAIN NAME）
     * 
     * @param string $subFolder 子資料夾
     */
    function getUserPath($subFolder = null) {
        global $sysSession;
        $username = $sysSession->username;
        
        if (isset($subFolder) === true) {
            $subFolder = $subFolder .'/';
        }
        $userPath = sprintf('/user/%1s/%1s/%s/' . $subFolder, substr($username, 0, 1), substr($username, 1, 1), $username);
        
        return $userPath;
    }

    /**
     * 解析附件檔案資料
     * 
     * @param string $attachment 附件檔案字串
     */
    function parseFileList($attachment) {
        $attach = explode("\t", $attachment);
        if ((count($attach) == 1) && empty($attach[0])) {
            $attach = array();
        }
        
        $fileList = array();
        for ($i = 0, $j = 0; $i < count($attach); $i = $i + 2, $j++) {
            $fileList[$j]['view_filename'] = $attach[$i];
            $fileList[$j]['real_filename'] = $attach[$i + 1];
        }  
        
        return $fileList;
    }
    
    // 容量轉換
    function format_size($size) {
        $mod = 1024;
        $units = explode(' ','KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        return round($size, 2) . ' ' . $units[$i];
    }    
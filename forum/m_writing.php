<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lib/file_api.php');
    require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
    require_once(sysDocumentRoot . '/lib/lib_newsfaq.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/lib_forum.php');
    require_once(sysDocumentRoot . '/lib/quota.php');
    require_once(sysDocumentRoot . '/lib/Hongu.php');
    require_once(sysDocumentRoot . '/forum/lib_mailfollow.php');
    require_once(sysDocumentRoot . '/forum/order.inc.php');
    require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
    require_once(sysDocumentRoot . '/lang/forum.php');
    require_once(sysDocumentRoot . '/lang/mooc_forum.php');
    require_once(sysDocumentRoot . '/lang/app_server_push.php');
    require_once(sysDocumentRoot . '/xmlapi/config.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');
    require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/DatabaseHandler.php');
    
    if (!defined('BOARD_TYPE')) define('BOARD_TYPE', 'board');
    define('USE_TABLE' , BOARD_TYPE == 'board' ? 'WM_bbs_posts' : 'WM_bbs_collecting');

    if (BOARD_TYPE == 'board') {
        $sysSession->cur_func = $_POST['etime'] ? '900200600' : '900200500';
    } else {
        $sysSession->cur_func = $_POST['etime'] ? '900300500' : '900300400';
    }

    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

    // 檢查上傳檔案是否超過限制
    if (detectUploadSizeExceed()) {
        die($MSG['upload_exceeds_limit'][$sysSession->lang]);
//        showXHTML_script('inline', 'alert("'.$MSG['upload_file_error'][$sysSession->lang].'");location.replace("'.(BOARD_TYPE=='board' ? 'index.php' : 'q_index.php').'");');
    }
    
    $postbid = htmlspecialchars($_POST['bid']);
    $ticket = md5(sysTicketSeed . BOARD_TYPE . $_COOKIE['idx'] . $postbid);
    $bid = intval(trim(sysNewDecode(rawurldecode(htmlspecialchars($_POST['enbid'])), $ticket, false)));

    if ($bid < 1000000001) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    // APP 課程公告 PUSH 用 - Begin
    $newPostFlag = false;
    // APP 課程公告 PUSH 用 - End

    // 檢查 ticket
    if (trim($_POST['ticket']) != $ticket) {
        wmSysLog($sysSession->cur_func, $sysSession->class_id , $bid , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
        // die($MSG['access_deny'][$sysSession->lang]);
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    // 確認$bid是存在的
    $isBoardIdExists = intval(dbGetOne(
        'WM_bbs_boards','count(*)',
        sprintf("board_id=%d", $bid)
    ));

    if ($isBoardIdExists <= 0) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    // 各項排序依據
    $OB = $OrderBy[BOARD_TYPE];

    function ErrorExit($_msg = '') {
        global $_POST, $sysSession, $_SERVER;
        echo "<body onload=\"document.getElementById('firstForm').submit();\">\n",
             "<div style=\"display: none\"><form action=\"",$_POST['whoami'],"\" method=\"POST\" id=\"firstForm\">\n";
        foreach($_POST as $k => $v) echo "<textarea name=\"$k\">", stripslashes($v), "</textarea>\n";
        echo '<input type="hidden" name="writeError" value="', $_msg, "\"\n",
             "</form></div></body>\n";
        wmSysLog($sysSession->cur_func, $sysSession->class_id , $bid , 2, 'auto', $_SERVER['PHP_SELF'], $_msg);
        exit;
    }

    // 驗證表單數值
    $messages = _formValidation();

    $result = array(
        'success' => true,
        'id'      => 0,
        'ticket'  => '',
        'message' => ''
    );

    if (count($messages) >= 1) {
        $errMsg = array();
        for ($i = 0, $size = count($messages); $i < $size; $i++) {
            $errMsg[] = $messages[$i];
        }
        $result = array(
            'error' => $errMsg,
            'img_error' => ''
        );
        echo json_encode($result);
        die();
    }

    /**
     * 表單驗證函數
     */
    function _formValidation()
    {
        global $sysSession, $MSG;
        
        $hongu = new Hongu();
        $rule = new Hongu_Validate_Rule();

        $rules['type'] = array(
            $rule->MAKE_RULE('Required', null, $MSG['required'][$sysSession->lang]),
            $rule->MAKE_RULE('InValues', array('board'), $MSG['bid_not_exist'][$sysSession->lang]),
            $rule->MAKE_RULE('XssAttack', null, $MSG['msg_xss'][$sysSession->lang])
        );
        
        // 非回覆要檢查主題是否滿1個字
        if ($_POST['isReply'] === '') {
            $rules['subject'] = array(
                $rule->MAKE_RULE('CharLength', array('min' => 1, 'max' => 255), $MSG['short_title'][$sysSession->lang]),
                $rule->MAKE_RULE('Required', null, $MSG['required'][$sysSession->lang]),
                $rule->MAKE_RULE('XssAttack', null, $MSG['msg_xss'][$sysSession->lang])
            );
        }
        
        $rules['content'] = array(
            $rule->MAKE_RULE('Required', null, $MSG['required'][$sysSession->lang]),
            $rule->MAKE_RULE('XssAttack', null, $MSG['msg_xss'][$sysSession->lang])
        );

        $params = $_POST;
        $valid = $hongu->getValidator();

        return $valid->check($params, $rules);
    }

    // 標題不許使用 html
    $subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);

    // 取出簽名檔
    $tag_serial = intval($_POST['tagline']);
    list($ctype, $tagline) = dbGetStSr('WM_user_tagline', 'ctype, tagline', "serial={$tag_serial} AND username='{$sysSession->username}'", ADODB_FETCH_NUM);
    if ($ctype == 'text') {
        $patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
        $replace  = array("<a href=\"\\1\" target=\"_blank\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
        $tagline  = nl2br(preg_replace($patterns, $replace, htmlspecialchars($tagline, ENT_QUOTES)));
    }

    // 取學校最新消息編號
    $schoolNewsBoard = dbGetOne('`WM_news_subject`', '`board_id`', '`type` = "news"');
    // 取公告版編號
	$annBid = dbGetOne('`WM_term_course`', 'bulletin', '`course_id` = ' . $sysSession->course_id);

    // 本文去除所有的不必要 html
    $content = strip_scr($_POST['content']);
    // 如果貼的是純文字，轉換 url 為 link
    if (!$_POST['isHTML']) {
        $patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
        $replace = array("<a href=\"\\1\" target=\"_blank\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
        $content = nl2br(preg_replace($patterns, $replace, htmlspecialchars($content, ENT_QUOTES)));
    }

    // 加上張貼者 簽名檔
    if ($tagline) $content .= "\n<br />\n<br />{$tagline}";
    $content = trimHtml($content);
    
//    echo '<pre>';
//    var_dump($_POST['isReply']);
//    var_dump(empty($_POST['mnode']));
//    echo '</pre>';
    if (!empty($_POST['mnode'])) {
        $isNodeExists = intval(dbGetOne(
            USE_TABLE,'count(*)',
            sprintf("board_id=%d and site=%d and node='%s'",
                $bid, $sysSiteNo, $_POST['mnode']
            )
        ));

        if (!$isNodeExists) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }
    // 回覆
    if ($_POST['isReply'] === '1' && empty($_POST['mnode']) === true) {
        $node = trim($_POST['node']);
        if(preg_match("/^[0-9]+$/", $node) == 1) {
            // string only contain the a to z , A to Z, 0 to 9
        } else {
            die('Acess Deny!');
        }
        // 取得 node 的最大子 node
        list($mnode) = dbGetStSr(USE_TABLE, 'MAX(node)', "board_id={$bid} and node like '" . substr($node, 0, 9) . "%'", ADODB_FETCH_NUM);
        // 產生本篇的 node
        // 雙層架構
        $nnode = (strlen($mnode) == 9) ? ($node . '000000001') : sprintf('%s%09d', substr($mnode, 0, 9), intval(substr($mnode, -9))+1);
    // 新貼文
    } elseif ($_POST['isReply'] === '' && empty($_POST['mnode'])) {
        // 取得目前板中最大的 node
        list($mnode) = dbGetStSr(USE_TABLE, 'MAX(node)', "board_id ={$bid} and length(node) = 9", ADODB_FETCH_NUM);
        // 產生本篇的 node
        $nnode = empty($mnode)?'000000001':sprintf("%09d", $mnode+1);
    } else {
        $nnode = $_POST['mnode'];
        if(preg_match("/^[0-9]+$/", $nnode) == 1) {
            // string only contain the a to z , A to Z, 0 to 9
        } else {
            die('Acess Deny!');
        }
    }

    // 本篇是使用哪種語系張貼
    $ll = array(
        'Big5'   => 1,
        'en'	 => 2,
        'GB2312' => 3,
        'EUC-JP' => 4,
        'user_define' => 5
    );

    // 強制給予POST來的討論版編號
    // 改為一律重新取得討論版擁有者
    $rs  = dbGetStSr('WM_bbs_boards', 'owner_id, bname', "board_id={$bid}", ADODB_FETCH_ASSOC);
    if(!$rs || $rs['owner_id'] === 0) {
        $errMsg[] = array(
            'id' => 'owner',
            'message' => $MSG['owner_id_error'][$sysSession->lang],
            'rule' => 'Hongu_Validate_Validator_Repeat'
        );
        $result = array(
            'error'   => $errMsg,
            'annFlag' => ($bid === $annBid)? true : false,
            'nid' => $nnode,
        );
        wmSysLog($sysSession->cur_func, $bid , $nnode , 0, 'auto', $_SERVER['PHP_SELF'], "board " . $bid . " WM_bbs_boards.owner_id error");
        echo json_encode($result);
        die();
    }
    if ($sysSession->board_ownerid !== $rs['owner_id']) {
        wmSysLog($sysSession->cur_func, $bid , $nnode , 0, 'auto', $_SERVER['PHP_SELF'], "board " . $rs['owner_id'] . " WM_bbs_boards.owner_id and session ". $sysSession->board_ownerid . " not the same");
    }
    $sysSession->board_ownerid = $rs['owner_id'];
    $sysSession->restore();
    
    $base_path = get_attach_file_path(BOARD_TYPE, $sysSession->board_ownerid, $bid);

    // 換掉可能之引號
    $username = mysql_escape_string($sysSession->username);
    $realname = mysql_escape_string($sysSession->realname);

    $node_id = '';

    // 取得 Quota 資訊開始
    $freeQuota = getRemainQuota($sysSession->board_ownerid);
    $type      = getQuotaType($sysSession->board_ownerid);
    $msgQuota  = str_replace(array('%TYPE%', '%OWNER%'),
                             array($MSG[$type][$sysSession->lang], $MSG[$type . '_owner'][$sysSession->lang]),
                             $MSG['quota_full'][$sysSession->lang]);

    if (!empty($_FILES))
        $freeQuota = $freeQuota - (array_sum( $_FILES['uploads']['size'] )/1024);
    if (($_POST['mp3path'] = basename(trim($_POST['mp3path']))) != '') {
        $srcfile   = $_SERVER['DOCUMENT_ROOT']."/base/{$sysSession->school_id}/board/{$bid}/".$_POST['mp3path'];
        $freeQuota = $freeQuota - (filesize($srcfile) / 1024);
    }
    if (($_POST['wbpath'] = basename(trim($_POST['wbpath']))) != '') {
        $srcfile   = $_SERVER['DOCUMENT_ROOT']."/base/{$sysSession->school_id}/board/{$bid}/".$_POST['wbpath'];
        $freeQuota = $freeQuota - (filesize($srcfile) / 1024);
    }
    if ($freeQuota <= 0 && (!empty($_FILES)|| $_POST['mp3path'] != '' || $_POST['wbpath'])) ErrorExit($msgQuota);
    // 取得Quota資訊結束

    // Add by yakko. for anicam sound rec
    if ($_POST['mp3path'] != '') {
        $srcfile = $_SERVER['DOCUMENT_ROOT']."/base/{$sysSession->school_id}/board/{$bid}/".$_POST['mp3path'];
        if ($_POST['etime']) {	// 如果是重編輯的話
            $destdir = $base_path.DIRECTORY_SEPARATOR.trim($_POST['mnode']);
        } else {
            $destdir = $base_path.DIRECTORY_SEPARATOR.$nnode;
        }
        if (!is_dir($destdir)) {
            if (strpos($_ENV['OS'], 'Windows') !== false) {
                exec('cmd.exe /Q /D /U /E:ON /C mkdir "' . $destdir . '"');
            } else {
                exec("mkdir -pm 755 '$destdir'");
            }
        }

        $destfile = $destdir.DIRECTORY_SEPARATOR.$_POST['mp3path'];
        if (copy($srcfile, $destfile)) {
            unlink($srcfile);
        }
    }

    if ($_POST['wbpath'] != '') {
        $srcfile = $_SERVER['DOCUMENT_ROOT']."/base/{$sysSession->school_id}/board/wb_temp/".$_POST['wbpath'];
        if ($_POST['etime']) {	// 如果是重編輯的話
            $destdir = $base_path.DIRECTORY_SEPARATOR.trim($_POST['mnode']);
        } else {
            $destdir = $base_path.DIRECTORY_SEPARATOR.$nnode;
        }
        if (!is_dir($destdir)) {
            if (strpos($_ENV['OS'], 'Windows') !== false) {
                exec('cmd.exe /Q /D /U /E:ON /C mkdir "' . $destdir . '"');
            }else{
                exec("mkdir -pm 755 '$destdir'");
            }
        }

        $destfile = $destdir.DIRECTORY_SEPARATOR.$_POST['wbpath'];
        if (copy($srcfile, $destfile)) {
            unlink($srcfile);
        }
    }
    // END Add by yakko. for anicam sound rec

    // 取上傳的檔名
    if (count($_POST['originalFilename']) >= 1) {
        foreach ($_POST['originalFilename'] as $key => $val) {
            if ($_POST['deleteFlag'][$key] === 'A' || $_POST['deleteFlag'][$key] === 'N') {
                $attach .= $_POST['originalFilename'][$key].chr(9).$_POST['diskFilename'][$key]. chr(9);
            }
        }
        $attach = chop($attach);
    }
    
    function create_folders($dir){
        return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
    }
    
    if ($_POST['img_src'] >= '0') {
        $arr_src = explode(',', $_POST['img_src']);
        if ($_POST['etime']) {	// 如果是重編輯的話
            $desFolder = $base_path.DIRECTORY_SEPARATOR.trim($_POST['mnode']);
        } else {
            $desFolder = $base_path.DIRECTORY_SEPARATOR.$nnode;
        }
        $start = strpos($desFolder,"/base");
        $pp = substr($desFolder,$start);

        foreach ($arr_src as $key => $value) {
            $fs= pathinfo($value);
            $srcfile = $_SERVER['DOCUMENT_ROOT'].$value;
            if (file_exists($srcfile)){
                    $desFolder = $desFolder.DIRECTORY_SEPARATOR;
                    create_folders($desFolder);
                    if (copy($srcfile, $desFolder.$fs[basename])) {
                        unlink($srcfile);
                        $content = str_replace ($value,$pp.DIRECTORY_SEPARATOR.$fs[basename],$content);
                    }
            }
        }
    }
    
    // 如果是重編輯的話
    if (empty($_POST['etime']) === false) {

        // 去掉夾檔中，被標示刪除的檔案 & 儲存夾檔
//        $attach = trim(remove_previous_uploaded($base_path . DIRECTORY_SEPARATOR . trim($_POST['mnode']), trim($_POST['o_att'])).
//                chr(9).
//                save_upload_file($base_path . DIRECTORY_SEPARATOR . trim($_POST['mnode']), $quota_limit, $quota_used)
//               );

//        if ($_POST['mp3path'] != '') {
//            if (strpos($_POST['o_att'],$_POST['mp3path']) === false) {
//                if (!empty($attach)) $attach .= chr(9);
//                $attach .= $_POST['mp3path'].chr(9).$_POST['mp3path'];
//            }
//        }


//        if ($_POST['wbpath'] != '') {
//            if (strpos($_POST['o_att'],$_POST['wbpath']) === false) {
//                if (!empty($attach)) $attach .= chr(9);
//                $attach .= $_POST['wbpath'].chr(9).$_POST['wbpath'];
//            }
//        }
        
        // 刪除檔案
        $deleteFlag = $_POST['deleteFlag'];
        if (is_array($deleteFlag) && count($deleteFlag) >= 1) {
            foreach ($deleteFlag as $k => $v) {
                if ($v === 'D') {
                    unlink($base_path . DIRECTORY_SEPARATOR . $nnode . '/' . $_POST['diskFilename'][$k]);
                }
            }
        }

        // 修改資料庫
        dbSet(USE_TABLE,
              "pt='{$_POST['etime']}',
              email = '$sysSession->email',
              homepage = '$sysSession->homepage',
              subject = '$subject',
              content = '$content',
              attach = " . ($attach?"'$attach'":"NULL"),
              "board_id = $bid and site = $sysSiteNo".
              " and node = '".trim($_POST['mnode'])."'"
             );
        $node_id = trim($_POST['mnode']);
        wmSysLog($sysSession->cur_func, $bid , $_POST['mnode'] , 0, 'auto', $_SERVER['PHP_SELF'], 'Edit '.BOARD_TYPE.' post(site:' . $sysSiteNo . ', subject:' . $subject . ')');
        
        
    $oldFolder = '/tmp/' . md5($_COOKIE['idx']) . '/';
//        $newFolder = $base_path . DIRECTORY_SEPARATOR . $nnode;
    $newFolder = $base_path . DIRECTORY_SEPARATOR . $nnode . '/';
    create_folders($newFolder);

    $oldFolderFiles = glob($oldFolder . '/*');
    if (is_array($oldFolderFiles) && count($oldFolderFiles) >= 1) {
        foreach ($oldFolderFiles as $v) {
            if (empty($_COOKIE['show_me_info']) === false) {
                echo '<pre>';
                var_dump(filesize($v), $v, $newFolder . pathinfo($v, PATHINFO_BASENAME));
                echo '</pre>';
            }
            copy($v, $newFolder . pathinfo($v, PATHINFO_BASENAME));
            unlink($v);
        }
    }
        
    } else {
        // 張貼或回覆
        // 判斷是否重複張貼
        if (dbGetOne(USE_TABLE, 'count(*)', "board_id=$bid and poster='$username' and pt > DATE_SUB(NOW(), INTERVAL 1 DAY) and content='$content '")) {
            if (BOARD_TYPE == 'board') {
                $errMsg = array();
                $errMsg[] = array(
                    'id' => 'content',
                    'message' => $MSG['repeat_post'][$sysSession->lang],
                    'rule' => 'Hongu_Validate_Validator_Repeat'
                );

                $result = array(
                    'error' => $errMsg,
                    'annFlag' => ($bid === $annBid) ? true : false
                );

                echo json_encode($result);
                wmSysLog($sysSession->cur_func, $sysSession->course_id, $bid, 1, 'auto', $_SERVER['PHP_SELF'], '討論版重複張貼(' . $content .')');
                die();
//                header('Location: '.($sysSession->post_no?"/forum/510,{$bid},{$sysSession->post_no}.php":"/forum/500,{$bid},{$sysSession->page_no},{$sysSession->sortby}.php"));
            } else {
//                header('Location: '.($sysSession->q_post_no?"/forum/570,{$bid},{$sysSession->q_post_no}.php":"/forum/560,{$bid},{$sysSession->q_page_no},{$sysSession->q_sortby}.php"));
            }
            exit;
        }

        if ($_POST['mp3path'] != '') {
            if (!empty($attach)) $attach .= chr(9);
            $attach .= $_POST['mp3path'].chr(9).$_POST['mp3path'];
        }

        if ($_POST['wbpath'] != '') {
            if (!empty($attach)) $attach .= chr(9);
            $attach .= $_POST['wbpath'].chr(9).$_POST['wbpath'];
        }

        // 如果是回覆，重新取主題，以避免竄改
        if ($_POST['isReply'] === '1') {
            list($subject) = dbGetStSr(
                USE_TABLE,
                'subject',
                "board_id ={$bid} and length(node) = 9 AND node='{$_POST['nid']}'",
                ADODB_FETCH_NUM
            );

            $subject = 'Re: ' . $subject;
        }

        // 加入資料庫
        if (BOARD_TYPE == 'board') {
            $fields = 'board_id,node,site,pt,poster,realname,email,homepage,subject,content,attach,lang';
            $values = "$bid, '$nnode',$sysSiteNo".
                      ", NOW(), '$username', '$realname ', ".
                      "'$sysSession->email', '$sysSession->homepage ', '$subject ', '$content ',".
                      ($attach?"'$attach'":"NULL") . "," . $ll[$sysSession->lang];
            // APP 課程公告 PUSH 用 - Begin
            $newPostFlag = true;
            // APP 課程公告 PUSH 用 - End
        } else {
            $fields = "board_id,node,site,pt,poster,realname,email,homepage,subject,content,attach,lang,".
                      "ctime,picker,path,type,post_node";
            $values = "$bid, '$nnode',$sysSiteNo".
                      ", NOW(), '$username', '$realname ', ".
                      "'$sysSession->email', '$sysSession->homepage ', '$subject ', '$content ',".
                      ($attach?"'$attach'":"NULL") . "," . $ll[$sysSession->lang].",Now(),'".
                      "$username','{$sysSession->q_path}','F',''";
        }
        dbNew(USE_TABLE, $fields, $values);

        // 如果是從複習快通車張貼，需與筆記關聯
        if(isset($_POST['noteid']) && isset($_POST['from']) && $_POST['from'] === 'review') {
            $nid = $_POST["noteid"];
            $fields = 'note_id, board_id, node, site';
            $values = $nid.','. $bid.', "' . $nnode . '",' . $sysSiteNo;
            dbNew('`WM_user_note_post`', $fields, $values);
        }
        
        if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0) {
            ErrorExit('Error:'.$sysConn->ErrorNo().' = '. $sysConn->ErrorMsg().'"');
        }
        $node_id = $nnode;
        if ($_POST['isReply'] === '1') {
            wmSysLog($sysSession->cur_func, $bid , $node_id, 0, 'auto', $_SERVER['PHP_SELF'], 'reply '.BOARD_TYPE.' post(site:' . $sysSiteNo . ', subject:' . $subject . ')');
        } else {
            wmSysLog($sysSession->cur_func, $bid , $node_id, 0, 'auto', $_SERVER['PHP_SELF'], 'new '.BOARD_TYPE.' post(site:' . $sysSiteNo . ', subject:' . $subject . ')');
        }

        // 遞增自己跟課程的張貼數
        dbSet('WM_term_major',  'post_times=post_times+1', "username='{$username}' and course_id='{$sysSession->course_id}'");
        dbSet('WM_term_course', 'post_times=post_times+1', "course_id='{$sysSession->course_id}'");
        
        
    $oldFolder = '/tmp/' . md5($_COOKIE['idx']) . '/';
//        $newFolder = $base_path . DIRECTORY_SEPARATOR . $nnode;
    $newFolder = $base_path . DIRECTORY_SEPARATOR . $nnode . '/';
    create_folders($newFolder);

    $oldFolderFiles = glob($oldFolder . '/*');
    if (is_array($oldFolderFiles) && count($oldFolderFiles) >= 1) {
        foreach ($oldFolderFiles as $v) {
            if (empty($_COOKIE['show_me_info']) === false) {
                echo '<pre>';
                var_dump(filesize($v), $v, $newFolder . pathinfo($v, PATHINFO_BASENAME));
                echo '</pre>';
            }
            copy($v, $newFolder . pathinfo($v, PATHINFO_BASENAME));
            unlink($v);
        }
    }
        
        $bname = $rs['bname'];
        $multiCaption = getCaption($bname);
        $board_name = $multiCaption[$sysSession->lang];

        $school_name	= $sysSession->school_name;					// 學校名稱
        $school_host    = $_SERVER['HTTP_HOST'];

        // Mail follow
        $MailData = Array();
        $MailData['attach']	= $attach;

        //$MailData['from']	= mailEncFrom($sysSession->course_name,' ');
        $MailData['subject']	= stripslashes($_POST['subject']); //$subject;
        $MailData['title']	= '==================' . $school_name. "\t" .$school_host . '==================' . "<br>\r\n";
        $MailData['course']	= $MSG['mail_cname'][$sysSession->lang]. $sysSession->course_name ."<br>\r\n" ;
        $MailData['body']	= $MSG['mail_board'][$sysSession->lang] . $board_name . "<br>\r\n" .
                              $MSG['mail_poster'][$sysSession->lang] . $username. '(' .$realname .')' . "<br>\r\n" .
                              $MSG['mail_ptime'][$sysSession->lang] . Date("Y-m-d H:i:s") . "<br>\r\n" .
                              $MSG['mail_subject'][$sysSession->lang] . $subject . "<br><br>\r\n" .
                              $content;
        $MailData['attach_dir']	= get_attach_file_path(BOARD_TYPE, $sysSession->board_ownerid, $bid) . DIRECTORY_SEPARATOR.$nnode;

        MailFollow($MailData,$bid);
    }
//    echo '<pre>';
//    var_dump($nnode);
//    echo '</pre>';

    // 儲存夾檔。如果有的話。
//        $attach = trim(save_upload_file($base_path . DIRECTORY_SEPARATOR . $nnode, $quota_limit, $quota_used));

    // 搬移檔案
//        $dirPersonal = md5($sysSession->username);
//        $dirPath = "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/board/temp/{$dirPersonal}/{$_POST['tmp']}/";
//        $oldFolder = $_SERVER['DOCUMENT_ROOT'] . $dirPath;
    

    // 更新quota資訊
    getCalQuota($sysSession->board_ownerid, $quota_used, $quota_limit);
    setQuota($sysSession->board_ownerid, $quota_used);

    // 是否為最新消息類型
    if(IsNewsBoard('news', $bid)) {
        $open_time   = (isset($_POST['ck_open_time']))? $_POST['open_time']:'0000-00-00 00:00:00';
        $close_time  = (isset($_POST['ck_close_time']))? $_POST['close_time']:'0000-00-00 00:00:00';
        $NEWS = dbGetStSr('WM_news_subject','news_id',"board_id='{$bid}'", ADODB_FETCH_ASSOC);
        dbSet('WM_news_posts', "open_time='{$open_time}',close_time='{$close_time}',news_id='{$NEWS['news_id']}'",
                "board_id={$bid} and node='{$node_id}'");
        if($sysConn->Affected_Rows() == 0) {
            dbNew('WM_news_posts','news_id,board_id,node,open_time,close_time',
                "{$NEWS['news_id']},{$bid},'{$node_id}','{$open_time}','{$close_time}'");
            createNewsXML($sysSession->school_id, 'news');
        }

        // APP 訊息推播 - Begin：未設定啟用時間，表示即時發佈、即時推播
        if ($open_time === '0000-00-00 00:00:00') {
            $dbHandler = new DatabaseHandler();
            $channels = $dbHandler->getAllUsers();

            $pushData = JsonUtility::encode(
                array(
                    'sender' => $sysSession->username,
                    'content' => $subject,
                    'alert' => $sysSession->school_name . $MSG['school_post_news'][$sysSession->lang],
                    'channel' => $channels,
                    'alertType' => 'NEWS',
                    'messageID' => $bid . '#' . $nnode
                )
            );
            require_once(sysDocumentRoot . '/xmlapi/push-handler.php');
        }
        // APP 訊息推播 - End
    } else if (BOARD_TYPE == 'quint' && IsNewsBoard('faq')) {
        createFAQXML($sysSession->school_id, 'faq');
    }
    
    // APP 課程公告 PUSH - Begin
    if (sysEnableAppServerPush && (intval($bid) === intval($annBid)) && $newPostFlag) {
        $pushData = JsonUtility::encode(
            array(
                'type' => 'bulletin',
                'id' => $bid . '#' . $nnode
            )
        );

        require_once(sysDocumentRoot . '/lib/app_course_push_handler.php');
    }
    // APP 課程公告 PUSH -End
    if (BOARD_TYPE == 'board') {
        // 回列表
        $result = array(
            'error'     => true,
            'annFlag' => ($bid === $annBid)? true : false,
            'nid' => $nnode,
        );
        echo json_encode($result);
//        header('Location: '.($sysSession->post_no?"/forum/510,{$bid},{$sysSession->post_no}.php":"/forum/500,{$bid},{$sysSession->page_no},{$sysSession->sortby}.php"));
    } else {
//        header('Location: '.($sysSession->q_post_no?"/forum/570,{$bid},{$sysSession->q_post_no}.php":"/forum/560,{$bid},{$sysSession->q_page_no},{$sysSession->q_sortby}.php"));
    }
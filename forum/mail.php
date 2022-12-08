<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/file_api.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/message/collect.php');
    require_once(sysDocumentRoot . '/mooc/models/forum.php');
    require_once(sysDocumentRoot . '/lang/forum.php');

    $sysSession->cur_func = '900100400';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    if(empty($_GET['target'])|| empty($_GET['node'])) die('wrong parameters');
    $type = (isset($_GET['type'])?$_GET['type']:'board');
    $cid = (isset($_GET['cid']) ? $_GET['cid'] : $sysSession->course_id);
    $bid = (isset($_GET['bid']) ? $_GET['bid'] : $sysSession->board_id);
    
    // 取討論版名稱
    $rsForum = new forum();
    $forumTopic = $rsForum->getCourseForumList($cid, array(), false, array($bid));
    $forumName = $forumTopic[sprintf("'%d'", mysql_real_escape_string($bid))]['board_name'];
    $bidName = (isset($forumName) ? $forumName : $sysSession->board_name);

    // 取得本 POST 內容
    if($type=='board')
        $RS = dbGetStSr('WM_bbs_posts', '*', "board_id = {$bid} and node = {$_GET['node']}", ADODB_FETCH_ASSOC);
    else
        $RS = dbGetStSr('WM_bbs_collecting','*',"board_id = {$bid} and node = {$_GET['node']}", ADODB_FETCH_ASSOC);

    // 取得收信人列表(去除空白資料 PREG_SPLIT_NO_EMPTY )
    if (preg_match(sysMailsRule, $_GET['target']))
        $target_mails = preg_split("/[ ,;]/", $_GET['target'], -1, PREG_SPLIT_NO_EMPTY);

    if ($target_mails) {
        /**
         * 1.1寄件者=課程名稱。改為寄件者之email
         * 1.2收件者email= 課程名稱@學校domain name
         * 1.3信件主旨=文章標題
         * 1.4信件內容=文章文章內容
         * 1.5信件附檔=文章附檔
         * 1.6信件內容第一行請加上「本信件由系統轉寄，請無直接回覆本信件」
         **/

        $school_name	= $sysSession->school_name;					// 學校名稱
        $school_host    = $_SERVER['HTTP_HOST'];  
    
        // 因應寄信內容可以被看見，給予完整URL
        preg_match('/^htt\w+:\/\//', $_SERVER['SCRIPT_URI'], $matches);
        $RS['content'] = str_replace ('<img alt="" src="/', '<img alt="" src="' . $matches[0] . $_SERVER['HTTP_HOST'] . '/', $RS['content']);      

        $MailData = Array();
        $MailData['from']	= mailEncFrom($sysSession->realname, $sysSession->email, 'utf-8');
        $MailData['to']		= implode(',', $target_mails);
        $MailData['subject']= html_entity_decode($RS['subject'], ENT_QUOTES);
        $MailData['body']	= '==================' . $school_name             . "\t"       . $school_host   . $MSG['mail_title'][$sysSession->lang] . "<br>\r\n" .
            $MSG['mail_cname'][$sysSession->lang]   . $sysSession->course_name ."<br>\r\n"  .
            $MSG['mail_board'][$sysSession->lang]   . $bidName  . "<br>\r\n" .
            $MSG['mail_poster'][$sysSession->lang]  . $RS['poster']            . '('        . $RS['realname'] .')'                                   . "<br>\r\n" .
            $MSG['mail_ptime'][$sysSession->lang]   . $RS['pt']                . "<br>\r\n" .
            $MSG['mail_subject'][$sysSession->lang] . $RS['subject']           . "<br>\r\n" .
            $RS['content'];
        $MailData['attach']	= $RS['attach'];
        $MailData['attach_dir']	= get_attach_file_path($type, $sysSession->board_ownerid) . DIRECTORY_SEPARATOR .$RS['node'];

        $mail = buildMail($MailData['from'], $MailData['subject'], $MailData['body'],'html',
            '',$MailData['attach'],$MailData['attach_dir'],0,FALSE);

        $mail->to = $MailData['to'];
        $mail->send();
        $msg = $MSG['mail_sent'][$sysSession->lang];
    } else {     
        $msg = $MSG['mail_send_error'][$sysSession->lang];
    }
?>
<?=json_encode($msg)?>
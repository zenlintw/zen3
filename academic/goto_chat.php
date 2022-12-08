<?php
    /**
     * 進聊天室
     *
     * @since   2003/12/25
     * @author  ShenTing Lin
     * @version $Id: goto_chat.php,v 1.1 2010/02/24 02:38:39 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/chatroom.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/common.php');

    $sysSession->cur_func = '2000200100';
    $sysSession->restore();
    if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }
    
    function goChatroom($val) {
        global $_COOKIE, $sysSession, $_SERVER, $sysConn;
        $rid = $sysConn->qstr($val);
        $idx = $sysConn->qstr($_COOKIE['idx']);
        dbSet('WM_session', "`room_id`={$rid}", "idx={$idx}");
        wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'auto', $_SERVER['PHP_SELF'], "goChatroom:{$val}");
    }

    // 這邊的判斷可能會因為 PHP 版本的更改而有所變動
//    echo '<pre>';
//    var_dump(!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA']));
//    echo '</pre>';
//    echo '<pre>';
//    var_dump($_REQUEST['HTTP_RAW_POST_DATA']);
//    echo '</pre>';
    if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
        header("Content-type: text/xml");
        echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
        
        if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
            echo '<manifest></manifest>';
            exit;
        }

        echo '<manifest>';
        
        $chat_id = trim(getNodeValue($dom, 'chat_id'));
        // WM5 教室討論室列表-偵測到還在討論室中
//        echo '<pre>';
//        var_dump('!empty($sysSession->room_id) && !empty($chat_id) && $chat_id !== $sysSession->room_id', !empty($sysSession->room_id) && !empty($chat_id) && $chat_id !== $sysSession->room_id);
//        var_dump($sysSession->room_id);
//        var_dump($chat_id);
//        echo '</pre>';
        if (!empty($sysSession->room_id) && !empty($chat_id) && $chat_id !== $sysSession->room_id) {
            dbSet('WM_session', "`room_id`='{$chat_id}'", "idx='{$_COOKIE['idx']}'");
            wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'auto', $_SERVER['PHP_SELF'], "chgChatroom: from {$sysSession->room_id} to {$chat_id}");
        }
        
        goChatroom($chat_id);
        require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
        // 判斷使用者是否使用行動裝置
        $detect = new Mobile_Detect;

        if ($detect->isMobile() && !$detect->isTablet()) {
            echo '<msg></msg>';
            echo '<uri>/learn/chat/index_phone.php</uri>';
        }else{
            echo '<msg></msg>';
            echo '<uri>/learn/chat/index.php</uri>';

//            global $sysConn;
//            $sysConn->debug = true;
            // 取討論室名稱
            list($title) = dbGetStSr('WM_chat_setting', '`title`', "`rid` = '{$chat_id}'", ADODB_FETCH_NUM);
            $lang = getCaption($title);
            $roomName = $lang[$sysSession->lang];

            // 名稱是「同步討論室」且「APP有開直播」時
            if (trim($roomName) === $MSG['msg_sync_chatroom'][$sysSession->lang]) {
                // 取直播路徑
                if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'APP_live_activity'")) === 1) {
                    $data = dbGetOne('APP_live_activity', 'url', 'course_id = ' . $sysSession->course_id . ' AND status = "on" ORDER BY begin_time DESC');
                    $live = '<live>' . $data . '</live>';
                    echo $live;
                }
            }
        }
        
        echo '</manifest>';
    }
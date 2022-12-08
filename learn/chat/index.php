<?php
/**
 * 聊天室
 *
 * @since   2003/11/26
 * @author  ShenTing Lin
 * @version $Id: index.php,v 1.1 2010/02/24 02:39:06 saly Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
require_once(sysDocumentRoot . '/lang/chatroom.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

#========= main ========================

$sysSession->cur_func = '2000200100';
$sysSession->restore();
if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}
// list($cnt) = dbGetStSr('WM_chat_Session', 'count(*)', "`username`='{$sysSession->username}'");
// ////////////////////////////////////////////////////////////////////////////

if ($_GET['r']) {
    $sysSession->room_id = $_GET['r'];
    $sysSession->restore();
}

$rid = $sysSession->room_id; // 聊天室編號
if (empty($rid)) {
    setRoomId('');
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], '禁止進入(room id is empty)!');
    showStateError($MSG['chat_deny'][$sysSession->lang]);
    die();
}

//假若這會議室是breeze live型式，就改以/breeze/joinmeeting
if (breeze == 'Y') {
    list($meetingtype, $breeze_meetingID) = dbGetStSr('WM_chat_mmc', 'meetingType,meetingID', "rid='{$sysSession->room_id}'", ADODB_FETCH_NUM);
    if ($meetingtype == 'breeze') {
        $sysSession->room_id = 0;
        $sysSession->restore();
        list($scoid, $urlpath) = explode(':', $breeze_meetingID);
        $url = "/breeze/JoinMeeting.php?scoid=" . $scoid . "&urlpath=" . $urlpath;
        header("Location: $url");
        exit;
    }
}


$now = time();
$RS1 = dbGetStSr('WM_chat_setting', '`owner`, `open_time`, `close_time`, `state`', "`rid`='{$rid}'", ADODB_FETCH_ASSOC);
if ($RS1) {
    // 檢查是否非法進入
    if (strlen($RS1['owner']) > 7 && substr($RS1['owner'], 0, 8) != $sysSession->course_id) {
        setRoomId('');
        wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 6, 'auto', $_SERVER['PHP_SELF'], 'illegal access chatroom!');
        showStateError('Illegal Access!!');
        die();
    }
    
    $groups = explode('_', $RS1['owner']);
    if (count($groups) == 3) {
        list($isMember) = dbGetStSr('WM_student_div', '1', "team_id={$groups[1]} and group_id={$groups[2]} and course_id='{$sysSession->course_id}' and username='{$sysSession->username}'", ADODB_FETCH_NUM);
        $isTeacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id);
        if (!$isTeacher && !$isMember) {
            setRoomId('');
            wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 6, 'auto', $_SERVER['PHP_SELF'], 'illegal access chatroom!');
            showStateError('Illegal Access!!');
            die();
        }
    }
    
    $open   = $RS1['open_time'];
    $close  = $RS1['close_time'];
    $status = $RS1['state'];
    // 檢查是否有儲存在資料庫中，有則需要參考設定資料
    
    $level = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id);
    
    // 檢查的順序：狀態 -> 日期 -> 身份
    $ot = $sysConn->UnixTimeStamp($open);
    $ct = $sysConn->UnixTimeStamp($close);
    
    // 'assistant','instructor','teacher'
    // 'disable','open','taonly'
    switch (trim($status)) {
        case 'disable': // 停用
            setRoomId('');
            wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 2, 'auto', $_SERVER['PHP_SELF'], '討論室尚未啟用!');
            showStateError($MSG['chat_disable'][$sysSession->lang]);
            die();
            break;
        
        case 'open': // 啟用
        case 'taonly': // 教師、助教專用
            if ((!$level) && (trim($status) == 'taonly')) { // 代表此user沒有教師、助教身份
                setRoomId('');
                wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 5, 'auto', $_SERVER['PHP_SELF'], '教師、助教專用討論室!');
                showStateError($MSG['status_taonly'][$sysSession->lang]);
                die();
            } else {
                if (($ot != 0) && ($now < $ot)) {
                    setRoomId('');
                    wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 3, 'auto', $_SERVER['PHP_SELF'], '討論室尚未開放!');
                    showStateError($MSG['chat_not_open'][$sysSession->lang]);
                    die();
                }
                if (($ct != 0) && ($now > $ct)) {
                    setRoomId('');
                    wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 4, 'auto', $_SERVER['PHP_SELF'], '討論室已經關閉!');
                    showStateError($MSG['chat_closed'][$sysSession->lang]);
                    die();
                }
            }
            break;
        /*
        case 'taonly' :
        break;
        */
        default:
    }
} else {
    setRoomId('');
    wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 7, 'auto', $_SERVER['PHP_SELF'], 'No this ChatRoom!');
    showStateError('No this ChatRoom!');
    die();
}

include_once(sysDocumentRoot . '/learn/chat/chat_room.php');
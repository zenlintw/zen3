<?php
/**
 * 主要的聊天室列表
 *     僅供呼叫用
 *
 * @since   2004/12/14
 * @author  ShenTing Lin
 * @version $Id: chat_main_list.php,v 1.2 2009-08-03 06:26:15 edi Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/chatroom.php');
require_once(sysDocumentRoot . '/lang/live_list.php');
require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');
require_once(sysDocumentRoot . '/webmeeting/global.php');
require_once(sysDocumentRoot . '/breeze/global.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/co_meeting/module/meeting.php');

#========= main =============
$sysSession->cur_func = '2000200100';
$sysSession->restore();
if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

// 必須設定 $owner_id
if (!isset($owner_id))
    die($MSG['access_deny'][$sysSession->lang]);
$owner_id = trim($owner_id);

// 不得利用 cookie、post 或 get 方法設定 $owner_id
$ary      = array(
    $_COOKIE['owner_id'],
    $_POST['owner_id'],
    $_GET['owner_id']
);

if (in_array($owner_id, $ary))
    die($MSG['access_deny'][$sysSession->lang]);

// 原始總筆數
list($total_cnt) = dbGetStSr('WM_chat_setting', 'count(*)', "`owner`='{$owner_id}'", ADODB_FETCH_NUM);

if (intval($total_cnt) <= 0) {
    // 建立一個預設的聊天室
    $rid   = uniqid('');
    $title = serialize($MSG['msg_sync_chatroom']);
    
    dbNew('WM_chat_setting', '`rid`, `owner`, `title`, `host` , `get_host`, ' . '`maximum`, `exit_action`, `jump`, `open_time`, `close_time`, ' . '`state`, `visibility`, `media`, `ip`, `port`, `protocol`', "'{$rid}', '{$owner_id}', '{$title}', '', 'N', " . "0, '', 'allow', 'NULL', 'NULL', 'open', 'visible', 'disable', '', 255, 'TCP'");
}

// 排序欄位陣列
$cour_sort = array(
    1 => 'open_time',
    2 => 'close_time',
    3 => 'state'
);

/** 排序 */
$_POST['sortby'] = intval($_POST['sortby']);
$sortby          = $cour_sort[$_POST['sortby']];
if (empty($sortby))
    $sortby = 'permute';

$order = trim($_POST['order']);
if (!in_array($order, array(
    'asc',
    'desc'
)))

$order = 'desc';
$smarty->assign('sort', $sortby);
$smarty->assign('order', $order);

chkSchoolId('WM_chat_setting');

$datalist = array();
$mmc_rids = array();

//刪除過期的Breeze Live暫時性會議
if ($Breeze_enable) {
    $breeze_meetings = getBreezeMeetingList($sysSession->course_id);
    $sess            = getEnableSessionId();
    for ($i = 0, $size = count($breeze_meetings); $i < $size; $i++) {
        $urlpath     = getMeetingUrlPath($sess, $breeze_meetings[$i]->scoId);
        $meetingData = getMeetingRid($breeze_meetings[$i]->scoId . ":" . $urlpath);
        $mmc_rids[]  = $meetingData->rid;
    }
    DeleteExpireMeetingRid($meetingData->rid, $sysSession->course_id, 'breeze');
}

if (($MMC_enable) || ($Anicam_enable) || ($Breeze_enable)) {
    if ($MMC_enable) {
        echo '<iframe id="ifrm_joinnet" src="about:blank" style="display:none" width="100" height="36"></iframe>';
        $online_meeting_info = get_online_meeting($MMC_Server_addr, $MMC_Server_API_Port, $MMC_Server_API_RootURL, $WM3_Meeting_Owner);
    } else {
        $online_meeting_info = 0;
    }
    
    if (strcmp($online_meeting_info, '0') != 0) {
        list($meetingId, $ownerName) = explode(':', $online_meeting_info);
        $meetingData = getMeetingRid($meetingId);
    }
    
    DeleteExpireMeetingRid($meetingData->rid, $sysSession->course_id, 'joinnet');
    
    $arr = getChatroomMMCList($sysSession->course_id);
    
    for ($i = 0; $i < count($arr); $i++) {
        if ($online_meeting_info == 0) {
            $mmc_rids[] = $arr[$i]->rid;
            if ($arr[$i]->meetingType == 'joinnet')
                continue;
        }
        
        if ($arr[$i]->meetingType == 'joinnet') {
            
            if ($arr[$i]->meetingID != $meetingId) {
                $mmc_rids[] = $arr[$i]->rid;
                continue;
            }
        }
        
        $mmc_rids[]          = $arr[$i]->rid;
        $meetingData         = getMeetingData($arr[$i]->rid);
        $lang                = getCaption($meetingData->title);
        // 討論室名稱
        $rs->fields['title'] = $lang[$sysSession->lang];
        
        $ot                            = intval($meetingData->open_time);
        $ct                            = intval($meetingData->close_time);
        $ot                            = $MSG['from'][$sysSession->lang] . ((empty($ot)) ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($meetingData->open_time)));
        $ct                            = $MSG['to'][$sysSession->lang] . ((empty($ct)) ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($meetingData->close_time)));
        // 起迄時間
        $rs->fields['open_time_view']  = $ot;
        $rs->fields['close_time_view'] = $ct;
        
        // 狀態
        $rs->fields['state_view'] = $chatStatus[$meetingData->state];
        
        
        if ($arr[$i]->meetingType == 'joinnet') {
            $rs->fields['onclick'] = "goJoinnet('" . trim($meetingData->rid) . "')";
        } else if ($arr[$i]->meetingType == 'breeze') {
            list($scoId, $urlpath) = explode(':', $arr[$i]->meetingID);
            $rs->fields['onclick'] = "goBreeze('" . trim($scoId) . "','" . trim($urlpath) . "')";
        } else {
            $rs->fields['onclick'] = "goChat('" . trim($meetingData->rid) . "')";
        }
        
        $datalist[] = $rs->fields;
        
    }
    
}

//取得會議入口
$meeting = new Meeting();
$datalist = $meeting->getListForMainList($sysSession->username, $sysSession->course_id);
$MTinfo = $meeting->isMeetingExist($sysSession->course_id);

$nickname = (empty(trim($sysSession->realname)))?$sysSession->username:$sysSession->realname;
$smarty->assign("nickname" , $nickname);
$smarty->assign("confid" , $MTinfo['confid']);
$smarty->assign("meeting_ip" , $meeting::serverIP);

$rs = dbGetStMr(
        'WM_chat_setting', 
        'rid, owner, title, host, get_host, maximum, exit_action, jump, open_time, close_time, state, visibility, media, ip, port, protocol, permute, tone', 
        "owner='{$owner_id}' AND `state` in ('disable','open') AND `visibility`='visible' order by " . $sortby . " " . $order,
        ADODB_FETCH_ASSOC
    );

// $datalist = array();
if ($rs && $rs->RecordCount() >= 1) {
    while (!$rs->EOF) {
        if (in_array(trim($rs->fields['rid']),$mmc_rids))         //因為已呈現在上列的程式段
        {
          
          $rs->MoveNext();
          continue;
        }
        // 討論室名稱
        $multiCaption = getCaption($rs->fields['title']);
        $title = $multiCaption[$sysSession->lang];
        $rs->fields['title'] = $title;
        
        // 起迄時間
        $ot = intval($rs->fields['open_time']);
        $ct = intval($rs->fields['close_time']);
        $ot = $MSG['from'][$sysSession->lang] . ((empty($ot)) ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($rs->fields['open_time'])));
        $ct = $MSG['to'][$sysSession->lang] . ((empty($ct)) ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($rs->fields['close_time'])));
        $rs->fields['open_time_view'] = $ot;
        $rs->fields['close_time_view'] = $ct;
        
        // 狀態
        $rs->fields['state_view'] = $chatStatus[$rs->fields['state']];
        
        // 動作
        $rs->fields['action'] = 'disabled';
        if ($rs->fields['state'] === 'open') {
            $now   = time();
            $open  = $sysConn->UnixTimeStamp($rs->fields['open_time']);
            $close = $sysConn->UnixTimeStamp($rs->fields['close_time']);
            if (!(!empty($open) && ($open > $now) || !empty($close) && ($now > $close))) {
                $rs->fields['action'] = 'enable';
            }
        }
        
        // 檢查討論室是否已經沒人，若沒人要轉貼到討論室紀錄
        $rid = trim($rs->fields['rid']);
//        echo '<pre>';
//        var_dump($title);
//        echo '</pre>';
        list($cntChatSession) = dbGetStSr('WM_chat_session', 'COUNT(idx)', "`rid` = '{$rid}'", ADODB_FETCH_NUM);
//        echo '<pre>';
//        var_dump($cntChatSession);
//        echo '</pre>';
//        list($cntSession) = dbGetStSr('WM_session', 'COUNT(idx)', "`room_id` = '{$rid}'", ADODB_FETCH_NUM);
//        echo '<pre>';
//        var_dump($cntSession);
//        echo '</pre>';
        list($cntChatMsg) = dbGetStSr('WM_chat_msg', 'COUNT(rid)', "`rid` = '{$rid}'", ADODB_FETCH_NUM);
//        echo '<pre>';
//        var_dump($cntChatMsg);
//        echo '</pre>';
        if ((int)$cntChatSession === 0 && (int)$cntChatMsg >= 1) {
//            global $sysConn;
//            $sysConn->debug = true;
            
            ob_start(); 
            $GLOBALS['HTTP_RAW_POST_DATA'] = '<manifest><exit>notebook</exit></manifest>';
            $sysSession->room_id = $rid;
            require_once(sysDocumentRoot . '/learn/chat/chat_logout.php');
            ob_end_clean();
            
            
//            global $sysConn;
//            $sysConn->debug = 0;
        }
                
        $datalist[] = $rs->fields;
        $rs->MoveNext();
    }
}

// 取目前是否有直播
$data = dbGetRow('APP_live_activity', 'id, name, url, begin_time', 'course_id = ' . $sysSession->course_id . ' AND status = "on" ORDER BY begin_time DESC');
// 如果有直播中，將同步討論室更名為同步討論室 ( 直播中 )
if (count($data) >= 1 && $data['begin_time'] <= date('Y-d-d H:i:s', time())) {
    foreach ($datalist as $k => $v) {
        if (trim($v['title']) === $MSG['msg_sync_chatroom'][$sysSession->lang] && $v['host'] === '' && $v['state'] === 'open') {
            $datalist[$k]['title'] = '<i class="fas fa-video" style="color: #CE1A0B; margin: 0 0.5em 0em 0em;"></i>' . $MSG['course_live_broadcast'][$sysSession->lang];
            $datalist[$k]['open_time'] = $data['begin_time'];
            $datalist[$k]['open_time_view'] = $data['begin_time'];
        }
    }
}

// assign
$smarty->assign('MSG', $MSG);
$smarty->assign('datalist', $datalist);

// output
if ($profile['isPhoneDevice']) {
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('common/course_header.tpl');
    $smarty->display('learn/chat_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}else{
    // output
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('learn/chat_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
}
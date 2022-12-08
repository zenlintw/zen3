<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/chatroom.php');
require_once(sysDocumentRoot . '/lib/file_api.php');

/*
 * 由 Owner_id 取得討論版之擁有者( 學校, 班級, 課程, 群組 )
 * @param $owner_id : 擁有者( 學校、課程、班級或群組編號, 群組編號為三欄合成之編號 )
 * @param $rec_readonly
 * 成功傳回 true , 失敗 false
 */
function getOwner($owner_id, &$rec_readonly)
{
    global $sysSession, $sysConn;
    
    switch (strlen($owner_id)) {
        case 5: // 學校討論版
            $rec_readonly = 1;
            return $sysSession->school_name;
            break;
        
        case 7: // 班級
            $rec_readonly = 1;
            return $sysSession->class_name;
            break;
        case 15: // 班級小組
            $rec_readonly = 0;
            return $sysSession->class_name;
            break;
        
        case 8: // 課程
            $rec_readonly = 1;
            return $sysSession->course_name;
            break;
        
        case 16: // 課程小組
            $rec_readonly = 0;
            return $sysSession->course_name;
            break;
        
        default:
            $rec_readonly = 1;
            return null;
            break;
    }
}

/* 產生討論板夾檔存放目錄 (不含 node)
 * ( 注意: 此 fucntion 改自 /lib/file_api.php , 若要 include file_api.php 需小心重複 )
 *
 * @param $board_id : 討論板編號
 * @param $owner_id : 擁有者( 學校、課程、班級或群組編號, 群組編號為三欄合成之編號 )
 * @return 傳回路徑
 */
function get_forum_attach_file_path($board_id, $owner_id)
{
    global $sysSession;
    
    if (empty($owner_id))
        return null;
    
    $ret = '/base/' . $sysSession->school_id;
    
    switch (strlen($owner_id)) {
        case 5: // 學校
            break;
        
        case 7: // 班級
            $class_id = substr($owner_id, 0, 7);
            $ret .= DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . $class_id;
            break;
        case 15: // 班級群組
            $class_id = $owner_id;
            $ret .= DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . $class_id;
            break;
        
        case 8: // 課程
            $course_id = $owner_id;
            $ret .= DIRECTORY_SEPARATOR . 'course' . DIRECTORY_SEPARATOR . $course_id;
            break;
        case 16: // 課程群組
            $course_id = substr($owner_id, 0, 8);
            $ret .= DIRECTORY_SEPARATOR . 'course' . DIRECTORY_SEPARATOR . $course_id;
            break;
        
        default: // 以上皆非, 傳回 null
            return null;
    }
    $ret .= DIRECTORY_SEPARATOR . 'board' . DIRECTORY_SEPARATOR . $board_id;
    return $ret;
}

/**
 * 建立討論板
 * @param array  $bname : 討論版名稱(各語系)
 * @param array  $title : 討論版主旨(該語系)
 * @param array  $owner_id : 討論版擁有者
 * @param string  $extras: 討論版特殊設定 ( 如 'extras=0;' )
 * @return int : 討論版編號
 **/
function dbNewBoard($bname, $owner_id, $extras = 'rank=0;')
{
    global $sysConn, $sysSession;
    
    $boardName = addslashes(serialize($bname));
    $RS        = dbNew('WM_bbs_boards', 'bname, title, owner_id, extras', "'{$boardName}', '{$bname[$sysSession->lang]}',{$owner_id},'{$extras}'");
    if ($RS) {
        $board_id = $sysConn->Insert_ID();
        
        // 建立討論板存放夾檔的目錄
        $BoardPath = get_forum_attach_file_path($board_id, $owner_id);
        mkdirs(sysDocumentRoot . $BoardPath);
        
        return $board_id;
    } else {
        return 0;
    }
}

/**
 * 取得 owner_id 所對應類型
 * @param $owner_id : 擁有者( 學校、課程、班級或群組編號, 群組編號為三欄合成之編號 )
 * @return string 類型名稱
 **/
function getOwnerType($owner_id)
{
    $len = strlen($owner_id);
    switch ($len) {
        case 5: // 學校
            return 'school';
        case 7: // 班級
            return 'class';
        case 8: // 課程
            return 'course';
        case 15: // 目前只有課程小組討論室, 沒有班級小組討論室
            return 'class_grp';
        case 16: // 是群組討論板 , 不需建立討論板
            return 'course_grp';
        default:
            if ($len)
                return 'others';
            else
                return null;
    }
}

/**
 * 建立討論室紀錄板
 * @param array $bname : 討論室紀錄版名稱(各語系)
 * @param array $owner_id : 討論版擁有者
 * @param array $result: 版號及紀錄板(chat records)編號陣列
 * @return string 所取得的值
 **/
function addRecordBoards($bname, $owner_id, &$result)
{
    global $sysConn, $sysSession;
    
    list($cnt) = dbGetStSr('WM_bbs_boards', 'count(*) as cnt', '1', ADODB_FETCH_NUM);
    if ($cnt == 0) {
        $RS = dbNew('WM_bbs_boards', 'board_id', '1000000000');
    }
    
    $result = Array(
        'board_id' => 0,
        'rec_id' => 0
    );
    
    $len              = strlen($owner_id);
    $rollback_boardid = 0;
    switch ($len) {
        case 5: // 學校
            $type               = 'school';
            $result['board_id'] = dbNewBoard($bname, $owner_id);
            $rollback_boardid   = $result['board_id'];
            break;
        case 7: // 班級
            $type               = 'class';
            $result['board_id'] = dbNewBoard($bname, $owner_id);
            $rollback_boardid   = $result['board_id'];
            break;
        case 8: // 課程
            $type               = 'course';
            $result['board_id'] = dbNewBoard($bname, $owner_id);
            $rollback_boardid   = $result['board_id'];
            break;
        case 15: // 目前只有課程小組討論室, 沒有班級小組討論室
            // $type = 'class_grp';
            return false;
        case 16: // 是群組討論板 , 不需建立討論板
            {
            // $type = 'course_grp';
            $course_id = substr($owner_id, 0, 8);
            $team_id   = substr($owner_id, 8, 4);
            $group_id  = substr($owner_id, 12, 4);
            // 取課程小組討論板
            list($board_id) = dbGetStSr('WM_student_group', 'board_id', "course_id={$course_id} and group_id={$group_id} and team_id={$team_id}", ADODB_FETCH_NUM);
            if ($board_id)
                $result['board_id'] = $board_id;
            else {
                echo "<!-- course group error : course_id={$course_id} and group_id={$group_id} and team_id={$team_id}-->\r\n";
                return false;
            }
            break;
        }
        default:
            echo "<!-- wrong owner_id (2): '{$owner_id}' -->\r\n";
            return false;
    }
    
    if (isset($result['board_id'])) {
        // 加入 WM_chat_records
        $RS1 = dbNew('WM_chat_records', 'board_id,type,owner_id', "{$result['board_id']},'{$type}',{$owner_id}");
        if ($RS1) {
            $result['rec_id'] = $sysConn->Insert_ID();
            return true;
        } else {
            echo "<!-- add chat_records failed -->\r\n";
            if ($rollback_boardid)
                dbDel('WM_bbs_boards', "board_id={$rollback_boardid}");
            return false;
        }
    }
    
    echo "<!-- can not get board id: '{$owner_id}' -->\r\n";
    return false;
}

/**
 * 取得討論室紀錄板號( 在取編號同時, 若該板不存在, 則會建立之 )
 * @param array $bname : 討論室紀錄板名稱(各語系)
 * @param array $result: 版號及討論室紀錄編號(rec_id)陣列
 * @return string 所取得的值
 **/
function dbGetRecordBoard($owner_id, &$result)
{
    global $sysConn, $MSG;
    
    if (!($type = getOwnerType($owner_id))) {
        echo "<!-- wrong owner : $owner_id -->\r\n";
        return false;
    }
    
    // 先取得討論室紀錄板號( 若無該版則建立一個 )
    $RS = dbGetStMr('WM_chat_records', 'rec_id,board_id', "type='{$type}' and owner_id={$owner_id}", ADODB_FETCH_ASSOC);
    if (!$RS) { // 查詢失敗
        echo "<!-- type='{$type}' and owner_id={$owner_id}-->\r\n";
        return false;
    }
    
    if ($RS->EOF) { // 沒有會議室紀錄版
        // 建立一個
        $bname = $MSG['chat_records'];
        return addRecordBoards($bname, $owner_id, $result);
    } else {
        $result = Array(
            'board_id' => $RS->fields['board_id'],
            'rec_id' => $RS->fields['rec_id']
        );
        return true;
    }
    
    return false;
}
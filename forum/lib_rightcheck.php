<?php
    /**
     * 討論版"整批" 權限函式庫
     *
     * @since   2004/07/08
     * @author  KuoYang Tsao
     * @copyright 2004 SUNNET
     **/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/learn/chatrec/lib_chat_records.php');

    // 檢查是否為學校管理員
    //
    function IsSchoolManager($school_id, $username)
    {
        global $sysRoles;
        return aclCheckRole($username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $school_id);
    }

    // 檢查是否為班級導師
    //
    function IsClassDirector($class_id, $username)
    {
        global $sysRoles;
        return aclCheckRole($username, $sysRoles['director'], $class_id);
    }

    // 檢查是否為課程教師
    //
    function IsCourseTeacher($course_id, $username)
    {
        global $sysRoles;
        return aclCheckRole($username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $course_id);
    }

    // 檢查是否為群組組長
    //
    function IsGroupCaptain($course_id, $team_id, $group_id, $username)
    {
        global $sysConn;
        if(empty($course_id) || empty($team_id) || empty($group_id) || empty($username))
            return false;

        list($IsGroupCaptain) = dbGetStSr('WM_student_group', 'count(*)', "course_id=$course_id and team_id=$team_id and group_id=$group_id and captain='$username'", ADODB_FETCH_NUM);
        return $IsGroupCaptain;
    }
    
    // 檢查是否為群組組員
    //
    function IsGroupMember($course_id, $team_id, $group_id, $username)
    {
        global $sysConn;
        if(empty($course_id) || empty($team_id) || empty($group_id) || empty($username))
            return false;
    
        $ct = dbGetOne('WM_student_div', 'count(*)', "course_id=$course_id and team_id=$team_id and group_id=$group_id and username='$username'");
        return ($ct>0);
    }

    /**
     * 判斷討論版開放或關閉或開放參觀
     * @param int $nt : 目前時間( unix timestamp)
     * @param int $ot : 啟用時間( unix timestamp)
     * @param int $ct : 關閉時間( unix timestamp)
     * @param int $st : 參觀時間( unix timestamp)
     * @return string : 'open':啟用 'close':關閉 'share':參觀
     */
    function getBoardStatus($nt, $ot, $ct, $st) {
        if(empty($ot)) {     // 開啟時間不限
    
            if(empty($ct))    // 關閉時間不限
                return 'open';
            else if($nt<$ct)    // 目前時間在關閉時間內
                return 'open';
            else    // 目前時間超出關閉時間
            {
                if(empty($st))    // 參觀時間不設
                    return 'close';
                else if($nt>=$st)        // 有參觀時間, 且已到參觀時間
                    return 'share';
                else                    // 有參觀時間, 但超出參觀時間
                    return 'close';
            }
    
        } else {        // 開啟時間有設
    
            if($nt<$ot) {    // 未到啟用時間
                if(empty($st))    // 參觀時間不設
                    return 'notopen';
                else if($nt>=$st)        // 有參觀時間, 且已到參觀時間
                    return 'share';
                else
                    return 'close';
            }
            else {            // 已到開放時間
                if(empty($ct))    // 不設關閉時間
                    return 'open';
                else { // 有設關閉時間
                    if($nt<$ct)    // 開放時間內
                        return 'open';
                    else {    // 目前時間超出關閉時間
                        if(empty($st))        // 未設參觀時間
                            return 'close';
                        else if ($nt>=$st)  // 有參觀時間, 未到參觀時間
                            return 'share';
                        else                  // 有參觀時間, 且已到參觀時間
                            return 'close';
                    }
                }
            }
    
        }
    }
    
    /*
     * IsCourseBBS()
     *    是否為課程公告板
     *    @param string $owner_id : 討論板 owner_id
     *    @param int    $board_id : 討論板號
     *    @return bool : 是=true   否( 或失敗 ) = false
     */
    function IsCourseBBS($owner_id, $board_id) {
        if(strlen($owner_id)!=8) return false;    // 不是課程
        $RS = dbGetStSr('WM_term_course', 'count(*) as total',"course_id='{$owner_id}' and bulletin={$board_id}", ADODB_FETCH_ASSOC);
        return ($RS && $RS['total']>0);
    }
    
    /*
     *    檢驗是否為 學校, 班級, 課程, 群組 討論版
     *    且對其是否具刪除權限
     */
    function ChkRight($board_id) {
        global $sysSession;

        // Get owner_id of Board
        // if(empty($Board_Owner) || empty($Board_OwnerID)) {
        if(empty($board_id)) return false;

        // 移除判斷有無 $sysSession->board_ownerid，改為一律重新取得討論版擁有者
        $rs  = dbGetStSr('WM_bbs_boards', 'owner_id', "board_id={$board_id}", ADODB_FETCH_ASSOC);
        if(!$rs || $rs['owner_id'] === 0) {
            return false;
        }
        $sysSession->board_ownerid = $rs['owner_id'];

        switch(strlen($sysSession->board_ownerid)) {
            case 5:// 學校討論版
                return IsSchoolManager($sysSession->board_ownerid, $sysSession->username);

            case 7:// 班級
                return IsClassDirector($sysSession->board_ownerid, $sysSession->username);

            case 8:// 課程
                return IsCourseTeacher($sysSession->board_ownerid, $sysSession->username);

            case 16:// 群組
                // 檢查是否為群組組長
                $course_id = substr($sysSession->board_ownerid, 0, 8);
                $team_id   = substr($sysSession->board_ownerid, 8, 4);
                $group_id  = substr($sysSession->board_ownerid,12, 4);
                $right     = (IsGroupCaptain($course_id, $team_id, $group_id, $sysSession->username) ||
                              IsCourseTeacher($course_id, $sysSession->username));
                return $right;
            default:
                return false;
        }
    }
    
    /*
     *    檢驗是否為 學校, 班級, 課程, 群組 討論版
     *    且對其是否具讀取權限
     */
    function ChkBoardReadRight($board_id) {
        global $sysSession, $sysConn, $sysRoles;
        
        if (empty($board_id) || !is_numeric($board_id)) return false;
        if ($board_id < 1000000000) return false;
        
        // 學校管理者身份，對任何討論板皆能讀取
        if (aclCheckRole($sysSession->username, $sysRoles['root']|$sysRoles['administrator']|$sysRoles['manager'], $sysSession->school_id) && $sysSession->env != 'learn') {
            return true;
        }
        
        // 取出討論板的owner
        $board_ownerid = dbGetOne('WM_bbs_boards','owner_id',sprintf('board_id=%d',$board_id));
        
        // 學校討論版 - 所有人都可以讀
        if (strlen($board_ownerid) == 5) return true;

        // 討論版時間與狀態控管
        if ($sysSession->env == 'learn') {
            // 是否為群組討論版（是：不稽核；否：要稽核）
            $isGroupForum = dbGetOne('WM_student_group', 'board_id', sprintf("course_id='%d' and board_id='%d'", $sysSession->course_id, $board_id), ADODB_FETCH_ASSOC);
            
            // 是否為討論室紀錄（是：不稽核；否：要稽核）
            dbGetRecordBoard($sysSession->course_id, $rsRecordBoard);
            $isRecordBoard = ((int)$board_id === (int)$rsRecordBoard['board_id']);// 都轉數字比較
            
            if ($isGroupForum === false && $isRecordBoard === false) {
                // 取得討論板的狀態設定
                list($forumState, $forumVisibility) = dbGetRow('WM_term_subject','state,visibility',sprintf('board_id=%d',$board_id),ADODB_FETCH_NUM);
                if ($forumVisibility != 'visible') return false;
                if ($forumState == 'disable')  return false;          
                $RS = dbGetStSr('WM_bbs_boards', 'bname,default_order, open_time, close_time, share_time', "board_id={$board_id}", ADODB_FETCH_ASSOC);
                $ot = $sysConn->UnixTimeStamp($RS['open_time']);
                $ct = $sysConn->UnixTimeStamp($RS['close_time']);
                $st = $sysConn->UnixTimeStamp($RS['share_time']);

                $status = getBoardStatus(time(), $ot, $ct, $st);
                if (in_array($status, array('close','notopen'))){
                    wmSysLog($sysSession->cur_func, $sysSession->course_id , $board_id , 2, 'auto', $_SERVER['PHP_SELF'], 'board_'.$status);
                    return false;
                }

                if ($forumState == 'public') return true;
            }
        }
        
        // 角色控管, 依據何種的討論板來判斷
        //遇owner_id資料錯誤為0時，則回傳true
        if ($board_ownerid == 0) return true;
        
        switch(strlen($board_ownerid)) {
            case 5:// 學校討論版
                return ture;
            
            case 7:// 班級
                if ($board_ownerid != $sysSession->class_id) return false;
                return (aclCheckRole($sysSession->username, $sysRoles['student']|$sysRoles['class_instructor']|$sysRoles['director'], $sysSession->class_id) > 0);
            
            case 8:// 課程
                
                // 快照本 會跨課存取討論板, 直接用$board_ownerid來驗證角色權限
                // if ($board_ownerid != $sysSession->course_id) return false;
                
                // 是否限定老師助教講師
                if ($forumState === 'taonly') {
                    return (aclCheckRole($sysSession->username, $sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'], $board_ownerid) > 0);
                } else {
                    return (aclCheckRole($sysSession->username, $sysRoles['auditor']|$sysRoles['student']|$sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher'], $board_ownerid) > 0);
                }
            
            case 16:// 群組
                // 檢查是否為群組組長
                $course_id = substr($board_ownerid, 0, 8);
                if ($course_id != $sysSession->course_id) return false;
                $team_id   = substr($board_ownerid, 8, 4);
                $group_id  = substr($board_ownerid,12, 4);
                $right     = (IsGroupMember($course_id, $team_id, $group_id, $sysSession->username) ||
                IsCourseTeacher($course_id, $sysSession->username));
                return $right;
        }
        return false;
    }
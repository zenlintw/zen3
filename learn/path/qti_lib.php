<?php
/**
 * 學生是否可以做 QTI 的檢查函數
 *
 * @since   2005/03/08
 * @author  ShenTing Lin
 * @version $Id: qti_lib.php,v 1.1 2010/02/24 02:39:09 saly Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

/**
 * 判斷三合一是否允許進入
 * @param    string    $qti_type    三合一種類(exam, homework, questionnaire)
 * @param    int        $qti_id        instance id
 * @param    string    $username    學員帳號
 * @return    entrance or error_no
 * 備註 :
 *        error_no : -1 不在acl名單中
 *                -2 已達可做次數(exam)或者已做且不可修改(homework, questionnaire)
 *                -3 程式錯誤 =_=
 */
function check_qti_can_do($qti_type, $qti_id, $username = '')
{
    global $_COOKIE, $sysSession, $sysRoles, $profile;
    
    $qti_id = intval($qti_id);
    if (empty($qti_id)) return -3;
    
    $username = trim($username);
    if (empty($username)) $username = $sysSession->username;
    
    $table = '';
    switch ($qti_type) {
        case 'homework': $table    = 'homework'; $do_times = '1'; break;
        case 'exam': $table    = 'exam'; $do_times = 'if(do_times < 1, 65535, do_times) '; break;
        case 'questionnaire': $table    = 'questionnaire'; $do_times = '1'; break;
    }
    if (empty($table)) return -3;
    
    // 檢查ACL設定
    $examinee_perm = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200);
    $aclVerified   = aclVerifyPermission($examinee_perm[$qti_type], aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'), $sysSession->course_id, $qti_id, $sysSession->username);
    if (!$aclVerified) {
        return -1;
    } else if ($aclVerified === 'WM2') { // 沒有設定acl, 預設正式生,增加教師試做
        $role = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['student'], $sysSession->course_id, true);
        if (!$role) return -1;
    }
    
    if ($qti_type == 'homework' && isAssignmentForGroup($qti_id)) { // 群組作業
        $isDone  = isAlreadySubmittedAssignmentForGroup($qti_id, $sysSession->username, $sysSession->course_id) ? 1 : 0;
        $time_id = $isDone;
    } else {
        list($isDone) = dbGetStSr('WM_qti_' . $table . '_result', 'count(*)', "exam_id={$qti_id} and examinee='{$username}'" . ($qti_type == 'exam' ? '' : ' and status != "break"'), ADODB_FETCH_NUM);
        
        // 新的測驗試卷編號，不使用isdone的原因是因為可能中間有試卷刪除，造成 time_id與 isdone數值不同
        list($time_id) = dbGetStSr('WM_qti_' . $table . '_result', 'max(time_id)', "exam_id={$qti_id} and examinee='{$username}'" . ($qti_type == 'exam' ? '' : ' and status != "break"'), ADODB_FETCH_NUM);
        $time_id = intVal($time_id);
    }
    
    if ($qti_type == 'homework') {
        list($canDo, $setting) = dbGetStSr('WM_qti_' . $table . '_test', 
                "( $isDone < 1 || modifiable='Y' )" . ' && publish="action" && ((CURRENT_TIMESTAMP() >= begin_time && CURRENT_TIMESTAMP() < close_time)|| CURRENT_TIMESTAMP() < delay_time),setting', 
                'exam_id=' . $qti_id . ' and course_id=' . $sysSession->course_id, ADODB_FETCH_NUM);
    } else {
        list($canDo, $setting) = dbGetStSr('WM_qti_' . $table . '_test', 
                ($qti_type == 'exam' ? 
                "( $isDone < $do_times )" : 
                "( $isDone < 1 || modifiable='Y' )"
                ) . ' && publish="action" && CURRENT_TIMESTAMP() >= begin_time && CURRENT_TIMESTAMP() < close_time,setting', 
                'exam_id=' . $qti_id . ' and course_id=' . $sysSession->course_id, ADODB_FETCH_NUM);
    }
    
    if ($canDo) {
        if ($qti_type == 'exam') {
            $time_id++;
            return sprintf('/learn/%s/exam_start.php?%d+%d+%s', $table, $qti_id, $time_id, md5(sysTicketSeed . $qti_id . $time_id . $_COOKIE['idx']));
        } else {
            // 配合APP API需求，不載入/mooc/common.php，以避免執行多餘SQL
            require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
            
            $detect = new Mobile_Detect;
            if (strpos($setting, 'upload') !== false && ($detect->isMobile() && !$detect->isTablet())) {
                return -4;
            } else {
                return sprintf('/learn/%s/exam_pre_start.php?%d+%d+%s', $table, $qti_id, $time_id, md5(sysTicketSeed . $qti_id . $time_id . $_COOKIE['idx']));
            }
        }
    } else
        return -2;
}
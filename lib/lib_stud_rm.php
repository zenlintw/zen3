<?php

    /**
       * /移除學員及相對資料的library
         *
         * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
         *
         * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
         * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
         * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
         *
         * @package     WM3
         * @author      Wing <wing@sun.net.tw>
         * @copyright   2000-2005 SunNet Tech. INC.
         * @version     CVS: $Id: lib_stud_rm.php,v 1.1 2010/02/24 02:39:34 saly Exp $
         * @link
         * @since       2006-01-26
         */

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/username.php');

    /**
     * 目的 : 抓取 測驗的編號
     * @param string $cid : 課程編號
     * @return string $str_exam_ids; : 課程的所有測驗的編號
     */
    function GetExamTestIds($cid, $qti_which='exam')
    {

        // 抓取 測驗的編號
        $exam_ids     = dbGetCol('WM_qti_' . $qti_which . '_test', 'exam_id', "course_id={$cid}");
        $str_exam_ids = implode(',', $exam_ids);

        return $str_exam_ids;
    }

    /**
     * 目的 : 抓取 作業的編號
     * @param string $cid : 課程編號
     * @return string $str_homework_ids; : 課程的所有作業的編號
     */
    function GetHomeTestIds($cid)
    {

        // 抓取 作業的編號
        return GetExamTestIds($cid, 'homework');
    }

    /**
     * 目的 : 抓取 問卷的編號
     * @param string $cid : 課程編號
     * @return string $str_qnaire_ids : 課程的所有問卷的編號
     */
    function GetQuestTestIds($cid)
    {

        // 抓取 問卷的編號
        return GetExamTestIds($cid, 'questionnaire');
    }

    /**
     * 目的 : 抓取 討論版的編號
     * @param string $cid : 課程編號
     * @return string $str_board_ids : 課程的所有討論版的編號
     */
    function GetBoards($cid)
    {

        // 抓取 討論版的編號
        $board_ids = dbGetCol('WM_bbs_boards', 'board_id', "owner_id={$cid}");
        $str_board_ids = implode(',', $board_ids);

        return $str_board_ids;
    }

    /**
     * 目的 : 抓取 成績的編號
     * @param string $cid : 課程編號
     * @return string $str_grade_ids : 課程的所有成績編號
     */
    function GetGradeIds($cid)
    {

        // 抓取 成績的編號
        $grade_ids = dbGetCol('WM_grade_list', 'grade_id', "course_id={$cid} ");
        $str_grade_ids = implode(',', $grade_ids);

        return $str_grade_ids;
    }

    /**
     * 目的 : 刪除學員在課程內的相關資料
     * @param string $cid              : 課程編號
     * @param string $user             : 欲刪除之學員帳號
     * @param bool   $last             : 是否是最後一次 (最後一次真正執行刪除動作)
     * @param bool   $sid              : 是否跨校退選 (判斷ID有無跟目前學校一樣)
     *
     * @return int display : 回傳資料秀訊息用 (0,刪除成功、1,刪除失敗、2,無此帳號、3.5,系統保留帳號、4,格式不正確)
     */
    function DelStudentAll($cid,$user,$last=false,$sid=0)
    {
        global $sysConn, $sysRoles;
        static $str_exam_ids,$str_homework_ids,$str_qnaire_ids,$str_board_ids,$str_grade_ids,$users;

        if (!isset($users)) $users = array();
        $cid = intval($cid);
        $result = checkUsername($user);
        if ($user == sysRootAccount) $result = 4;
        if ($result == 2){
            // 如果有要跨校退選
            if (0 != $sid) {
                $sysConn->Execute('use '.sysDBprefix.$sid);
                $sqls = sprintf('select count(*) from WM_term_major as M inner join WM_term_course as C on M.course_id=C.course_id and C.status != 9 where M.username="%s" and M.course_id=%d and M.role&%d' , $user, $cid, $sysRoles['student'] | $sysRoles['auditor']);
                $isStudent = $sysConn->GetOne($sqls);
            } else {
                $isStudent = aclCheckRole($user, $sysRoles['student'] | $sysRoles['auditor'], $cid);
            }
            if ($isStudent)
            {
                if (0 != $sid) {
                    $sqls = sprintf("UPDATE %s SET role=role & %d WHERE course_id=%d and username='%s' limit 1", '`WM_term_major`', ($sysRoles['all'] ^ $sysRoles['student'] ^ $sysRoles['auditor']), $cid, $user);                    
                    $sysConn->Execute($sqls);
                } else {
                    dbSet('WM_term_major','role=role & ' . ($sysRoles['all'] ^ $sysRoles['student'] ^ $sysRoles['auditor']), 'course_id=' . $cid . ' and username="' . $user . '" limit 1');
                }
                if ($sysConn->Affected_Rows())
                {
                    $users[] = $user;
                }
                $display = 0;

            }else{
                $display = 1;
            }

        }elseif ($result == 0){
                $display = 2;
        }elseif ($result == 1){
                $display = 3;
        }elseif ($result == 3){
                $display = 4;
        }elseif ($result == 4){
                $display = 5;
        }

        // 真正執行刪除動作 begin
        if ($last)
        {
            if (!isset($str_exam_ids))     $str_exam_ids     = GetExamTestIds($cid);  // 課程所有的測驗編號
            if (!isset($str_homework_ids)) $str_homework_ids = GetHomeTestIds($cid);  // 課程所有的作業編號
            if (!isset($str_qnaire_ids))   $str_qnaire_ids   = GetQuestTestIds($cid); // 課程所有的問卷編號
            if (!isset($str_board_ids))    $str_board_ids    = GetBoards($cid);       // 課程所有的討論版編號
            if (!isset($str_grade_ids))    $str_grade_ids    = GetGradeIds($cid);     // 課程所有的成績編號

            $users[] = $user;

            if (count($users) > 1)
                $str_users = ' in ("' . implode('","', $users) . '")';
            else
                $str_users = '="' . $users[0] . '"';

            // 修課
            $sqls = 'insert into WM_history_term_major(username,course_id,role,post,hw,qp,exam,bookmark,degree,total_node,login_times,post_times,dsc_times,last_login,add_time) ' .
                       ' select username,course_id,role,post,hw,qp,exam,bookmark,degree,total_node,login_times,post_times,dsc_times,last_login,add_time ' .
                    ' from WM_term_major where ' .
                    " course_id={$cid} " .
                    ' and username' . $str_users;

            $sysConn->Execute($sqls);

            // 作業
            if (strlen($str_homework_ids) > 0){
                $sqls = 'insert into WM_history_qti_homework_result(exam_id,examinee,time_id,status,begin_time,submit_time,score,comment,content) ' .
                        ' select exam_id,examinee,time_id,status,begin_time,submit_time,score,comment,content ' .
                        ' from WM_qti_homework_result where ' .
                           ' exam_id in (' . $str_homework_ids . ') ' .
                        ' and examinee' . $str_users;

                 $sysConn->Execute($sqls);

                dbDel('WM_qti_homework_result', ' exam_id in (' . $str_homework_ids . ') and  examinee' . $str_users);

            }


            // 測驗
            if (strlen($str_exam_ids) > 0){
                $sqls = 'insert into WM_history_qti_exam_result(exam_id,examinee,time_id,status,begin_time,submit_time,score,comment,content) ' .
                        ' select exam_id,examinee,time_id,status,begin_time,submit_time,score,comment,content ' .
                        ' from WM_qti_exam_result where ' .
                        ' exam_id in (' . $str_exam_ids . ') ' .
                        ' and examinee' . $str_users;
                $sysConn->Execute($sqls);

                dbDel('WM_qti_exam_result', ' exam_id in (' . $str_exam_ids . ') and  examinee' . $str_users);
            }

            // 問卷
            if (strlen($str_qnaire_ids) > 0){
                $sqls = 'insert into WM_history_qti_questionnaire_result(exam_id,examinee,time_id,status,begin_time,submit_time,score,comment,content) ' .
                        ' select exam_id,examinee,time_id,status,begin_time,submit_time,score,comment,content ' .
                        ' from WM_qti_questionnaire_result where ' .
                        ' exam_id in (' . $str_qnaire_ids . ') ' .
                        ' and examinee' . $str_users;
                $sysConn->Execute($sqls);

                dbDel('WM_qti_questionnaire_result', ' exam_id in (' . $str_qnaire_ids . ') and  examinee' . $str_users);
            }

            // 修課成績
            $sqls = 'insert into WM_history_grade_stat(course_id,username,total,average,`range`) ' .
                    ' select course_id,username,total,average,`range` ' .
                    ' from WM_grade_stat ' .
                    ' where course_id=' . $cid .
                    ' and username' . $str_users;

               $sysConn->Execute($sqls);

            dbDel('WM_grade_stat', 'course_id=' . $cid . " and username" . $str_users);

            // 閱讀記錄
            $sqls = 'insert into WM_history_record_reading(course_id,username,begin_time,over_time,url,activity_id) ' .
                    ' select course_id,username,begin_time,over_time,url,activity_id ' .
                    ' from WM_record_reading ' .
                    ' where course_id=' . $cid .
                    ' and username' . $str_users;

            $sysConn->Execute($sqls);

            dbDel('WM_record_reading', 'course_id=' . $cid . " and username" . $str_users);

             $sqls = 'insert into WM_history_record_daily_personal(username,course_id,thatday,reading_seconds) ' .
                     ' select username,course_id,thatday,reading_seconds ' .
                     ' from WM_record_daily_personal ' .
                     ' where course_id=' . $cid .
                     ' and username' . $str_users;

            $sysConn->Execute($sqls);

            dbDel('WM_record_daily_personal', 'course_id=' . $cid . " and username" . $str_users);

             $sqls = 'insert into WM_history_scorm_tracking(course_id,username,activity_id,tm_data) ' .
                    ' select course_id,username,activity_id,tm_data ' .
                    ' from WM_scorm_tracking ' .
                    ' where course_id=' . $cid .
                    ' and username' . $str_users;

             $sysConn->Execute($sqls);

             dbDel('WM_scorm_tracking', 'course_id=' . $cid . " and username" . $str_users);

             // 小組分組名單
             dbDel('WM_student_div', 'course_id=' . $cid . " and username" . $str_users);

             // 小組組長名單
             dbSet('WM_student_group',"captain=NULL" ,'course_id=' . $cid . " and captain" . $str_users);

            // 訂閱討論版文章 & 是否閱讀過某篇文章
            if (strlen($str_board_ids) > 0){
                dbDel('WM_bbs_order', 'board_id in (' . $str_board_ids . ") and username" . $str_users);
                dbDel('WM_bbs_readed', 'board_id in (' . $str_board_ids . ") and username" . $str_users);
            }

            // 學員的每項成績
            if (strlen($str_grade_ids) > 0){
                dbDel('WM_grade_item', 'grade_id in (' . $str_grade_ids . ") and username" . $str_users);
            }

            $users = array();
        }
        // 真正執行刪除動作 end

        return $display;
    }

?>

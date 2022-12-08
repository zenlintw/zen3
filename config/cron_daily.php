#!/usr/local/bin/php
<?php
    /**
     *    ※ WM daily 定時執行程式
     *
     * @since   2004/08/31
     * @author  Wiseguy Liang
     * @version $Id: cron_daily.php,v 1.1 2010/02/24 02:38:56 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     *
     **/

    // 系統設定
    set_time_limit(0);
/*
    require_once(dirname(__FILE__) . '/sys_config.php');
    require_once(sysDocumentRoot . '/lib/adodb/adodb.inc.php');
*/
    require_once(dirname(__FILE__) . '/console_initialize.php');

    require_once(sysDocumentRoot . '/teach/student/stud_mailto_lib.php');
    require_once(sysDocumentRoot . '/lib/mime_mail.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lib/mime_detection.php');
    require_once(sysDocumentRoot . '/mooc/models/user.php');

    // 當天有異動的課超過這個數量，則分批計算 quota
    define('MAX_CALC_COURSE_AMOUNT', 100);


    // 傳回課程名稱
    function getCourseName($l_cid)
    {
        if ($caption = dbGetOne('WM_term_course', 'caption', 'course_id=' . intval($l_cid)))
        {
            if ($name = unserialize($caption))
                return $name[$GLOBALS['school_language']];
            else
                return '--=unknown=--';
        }
        else
            return '--=unknown=--';
    }

    if (!defined('record_traceback_days')) define('record_traceback_days', 5);
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

    $keep = $_COOKIE['school_hash']; unset($_COOKIE['school_hash']);
    require_once(sysDocumentRoot . '/sys/calendar_email.php');

    // 刪除超過3天沒有做電子信箱驗證的資料 B
    $rsUser = new user();
    $users = $rsUser->getExpiredUsers(3);

    if (count($users) >= 1) {
        $r = $rsUser->delExpiredTmpUsers($users);
    }
    // 刪除超過3天沒有做電子信箱驗證的資料 E

    $c       = time();
    $etime   = date('Y-m-d 00:00:00', $c);
    $stime   = date('Y-m-d 00:00:00', strtotime($etime)-38400); // 前一天
    $thatday = substr($stime, 0, 10);
    $basedir = sysDocumentRoot ;

    foreach($sysConn->GetCol('show databases') as $db)
    {
        if (!preg_match('/^' . sysDBprefix . '([0-9]{5})$/', $db, $sid)) continue;

        // 清查學校的 quota
        $sysConn->Execute('use ' . sysDBname);

        if(is_dir("{$basedir}/base/{$sid[1]}")) {
            $quota_used = (int)exec("cd '{$basedir}/base/{$sid[1]}'" . ' 2>/dev/null && ls -1a | sed \'/^\(\.\+\|class\|course\|content\)$/d\' | xargs du -skc');
        } else {
            $quota_used = 0;
        }
        $sqls = "update WM_school set quota_used={$quota_used} where school_id={$sid[1]}";
        $sysConn->Execute($sqls);

        $RS              = $sysConn->Execute("select school_name,language,school_mail from WM_school where school_id='{$sid[1]}'");
        $school_name     = addslashes($RS->fields['school_name']);
        $school_language = $RS->fields['language'];
        $school_mail     = $RS->fields['school_mail'];

        $sysConn->Execute('use ' . $db);
        $GLOBALS['db'] = $db;

        // 清除過久的暫存資料 (三天前的刪除)
        $sysConn->Execute('DELETE FROM WM_save_temporary WHERE DATE_SUB(NOW(),INTERVAL 3 DAY) >= save_time');

        // 清除帳號已不存在之個人記錄 begin
        $user_relative_tables = array('WM_bbs_readed',
                                      'WM_cal_setting',
                                      'WM_chat_user_setting',
                                      'WM_class_director',
                                      'WM_class_member',
                                      'WM_content_ta',
                                      'WM_im_message',
                                      'WM_im_setting',
                                      'WM_ipfilter',
                                      'WM_member_div',
                                      'WM_student_div',
                                      'WM_term_teacher',
                                      'WM_user_picture',
                                      'WM_user_tagline');
        foreach ($user_relative_tables as $user_relative_table)
        {
            $deleted_users = $sysConn->GetCol('SELECT distinct R.username FROM `' . $user_relative_table . '` as R left join `WM_user_account` as U on R.username=U.username where isnull(U.username)');
            if (is_array($deleted_users) && count($deleted_users))
                $sysConn->Execute('DELETE FROM `' . $user_relative_table . '` WHERE username in ("' . implode('","', $deleted_users) . '")');
        }
        // 清除帳號已不存在之個人記錄 end

        // 刪除已不是該門課成員卻訂閱該課討論板之記錄 begin
        $keep = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
        $RS = $sysConn->GetArray('SELECT O.* ' .
                                 'FROM WM_bbs_order AS O ' .
                                 'LEFT JOIN WM_bbs_boards AS B ON O.board_id = B.board_id ' .
                                 'LEFT JOIN WM_term_major AS M ON O.username = M.username ' .
                                 'AND LEFT(B.owner_id,8) = M.course_id ' .
                                 'WHERE LENGTH(B.owner_id)>7 AND M.username IS NULL AND M.course_id IS NULL');
        if (is_array($RS) && count($RS))
            foreach ($RS as $row)
                $sysConn->Execute('delete from WM_bbs_order where board_id=' . $row[0] . ' and username="' . $row[1] . '" limit 1');

        $RS = $sysConn->GetArray('SELECT O.* ' .
                                 'FROM WM_bbs_order AS O ' .
                                 'LEFT JOIN WM_bbs_boards AS B ON O.board_id = B.board_id ' .
                                 'LEFT JOIN WM_class_member AS M ON B.owner_id = M.class_id ' .
                                 'AND O.username = M.username ' .
                                 'WHERE LENGTH(B.owner_id)=7 AND M.class_id IS NULL AND M.username IS NULL');
        if (is_array($RS) && count($RS))
            foreach ($RS as $row)
                $sysConn->Execute('delete from WM_bbs_order where board_id=' . $row[0] . ' and username="' . $row[1] . '" limit 1');

        $ADODB_FETCH_MODE = $keep;
        // 刪除已不是該門課成員卻訂閱該課討論板之記錄 end

        // 清除討論室中不正常離線的人員 (Begin)
        // 取得目前線上的人員
        $sqls = 'SELECT `idx` FROM WM_session';
        $RS = $sysConn->Execute($sqls);
        if ($RS) {
            $user = array();
            while($fields = $RS->FetchRow()){
                $user[] = "'{$fields['idx']}'";
            }
            $idxs = implode(',', $user);
            // 清除已經不在線上的討論室人員
            if (!empty($idxs))
            {
                $sqls = "DELETE FROM WM_chat_session WHERE `idx` not in ({$idxs})";
                if ($sysConn->Execute($sqls)); else echo $sysConn->ErrorMsg();
            }
        } else
            echo $sysConn->ErrorMsg();
        // 清除討論室中不正常離線的人員 (End)

        $keep_s = $stime; $keep_e = $etime; $keep_d = $thatday; $day_count = record_traceback_days;
        do
        {
            // 將每天的個人閱讀時間記入
            $sqls = 'replace into WM_record_daily_personal (username,course_id,thatday,reading_seconds) ' .
                    "select username,course_id,'$thatday',sum(unix_timestamp(over_time)-unix_timestamp(begin_time)+1) " .
                    'from WM_record_reading ' .
                    "where over_time >= '$stime' and over_time < '$etime' " .
                    'group by username,course_id';
            if ($sysConn->Execute($sqls)); else echo $sysConn->ErrorMsg();

            $etime   = $stime;
            $stime   = date('Y-m-d 00:00:00', strtotime($etime)-38400);
            $thatday = substr($stime, 0, 10);
            $isEmpty = dbGetOne('WM_record_daily_personal', 'count(*)', "thatday='$thatday'");
        }
        while($isEmpty === '0' && $day_count--);

        $stime = $keep_s; $etime = $keep_e; $thatday = $keep_d; $day_count = record_traceback_days;
        do
        {
            // 將每天的課程閱讀時間記入
            $sqls = 'replace into WM_record_daily_course (course_id,thatday,reading_seconds) ' .
                    "select course_id,'$thatday',sum(unix_timestamp(over_time)-unix_timestamp(begin_time)+1) " .
                    'from WM_record_reading ' .
                    "where over_time >= '$stime' and over_time < '$etime' " .
                    'group by course_id';
            if ($sysConn->Execute($sqls)); else echo $sysConn->ErrorMsg();

            $etime   = $stime;
            $stime   = date('Y-m-d 00:00:00', strtotime($etime)-38400);
            $thatday = substr($stime, 0, 10);
            $isEmpty = dbGetOne('WM_record_daily_course', 'count(*)', "thatday='$thatday'");
        }
        while($isEmpty === '0' && $day_count--);

        // 清查所有課程的張貼數
        $sqls = 'select C.course_id,count(*)
                from WM_term_course as C
                ,WM_bbs_boards as B
                ,WM_bbs_posts as P
                where C.course_id=left(B.owner_id,8)
                and B.board_id=P.board_id
                group by C.course_id';
        $course_posts1 = $sysConn->GetAssoc($sqls);
        $sqls = 'select C.course_id,count(*)
                from WM_term_course as C
                ,WM_bbs_boards as B
                ,WM_bbs_collecting as P
                where C.course_id=left(B.owner_id,8)
                and B.board_id=P.board_id and P.type = "F"
                group by C.course_id';
        $course_posts2 = $sysConn->GetAssoc($sqls);

                $sqls      = 'SELECT C.course_id,P.poster, COUNT( * ) as cou
                                 FROM WM_term_course AS C, WM_bbs_boards AS B, WM_bbs_posts AS P
                                 WHERE C.course_id = LEFT( B.owner_id, 8 )
                                 AND B.board_id = P.board_id
                                 GROUP BY C.course_id,P.poster';
                $rs        = $sysConn->Execute($sqls);
                $post_data = array();
                while ($fields = $rs->FetchRow()) {
                    $course_id = $fields['course_id'];
                    $username  = $fields['poster'];
                    $count     = $fields['cou'];

                    $post_data[$course_id][$username] = intval($count);
                }

        // 清查所有課程的 quota
        // 修改為：只算前一天有人有登入的課程
        if (chdir("{$basedir}/base/{$sid[1]}/course"))
        {
            $ret = '';
            $maybe_modified_courses = $sysConn->GetCol('SELECT DISTINCT department_id FROM WM_log_classroom WHERE function_id =2500100200 AND log_time > DATE_SUB(CURDATE(), INTERVAL 2 DAY) UNION ' .
                                                       'SELECT DISTINCT department_id FROM WM_log_teacher   WHERE function_id =2500200200 AND log_time > DATE_SUB(CURDATE(), INTERVAL 2 DAY)');
            if (is_array($maybe_modified_courses) && ($total_amount = count($maybe_modified_courses)))
            {
                sort($maybe_modified_courses, SORT_NUMERIC);
                for ($i = 0; $i < $total_amount; $i+=MAX_CALC_COURSE_AMOUNT) // 預設每一次算一百門課，以免命令列過長
                {
                    $x = implode(' ', array_slice($maybe_modified_courses, $i, MAX_CALC_COURSE_AMOUNT));
                    $ret .= `du -sk $x 2>/dev/null`;
                }

                foreach(explode("\n", $ret) as $item)
                {
                    if (preg_match('!^([0-9]+)[\t ]+([0-9]{8})$!', $item, $regs))
                    {
                        $sqls = 'update WM_term_course set post_times=' .
                                (intval($course_posts1[$regs[2]]) + intval($course_posts2[$regs[2]])) .
                                ',quota_used=' . intval($regs[1]) . ' where course_id=' . intval($regs[2]);
                        $sysConn->Execute($sqls);

                                                $studRS = dbGetStMr('WM_term_major', 'username,post_times', "course_id={$regs[2]}");
                                                if ($studRS) {
                                                    while (!$studRS->EOF) {
                                                        $student = $studRS->fields['username'];
                                                        $post    = intval($studRS->fields['post_times']);

                                                        if (!empty($post_data[$regs[2]][$student]) && $post != $post_data[$regs[2]][$student]) {
                                                            dbSet('WM_term_major', "post_times={$post_data[$regs[2]][$student]}", "username='{$student}' and course_id={$regs[2]}");
                                                        }
                                                        $studRS->MoveNext();
                                                    }
                                                }
                    }
                }
            }

            unset($x, $item, $regs, $sqls, $ret, $i);
        }

        // 加強型備份機制。產生不必備份的課程列表
        $course_lists = array();
        chdir("{$basedir}/base/{$sid[1]}/course") and ($course_lists = glob('????????', GLOB_ONLYDIR|GLOB_NOSORT));
        if (is_array($course_lists) && count($course_lists))
        {
            $unnecessary_backups = array_diff($course_lists, $maybe_modified_courses);
            if ($fp = fopen("{$basedir}/base/{$sid[1]}/course/.rsync-filter", 'w'))
            {
                if (is_array($unnecessary_backups) && count($unnecessary_backups))
                    foreach ($unnecessary_backups as $unnecessary_backup)
                        fwrite($fp, "- $unnecessary_backup\nP $unnecessary_backup\n");
                else
                    fwrite($fp, '');

                fclose($fp);
            }
        }
        unset($course_lists, $unnecessary_backups, $unnecessary_backup, $fp);


        // 清查所有教材的 quota
        $ret = `cd {$basedir}/base/{$sid[1]}/content 2>/dev/null && du -sk ?????? 2>/dev/null`;
        foreach(explode("\n", $ret) as $item)
        {
            if (preg_match('!^([0-9]+)[\t ]+([0-9]{6})$!', $item, $regs))
            {
                $sqls = "update WM_content set quota_used={$regs[1]} where content_id={$regs[2]}";
                $sysConn->Execute($sqls);
            }
        }

        // 清查所有班級的 quota
        $ret = `cd {$basedir}/base/{$sid[1]}/class 2>/dev/null && du -sk ??????? 2>/dev/null`;
        foreach(explode("\n", $ret) as $item)
        {
            if (preg_match('!^([0-9]+)[\t ]+([0-9]{7})$!', $item, $regs))
            {
                $sqls = "update WM_class_main set quota_used={$regs[1]} where class_id={$regs[2]}";
                $sysConn->Execute($sqls);
            }
        }

        unset($ret, $item, $regs);

        // 清除學習排行榜資料
        // $sysConn->Execute("delete from WM_record_learn_record");
        $sysConn->Execute('TRUNCATE TABLE WM_record_learn_record');
        if ($sysConn->ErrorNo()) die($sysConn->ErrorNo() . ' : ' . $sysConn->ErrorMsg());

        $sqls = 'insert into WM_record_learn_record select username, concat(IFNULL(`last_name`,""),IFNULL(`first_name`,"")) as realname, 0,0,0,0,0,0,0 from WM_user_account where username != "root"';
        $sysConn->Execute($sqls);
        // 產生學習排行榜的資料
        $sqls = 'replace into WM_record_learn_record
                 select UA.username, concat(UA.last_name,UA.first_name) as realname,count(TM.course_id) as total_course,
                 (round((sum(TC.credit * GS.total)),1)/sum(if(isnull(TC.credit),0,TC.credit))) as total_grade,
                 sum(TM.login_times) as login_times,
                 sum(TM.post_times) as post_times,
                 sum(TM.dsc_times) as dsc_times,0,0
                 from WM_user_account as UA left join WM_term_major as TM on UA.username = TM.username
                 left join WM_grade_stat as GS on UA.username=GS.username and TM.course_id=GS.course_id
                 left join WM_term_course as TC on TM.course_id=TC.course_id
                 where ((TC.status <> 9) or (TC.status is not null)) and UA.username !="root"
                 group by UA.username order by UA.username';
        $sysConn->Execute($sqls);

        $d1 = $sysConn->GetAssoc('SELECT username,count(*) as cnt from `WM_record_reading` group by username');
        $d2 = $sysConn->GetAssoc("SELECT username,sum(reading_seconds) as rss FROM `WM_record_daily_personal` group by username");

        foreach($d1 as $username => $a1)
            $sysConn->Execute("update WM_record_learn_record set total_readtime=".intval($d2[$username]).", total_readpages=".intval($a1)." where username='{$username}'");

        // $sysConn->Execute('use ' . $db);

        //建立school_hash
        $skey = md5($_SERVER['HTTP_HOST'].$sid[1]);
        $_COOKIE['school_hash'] = substr($skey, 0, 17) . $sid[1] . substr($skey, -10);

        //自動寄信點名
        //先取得enable且點名期間仍有效的點名設定
        $rs = $sysConn->Execute('select * from `WM_roll_call` as R
                                 join `WM_term_course` as C
                                 on R.course_id = C.course_id
                                 where R.enable="enable"
                                 and C.status  < 5 and C.status > 0
                                 and ((R.begin_time is null) or (R.begin_time < NOW()))
                                 and ((R.end_time is null) or (R.end_time > NOW()))
                                 and ((C.st_begin is null) or (C.st_begin <= CURDATE()))
                                 and ((C.st_end is null) or (C.st_end >= CURDATE()))');
        if ($rs)
        while($obj = $rs->FetchNextObj())
        {

            //依照自動寄信頻率去過濾
            if ($obj->frequence == 'once'){
                if ($obj->freq_extra != date("Y-m-d")) continue;
            }else if ($obj->frequence == 'week'){
                if ($obj->freq_extra != date("l")) continue;
            }else if ($obj->frequence == 'month'){
                if ($obj->freq_extra != date("j")) continue;
            }

            //取得符合條件的使用者

            //寄信角色的條件
            $roles = array();
            $roles[0] = $obj->role;

            //寄信是否有群組條件
            $groups = array();
            if (intval($obj->team_id) == 0) $obj->team_id = 'all';
            if (intval($obj->group_id) == 0) $obj->group_id = 'all';
            $groups[0] = array($obj->team_id, $obj->group_id);

            $students = array();
            //依過濾條件取出學生列表

            $filters = array($obj->mtType, $obj->mtFilter, $obj->mtOP, $obj->mtVal);

            switch ($obj->mtType) {
                case 'login'         :   // 登入
                    $students = func_login($filters, $sid[1], $obj->course_id);
                    break;
                case 'lesson'        :   // 上課
                    $students = func_lesson($filters, $obj->course_id);
                    break;
                case 'chat'          :   // 討論
                    $students = func_chat($filters, $obj->course_id);
                    break;
                case 'post'          :   // 張貼
                    $students = func_post($filters, $obj->course_id);
                    break;
                case 'progress'      :   // 學習進度
                    $students = func_progress($filters, $obj->course_id);
                    break;
                case 'exam'          :   // 測驗
                case 'homework'      :   // 作業
                case 'questionnaire' :   // 問卷
                    $students = func_QTI($filters,$sid[1],$obj->course_id);
                    break;
            }

            // $sysConn->Execute('use ' . $db);
            $tmp_coursename = getCourseName($obj->course_id);

            // 處理附檔 start
            $mail_attach = array();
            $attachs =  unserialize($obj->mail_attach);
            if ($attachs && count($attachs) > 0) {
                foreach ($attachs as $k => $v) {
                    $filename = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/rollcall/%d/' .$v, $sid[1], $obj->course_id, $obj->serial_id);
                    if (is_file($filename)) {
                        $type = detect_mime($k);
                        if ($type === false) $type = 'application/octet-stream';
                        $mail_attach[] = array($filename, $k, $type);
                    }
                }
            }
            // 處理附檔 end

            foreach($students as $val)
            {
                $userdetail = getUserDetailData($val['username']);
                $cont = str_replace(array('%username%', '%realname%', '%COURSE_NAME%'),
                                    array($val['username'], $userdetail['realname'], $tmp_coursename),
                                    $obj->mail_content);
                $subj = str_replace('%COURSE_NAME%',$tmp_coursename,$obj->mail_subject);
                $mail = new mime_mail;
                $mail->subject   = mailEncSubject($subj);
                $mail->body      = $cont;
                $mail->body_type = 'text/html';
                $mail->from      = mailEncFrom($school_name, $school_mail);
                $mail->to        = $userdetail['email'];
                $mail->charset   = 'utf-8';
                if (count($mail_attach)) { // 附檔
                    foreach ($mail_attach as $attach)
                        $mail->add_attachment(file_get_contents($attach[0]), $attach[1], $attach[2],'utf-8');
                }
                $mail->send();
            }
        }

        // 雲端筆記 APP專用 - 刪除筆記分享的share key - Begin
        $overDueTime = time() - 86400;
        $sysConn->Execute("DELETE FROM APP_note_share WHERE `due_time` < {$overDueTime}");
        // 雲端筆記 APP專用 - 刪除筆記分享的share key - End

        // 清除過期(15天)或是錯誤的推播資訊
        // $appWhere = "((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`update_datetime`)) > (60 * 60 * 24 * 15)) OR (`app_uuid` = '') OR (`devicetoken` = '')";
        // $sysConn->Execute("DELETE FROM `APP_push_device_info` WHERE " . $appWhere);
        // $sysConn->Execute("DELETE FROM `APP_push_subscribe_channel` WHERE " . $appWhere);
        
        // APP IRS: 關閉目前所有正在進行的互動(B)
        $sysConn->Execute("UPDATE `WM_qti_exam_test` SET `publish` = 'close', `close_time` = NOW() WHERE `type` = 5 AND `publish` = 'action';");
        $sysConn->Execute("UPDATE `WM_qti_questionnaire_test` SET `publish` = 'close', `close_time` = NOW() WHERE `type` = 5 AND `publish` = 'action';");
        // APP IRS: 關閉目前所有正在進行的互動 (E)

        //加log => WM_log_others
        $sysConn->Execute("insert into WM_log_others (function_id, username, log_time) values (".cron_daily_function_id.",'root',NOW())");
    }

    // 清除過期的"保持登入"的資料
    $sysConn->Execute(sprintf("DELETE FROM `%s`.`WM_persist_login` WHERE `expire_time`<NOW()",sysDBname));

<?php
/**************************************************************************************************
 *                                                                                                *
 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
 *                                                                                                *
 *      Programmer: Edi Chen                                                                      *
 *      Creation  : 2005/06/30                                                                    *
 *      work for  : re-calculate of grade                                                         *
 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
 *                                                                                                *
 **************************************************************************************************/
ignore_user_abort(true);
set_time_limit(0);

/**
 * 為 sprintf() 跳脫 % 字元
 *
 * @param   string  $str    字串
 * @return  string          含有 % 字元將變成兩個
 */
function escape_percent_sign($str)
{
    return str_replace('%', '%%', $str);
}

/**
 * 為 sprintf() 還原 % 字元
 *
 * @param   string  $str    字串
 * @return  string          含有 %% 字元將變成一個
 */
function unescape_percent_sign($str)
{
    return str_replace('%%', '%', $str);
}

/**
 * 重新計算QTI的分數
 * @param string $examinee username
 * @param int $exam_id exam id
 * @param string $qti_type homework/exam/questionnaire
 * @param int $team_id if group's homework
 * @param boolean success or fail
 */
function reCalculateQTIGrade($examinee, $exam_id, $qti_type, $comment = null, $team_id = null, $forceOverwrite = 'Y')
{
    if (empty($_COOKIE['show_me_info']) === false) {
        echo '<pre>';
        var_dump('強制覆蓋分數（即便原本有分數）', $forceOverwrite);
        var_dump('學員帳號', $examinee);
        echo '</pre>';
    }
    
    global $sysConn, $sysSession, $ADODB_FETCH_MODE;
    $keep = $ADODB_FETCH_MODE;
    // 注意：非同儕互評沒有  teacher_percent, peer_percent, self_percent 欄位
    if ($qti_type === 'peer') {
        list($calcGradeMethod, $teacher_percent, $peer_percent, $self_percent) = dbGetStSr('WM_qti_' . $qti_type . '_test', 'count_type, teacher_percent, peer_percent, self_percent', 'exam_id=' . $exam_id, ADODB_FETCH_NUM);
    } else {
        list($calcGradeMethod) = dbGetStSr('WM_qti_' . $qti_type . '_test', 'count_type', 'exam_id=' . $exam_id, ADODB_FETCH_NUM);
    }
    
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    if ($calcGradeMethod != 'none') {
        $grade_types = array('homework' => 1, 'exam' => 2, 'questionnaire' => 3, 'peer' => 4);
        if (!in_array($qti_type, array_keys($grade_types))) {
            $ADODB_FETCH_MODE = $keep;
            return false;
        }

        $grades = $sysConn->GetCol('select grade_id from WM_grade_list where source=' .
                                   $grade_types[$qti_type] . ' and property=' . $exam_id . ' order by grade_id');
        if (is_array($grades)) {
            while (count($grades) < 1) // 尚未建立對應成績單
            {
                $sqls = 'insert into WM_grade_list (course_id,title,source,property,percent,publish_begin,publish_end) ' .
                    'select course_id,title,' . $grade_types[$qti_type] . ',exam_id,percent,'.
                    'if (announce_type="user_define", announce_time, if(announce_type="never", "0000-00-00 00:00:00", if(announce_type="close_time", close_time, if(begin_time="0000-00-00 00:00:00", "1970-01-01 00:00:00", begin_time)))),' .
                    'if (announce_type="never", "0000-00-00 00:00:00", "9999-12-31 00:00:00") ' .
                    ' from WM_qti_' . $qti_type . '_test where exam_id=' . $exam_id;
                $sysConn->Execute($sqls);
                wmSysLog($sysSession->cur_func, $sysSession->course_id, $exam_id, 0, 'auto', $_SERVER['PHP_SELF'], 'create new grade list grade id = ' . $grades[0]);
                
                // 因為總是會多出一個成績，所以檢查是否多出來，若多出就砍掉
                $grades = $sysConn->GetCol('select grade_id from WM_grade_list where source=' .
                    $grade_types[$qti_type] . ' and property=' . $exam_id . ' order by grade_id');
                if (count($grades) > 1) {
                    $gids = implode(',', array_slice($grades, 1));
                    dbDel('WM_grade_list', 'grade_id in (' . $gids . ')');
                    wmSysLog($sysSession->cur_func, $sysSession->course_id, $exam_id, 0, 'auto', $_SERVER['PHP_SELF'], 'remove unwanted grade id in ' . $gids);
                    $grades = array_slice($grades, 0, 1);
                }
            }
            
            switch ($calcGradeMethod) {
                case 'last':
                    $score = round($sysConn->GetOne('select SUBSTRING(MAX(CONCAT(LPAD(time_id,6,"0"),score)),7) from WM_qti_' . $qti_type . "_result where exam_id={$exam_id} and examinee='{$examinee}' and (status='revised' or status='publish')"), 2);
                    break;
                case 'average':
                case 'max':
                case 'min':
                    if ($calcGradeMethod == 'average') $calcGradeMethod = 'avg';
                    $score = round($sysConn->GetOne("select {$calcGradeMethod}(score) from WM_qti_" . $qti_type . "_result where exam_id={$exam_id} and examinee='{$examinee}' and (status='revised' or status='publish')"), 2);
                    break;
                default:
                    if ($qti_type === 'peer') {
                        // 老師分數
                        $score_teacher = round($sysConn->GetOne('select SUBSTRING(MIN(CONCAT(LPAD(time_id,6,"0"),score)),7) from WM_qti_' . $qti_type . "_result where exam_id={$exam_id} and examinee='{$examinee}' and (status='revised' or status='publish' or status='submit')"), 2);
                        
                        //互評分數
                        $score_peer = round($sysConn->GetOne('select AVG(score) from WM_qti_peer_result_score where exam_id = ' . $exam_id . ' and examinee = \'' . $examinee . '\' and score_type = 0'), 2);
                        
                        // 自評分數
                        $score_self = round($sysConn->GetOne('select SUBSTRING(MIN(CONCAT(LPAD(time_id,6,\'0\'),score)),7) from WM_qti_peer_result_score where exam_id = ' . $exam_id . ' and examinee = \'' . $examinee . '\'and score_type = 1'), 2);
                        
                        // 權重
                        $score = ($score_teacher * $teacher_percent / 100) + ($score_peer * $peer_percent / 100) + ($score_self * $self_percent / 100);
                    } else {
                        $score = round($sysConn->GetOne('select SUBSTRING(MIN(CONCAT(LPAD(time_id,6,"0"),score)),7) from WM_qti_' . $qti_type . "_result where exam_id={$exam_id} and examinee='{$examinee}' and (status='revised' or status='publish')"), 2);
                    }
                    break;
            }
            if (($qti_type === 'homework' || $qti_type === 'peer') && isSet($team_id) && preg_match('/^\d+$/', $team_id)) {
                include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
                $group_mates = getMyGroupMates($team_id, $examinee);
                if (!is_array($group_mates) || count($group_mates) < 1)
                    die('grouping assignment error !');
                
                if (!isSet($comment)) { // 取得原先舊的評語
                    $rs = dbGetStMr('WM_grade_item', 'grade_id, username, comment', 'grade_id in(' . implode(',', $grades) . ') and username in("' . implode('","', $group_mates) . '")', ADODB_FETCH_ASSOC);
						if ($rs) while ($row = $rs->FetchRow())
							$org_comment[$row['grade_id']][$row['username']] = $row['comment'];
                } else
                    $comment_arr = array_fill(0, count($grades), escape_percent_sign($comment));
                
                foreach ($group_mates as $member) {
                    if (!isSet($comment)) {
                        $comment_arr = array();
                        foreach ($grades as $gid)
                            $comment_arr[] = escape_percent_sign($org_comment[$gid][$member]);
                    }
                    
                    $sqls = 'replace WM_grade_item (grade_id,username,score,comment) values ' .
                        vsprintf(vsprintf(str_repeat(sprintf('(%%u,"%s",%.2f,"%%%%s"),', $member, $score),
                            count($grades)),
                            $grades), $comment_arr);
                    $sysConn->Execute(substr(unescape_percent_sign($sqls), 0, -1));
                }
            } else {
                if (!isSet($comment)) { // 取得原先舊的評語
                    $org_comment = $sysConn->GetAssoc('select grade_id, comment from WM_grade_item where grade_id in (' . implode(',', $grades) . ') and username="' . $examinee . '"');
                    foreach ($grades as $gid)
                        $comment_arr[] = escape_percent_sign($org_comment[$gid]);
                } else
                    $comment_arr = array_fill(0, count($grades), escape_percent_sign($comment));
                
                // 是否已經有成績
                $rsGradeItem = $sysConn->GetRow('select count(score) cnt from WM_grade_item where grade_id in (' . implode(',', $grades) . ') and username="' . $examinee . '"');
                
                if (empty($_COOKIE['show_me_info']) === false) {
                    echo '<pre>';
                    var_dump('學員帳號', $examinee);
                    var_dump('是否已經有成績', $rsGradeItem);
                    var_dump('QTI類別', $qti_type);
                    var_dump('是否更新資料', (((int)$rsGradeItem['cnt'] === 0 || $forceOverwrite === 'Y') && $qti_type === 'exam') || $qti_type !== 'exam');
                    echo '</pre>';
                }
                
                // 測驗：沒有成績或者設定強制覆蓋分數時，才更新分數
                if ((((int)$rsGradeItem['cnt'] === 0 || $forceOverwrite === 'Y') && $qti_type === 'exam') || $qti_type !== 'exam') {
                    $sqls = 'replace WM_grade_item (grade_id,username,score,comment) values ' .
                        vsprintf(vsprintf(str_repeat(sprintf('(%%u,"%s",%.2f,"%%%%s"),', $examinee, $score),
                            count($grades)),
                            $grades), $comment_arr);
                    $sysConn->Execute(substr(unescape_percent_sign($sqls), 0, -1));
                }
                
                if ($qti_type === 'peer') {
                    $update = sprintf('total_score=%.2f', $score);
                    $where  = sprintf('exam_id=%d and examinee="%s" limit 1', $exam_id, $examinee);
                    
                    dbSet('WM_qti_peer_result', unescape_percent_sign($update), $where);
                }
            }
            
            $ADODB_FETCH_MODE = $keep;
            return true;
        }
    }
    $ADODB_FETCH_MODE = $keep;
    return false;
}

/**
 * 重新計算總分平均排名
 * @param int $cid course_id
 */
function reCalculateGrades($cid = '')
{
    global $sysConn, $sysSession;
    if ($cid == '') $cid = $sysSession->course_id;
    
    $grade_items = intval($sysConn->GetOne('select count(*) from WM_grade_list where percent != 0 and course_id=' . $cid));
    $grade_sheet = $sysConn->GetAssoc('select M.username,sum(I.score * L.percent / 100) as total ' .
                                      'from WM_term_major as M left join WM_grade_list as L ' .
                                      'on M.course_id=L.course_id ' .
                                      'left join WM_grade_item as I ' .
                                      'on L.grade_id=I.grade_id and M.username=I.username ' .
                                      "where M.course_id={$cid} " .
                                      'group by M.username order by total DESC');
    if ($grade_items && is_array($grade_sheet) && count($grade_sheet)) {
        $sqls = 'replace into WM_grade_stat (`course_id`,`username`,`total`,`average`,`range`) values ';
        $i    = 1;
        foreach ($grade_sheet as $u => $s) {
            $sqls .= sprintf('(%u,"%s",%.2f,%.2f,%u),', $cid, $u, $s, $s / $grade_items, $i++);
        }
        $sysConn->Execute(substr($sqls, 0, -1));
    }
}
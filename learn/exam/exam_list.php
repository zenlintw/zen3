<?php
/**************************************************************************************************
 *                                                                                                *
 *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *        Programmer: Wiseguy Liang                                                         *
 *        Creation  : 2003/03/21                                                            *
 *        work for  : list all available exam(s)                                            *
 *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *        Identifier: $Id: exam_list.php,v 1.8 2010-11-05 09:36:10 lst Exp $
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
if (QTI_which === 'homework') {
    require_once(sysDocumentRoot . '/lang/peer_learn.php');
}
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/exam_lib.php');
include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');

// 手機版需要折行才能呈現
if ($profile['isPhoneDevice']) {
    $MSG['retest'][$sysSession->lang] = str_replace('(', '<BR />(', $MSG['retest'][$sysSession->lang]);
}

$assignmentsForGroup = array();
$examinee_perm       = array(
    'homework' => 1700400200,
    'peer' => 1710400200,
    'exam' => 1600400200,
    'questionnaire' => 1800300200
);
//ACL begin
if (QTI_which == 'exam') {
    $sysSession->cur_func = '1600400100';
    $sysSession->restore();
    if (!aclVerifyPermission(1600400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }
} else if (QTI_which == 'homework') {
    $sysSession->cur_func = '1700400100';
    $sysSession->restore();
    if (!aclVerifyPermission(1700400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }
} else if (QTI_which == 'questionnaire') {
    $sysSession->cur_func = '1800300100';
    $sysSession->restore();
    if (!aclVerifyPermission(1800300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }
}
//ACL end

// url指定測驗編號
$examId = '';
if (isset($_GET['exam_id']) === TRUE) {
    $inputExamId = htmlspecialchars($_GET['exam_id']);
    $key = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
    $examIdNofilter = trim(sysNewDecode($inputExamId, $key));
    if (preg_match('/^([0-9]{9})$/', $examIdNofilter, $matches)) {
        $examId = $matches[1];
    } else {
        noCacheRedirect($baseUrl . '/mooc/message.php?type=18');
    }
}

wmSysLog($sysSession->cur_func, $sysSession->course_id, $_SERVER['argv'][0], 0, 'auto', $_SERVER['PHP_SELF'], 'Go to ' . QTI_which . ' list !  ');
// MIS#23642 若有不正常的作答紀錄則刪除 by Small 2012/01/04
list($nullCount) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', " `examinee`='{$sysSession->username}' and ISNULL(status)", ADODB_FETCH_NUM);
if ($nullCount > 0)
    dbDel('WM_qti_' . QTI_which . '_result', "`examinee`='{$sysSession->username}' and ISNULL(status)");
/*
list($forTA) = dbGetStSr('WM_qti_'.QTI_which.'_result','count(*)',"status='forTA' and `examinee`='{$sysSession->username}'",ADODB_FETCH_NUM);
if($forTA>0)
dbDel('WM_qti_'.QTI_which.'_result',"status='forTA' and `examinee`='{$sysSession->username}'");
*/
// MIS#22742 刪除暫存資料 by Small 2011/10/13
// $sysConn->debug=true;
dbDel('WM_save_temporary', "locate({$sysSession->cur_func},function_id) and username='{$sysSession->username}'");

if (!defined('QTI_env'))
    list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
else
    $topDir = QTI_env;
$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

$announces = array(
    'never' => $MSG['score_publish0'][$sysSession->lang],
    'now' => $MSG['score_publish4'][$sysSession->lang],
    'close_time' => $MSG['score_publish5'][$sysSession->lang]
);

function genTicket($var, $times, $username = '')
{
    global $sysSession;
    return sprintf('%s+%s+%s', $var, $times, md5(sysTicketSeed . $var . $times . $username . $_COOKIE['idx']));
}

function generate_event($exam_id)
{
    global $MSG, $sysSession;
    switch (QTI_which) {
        case 'exam':
            return sprintf('onclick="viewResult(\'%s\');"', genTicket($exam_id, 1));
            break;
        case 'homework':
            return sprintf('onclick="viewExemplar(\'%s\', this);"', genTicket($exam_id, 1));
            break;
        case 'questionnaire':
            return sprintf('onclick="viewStat(\'%s\');"', genTicket($exam_id, 1));
            break;
    }
}


$exam_types = array(
    $MSG['exam_type0'][$sysSession->lang],
    $MSG['exam_type1'][$sysSession->lang],
    $MSG['exam_type2'][$sysSession->lang],
    $MSG['exam_type3'][$sysSession->lang],
    $MSG['exam_type4'][$sysSession->lang],
    $MSG['exam_type5'][$sysSession->lang],
    $MSG['exam_type6'][$sysSession->lang],
    $MSG['exam_type7'][$sysSession->lang],
    $MSG['exam_type8'][$sysSession->lang],
    $MSG['exam_type9'][$sysSession->lang]
);

showXHTML_head_B($MSG[QTI_which . '_title'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    // 手機版或學校問卷
    if ($profile['isPhoneDevice']) {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
        echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
        echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
    }else{
        showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    }
    showXHTML_CSS('include', "/public/css/common.css");
    showXHTML_CSS('include', "/public/css/qti_list.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/peer.css");

    $is_exam = (QTI_which == 'exam') ? 'true' : 'false';
    $is_questionnaire = (QTI_which == 'questionnaire') ? 'true' : 'false';
    $is_phoneDevice = ($profile['isPhoneDevice'])?'true':'false';

    $scr    = <<< EOB
    var isPhoneDevice = '{$profile[isPhoneDevice]}';
function togo(id, blnTeacher, obj, con) {
    var isFirst = true;
    if (blnTeacher && !confirm("{$MSG['try2do_help'][$sysSession->lang]}")) return;
    id += blnTeacher ? '+1' : '+0'; // 是否是教師試做
    
    if (con) id += '+1';
    
    if ({$is_exam}) {
        if (examWin && examWin.closed === false) {
        } else {
            if ({$is_phoneDevice}) id += '+phone';
            examWin = window.open('exam_start.php?' + id, '', 'top=0,left=0,width=' + (screen.availWidth - 6) + ',height=' + (screen.availHeight - 46) + ',toolbar=0,menubar=0,scrollbars=1,resizable=0,status=0');
            // examwin.resizeTo(screen.width, screen.height);
        }
    } else {
        var test_type = $(obj).parents('.box2').data('type');
        if (test_type === 'peer') {
            if ({$is_phoneDevice}) {
                id += '+phone';
                window.open('/learn/peer/exam_pre_start.php?' + id, '', 'top=0,left=0,width=' + (screen.availWidth - 6) + ',height=' + (screen.availHeight - 46) + ',toolbar=0,menubar=0,scrollbars=1,resizable=0,status=0');
            } else {
                location.replace('/learn/peer/exam_pre_start.php?' + id);
            }
        } else {
            if ({$is_phoneDevice}) {
                id += '+phone';
                window.open('exam_pre_start.php?' + id, '', 'top=0,left=0,width=' + (screen.availWidth - 6) + ',height=' + (screen.availHeight - 46) + ',toolbar=0,menubar=0,scrollbars=1,resizable=0,status=0');
            } else {
                location.replace('exam_pre_start.php?' + id);
            }
        }
    }

    if (typeof examWin !== "undefined") {
        examWin.onunload = function() {
            if (isFirst) {
                isFirst = false;
                return;
            }
            window.focus();
            window.location.reload();
        };
    }
}

function view_homework(type, eid, obj) {
    window.open('/learn/' + type + '/view_exemplar.php?' + eid + '+{$sysSession->username}+personal', 'result', 'width=1100, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}
EOB;
    // add by wing 判斷其身份為哪幾種
    $permit = false;
    list($roles) = dbGetStsr('WM_term_major', 'role', "course_id=$course_id and username='{$sysSession->username}'", ADODB_FETCH_NUM);
    if ($roles & $sysRoles['student'])
        $permit = true;

    // 測驗、作業讓老師可以試做
    $isTeacher = $roles & ($sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor']);

    if (defined('QTI_env') && QTI_env == 'academic' && QTI_which == 'questionnaire')
        $permit = true;
    if (QTI_which === 'homework') {
        $sqls = "SELECT \"peer\" test_type,
                    T.exam_id,
                    T.course_id,
                    T.title,
                    T.sort,
                    T.type,
                    T.modifiable,
                    T.publish,
                    T.percent,
                    T.do_times,
                    T.item_per_page,
                    T.announce_type,
                    T.announce_time,
                    T.setting,
                    T.content,
                    '1' as time_id,
                    T.peer_percent,
                    T.self_percent,
                    T.teacher_percent,
                    T.start_date,
                    T.end_date,
                    T.assess_way,
                    T.assess_type,
                    T.peer_times,
                    T.assess_relation,
                    count(R.exam_id) AS times,
                    T.create_time,
                    0 AS max_time_id,
                    T.title caption,
                    case T.modifiable when 'Y' then 0.1 else 0.4 end modifiable_sort,
                    R.status,
                    T.begin_time,
                    T.close_time,
                    T.close_time as delay_time,
                    0 as do_time,
                    (CASE
                    WHEN T.close_time >= NOW() AND COUNT(R.exam_id)=0 THEN '1'
                    WHEN T.close_time >= NOW() AND modifiable='Y' THEN '2'
                    ELSE '3'
                    END) AS DO
             FROM WM_qti_peer_test AS T
             LEFT JOIN WM_qti_peer_result AS R ON T.exam_id = R.exam_id
             AND R.examinee = \"$sysSession->username\"
             WHERE course_id = $course_id
               AND publish='action'
             GROUP BY T.exam_id
             UNION ALL
             SELECT \"homework\" test_type,
                    T.exam_id,
                    T.course_id,
                    T.title,
                    T.sort,
                    T.type,
                    T.modifiable,
                    T.publish,
                    T.percent,
                    T.do_times,
                    T.item_per_page,
                    T.announce_type,
                    T.announce_time,
                    T.setting,
                    T.content,
                    R.time_id,
                    '' as peer_percent,
                    '' as self_percent,
                    '' AS teacher_percent,
                    '' AS start_date,
                    '' AS end_date,
                    '' AS assess_way,
                    '' AS assess_type,
                    '' as peer_times,
                    '' as assess_relation,
                    COUNT(R.exam_id) AS times,
                    T.create_time,
                    MAX(R.time_id) AS max_time_id,
                    T.title caption,
                    case T.modifiable when 'Y' then 0.1 else 0.4 end modifiable_sort,
                    R.status,
                    T.begin_time,
                    T.close_time,
                    T.delay_time,                    
                    R.begin_time as do_time,
                    (CASE
                    WHEN (T.close_time >= NOW() || (T.close_time < NOW() && T.delay_time >= NOW())) AND COUNT(R.exam_id)=0 THEN '1'
                    WHEN (T.close_time >= NOW() || (T.close_time < NOW() && T.delay_time >= NOW())) AND modifiable='Y' THEN '2'
                    ELSE '3'
                    END) AS DO
             FROM WM_qti_homework_test AS T
             LEFT JOIN WM_qti_homework_result AS R ON T.exam_id = R.exam_id
             AND R.examinee = '$sysSession->username'
             WHERE course_id = $course_id
               AND publish = 'action'
             GROUP BY T.exam_id
             ORDER BY DO ASC, create_time DESC, sort, exam_id desc";
//echo '<pre>';
//var_dump($sqls);
//echo '</pre>';
        $RS   = $sysConn->execute($sqls);
    } else {
        $parameterExamId = '';
        if (empty($examId) === FALSE) {
            $parameterExamId = sprintf("and T.exam_id = %s", $examId);
        }
        
        $RS = dbGetStMr(
            'WM_qti_' . QTI_which . '_test as T Left join WM_qti_' . QTI_which . '_result AS R ON T.exam_id = R.exam_id AND R.examinee = "' . $sysSession->username . '"',
            "T.*, count(R.exam_id) as times, max(R.time_id) as max_time_id,
            CASE T.modifiable WHEN 'Y' THEN 0.1 ELSE 0.4 END modifiable_sort,
            case T.do_times WHEN 0 then 0.1 else 0.8 end do_times_sort,
            R.status,
            case IFNULL(R.status, 'NULL') when 'NULL' then 0 when 'break' then 0  when 'submit' then 0.7 when 'revised' then 1 when 'publish' then 1 end status_sort,
            CAST(case T.begin_time when '0000-00-00 00:00:00' then 0.1 else (T.begin_time >= now()) end AS DECIMAL(10,2)) begin_time_sort, 
            CAST(case T.close_time when '9999-12-31 00:00:00' then 0.1 else (T.close_time <= now()) end AS DECIMAL(10,2)) close_time_sort,
            CAST(case T.modifiable WHEN 'Y' then 0.1 else 0.4 end AS DECIMAL(10,2)) + CAST(case T.do_times WHEN 0 then 0.1 else 0.8 end AS DECIMAL(10,2)) + CAST(case IFNULL(R.status, 'NULL') when 'NULL' then 0 when 'break' then 0  when 'submit' then 0.7 when 'revised' then 1 when 'publish' then 1 end AS DECIMAL(10,2)) + CAST( case T.begin_time when '0000-00-00 00:00:00' then 0.1 else (T.begin_time >= now()) end AS DECIMAL(10,2)) reply_time_sort",
            "course_id=$course_id and publish='action' {$parameterExamId} group by T.exam_id order by reply_time_sort, close_time_sort, begin_time desc",
            ADODB_FETCH_ASSOC
        );
    }
    if ($sysConn->ErrorNo() > 0) {
        echo $sysConn->ErrorMsg();
    }

    $p = aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable');

    if ($RS) {
        $dashCnt = 0;
        while (!$RS->EOF) {
            if (empty($_COOKIE['show_me_info']) === false) {
                echo '<pre>';
                var_dump('--資料分隔線---');
                echo '</pre>';
            }   

            if($RS->fields['type'] == 5) {
                $RS->MoveNext();
                continue;
            }
            
            if (isset($RS->fields['test_type']) === false) {
                $RS->fields['test_type'] = QTI_which;
            }
            $assignmentsForGroup = getAssignmentsForGroup(null, $RS->fields['test_type']);
            $aclVerified         = aclVerifyPermission($examinee_perm[QTI_which], $p, $course_id, $RS->fields['exam_id']);
            if ($aclVerified === 'WM2')
                $aclVerified = $permit;
            if (!$aclVerified && !$isTeacher) {
                $RS->MoveNext();
                continue; // 如果這個三合一不是指派給我的，就不秀 (復航客製)
            }
            
            /*if (strpos($RS->fields['setting'], 'upload') !== false && $profile['isPhoneDevice']) {
                $RS->MoveNext();
                continue;
            }*/
            

            $time_id   = intval($RS->fields['max_time_id']);
            $last_stat = '';
            if (QTI_which == 'exam') {
                // Bug#1417 修正刪除考試紀錄後，無法進入考試 by Small 2006/09/14
                // 取已作答總次數 (有測驗但是不一定完成測驗)
                list($times) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', "exam_id    ={$RS->fields['exam_id']} and examinee='{$sysSession->username}' and status!='forTA' ", ADODB_FETCH_NUM);
                // 取得最後測驗的狀態
                list($last_stat) = dbGetStSr('WM_qti_' . QTI_which . '_result', '`status`', "exam_id    ={$RS->fields['exam_id']} and examinee='{$sysSession->username}' and status!='forTA' order by `time_id` desc", ADODB_FETCH_NUM);
                // 取得最大的time_id
                list($time_id) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'max(time_id)', "exam_id={$RS->fields['exam_id']} and examinee='{$sysSession->username}' and status!='forTA' ", ADODB_FETCH_NUM);
            } else if (($RS->fields['test_type'] === 'homework' || $RS->fields['test_type'] === 'peer') && isset($assignmentsForGroup[$RS->fields['exam_id']])) // 判斷群組作業是否已有人繳交
                {
                $times = isAlreadySubmittedAssignmentForGroup($RS->fields['exam_id'], $sysSession->username, $sysSession->course_id, $RS->fields['test_type']) ? 1 : 0;
            } else {
                list($times) = dbGetStSr('WM_qti_' . $RS->fields['test_type'] . '_result', 'count(*)', "exam_id    ={$RS->fields['exam_id']} and examinee='{$sysSession->username}' and status!='break'", ADODB_FETCH_NUM);
            }
            $times      = intval($times);
            $begin_time = strtotime($RS->fields['begin_time'] == '9999-12-31 00:00:00' ? '0000-00-00 00:00:00' : $RS->fields['begin_time']);
            $close_time = strtotime($RS->fields['close_time']);
            $delay_time = strtotime($RS->fields['delay_time']);
            $now        = date('Y-m-d H:i:s');
            if (($now >= $RS->fields['begin_time'] and $now <= $RS->fields['close_time']) or ($RS->fields['begin_time'] === null and $RS->fields['close_time'] === null) or ($RS->fields['begin_time'] === null and $now <= $RS->fields['close_time']) or ($now >= $RS->fields['begin_time'] and $RS->fields['close_time'] === null) or ($now <= $RS->fields['delay_time'])) {
                $isPay = ' active';
            } else {
                $isPay = '';
            }
            $begin_time = substr($RS->fields['begin_time'], 0, 10);
            $close_time = substr($RS->fields['close_time'], 0, 10);
            $delay_time = substr($RS->fields['delay_time'], 0, 10);
            if ($begin_time === '0000-00-00') {
                $begin_time = $MSG['now'][$sysSession->lang];
            }
            if ($close_time === '9999-12-31') {
                $close_time = $MSG['forever'][$sysSession->lang];
            }
            $payDate = $begin_time . ' ~ ' . $close_time;
            if ((($now >= $RS->fields['start_date'] and $now <= $RS->fields['end_date']) or ($RS->fields['start_date'] === null and $RS->fields['end_date'] === null) or ($RS->fields['start_date'] === null and $now <= $RS->fields['end_date']) or ($now >= $RS->fields['start_date'] and $RS->fields['end_date'] === null)) && ($RS->fields['peer_percent'] >= 1 || $RS->fields['self_percent'] >= 1)) {
                $isRating = ' active';
            } else {
                $isRating = '';
            }
            $start_date = substr($RS->fields['start_date'], 0, 10);
            $end_date   = substr($RS->fields['end_date'], 0, 10);
            if ($start_date === '0000-00-00') {
                $start_date = $MSG['now'][$sysSession->lang];
            }
            if ($end_date === '9999-12-31') {
                $end_date = $MSG['forever'][$sysSession->lang];
            }
            $ratingDate  = $start_date . ' ~ ' . $end_date;
            $rsGradeList = dbGetStMr('WM_grade_list', 'publish_begin, publish_end', 'course_id = ' . sprintf('%08u', $sysSession->course_id) . ' and property = ' . sprintf('%09u', $RS->fields['exam_id']));
            if ($rsGradeList) {
                while (!$rsGradeList->EOF) {
                    $score_begin_time = $rsGradeList->fields['publish_begin'];
                    $score_close_time = $rsGradeList->fields['publish_end'];
                    $rsGradeList->MoveNext();
                }
                if (($now >= $score_begin_time and $now <= $score_close_time) or ($score_begin_time === null and $score_close_time === null) or ($score_begin_time === null and $now <= $score_close_time) or ($now >= $score_begin_time and $score_close_time === null)) {
                    $isScore = ' active';
                } else {
                    $isScore = '';
                }
                if ($score_begin_time === $score_close_time && $score_close_time === '0000-00-00 00:00:00') {
                    $scoreDate = $MSG['score_publish0'][$sysSession->lang];
                } else {
                    if ($score_begin_time === '1970-01-01 00:00:00' || $score_begin_time === '0000-00-00 00:00:00') {
                        $score_begin_time = $MSG['now'][$sysSession->lang];
                    }
                    if ($score_close_time === '9999-12-31 00:00:00') {
                        $score_close_time = $MSG['forever'][$sysSession->lang];
                    }
                    $scoreDate = substr($score_begin_time, 0, 10) . ' ~ ' . substr($score_close_time, 0, 10);
                }
            }

            // 作業型態及比重
            $assignmentsForGroup = getAssignmentsForGroup(null, $RS->fields['test_type']);
            
            // 測驗關閉的條件
//            $isContinue = false;
            $extra      = array(
                'username' => $sysSession->username,
//                'last_stat' => $last_stat
                'function_id' => $examinee_perm[QTI_which],
                'isgroup' => isset($assignmentsForGroup[$RS->fields['exam_id']]),
            );
//            $now        = time();
            $isTimeout = checkExamWhetherTimeout($RS->fields, strtotime($now), $times, NULL, $extra);
            $title      = (strpos($RS->fields['title'], 'a:') === 0) ? getCaption($RS->fields['title']) : array(
                'Big5' => $RS->fields['title'],
                'GB2312' => $RS->fields['title'],
                'en' => $RS->fields['title'],
                'EUC-JP' => $RS->fields['title'],
                'user_define' => $RS->fields['title']
            );
            if (empty($_COOKIE['show_me_info']) === false) { 
                echo '<pre>';
                var_dump('測驗編號', $RS->fields['exam_id']);
                var_dump('測驗名稱', $title['Big5']);
                var_dump('是否超過期限', $isTimeout);
                echo '</pre>';
            }  
            
            $iconAssignmentsType = 'icon-user-blue';
            $imgTitle            = $MSG['for personal'][$sysSession->lang];
            if ($RS->fields['test_type'] != 'questionnaire') {
                if ($RS->fields['test_type'] === 'homework' || $RS->fields['test_type'] === 'peer') {
                    // 判斷是否為群組作業
                    if (isset($assignmentsForGroup[$RS->fields['exam_id']])) {
                        $iconAssignmentsType = 'icon-group-blue';
                        $imgTitle            = $MSG['for group'][$sysSession->lang];
                    }
                } else {
                    $imgTitle   = $exam_types[$RS->fields['type']];
                    $titleClass = ($RS->fields['type'] == 3) ? 'title-orange' : (($RS->fields['type'] == 5)?'title-purple':'title-blue');
                }
                if (QTI_which == 'homework' && $profile['isPhoneDevice']) {
                    $style = 'style="display: inline-block;"';
                } else {
                    $style = '';
                }
                $forHtml     = '<div class="' . $iconAssignmentsType . ' exam-type-tips" data-toggle="tooltip" '.$style.' title="' . (QTI_which == 'homework' ? $MSG['assignment type'][$sysSession->lang] : $MSG['table_title1'][$sysSession->lang]) . ': ' . $imgTitle . '">' . '</div>';
                $percentHtml = '<span class="sparkpie exam-percent-tips"  data-toggle="tooltip" title="' . $RS->fields['percent'] . '%">' . $RS->fields['percent'] . ',' . (100 - $RS->fields['percent']) . '</span>';
                if (QTI_which == 'homework') {
                    $percentHtml .= '&nbsp;';
                }
            }else{
                $imgTitle   = $exam_types[$RS->fields['type']];
                $titleClass = ($RS->fields['type'] == 5)?'title-purple':'title-blue';
            }

            // 名稱
            $nameHtml = '<span style="width: 230px;" title="' . $title[$sysSession->lang] . '">';
            if (QTI_which == 'homework' && !$isTimeout) {
        if ($RS->fields['test_type'] == 'peer')
                    $content = dbGetOne('WM_qti_peer_test', 'content', 'exam_id=' . intval($RS->fields['exam_id']));
        else
            $content = dbGetOne('WM_qti_homework_test', 'content', 'exam_id=' . intval($RS->fields['exam_id']));
                if (!$dom = domxml_open_mem($content)) {
                    die('Error while parsing the document.');
                }
                $ctx  = xpath_new_context($dom);
                $ret1 = $ctx->xpath_eval('count(//item)');
                if (intval($ret1->value) > 0) {
                    $nameHtml .= '<a href="javascript:;" onclick="homework_preview(' . $RS->fields['exam_id'] . ', this);return false;">' . htmlspecialchars($title[$sysSession->lang]) . '</a>';
                } else {
                    $nameHtml .= htmlspecialchars($title[$sysSession->lang]);
                }
            } else {
                $nameHtml .= htmlspecialchars($title[$sysSession->lang]);
            }
            $nameHtml .= '</span>';

            // 繳交期間
            if ($RS->fields['test_type'] === 'homework') {
                $beginTime = (($now > $RS->fields['delay_time'] && ($RS->fields['delay_time']!== '0000-00-00 00:00:00')) || ($now > $RS->fields['close_time'] && $now <= $RS->fields['delay_time'] && $RS->fields['delay_time'] !== '9999-12-31 00:00:00')) ? $MSG['payback_deadline'][$sysSession->lang] . $RS->fields['delay_time'] : ($MSG['from'][$sysSession->lang] . (strpos($RS->fields['begin_time'], '0000') === 0 ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS->fields['begin_time']))) . ' ' . $MSG['to2'][$sysSession->lang] . (strpos($RS->fields['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS->fields['close_time']))));
            } else {
                $beginTime = ($MSG['from'][$sysSession->lang] . (strpos($RS->fields['begin_time'], '0000') === 0 ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS->fields['begin_time']))) . ' ' . $MSG['to2'][$sysSession->lang] . (strpos($RS->fields['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS->fields['close_time']))));
            }

            // 查看結果
            $resultEnable = '';
            if ($RS->fields['test_type'] == 'homework' && ($times > 0 || isAlreadySubmittedAssignmentForGroup($RS->fields['exam_id'], $sysSession->username))) {
                $resultEnable = 'active';
                $resultEvent  = 'onclick="view_homework(\'homework\', \'' . genTicket($RS->fields['exam_id'], 1, $sysSession->username) . '\', this);"';
            } elseif (QTI_which != 'exam') {
                //            $resultEnable = ($isTimeout ? '' : 'active');
                $resultEvent = '';
                $resultTxt   = $isTimeout ? ($times ? $MSG['have submitted'][$sysSession->lang] : $MSG['ready2test'][$sysSession->lang]) : ($times ? $MSG['modify_questionnaire'][$sysSession->lang] : $MSG['ready2test'][$sysSession->lang]);
            } else {
                $resultEvent  = '';
            }

            // 繳交
            $ready2testEnable = '';
            $ready2testEvent  = '';
            if ($aclVerified) {
                if (QTI_which == 'exam') {
                    // 若是有之前有進行到一半的測驗，則隱藏進行測驗的按鈕
                    if ($isContinue) {
                        $ready2testEnable = ($isTimeout ? '' : 'active');
                        $ready2testEvent  = '';
                    } else {
                        $ready2testEnable = ($isTimeout ? '' : 'active');
                        $ready2testEvent  = ($isTimeout ? '' : ('onclick="togo(\'' . genTicket($RS->fields['exam_id'], $time_id + 1) . '\', false, this)"'));
                    }
                } else {
                    $ready2testEnable = ($isTimeout ? '' : 'active');
                    $ready2testEvent  = ($isTimeout ? '' : ('onclick="togo(\'' . genTicket($RS->fields['exam_id'], $time_id + 1) . '\', false, this)"'));
                }
            }
            
            /*if (strpos($RS->fields['setting'], 'upload') !== false && $profile['isPhoneDevice']) {
                $btn_disable = 'disabled';
            } else {
                $btn_disable = '';
            }*/
            
            if (isset($assignmentsForGroup[$RS->fields['exam_id']])) { // 判斷群組作業是否已有人繳交
                $payTimes = isAlreadySubmittedAssignmentForGroup($RS->fields['exam_id'], $sysSession->username, $sysSession->course_id,$RS->fields['test_type']) ? 1 : 0;
            } else {
                list($payTimes) = dbGetStSr('WM_qti_peer_result', 'count(*)', "exam_id = {$RS->fields['exam_id']} and examinee = '{$sysSession->username}' and status != 'break'", ADODB_FETCH_NUM);
            }
            
            $payTimes = intval($payTimes);
            if ($RS->fields['test_type'] === 'peer') {
                if ($payTimes > 0 || isAlreadySubmittedAssignmentForGroup($RS->fields['exam_id'], $sysSession->username)) {
                    $isLookLast = '<button class="btn btn-plane-white ' . ((QTI_which == 'exam') ? '' : 'btn-small') . '" '.$btn_disable.' onclick="view_homework(\'peer\', \'' . genTicket($RS->fields['exam_id'], 1, $sysSession->username) . '\', this);">' . $MSG['look_last'][$sysSession->lang] . '</button>';
                } else {
                    $isLookLast = '<button class="btn btn-plane-white disabled ' . ((QTI_which == 'exam') ? '' : 'btn-small') . '">' . $MSG['look_last'][$sysSession->lang] . '</button>';
                }
            } else {
                $isLookLast = '';
            }
            $examinee1 = 'peer';
            $examinee2 = 'self';
            
            // 如果是分組互評則判斷是否組長
            $right       = '1';
            $captainName = '';
            if (isset($assignmentsForGroup[$RS->fields['exam_id']])) {
                include_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
                $right = '0';

                // 取本作業的設定的分組
                $team_id = $sysConn->GetOne(sprintf("SELECT DISTINCT SUBSTRING(member, 2, 1) FROM WM_acl_list LEFT JOIN WM_acl_member ON WM_acl_member.acl_id = WM_acl_list.acl_id WHERE function_id = '%s' and unit_id = '%s' and instance = '%s'", $examinee_perm[$RS->fields['test_type']], $sysSession->course_id, $RS->fields['exam_id']));

                // 取子分組流水號
                $group_id = $sysConn->GetOne(sprintf("select group_id from WM_student_div where course_id = '%s' and team_id = '%s' and username = '%s'", $course_id, $team_id, $sysSession->username));

                // 驗證是否有在對象中
                $isTarget = $sysConn->GetOne(sprintf("SELECT COUNT(member) FROM WM_acl_list LEFT JOIN WM_acl_member ON WM_acl_member.acl_id = WM_acl_list.acl_id WHERE function_id = '%s' and unit_id = '%s' and instance = '%s' and WM_acl_member.member = '@%s.%s'", $examinee_perm[$RS->fields['test_type']], $sysSession->course_id, $RS->fields['exam_id'], $team_id, $group_id));
                if ($isTarget === '0') {
                    $right = '0';
                } else {
                    // 取帳號是否為指定分組的指定子群組的組長
                    $right = IsGroupCaptain($course_id, $team_id, $group_id, $sysSession->username); // 1組長, 0不是組長
                }

                // 取組長姓名，如果是組長直接抓session，如果不是重新從資料表取得
                if ($right === '1') {
                    $captainName = $sysSession->realname;
                } else {
                    list($firstName, $lastName) = $sysConn->GetRow(sprintf("select WM_user_account.first_name, WM_user_account.last_name from WM_student_group left join WM_user_account on WM_user_account.username = WM_student_group.captain where course_id = '%s' and team_id = '%s' and group_id = '%s'", $course_id, $team_id, $group_id));
                    $captainName = checkRealname($firstName, $lastName);
                    $isRating = '';
                }
                $groupRatingNote = sprintf($MSG['only_leader_rating'][$sysSession->lang], '');
                // 提示訊息
                $htmlGroupRatingNote = '<div style="margin-top: -0.7em;">' . $groupRatingNote . '</div>';
                
                // 取分組的成員（計算自評用）
                $ratedGroupmates = array();
                $group_mates = getMyGroupMates($team_id, $sysSession->username, $course_id);
                if (is_array($group_mates) && count($group_mates) >= 1) {
                    $ratedGroupmates = array_merge($ratedGroupmates, $group_mates);
                }
                $ratedGroupmates = array_unique($ratedGroupmates);
                $examinees = '';
                foreach ($ratedGroupmates as $v) {
                    $examinees .= sprintf("'%s',", $v);
                }
                if (is_array($ratedGroupmates) && count($ratedGroupmates) >= 1) {
                    $examinees = substr($examinees, 0, -1);
                }
            } else {
                $group_id = null;
                $groupRatingNote = '';
                $htmlGroupRatingNote = '';
                
                // 取個人（計算自評用）
                $examinees = sprintf("'%s'", $sysSession->username);
            }
            
            // 有沒有被分到組
            if (isset($assignmentsForGroup[$RS->fields['exam_id']]) === true && $isTarget === '0' && $right === '0' && $isTeacher === 0) {
                $RS->MoveNext();
                continue;
            }
                
            if ((int) $RS->fields['peer_percent'] >= 1 && (int) $RS->fields['self_percent'] >= 1) {

                // 有權限（不分群組或個人通用）
                if ($right === '1') {
                    $enterRating = '<div class="level1 active">
                                             <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>' . $groupRatingNote . '<div class="process-period">' . $ratingDate . '</div>
                                         </div>';
                } else {
                    $enterRating = '<div class="active" onClick="javascript:alert(\'' . $groupRatingNote . '\');">
                                             <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>' . $htmlGroupRatingNote . '<div class="process-period">' . $ratingDate . '</div>
                                         </div>';
                }
                
                $peer_count  = round($sysConn->GetOne('select count(score) from WM_qti_peer_result_score where exam_id = ' . $RS->fields['exam_id'] . ' and time_id = ' . ($time_id + 1) . ' and creator = \'' . $sysSession->username . '\' and score_type = 0'), 2);

                // 取自評次數
                $self_count  = round($sysConn->GetOne('select count(score) from WM_qti_peer_result_score where exam_id = ' . $RS->fields['exam_id'] . ' and time_id = ' . ($time_id + 1) . ' and examinee in (' . $examinees . ') and score_type = 1'), 2);
                if ((int) $peer_count === 0) {
                    $peerButton = $MSG['peer_assessment'][$sysSession->lang];
                } else if ((int) $peer_count >= (int) $RS->fields['peer_times']) {
                    $peerButton = $MSG['rated'][$sysSession->lang] . ' <span class="strong">' . $peer_count . '</span> ' . $MSG['piece'][$sysSession->lang];
                } else {
                    $peerButton = $MSG['rated'][$sysSession->lang] . ' <span class="strong">' . $peer_count . '</span> /' . $RS->fields['peer_times'] . $MSG['piece'][$sysSession->lang];
                }

                // 修先順序 1先互評 2先自評 0沒有順序
                switch ($RS->fields['assess_relation']) {
                    case '0':
                        if ((int) $self_count === 1) {
                            $enterRating .= '<div class="level2">
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange" style="padding: 0; margin-top: 0;">' . $peerButton . '</a>
                                                 <button type="button" class="btn btn-primary self btn-orange disabled" style="margin-left: 0; margin-top: 0;">' . $MSG['self_assessment'][$sysSession->lang] . '</button>
                                             </div>';
                        } else {
                            $enterRating .= '<div class="level2">
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer btn-orange" style="padding: 0; margin-top: 0;">' . $peerButton . '</a>
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary self btn-orange" style="padding: 0; margin-top: 0;">' . $MSG['self_assessment'][$sysSession->lang] . '</a>
                                             </div>';
                        }
                        break;
                    case '1':
                        if ((int) $peer_count >= (int) $RS->fields['peer_times'] && (int) $self_count === 0) {
                            $enterRating .= '<div class="level2">
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange" style="padding: 0; margin-top: 0;">' . $peerButton . '</button></a>
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary self span2 btn-orange" style="padding: 0; margin-top: 0;">' . $MSG['self_assessment'][$sysSession->lang] . '</button></a>
                                             </div>';
                        } else if ((int) $peer_count >= (int) $RS->fields['peer_times'] && (int) $self_count === 1) {
                            $enterRating .= '<div class="level2">
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange" style="padding: 0; margin-top: 0;">' . $peerButton . '</button></a>
                                                 <button type="button" class="btn btn-primary self btn-orange disabled" style="margin-left: 0; margin-top: 0;">' . $MSG['self_assessment'][$sysSession->lang] . '</button>
                                             </div>';
                        } else {
                            $enterRating .= '<div class="level2">
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange" style="padding: 0; margin-top: 0;">' . $peerButton . '</button></a>
                                                 <button type="button" class="btn btn-primary self btn-orange disabled" style="margin-left: 0; margin-top: 0;">' . $MSG['self_assessment'][$sysSession->lang] . '</button>
                                             </div>';
                        }
                        break;
                    case '2':
                        if ((int) $self_count === 1) {
                            $enterRating .= '<div class="level2">
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange" style="padding: 0; margin-top: 0;">' . $peerButton . '</button></a>
                                                 <button type="button" class="btn btn-primary self btn-orange disabled" style="margin-left: 0; margin-top: 0;">' . $MSG['self_assessment'][$sysSession->lang] . '</button>
                                             </div>';
                        } else {
                            $enterRating .= '<div class="level2">
                                                 <button type="button" class="btn btn-primary peer btn-orange disabled" style="margin-left: 0; margin-top: 0;">' . $peerButton . '</button>
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary self span2 btn-orange" style="padding: 0; margin-top: 0;">' . $MSG['self_assessment'][$sysSession->lang] . '</button></a>
                                             </div>';
                        }
                        break;
                }
            } elseif ((int) $RS->fields['peer_percent'] === 0 && (int) $RS->fields['self_percent'] === 0) {
                $enterRating = '<div class="active">
                                        <div class="process-title">' . $MSG['peer_title'][$sysSession->lang] . '</div>
                                    </div>';
                // 沒有自評
            } elseif ((int) $RS->fields['peer_percent'] >= 1 && (int) $RS->fields['self_percent'] === 0) {
                if ($isRating === '') {                    
                    $enterRating = '<div class="active" onClick="javascript:alert(\'' . $groupRatingNote . '\');">
                                             <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>' . $htmlGroupRatingNote . '<div class="process-period">' . $ratingDate . '</div>
                                         </div>';
                } else {
                    $peer_count = round($sysConn->GetOne('select count(score) from WM_qti_peer_result_score where exam_id = ' . $RS->fields['exam_id'] . ' and time_id = ' . ($time_id + 1) . ' and creator = \'' . $sysSession->username . '\' and score_type = 0'), 2);
                    if ((int) $peer_count === 0) {
                        $peerButton = $MSG['peer_assessment'][$sysSession->lang];
                    } else if ((int) $peer_count >= (int) $RS->fields['peer_times']) {
                        $peerButton = $MSG['rated'][$sysSession->lang] . ' <span class="strong">' . $peer_count . '</span> ' . $MSG['piece'][$sysSession->lang];
                    } else {
                        $peerButton = $MSG['rated'][$sysSession->lang] . ' <span class="strong">' . $peer_count . '</span> /' . $RS->fields['peer_times'] . $MSG['piece'][$sysSession->lang];
                    }

                    // 有權限（不分群組或個人通用）
                    if ($right === '1') {
                        $enterRating = '<div class="level1 active">
                                                 <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>' . $groupRatingNote . '<div class="process-period">' . $ratingDate . '</div>
                                             </div>
                                             <div class="level2">
                                                 <a href="/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer btn-orange" style="padding: 0; margin-top: 0;">' . $peerButton . '</a>
                                             </div>';
                    } else {
                        $enterRating = '<div class="active" onClick="javascript:alert(\'' . $groupRatingNote . '\');">
                                                 <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>' . $htmlGroupRatingNote . '<div class="process-period">' . $ratingDate . '</div>
                                             </div>';
                    }
                }
            } elseif ((int) $RS->fields['peer_percent'] === 0 && (int) $RS->fields['self_percent'] >= 1) {
                // 取自評次數
                $self_count = round($sysConn->GetOne('select count(score) from WM_qti_peer_result_score where exam_id = ' . $RS->fields['exam_id'] . ' and examinee in (' . $examinees . ') and score_type = 1'), 2);
                if ((int) $self_count === 1) {
                    $enterRating = '<div class="active">
                                            <div class="process-title">' . $MSG['self_assessment_finished'][$sysSession->lang] . '</div>
                                            <div class="process-period">' . $ratingDate . '</div>
                                        </div>';
                } else if ($isRating === '') {          
                    $enterRating = '<div class="active" onClick="javascript:alert(\'' . $groupRatingNote . '\');">
                                             <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>' . $htmlGroupRatingNote . '<div class="process-period">' . $ratingDate . '</div>
                                         </div>';
                } else {
                    $enterRating = '<div class="active" onClick="javascript:location.href=\'/learn/peer/exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '\'">
                                            <div class="process-title">' . $MSG['btn_enter2'][$sysSession->lang] . $MSG['self_assessment'][$sysSession->lang] . '</div>
                                            <div class="process-period">' . $ratingDate . '</div>
                                        </div>';
                }
            }
            // 續考
            if (QTI_which == 'exam') {
                // MIS#22982 by Small 2011/11/02
                // list ($last_time_id, $status, $content) = $sysConn->GetRow('select time_id, status, content from WM_qti_exam_result where exam_id=' . $RS->fields['exam_id'] . ' order by time_id desc limit 1');
                list($last_time_id, $status, $content) = $sysConn->GetRow('select time_id, status, content from WM_qti_exam_result where exam_id=' . $RS->fields['exam_id'] . ' and examinee="' . $sysSession->username . '" order by time_id desc limit 1');

                // 續考的條件：未逾時、現在這次考試就是最後一次答案卷、所有題目在同一頁(不分頁)
                // if (!$isTimeout && $status == 'break' && ($RS->fields['item_per_page'] > 0))
                $continueDisable = 'disabled';
                $continueEvent   = '';
                if ($isContinue) {
                    $continueDisable = '';
                    $continueEvent   = 'onclick="togo(\'' . genTicket($RS->fields['exam_id'], $last_time_id) . '\', false, this, true)"';
                }
            }

            // 公布日期
            $announceEnable = false;
            $announceEvent  = '';
            switch ($RS->fields['announce_type']) {
                case 'now':
                    // 有效繳交才能進入本頁面
                    $submitTimes = dbGetOne('WM_qti_' . QTI_which . '_result','count(exam_id)',sprintf('exam_id=%u and examinee="%s" AND status IN ("submit", "revised")', $RS->fields['exam_id'], $sysSession->username), ADODB_FETCH_ASSOC);
                    if ((int)$submitTimes >= 1) {
                        $announceEvent  = generate_event($RS->fields['exam_id']);
                        $announceEnable = true;
                    }
                    $announceTxt = $announces[$RS->fields['announce_type']];
                    break;
                case 'close_time':
                    if ($RS->fields['close_time'] <= $now) {
                        $announceEvent  = generate_event($RS->fields['exam_id']);
                        $announceEnable = true;
                    }
                    $announceTxt = $announces[$RS->fields['announce_type']];
                    break;
                case 'delay_time':
                    if ($RS->fields['delay_time'] <= $now) {
                        $announceEvent  = generate_event($RS->fields['exam_id']);
                        $announceEnable = true;
                    }
                    $announceTxt = $announces[$RS->fields['announce_type']];
                    break;
                case 'user_define':
                    if ($RS->fields['announce_time'] != '9999-12-31 00:00:00' && strtotime($RS->fields['announce_time']) <= time()) {
                        $announceEvent  = generate_event($RS->fields['exam_id']);
                        $announceEnable = true;
                    }
                    $announceTxt = substr($RS->fields['announce_time'], 0, 16);
                    break;
                default:
                    $announceTxt = $announces[$RS->fields['announce_type']];
                    break;
            }
            // 如果是作業，時間為觀看佳作
            if (QTI_which == 'homework') {
                $publishTxt       = $MSG['score_publish_' . QTI_which][$sysSession->lang] . ': ' . $announceTxt;
                $announceTxt      = '';
                $publishEvent     = $announceEvent;
                $announceEvent    = '';
                $excellentBtnHtml = '<button class="btn btn-plane-white btn-small ' . (($announceEnable == true) ? '' : 'disabled') . '" '.$btn_disable.' title="' . $publishTxt . '" ' . $publishEvent . '>' . $MSG['look_best'][$sysSession->lang] . '</button>';
            } else if ((int)$RS->fields['do_times'] === 1 && (int)$RS->fields['do_times'] <= $times && !(in_array($RS->fields['status'], array('submit', 'revised', 'publish')))) {
                $resultEvent  = '';
            } else {
                $resultEnable = (($announceEnable == true) ? 'active' : '');
                $resultEvent  = $announceEvent;
            }

            // 測驗繳交狀況
            if (QTI_which == 'exam') {
                $note     = '&nbsp;';
                $noteHtml = '&nbsp;';
                
                if (empty($content))
                {
                   $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
                                           $sysSession->school_id,
                                           $sysSession->course_id,
                                           QTI_which,
                                           $RS->fields['exam_id'],
                                           $sysSession->username);
                   $file =     $last_time_id.'.xml';          
            
                   $full_path = $xml_path.$file;
                   if (is_file($full_path)) {
                       $content = file_get_contents($full_path);
                   }
                }
                
                if (isset($content) && preg_match('/<wm:submit_status>(.*)<\/wm:submit_status>/isU', $content, $match)) {
                    switch ($match[1]) {
                        case 'over':
                            $note = $MSG['msg_exam_quit'][$sysSession->lang];
                            break; // 放棄作答
                        case 'mark':
                            $note = $MSG['out_of_time'][$sysSession->lang];
                            break; // 作答逾時
                        case 'timeout':
                            $note = $MSG['msg_exam_timeout'][$sysSession->lang];
                            break; // 作答時間到自動交卷
                        case 'submit':
                        case 'continue':
                            $match[1] = 'submit';
                            $note = $MSG['msg_exam_submit'][$sysSession->lang];
                            break; // 繳交完成
                        case 'chgWin':
                            $note = $MSG['msg_exam_chgwin'][$sysSession->lang];
                            break; // 切換視窗，強制交卷
                    }
                    $noteHtml = '<img src="/public/images/answer_' . strtolower($match[1]) . '.png" title="' . $note . '"/>';
                }
            }


            // 教師試作
            if ($isTeacher) {
                $tryBtnHtml = '<button class="btn btn-plane-white ' . ((QTI_which == 'exam') ? '' : 'btn-small') . '" '.$btn_disable.' onclick="togo(\'' . genTicket($RS->fields['exam_id'], $time_id + 1) . '\', true, this)">' . $MSG['try2do'][$sysSession->lang] . '</button>';
            } else {
                $tryBtnHtml = '&nbsp;';
            }

            // 測驗 評分 結果區塊顯示
            if ($RS->fields['test_type'] === 'peer') {
                $payStyle    = 'width: 33%;';
                $ratingStyle = 'width: 34%;';
                $resultStyle = 'width: 33%;';
            } else {
                $payStyle    = 'width: 50%;'; // (QTI_which == 'questionnaire')?'width: 100%;':'width: 50%;';
                $ratingStyle = 'display: none;';
                $resultStyle = 'width: 50%;'; // (QTI_which == 'questionnaire')?'display: none;':'width: 50%;';
            }
            $statusStyle = (QTI_which == 'exam') ? 'min-width: 110px;' : 'display: none;';

            // 組 html

            // 虛線
            if ($dashCnt != 0 && QTI_which == 'exam') {
                $html .= '<div class="divider-horizontal" style="margin: 1.2em 0; border-style: dashed;"></div>';
            } else {
                $dashCnt++;
            }

            $html .= '<div class="box2" data-type="' . $RS->fields['test_type'] . '">';
            if (QTI_which == 'exam') {
                $html .= '<div class="func-title ' . $titleClass . '">
                            <div class="element type" style="">
                            </div>
                            <div class="element title" style="width: 100%;">
                                <div class="class">' . $imgTitle . '：</div>' . $nameHtml . '</div>
                            <div class="element" style="min-width: 40px;">' . $percentHtml . '</div>
                            <div class="element" style="min-width: 110px;">' . $tryBtnHtml . '</div>
                        </div>';
            }else if (QTI_which == 'questionnaire') {
                $html .= '<div class="func-title ' . $titleClass . '">
                            <div class="element type" style="">
                            </div>
                            <div class="element title" style="width: 100%;">
                                <div class="class">' . $imgTitle . '：</div>' . $nameHtml . '</div>
                            <div class="element" style="min-width: 110px;">' . $tryBtnHtml . '</div>
                        </div>';
            } else {
                if ($profile['isPhoneDevice']) {
                    $html .= '<div  style="width: 100%;">' . $forHtml . '<div style="display: inline-block;">'.$percentHtml .'</div>
                            <div class="operate" style="display: table;float: right;">' . '<div class="btn-group" style="display: table-cell;">' . $tryBtnHtml . $isLookLast . $excellentBtnHtml . '</div>
                            

                        </div></div><div class="title">'. $nameHtml . $note . '</div>';
                } else {
                    $html .= '<div class="title" style="width: 70%;">' . $forHtml . $percentHtml . $nameHtml . $note . '</div>
                            <div class="operate" style="display: table;">' . '<div class="btn-group" style="display: table-cell;">' . $tryBtnHtml . $isLookLast . $excellentBtnHtml . '</div>

                        </div>';
                }
            }
            switch ($RS->fields['test_type']) {
                case 'homework':
                case 'peer':
                    if ($times >= 1) {
                        $step1Caption = $MSG['have submitted'][$sysSession->lang];
                    } else {
                        $step1Caption = $MSG['ready2test'][$sysSession->lang];
                    }
                    break;
                case 'exam':
                    $step1Caption = $MSG['ready2test'][$sysSession->lang];
                    break;
                case 'questionnaire':
                    $step1Caption = $resultTxt;
                    break;
            }
            
            $captionTimes = '';
            if (QTI_which == 'exam' && $times >= 1) {
                $captionTimes = str_replace('%times%', $times, $MSG['retest_times'][$sysSession->lang]);
            }
            
            /*if (strpos($RS->fields['setting'], 'upload') !== false && $profile['isPhoneDevice']) {
                $html .= '<div class="content">
                              <div class="data5 mooc-process">
                              <div class="process-btn pay " style="width: 100%;">
                                        <div class="level1 ">
                                            <div class="main-text" style="font-size:12px;color: #000000;text-align: left;font-weight: unset;line-height: 2.2em;opacity: .65;">行動裝置不支援檔案上傳，請改用電腦裝置來上傳檔案。</div>
                                    </div></div>
                              </div>
                          </div>';
            } else {*/
                $html .= '<div class="content">
                                <div class="data5 mooc-process">
                                    <div class="process-btn pay ' . $ready2testEnable . '" ' . ((QTI_which == 'exam' && $times > 0) ? '' : $ready2testEvent) . ' style="' . $payStyle . '">
                                        <div class="level1 ' . ((QTI_which == 'exam' && $times > 0) ? 'active' : '') . '">
                                            <div class="main-text"><span class="caption-enter">' . $step1Caption . '</span><span class="caption-times">' . $captionTimes . '</span></div>
                                        <div class="sub-text">' . $beginTime . '</div>
                                    </div>';
                if (QTI_which == 'exam') {
                    $html .= '<div class="level2" style="display: none;">
                                            <div class="btn btn-blue ' . (($continueDisable == 'disabled') ? '' : 'disabled') . '" ' . $ready2testEvent . '>' . str_replace('%times%', $times, $MSG['retest'][$sysSession->lang]) . '</div>
                                            <div class="btn btn-blue ' . $continueDisable . '" ' . $continueEvent . '>' . $MSG['table_title8'][$sysSession->lang] . '</div>
                                        </div>';
                }
                if ($RS->fields['test_type'] === 'peer') {
                    $announceTxt  = '';
                    $resultEnable = '';
                } else {
                    $scoreDate = '';
                    $isScore   = '';
                }
                if ($isScore === ' active') {
                    $isScoreClick = 'onClick="javascript:window.open(\'/learn/peer/look_result.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id'] . $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1)  . '+' . $group_id  . '+' . $team_id . '\', \'result\', \'width=1050, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1\')"';
                } else {
                    $isScoreClick = '';
                }
                $html .= '</div>
                                    <div class="process-btn rating' . $isRating . '" style="' . $ratingStyle . '">' . $enterRating . '
                                </div>
                                    <div class="process-btn score ' . $resultEnable . ' ' . $isScore . '" ' . $resultEvent . ' ' . $isScoreClick . ' style="' . $resultStyle . '">
                                    <div class="level1">
                                        <div class="main-text">' . $MSG['check_result'][$sysSession->lang] . '</div>
                                            <div class="sub-text">' . $announceTxt . $scoreDate . '</div>
                                    </div>
                                </div>
                                <div class="process-btn"  style="' . $statusStyle . '">
                                    <div class="level1">' . $noteHtml . '</div>
                                </div>
                            </div>
                        </div>
                    </div>';
            //}

            $RS->MoveNext();
        }
    }
    if (empty($html)) {
        $html = '<div class="box2">
                <div class="content">
                    <div class="data4">
                        <div class="message">' . $MSG['no_data'][$sysSession->lang] . '</div>
                    </div>
                </div>
            </div>';
    }
    

    if ($profile['isPhoneDevice']) {
        echo '<script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>';
        echo '<script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>';
    }else{
        showXHTML_script('include', '/lib/jquery/jquery.min.js');
    }
    showXHTML_script('include', '/theme/default/sparkline/js/jquery.sparkline.min.js');
    showXHTML_script('include', "/theme/default/bootstrap/js/bootstrap-tooltip.js");
    showXHTML_script('include', '/learn/exam/exam_list.js');
    showXHTML_script('inline', $scr);
showXHTML_head_E();

showXHTML_body_B();
    if ($profile['isPhoneDevice']) {
        require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

        if (QTI_env == 'academic') {
            $smarty->display('common/site_header.tpl');
        }else{
            
            if (isset($_GET['exam_id']) === TRUE) {
                
            } else { 
                $smarty->display('common/course_header.tpl');
            }
            $smarty->assign('isCourseTeacher', $isTeacher);
        }
        $smarty->display('phone/learn/exam_style.tpl');
        if (QTI_which != 'exam') {
            $mobile_tip = str_replace("%TYPE%",((defined('QTI_env') && QTI_env == 'academic' && QTI_which == 'questionnaire') ? $MSG['title2'][$sysSession->lang] : $MSG[QTI_which . '_title'][$sysSession->lang]),$MSG['mobile_tip'][$sysSession->lang]);
        /*$tip_html = '<div class="content">
                         <div class="alert alert-danger" style="font-size:16px;color: #000000;text-align: left;font-weight: unset;line-height: 1.5em;opacity: .65;"><span class="lcms-red-starmark">* </span>' . $mobile_tip . '</div>
                     </div>';*/
        }
    }
    echo '<div class="box1">
                <div class="title">' . ((defined('QTI_env') && QTI_env == 'academic' && QTI_which == 'questionnaire') ? $MSG['title2'][$sysSession->lang] : $MSG[QTI_which . '_title'][$sysSession->lang]) . '</div>
                <div class="content">' . $tip_html.$html . '</div>
            </div>';
showXHTML_body_E();
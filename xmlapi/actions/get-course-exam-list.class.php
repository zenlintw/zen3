<?php
define('API_QTI_which', 'exam');
if (!defined('QTI_env')) {
    define('QTI_env', 'learn');
}

include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/exam_lib.php');

/**
 * 取得課程測驗列表
 */
class GetCourseExamListAction extends baseAction
{
    var $_mysqlUsername = '';

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysRoles, $sysSession;

        $testTable = 'WM_qti_exam_test';
        $courseList = array();
        $examListResult = array();
        $unitExams = array();
        $unitInfo = array();
        $announce = array(
            'never'       => 0,
            'now'         => 1,
            'close_time'  => 2,
            'user_define' => 3
        );
        $pageFlow = array(
            'none'       => 0,
            'can_return' => 0,
            'lock'       => 1
        );
        $examListTotalSize = 0;

        $keyword = (isset($_GET['keyword']) && !empty($_GET['keyword'])) ? charset2Utf8(trim($_GET['keyword'])) : '';

        if (!isset($_GET['offset'])) {
            $offset = 0;
        } else {
            $offset = intval($_GET['offset']);
        }

        if (!isset($_GET['pagesize'])) {
            $size = 15;
        } else {
            $size = intval($_GET['pagesize']);
        }

        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);
        // 取得使用者的課程列表
        $rs = &dbGetCoursesWithQTI(
            'M.*, C.caption, C.teacher, C.st_begin, C.st_end',
            $this->_mysqlUsername,
            $sysRoles['auditor']|$sysRoles['student']|$sysRoles['teacher']|$sysRoles['assistant']|$sysRoles['instructor'],
            'course_id DESC',
            '',
            $testTable,
            $keyword
        );

        if ($rs['result'] && $rs['totalSize'] > 0) {
            // 有修課才有取測驗的必要

            while ($row = $rs['result']->FetchRow()) {
                // 若課程有測驗，則將course_id加入稍後取問卷的清單陣列
                $courseList[] = $row['course_id'];
                $unitName = getCaption($row['caption']);
                $period = str_replace(
                    '-',
                    '/',
                    sprintf('%s%s',
                        ((!empty($row['st_begin']))? $row['st_begin'] : '0').' ~ ',
                        ((!empty($row['st_end']))? $row['st_end'] : '0'))
                );
                $unitInfo[$row['course_id']] = array(
                    'name' => strip_tags(nodeTitleStrip(htmlspecialchars_decode($unitName[$sysSession->lang]))),
                    'period' => $period
                );
            }
        
            // 如果清單陣列有值，則表示要進行取得試卷的動作
            if (count($courseList) > 0) {
                $examListResult = getQTIListRecord(implode(',', $courseList), API_QTI_which, $this->_mysqlUsername, $offset, $size);
            }
            $examList = $examListResult['record'];
            $examListTotalSize = $examListResult['total'];

            // 有需要取出試卷
            if (count($examList) > 0) {
                while (!$examList->EOF) {
                    $courseId = $examList->fields['course_id'];
                    $qtiId = $examList->fields['exam_id'];

                    $aclVerified = verifyQTIPermission(API_QTI_which, $courseId, $qtiId, $this->_mysqlUsername);
                    if (!$aclVerified) {
                        $examList->MoveNext();
                        continue;
                    }
                    $now = strtotime(date('Y-m-d H:i:s'));

                    $times = getQTIAnswerCount(API_QTI_which, $examList->fields['exam_id'], $this->_mysqlUsername);

                    // 測驗關閉的條件
                    $isContinue = false;
                    // 取得最後測驗的狀態
                    $lastStat = dbGetOne(
                        'WM_qti_exam_result',
                        '`status`',
                        "exam_id={$examList->fields['exam_id']} AND examinee='{$this->_mysqlUsername}' ORDER BY `time_id` DESC"
                    );
                    $extra = array(
                        'username'  => $this->_mysqlUsername,
                        'last_stat' => $lastStat
                    );
                    $isTimeout = checkAPPExamWhetherTimeout($examList->fields, $now, $times, $isContinue, $extra, 'exam');

                    // 取得及格成績
                    $matches = array();
                    $thresholdScore = 0;
                    if (preg_match('/\bthreshold_score="([^"]*)"/', $examList->fields['content'], $matches)) {
                        $thresholdScore = ($matches[1] == '') ? 0 : floatval($matches[1]);
                    }
                    // 自我評量要取得公布設定
                    $scorePublishType = '';
                    if (intval($examList->fields['type']) == 1 && preg_match('/\bscore_publish_type="(\w*)"/', $examList->fields['content'], $matches)) {
                        // 選擇作答完公布(now)或是到了自訂時間或是到了關閉時間就要提供s、sa、sar
                        if ($examList->fields['announce_type'] === 'now' ||
                                ($examList->fields['announce_type'] === 'user_define' && (time() > strtotime($examList->fields['announce_time']))) ||
                                ($examList->fields['announce_type'] === 'close_time' && (time() > strtotime($examList->fields['close_time'])))) {
                            if ($matches[1] == 'simple') {
                                $scorePublishType = 's';    // score
                            } else if ($matches[1] == 'detailed') {
                                $scorePublishType = 'sa';   // score+answer
                            } else if ($matches[1] == 'complete') {
                                $scorePublishType = 'sar';  // score+answer+reference
                            }
                        }
                    }

                    // 取得題目總數
                    if (intval($examList->fields['random_pick']) === 0) {
                        $totalItems = getQTIContentItemCount($examList->fields['content']);
                    } else {
                        // 隨機挑題
                        $totalItems = intval($examList->fields['random_pick']);
                    }

                    // 取得總分
                    $totalScore = 0;
                    if (strpos($examList->fields['content'], '<wm_immediate_random_generate_qti') !== FALSE) {
                        if (preg_match('!<score [^>]*>(.*)</score>!sU', $examList->fields['content'], $regs)) {
                            $totalScore = intval($regs[1]);
                        }
                    } else {
                        $out = array();
                        preg_match_all('/ score="([0-9.]+)">/', $examList->fields['content'], $out, PREG_PATTERN_ORDER);
                        if (count($out[1]) > $totalItems) {
                            $totalScore = array_sum(array_splice($out[1], 0, $totalItems));
                        } else {
                            $totalScore = isset($out[1]) ? array_sum($out[1]) : 0;
                        }
                    }

                    $announceType = intval($announce[$examList->fields['announce_type']]);
                    $announceTime = $examList->fields['announce_time'];
                    $passStatus = verifyPassStatus($examList, $times, $thresholdScore, $announceType, $announceTime);

                    $unitExams[] = array(
                        'unit_id' => $courseId,
                        'unit_name' => $unitInfo[$courseId]['name'],
                        'unit_period' => $unitInfo[$courseId]['period'],
                        'examId' => (integer)$examList->fields['exam_id'],
                        'examType' => $examList->fields['type'],
                        'title' => qtiPaperTitle($examList->fields['title']),
                        'score' => $totalScore,
                        'passed' => $passStatus['passed'],
                        'status' => $aclVerified && !$isTimeout ? 1 : 0,
                        'thresholdScore' => $thresholdScore,
                        'score_publish_type' => $scorePublishType,
                        'intervalTime' => (intval($examList->fields['do_interval']) === 0) ? 60 : intval($examList->fields['do_interval']),
                        'announce_type' => $announceType,
                        'announce_time' => $announceTime,
                        'pageFlow' => $pageFlow[$examList->fields['ctrl_paging']],
                        'examItems' => $totalItems,
                        'limitTimes' => intval($examList->fields['do_times']),
                        'notice' => $examList->fields['notice'],
                        'doneTimes' => $times,
                        'beginTime' => $examList->fields['begin_time'] == '9999-12-31 00:00:00' ? '0000-00-00 00:00:00' : $examList->fields['begin_time'],
                        'closeTime' => $examList->fields['close_time'],
                        'passStatus' => $passStatus['passStatus'],
                        'support' => checkSupportAPP($examList->fields['exam_id'], $courseId, API_QTI_which)
                    );
                    $examList->MoveNext();
                }
            }
        }
        $responseObject = array(
            'code' => 0,
            'message' => 'success',
            'data' => array(
                'total_size' => $examListTotalSize,
                'list' => $unitExams
            ),
        );
        $statusCode = '200';
        $this->header('application/json', $statusCode);
        echo JsonUtility::encode($responseObject);
        exit();
    }
}
<?php
//define('API_QTI_which', 'exam');
if (!defined('QTI_env')) {
    define('QTI_env', 'learn');
}
include_once(dirname(__FILE__) . '/action.class.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/exam_lib.php');
require_once(PATH_LIB . 'common-qti.php');

/**
 * 取得課程測驗詳情
 */
class GetQtiDetailInfoAction extends baseAction
{
    var $_qtiTypes = array('exam', 'questionnaire', 'homework'),
        $_announceTypes = array(
            'never'       => 0,
            'now'         => 1,
            'close_time'  => 2,
            'user_define' => 3
        ),
        $_pageFlow = array(
            'none'       => 0,
            'can_return' => 0,
            'lock'       => 1
        ),
        $_mysqlUsername = '';

    /**
     * @param $xmlContent QTI xml
     * @return float|int 及格分數
     */
    function getQTIThresholdScore($xmlContent) {
        $matches = array();
        $thresholdScore = 0;

        if (preg_match('/\bthreshold_score="([^"]*)"/', $xmlContent, $matches)) {
            $thresholdScore = ($matches[1] == '') ? 0 : floatval($matches[1]);
        }
        return $thresholdScore;
    }

    /**
     * 統計 xml 總分
     * @param string $xmlContent QTI xml
     * @param integer $totalItems 題目總數
     * @return float|int 總分
     */
    function getQTITotalScore($xmlContent, $totalItems) {
        $totalScore = 0;
        // 沒有給予 item 總數時，自行重新計算
        $totalItems = (isset($totalItems)) ? $totalItems : getQTIContentItemCount($xmlContent);

        if (strpos($xmlContent, '<wm_immediate_random_generate_qti') !== FALSE) {
            if (preg_match('!<score [^>]*>(.*)</score>!sU', $xmlContent, $regs)) {
                $totalScore = intval($regs[1]);
            }
        } else {
            $out = array();
            preg_match_all('/ score="([0-9.]+)">/', $xmlContent, $out, PREG_PATTERN_ORDER);
            if (count($out[1]) > $totalItems) {
                $totalScore = array_sum(array_splice($out[1], 0, $totalItems));
            } else {
                $totalScore = isset($out[1]) ? array_sum($out[1]) : 0;
            }
        }

        return $totalScore;
    }

    /**
     * 取得QTI的試卷列表
     *
     * @param string $qtiId 單位代碼
     * @param string $qtiType 三合一的哪一種 exam|questionnaire|homework
     * @param string $username 帳號
     *
     * @return array 列表資料, 總筆數
     **/
    function getQTIDetailById ($qtiId, $qtiType, $username) {
        $username = mysql_real_escape_string($username);
        $testTable = 'WM_qti_' . $qtiType . '_test';
        $resultTable = 'WM_qti_' . $qtiType . '_result';

        return  dbGetStSr(
            sprintf(
                '`%s` AS T LEFT JOIN `%s` AS R ON T.exam_id = R.exam_id AND R.examinee = "%s"',
                $testTable,
                $resultTable,
                $username),
            'SQL_CALC_FOUND_ROWS T.*',
            sprintf(
                "T.exam_id = %d GROUP BY T.`exam_id`",
                intval($qtiId))
        );
    }

    /**
     * 取得課程資訊
     * @param $courseId 課程編號
     * @return array 課程名稱, 課程時間
     */
    function getSimpleCourseById ($courseId) {
        $unitInfo = array();
        $course_rs = dbGetStSr(
            "`WM_term_course`",
            "caption, teacher, st_begin, st_end",
            sprintf("`course_id` = %d", intval($courseId)));
        if ($course_rs) {
            $unitName = getCaption($course_rs['caption']);
            $period = str_replace(
                '-',
                '/',
                sprintf('%s%s',
                    ((!empty($row['st_begin'])) ? $course_rs['st_begin'] : '0').' ~ ',
                    ((!empty($row['st_end'])) ? $course_rs['st_end'] : '0'))
            );
            $unitInfo = array(
                'name' => strip_tags(nodeTitleStrip(htmlspecialchars_decode($unitName['Big5']))),
                'period' => $period
            );
        }
        return $unitInfo;
    }

    function dataHandler ($data) {
        $params = array();

        // type 須符合 qti
        $params['type'] = trim($data['type']);
        if (!in_array($data['type'], $this->_qtiTypes)) {
            $this->returnHandler(3, 'fail');
        }

        // 檢查測驗狀態 - 測驗編號不正確 或是 非三合一型態
        $params['eid'] = intval($data['eid']);
        if ($data['eid'] < 100000001 || $data['eid'] > 999999999) {
            $this->returnHandler(4, 'fail', array(), '404');
        }

        return $params;
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession;
        $unitData = array();

        // 處理網址傳來的參數
        $inputData = $_GET;
        $params = $this->dataHandler($inputData);

        // 取得試卷詳情
        $qtiResult = $this->getQTIDetailById($params['eid'], $params['type'], $sysSession->username);
        if ($qtiResult) {
            $extra = array();
            $now = strtotime(date('Y-m-d H:i:s'));
            $qtiType = $params['type'];

            // 驗證使用者在試卷權限
            $aclVerified = verifyQTIPermission($qtiType, $qtiResult['course_id'], $params['exam_id'], $sysSession->username);
            if (!$aclVerified) {
                $this->returnHandler(2, 'fail', array(''));
            }

            // 取得單位資料
            $unitInfo = $this->getSimpleCourseById($qtiResult['course_id']);

            // 作答次數
            $times = getQTIAnswerCount($qtiType, $params['exam_id'], $sysSession->username);

            // 取得題目總數
            $totalItems = getQTIContentItemCount($qtiResult['content']);

            // 公布設定與時間
            $announceType = $this->_announceTypes[$qtiResult['announce_type']];
            $announceTime = $qtiResult['announce_time'];

            if ($qtiType === "exam") {
                // 測驗關閉的條件
                $isContinue = false;

                // 取得最後測驗的狀態
                $lastStat = dbGetOne(
                    'WM_qti_exam_result',
                    '`status`',
                    sprintf(
                        "`exam_id` = %d AND `examinee` = '%s' ORDER BY `time_id` DESC",
                        $qtiResult['exam_id'],
                        mysql_real_escape_string($sysSession->username))
                );
                $extra = array(
                    'username' => $sysSession->username,
                    'last_stat' => $lastStat
                );

                // 取得及格成績
                $thresholdScore = $this->getQTIThresholdScore($qtiResult['content']);

                // 取得總分
                $totalScore = $this->getQTITotalScore($qtiResult['content'], $totalItems);

                // 通過狀態
                $passStatus = verifyPassStatus($qtiResult, $times, $thresholdScore, $announceType, $announceTime);
            }

            // checkAPPExamWhetherTimeout 內的 $isContinue 似乎已沒作用?
            $isTimeout = checkAPPExamWhetherTimeout($qtiResult, $now, $times, $isContinue, $extra, $params['type']);

            // 三合一共用資料
            $unitCommon = array(
                'unit_id' => (integer)$qtiResult['course_id'],
                'unit_name' => $unitInfo['name'],
                'unit_period' => $unitInfo['period'],
                'examId' => (integer)$qtiResult['exam_id'],
                'title' => qtiPaperTitle($qtiResult['title']),
                'status' => $aclVerified && !$isTimeout ? 1 : 0,
                'notice' => $qtiResult['notice'],
                'beginTime' => $qtiResult['begin_time'] == '9999-12-31 00:00:00' ? '0000-00-00 00:00:00' : $qtiResult['begin_time'],
                'closeTime' => $qtiResult['close_time'],
                'announce_type' => $announceType,
                'announce_time' => $announceTime,
            );
            switch ($params['type']) {
                case "exam":
                    // 測驗資料
                    $unitExtra = array(
                        'pageFlow' => $this->_pageFlow[$qtiResult['ctrl_paging']],
                        'examItems' => $totalItems,
                        'score' => $totalScore,
                        'thresholdScore' => $thresholdScore,
                        'limitTimes' => intval($qtiResult['do_times']),
                        'doneTimes' => $times,
                        'intervalTime' => intval($qtiResult['do_interval']),
                        'passed' => $passStatus['passed'],
                        'passStatus' => $passStatus['passStatus'],
                        'support' => checkSupportAPP($qtiResult['exam_id'], $qtiResult['course_id'], $params['type'])
                    );
                    break;
                case "homework":
                    //TODO: 作業目前沒有定義需要的資料，先與問卷相同
                case "questionnaire":
                    // 問卷資料
                    $unitExtra = array(
                        'unit_type' => (strlen(strval($qtiResult['course_id'])) === 5)? 'school' : 'course',
                        'amount' => $totalItems,
                        'pageFlow' => 0,
                        'modifiable' => ($qtiResult['modifiable'] === 'Y')? true : false,
                        'upload' => ($qtiResult['setting'] == 'upload')? true : false,
                        'anonymity' => ($qtiResult['setting'] == 'anonymity')? true : false,
                        'done' => ($times > 0)? true : false,
                        'statistics_viewable' => false
                    );
                    break;
            }
            // 合併資料
            $unitData = array_merge($unitCommon, $unitExtra);
        } else {
            // 資料取得有誤
            $this->returnHandler(5, 'fail');
        }

        $this->returnHandler(0, 'success', $unitData);
    }
}
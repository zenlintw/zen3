<?php
define('XMLAPI', true);
define('API_QTI_which', 'exam');
if (!defined('QTI_env')) {
    define('QTI_env', 'learn');
}


define('QTI_DISPLAY_ANSWER',   true); // 是否顯示答案
define('QTI_DISPLAY_OUTCOME',  true); // 是否顯示得分
define('QTI_DISPLAY_RESPONSE', true); // 是否顯示作答答案
include_once(dirname(__FILE__).'/action.class.php');
include_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
include_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
require_once(PATH_LIB . 'qti.php');

/**
 * 提交測驗
 */
class SubmitExamAction extends baseAction
{
    var $_QTI_WHICH = '';
    var $_mysqlUsername = '';
    var $_functionId = array('exam' => 1600400200, 'homework' => 1700400200, 'questionnaire' => 1800300200);
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $ctx;

        // 變數宣告
        $totalScore = 0;
        $returnData = array();
        $announce = array(
            'never'       => 0,
            'now'         => 1,
            'close_time'  => 2,
            'user_define' => 3
        );
        $enforceSubmit = false;

        // 處理接收的資料
        if (isset($_GET['eid'])) {
            $qtiId = intval($_GET['eid']);
            $type = (isset($_GET['type'])) ? trim($_GET['type']) : 'exam';
            $data = JsonUtility::decode($GLOBALS['HTTP_RAW_POST_DATA']);
            $answerItems = $data['items'];
        } else {
            $inputData = file_get_contents('php://input');
            $postData = JsonUtility::decode($inputData);
            $qtiId = intval($postData['eid']);
            $type = trim($postData['type']);
            $answerItems = $postData['items'];
            $enforceSubmit = isset($postData['enforceSubmit']) ? $postData['enforceSubmit'] : false;
        }

        $this->_QTI_WHICH = $type;
        $this->_mysqlUsername = mysql_real_escape_string($sysSession->username);

        $qti = new Qti();
        $res = $qti->checkExamStat($qtiId, $this->userSessionId, $sysSession->username, $exam, $this->_QTI_WHICH, $enforceSubmit);
        if ($res > 0) {
            $this->responseHandler(400, $res, 'fail', $returnData);
        }

        // 解析資料
        $xml = $qti->saveAnswer($exam, $answerItems);
        if ($xml === false) {
            $this->responseHandler(400, 14, 'fail', $returnData);
        }

        $timeId = $exam['time_id']; // 取得最大測驗次數
        $courseId = $exam['course_id']; // 課程編號

        $xml = str_replace(
            '</questestinterop>',
            '<wm:submit_status>appSubmit</wm:submit_status></questestinterop>',
            $xml
        );

        $ctx = xpath_new_context(domxml_open_mem($xml));
        if ($this->_QTI_WHICH === 'exam') {
            // 計算得分
            ob_start();
            parseQuestestinterop($xml);
            $result_html = ob_get_contents();
            ob_end_clean();
            if (preg_match_all('/<input\b[^>]*\bname="item_scores\b[^>]*\bvalue="(-?[0-9.]*)"/', $result_html, $regs)) {
                $totalScore = array_sum($regs[1]);
            }

            // 判斷是否都是是非選擇題
            $status = 'revised';
            if ($dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $xml))) {
                $ctx = xpath_new_context($dom);
                $ret1 = $ctx->xpath_eval('count(//item/presentation//render_choice)+count(//item/presentation//response_grp/render_extension)');
                $ret2 = $ctx->xpath_eval('count(//item/presentation)');
                $status = (intval($ret1->value) < intval($ret2->value)) ? 'submit' : 'revised';
            }
        }

        // 存入資料庫
        dbSet(
            'WM_qti_' . $this->_QTI_WHICH . '_result',
            'content="' . mysql_real_escape_string($xml) . '", `status`="' . $status . '", submit_time=NOW(), score=' . $totalScore,
            "exam_id={$qtiId} AND examinee='{$this->_mysqlUsername}' AND time_id={$timeId}"
        );
        appSysLog($this->_functionId[$this->_QTI_WHICH], $courseId, $qtiId, 0, 'classroom', $_SERVER['PHP_SELF'], 'APP ' . strtoupper($this->_QTI_WHICH) . ' start! Num of times: ' . $timeId, $sysSession->username);

        $rs = dbGetStSr('WM_qti_' . $this->_QTI_WHICH . '_test', '*', "exam_id={$qtiId}");

        // 測驗關閉的條件
        $times = getQTIAnswerCount($this->_QTI_WHICH, $qtiId, $this->_mysqlUsername);
        $extra = array(
            'username'  => $this->_mysqlUsername,
            'last_stat' => $status
        );
        $now = strtotime(date('Y-m-d H:i:s'));
        $isTimeout = checkAPPExamWhetherTimeout($rs, $now, $times, false, $extra, $this->_QTI_WHICH);

        if ($this->_QTI_WHICH === 'exam') {
            $announceType = intval($announce[$rs['announce_type']]);
            $announceTime = $rs['announce_time'];
            $thresholdScore = 0;
            if (preg_match('/\bthreshold_score="([^"]*)"/', $rs['content'], $matches)) {
                $thresholdScore = ($matches[1] == '') ? 0 : floatval($matches[1]);
            }
            $passStatus = verifyPassStatus($rs, $times, $thresholdScore, $announceType, $announceTime);
        }
        // 取得使用者繳卷順序
            // 取得此測驗所有人的繳卷時間
        $submitRankData = dbGetAssoc(
            "`WM_qti_" . $this->_QTI_WHICH . "_result`",
            "`examinee`, `submit_time`",
            sprintf("`exam_id` = %d AND `status` != 'break' ORDER BY `submit_time`", $qtiId),
            ADODB_FETCH_ASSOC);
        $submitRank = 1;
        $userSubmitTime = strtotime($submitRankData[$sysSession->username]);
        foreach ($submitRankData AS $v) {
            // 有比自己早繳卷的人排行就 + 1
            if (strtotime($v) < $userSubmitTime) {
                $submitRank++;
            }
        }
        // 計算使用者答對答錯題數
        if ($this->_QTI_WHICH === 'exam') {
            $QtiDomXml = new QtiDomXml();
            $xml = mb_convert_encoding($xml, 'UTF-8', 'UTF-8');
            $dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $xml));
            $root = $dom->document_element();
            $ctx = xpath_new_context($dom);
            $QtiDomXml->replaceSectionOrder($dom, $root, $ctx);
            $QtiDomXml->replaceItemToComplete($dom, $root, $ctx, $this->_QTI_WHICH);
            $dom = @domxml_open_mem(
                preg_replace(
                    array(
                        '/<item\s+xmlns="http:\/\/www\.imsglobal\.org\/question\/qtiv1p2\/qtiasiitemncdtd\/ims_qtiasiv1p2\.dtd"\s+xmlns:wm="http:\/\/www\.sun\.net\.tw\/WisdomMaster"\s+/',
                        '/<item\s+xmlns:wm="http:\/\/www\.sun\.net\.tw\/WisdomMaster"\s+/',
                        '/<item\s+xmlns="http:\/\/www\.imsglobal\.org\/question\/qtiv1p2\/qtiasiitemncdtd\/ims_qtiasiv1p2\.dtd"\s+/'
                    ),
                    array('<item ',
                        '<item ',
                        '<item '
                    ),
                    $QtiDomXml->setEncoding($dom->dump_mem())
                )
            );

            $ctx = xpath_new_context($dom);
            $qti = new Qti();
            $resultItems = $qti->transformer($dom, $ctx, $this->_QTI_WHICH);
            $resultStatistics = $qti->statistics($resultItems);
        }
        // @TODO 等沒有app server再使用ob_flush
        // 計算成績(非問卷)
        if (reCalculateQTIGrade($sysSession->username, $qtiId, $this->_QTI_WHICH) && $this->_QTI_WHICH !== 'questionnaire') {
            reCalculateGrades($courseId);
        }

        $returnData = array(
            'status' => !$isTimeout ? 1 : 0,              // 試卷型態(exam | questionnaire | homework)
            'statistics' => array(      // 試卷統計
                'score' => $totalScore, // 得分
                'correct' => (isset($resultStatistics)) ? $resultStatistics['correct'] : 0,         // 答對題數
                'wrong' => (isset($resultStatistics)) ? $resultStatistics['wrong'] : 0,           // 答錯題數
                'pass' => (isset($passStatus)) ? $passStatus['passStatus'] : true,         // 是否及格
                'doneTimes' => $times,       // 作答次數
                'rank' => (isset($submitRank)) ? $submitRank : 0              // 繳卷順序排行
            ),
            'items' => $resultItems,          // 作答結果
        );

        // output
        $this->responseHandler(200, 0, 'success', $returnData);
    }
    /**
     * 回應處理
     *
     * @param string $status http status code, 200|400
     * @param integer $code 0~15
     * @param string $message success|fail
     * @param array $data
     *
     **/
    function responseHandler ($status, $code, $message, $data) {
        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        // output
        $this->header('application/json', $status);
        echo JsonUtility::encode($responseObject);
        exit();
    }
}
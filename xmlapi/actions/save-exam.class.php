<?php
define('XMLAPI', true);
define('API_QTI_which', 'exam');
if (!defined('QTI_env')) {
    define('QTI_env', 'learn');
}

include_once(dirname(__FILE__).'/action.class.php');
require_once(PATH_LIB . 'qti.php');

/**
 * 暫存測驗結果
 */
class SaveExamAction extends baseAction
{
    function main()
    {
        global $sysSession;

        // 驗證 Ticket
        parent::checkTicket();
        $eid = abs(intval($_GET['eid']));

        $statusCode = '200';
        $responseObject = array(
            'code' => 0,
            'message' => 'success',
            'data' => array()
        );

        $qti = new Qti();
        $res = $qti->checkExamStat($eid, $this->userSessionId, $sysSession->username, $exam);

        if ($res > 0) {
            $responseObject['code'] = 2;
            $responseObject['message'] = 'fail';
            $statusCode = '404';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            exit();
        }

        if ($exam['time_id'] <= 0) {
            $responseObject['code'] = 3;
            $responseObject['message'] = 'fail';
            $statusCode = '500';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            exit();
        } else if ($exam['last_stat'] !== 'break') {
            $responseObject['code'] = 4;
            $responseObject['message'] = 'fail';
            $statusCode = '500';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            exit();
        }

        // 解析資料
        $data = JsonUtility::decode($GLOBALS['HTTP_RAW_POST_DATA']);
        $xml = $qti->saveAnswer($exam, $data);
        if ($xml === false) {
            $responseObject['code'] = 5;
            $responseObject['message'] = 'fail';
            $statusCode = '500';

            $this->header('application/json', $statusCode);
            echo JsonUtility::encode($responseObject);
            exit();
        }

        $timeId = $exam['time_id']; // 取得最大測驗次數
        $courseId = $exam['course_id']; // 課程編號
        $examinee_perm = array(
            'exam' => 1600400200
        );

        // 存入資料庫 (必須在 $qti->transformer 之後，因為要等 transformer 後產生 item_result 的資料)
        dbSet(
            'WM_qti_' . API_QTI_which . '_result',
            'content="' . mysql_real_escape_string($xml) . '"',
            "exam_id={$eid} AND examinee='{$sysSession->username}' AND time_id={$timeId}"
        );
        appSysLog($examinee_perm['exam'], $courseId, $eid , 0, 'auto', $_SERVER['PHP_SELF'], API_QTI_which . ' start! Num of times: ' . $timeId);

        // output
        $this->header('application/json', $statusCode);
        echo JsonUtility::encode($responseObject);
        exit();
    }
}
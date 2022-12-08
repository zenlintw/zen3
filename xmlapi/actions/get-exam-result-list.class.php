<?php
include_once(dirname(__FILE__) . '/action.class.php');

/**
 * 取得作答紀錄列表
 */
class GetExamResultListAction extends baseAction
{
    var $_default_qti_type = array('exam', 'questionnaire', 'homework');
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        // 預設回傳資料
        $code = 2;
        $message = 'No Data';
        $data = array();

        $jsonHandler = new JsonUtility();

        $type = trim($_REQUEST['type']);
        $eid = intval($_REQUEST['eid']);
        $mysqlUsername = mysql_real_escape_string($sysSession->username);

        // 型態是三合一，而且eid不為0
        if ($type !== '' && in_array($type, $this->_default_qti_type) && $eid > 0) {
            $table = '`WM_qti_' . $type . '_result`';
            $fields = '*';
            $where = "`examinee` = '{$mysqlUsername}' AND `exam_id` = {$eid} AND `status` != 'break'";

            $RS = dbGetStMr($table, $fields, $where);
            if ($RS) {
                $data = array();

                // 因為是先作測驗紀錄，故先以測驗的項目為主，之後有擴充再異動
                while ($result = $RS->FetchRow()) {
                    $submitSecond = intval(strtotime($result['submit_time']));
                    $beginSecond = intval(strtotime($result['begin_time']));
                    $answerSeconds = 0;

                    if ($submitSecond > $beginSecond) {
                        $answerSeconds = $submitSecond - $beginSecond;
                    }

                    $matches = array();
                    $thresholdScore = -1;
                    if (preg_match('/\bthreshold_score="([^"]*)"/', $result['content'], $matches)) {
                        $thresholdScore = ($matches[1] == '') ? -1 : floatval($matches[1]);
                    }

                    $info = array (
                        'time_id' => $result['time_id'],
                        'status' => $result['status'],
                        'begin_time' => $result['begin_time'],
                        'answer_seconds' => $answerSeconds,
                        'score' => floatval($result['score']),
                        'thresholdScore' => ($thresholdScore === -1) ? -1 : floatval($thresholdScore),
                        'comment' => (is_null($result['comment'])) ? '' : $result['comment']
                    );

                    $data[] = $info;
                    unset($info);
                }

                $code = 0;
                $message = 'success';
            }
        }

        $returnData = array (
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        $jsonObject = $jsonHandler->encode($returnData);

        // output
        header('Content-Type: application/json');
        echo $jsonObject;
        exit();
   }
}
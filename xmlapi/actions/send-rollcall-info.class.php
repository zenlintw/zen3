<?php
/**
 * 教師啟用或關閉點名
 */

include_once(dirname(__FILE__) . '/action.class.php');

class SendRollcallInfoAction extends baseAction
{



    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession;

        /* 處理資料 */
        $inputData = file_get_contents('php://input');
        $jsonHandler = new JsonUtility();
        $postData = $jsonHandler->decode($inputData);
        $data = $postData['data'];

        $postRollID = (isset($data['rid'])) ? intval($data['rid']) : 0;
        $courseID = (isset($data['course_id'])) ? intval($data['course_id']) : 0;
        $mode = intval($data['mode']);
        $deviceStatus = (isset($data['device_status'])) ? trim(json_encode($data['device_status'])) : "";
        $creator = $sysSession->username;
        $beginTime = trim($data['begin_time']);
        $endTime = trim($data['end_time']);

        // 回傳的預設值
        $rollType = '';
        $rollID = 0;
        $code = 2;
        $message = 'fail';
        $rollcallRecord = array();

        if ($courseID > 10000000) {
            $rollcallHandler = new rollcall();

            if ($postRollID === 0) {
                // 開始點名，新建立點名
                $rollID = $rollcallHandler->startRoll($courseID, $creator, $mode, $deviceStatus, $beginTime);
                if ($rollID !== 0) {
                    $code = 0;
                    $message = 'success';
                    $rollcallHandler->studentRecordBuild($courseID, $rollID);
                    $rollcallRecord = $rollcallHandler->getRollRecord($courseID, $rollID);
                    $rollType = 'new';
                } else {
                    $code = 3;
                    $message = 'fail';
                }

            } else {
                // 結束點名的
                $rollcallHandler->closeRoll($courseID, $postRollID, $endTime);
                $rollType = 'close';
                $code = 0;
                $message = 'success';
            }
        }

        $responseObject = array(
            'code' => $code,            // 0:建立新點名成功, 2:課程編號錯誤, 3:建立新點名失敗
            'message' => $message,
            'data' => array(
                'rid' => $rollID,
                'type' => $rollType,
                'student_total' => COUNT($rollcallRecord),
                'rollcall_record' => $rollcallRecord
            )
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
<?php
/**
 * 取得單一學生點名紀錄歷程
 */

include_once(dirname(__FILE__) . '/action.class.php');

class GetRollcallRecordStudentListAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        // 參數處理
        $courseID = intval($_REQUEST['cid']);
        $rollcallRecord = array();

        if ($courseID !== 0) {
            $rollcallHandler = new rollcall();
            $rollcallRecord = $rollcallHandler->getRollRecord($courseID, 0, $sysSession->username);
            $code = 0;
            $message = 'success';
        } else {
            $code = 2;
            $message = 'fail';
        }

        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'list' => $rollcallRecord
            )
        );

        $jsonUtility = new JsonUtility();
        $jsonEncode = $jsonUtility->encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
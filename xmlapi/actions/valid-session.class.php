<?php
/**
 * 驗證session
 */
include_once(dirname(__FILE__).'/action.class.php');

class ValidSessionAction extends baseAction
{
    function ValidSessionAction()
    {
        parent::baseAction();
    }

    function main()
    {
        $jsonUtility = new JsonUtility();
        $inputData = file_get_contents('php://input');
        $postData = $jsonUtility->decode($inputData);

        if (!isset($postData['session_idx']) || !isset($postData['session_username'])) {
            global $sysSession;
            header('Content-Type: application/json');
            echo '{"code":0,"message":"valid ok.","data":{}}';

            $dbHandler = new DatabaseHandler();

            // 向下相容，故要先轉換格式後才能存入資料庫
            $deviceData = $dbHandler->reGenerateData($postData['deviceData']);

            if ($deviceData['device_os_token'] !== '' && $deviceData['device_uuid'] !== 'DESKTOP_DEVICE_ID') {
                $dbHandler->tokenHandler($sysSession->username, $deviceData);
            }
        } else {
            parent::checkTicket();
        }
    }
}
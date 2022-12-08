<?php
/**
 * 議題討論板文章的按讚處理
 */

include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
include_once(sysDocumentRoot.'/lib/lib_forum.php');

class BoardPushHandlerAction extends baseAction
{
    function main()
    {
        parent::checkTicket();

        global $sysSession;

        $code = 0;
        $message = 'success';
        $data = array();

        // 處理接收的資料 - Begin
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);
        $bid = intval($postData['bid']);
        $nid = trim($postData['nid']);
        $action = trim($postData['act']);

        $sid = intval(1000100000 + $sysSession->school_id);

        if ($action === 'del') {
            $firstPush = '0';
        } else {
            $firstPush = '1';
        }


        $rsPush = new forum();

        $rsPush->setPush(
            $bid,
            $nid,
            $sid,
            $sysSession->username,
            $firstPush
        );

        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
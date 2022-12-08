<?php
/**
 * 議題討論板回覆文章的留言處理
 */

include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
include_once(sysDocumentRoot.'/lib/lib_forum.php');

class BoardWhisperHandlerAction extends baseAction
{
    function reGenerateValueHandler ($data) {
        $allWhisper = array();

        if (count($data) > 0) {
            foreach ($data as $whisperID => $whisperData) {
                $whisper['wid'] = intval($whisperData['wid']);
                $whisper['sid'] = intval($whisperData['sid']);
                $whisper['board_id'] = intval($whisperData['board_id']);
                $whisper['picture'] = urlencode(base64_encode($whisperData['creator']));
                $whisper['creator'] = trim($whisperData['creator']);
                $whisper['realname'] = trim($whisperData['creator_realname']);
                $whisper['content'] = trim($whisperData['content']);
                $whisper['create_time'] = trim($whisperData['create_time']);
                $whisper['create_time_description'] = trim($whisperData['create_time_length']);
                $whisper['can_delete'] = verifyDeleteRight($whisper['creator'], $whisper['board_id']);
                $allWhisper[] = $whisper;
            }
        }

        return $allWhisper;
    }
    function main()
    {
        parent::checkTicket();

        global $sysSession;

        $code = 0;
        $message = 'success';
        $data = array();
        $content = '';

        $action = trim($_REQUEST['act']);

        if ($action === 'get') {
            $sid = intval(1000100000 + $sysSession->school_id);
            $bid = intval($_REQUEST['bid']);
            $nid = trim($_REQUEST['nid']);
        } else {
            // 處理接收的資料 - Begin
            $inputData = file_get_contents('php://input');
            $postData = JsonUtility::decode($inputData);

            $sid = intval(1000100000 + $sysSession->school_id);
            $bid = intval($postData['bid']);
            $nid = trim($postData['nid']);
            $wid = (isset($postData['wid'])) ? intval($postData['wid']) : 0;
            $content = trim($postData['content']);
        }


        $rsWhisper = new forum();

        switch ($action) {
        case 'set':
            $whisper = $rsWhisper->setWhisper(
                $sid,
                $bid,
                $nid,
                $content,
                $sysSession->username,
                $sysSession->realname,
                $sysSession->email
            );
            if ($whisper['code'] === 1) {
                $data = $whisper['data'];
            } else {
                $code = 3;
                $message = 'fail(set whisper fail)';
            }
            break;
        case 'mod':
            $whisper = $rsWhisper->modWhisper(
                $wid,
                $content,
                $sysSession->username,
                $sysSession->realname,
                $sysSession->email
            );
            if ($whisper['code'] === 1) {
                $data = $whisper['data'];
            } else {
                $code = 4;
                $message = 'fail(modify whisper fail)';
            }
            break;
        case 'del':
            $whisper = $rsWhisper->delWhisper(
                $wid,
                $sysSession->username
            );
            if ($whisper['code'] === 0) {
                $code = 5;
                $message = 'fail(delete whisper fail)';
            }

            break;
        case 'get':
            $whisperData = $rsWhisper->getWhisper(
                array(array($bid, $nid)),
                array(),
                true,
                'ASC'
            );
            $data = $this->reGenerateValueHandler($whisperData[$bid . '|' . $nid]);
            break;
        default:
            $code = 2;
            $message = 'fail(not allow action)';
            break;
        }

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
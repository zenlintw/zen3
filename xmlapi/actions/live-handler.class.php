<?php
/**
 * 直播活動增刪修
 * error code:
 *   2：沒有權限 (非該班期學生)
 *   3：動作設定錯誤
 *      課程編號設定錯誤
 *      直播編號設定錯誤
 *      直播名稱設定錯誤
 *      直播網址設定錯誤
 *      狀態設定錯誤
 *   4：資料庫寫入有誤
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/xmlapi/lib/live.php');
require_once(sysDocumentRoot . '/lang/app_server_push.php');

class LiveHandlerAction extends baseAction
{
    var $_allowAct = array('set', 'del', 'mod'),
        $_allowStatus = array('on', 'off');

    // 處理 APP_live_activity 資料
    function activityRecordHandler ($act, $data) {
        global $sysConn, $LiveActivity;

        switch ($act) {
            case 'set':
                // 新增
                $LiveActivity->newRecord($data);
                break;
            case 'del':
                // 刪除
                $LiveActivity->delRecord($data['id']);
                break;
            case 'mod':
                // 修改，目前只提供修改 name、status
                $LiveActivity->modRecord($data, $data['id']);
                break;
        }

        // 資料庫操作 error 判斷
        if ($sysConn->ErrorNo()) {
            return array(
                "status" => false,
                "error_msg"  => $sysConn->ErrorMsg()
            );
        } else {
            return array(
                "status" => true,
                "insert_id"  => $sysConn->Insert_ID()
            );
        }
    }

    // 驗證資料格式
    function dataHandler (&$data) {
        global $LiveActivity;
        $dataErrCode = 3;

        $act = $data['act'] = trim($data['act']);
        $liveId = $data['live_id'] = intval($data['live_id']);
        $courseId = $data['course_id'] = intval($data['course_id']);
        $name = $data['name'] = trim($data['name']);
        $url = $data['url'] = trim($data['url']);
        $status = $data['status'] = trim($data['status']);

        if (!in_array($act, $this->_allowAct)) {
            // 動作設定錯誤
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'act error!'));
        }

        // mod、del 直接用 live_id 取得 course_id
        if (($act === 'mod' || $act === 'del')) {
            $courseId = $data['course_id'] = $LiveActivity->getCourseIdByLiveId($liveId);
        }
        // 驗證課程編號是否正確
        if (!isset($courseId) || !($courseId > 10000000 && $courseId < 99999999)) {
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'course_id error!'));
        }

        // TODO: 分離 增刪修 的資料判斷
        if (empty($liveId) && ($act === 'mod' || $act === 'del')) {
            // 直播編號設定錯誤
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'live_id error!'));
        }

        if (empty($name) && ($act === 'set')) {
            // 直播名稱設定錯誤
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'name error!'));
        }

        if (empty($url) && ($act === 'set')) {
            // 直播網址設定錯誤
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'url error!'));
        }

        if (!in_array($status, $this->_allowStatus) && ($act === 'set' || $act === 'mod')) {
            // 狀態設定錯誤
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'status error!'));
        }
    }

    /**
     * 驗證使用者使用權限
     * @param string $username
     * @param integer $courseId
     */
    function aclCheck ($username, $courseId) {
        global $sysRoles;

        // 確認使用權限
        $aclCheck = aclCheckRole($username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $courseId);
        if (!$aclCheck) {
            $this->returnHandler(2, 'fail', array(), 403);
        }
    }
    
    /**
     * 推播處理
     *
     * @param string $courseName 課程名稱
     * @param array $channels 推播接收人員清單
     * @return boolean 直接回傳true
     **/
    function notifyHandler ($courseName, $channels) {
        global $sysSession, $MSG;
        
        $alertMessage = str_replace('%COURSE%', $courseName, $MSG['app_live_course_on_air'][$sysSession->lang]);
        $jsonHandler = new JsonUtility();
    
        $pushData = $jsonHandler->encode(
            array(
                'sender' => $sysSession->username,
                'content' => $alertMessage,
                'alert' => $alertMessage,
                'channel' => $channels,
                'alertType' => 'LIVE',
                'messageID' => 0,
                'writeInDB' => false
            )
        );
    
        require(sysDocumentRoot . '/xmlapi/push-handler.php');
        
        return true;
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession, $LiveActivity;
        $LiveActivity = new LiveActivity();

        // 從 body 取得參數
        $postData = file_get_contents('php://input');
        $inputData = JsonUtility::decode($postData);

        // 資料處理
        $this->dataHandler($inputData);

        // 權限驗證
        $this->aclCheck($sysSession->username, $inputData['course_id']);

        $recordResult = $this->activityRecordHandler($inputData['act'], array(
            'id' => $inputData['live_id'],
            'course_id' => $inputData['course_id'],
            'name' => $inputData['name'],
            'url' => $inputData['url'],
            'status' => $inputData['status'],
        ));

        if ($recordResult['status']) {
            if ($inputData['act'] === 'set') {
                $inputData['live_id'] = intval($recordResult['insert_id']);
    
                // 課程名稱
                $courseId = intval($inputData['course_id']);
                $caption = dbGetOne('`WM_term_course`', '`caption`', "`course_id` = {$courseId}");
                $cp = getCaption($caption);
                $courseName = $cp[$sysSession->lang];
                
                $channels = $LiveActivity->getLiveCourseMajor($courseId, $sysSession->username);
                $this->notifyHandler($courseName, $channels);
            }
            appSysLog(999999024, $inputData['course_id'], $inputData['live_id'], 0, 'auto', $_SERVER['PHP_SELF'], 'Do live-handler act: ' . $inputData['act'] . ' success!');
            $this->returnHandler(0, 'success', array(
                'live_id' => $inputData['live_id']
            ));
        } else {
            appSysLog(999999024, $inputData['course_id'], $inputData['live_id'] , 0, 'auto', $_SERVER['PHP_SELF'], 'Do live-handler act: "' . $inputData['act'] . '" fail! error code: "' . addslashes($recordResult['error_msg']) . '"');
            $this->returnHandler(4, 'fail');
        }
    }
}
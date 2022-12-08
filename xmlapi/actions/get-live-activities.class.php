<?php
/**
 * 取得直播列表
 * error code:
 *   2：沒有權限 (非該班期學生)
 *   3：課程編號錯誤
 *      狀態錯誤
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/xmlapi/lib/live.php');

class GetLiveActivitiesAction extends baseAction
{
    var $_allowStatus = array('all', 'on', 'off');

    /**
     * 取得直播活動列表
     * @param $courseId
     */
    function getLiveActivities ($courseId, $where, $offset, $size) {
        $activityList = array();

        $LiveActivity = new LiveActivity();
        $activities = $LiveActivity->getActivitiesByCid(
            $courseId,
            '`id`, `name`, `url`, `status`, `begin_time`, `end_time`',
            $where,
            $offset,
            $size
        );

        if ($rs = $activities['rs']) {
            while ($row = $rs->FetchRow()) {
                $activityList[] = array(
                    'live_id'=> intval($row['id']),
                    'name'=> $row['name'],
                    'url'=> $row['url'],
                    'status'=> $row['status'],
                    'begin_time'=> $row['begin_time'],
                    'end_time'=> $row['end_time']
                );
            }
        }
        return array(
            'total_size' => $activities['total_size'],
            'list' => $activityList
        );
    }

    // 資料處理及驗證
    function dataHandler (&$data) {
        $dataErrCode = 3;

        $courseId = $data['course_id'] = intval($data['course_id']);
        $status = $data['status'];

        // TODO: 目前先將 courseId = 0 設定為取全校課程，需求有變更的話再將此判斷移除
        // 驗證課程編號是否正確
        if (!isset($courseId) ||
                !($courseId === 0 || ($courseId > 10000000 && $courseId < 99999999))) {
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'course_id error!'));
        }

        // 驗證狀態是否正確
        if (isset($status) && !in_array($status, $this->_allowStatus)) {
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'status error!'));
        }

        $data['offset'] = isset($data['offset']) ? intval($data['offset']) : 0;
        $data['size'] = isset($data['size']) ? intval($data['size']) : 15;
    }

    /**
     * 驗證使用者使用權限
     * @param $username
     */
    function aclCheck ($username, $courseId) {
        global $sysRoles;

        // 確認使用權限
        $aclCheck = aclCheckRole(
            $username,
            $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] | $sysRoles['student'] | $sysRoles['auditor'],
            $courseId
        );
        if (!$aclCheck) {
            $this->returnHandler(2, 'fail', array(), 403);
        }
    }

    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession, $sysRoles;
        $courseIds = array();

        // 從 GET 取得參數
        $inputData = $_GET;

        // 資料處理
        $this->dataHandler($inputData);

        // 取得直播活動列表
        if ($inputData['course_id'] === 0) {
            // 課程編號為 0 時，撈取該學員所有課程
            $courseIds = dbGetCol(
                '`WM_term_major`',
                '`course_id`',
                sprintf(
                    '`username` = "%s" AND `role`&%d ',
                    $sysSession->username,
                    $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] | $sysRoles['student'] | $sysRoles['auditor']
                ),
                ADODB_FETCH_ASSOC
            );
        } else {
            // 權限驗證
            $this->aclCheck($sysSession->username, $inputData['course_id']);
            $courseIds = array($inputData['course_id']);
        }

        if (count($courseIds) > 0) {
            // 指定狀態
            switch ($inputData['status']) {
                case 'on':
                    $where = '`status` = "on"';
                    break;
                case 'off':
                    $where = '`status` = "off"';
                    break;
                default:
                    $where = '';
            }
            $activities = $this->getLiveActivities($courseIds, $where, $inputData['offset'], $inputData['size']);
        } else {
            $activities = array(
                'total_size' => 0,
                'list' => array()
            );
        }

        $this->returnHandler(0, 'success', array(
            'total_size' => $activities['total_size'],
            'list' => $activities['list']
        ));
    }
}
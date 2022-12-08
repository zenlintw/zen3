<?php
/**
 * 取得直播狀態
 * error code:
 *   2：沒有權限 (非該班期學生或老師)
 *   3：資料格式錯誤
 *   4：sql 撈取錯誤
 */
include_once(dirname(__FILE__).'/action.class.php');
require_once(sysDocumentRoot . '/xmlapi/lib/live.php');

class GetLiveStatusAction extends baseAction
{

    /**
     * 取得正在直播的個數
     * @param $courseId
     */
    function getLivingActivitiesNum ($courseIds) {
        if (count($courseIds) > 1) {
            $where = sprintf(" `course_id` in (%s) ", implode(", ", $courseIds));
        } else {
            $where = sprintf(" `course_id` = %d ", intval($courseIds[0]));
        }

        $liveCount = dbGetOne(
            'APP_live_activity',
            'COUNT(`id`)',
            sprintf('status = "on" AND %s', $where)
        );

        return ($liveCount!== false) ? intval($liveCount) : -1;
    }

    // 資料處理及驗證
    function dataHandler (&$data) {
        $dataErrCode = 3;

        $courseId = $data['course_id'] = intval($data['course_id']);

        // TODO: 目前先將 courseId = 0 設定為取全校課程，需求有變更的話再將此判斷移除
        // 驗證課程編號是否正確
        if (!isset($courseId) ||
                !($courseId === 0 || ($courseId > 10000000 && $courseId < 99999999))) {
            $this->returnHandler($dataErrCode, 'fail', array('errMsg' => 'course_id error!'));
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

        // 從 GET 取得參數
        $inputData = $_GET;

        // 資料處理
        $this->dataHandler($inputData);

        // 權限驗證
        $this->aclCheck($sysSession->username, $inputData['course_id']);

        // 取得課程編號
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
            $courseIds[] = array($inputData['course_id']);
        }

        // 取得當前正在直播的個數
        $isLiveNum = $this->getLivingActivitiesNum($courseIds);
        if ($isLiveNum === -1) {
            $this->returnHandler(4, 'fail', array('errMsg' => 'sql error!'));
        }

        $this->returnHandler(0, 'success', array(
            'has_living' => $isLiveNum > 0
        ));
    }
}
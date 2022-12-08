<?php
/**
 * 刪除點名紀錄
 */

include_once(dirname(__FILE__) . '/action.class.php');

class DeleteRollcallInfoAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession, $sysRoles;

        $code = 0;
        $message = 'success';

        /* 處理資料 */
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);
        // 目前尚未對資料作加密
        $aesCode = intval($postData['aesCode']);
        $data = $postData['data'];

        $rollId = intval($data['rid']);
        $course_id = dbGetOne('`APP_rollcall_base`', '`course_id`', sprintf('`rid` = %d', $rollId));

        // 確認使用者權限
        $teach = aclCheckRole($sysSession->username, $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'], $course_id);
        if ($teach === '0' || $teach === false) {
            $code = 2;
            $message = 'fail';
        }
        $isRollExist = dbGetOne(
            'APP_rollcall_base',
            'count(`rid`)',
            sprintf('`rid` = %d AND `course_id` = %d', $rollId, $course_id)
        );
        if (intval($isRollExist) > 0 && $code === 0) {
            dbDel(
                'APP_rollcall_base',
                sprintf('`rid` = %d AND `course_id` = %d', $rollId, $course_id)
            );
        } else {
            $code = 3;
            $message = 'fail';
        }

        $responseObject = array(
            'code' => $code,
            'message' => $message
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
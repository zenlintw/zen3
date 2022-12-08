<?php
/**
 * 取得當下點名狀態
 */

include_once(dirname(__FILE__) . '/action.class.php');

class GetRollcallStatusAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession;

        // 預設值
        $code = 0;
        $message = 'success';
        $data = array();

        $username = mysql_real_escape_string($sysSession->username);

        // 處理參數資料
        $get_rid = intval($_GET['rid']);
        $get_cid = intval($_GET['cid']);

        if ($get_rid === 0) {
            // 學生沒有rid，所以會透過order by取得目前課程最新的rid
            $where = " `course_id` = {$get_cid} ";
        } else {
            $where = " `course_id` = {$get_cid} AND `rid` = {$get_rid} ";
        }
        // 取得進行中點名資訊
        list($cur_rid, $cur_mode) = dbGetStSr(
            "`APP_rollcall_base`",
            "`rid`, `mode`",
            $where . " AND `end_time` = '0000-00-00 00:00:00' ORDER BY `rid` DESC"
        );

        if ($cur_rid !== false && $cur_rid > 0) {
            $cur_rid = intval($cur_rid);
            // 使用者目前點名狀態
            $sign_status = dbGetOne(
                "`APP_rollcall_record`",
                "`rollcall_status`",
                sprintf("`rid` = %d AND `username` = '%s'", $cur_rid, $username)
            );

            // 取得總點名人數、已點名人數(1~8)、待唱名人數(10)
            list($signed_count, $notice_count) = dbGetStSr(
                "`APP_rollcall_record`",
                "SUM(CASE 
                    WHEN `rollcall_status` > 0 AND `rollcall_status` < 9 AND `rollcall_status` != 2
                    THEN 1 ELSE 0
                END) AS `signed`, 
                SUM(CASE 
                    WHEN `rollcall_status` = 10 
                    THEN 1 ELSE 0
                END) AS `ten`",
                sprintf("`rid` = %d", $cur_rid)
            );

            $data = array(
                "sign_total" => intval($signed_count),
                "sign_status" => intval($sign_status),
                "roll_mode" => intval($cur_mode),
                "roll_id" => intval($cur_rid),
                "student_notice" => intval($notice_count)
            );
        } else {
            // 沒有進行中點名，或課程編號錯誤
            $code = 2;
            $message = 'fail';
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
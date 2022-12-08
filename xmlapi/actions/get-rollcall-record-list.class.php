<?php
/**
 * 取得banner列表.
 */

include_once(dirname(__FILE__) . '/action.class.php');

class GetRollcallRecordListAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysConn;

        $allData = array();
        $sizeWhere = '';

        // 參數處理
        $courseId = intval($_REQUEST['cid']);
        if (isset($_REQUEST['offset']) && isset($_REQUEST['size'])) {
            $sizeWhere = sprintf(
                " LIMIT %d, %d ",
                intval($_REQUEST['offset']),
                intval($_REQUEST['size'])
            );
        }

        if ($courseId !== 0) {
            // 取得資料
            $rs = dbGetStMr('`APP_rollcall_base`', 'SQL_CALC_FOUND_ROWS *', "`course_id` = {$courseId} ORDER BY `create_time` DESC" . $sizeWhere);
            if ($rs) {
                $totalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));
                $i = 0;
                while ($row = $rs->FetchRow()) {
                    $i = $i + 1;
                    $suchRid = intval($row['rid']);
                    // 取得該次點名的全部紀錄
                    $suchRidRecords = dbGetAll('`APP_rollcall_record`', 'SQL_CALC_FOUND_ROWS *', "`rid` = {$suchRid} GROUP BY trim(`username`)");
                    $suchRidTotalUsers = COUNT($suchRidRecords);
                    // 取得該次點名的已到紀錄
                    $suchRidRecordSigned = dbGetAll('`APP_rollcall_record`', 'SQL_CALC_FOUND_ROWS *', "`rid` = {$suchRid} AND `rollcall_status` IN (1,3,4) GROUP BY trim(`username`)");
                    $suchRidSignedUsers = COUNT($suchRidRecordSigned);

                    $data['serial'] = $i;
                    $data['roll_id'] = $suchRid;
                    $data['roll_create_time'] = $row['create_time'];
                    $data['roll_begin_time'] = $row['begin_time'];
                    $data['roll_end_time'] = $row['end_time'];
                    $data['total'] = intval($suchRidTotalUsers);
                    $data['signed'] = intval($suchRidSignedUsers);
                    $data['unsigned'] = intval($suchRidTotalUsers) - intval($suchRidSignedUsers);

                    $allData[] = $data;
                }

                $code = 0;
                $message = 'success';
            } else {
                $code = 2;
                $message = 'fail';
                $totalSize = 0;
            }
        } else {
            $code = 2;
            $message = 'fail';
            $totalSize = 0;
        }

        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'total_size' => $totalSize,
                'list' => $allData
            )
        );

        $jsonHandler = new JsonUtility();
        $jsonEncode = $jsonHandler->encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
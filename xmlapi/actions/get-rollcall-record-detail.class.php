<?php
/**
 * 取得點名詳情列表.
 */

include_once(dirname(__FILE__) . '/action.class.php');

class GetRollcallRecordDetailAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysConn;

        // 變數設定
        $datas = array();
        $sizeWhere = '';
        $find = '';
        $orderBy = '';

        // 參數處理
        $rid = intval($_GET['rid']);
        $keyword = (isset($_GET['keyword'])) ? trim(charset2Utf8($_GET['keyword'])) : "";
        $keyword = trim($keyword);
        $status = trim($_GET['status']);
        if (isset($_GET['offset']) && isset($_GET['size'])) {
            $sizeWhere = sprintf(
                "LIMIT %d, %d",
                intval($_GET['offset']),
                intval($_GET['size'])
            );
        }

        if ($rid !== 0) {
            if (strlen($keyword) > 0) {
                $find .= sprintf(
                    " AND (
                        LOCATE('%s', u.`first_name`) OR
                        LOCATE('%s', u.`last_name`) OR
                        LOCATE('%s', concat_ws('', u.`last_name`, u.`first_name`)) OR
                        LOCATE('%s', concat_ws(' ', u.`first_name`, u.`last_name`)) OR
                        LOCATE('%s', m.`username`) OR
                        LOCATE('%s', r.`memo`)
                    ) ",
                    mysql_real_escape_string($keyword),
                    mysql_real_escape_string($keyword),
                    mysql_real_escape_string($keyword),
                    mysql_real_escape_string($keyword),
                    mysql_real_escape_string($keyword),
                    mysql_real_escape_string($keyword)
                );
            }

            /**
             * all >> 全部
             * signed >> 1:已點名|3:遲到|4:早退
             * unsigned >> 0:點名預塞|2:未點名|4:早退|5:病假|6:公假|7:事假|8:喪假|9:裝置重複|10:學生提示簽到
             */
            if ($status === 'signed') {
                $find .= " AND rollcall_status IN (1,3,4) GROUP BY trim(r.username)";
            } else if ($status === 'unsigned') {
                $orderBy = ' GROUP BY trim(r.username) ORDER BY `isTen` DESC ';
                $find .= " AND (r.rollcall_status IS NULL OR r.rollcall_status IN (0, 2, 5, 6, 7, 8, 9, 10)) ";
            }

            // 取得資料
            $fields = "SQL_CALC_FOUND_ROWS *, r.username as username,
                (CASE
                    WHEN `rollcall_status` = 10 THEN 1 
                    ELSE 0
                END) AS `isTen`";
            $tables = "APP_rollcall_record AS r
                       LEFT JOIN WM_user_account AS u ON u.username = trim(r.username)
                       LEFT JOIN WM_user_picture as p ON p.username = r.username";
            $where = "r.rid = {$rid} " . $find . $orderBy . $sizeWhere;
            $rs = dbGetStMr($tables, $fields, $where, ADODB_FETCH_ASSOC);

            if ($rs) {
                while ($row = $rs->FetchRow()) {
                    // 取出學生圖片
                    if (!is_null($row['picture']) && !is_null($row['picture'])) {
                        $imgSrc = 'data:image/jpeg;base64,' . base64_encode($row['picture']);
                    } else {
                        // 沒有設定就取預設圖片
                        $imgSrc = "";
                    }

                    $data['username'] = $row['username'];
                    $data['realname'] = checkRealname($row['first_name'], $row['last_name']);
                    $data['user_picture'] = $imgSrc;
                    $data['sign_status'] = (isset($row['rollcall_status'])) ? intval($row['rollcall_status']) : 0;
                    $data['memo'] = $row['memo'];
                    $data['sign_datetime'] = $row['rollcall_time'];
                    $data['update_datetime'] = $row['modify_time'];

                    $datas[] = $data;
                }

                $code = 0;
                $message = 'success';
                $totalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));
            } else {
                $code = 2;
                $message = 'fail';
                $totalSize = 0;
            }
        } else {
            $code = 3;
            $message = 'fail';
            $totalSize = 0;
        }

        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'total_size' => $totalSize,
                'list' => $datas
            )
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
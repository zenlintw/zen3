<?php
/**
 * 取得banner列表.
 */

include_once(dirname(__FILE__) . '/action.class.php');

class SendRollcallRecordAction extends baseAction
{
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();
        global $sysSession, $sysRoles;

        $code = 0;
        $message = 'success';
        $sameDevice = false;
        $processing = false;
        $today = date('Y-m-d H:i:s');
        $modifier = mysql_real_escape_string($sysSession->username);

        /* 資料處理 */
        $inputData = file_get_contents('php://input');
        $postData = JsonUtility::decode($inputData);
        $courseId = intval($postData['cid']);
        $data = $postData['data'];

        if (is_array($data) && count($data) > 0) {
            // 有無老師權限
            if (aclCheckRole($sysSession->username, $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'], $courseId)) {
                $isTeacher = true;
            } else {
                $isTeacher = false;
            }

            // TODO: 點名權限判斷，或資料加密
            foreach ($data as $val) {
                $rid = intval($val['rid']);
                $username = mysql_real_escape_string(trim($val['username']));
                $status = intval($val['status']);
                $memo = mysql_real_escape_string(trim($val['memo']));
                $device_ident = mysql_real_escape_string(trim($val['device_ident']));
                $device_status = mysql_real_escape_string(trim(json_encode($val['device_status'])));

                // 如果狀態為 10，允許不給予 rid(學生藍芽取不到)
                if ($status === 10) {
                    // 取當前課程最新點名作為 rid
                    list($rid, $end_time) = dbGetStSr(
                        '`APP_rollcall_base`',
                        '`rid`, `end_time`',
                        sprintf("`course_id` = %d ORDER BY `rid` DESC", $courseId)
                    );
                    $rid = intval($rid);
                } else {
                    list($currCid, $end_time) = dbGetStSr(
                        '`APP_rollcall_base`',
                        '`course_id`, `end_time`',
                        "`rid` = {$rid}"
                    );
                    // 跳過非當前課程的點名紀錄
                    if ($isTeacher === false && intval($currCid) !== intval($courseId)) {
                        continue;
                    }
                }

                // 點名是否進行中
                $processing = ($end_time === '0000-00-00 00:00:00') ? true : false;
                // 點名進行中或有老師權限才可修改
                if ($processing === true || $isTeacher === true) {
                    //機器碼已存在 點名狀態改為9
                    $is_set = dbGetOne(
                        '`APP_rollcall_record`',
                        'count(*)',
                        "`device_ident` = '{$device_ident}' AND `rid` = {$rid} AND `username` != '{$username}'"
                    );

                    // 自動點名時，才會有 $device_ident，老師修改時新增的資料不做裝置判斷
                    if ($device_ident !== '' && $is_set > 0 && $isTeacher === false) {
                        $status = 9;
                        $memo = '使用他人裝置點名';
                        $sameDevice = true;
                    }

                    // 寫入資料
                    list($rollcall_time, $record_status) = dbGetStSr(
                        '`APP_rollcall_record`',
                        '`rollcall_time`, `rollcall_status`',
                        "`rid` = {$rid} AND `username` = '{$username}' ORDER BY `rollcall_time` DESC"
                    );
                    // 點名進行中且學生已點名(status = 1)時，不作寫入的動作
                    if ($processing === true && intval($record_status) === 1) {
                        continue;
                    } else if ($rollcall_time) {
                        $table = '`APP_rollcall_record`';
                        $where = "`rid` = {$rid} AND `username` = '{$username}'";
                        if ($rollcall_time === '0000-00-00 00:00:00') {
                            // 該節課第一次點名會預塞資料，所以如果是0000，則要異動點名時間
                            if ($isTeacher) {
                                $setValue = "`rollcall_status` = {$status}, `rollcall_time` = '{$today}', `memo` = '{$memo}', `modifier` = '{$modifier}', `modify_time` = '{$today}', `device_ident` = '{$device_ident}'";
                            } else {
                                $setValue = "`rollcall_status` = {$status}, `rollcall_time` = '{$today}', `memo` = '{$memo}', `modifier` = '{$modifier}', `modify_time` = '{$today}', `device_ident` = '{$device_ident}', `device_status` = '{$device_status}'";
                            }
                        } else {
                            // 重複點名只要修改變更時間
                            if ($isTeacher) {
                                $setValue = "`rollcall_status` = {$status}, `memo` = '{$memo}', `modifier` = '{$modifier}', `modify_time` = '{$today}', `device_ident` = '{$device_ident}'";
                            } else {
                                $setValue = "`rollcall_status` = {$status}, `memo` = '{$memo}', `modifier` = '{$modifier}', `modify_time` = '{$today}', `device_ident` = '{$device_ident}', `device_status` = '{$device_status}'";
                            }
                        }
                        //修改
                        dbSet($table, $setValue, $where);
                    } else {
                        //新增
                        dbNew(
                            '`APP_rollcall_record`',
                            '`rid`, `username`, `rollcall_time`, `rollcall_status`, `memo`, `device_ident`, `device_status`',
                            "{$rid}, '{$username}', '{$today}', {$status}, '{$memo}', '{$device_ident}', '{$device_status}'"
                        );
                    }
                }
            }
        } else {
            $code = 2;
            $message = 'fail';
        }

        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'sameDevice' => $sameDevice,
                'processing' => $processing
            )
        );

        $jsonEncode = JsonUtility::encode($responseObject);

        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
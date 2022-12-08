<?php
/**
 * 負責點名有關的處理
 **/

class rollcall {

    /**
     * 將節次對應表從資料庫裡面撈出來 (先保留，目前不需要轉節次)
     * @return array 對應表
     **/
    function makeSessionTable () {
        $sessionTable = array();

        $table = '`CO_ilms_timetable`';
        $field = '*';
        $where = '1';

        $RS = dbGetStMr($table, $field, $where);
        if ($RS) {
            while ($sessionData = $RS->FetchRow()) {
                $begin = $sessionData['time_begin'];
                $end = $sessionData['time_end'];

                $sessionTable[$sessionData['seq_no']] = array(
                    'b' => $begin,
                    'e' => $end
                );
            }
        }
        return $sessionTable;
    }

    /**
     * 將時間轉成節次 (先保留，目前不需要轉節次)
     * @param array $sessionTable 節次對應表
     * @param string $time (yyyy-mm-dd hh:ii:ss)
     * @return Number 節次
     **/
    function timeToSession ($sessionTable, $time) {
        $time = str_replace('T', ' ', $time);

        $timeSplit = explode(' ', $time);
        $YMD = $timeSplit[0];
        $recordTime = datetimeToSeconds($time);

        for ($i = 0; $i < COUNT($sessionTable); $i++) {
            $sessionBtime = datetimeToSeconds($YMD . ' ' . $sessionTable[$i]['b']);
            $sessionEtime = datetimeToSeconds($YMD . ' ' . $sessionTable[$i]['e']);

            if ($recordTime >= $sessionBtime && $recordTime <= $sessionEtime) {
                return $i;
                break;
            }
        }

        return 100;
    }

    /**
     * 建立一筆新的點名
     * @param integer $courseID     課程編號
     * @param string $creator       建立者
     * @param integer $mode         1:Nearby | 2:Beacon
     * @param string $deviceStatus  裝置狀態
     * @param string $beginTime     點名啟動時間
     * @return integer 點名編號
     **/
    function startRoll ($courseID, $creator, $mode, $deviceStatus, $beginTime) {
        global $sysConn;

        $courseID = intval($courseID);
        $creator = mysql_real_escape_string($creator);
        $mode = intval($mode);
        $deviceStatus = mysql_real_escape_string($deviceStatus);
        $createTime = date('Y-m-d H:i:s');
        $beginTime = mysql_real_escape_string($beginTime);

        $table = '`APP_rollcall_base`';

        // 先將原先還沒正常結束的點名關閉
        $setValue = "`end_time` = '{$createTime}'";
        $setWhere = "`course_id` = {$courseID} AND `end_time` = '0000-00-00 00:00:00'";
        dbSet($table, $setValue, $setWhere);

        // 再重先建立一筆新的點名
        $fields = '`course_id`, `creator`, `mode`, `device_status`, `create_time`, `begin_time`';
        $values = "{$courseID}, '{$creator}', '{$mode}', '{$deviceStatus}', '{$createTime}', '{$beginTime}'";
        dbNew($table, $fields, $values);

        return intval($sysConn->Insert_ID());
    }

    /**
     * 結束點名
     * @param integer $courseID     課程編號
     * @param integer $rollID       點名編號
     * @param string $endTime       結束時間
     *
     **/
    function closeRoll ($courseID, $rollID, $endTime) {
        $courseID = intval($courseID);
        $rollID = intval($rollID);
        $endTime = mysql_real_escape_string($endTime);

        $table = '`APP_rollcall_base`';
        $values = "`end_time` = '{$endTime}'";
        $where = "`course_id` = {$courseID} AND `rid` = {$rollID}";

        dbSet($table, $values, $where);
    }

    function dropRoll ($courseID, $rollID) {
        $courseID = intval($courseID);
        $rollID = intval($rollID);

        // 刪除已經建立的學生簽到紀錄
        $rollRecordTable = '`APP_rollcall_record`';
        $rollRecordWhere = "`rid` = {$rollID}";
        dbDel($rollRecordTable, $rollRecordWhere);
        // 刪除已經建立的點名紀錄
        $rollBaseTable = '`APP_rollcall_base`';
        $rollBaseWhere = "`course_id` = {$courseID} AND `rid` = {$rollID}";
        dbDel($rollBaseTable, $rollBaseWhere);
    }

    /**
     * 啟動點名時，先將人員點名結果寫入(預設未到)
     *
     * @param integer $courseID 課程編號
     * @param integer $rollID   點名編號
     * @param integer $status   點名狀態
     **/
    function studentRecordBuild ($courseID, $rollID, $status = 2) {
        global $sysRoles, $sysConn;

        $courseID = intval($courseID);
        $rollID = intval($rollID);

        $majorTable = '`WM_term_major`';
        $majorField = '`username`';
        $majorWhere = "`course_id` = {$courseID} AND `role` & {$sysRoles['student']}";
        $majorRS = dbGetStMr($majorTable, $majorField, $majorWhere);

        if ($majorRS) {
            $rollRecordTable = '`APP_rollcall_record`';
            $rollRecordFields = '`rid`, `username`, `rollcall_status`';
            $values = array();

            while ($student = $majorRS->FetchRow()) {
                $username = mysql_real_escape_string(trim($student['username']));
                $values[] = "({$rollID}, '{$username}', {$status})";
            }

            if (COUNT($values) > 0) {
                // 將VALUE串起來，這樣只要新增一次就好
                $rollRecordValues = (COUNT($values) === 1) ? $values[0] : implode(',', $values);
                chkSchoolId($rollRecordTable);
                $buildSQL = "INSERT INTO {$rollRecordTable} ({$rollRecordFields}) VALUES {$rollRecordValues}";
                $sysConn->Execute($buildSQL);
            }
        }
    }

    /**
     * 取得單一點名紀錄的學生紀錄
     * @param integer $courseID 課程編號
     * @param integer $rollID   點名編號
     * @param string $username  學生帳號
     * @return array 紀錄
     **/
    function getRollRecord($courseID, $rollID = 0, $username = '') {
        $courseID = intval($courseID);
        $rollID = intval($rollID);
        $mysqlUsername = $username;

        $table = '`APP_rollcall_record`';
        $fields = '*';

        $recordData = array();

        if ($rollID > 0) {
            // 教師取得單一點名紀錄的學員紀錄
            $where = "`rid` = {$rollID}";
        } else if ($username !== '') {
            // 學生取得自己的課程點名紀錄
            $table = "`APP_rollcall_record` AS arr LEFT JOIN `APP_rollcall_base` AS arb ON arr.`rid` = arb.`rid` AND arb.`course_id` = {$courseID}";
            $fields = 'arb.`create_time`, arr.*';
            $where = "arr.`username` = '{$mysqlUsername}' AND arb.`begin_time` IS NOT NULL ORDER BY `create_time`";
        } else {
            return $recordData;
        }

        $RS = dbGetStMr($table, $fields, $where);
        if ($RS) {
            while ($record = $RS->FetchRow()) {
                $data['roll_id'] = intval($record['rid']);
                $data['roll_time'] = $record['rollcall_time'];
		        $data['day'] = ($record['rollcall_time'] === '0000-00-00 00:00:00') ? substr($record['create_time'], 0, 10) : substr($record['rollcall_time'], 0, 10);
		        $data['time'] = substr($record['rollcall_time'], 11, 8);
                if ($username === '') {
                    // 教師取得單一課程的學生紀錄需要有帳號
                    $data['username'] = $record['username'];
                }
                $data['sign_status'] = intval($record['rollcall_status']);

                $recordData[] = $data;
            }
        }

        return $recordData;
    }
}

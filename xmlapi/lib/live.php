<?php
/**
 * Class LiveActivity
 * TODO: 新增紀錄(newRecord) data 資料驗證
 */
class LiveActivity {
    var $_ACTIVITY_TABLE = 'APP_live_activity';

    function newRecord ($data) {
        dbNew(
            sprintf('`%s`', $this->_ACTIVITY_TABLE),
            '`course_id`, `name`, `url`, `status`, `begin_time`',
            sprintf(
                '%d, "%s", "%s", "%s", NOW()',
                intval($data['course_id']),
                mysql_real_escape_string($data['name']),
                mysql_real_escape_string($data['url']),
                mysql_real_escape_string($data['status'])
            )
        );
    }

    function delRecord($id) {
        dbDel(
            sprintf('`%s`', $this->_ACTIVITY_TABLE),
            sprintf('`id` = %d', intval($id))
        );
    }

    function modRecord ($data, $id) {
        // 修改目前只提供修改 name、status
        $allowColumns = array('name', 'status');
        $setValues = array();

        foreach($data AS $column => $value) {
            $column = trim($column);
            if (!in_array($column, $allowColumns)) {
                // 不是允許的欄位就過濾掉
                continue;
            }
            switch ($column) {
                case 'status':
                    if ($data['status'] === 'off') {
                        $setValues[] = '`end_time` = NOW()';
                    }
                    break;
                default:
            }
            if (gettype($value) === 'integer') {
                $setValues[] = sprintf(
                    '`%s` = %d',
                    $column,
                    mysql_real_escape_string(intval($value))
                );
            } else {
                $setValues[] = sprintf(
                    '`%s` = "%s"',
                    $column,
                    mysql_real_escape_string(trim($value))
                );
            }
        }
        if (count($setValues) > 0) {
            dbSet(
                sprintf('`%s`', $this->_ACTIVITY_TABLE),
                implode(', ', $setValues),
                sprintf('`id` = %d', intval($id))
            );
        }
    }

    function getActivities($fields, $where, $offset, $size) {
        $fields = (empty($fields)) ? "*" : $fields;

        global $sysConn;
        $orderBy = ' ORDER BY `id` DESC ';
        $limit = '';

        if (isset($offset) && isset($size)) {
            $limit = sprintf(' Limit %d, %d ', $offset, $size);
        }

        $rs = dbGetStMr(
            'APP_live_activity',
            sprintf('SQL_CALC_FOUND_ROWS %s', $fields),
            (!empty($where) ? $where : " 1 = 1 ") .
            $orderBy . $limit
        );

        $totalSize = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

        return array(
            'total_size' => $totalSize,
            'rs' => $rs
        );
    }

    function getActivitiesByCid($courseIds, $fields, $where, $offset, $size) {
        if (!empty($where)) {
            $conditions[] = $where;
        }

        if (count($courseIds) === 1) {
            $conditions[] = sprintf("`course_id` = %d", $courseIds[0]);
        } else if (count($courseIds) > 1) {
            $conditions[] = sprintf("`course_id` in (%s)", implode(", ", $courseIds));
        } else {
            // 不限制課程就直接使用 getActivities
            return false;
        }

        return $this->getActivities($fields, implode(" AND ", $conditions), $offset, $size);
    }

    function getCourseLastActivity($fields, $courseId) {
        return dbGetRow(
            sprintf('`%s`', $this->_ACTIVITY_TABLE),
            $fields,
            sprintf('`course_id` = %d', intval($courseId)) .
                ' ORDER BY `id` DESC '
        );
    }

    // 取得課程編號
    function getCourseIdByLiveId ($liveId) {
        return dbGetOne(
            sprintf('`%s`', $this->_ACTIVITY_TABLE),
            'course_id',
            sprintf('`id` = %d', intval($liveId))
        );
    }
    
    /**
     * 欲推播，故需取得課程成員(除了直播建立者外)
     *
     * @param integer $courseId 課程編號
     * @param string $creator 直播建立者帳號
     * @return array 帳號串
     **/
    function getLiveCourseMajor ($courseId, $creator) {
        $courseID = intval($courseId);
        $creator = mysql_real_escape_string($creator);
        $username = array();
    
        $RS = dbGetStMr('`WM_term_major`', '`username`', "`course_id` = {$courseID} AND `username` != '{$creator}' AND `role` > 0");
        if ($RS) {
            while ($member = $RS->FetchRow()) {
                $username[] = $member['username'];
            }
        }
    
        return $username;
    }
}

// TODO: 預留，未來實作筆記
class LiveNote {}
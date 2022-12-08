<?php
class APPLog {
    function addLog ($type, $username, $courseId, $qtiId, $qtiType, $logTime, $log) {
        global $sysConn;

        switch ($type) {
            case 'database':
                $tableName = 'APP_error_log';
                $sysConn->Execute('USE '.sysDBschool);
                $sql = sprintf("SHOW TABLES WHERE Tables_in_%s='%s'", sysDBschool, $tableName);
                if (!$sysConn->GetOne($sql)) {
                    // 建立新表
                    if (is_file(sysDocumentRoot. '/app_install/' . $tableName . '.sql')) {
                        $sql = file_get_contents(sysDocumentRoot. '/app_install/' . $tableName . '.sql');
                        $sysConn->Execute($sql);
                    }
                }

                $username = mysql_real_escape_string($username);
                $courseId = intval($courseId);
                $qtiId = intval($qtiId);
                $qtiType = mysql_real_escape_string($qtiType);
                $logTime = mysql_real_escape_string($logTime);
                $log = mysql_real_escape_string($log);
                $fields = '`username`, `course_id`, `qti_id`, `type`, `log_time`, `log`';
                $values = "'{$username}', {$courseId}, {$qtiId}, '{$qtiType}', '{$logTime}', '{$log}'";
                dbNew($tableName, $fields, $values);
                break;
            case 'file':

            default:
        }
    }
}
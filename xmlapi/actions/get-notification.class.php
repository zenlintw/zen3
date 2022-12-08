<?php
/**
 * 取得推播訊息
 */

include_once(dirname(__FILE__).'/action.class.php');

class GetNotificationAction extends baseAction
{
    function getUnitName ($type, $messageID) {
        if (strstr($messageID, '#')) {
            $messageID = explode('#', $messageID);
            $boardID = intval($messageID[0]);
        } else {
            $messageID = intval($messageID);
        }

        switch ($type) {
        case 'BULLETIN':
            $unitCaption = dbGetOne('`WM_term_course`', '`caption`', "`bulletin` = {$boardID}");
            break;
        case 'FORUM':
            $unitCaption = dbGetOne('`WM_term_course`', '`caption`', "`discuss` = {$boardID}");
            break;
        case 'EXAM':
        case 'HOMEWORK':
        case 'QUESTIONNAIRE':
            $table = '`WM_qti_' . strtolower($type) . '_test` AS QT LEFT JOIN `WM_term_course` AS TC ON QT.`course_id` = TC.`course_id`';
            $field = 'TC.`caption`';
            $where = "QT.`exam_id` = {$messageID}";
            $unitCaption = dbGetOne($table, $field, $where);
            break;
        case 'GRADE':
            $table = '`WM_grade_list` AS GT LEFT JOIN `WM_term_course` AS TC ON GT.`course_id` = TC.`course_id`';
            $field = '`caption`';
            $where = "GT.`grade_id` = {$messageID}";
            $unitCaption = dbGetOne($table, $field, $where);
            break;
        default:
            $unitCaption = '';
        }
        if ($unitCaption !== '') {
            $captions = getCaption($unitCaption);
            return $captions['Big5'];
        } else {
            return '';
        }
    }
    function main()
    {
        parent::checkTicket();

        global $sysSession, $sysConn, $MSG;

        if (isset($_GET['size'])) {
            $size = max(1, intval($_GET['size']));
        } else {
            $size = 10;
        }

        if (isset($_GET['offset'])) {
            $offset = max(0, intval($_GET['offset']));
        } else {
            $offset = 0;
        }

        $username = mysql_real_escape_string($sysSession->username);
        $diffTime = 86400*30;
        $allData = null;
        $total = 0;

        // 取得推播訊息
        $table = '`APP_notification_message`';
        $fields = 'SQL_CALC_FOUND_ROWS *';
        $where = "`message_type` != 'NEWS' AND `receiver` = '{$username}' AND `message_id` != '' AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`send_time`) <= {$diffTime} GROUP BY `message_id` ORDER BY `send_time` DESC LIMIT {$offset}, {$size}";
        $RS = dbGetStMr($table, $fields, $where);

        $messageFromOtherTable = array('NEWS', 'BULLETIN', 'FORUM');

        if ($RS) {
            // SQL 執行成功
            $total = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));
            while (!$RS->EOF) {
                // 訊息編號
                $data['msg_id'] = str_replace('%', '*SUN*', $RS->fields['google_message']);

                if (in_array($RS->fields['message_type'], $messageFromOtherTable) && $RS->fields['message_id'] !== '') {
                    $messageID = explode('#', $RS->fields['message_id']);
                    $boardID = intval($messageID[0]);
                    $nodeID = mysql_real_escape_string($messageID[1]);

                    list($subject, $content) = dbGetStSr('WM_bbs_posts', '`subject`,`content`',  "`board_id` = {$boardID} AND `node` = '{$nodeID}'", ADODB_FETCH_NUM);
                    $unitCaption = '';
                    // 訊息標題
                    switch ($RS->fields['message_type']) {
                        case 'NEWS':
                            $tag = $MSG['app_push_title_tag_news'][$sysSession->lang];
                            break;
                        case 'BULLETIN':
                            $tag = $MSG['app_push_title_tag_bulletin'][$sysSession->lang];
                            $unitCaption = dbGetOne('`WM_term_course`', '`caption`', "`bulletin` = {$boardID}");
                            break;
                        case 'FORUM':
                            $tag = $MSG['app_push_title_tag_forum'][$sysSession->lang];
                            $unitCaption = dbGetOne('`WM_term_course`', '`caption`', "`discuss` = {$boardID}");
                            break;
                    }
                    $data['subject'] = $tag . $subject;
                    // 訊息內容
                    $data['content'] = chgImgSrcRelative2Absolute($content);
                } else {
                    // 訊息標題
                    $data['subject'] = $RS->fields['title'];
                    // 訊息內容
                    $data['content'] = nl2br($RS->fields['content']);
                }
                $data['message_type'] = $RS->fields['message_type'];
                $data['unit'] = $this->getUnitName($RS->fields['message_type'], $RS->fields['message_id']);

                // 已讀未讀
                if (!empty($RS->fields['user_read_time'])) {
                    // 若是read、reply、forward表示已讀
                    $data['read'] = 1;
                } else {
                    // 若是空值表示未讀
                    $data['read'] = 0;
                }
                // 推播時間
                $data['create_datetime'] = $RS->fields['send_time'];

                $allData[] = $data;

                $RS->MoveNext();
            }

            $code = 0;
            $message = 'success';
        } else {
            // SQL 執行失敗
            $code = 2;
            $message = 'SQL Error';
        }

        
        $responseObject = array(
            'code' => $code,
            'message' => $message,
            'data' => array(
                'total_size' => $total,
                'list' => $allData
            )
        );

        $jsonEncode = JsonUtility::encode($responseObject);
        
        // output
        header('Content-Type: application/json');
        echo $jsonEncode;
        exit();
    }
}
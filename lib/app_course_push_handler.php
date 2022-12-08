<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/app_exam.php');
require_once(sysDocumentRoot . '/xmlapi/config.php');
require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');

$_QTITypes = array('exam', 'homework', 'questionnaire');

/**
 * 驗證可推播型態
 *
 * @param String $type 型態
 * @return Boolean 是不是允許型態
 **/
function validPushType ($type) {
    $types = array('exam', 'homework', 'questionnaire', 'bulletin'); // 測驗、作業、問卷、課程公告

    return (in_array($type, $types));
}

/**
 * 驗證是否為課程管理者
 *
 * @return Boolean 是否有教師、講師、助教的權限
 **/
function validPushPrivilege () {
    global $sysSession, $sysRoles;

    if ($sysSession->env === 'academic') {
        return aclCheckRole($sysSession->username, $sysRoles['manager'] | $sysRoles['administrator'] | $sysRoles['root'], $sysSession->school_id);
    } else {
        return aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id);
    }
}

/**
 * 取得公告資訊
 *
 * @param string $id 文章編號
 * @return array 公告資訊
 **/
function getBulletinInfo ($id) {
    $data = array();

    $bulletID = explode('#', $id);
    $boardID = intval($bulletID[0]);
    $nodeID = mysql_real_escape_string($bulletID[1]);

    $RS = dbGetStSr('WM_bbs_posts', '*', "`board_id` = {$boardID} AND `node` = '{$nodeID}'");
    if ($RS) {
        $data = array(
            'subject' => $RS['subject'],
            'content' => $RS['content'],
            'post_time' => $RS['pt']
        );
    }

    return $data;
}

/**
 * 根據試卷ACL取得需填答的帳號清單
 *
 * @param String $type QTI型態
 * @param Integer $id 試卷編號
 * @return String 帳號清單 (只有課程非開放型問卷的QTI或是校務開放型問卷)
 **/
function getQTIMember ($type, $id) {
    global $sysSession, $sysConn, $sysRoles;

    $total = array();
    $role = array();
    $functionID = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200);
    $typeID = $functionID[$type];
    $unitID = ($sysSession->env === 'teach') ? intval($sysSession->course_id) : intval($sysSession->school_id);
    $id = intval($id);

    $subQuery = "(SELECT `acl_id` FROM `WM_acl_list` WHERE `unit_id` = {$unitID} AND `instance` = {$id} AND `function_id` = {$typeID} AND `permission` = 'enable')";
    $table = '`WM_acl_member`';
    $field = '`member`';
    $where = '`acl_id` IN ' . $subQuery ;   // 一份試卷可能不只設定一個ACL，所以要用IN
    $RS = dbGetStMr($table, $field, $where);

    if ($RS && $sysConn->Affected_Rows() > 0) {
        while ($member = $RS->FetchRow()) {
            if (strstr($member['member'], '#')) {
                // 課程ACL為身份設定
                $thisRole = str_replace('#', '', $member['member']);
                $role[] = $sysRoles[$thisRole];
            } else if ($member['member'] === 'guest') {
                if ($sysSession->env === 'teach') {
                    // 課程開放型問卷給課程內正式生與旁聽生的帳號
                    $role = array($sysRoles['student'], $sysRoles['auditor']);
                } else {
                    // 校務開放型問卷給目前有註冊推播的帳號
                    $total = getAPPChannels();
                }
            } else {
                // 課程ACL為帳號設定
                $total[] = $member['member'];
            }
        }
    } else {
        // 未設定則預設正式生
        $role[] = $sysRoles['student'];
    }

    if ($sysSession->env === 'teach') {
        // 辦公室環境才需要處理身份
        $table = '`WM_term_major`';
        $field = '`username`';

        // 若ACL只有單獨帳號設定，沒有身份，則條件為空字串
        $roleCondition = (count($role) > 0) ? " AND `role` & " . implode('|', $role): '';
        $where = "`course_id` = {$unitID} AND `role` > 0" . $roleCondition;

        $RS = dbGetStMr($table, $field, $where);
        if ($RS && $sysConn->Affected_Rows() > 0) {
            while ($individual = $RS->FetchRow()) {
                if (!in_array($individual['username'], $total)) {
                    $total[] = $individual['username'];
                }
            }
        }
    }

    return implode(',', $total);
}

/**
 * 取得課程成員
 *
 * @return String 課程成員帳號
 **/
function getCourseMember () {
    global $sysSession;

    $courseID = intval($sysSession->course_id);
    $username = array();

    $RS = dbGetStMr('`WM_term_major`', '`username`', "`course_id` = {$courseID} AND `role` > 0");
    if ($RS) {
        while ($member = $RS->FetchRow()) {
            $username[] = $member['username'];
        }
    }

    return implode(',', $username);
}

/**
 * 取得所有訂閱channel的帳號
 *
 * @return array channel陣列
 **/
function getAPPChannels () {
    $username = array();

    $table = '`APP_notification_device`';
    $field = '`username`';
    $where = '`device_token_abandon` = 0 GROUP BY `username`';

    $RS = dbGetStMr($table, $field, $where);
    if ($RS) {
        while ($member = $RS->FetchRow()) {
            $username[] = $member['username'];
        }
    }

    return $username;
}

/**
 * 比照問卷列表方式製作開放型問卷的連結 (Copy from /teach/exam/exam_maintain.php)
 *
 * @param integer $unitID 學校編號
 * @param integer $instance 試卷編號
 * @return string 開放型問卷的連結
 **/
function genForGuestLink($unitID, $instance) {
    global $_SERVER;

    $salt = rand(100000, 999999);
    $url  = sprintf('/Q/%u/%u/%u/1/', $unitID, $instance, $salt);
    return WM_SERVER_HOST . $url . md5($_SERVER['HTTP_HOST'] . $url);
}

//------------------------------------ Main Begin ------------------------------------//
// QTI推播用ajax傳進來，所以要用$pushData接收；課程公告用require方式傳進來，所以已經有$pushData
if (!isset($pushData)) {
    $pushData = file_get_contents('php://input');
}
$pushObject = JsonUtility::decode($pushData);
$examID = intval($pushObject['id']);
$type = trim($pushObject['type']);

// 不是管理者，不允許進行推播
if (!validPushPrivilege()) {
    exit();
}

if (in_array($type, $_QTITypes)) { // QTI推播
    $ids = trim($pushObject['id']);

    if (strstr($ids, ',')) {
        // 透過左側工具列選取多個試卷發佈的時候
        $idArray = explode(',', $ids);
    } else {
        // 建立試卷時或是透過左側工具列選取一個試卷發佈的時候
        $idArray[] = $ids;
    }

    $table = 'WM_qti_' . $type . '_test';
    $fields = '*';
    for ($i = 0; $i < count($idArray); $i++) {
        $id = $idArray[$i];
        $where = "`exam_id` = {$id}";

        $QTIInfo = dbGetStSr($table, $fields, $where);

        if (!$QTIInfo) {
            // 取資料異常就跳過此筆推播
            continue;
        } else {
            $members = getQTIMember($type, $id);

            // 有人員清單才進行推播
            if ($members !== '') {
                $title = getCaption($QTIInfo['title']);
                $title = $title['Big5'];

                // 如果是管理者環境，轉成開放型問卷
                $type = ($sysSession->env === 'academic') ? 'public' : $type;
                // 預設是個人帳號
                $role = 'individual';

                $beginTime = ($QTIInfo['begin_time'] === '0000-00-00 00:00:00') ? $MSG['exam_begin_time_from_now_on'][$sysSession->lang] : $QTIInfo['begin_time'];
                $closeTime = ($QTIInfo['close_time'] === '9999-12-31 00:00:00') ? $MSG['exam_end_time_forever'][$sysSession->lang] : $QTIInfo['close_time'];

                $announceType = array(
                    'never' => $MSG['app_never_announce'][$sysSession->lang],
                    'now' => $MSG['app_finish_announce'][$sysSession->lang],
                    'close_time' => $MSG['app_close_announce'][$sysSession->lang],
                    'user_define' => $MSG['app_user_announce'][$sysSession->lang]
                );
                $announce = $announceType[$QTIInfo['announce_type']];
                $percentage = intval($QTIInfo['percent']);

                switch ($type) {
                    case 'exam': // 課程測驗
                        $alert = $MSG['exam_publish_push_type_exam'][$sysSession->lang] . ' - ' . $title . $MSG['exam_publish_push_message'][$sysSession->lang] . "\n";
                        $content = $MSG['exam_publish_content_course_title'][$sysSession->lang] . $sysSession->course_name . "\n" .
                            $MSG['exam_publish_content_exam_title'][$sysSession->lang] . $title . "\n" .
                            $MSG['exam_publish_content_qti_percentage'][$sysSession->lang] . $percentage . "%\n" .
                            $MSG['exam_publish_content_period'][$sysSession->lang] . $beginTime . ' ~ ' . $closeTime . "\n" .
                            $MSG['exam_publish_content_answer_publish'][$sysSession->lang] . $announce;
                        break;
                    case 'homework': // 課程作業
                        $alert = $MSG['exam_publish_push_type_homework'][$sysSession->lang] . ' - ' . $title . $MSG['exam_publish_push_message'][$sysSession->lang];
                        $content = $MSG['exam_publish_content_course_title'][$sysSession->lang] . $sysSession->course_name . "\n" .
                            $MSG['exam_publish_content_homework_title'][$sysSession->lang] . $title . "\n" .
                            $MSG['exam_publish_content_qti_percentage'][$sysSession->lang] . $percentage . "%\n" .
                            $MSG['exam_publish_content_period'][$sysSession->lang] . $beginTime . ' ~ ' . $closeTime . "\n" .
                            $MSG['exam_publish_content_answer_publish'][$sysSession->lang] . $announce;
                        break;
                    case 'questionnaire': // 課程問卷
                        $alert = $MSG['exam_publish_push_type_questionnaire'][$sysSession->lang] . ' - ' . $title . $MSG['exam_publish_push_message'][$sysSession->lang];
                        $content = $MSG['exam_publish_content_course_title'][$sysSession->lang] . $sysSession->course_name . "\n" .
                            $MSG['exam_publish_content_questionnaire_title'][$sysSession->lang] . $title . "\n" .
                            $MSG['exam_publish_content_period'][$sysSession->lang] . $beginTime . ' ~ ' . $closeTime . "\n" .
                            $MSG['exam_publish_content_result_publish'][$sysSession->lang] . $announce;
                        break;
                    case 'public': // 開放型校務問卷
                        $alert = $MSG['exam_publish_push_type_questionnaire_public'][$sysSession->lang] . ' - ' . $title . $MSG['exam_publish_push_message'][$sysSession->lang];
                        $content = $MSG['exam_publish_content_course_title'][$sysSession->lang] . $sysSession->course_name . "\n" .
                            $MSG['exam_publish_content_questionnaire_title'][$sysSession->lang] . $title . "\n" .
                            $MSG['exam_publish_content_period'][$sysSession->lang] . $beginTime . ' ~ ' . $closeTime . "\n" .
                            $MSG['exam_publish_content_result_publish'][$sysSession->lang] . $announce . "\n" .
                            $MSG['public_questionnaire_link_copy_tips'][$sysSession->lang] . "\n" .
                            genForGuestLink($QTIInfo['course_id'], $QTIInfo['exam_id']);
                        $type = 'questionnaire';
                        $role = 'public-questionnaire-channel';
                        break;
                }

                $data = array(
                    'sender' => $sysSession->username,
                    'alert' => $alert,
                    'content' => $content,
                    'role' => $role,
                    'account' => $members,
                    'type' => $type,
                    'messageID' => $id
                );

                $coursePushData  = JsonUtility::encode($data);
                require_once(sysDocumentRoot . '/lib/app_course_server_push_ajax.php');
            } else {
                continue;
            }
        }
    }
} else if ($type === 'bulletin') { // 課程公告推播
    $members = getCourseMember();
    if ($members !== '') {
        $bulletinInfo = getBulletinInfo($pushObject['id']);

        $alert = $MSG['bulletin_notice'][$sysSession->lang] . ' - ' . $sysSession->course_name . $MSG['bulletin_push_message'][$sysSession->lang];
//        $content = $MSG['exam_publish_content_course_title'][$sysSession->lang] . $sysSession->course_name . "\n" .
//            $MSG['bulletin_title'][$sysSession->lang] . $bulletinInfo['subject'] . "\n".
//            $MSG['bulletin_content'][$sysSession->lang] . $bulletinInfo['content'] . "\n";
        $content = $MSG['app_bulletin_date_time'][$sysSession->lang] . $bulletinInfo['post_time'];

        $data = array(
            'sender' => $sysSession->username,
            'alert' => $alert,
            'content' => $content,
            'role' => 'individual',
            'account' => $members,
            'type' => $type,
            'messageID' => $pushObject['id']
        );

        $coursePushData  = JsonUtility::encode($data);
        require_once(sysDocumentRoot . '/lib/app_course_server_push_ajax.php');
    }
} else {
    // 保留後續擴充
    exit();
}

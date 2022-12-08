<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/app_server_push.php');
require_once(sysDocumentRoot . '/xmlapi/config.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/push-config.php');
require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');
require_once(sysDocumentRoot . '/xmlapi/lib/lang.php');
require_once(sysDocumentRoot . '/xmlapi/lib/common.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/NotificationDatabase.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Apns.php');
require_once(sysDocumentRoot . '/xmlapi/lib/NotificationPush/Gcm.php');

$sysSession->cur_func = 999999003;
if (isset($coursePushData)) {
    // QTI推播(透過require)
    $pushCourseObject = JsonUtility::decode($coursePushData);
} else {
    // 寄信與點名(透過AJAX)
    $pushCourseObject = JsonUtility::decode(file_get_contents('php://input'));
}
if (trim($pushCourseObject['content']) !== '' && (trim($pushCourseObject['role']) !== '' || trim($pushCourseObject['account']) !== '')) {
    // 身分
    $role = trim($pushCourseObject['role']);
    $type = isset($pushCourseObject['type']) ? $pushCourseObject['type'] : '';

    $permitRoles = array('auditor', 'student', 'assistant', 'instructor', 'teacher', 'all', 'individual');
    if (!in_array($role, $permitRoles)) {
        $result = 'app_push_message_fail';
    }

    switch ($role) {
        case 'all':
            $roleCondition = ' AND A.role & ' . ($sysRoles['student'] | $sysRoles['auditor'] | $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher']);
            break;
        case 'student':
        case 'auditor':
        case 'assistant':
        case 'instructor':
        case 'teacher':
            $roleCondition = ' AND A.role & ' . $sysRoles[$role];
            break;
        case 'individual':
        case 'public-questionnaire-channel':
            $account = trim($pushCourseObject['account']);
            break;
    }

    $sqls = str_replace('%COURSE_ID%', $sysSession->course_id, $Sqls['get_course_all_student']);

    if ($role === 'public-questionnaire-channel') {
        // 從開放型校務問卷來的身份
        if (strstr($account, ',') === '') {
            $channels[] = $account;
        } else {
            $channels = explode(',', $account);
        }
    } else if ($role === 'individual') {
        // 個別帳號
        if (strstr($account, ',') === '') {
            $accountList = '"' . $account . '"';
        } else {
            $accountList = '"' . str_replace(',', '","', $account) . '"';
        }

        $RS = dbGetStMr('WM_term_major', 'username', "course_id = {$sysSession->course_id} AND username IN ({$accountList})");

        if ($RS) {
            while (!$RS->EOF) {
                $channels[] = $RS->fields['username'];
                $RS->MoveNext();
            }
        }
    } else {
        // 非個別帳號，透過sql抓取帳號
        $sqls = $sqls . $roleCondition;

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $RS = $sysConn->Execute($sqls);
        if ($sysConn->ErrorNo() > 0) {
            wmSysLog($sysSession->cur_func, $sysSession->course_id , '', 1, 'auto', $_SERVER['PHP_SELF'], $sysConn->ErrorMsg());
            // 成員資料取得失敗
            $result = 'app_push_message_sql_error';
        }
        while (!$RS->EOF) {
            $channels[] = $RS->fields['username'];
            $RS->MoveNext();
        }
    }

    // 推播訊息內容
    if (!isset($pushCourseObject['alert'])) {
        // 沒有傳alert資料，就用預設文字
        $alertMessage = '[' . $sysSession->course_name . ']' . $MSG['app_push_message_default'][$sysSession->lang];
    } else {
        // 有傳alert的話，就用alert去處理
        $alertMessage = $pushCourseObject['alert'];
    }

    if (count($channels) > 0) {
        // APP 訊息推播 - Begin
        $pushData = JsonUtility::encode(
            array(
                'sender' => isset($pushCourseObject['sender']) ? $pushCourseObject['sender'] : 'SYSTEM_PUSH',
                'content' => $pushCourseObject['content'],
                'alert' => $alertMessage,
                'channel' => $channels,
                'alertType' => $type,
                'messageID' => $pushCourseObject['messageID']
            )
        );

        require(sysDocumentRoot . '/xmlapi/push-handler.php');
        unset($channels);

        if ($type !== 'grade') {
            if (isset($response)) {
                $response = JsonUtility::decode($response);
                $result = 'app_push_message_success';
                $msg = 'Success: IOS=' . $response['data']['IOS'] . ', ANDROID=' . $response['data']['ANDROID'] . ', JPUSH=' . $response['data']['JPUSH'];
                wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'teacher', $_SERVER['PHP_SELF'], $msg, $sysSession->username);
            } else {
                $result = 'app_push_message_fail';
            }
        }
        // APP 訊息推播 - End
    } else {
        // 沒有所選身分的成員
        $result = 'app_push_message_no_such_role';
    }
} else {
    $result = 'app_push_message_fail';
}
// 拿掉echo，有必要的時候再處理
/*
if ($type !== 'grade') {
    // make json
    $jsonObj = array(
        'code' => 0,
        'message' => $result
    );

     $jsonEncode = JsonUtility::encode($jsonObj);

    // output
     header('Content-Type: application/json');
     echo $jsonEncode;
     exit();
}
*/
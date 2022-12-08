<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/app_server_push.php');
require_once(sysDocumentRoot . '/xmlapi/config.php');
require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');

/**
 * 驗證是否為課程管理者
 *
 * @return Boolean 是否有教師、講師、助教的權限
 **/
function validPushPrivilege () {
    global $sysSession, $sysRoles;

    return aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id);
}

/**
 * 取得成績資訊
 *
 * @param integer $courseID 課程編號
 * @param integer $gradeID 成績編號
 * @return array 成績資訊
 **/
function getGradeInfo ($courseID, $gradeID) {
    $gradeInfo = array();
    $gradeSources = array(
        1 => 'homework',
        2 => 'exam',
        3 => 'questionnaire',
        9 => 'custom'
    );

    $courseID = intval($courseID);
    $gradeID = intval($gradeID);
    $RS = dbGetStSr('WM_grade_list', '`title`, `source`, `property`, `percent`', "`course_id` = {$courseID} AND `grade_id` = {$gradeID}");
    if ($RS) {
        $title = getCaption($RS['title']);
        $title = $title['Big5'];
        $source = intval($RS['source']);

        $gradeInfo = array(
            'title' => $title,
            'source' => $gradeSources[$source],
            'property' => $RS['property'],
            'percent' => $RS['percent']
        );
    }

    return $gradeInfo;
}

/**
 * 取得試卷及格分數
 *
 * @param integer $id
 * @return float 及格分數
 **/
function getThresholdScore($id) {
    $id = intval($id);

    $table = 'WM_qti_exam_test';
    $field = '`content`';
    $where = "`exam_id` = {$id}";

    $content = dbGetOne($table, $field, $where);

    $matches = array();
    $thresholdScore = 0;
    if (preg_match('/\bthreshold_score="([^"]*)"/', $content, $matches)) {
        $thresholdScore = ($matches[1] == '') ? 0 : floatval($matches[1]);
    }

    return $thresholdScore;
}
/**
 * 取得課程成員
 *
 * @param integer $id 成績編號
 * @return array 成績資訊
 **/
function getGradeItems ($id) {
    $gradeItems = array();

    $id = intval($id);
    $RS = dbGetStMr('`WM_grade_item`', '`username`, `score`', "`grade_id` = {$id}");
    if ($RS) {
        while($gradeItem = $RS->FetchRow())
        $gradeItems[$gradeItem['username']] = $gradeItem['score'];
    }

    return $gradeItems;
}

// 不是課程管理者，不允許進行推播
if (!isset($_POST['courseID']) && !validPushPrivilege()) {
    exit();
}
if (isset($_POST['courseID']) && isset($_POST['gradeID'])) {
    $courseID = intval($_POST['courseID']);
    $gradeID = intval($_POST['gradeID']);
    $sysSession->course_id = $courseID;
} else {
    $pushObject = JsonUtility::decode(file_get_contents('php://input'));
    $courseID = intval($pushObject['courseID']);
    $gradeID = intval($pushObject['gradeID']);
}

$gradeInfo = getGradeInfo($courseID, $gradeID);

$lang = (!isset($sysSession) || $sysSession->lang == '') ? 'Big5' : $sysSession->lang;

$courseCaption = dbGetOne('`WM_term_course`', '`caption`', "`course_id` = {$courseID}");
$courseCaption = getCaption($courseCaption);
$courseName = $courseCaption['Big5'];

$alert = $MSG['app_grade_push_alert_message'][$lang] . ' - ' . $gradeInfo['title'] . $MSG['grade_publish_push_message'][$lang];
$originalContent = $MSG['grade_publish_content_course_title'][$lang] . $courseName . "\n" .
           $MSG['grade_publish_content_grade_title'][$lang] . $gradeInfo['title'] . "\n" .
           $MSG['grade_publish_content_percentage'][$lang] . $gradeInfo['percent'] . "%\n" .
           $MSG['grade_publish_content_score'][$lang] . '%%SCORE%%';
if ($gradeInfo['source'] === 'exam') {
    $thresholdScore = getThresholdScore($gradeInfo['property']);
    $content .= "\n" . $MSG['grade_publish_content_threshold_score'][$lang] . $thresholdScore;
}

$gradeItems = getGradeItems($gradeID);

// 因為每人的成績不一樣，所以個別推播
foreach($gradeItems as $username => $score) {
    $pushContent = str_replace('%%SCORE%%', $score, $originalContent);

    $data = array(
        'sender' => ($sysSession->username === 'guest') ? 'SYSTEM_PUSH' : $sysSession->username,
        'alert' => $alert,
        'content' => $pushContent,
        'role' => 'individual',
        'account' => $username,
        'type' => 'grade',
        'messageID' => $gradeID
    );

    $coursePushData  = JsonUtility::encode($data);
    require(sysDocumentRoot . '/lib/app_course_server_push_ajax.php');

    unset($data);
    unset($coursePushData);
    $data = array();
}

// make json
$jsonObj = array(
    'code' => 0,
    'message' => 'app_push_message_success'
);

$jsonEncode = JsonUtility::encode($jsonObj);

// output
header('Content-Type: application/json');
echo $jsonEncode;
exit();
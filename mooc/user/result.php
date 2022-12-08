<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/myteaching.php');
require_once(sysDocumentRoot . '/lang/activities.php');
require_once(sysDocumentRoot . '/lang/irs.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

if ($sysSession->username == 'guest') {
    header('Location: /mooc/index.php');
    exit;
}

$role = $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'];
if (!aclCheckRole($sysSession->username, $role, $_POST['course_id'])){
    header('Location: /mooc/index.php');
    exit;
}

$course_id = $_POST['course_id'];
$exam_type = $_POST['exam_type'];
$exam_id   = $_POST['exam_id'];

$forGuestQuests = array();
$forGuest = '';
if ($exam_type == 'questionnaire') $forGuestQuests = aclGetForGuestQuest($course_id);

if (in_array($exam_id, $forGuestQuests)) {
    $forGuest = 1;
}

$rsCourse = new course();
$courseData = $rsCourse->getCourseById($course_id);
$enc_course_id = sysNewEncode($course_id);
$courseData['enc_course_id'] = $enc_course_id;

$sql = "select title from `WM_qti_" . $exam_type . "_test` where exam_id={$exam_id}";
$stitle = $sysConn->GetOne($sql);

$cpTitle = unserialize($stitle);
$title   = $cpTitle[$sysSession->lang];

// 計算分數
$sql = "select count(*) as submit,ROUND(MAX(score),0) as max,ROUND(MIN(score),0) as min,ROUND(AVG(score),0) as avg from `WM_qti_" . $exam_type . "_result` where exam_id={$exam_id} and status!='break'";
$score_data = $sysConn->GetRow($sql);

// 取得題目 資訊
define('API_QTI_which',$exam_type);
require_once(sysDocumentRoot . '/xmlapi/lib/qti.php');
$qtiLib = new Qti();
$qtiDetail = new QtiResult();
$qtiDetail->init($exam_type);
$qtiDetail->getQtiDetail($exam_id);
$getAnswer = ($exam_type === 'exam') ? true : false;
$itemInfo = $qtiLib->transformer($qtiDetail->qtiData['dom'], $qtiDetail->qtiData['ctx'], $exam_type, $getAnswer);

$role     = $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'];
$rsCourse = new course();
$courses = $rsCourse->getUserCoursesDetail($sysSession->username, $role, false, $search, array());

// 輪替色系
$color = array('#815cb4', '#3aabdd', '#278f7f', '#ff7d13', '#e8483f', '#e3a729', '#42b6b5', '#81a41c', '#5e92fc', '#cf4aab');


$smarty->assign('courseData', $courseData);
$smarty->assign('title', $title);
$smarty->assign('type', $exam_type);
$smarty->assign('exam_id', $exam_id);
$smarty->assign('item', $itemInfo);
$smarty->assign('items', count($itemInfo));
$smarty->assign('score_data', $score_data);
$smarty->assign('color', $color);
$smarty->assign('forGuest', $forGuest);
$smarty->display('user/result.tpl');
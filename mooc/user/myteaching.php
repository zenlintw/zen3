<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');

if ($sysSession->username == 'guest') {
    header('Location: /mooc/index.php');
    exit;
}

$course_type      = 0;
$validCourseTypes = array(
    'open',
    'spoc',
    'mooc',
    'micro',
    'direct',
    'ebook',
    'pack'
);
if (in_array(strtolower($_POST['course_type']), $validCourseTypes)) {
    $course_type = intval(array_search(strtolower($_POST['course_type']), $validCourseTypes)) + 1;
}
$smarty->assign('CourseType', (($course_type == 0) ? 'all' : strtolower($_POST['course_type'])));
$smarty->assign('keyword', (($keyword == '') ? '' : $_POST['keyword']));
$smarty->assign('CourseTypeValue', $course_type);

$optionCourseStatus = array(
    '%' => '全部狀態',
    '5' => '準備中或審核中',
    '1' => $MSG['cs_state_open_a'][$sysSession->lang],
//    '0' => '下架',
);
$smarty->assign('optionCourseStatus', $optionCourseStatus);
$smarty->assign('courseStatus', ((isset($_POST['course_status']) === false || $_POST['course_status'] === '%') ? '%' : htmlspecialchars($_POST['course_status'])));


$role     = $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'];
$rsCourse = new course();
$courses = $rsCourse->getUserCoursesDetail($sysSession->username, $role, false, array(), array());

// 所有課程類別名稱
$allCourseTypeName = dbGetAssoc('WP_div_master', 'value_id, value_name', "type_id='course_type'");

$allSchoolName = dbGetAssoc('WM_school', 'school_id, school_name', 'school_id != 10001');
foreach ($courses as $scid => $courseData) {
    if (empty($courses[$scid]['role']) || ($courses[$scid]['status'] == 0)) {
        continue;
    }
    $isTeach = ($courses[$scid]['role'] & $sysRoles['teacher']);
    $isTeach = ($isTeach || ($courses[$scid]['role'] & $sysRoles['instructor']));
    $isTeach = ($isTeach || ($courses[$scid]['role'] & $sysRoles['assistant']));
    if (($stas == 5) && !$isTeach) { // 判斷如果課程狀態為準備中則只限教師才可看到
        continue;
    }
    
    $stBegin = $courses[$scid]['st_begin'];
    $now     = date('Y-m-d H:i:s');
    $stEnd   = $courses[$scid]['st_end'];
    $enBegin = $courses[$scid]['en_begin'];
    if (($now >= $stBegin and $now <= $stEnd) or ($stBegin === null and $stEnd === null) or ($stBegin === null and $now <= $stEnd) or ($now >= $stBegin and $stEnd === null)) {
        $isClassing = true;
    } else {
        $isClassing = false;
    }
    if ($stBegin === null) {
        $stBegin = $MSG['rightnow'][$sysSession->lang];
    }
    if ($stEnd === null) {
        $stEnd = '無限期';
    }
    $classPeriod = $stBegin . '~' . $stEnd;
    
    $courses[$scid]['school_name']      = $allSchoolName[$courses[$scid]['from_school_id']];
    $courses[$scid]['course_type_text'] = $allCourseTypeName[$courses[$scid]['course_type']];
    $courses[$scid]['classPeriod']      = $classPeriod;
    $courses[$scid]['classmateNum']     = intval(dbGetOne('WM_term_major', 'count(*)', sprintf("course_id=%d and role&16127", $courses[$scid]['course_id'])));
    
    $Ccpr = dbGetRow(sysDBprefix . $courseData['from_school_id'] . ".CO_course_publish_review", "*", "course_id={$courseData['from_course_id']} ORDER BY serial_no DESC");
    if (count($Ccpr) === 0 && in_array($courseData['status'], array(
        1,
        2,
        3,
        4
    ))) { //若該課程已上架,但都沒有審核記錄,則新增一筆審核通過的資料
        dbNew(sysDBprefix . $courseData['from_school_id'] . ".CO_course_publish_review", "course_id,review_status,username,create_time,review_time", "{$courseData['from_course_id']},1,'{$sysSession->username}',NOW(),NOW()");
        $Ccpr = dbGetRow(sysDBprefix . $courseData['from_school_id'] . ".CO_course_publish_review", "*", "course_id={$courseData['from_course_id']} ORDER BY serial_no DESC");
    }
    
    switch ($Ccpr['review_status']) {
        default:
        case '1':
            if ($courseData['status'] == '5') {
                $Ccpr['review_text'] = '<div class="review_text" style="margin-bottom: 13px;">課程狀態：<div class="label label-success">準備中</div></div>';
            } else {
                $Ccpr['review_text'] = '';
            }
            break;
        case 'p':
            $Ccpr['review_text'] = '<div class="review_text" style="margin-bottom: 13px;">審核狀態：<div class="label label-primary">加盟機關審核中</div></div>';
            break;
        case 'p2':
            $Ccpr['review_text'] = '<div class="review_text" style="margin-bottom: 13px;">審核狀態：<div class="label label-primary">Portal機關審核中</div></div>';
            break;
        case '0':
            $Ccpr['review_text'] = '<div class="review_text" style="margin-bottom: 13px;">審核狀態：<div class="label label-danger">不通過</div></div>';
            break;
    }
    $courses[$scid]['Ccpr'] = $Ccpr;
}

$smarty->assign('courseList', $courses);
$smarty->display('user/myteaching.tpl');
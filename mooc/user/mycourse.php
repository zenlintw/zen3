<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

// 回到我的課程時，$sysSession->course_id 要重設為學校id
if ($sysSession->course_id > 10000000) {
    $sysSession->course_id = $sysSession->school_id;
    $sysSession->restore();
}

require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/academic/course/course_lib.php');

if ($sysSession->username == 'guest') {
    header('Location: /mooc/index.php');
    exit;
}

if(strlen($_GET['url'])>0){
    preg_match('/\d+$/', urlencode($_GET['url']), $m);      //取得ebook_id
    $token = getNLPIToken();        //取得國資圖Token
    $gotoEbookURL = getNLPIUrl($token, $m[0]);      //取得電子書URL
    header('Location:'.$gotoEbookURL);
    exit;
}

$role=$sysRoles['auditor']|$sysRoles['student'];
$rsCourse = new course();
$courses = $rsCourse->getUserCoursesDetail($sysSession->username, $role, false, $_POST['query'], array());

foreach((array)$courses as $scid => $courseData) {
	if (empty($courses[$scid]['role']) || ($courses[$scid]['status'] == 0) || ($courses[$scid]['status'] == 5)) {
        continue;
    }

    $stBegin = $courses[$scid]['st_begin'];
    $now = date('Y-m-d H:i:s');
    $stEnd = $courses[$scid]['st_end'];
    $enBegin = $courses[$scid]['en_begin'];
    if (($now >= $stBegin and $now <= $stEnd) or ($stBegin === null and $stEnd === null) or
        ($stBegin === null and $now <= $stEnd) or ($now >= $stBegin and $stEnd === null)) {
        $isClassing = true;
    } else {
        $isClassing = false;
    }
    if ($stBegin === null && $stEnd !== null) {
        $stBegin = $MSG['rightnow'][$sysSession->lang];
    }
    if ($stBegin === null && $stEnd === null) {
        // $classPeriod = $MSG['notset'][$sysSession->lang];
        $stBegin = $MSG['rightnow'][$sysSession->lang];
        $stEnd = $MSG['no_ending_date'][$sysSession->lang];
        $classPeriod = $stBegin . '~' . $stEnd;
    } else {
        $classPeriod = $stBegin . '~' . $stEnd;
    }

    // $courses[$scid]['school_name'] = $allSchoolName[$courses[$scid]['from_school_id']];
    // $courses[$scid]['classPeriod'] = $classPeriod;
    // $CategoryGroups = array();
    // getCategoryGroupName($courses[$scid]['course_id'],$CategoryGroups);
    // if (count($CategoryGroups)) {
    //     krsort($CategoryGroups);
    //     $groupNames = dbGetAssoc('WM_term_course','course_id,caption',sprintf("course_id in (%s)", implode(',',$CategoryGroups)));
    //     for($i=0, $size=count($CategoryGroups); $i<$size; $i++) {
    //         $cp = unserialize($groupNames[$CategoryGroups[$i]]);
    //         $CategoryGroups[$i] = $cp[$sysSession->lang];
    //     }
    //     $courses[$scid]['category_name'] = implode('&nbsp;&gt;&nbsp;',$CategoryGroups);
    // }else{
    //     $courses[$scid]['category_name'] = '';
    // }

}

$smarty->assign('csrfToken', md5($sysSession->idx));
$smarty->assign('courseList',$courses);
$smarty->display('user/mycourse.tpl');
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/mycourse.php');

$rsCourse = new course();

if (!isset($_POST['groupId'])) {
    // 判斷有無使用搜尋系統
    /* if (count($_POST) >= 1 && !isset($_POST['group_id'])) {
        // 取所有課程：課程編號、課程名稱、課程狀態、老師群姓名、老師大頭照
        $course = $rsCourse->getAllCourse('', '', $_POST['keyword']);
    } else {
        // 取報名中的課程：課程編號、課程名稱、課程狀態、老師群姓名、老師大頭照
        $course = $rsCourse->getAllCourse('signing');
    } */
} else {
    $smarty->assign('group_id', $_POST['groupId']);
    $courseGroup = '';
}

// 取課程群組組成JS陣列
$courseGroup = $rsCourse->getHtmlCourseGroup();

// $smarty->assign('courseIng', $course);
$smarty->assign('htmlCourseGroup', $courseGroup);
$smarty->assign('keyword', htmlspecialchars($_POST['keyword'],ENT_QUOTES));

$smarty->display('explorer.tpl');
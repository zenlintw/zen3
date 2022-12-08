<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/mooc_teach.php');
require_once(sysDocumentRoot . '/lang/mycourse.php');
$sysSession->env='teach';
// 是否有老師或開課者的身份
if (!aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) && !aclCheckRole($sysSession->username, $sysRoles['course_opener'], $sysSession->school_id)) {
    header("LOCATION: /learn/index.php");
    exit;
}

// 取老師教授的課程：課程編號、課程名稱、課程狀態、老師群姓名、老師大頭照
$rsCourse = new course();
$userCourses = $rsCourse->getUserCoursesSimple($sysSession->username,$sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']);
$smarty->assign('userCourses', $userCourses);

// 沒報名課程，MOOC也沒開啟時，探索課程按鈕不顯示
if (defined('sysEnableMooc') && (sysEnableMooc <= 0)) {
    if ($rsSchool->getSchoolStudentMooc($sysSession->school_id) > 0) {
        $moocDisabled =  false;
    } else {
        echo '不允許觀看';
        die();
    }
}
// 判斷目前環境，以利 chgcourse() 轉換環境
switch($sysSession->env) {
        case 'teach'  : $smarty->assign('curEnv', 2); break;
        case 'direct' : $smarty->assign('curEnv', 3); break;
        case 'academic': $smarty->assign('curEnv', 4); break;
        default: $smarty->assign('curEnv', 2);
}
// 不顯示探索課程
$smarty->assign('exploreEnable', false);

// 是否有開課權限
$openEnable = aclCheckRole($sysSession->username, $sysRoles['course_opener'], $sysSession->school_id);
$smarty->assign('openEnable', $openEnable);

// 不顯示側邊欄開關
$smarty->assign('toggleEnable', 'false');

// 不顯示課程迷失bar
$smarty->assign('courseBarEnable', false);

// 變換為teach環境的js
$smarty->assign('teachDirect', 'teach/');
// Header
$smarty->display('common/tiny_header.tpl');
$smarty->display('mooc_header.tpl');
$smarty->display('mycourse.tpl');
// Footer
$smarty->display('common/tiny_footer.tpl');

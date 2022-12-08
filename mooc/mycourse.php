<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/mooc_teach.php');
require_once(sysDocumentRoot . '/lang/mycourse.php');

// 我的課程呈現方式 - 設定為文字模式時，就切換到文字模式的程式
if ($rsSchool->getMyCourseView($sysSession->school_id) == 'T') {
    header('LOCATION: /learn/mycourse/index.php');
    exit;
}

// 設定可以使用的環境
if (isset($_GET['env']) && in_array($_GET['env'], array('learn','teach'))) {
    $sysSession->env = $_GET['env'];
    $smarty->assign('kind', $_GET['env']);
    if (($sysSession->env == 'teach')&&($profile['isTeacher'])) {
        $sysSession->env='learn';
    }
}else{
    $sysSession->env=($profile['isTeacher'])?'teach':'learn';
    $smarty->assign('kind', $sysSession->env);
}

// 判斷目前環境，以利 chgcourse() 轉換環境
switch($sysSession->env) {
    case 'teach'  : $smarty->assign('curEnv', 2); break;
    case 'direct' : $smarty->assign('curEnv', 3); break;
    case 'academic': $smarty->assign('curEnv', 4); break;
    default: $smarty->assign('curEnv', 1);
}

//是否為獨立校
$smarty->assign('is_independent', is_independent_school);

// Header
$smarty->display('common/tiny_header.tpl');
$smarty->display('mycourse.tpl');
// Footer
$smarty->display('common/tiny_footer.tpl');
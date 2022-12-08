<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/mooc_teach.php');

// 改為只允許開課者能執行開課作業
if (!aclCheckRole($sysSession->username, $sysRoles['course_opener'], $sysSession->school_id)/* && !aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'])*/) {
    header("LOCATION: /learn/index.php");
    exit;
}

// 沒報名課程，MOOC也沒開啟時，探索課程按鈕不顯示
if (defined('sysEnableMooc') && (sysEnableMooc <= 0)) {
    if ($rsSchool->getSchoolStudentMooc($sysSession->school_id) > 0) {
        $moocDisabled =  false;
    } else {
        echo '不允許觀看';
        die();
    }
}

$sysSession->env = 'teach';
$sysSession->restore();
// 判斷目前環境，以利 chgcourse() 轉換環境
switch($sysSession->env) {
        case 'teach'  : $smarty->assign('curEnv', 2); break;
        case 'direct' : $smarty->assign('curEnv', 3); break;
        case 'academic': $smarty->assign('curEnv', 4); break;
        default: $smarty->assign('curEnv', 1);
}

$smarty->assign('isStudentMooc', isset($moocDisabled)?$moocDisabled:true);

$smarty->assign('create_course', $MSG['create_course'][$sysSession->lang]);
$smarty->assign('description_course_open', $MSG['description_course_open'][$sysSession->lang]);
$smarty->assign('btn_sure', $MSG['btn_sure'][$sysSession->lang]);
$smarty->assign('btn_cancel', $MSG['btn_cancel'][$sysSession->lang]);
$smarty->assign('msg_fill_coursename', $MSG['msg_fill_coursename'][$sysSession->lang]);
$smarty->assign('msg_save_fail', $MSG['msg_save_fail'][$sysSession->lang]);
$smarty->assign('msg_save_success', $MSG['msg_save_success'][$sysSession->lang]);

// 是否有啟用LCMS
$lcmsEnable = sysLcmsEnable ? 'true' : 'false';
$smarty->assign('lcmsEnable', $lcmsEnable);

// 不顯示側邊欄開關
$smarty->assign('toggleEnable', 'false');

// 不顯示課程迷失bar
$smarty->assign('courseBarEnable', false);


// 顯示開課介面
$smarty->assign('courseOpenEnable', true);


// 變換為teach環境的js
$smarty->assign('teachDirect', 'teach/');


$ticket = md5('Create' . $sysSession->ticket . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
$smarty->assign('ticket', $ticket);
$smarty->assign('status', 5);
$smarty->assign('review', 1);
$smarty->assign('url', 'http://');
$smarty->assign('quota_used', 0);
$smarty->assign('quota_limit', 204800);
$smarty->assign('fair_grade', 60);
$smarty->assign('en_begin_date', date("Y-m-d"));
$smarty->assign('en_end_date', date("Y-m-d"));
$smarty->assign('st_begin_date', date("Y-m-d"));
$smarty->assign('st_end_date', date("Y-m-d"));


// Header
$smarty->display('common/tiny_header.tpl');
$smarty->display('mooc_header.tpl');
$smarty->display('mycourse.tpl');
// Footer
$smarty->display('common/tiny_footer.tpl');

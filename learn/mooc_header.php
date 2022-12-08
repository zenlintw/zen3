<?php
/**
 * 簡化並保留必要的原本 sysbar 的功能
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lang/sysbar.php');
require_once(sysDocumentRoot . '/lang/personal.php');

// Header
$smarty->display('common/tiny_header.tpl');
//$smarty->display('common/site_header.tpl');
// 沒開 Mooc，但 student_Mooc = 1
if (defined('sysEnableMooc') && (sysEnableMooc <= 0)) {
    $isStudentMooc = ($rsSchool->getSchoolStudentMooc($sysSession->school_id) > 0) ? false: true;
}

// 顯示側邊欄開關(toggle)
$smarty->assign('toggleEnable', 'true');
// 顯示課程迷失bar
$smarty->assign('courseBarEnable', true);
//title名稱
$name = $sysConn->GetOne("SELECT banner_title1 FROM ".sysDBprefix."MASTER.CO_school where school_id=$sysSession->school_id ");

$smarty->assign('PortalcourseId', $sysSession->course_id);
// Content
$curLang = $sysSession->lang;
$smarty->assign(array(
    'username'      => $sysSession->username,
    'curLang'       => $curLang,
    'schoolName'    => htmlspecialchars($name),
    'courseId'      => $sysSession->course_id,
    'courseName'    => htmlspecialchars($sysSession->course_name),
    'isLearning'    => (intval($sysSession->course_id) > 10000000),
    'isStudentMooc' => (isset($isStudentMooc))?$isStudentMooc:true
));

// 語系選單
$languages = array(
    'Big5'        => $MSG['lang_big5']['Big5'],
    'en'          => $MSG['lang_en']['en'],
    'GB2312'      => $MSG['lang_gb']['GB2312'],
    'EUC-JP'      => $MSG['lang_jp']['EUC-JP'],
    'user_define' => $MSG['lang_user']['user_define']
);
removeUnAvailableChars($languages);
$smarty->assign('languages', $languages);

setTicket();
$ticket = md5($sysSession->username . $sysSession->school_id . $sysSession->ticket);
$smarty->assign('ticket', $ticket);

$smarty->assign('portal_mycourse',  '/mooc/user/mycourse.php');
$smarty->display('mooc_header.tpl');

// Footer
$smarty->display('common/tiny_footer.tpl');
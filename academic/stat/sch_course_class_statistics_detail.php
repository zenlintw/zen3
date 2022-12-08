<?php
/** 
 * 課程上課統計
 * 
 * Create date: 2015/04/30
 * Author: Sean
 **/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lang/personal.php');
require_once(sysDocumentRoot . '/lang/teach_statistics.php');
require_once(sysDocumentRoot . '/mooc/models/statistics.php');

global $MSG, $sysSession;

$stat = new Statistics();
$teachNum = $stat->courseTeacherNum($_POST['cid']);
$asistNum = $stat->courseAsistNum($_POST['cid']);
$instrNum = $stat->courseInstrNum($_POST['cid']);

$_POST['select_page'] = $_POST['pre_select_page'];
$_POST['switch_st_during'] = $_POST['pre_switch_st_during'];
$_POST['st_begin'] = $_POST['pre_st_begin'];
$_POST['st_end'] = $_POST['pre_st_end'];
$_POST['course_stat'] = $_POST['pre_course_stat'];
$_POST['courseName'] = $_POST['pre_courseName'];

// 統計報表
$genderP = $stat->getGenderPercent($_POST['cid']);
$statusP = $stat->getStatusPercent($_POST['cid']);
$educationP = $stat->getEduPercent($_POST['cid']);
$countryP = $stat->getCountryPercent($_POST['cid']);
$ageP = $stat->getAgePercent($_POST['cid']);
$roleP = $stat->getRolePercent($_POST['cid']);

$smarty->assign('MSG', $MSG);
$smarty->assign('sysSession', $sysSession);
$smarty->assign('post', $_POST);
$smarty->assign('teachNum', $teachNum->fields['Number']);
$smarty->assign('asistNum', $asistNum->fields['Number']);
$smarty->assign('lecturersNum', $instrNum->fields['Number']);
$smarty->assign('genderP', $genderP);
$smarty->assign('statusP', $statusP);
$smarty->assign('educationP', $educationP);
$smarty->assign('countryP', $countryP);
$smarty->assign('ageP', $ageP);
$smarty->assign('roleP', $roleP);

//output
$smarty->display('academic/stat/sch_course_class_statistics_detail.tpl');
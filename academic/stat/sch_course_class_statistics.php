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
require_once(sysDocumentRoot . '/lang/teach_statistics.php');
require_once(sysDocumentRoot . '/mooc/models/statistics.php');
require_once(sysDocumentRoot . '/lib/course.php');

global $MSG, $sysSession;
$stat = new Statistics();
$pos = array();
$user_lang = $sysSession->lang;

$_POST['select_page'] = $_POST['pre_select_page'];
$_POST['switch_st_during'] = $_POST['pre_switch_st_during'];
$_POST['st_begin'] = $_POST['pre_st_begin'];
$_POST['st_end'] = $_POST['pre_st_end'];
$_POST['course_stat'] = $_POST['pre_course_stat'];
$_POST['courseName'] = $_POST['pre_courseName'];

$data_count = $stat->getAllCourseInfo_Stat_count($_POST)->fields['COUNT'];

$pos = $stat->pager($data_count, $_POST['pageAct'], $_POST['select_page'], 10);
$courseDataList = $stat->getAllCourseInfo_Stat($_POST, $pos);
$recount = sizeof($courseDataList);

// assign
$smarty->assign('post', $_POST);
$smarty->assign('MSG', $MSG);
$smarty->assign('sysSession', $sysSession);
$smarty->assign('courseDataList', $courseDataList);
$smarty->assign('pos', $pos);
$smarty->assign('reccount', $reccount);
$smarty->assign('totalpage', $totalpage);
$smarty->assign('data_count', $data_count);
$smarty->assign('user_lang', $user_lang);
$smarty->assign('name', $name);

// output
$smarty->display('academic/stat/sch_course_class_statistics.tpl');
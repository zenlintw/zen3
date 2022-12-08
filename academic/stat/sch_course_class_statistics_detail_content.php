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
$data_count = $stat->courseStudentInfoList_count($_POST['cid'], $_POST)->fields['COUNT'];
$pos = $stat->pager($data_count, $_POST['pageAct'], $_POST['select_page'], 10);

$courseStudentData = $stat->courseStudentInfoList($_POST['cid'], $_POST, $pos);

//assign
$smarty->assign('MSG', $MSG);
$smarty->assign('sysSession', $sysSession);
$smarty->assign('courseStudentData', $courseStudentData);
$smarty->assign('post', $_POST);

$smarty->assign('post_st', $_POST);
$smarty->assign('data_count', $data_count);
$smarty->assign('pos', $pos);
$smarty->assign('totalpage', $totalpage);
$smarty->assign('data_count', $data_count);

//output
$smarty->display('academic/stat/sch_course_class_statistics_detail_content.tpl');

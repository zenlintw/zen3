<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/myteaching.php');
require_once(sysDocumentRoot . '/lang/student.php');

if ($sysSession->username == 'guest') {
    header('Location: /mooc/index.php');
    exit;
}

$course_id = intval(sysNewDecode($_POST['course_id']));
if ($course_id < 10000000) {
    header('Location: /mooc/index.php');
    exit;
}

$role = $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'];
if (!aclCheckRole($sysSession->username, $role, $course_id)){
    header('Location: /mooc/user/code.php');
    exit;
}

$random_seat = md5(uniqid(rand(), true));
$ticket = md5(sysTicketSeed . $sysSession->username . $random_seat);

if (isset($_POST['keyword']) && $_POST['keyword']!='') {
	$search = ' and B.first_name like "%'.$_POST['keyword'].'%"';
	
	$smarty->assign('tip_msg', str_replace('#name#',$_POST['keyword'],'<font color="#0088D2">'.$MSG['search_result'][$sysSession->lang].'</font>'));
} else {
	$search = '';
	$smarty->assign('tip_msg', $MSG['no_student'][$sysSession->lang]);
}

$rsCourse = new course();
$courseData = $rsCourse->getCourseById($course_id);
$courseData['enc_course_id'] = $_POST['course_id'];


$cour_sort = array(
	'',
	'B.username',
	'B.company',

);

    /*
    * 排序
    */
    $_POST['sortby'] = intval($_POST['sortby']);
    $sortby = $cour_sort[$_POST['sortby']];
    if (empty($sortby)) $sortby = 'B.username';

    $order = trim($_POST['order']);
    if (!in_array($order, array('asc', 'desc'))) $order = 'asc'; 


$smarty->assign('sort', $_POST['sortby']);
$smarty->assign('order', $order);

$sqls = str_replace('%COURSE_ID%', $course_id, $Sqls['get_course_all_student']);
$sqls .= $search.' and A.role&'.($sysRoles["student"] | $sysRoles["auditor"]).' order by ' . $sortby . " $order";
// $sysConn->debug=true;
if ($rs = $sysConn->Execute($sqls)){
    while($fields = $rs->FetchRow()){
        $datalist[] = $fields;
    }
}

$active = 0;
foreach(array('questionnaire', 'exam') as $type) {
    $sql = "select exam_id from `WM_qti_" . $type . "_test` where course_id={$course_id} and publish='action' and begin_time!='0000-00-00 00:00:00' and close_time='9999-12-31 00:00:00'";
    $exam_id = $sysConn->GetOne($sql);
    if($exam_id!='') {
        $active = 1;
        break;
    }
}

$smarty->assign('courseData', $courseData);
$smarty->assign('data', $datalist);
$smarty->assign('ticket', $ticket);
$smarty->assign('referer', $random_seat);
$smarty->assign('active', $active);
$smarty->assign('keyword', $_POST['keyword']);
$smarty->display('user/student.tpl');
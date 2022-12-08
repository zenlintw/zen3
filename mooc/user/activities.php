<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/myteaching.php');
require_once(sysDocumentRoot . '/lang/activities.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

if ($sysSession->username == 'guest') {
    header('Location: /mooc/index.php');
    exit;
}


// $course_id = intval(sysNewDecode($_POST['course_id']));
$course_id = $sysSession->course_id;
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
$ticket = md5(sysTicketSeed . $sysSession->username . $course_id . $random_seat);

$forGuestQuests = array();
$forGuest = '';
$forGuestQuests = aclGetForGuestQuest($course_id);


$rsCourse = new course();
$courseData = $rsCourse->getCourseById($course_id);
$role     = $sysRoles['student'] | $sysRoles['auditor'];
list($majorCount) = dbGetStSr('WM_term_major', 'count(*)', "course_id={$course_id} and role&{$role}", ADODB_FETCH_NUM);

$num = 0;
$has_active = 0;
$Data = array();
// 取得IRS測驗資料
$sql = 'select *,"exam",(CASE WHEN NOW()>=close_time THEN "3" WHEN publish="action" AND begin_time!="0000-00-00 00:00:00" THEN "1" ELSE "2" END) AS list from WM_qti_exam_test where type=5 and course_id='.$course_id;
$sql.= ' union ';
$sql.= 'select *,"questionnaire",(CASE WHEN NOW()>=close_time THEN "3" WHEN publish="action" AND begin_time!="0000-00-00 00:00:00" THEN "1" ELSE "2" END) AS list from WM_qti_questionnaire_test where type=5 and course_id='.$course_id;
$sql.= ' order by list asc,create_time desc,exam_id desc';
$rs = $sysConn->Execute($sql);



if ($rs) while ($fields = $rs->FetchRow()) {
    $Data[$num] = $fields;
    $cpTitle = unserialize($fields['title']);
    $Data[$num]['title'] = $cpTitle[$sysSession->lang];
    $Data[$num]['exam_type'] = $fields['exam'];
    
    $Data[$num]['goto'] = sysNewEncode(serialize(array('course_id'=>$course_id, 'type'=>$fields['exam'], 'exam_id'=>$fields['exam_id'])), 'wm5IRS');
    if (!empty($fields['close_time']) && (time()>=strtotime($fields['close_time']))){
    	$Data[$num]['status'] = 'over';
    } else if (($fields['publish'] == 'action') && (!empty($fields['begin_time']))){
    	$Data[$num]['status'] = 'active';
    	$has_active = 1;
    } else {
    	$Data[$num]['status'] = 'start';
    }
    if ($fields['exam']=='questionnaire' && in_array($fields['exam_id'],$forGuestQuests)) {
        $Data[$num]['start'] = $sysConn->GetOne('select count(examinee) from WM_qti_'.$fields['exam'].'_result where exam_id='.$fields['exam_id'].' and status!="break"');
    } else {
        $Data[$num]['start'] = $sysConn->GetOne('select count(distinct(examinee)) from WM_qti_'.$fields['exam'].'_result where exam_id='.$fields['exam_id'].' and status!="break"');
    }
    $Data[$num]['nostart'] = $majorCount - $Data[$num]['start'];
    if ($majorCount!=0) {
        $Data[$num]['start_rate'] = round($Data[$num]['start']/$majorCount*100);
    } else {
    	$Data[$num]['start_rate'] = '0';
    }

    if ($fields['exam']=='questionnaire' && in_array($fields['exam_id'],$forGuestQuests)) {
        $Data[$num]['forGuest'] = '1';
    } else {
        $Data[$num]['forGuest'] = '0';
    }
    
    $num++;
}

$smarty->assign('courseData', $courseData);
$smarty->assign('has', $has_active);
$smarty->assign('ticket', $ticket);
$smarty->assign('referer', $random_seat);
$smarty->assign('cid', $sysSession->course_id);
$smarty->assign('irsQuestionnaireList', $Data);
$smarty->display('user/activities.tpl');
unset($_POST);
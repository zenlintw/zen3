<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/myteaching.php');
require_once(sysDocumentRoot . '/lang/activities.php');
require_once(sysDocumentRoot . '/lang/irs.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

if ($sysSession->username == 'guest') {
    header('Location: /mooc/index.php');
    exit;
}

$role = $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'];
if (!aclCheckRole($sysSession->username, $role, $_POST['course_id'])){
    header('Location: /mooc/index.php');
    exit;
}

$course_id = $_POST['course_id'];
$exam_type = $_POST['exam_type'];
$exam_id   = $_POST['exam_id'];

$sysSession->course_id = $course_id;
$sysSession->restore();

$uploads_dir = sprintf('%s/base/%05d/course/%08d/temp', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id);
if (is_dir($uploads_dir)) {
	exec('/bin/rm -rf '.$uploads_dir);
}
$rsCourse = new course();
$courseData = $rsCourse->getCourseById($course_id);
$enc_course_id = sysNewEncode($course_id);
$courseData['enc_course_id'] = $enc_course_id;

if ($exam_id!='') {

	$sql = "select title from `WM_qti_" . $exam_type . "_test` where exam_id={$exam_id}";
	$stitle = $sysConn->GetOne($sql);
	
	$cpTitle = unserialize($stitle);
	$title   = $cpTitle[$sysSession->lang];
	
	// 計算分數
	$sql = "select count(*) as submit,ROUND(MAX(score),0) as max,ROUND(MIN(score),0) as min,ROUND(AVG(score),0) as avg from `WM_qti_" . $exam_type . "_result` where exam_id={$exam_id} and status!='break'";
	$score_data = $sysConn->GetRow($sql);
	
	// 取得題目 資訊
	define('API_QTI_which',$exam_type);
	require_once(sysDocumentRoot . '/xmlapi/lib/qti.php');
	$qtiLib = new Qti();
	$qtiDetail = new QtiResult();
	$qtiDetail->init($exam_type);
	$qtiDetail->getQtiDetail($exam_id);
	$getAnswer = ($exam_type === 'exam') ? true : false;
	$itemInfo = $qtiLib->transformer($qtiDetail->qtiData['dom'], $qtiDetail->qtiData['ctx'], $exam_type, $getAnswer);
	
	//var_dump($itemInfo);
	
	$total_score = 0;
	foreach ($itemInfo as $val) {
		$total_score+=$val['score'];
	}

}

$role     = $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'];
$rsCourse = new course();
$courses = $rsCourse->getUserCoursesDetail($sysSession->username, $role, false, $search, array());

// 輪替色系
$color = array('#815cb4', '#3aabdd', '#278f7f', '#ff7d13', '#e8483f', '#e3a729', '#42b6b5', '#81a41c', '#5e92fc', '#cf4aab');

$img = array('gif','jpeg','png','bmp','jpg','GIF','JPEG','PNG','BMP','JPG');
$smarty->assign('img_arr', $img); 

$sel_type = array(1=>$MSG['correct'][$sysSession->lang],2=>$MSG['s_choice'][$sysSession->lang],3=>$MSG['m_choice'][$sysSession->lang],5=>$MSG['short'][$sysSession->lang]);
$smarty->assign('arr_type', $sel_type);

$uploadMaxFilesize = ini_get('upload_max_filesize');
switch(substr($uploadMaxFilesize, -1, 1)) {
    case 'K':
        $transform = 1024;
        break;
    
    case 'M':
        $transform = 1024 * 1024;
        break;
    
    case 'G':
        $transform = 1024 * 1024 * 1024;
        break;
}
$uploadMaxFilesize = substr($uploadMaxFilesize, 0, -1) * $transform;

if ($exam_type == 'questionnaire' && aclCheckWhetherForGuestQuest($course_id, $exam_id))
{
    $forGuest = true;
}

$smarty->assign('courseData', $courseData);
$smarty->assign('title', $title);
$smarty->assign('type', $exam_type);
$smarty->assign('exam_id', $exam_id);
$smarty->assign('item', $itemInfo);
$smarty->assign('items', count($itemInfo));
$smarty->assign('score_data', $score_data);
$smarty->assign('total_score', round($total_score));
$smarty->assign('color', $color);
$smarty->assign('forGuest', $forGuest);
$smarty->assign('uploadMaxFilesize', $uploadMaxFilesize);
$smarty->display('user/exam_edit.tpl');
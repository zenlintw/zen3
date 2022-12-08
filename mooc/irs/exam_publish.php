<?php
// error_reporting(E_ALL^E_NOTICE);
// ini_set('display_errors', 1);
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/irs.php');
require_once(sysDocumentRoot . '/lang/irs.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

if (!empty($_GET['goto'])) {
    $goto = sysNewDecode($_GET['goto'],'wm5IRS');
    if ($goto === false){
        header('LOCATION: /mooc/irs/message.php?goto='.sysNewEncode(serialize(array('code'=>'1')), 'wm5IRS'));
        exit;
    }else{
        $gotoData = unserialize($goto);
        // 驗證參數值
        if ((strlen($gotoData['course_id']) != 8) ||
            (strlen($gotoData['exam_id']) != 9) ||
            !in_array($gotoData['type'], array('exam','questionnaire'))
        ){
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $course_id = intval($gotoData['course_id']);
        $type      = $gotoData['type'];
        $exam_id   = intval($gotoData['exam_id']);
    }

    if (!aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] | $sysRoles['student'] | $sysRoles['auditor'], $_GET['course_id'])){
        header('LOCATION: /mooc/irs/message.php?goto='.sysNewEncode(serialize(array('code'=>'2')), 'wm5IRS'));
        exit;
    }
} else {
	header('HTTP/1.1 403 Forbidden');
    exit;
}

$role = $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'];
if (!aclCheckRole($sysSession->username, $role, $course_id)){
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$forGuestQuests = array();
$forGuest = '';
if ($type == 'questionnaire') $forGuestQuests = aclGetForGuestQuest($course_id);

if (in_array($exam_id, $forGuestQuests)) {
    $forGuest = 1;
}

// 取得測驗名稱
$RS = dbGetStSr('WM_qti_' . $type . '_test', '*', "exam_id={$exam_id}");
$title = unserialize($RS['title']);

// 取得課程名稱
$caption = $sysConn->GetOne('select caption from WM_term_course where course_id=' . $course_id);
$course_name = unserialize($caption);

// 取得qrcode
$qrcodeUrl = irs::getIrsResponseUrl($course_id,$type,$exam_id);
//$tmp = file_get_contents('http://wm5irs-ming.sun.net.tw'.$qrcodeUrl);
//$qrcode = 'http://wm5irs-ming.sun.net.tw'.$qrcodeUrl;

// 取得題目 資訊
define('API_QTI_which',$type);
require_once(sysDocumentRoot . '/xmlapi/lib/qti.php');
$qtiLib = new Qti();
$qtiDetail = new QtiResult();
$qtiDetail->init($type);
$qtiDetail->getQtiDetail($exam_id);
$getAnswer = ($type === 'exam') ? true : false;
$itemInfo = $qtiLib->transformer($qtiDetail->qtiData['dom'], $qtiDetail->qtiData['ctx'], $type, $getAnswer);

// 輪替色系
$color = array('#815cb4', '#3aabdd', '#278f7f', '#ff7d13', '#e8483f', '#e3a729', '#42b6b5', '#81a41c', '#5e92fc', '#cf4aab');

// 互動碼
$code = strtoupper(dechex($course_id));

$smarty->assign('title', $title[$sysSession->lang]);
$smarty->assign('publish', $RS['publish']);
$smarty->assign('course_name', $course_name[$sysSession->lang]);
$smarty->assign('qrcode', $qrcodeUrl);
$smarty->assign('course_id', $course_id);
$smarty->assign('qti_type', $type);
$smarty->assign('exam_id', $exam_id);
$smarty->assign('item', $itemInfo);
$smarty->assign('items', count($itemInfo));
$smarty->assign('color', $color);
$smarty->assign('code', $code);
$smarty->assign('forGuest', $forGuest);
$smarty->assign('teach_username', $sysSession->username);
$smarty->assign('sysWebsocketHost', sysWebsocketHost);

echo $smarty->fetch('irs/exam_publish.tpl'); 
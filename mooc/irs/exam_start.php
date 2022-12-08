<?php
// error_reporting(E_ALL^E_NOTICE);
// ini_set('display_errors', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/lib_counter.php');// 線上
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/mooc/models/school.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/xmlapi/config.php');
require_once(sysDocumentRoot . '/xmlapi/initialize.php');
require_once(sysDocumentRoot . '/xmlapi/lib/common.php');
require_once(sysDocumentRoot . '/xmlapi/lib/common-qti.php');
require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');
require_once(sysDocumentRoot . '/xmlapi/lib/encryption.php');
require_once(sysDocumentRoot . '/xmlapi/actions/start-exam.class.php');
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

        $_GET['course_id'] = intval($gotoData['course_id']);
        $_GET['type'] = $gotoData['type'];
        $_GET['eid'] = intval($gotoData['exam_id']);

    }
    $forGuestQuests = array();
    if ($_GET['type'] == 'questionnaire') $forGuestQuests = aclGetForGuestQuest($_GET['course_id']);

//    echo '<pre>';
//    var_dump($_GET['eid']);
//    var_dump($forGuestQuests);
//    var_dump(!in_array($_GET['eid'], $forGuestQuests));
//    echo '</pre>';

    // 不是匿名問卷
    if (!in_array($_GET['eid'], $forGuestQuests)) {
        if ($sysSession->username=='guest') {
            header('Location: /mooc/irs/login.php?goto='.$_GET['goto']);
            die();
        } else {
            // TODO: 掃QRcode自動加入選課功能，屬於IRS雲端版功能，愛上互動無此功能故取消此段程式
            // list($majorExists) = dbGetStSr('WM_term_major', 'count(*)', "username = '{$sysSession->username}' and course_id={$_GET['course_id']} and role&{$sysRoles['student']}", ADODB_FETCH_NUM);

            // if ($majorExists==0) {
            //     $sysConn->Execute("insert into WM_term_major (username,course_id,role,add_time) values ('{$sysSession->username}','{$_GET['course_id']}'," . $sysRoles['student'] . ",NOW())");
            //     if ($sysConn->ErrorNo() == 1062){ // 如果已經有本課某種身份
            //         $mask = $sysRoles['all'] ^ $sysRoles['auditor'];
            //         $sysConn->Execute("update WM_term_major set role=role & {$mask} | {$sysRoles['student']},add_time=NOW() where username='{$sysSession->username}' and course_id='{$_GET['course_id']}'");
            //     }
            // }
        }

        if (!aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] | $sysRoles['student'] | $sysRoles['auditor'], $_GET['course_id'])){
            header('LOCATION: /mooc/irs/message.php?goto='.sysNewEncode(serialize(array('code'=>'2')), 'wm5IRS'));
            exit;
        }
    } else {
        $_GET['ForGuestQuest'] = 1;
    }

}

//echo '<pre>';
//var_dump($_GET['ForGuestQuest']);
//echo '</pre>';

$sysSession->env = 'learn';
$sysSession->restore();

$_REQUEST = $_GET;
$_REQUEST['ticket'] = $_COOKIE['idx'];
// $_GET['type'] = 'exam';
// $_GET['eid'] = 100000002;


list($times) = dbGetStSr(
            'WM_qti_' . $_GET['type'] . '_result',
            'count(*)',
            "exam_id={$_GET['eid']} AND examinee='{$sysSession->username}' AND status!='break' ",
            ADODB_FETCH_NUM
        );

if ($_GET['ForGuestQuest']==1) $times = 0;

//echo '<pre>';
//var_dump($_GET['ForGuestQuest']);
//var_dump($times);
//echo '</pre>';
//die();
$smarty->assign('forGuest', $_GET['ForGuestQuest']);

// 取得測驗名稱
$RS = dbGetStSr('WM_qti_' . $_GET['type'] . '_test', '*', "exam_id={$_GET['eid']}");
$title = unserialize($RS['title']);
$smarty->assign('title', $title[$sysSession->lang]);

if ($times > 0) {
    $smarty->display('irs/already_submit.tpl');
    exit();
}

$oAction = new StartExamAction;
ob_start();
$oAction->main();
$rtn = ob_get_contents();
ob_end_clean();
$result = JsonUtility::decode($rtn);


if (isset($result['code'])) {
    if ($result['code']=='4' || $result['code']=='6') {
        header('LOCATION: /mooc/irs/message.php?goto='.sysNewEncode(serialize(array('code'=>'4')), 'wm5IRS'));
        exit;
    }
    if ($result['code']=='7') {
        header('LOCATION: /mooc/irs/message.php?goto='.sysNewEncode(serialize(array('code'=>'7')), 'wm5IRS'));
        exit;
    }
}

/*
 * ALTER TABLE  `WM_term_major` ADD  `exam_time` DATETIME NULL ;
 */
dbSet('WM_term_major','exam_time=NOW()',"course_id={$_GET['course_id']} and username='{$sysSession->username}'");

$item_output = '';
$i=1;
$img = array('gif','jpeg','png','bmp','jpg');
foreach ($result['data']['items'] as $key => $val) {
    $smarty->assign('data', $val);
    $smarty->assign('serinal', $i);
    $smarty->assign('total', count($result['data']['items']));

    $mod = count($val['attaches'])%2;
    $smarty->assign('odd', $mod);
    $smarty->assign('img_arr', $img);
    switch ($val['type']) {
        case 1: // 是非
            $item_output .= $smarty->fetch('irs/correct.tpl');
            break;
        case 2: // 單選
            $item_output .= $smarty->fetch('irs/s_choice.tpl');
            break;
        case 3: // 多選
            $item_output .= $smarty->fetch('irs/m_choice.tpl');
            break;
        case 5: // 簡答
            $item_output .= $smarty->fetch('irs/short.tpl');
            break;
    }

    $item_output .= $smarty->fetch('irs/button.tpl');
    $i++;
}

$ticket  = md5(sysTicketSeed . $_GET['eid'] . $result['data']['time_id']);

header('Content-Type: text/html');
echo $smarty->fetch('irs/exam_start.tpl');
echo '<form name=responseForm id=responseForm lang=zh-tw style="DISPLAY: inline" action=save_answer.php encType=multipart/form-data method=post accept-charset=UTF-8 data-cke-expando="3">';
echo '<input type="hidden" name="exam_id" value="'.$_GET['eid'].'">';
echo '<input type="hidden" name="type" value="'.$_GET['type'].'">';
echo '<input type="hidden" name="forGuest" value="'.$_GET['ForGuestQuest'].'">';
echo '<input type="hidden" name="timeId" id="timeId" value="'.$result['data']['time_id'].'">';
echo '<input type="hidden" name="ticket" id="ticket" value="'.$ticket.'">';
echo '<input type="hidden" name="nickname" id="nickname" value="">';
echo $item_output;
echo '</form>';
echo $smarty->fetch('common/tiny_footer.tpl');

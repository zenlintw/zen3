<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
require_once(sysDocumentRoot . '/xmlapi/lib/common-qti.php');
include_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
define('QTI_DISPLAY_ANSWER',   true); // 是否顯示答案
define('QTI_DISPLAY_OUTCOME',  true); // 是否顯示得分
define('QTI_DISPLAY_RESPONSE', true); // 是否顯示作答答案
include_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
require_once(sysDocumentRoot . '/lang/irs.php');

$qtype = $_POST['type'];
$exam_id = intval($_POST['exam_id']);
$forGuest = $_POST['forGuest'];
$timeId = $_POST['timeId']; // 取得最大測驗次數

if ($_POST['ticket'] != md5(sysTicketSeed . $_POST['exam_id'] . $_POST['timeId'])) {
   die('Fake Ticket');
}

list($close_time,$title,$courseId) = dbGetStSr(
            'WM_qti_' . $qtype . '_test',
            'close_time,title,course_id',
            "`exam_id`={$exam_id}"
        );

$title = unserialize($title);
$smarty->assign('title', $title[$sysSession->lang]);        
        
$exceptTimes = array('0000-00-00 00:00:00', '1970-01-01 08:00:00', '9999-12-31 00:00:00');
$now = time();
$closeTime = strtotime($close_time);
if (!in_array($close_time, $exceptTimes) && $closeTime <= $now) {
    $smarty->display('irs/no_interactive.tpl');
    exit();
}

if ($forGuest!=1) {
    list($times) = dbGetStSr(
                'WM_qti_' . $qtype . '_result',
                'count(*)',
                "exam_id={$exam_id} AND examinee='{$sysSession->username}' AND status!='break' ",
                ADODB_FETCH_NUM
            );

    if ($times > 0) {        
        $smarty->display('irs/already_submit.tpl');
        exit();
    }
}
    
ignore_user_abort(true);
set_time_limit(0);


define('PATH_LIB', sysDocumentRoot . '/xmlapi/lib/');
define('API_QTI_which', $qtype);
if (!defined('QTI_env')) {
    define('QTI_env', 'learn');
}

require_once(sysDocumentRoot . '/xmlapi/lib/qti.php');


if ($qtype == 'exam')
{
    $sysSession->cur_func = '1600400200';
}
else if ($qtype == 'homework')
{
    $sysSession->cur_func = '1700400200';
}
else
{
    $sysSession->cur_func = '1800300200';
}

$sysSession->restore();

$data = array();
$i = 0;
foreach ($_POST['ans'] as $item_id => $val) {
    $data[$i]['item_id'] = $item_id;
    foreach($val as $type => $ans) {
        $data[$i]['type'] = $type;
        if ($type!=3) {
            $data[$i]['answer'][0] = $ans;
        } else {
            $data[$i]['answer'] = $ans;
        }
    }
    $i++;
}

$qti = new Qti();

if ($forGuest!= 1) {
    $res = $qti->checkExamStat($exam_id, $_COOKIE['idx'], $sysSession->username, $exam, $qtype, $forGuest);
} else {

    list($exam['result_content']) = dbGetStSr(
        'WM_qti_' . $qtype . '_result',
        '`content`',
        "exam_id={$exam_id} AND examinee='{$sysSession->username}' AND time_id={$timeId} ",
        ADODB_FETCH_NUM
    );
}

if (isset($exam['result_content']) === FALSE) {
    header('Location: /mooc/user/code.php');
    exit;
}
$xml = $qti->saveAnswer($exam, $data);

$ctx = xpath_new_context(domxml_open_mem($xml));
if ($qtype === 'exam') {
    ob_start();
    parseQuestestinterop($xml);
    $result_html = ob_get_contents();
    ob_end_clean();
    if (preg_match_all('/<input\b[^>]*\bname="item_scores\b[^>]*\bvalue="(-?[0-9.]*)"/', $result_html, $regs))
        $total_score = array_sum($regs[1]);
    else
    $total_score = 0;           
} else {
    $total_score = 'NULL';
}

// 判斷是否都是是非選擇題
$ret1 = $ctx->xpath_eval('count(//item/presentation//render_choice)+count(//item/presentation//response_grp/render_extension)+count(//item/presentation//response_str/render_fib)');
$ret2 = $ctx->xpath_eval('count(//item/presentation)');
$ret3 = $ctx->xpath_eval('//item/presentation//response_str/render_fib[@prompt="Box"]');
$status = (intval($ret1->value) < intval($ret2->value)) ? 'submit' : 'revised';
if ($status=='revised' && count($ret3->nodeset)!=0)  $status = 'submit';
if ($status=='revised') $update_score = ',score=' . $total_score . '';
if ($forGuest) $nick = ',comment="' . $_POST['nickname'] . '"';

//echo '<pre>';
//var_dump($forGuest);
//var_dump($forGuest == 1);
//echo '</pre>';
if ($forGuest == 1) {
    $username = 'guest';
    
    list($newTimeId) = dbGetStSr(
                'WM_qti_' . $qtype . '_result',
                'MAX(time_id)',
                "exam_id={$exam_id} AND examinee = 'guest'",
                ADODB_FETCH_NUM
            );
//    echo '<pre>';
//    var_dump($newTimeId);
//    echo '</pre>';
    $setTimeId = 'time_id = ' . ((int)$newTimeId + 1) . ', ';
} else {
    $username = $sysSession->username;
    $setTimeId = '';
}
//echo '<pre>';
//var_dump($setTimeId);
//echo '</pre>';
dbSet('WM_qti_' . $qtype . '_result',
    $setTimeId . 'examinee="' . $username . '", content="' . mysql_real_escape_string($xml) . '",status="' . $status . '",submit_time=now()' . $update_score . ',content=replace(content, "</questestinterop>", "<wm:submit_status>submit</wm:submit_status></questestinterop>")'. $nick,
    "exam_id={$exam_id} and examinee='{$sysSession->username}' and time_id={$timeId}");

wmSysLog($sysSession->cur_func, $courseId , $exam_id , 0, 'classroom', $_SERVER['PHP_SELF'], '(iOS)'.$qtype . ' finish! Num of times: ' . $timeId);

if ($status=='revised' && $qtype == 'exam') {
    include_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
    if (reCalculateQTIGrade($sysSession->username, $exam_id, $qtype)) {
            reCalculateGrades($courseId);
    }
}

$smarty->assign('score', $total_score);
$smarty->assign('type', $qtype);
$smarty->assign('code', $courseId);
$smarty->assign('exam_id', $exam_id);
$smarty->display('irs/exam_complete.tpl');
exit();

?>
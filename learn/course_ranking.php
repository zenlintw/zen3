<?php
/**
 * 課程排行
 * $Id: course_ranking.php,v 1.1 2010/02/24 02:39:05 saly Exp $
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/course_ranking.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/lib_logs.php');
require_once(sysDocumentRoot . '/learn/mycourse/lib.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

// {{{ 函數宣告 begin
function sort_method($a, $b)
{
    global $order_idx;
    // Bug#1537-修改排序機制 by Small 2006/12/13
    // return $a[$order_idx] - $b[$order_idx];
    return ($a[$order_idx] >= $b[$order_idx])? 1 : -1;
}
// }}} 函數宣告 end

// {{{ 主程式 begin
$sysSession->cur_func='1500300100';
$sysSession->restore();
if (!aclVerifyPermission(1500300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
}

$topics = array($MSG['course_name'][$sysSession->lang],
                $MSG['student_amount'][$sysSession->lang],
                $MSG['auditor_amount'][$sysSession->lang],
                $MSG['to_class_times'][$sysSession->lang],
                $MSG['post_times'][$sysSession->lang],
                $MSG['discuss_times'][$sysSession->lang],
                $MSG['read_time'][$sysSession->lang]);

$sk = array(null, 'stud', 'audi', 'login_times', 'post_times', 'dsc_times', 'rss');

if ((isset($_POST['sortby'])=== false) || (!(in_array($_POST['sortby'], $sk))))
{
    $_POST['sortby']='post_times';
}

if ((isset($_POST['order'])===false) || (!(in_array($_POST['order'], array('desc','asc')))))
{
    $_POST['order']='desc';
}

//$orderBy = 'C.course_id ASC';
//if ($_POST['sortby'] === 'name') {
//    if ($_POST['order'] === 'desc') {
//        $orderBy = 'C.course_id DESC';
//    } 
//}

//global $sysConn;
//$sysConn->debug = true;

$lines = 10;

if (isset($_POST['page']) === FALSE || empty($_POST['page']) === TRUE) {
    $_POST['page'] = 1;
}

//    echo '<pre>';
//    var_dump($_POST['sortby']);
//    echo '</pre>';

// 主排序資料
switch($_POST['sortby']) {
    // 課程名稱、上課次數、張貼篇數、討論次數、閱讀時間
    case 'name':
    case 'login_times':
    case 'post_times':
    case 'dsc_times':
        $sql1 = "
                 select SQL_CALC_FOUND_ROWS C.course_id,C.caption,C.login_times,C.post_times,C.dsc_times, 0 as rss
                 from WM_term_course as C
                 where C.course_id!=10000000 AND C.kind='course' AND (C.status between 0 and 4) 
                 group by C.course_id order by " . $_POST['sortby'] . " " . $_POST['order'] . " LIMIT " . ($_POST['page'] - 1) * $lines . ", " . $lines . "
                ";
        break;
    // 閱讀時間
    case 'rss':
        $sql1 = "
                 select SQL_CALC_FOUND_ROWS C.course_id,C.caption,C.login_times,C.post_times,C.dsc_times,sum(R.reading_seconds) as rss
                 from WM_term_course as C left outer join WM_record_daily_course as R
                 on C.course_id=R.course_id
                 where C.course_id!=10000000 AND C.kind='course' AND (C.status between 0 and 4) 
                 group by C.course_id order by " . $_POST['sortby'] . " " . $_POST['order'] . " LIMIT " . ($_POST['page'] - 1) * $lines . ", " . $lines . "
                ";
        break;

    // 正式生人數、旁聽生人數
    case 'stud':
    case 'audi':
        $sql1 = "
                 select SQL_CALC_FOUND_ROWS C.course_id,
                 sum(if(M.role&" . $sysRoles['student'] . "=" . $sysRoles['student'] . ",1,0)) as stud,
                 sum(if(M.role&" . $sysRoles['auditor'] . "=" . $sysRoles['auditor'] . ",1,0)) as audi
                 from WM_term_course as C left outer join WM_term_major as M
                 on C.course_id=M.course_id
                 where C.course_id!=10000000 AND C.kind='course'  AND (C.status between 0 and 4) 
                 group by C.course_id order by " . $_POST['sortby'] . " " . $_POST['order'] . " LIMIT " . ($_POST['page'] - 1) * $lines . ", " . $lines . "
                ";
        break;
}

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$all = $sysConn->GetArray($sql1);
$total_course = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

$courseIds = array();
foreach ($all as $v) {
    $courseIds[] = $v['course_id'];
}

//    echo '<pre>';
//    var_dump($all);
//    var_dump($courseIds);
//    echo '</pre>';

// 取其他欄位資料
switch($_POST['sortby']) {
    // 課程名稱、上課次數、張貼篇數、討論次數、閱讀時間
    case 'name':
    case 'login_times':
    case 'post_times':
    case 'dsc_times':
        $sql2 = "
                 select course_id, sum(reading_seconds) as rss
                 from WM_record_daily_course
                 where course_id IN (" . implode(',', $courseIds) . ") 
                 group by course_id
                ";
        $sql3 = "
                 select C.course_id,
                 sum(if(M.role&" . $sysRoles['student'] . "=" . $sysRoles['student'] . ",1,0)) as stud,
                 sum(if(M.role&" . $sysRoles['auditor'] . "=" . $sysRoles['auditor'] . ",1,0)) as audi
                 from WM_term_course as C left outer join WM_term_major as M
                 on C.course_id=M.course_id
                 where C.course_id IN (" . implode(',', $courseIds) . ") 
                 group by C.course_id
                ";
        break;
    case 'rss':
        $sql2 = "
                 select course_id,caption,login_times,post_times,dsc_times
                 from WM_term_course
                 where course_id IN (" . implode(',', $courseIds) . ") 
                 group by course_id
                ";
        $sql3 = "
                 select C.course_id,
                 sum(if(M.role&" . $sysRoles['student'] . "=" . $sysRoles['student'] . ",1,0)) as stud,
                 sum(if(M.role&" . $sysRoles['auditor'] . "=" . $sysRoles['auditor'] . ",1,0)) as audi
                 from WM_term_course as C left outer join WM_term_major as M
                 on C.course_id=M.course_id
                 where C.course_id IN (" . implode(',', $courseIds) . ") 
                 group by C.course_id
                ";
        break;

    // 正式生人數、旁聽生人數
    case 'stud':
    case 'audi':
        $sql2 = "
                 select course_id,caption,login_times,post_times,dsc_times
                 from WM_term_course
                 where course_id IN (" . implode(',', $courseIds) . ") 
                 group by course_id
                ";
        $sql3 = "
                 select course_id, sum(reading_seconds) as rss
                 from WM_record_daily_course
                 where course_id IN (" . implode(',', $courseIds) . ") 
                 group by course_id
                ";
        break;
}

$al2 = $sysConn->GetArray($sql2);
$al3 = $sysConn->GetArray($sql3);

// 重整其他欄位的資料
switch($_POST['sortby']) {
    // 課程名稱、上課次數、張貼篇數、討論次數、閱讀時間
    case 'name':
    case 'login_times':
    case 'post_times':
    case 'dsc_times':
            $students = array();
            foreach ($al2 as $v) {
                $info[$v['course_id']]['rss'] = $v['rss'];
            }
            
            foreach ($al3 as $v) {
                $students[$v['course_id']]['stud'] = $v['stud'];
                $students[$v['course_id']]['audi'] = $v['audi'];
            }
        break;
    
    case 'rss':
            $students = array();
            foreach ($al2 as $v) {
                $info[$v['course_id']]['caption'] = $v['caption'];
                $info[$v['course_id']]['login_times'] = $v['login_times'];
                $info[$v['course_id']]['post_times'] = $v['post_times'];
                $info[$v['course_id']]['dsc_times'] = $v['dsc_times'];
            }
            
            foreach ($al3 as $v) {
                $students[$v['course_id']]['stud'] = $v['stud'];
                $students[$v['course_id']]['audi'] = $v['audi'];
            }

//                echo '<pre>';
//                var_dump($students);
//                echo '</pre>';
        break;

    // 正式生人數、旁聽生人數
    case 'stud':
    case 'audi':
            $info = array();
            foreach ($al2 as $v) {
                $info[$v['course_id']]['caption'] = $v['caption'];
                $info[$v['course_id']]['login_times'] = $v['login_times'];
                $info[$v['course_id']]['post_times'] = $v['post_times'];
                $info[$v['course_id']]['dsc_times'] = $v['dsc_times'];
            }
            
            foreach ($al3 as $v) {
                $info[$v['course_id']]['rss'] = $v['rss'];
            }

//                echo '<pre>';
//                var_dump($info);
//                echo '</pre>';
        break;
}

//    if ($all && $al2)
//    {
//        foreach ($all as $x => $element)
//        {
//            $all[$x]['stud'] = $al2[$x]['stud'];
//            $all[$x]['audi'] = $al2[$x]['audi'];
//        }
//        
//        if ($_POST['sortby'] === 'name') {
//        } else {
//            unset($al2);
//            $order_idx = $_POST['sortby'];
//            usort($all, 'sort_method');
//            if ($_POST['order']=='desc') $all = array_reverse($all);
//        }
//    }

// 補陣列數值
$datalist = array();
if (is_array($all)){
    foreach($all as $fields){

        switch($_POST['sortby']) {
            // 課程名稱、上課次數、張貼篇數、討論次數、閱讀時間
            case 'name':
            case 'login_times':
            case 'post_times':
            case 'dsc_times':
                $titles = unserialize($fields['caption']);
                $fields['caption'] = "(" . $fields['course_id'] . ") " . htmlspecialchars($titles[$sysSession->lang]);
                
                $fields['rss'] = zero2gray(sec2timestamp($fields['rss']));

                $fields['stud'] = $students[$fields['course_id']]['stud'];
                $fields['audi'] = $students[$fields['course_id']]['audi'];
                break;
                
            case 'rss':
                $titles = unserialize($fields['caption']);
                $fields['caption'] = "(" . $fields['course_id'] . ") " . htmlspecialchars($titles[$sysSession->lang]);
                $fields['rss'] = zero2gray(sec2timestamp($fields['rss']));
                
                $fields['login_times'] = $info[$fields['course_id']]['login_times'];
                $fields['post_times'] = $info[$fields['course_id']]['post_times'];
                $fields['dsc_times'] = $info[$fields['course_id']]['dsc_times'];

                $fields['stud'] = $students[$fields['course_id']]['stud'];
                $fields['audi'] = $students[$fields['course_id']]['audi'];
                break;

            // 正式生人數、旁聽生人數
            case 'stud':
            case 'audi':
                $titles = unserialize($info[$fields['course_id']]['caption']);
                $fields['caption'] = "(" . $fields['course_id'] . ") " . htmlspecialchars($titles[$sysSession->lang]);
                $fields['login_times'] = $info[$fields['course_id']]['login_times'];
                $fields['post_times'] = $info[$fields['course_id']]['post_times'];
                $fields['dsc_times'] = $info[$fields['course_id']]['dsc_times'];
                $fields['rss'] = zero2gray(sec2timestamp($info[$fields['course_id']]['rss']));
                break;
        }

        $datalist[] = $fields;
    }
}

//    echo '<pre>';
//    var_dump($datalist);
//    echo '</pre>';

// 資料更新訊息
$lasttime = getCronDailyLastExecuteTime();
if ($lasttime == 0)
{
    $msgUpdate = $MSG['msg_cron_daily_fail'][$sysSession->lang];
}else{
    $msgUpdate = $MSG['msg_last_updated_time'][$sysSession->lang].'<font color="red">'.$lasttime.'</font>';
}
$smarty->assign('msgUpdate', $msgUpdate);

// 分頁功能
// 計算總共分幾頁
$total_page   = ceil($total_course / $lines);
// 產生下拉換頁選單
$all_page     = range(0, $total_page);
$all_page[0]  = $MSG['page_all'][$sysSession->lang];
// 設定下拉換頁選單顯示第幾頁
$setting_no   = intval(getSetting('page_no'));

//$setting_no = empty($setting_no) ? $total_page : $setting_no;
$page_no = isset($_POST['page']) ? intval($_POST['page']) : $setting_no;
if (($page_no < 0) || ($page_no > $total_page))
    $page_no = $total_page;
if ($page_no == 0)
    $page_no = 1;
saveSetting('page_no', $page_no); // 回存設定

$js = <<< BOF
var total_count = {$total_course};
var page_size = {$lines};
var total_page = {$total_page};
var page_no = {$page_no};
BOF;


// assign
$smarty->assign('sort', $_POST['sortby']);
$smarty->assign('order', $_POST['order']);
$smarty->assign('inlineSchoolJS', $js);
$smarty->assign('datalist', $datalist);

// output
$smarty->display('common/tiny_header.tpl');
$smarty->display('learn/course_ranking.tpl');
$smarty->display('common/tiny_footer.tpl');
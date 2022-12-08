<?php
/**
 * 我的教室
 *
 * @since   2003/06/12
 * @author  ShenTing Lin
 * @version $Id: major.php,v 1.1 2010-02-24 02:39:08 saly Exp $
 * @copyright 2003 SUNNET
 * @備註 : 此支程式專供/learn/mycourse/index.php中所引用
 **/
require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_short_link_lib.php');
include_once(sysDocumentRoot . '/lib/course.php');

if (!aclVerifyPermission(2500100200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$sortby_arr = array(
    'course_id',
    'caption',
    'st_begin',
    'st_end',
    'post',
    'homework',
    'exam',
    'questionnaire',
    'status',
    'role',
    'isTeach',
    'progress'
);
$order_arr  = array(
    'asc',
    'desc'
);

// 更新討論版未讀文章篇數
checkFORUM($sysSession->username); // from /learn/mycourse/modules/mod_short_link_lib.php

$sortby = $_POST['sortby'];
$order  = empty($_POST['order']) ? getSetting('cour_major_order') : $_POST['order'];
$userTicket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->ticket . $sysSession->school_id);

foreach ($sortby_arr as $k => $v) {
    if ($v == $sortby) {
        $sortby_idx = $k;
        break;
    }
}

function getSortLink($msg, $type)
{
    global $icon_up, $icon_dn, $sortby, $order;
    $rtn = '<a class="cssAnchor" href="javascript:;" onclick="return false;">' . $msg;
    if ($type == $sortby)
        $rtn .= $order == 'desc' ? $icon_dn : $icon_up;
    return $rtn . '</a>';
}

// 課程名稱
$course_name = ($_POST['course_name'] == $MSG['msg_course1'][$sysSession->lang]) ? '' : trim(strip_tags(stripslashes($_POST['course_name'])));
$smarty->assign('course_name', $course_name);

// 取得教師身份列表
$teach = dbGetAssoc('WM_term_major', 
        'course_id, role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ' as level', 
        "username='{$sysSession->username}' and role&" . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']));

$courses  = array();
// 取出群組中修課的課程編號
$group_id = intval(getSetting('group_id'));
if (($status == 0) || ($status == 2)) {
    $group_id = 10000000;
    saveSetting('group_id', $group_id);
    saveSetting('page_no', '1');
}
if ($group_id <= 10000000) {
    $RS = dbGetCourses('C.course_id, C.caption, C.st_begin, C.st_end, C.status, M.role, M.post, M.hw, M.qp, M.exam', 
            $sysSession->username, $sysRoles['auditor'] | $sysRoles['student'] | $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'], 
            'C.course_id desc');
} else {
    // 取得課程群組中選修的課程
    // 利用字串取代，帳號：%USERNAME%，群組編號：%GROUP_ID%
    $sqls             = 'SELECT CS.`course_id`, CS.`caption`, CS.`st_begin`, CS.`st_end`, CS.`status`, ' . 
            'MJ.`role`, MJ.`post`, MJ.`hw`, MJ.`qp`, MJ.`exam` ' . 
            'FROM `WM_term_group` AS GP, `WM_term_major` AS MJ ' . 'LEFT JOIN `WM_term_course` AS CS ON CS.`course_id` = MJ.`course_id` ' . 
            'WHERE GP.`parent`=%GROUP_ID% AND MJ.`username`="%USERNAME%" ' . 
            ' AND CS.status<9 ' . 
            ' AND MJ.`course_id`=GP.`child` ORDER BY CS.course_id desc, GP.`permute` ASC';
    $sqls             = str_replace(array(
        '%USERNAME%',
        '%GROUP_ID%'
    ), array(
        $sysSession->username,
        $group_id
    ), $sqls);
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    $RS               = $sysConn->Execute($sqls);
}

// 三合一的種類
$type_array = array(
    'homework',
    'exam',
    'questionnaire'
);

if ($RS) {
    while (!$RS->EOF) {
        $csid = intval($RS->fields['course_id']);
        $stas = intval($RS->fields['status']);
        if (empty($csid) || empty($RS->fields['role']) || ($stas == 0)) {
            $RS->MoveNext();
            continue;
        }
        $isTeach = ($RS->fields['role'] & $sysRoles['teacher']);
        $isTeach = ($isTeach || ($RS->fields['role'] & $sysRoles['instructor']));
        $isTeach = ($isTeach || ($RS->fields['role'] & $sysRoles['assistant']));
        if (($stas == 5) && !$isTeach) { // 判斷如果課程狀態為準備中則只限教師才可看到
            $RS->MoveNext();
            continue;
        }
        
        $role      = $RS->fields['role'];
        $isStudent = ($role & $sysRoles['student']); // 判斷是否為正式生
        
        // 儲存三合一的未繳作業  未寫考卷  未填問卷
        $QTI_undo = array();
        
        for ($q_i = 0; $q_i < count($type_array); $q_i++) {
            $QTI_undo[$type_array[$q_i]] = 0;
            $ary = checkQTI($username, $type_array[$q_i], $csid);
            $undo = 0;
            if (is_array($ary) && count($ary))
            {
                foreach($ary as $cids) {
                    $undo += $cids['total_undo'];
                }
                
                if($type_array[$q_i] == 'homework') {
                    $peer_ary = checkQTI($sysSession->username, 'peer', $csid);
                    foreach ($peer_ary as $k => $v) {
                        $undo += $v['total_undo'];
                    }
                }
                
                $QTI_undo[$type_array[$q_i]] = $undo;
            }
        }
        
        $ary = array();
        if ($RS->fields['role'] & $sysRoles['teacher'])
            $ary[] = '<div>' . $MSG['teacher'][$sysSession->lang] . '</div>';
        if ($RS->fields['role'] & $sysRoles['instructor'])
            $ary[] = '<div>' . $MSG['instructor'][$sysSession->lang] . '</div>';
        if ($RS->fields['role'] & $sysRoles['assistant'])
            $ary[] = '<div>' . $MSG['assistant'][$sysSession->lang] . '</div>';
        if ($RS->fields['role'] & $sysRoles['student'])
            $ary[] = '<div>' . $MSG['student'][$sysSession->lang] . '</div>';
        if ($RS->fields['role'] & $sysRoles['auditor']) {
            $ary[] = sprintf('<div>%s &nbsp;<input type="button" name="btnDropCourse" value="%s" class="btn" onclick="if(confirm(\'%s\')){drop_elective(\'%s\',\'drop_elective\',\'%s\')}"; /></div>',
                $MSG['auditor'][$sysSession->lang],
                $MSG['btn_drop_elective'][$sysSession->lang],
                $MSG['msg_drop_elective'][$sysSession->lang],
                $userTicket,
                $csid
            );
        }
        
        $courses[] = array(
            $csid,
            fetchTitle($RS->fields['caption']),
            $RS->fields['st_begin'],
            $RS->fields['st_end'],
            $RS->fields['post'],
            ($QTI_undo['homework']) ? $QTI_undo['homework'] : 0,
            ($QTI_undo['exam']) ? $QTI_undo['exam'] : 0,
            ($QTI_undo['questionnaire']) ? $QTI_undo['questionnaire'] : 0,
            $stas,
            implode('', $ary),
            $isTeach
        );
        $RS->MoveNext();
    }
    
    if (!empty($course_name)) {
    	$new_course = array();
	    foreach ($courses as $key => $val) {
	        if (strpos($courses[$key][1], htmlspecialchars($course_name)) !== false) {
	            $new_course[] = $val;
	        } 
	    } 
	    unset($courses);
	    $courses = $new_course;
    }

}

// 用來對課程作排序
function cmp($a, $b)
{
    global $sortby_idx, $order;
    if ($a[$sortby_idx] == $b[$sortby_idx])
        return 0;
    if ($order == 'desc')
        return $a[$sortby_idx] > $b[$sortby_idx] ? -1 : 1;
    else
        return $a[$sortby_idx] > $b[$sortby_idx] ? 1 : -1;
}

// 對課程作排序
if (in_array($sortby, $sortby_arr) && in_array($order, $order_arr)) {
    usort($courses, 'cmp');
    $courses = array_values($courses); // 重建keys
}

$cnt          = count($courses);
// 計算全部的課程數
$total_course = $cnt;
// 計算總共分幾頁
$total_page   = ceil($total_course / $lines);
// 產生下拉換頁選單
$all_page     = range(0, $total_page);
$all_page[0]  = $MSG['page_all'][$sysSession->lang];
// 設定下拉換頁選單顯示第幾頁
$setting_no   = intval(getSetting('page_no'));
//$setting_no = empty($setting_no) ? $total_page : $setting_no;
$page_no      = isset($_POST['page']) ? intval($_POST['page']) : $setting_no;
if (($page_no < 0) || ($page_no > $total_page))
    $page_no = $total_page;
    if ($page_no == 0) $page_no = 1;
saveSetting('page_no', $page_no); // 回存設定

$js = <<< BOF
var MSG_SYS_ERROR     = "{$MSG['msg_system_error'][$sysSession->lang]}";
var MSG_DROP_SUCCESS  = "{$MSG['drop_elective_success'][$sysSession->lang]}";
var MSG_DROP_FAIL  = "{$MSG['drop_elective_fail'][$sysSession->lang]}";
var total_count = {$total_course};
var page_size = {$lines};
var total_page={$total_page};
var page_no = {$page_no};

// 用來排序的function
function chgPageSort(sortby) {
    var obj = document.getElementById('actFm');

    if ((typeof(obj) != "object") || (obj == null)) return false;
    if (obj.sortby.value != sortby) {
        obj.order.value = 'asc';
        obj.sortby.value = sortby;
    } else
        obj.order.value = obj.order.value == 'asc' ? 'desc' : 'asc';
    obj.submit();
}

function CancelQuery(){
	obj  = document.getElementById("actFm");
	obj.course_name.value = '';
	
	window.onunload = function () {};
	obj.submit();
}

/**
 * 搜尋課程
 *
 **/
function queryCourse() {
	var obj = null;
	// course name keyword
	obj = document.getElementById("cour_keyword");
	var cour_key = (obj == null) ? "" : obj.value;

	obj  = document.getElementById("actFm");
	obj.course_name.value = cour_key;
	obj.isquery.value = "true";
	window.onunload = function () {};
	obj.submit();
}

BOF;

if ($page_no == 0) {
    $begin = 0;
    $end   = $cnt;
} else {
    $begin = intval($page_no - 1) * $lines;
    $end   = intval($page_no) * $lines;
    if ($begin < 0)
        $begin = 0;
    if ($end > $cnt)
        $end = $cnt;
}

switch ($sysSession->env) {
    case 'teach':
        $nEnv = 2;
        break;
    case 'direct':
        $nEnv = 3;
        break;
    case 'acdemic':
        $nEnv = 4;
        break;
    default:
        $nEnv = 1;
}
$smarty->assign('nEnv', $nEnv);

$datalist = array();
for ($i = $begin; $i < $end; $i++) {
    $datalist[] = $courses[$i];
}
$smarty->assign('sort', $sortby);
$smarty->assign('order', $order);
$smarty->assign('inlineMajorJS', $js);
$smarty->assign('datalist', $datalist);

<?php
/**
 * 全校課程
 *
 * @since   2003/06/12
 * @author  ShenTing Lin
 * @version $Id: school.php,v 1.1 2010-02-24 02:39:08 saly Exp $
 * @copyright 2003 SUNNET
 * @備註 : 此支程式專供/learn/mycourse/index.php中所引用
 **/

if (!aclVerifyPermission(2500300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

/**
 * 取得群組中課程
 * @param integer $gid : 群組編號
 * @return array $res
 **/
function csGetCsList($gid, $lines)
{
//    global $sysConn;
//    $sysConn->debug = true;
    
    if (isset($_POST['page']) === FALSE || empty($_POST['page']) === TRUE) {
        $_POST['page'] = 1;
    }
    
    if ($gid == 10000000) // 全校課程
        {
        // eader('Location: /learn/index.php');
        // exit();
        return dbGetAssoc('WM_term_course', 'SQL_CALC_FOUND_ROWS `course_id`, `caption`, `teacher`, `kind`, `en_begin`, `en_end`, `st_begin`, `st_end`, `status`, `n_limit`, `a_limit`', 'kind="course" and (status IN (1, 3) or (status IN (2, 4) and (now() >= st_begin OR st_begin = "0000-00-00 00:00:00" OR st_begin IS NULL) and (now() <= st_end OR st_end = "0000-00-00 00:00:00" OR st_end IS NULL))) AND caption LIKE "%' . htmlspecialchars($_POST['course_name']) . '%" order by course_id DESC LIMIT ' . ($_POST['page'] - 1) * $lines .  ', ' . $lines, ADODB_FETCH_ASSOC);
    } else if ($gid > 10000000) // 群組內課程
        {
        return dbGetAssoc('WM_term_course as B left join WM_term_group as G on B.course_id=G.child', 'SQL_CALC_FOUND_ROWS B.*', 'G.parent=' . $gid . ' and B.kind="course" and (B.status IN (1, 3) or (B.status IN (2, 4) and (now() >= st_begin OR st_begin = "0000-00-00 00:00:00" OR st_begin IS NULL) and (now() <= st_end OR st_end = "0000-00-00 00:00:00" OR st_end IS NULL))) AND caption LIKE "%' . htmlspecialchars($_POST['course_name']) . '%" order by B.course_id desc LIMIT ' . ($_POST['page'] - 1) * $lines .  ', ' . $lines, ADODB_FETCH_ASSOC);
    }
//    global $sysConn;
//    $sysConn->debug = 0;
}

// 判斷是否允許 guest 登入
list($guest_login) = dbGetStSr('WM_school', 'guest', 'school_id=' . $sysSession->school_id . ' and school_host="' . $_SERVER['HTTP_HOST'] . '"', ADODB_FETCH_NUM);

$sysConn->Execute('use ' . sysDBschool);

/*
$c_member = $sysConn->GetAssoc('select concat(course_id, "_' . $sysRoles['student'] . '"), count(*) from WM_term_major where role & ' . $sysRoles['student'] . ' group by course_id
                                       union
                                       select concat(course_id, "_' . $sysRoles['auditor'] . '"), count(*) from WM_term_major where role & ' . $sysRoles['auditor'] . ' group by course_id');
*/

function detailCheck($val)
{
    global $sysSession, $sysConn, $sysRoles;
    
    // 取得今天的日期
    $today = date('Ymd', time());
    
    // 旁聽生目前修課人數
//    $ap = $c_member[$val['course_id'] . '_' . $sysRoles['auditor']];
     list($ap) = dbGetStSr('WM_term_major', 'count(course_id)', "`course_id`={$val['course_id']} && (`role` & {$sysRoles['auditor']})");
    // 正式生目前修課人數
//    $sp = $c_member[$val['course_id'] . '_' . $sysRoles['student']];
     list($sp) = dbGetStSr('WM_term_major', 'count(course_id)', "`course_id`={$val['course_id']} && (`role` & {$sysRoles['student']})");
    // 報名狀態
    
    $b  = 0;
    $e  = 0;
    $en = 0;
    if (!empty($val['en_begin']) && $val['en_begin'] != '0000-00-00') {
        $t = $sysConn->UnixDate($val['en_begin']);
        $b = date('Ymd', $t);
        if (intval($today) < intval($b))
            $en = 1; // 尚未開始
    }
    if (!empty($val['en_end']) && $val['en_end'] != '0000-00-00') {
        $t = $sysConn->UnixDate($val['en_end']);
        $e = date('Ymd', $t);
        if (intval($today) > intval($e))
            $en = 3; // 已經結束
    }
    if (!empty($b) && !empty($e) && empty($en))
        $en = 2;
    
    // 上課狀態
    $b  = 0;
    $e  = 0;
    $st = 0;
    if (!empty($val['st_begin']) && $val['st_begin'] != '0000-00-00') {
        $t = $sysConn->UnixDate($val['st_begin']);
        $b = date('Ymd', $t);
        if (intval($today) < intval($b))
            $st = 1; // 尚未開始
    }
    if (!empty($val['st_end']) && $val['st_end'] != '0000-00-00') {
        $t = $sysConn->UnixDate($val['st_end']);
        $e = date('Ymd', $t);
        if (intval($today) > intval($e))
            $st = 3; // 已經結束
    }
    if (!empty($b) && !empty($e) && empty($st))
        $st = 2;
    
    $val['ap'] = (empty($val['a_limit'])) ? true : (intval($val['a_limit']) > intval($ap));
    $val['sp'] = (empty($val['n_limit'])) ? true : (intval($val['n_limit']) > intval($sp));
    $val['st'] = $st;
    $val['en'] = $en;
    
    return $val;
}

// 取出全校課程中的課程編號
$group_id = intval(getSetting('group_id'));
if (($status == 0) || ($status == 2)) {
    $group_id = 10000000;
    saveSetting('group_id', $group_id);
    saveSetting('page_no', '1');
}

$courses = array();
$csary   = csGetCsList($group_id, $lines);
$total_course = intval($sysConn->GetOne("SELECT FOUND_ROWS()"));

// 課程名稱
$course_name = ($_POST['course_name'] == $MSG['msg_course1'][$sysSession->lang]) ? '' : trim(strip_tags(stripslashes($_POST['course_name'])));

if (empty($course_name)) {
    if (is_array($csary)) {
        foreach ($csary as $cid => $val) {
            if (($val['status'] == 0) || ($val['status'] == 5) || ($val['status'] == 9))
                continue;
            $val['course_id'] = $cid;

            // 檢查課程狀態
            $val = detailCheck($val);
    //        if (($val['status'] == 2) || ($val['status'] == 4)) {
    //            // 檢查課程是否過期
    //            if ($val['st'] == 3)
    //                continue;
    //        }

            $courses[] = $val;
        }
    }
} else {
    // 搜尋課程
    if (is_array($csary)) {
        foreach ($csary as $cid => $val) {
            // 基本的課程狀態檢查
            if (($val['status'] == 0) || ($val['status'] == 5) || ($val['status'] == 9))
                continue;
            $val['course_id'] = $cid;

            // 檢查課程狀態
            $val = detailCheck($val);
    //        if (($val['status'] == 2) || ($val['status'] == 4)) {
    //            // 檢查課程是否過期
    //            if ($val['st'] == 3)
    //                continue;
    //        }

            // 課程名稱
            $lang1  = getCaption($val['caption']);
            $title1 = $lang1[$sysSession->lang];
            if (!empty($course_name)) {
                if (strpos($title1, htmlspecialchars($course_name)) === false) {
                    continue;
                }
            }
            $courses[] = $val;
        }
    }
}
//echo '<pre>';
//var_dump($courses);
//echo '</pre>';
//自訂排序，以課程名稱排序,由小至大
function cmp($a, $b)
{
    global $sysSession;
    $tmp  = unserialize($a['caption']);
    $cap1 = $tmp[$sysSession->lang];
    $tmp  = unserialize($b['caption']);
    $cap2 = $tmp[$sysSession->lang];
    return strcmp($cap1, $cap2);
}

//自訂排序，以課程名稱排序，由大至小
function cmp1($a, $b)
{
    global $sysSession;
    $tmp  = unserialize($a['caption']);
    $cap1 = $tmp[$sysSession->lang];
    $tmp  = unserialize($b['caption']);
    $cap2 = $tmp[$sysSession->lang];
    return strcmp($cap2, $cap1);
}
if ($_POST['CourseName_SORT'] == 1) {
    usort($courses, "cmp");
} else if ($_POST['CourseName_SORT'] == 2) {
    usort($courses, "cmp1");
}

$cnt = count($courses);

// 計算全部的課程數
//$total_course = $cnt;

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
	var total_page={$total_page};
	var page_no = {$page_no};
	var show_img_title   = "{$MSG['show_img_title'][$sysSession->lang]}";
	var hidden_img_title = "{$MSG['hidden_img_title'][$sysSession->lang]}";

	var cour_txt         = "{$MSG['msg_course1'][$sysSession->lang]}";
	var teach_txt        = "{$MSG['msg_course4'][$sysSession->lang]}";

	var AuditWin = null;
	function add_audit(cour_id){
		if ((AuditWin != null) && !AuditWin.closed) {
			AuditWin.focus();
		} else {
			// AuditWin = showDialog("audit_course.php?cour_id="+cour_id, false , "", true, "200px", "300px", "400px", "300px", "status=0, resizable=1, scrollbars=0");
			var appearance = "toolbar=0, location=0, directories=0, top=200, left=200, width=450, height=200, resizable=0, scrollbars=0";
			window.open("audit_course.php?cour_id="+cour_id,'AuditWin',appearance);
		}
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

//if ($page_no == 0) {
//    $begin = 0;
//    $end   = $cnt;
//} else {
//    $begin = intval($page_no - 1) * $lines;
//    $end   = intval($page_no) * $lines;
//    if ($begin < 0)
//        $begin = 0;
//    if ($end > $cnt)
//        $end = $cnt;
//}

$begin = 0;
$end = $cnt;

$datalist = array();
for ($i = $begin; $i < $end; $i++) {
    $val            = $courses[$i];
    $val['caption'] = fetchTitle(getCaption($val['caption']));
    $en             = $MSG['from2'][$sysSession->lang] . (empty($val['en_begin']) ? $MSG['now'][$sysSession->lang] : $val['en_begin']) . '<br>' . $MSG['to2'][$sysSession->lang] . (empty($val['en_end']) ? $MSG['forever'][$sysSession->lang] : $val['en_end']);
    $val['enroll']  = $en;
    $st             = $MSG['from2'][$sysSession->lang] . (empty($val['st_begin']) ? $MSG['now'][$sysSession->lang] : $val['st_begin']) . '<br>' . $MSG['to2'][$sysSession->lang] . (empty($val['st_end']) ? $MSG['forever'][$sysSession->lang] : $val['st_end']);
    $val['study']   = $st;
    // 旁聽
    $msg            = '&nbsp;';
    if (aclCheckRole($sysSession->username, $sysRoles['auditor'], $val['course_id'])) {
        $msg = $MSG['msg_help_audit_be'][$sysSession->lang];
    } else if (aclCheckRole($sysSession->username, $sysRoles['student'], $val['course_id'])) {
        $msg = '-';
    } else if ((intval($val['status']) == 3) || (intval($val['status']) == 4)) {
        $msg = $MSG['msg_help_audit_no'][$sysSession->lang];
    } else if ($sysSession->username != 'guest') {
        $msg = ($val['ap']) ? '<a id="winAudit_' . $val['course_id'] . '" data-fancybox-type="iframe" href="audit_course.php?cour_id=' . $val['course_id'] . '" onclick="javascript:openAuditWindow(' . $val['course_id'] . ');" class="cssAnchor">' . $MSG['msg_help_audit_yes'][$sysSession->lang] . '</a>' : $MSG['msg_help_audit_full'][$sysSession->lang];
    } else {
        $msg = ($val['ap']) ? $MSG['msg_help_audit_yes'][$sysSession->lang] : $MSG['msg_help_audit_full'][$sysSession->lang];
    }
    $val['auditHelp'] = $msg;
    
    // 報名
    $msg = '&nbsp;';
    if (aclCheckRole($sysSession->username, $sysRoles['student'], $val['course_id'])) {
        $msg = $MSG['msg_help_enroll_be'][$sysSession->lang];
    } else if (!$val['sp']) {
        $msg = $MSG['msg_help_enroll_full'][$sysSession->lang];
    } else {
        switch ($val['en']) {
            case 0:
            case 2:
                // $msg = $MSG['msg_help_enroll_open'][$sysSession->lang];
                $msg = '<a id="winEnploy_' . $val['course_id'] . '" data-fancybox-type="iframe" href="enploy_course.php?cour_id=' . $val['course_id'] . '" onclick="javascript:openEnployWindow(' . $val['course_id'] . ');" class="cssAnchor">' . $MSG['msg_help_enroll_open'][$sysSession->lang] . '</a>';
                break;
            case 1:
                $msg = $MSG['msg_help_enroll_begin'][$sysSession->lang];
                break;
            case 3:
                $msg = $MSG['msg_help_enroll_end'][$sysSession->lang];
                break;
        }
    }
    $val['enrollHelp'] = $msg;
    // $detail = '<a href="javascript:;" onclick="showDetail(' . $val['course_id'] . '); return false;">' . $icon . '</a>';
    $datalist[]        = $val;
}

$smarty->assign('course_name', $course_name);
$smarty->assign('inlineSchoolJS', $js);
$smarty->assign('datalist', $datalist);
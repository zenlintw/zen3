<?php
/**************************************************************************************************
 *                                                                                                *
 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *		Programmer: Wiseguy Liang                                                         *
 *		Creation  : 2003/03/21                                                            *
 *		work for  : Remove exam and relative result                                       *
 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/teach/grade/grade_recal.php'); // 重新計算成績

//ACL begin
if (QTI_which == 'exam') {
    $sysSession->cur_func = '1600200300';
} else if (QTI_which == 'homework') {
    $sysSession->cur_func = '1700200300';
} else if (QTI_which == 'questionnaire') {
    $sysSession->cur_func = '1800200300';
} else if (QTI_which == 'peer') {
    $sysSession->cur_func = '1710200300';
}
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    
}
//ACL end

if (!defined('QTI_env'))
    list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
else
    $topDir = QTI_env;

$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

if ($topDir == 'academic')
    $source = sprintf(sysDocumentRoot . '/base/%05d/%s/A/', $sysSession->school_id, QTI_which);
else
    $source = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/', $sysSession->school_id, $sysSession->course_id, QTI_which);

if (!isset($_POST['ticket'])) { // 檢查 ticket 是否存在
    wmSysLog($sysSession->cur_func, $course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
    die('Access denied.');
}
$ticket = md5(sysTicketSeed . $course_id . $_POST['referer']); // 產生 ticket
if ($ticket != $_POST['ticket']) { // 檢查 ticket
    wmSysLog($sysSession->cur_func, $course_id, 0, 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
    die('Fake ticket.');
}

// 資安處理
$_POST['lists'] = htmlspecialchars($_POST['lists']);

if (!ereg('^[0-9]+(,[0-9]+)*$', $_POST['lists'])) { // 檢查 lists
    wmSysLog($sysSession->cur_func, $course_id, 0, 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
    die('Fake lists.');
}

if (basename($_SERVER['PHP_SELF']) == 'exam_remove.php') {
    chkSchoolId('WM_history_qti_' . QTI_which . '_result');
    $sysConn->Execute(sprintf('insert into WM_history_qti_%s_result select NULL, R.* from WM_qti_%s_result as R where exam_id in (%s)', QTI_which, QTI_which, $_POST['lists']));
    dbDel('WM_qti_' . QTI_which . '_test', "exam_id in ({$_POST['lists']})");
    wmSysLog($sysSession->cur_func, $course_id, 0, 0, 'auto', $_SERVER['PHP_SELF'], 'remove ' . QTI_which . ' test:' . $_POST['lists']);
}

dbDel('WM_qti_' . QTI_which . '_result', "exam_id in ({$_POST['lists']})");

// 清除續考資料
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'WM_qti_" . QTI_which . "_result_extra'")) === 1) {
    dbDel('WM_qti_' . QTI_which . '_result_extra', "exam_id in ({$_POST['lists']})");
}

// 同步刪除成績 begin
$rsGradeList    = dbGetStMr('WM_grade_list', 'grade_id, source', "course_id = {$course_id} and property in ({$_POST['lists']})", ADODB_FETCH_ASSOC);
$grade_peer_ids = array();
$grade_exam_ids = array();
$grade_ids      = array();
if ($rsGradeList) {
    while (!$rsGradeList->EOF) {
        if ($rsGradeList->fields['source'] === '4') {
            $grade_peer_ids[] = $rsGradeList->fields['grade_id'];
        } else {
            $grade_exam_ids[] = $rsGradeList->fields['grade_id'];
        }
        $grade_ids[] = $rsGradeList->fields['grade_id'];
        
        $rsGradeList->MoveNext();
    }
}
if (is_array($grade_ids) and count($grade_ids)) {
    if (count($grade_peer_ids) >= 1) {
//        dbSet('WM_grade_list', "publish_begin = '0000-00-00 00:00:00', publish_end = '0000-00-00 00:00:00'", 'grade_id in (' . implode(',', $grade_peer_ids) . ')');
          if (!$peer_clear) { 
              dbDel('WM_grade_list', 'grade_id in (' . implode(',', $grade_peer_ids) . ')');
          }
    }
    if (count($grade_exam_ids) >= 1) {
        dbDel('WM_grade_list', 'grade_id in (' . implode(',', $grade_exam_ids) . ')');
    }
    dbDel('WM_grade_item', 'grade_id in (' . implode(',', $grade_ids) . ')');
    reCalculateGrades($course_id);
}
/* #56148 (B) [MOOCs] 清除自評及互評的成績 By Spring */
if (QTI_which == 'peer') {
    if (count($grade_peer_ids) >= 1) {
        // 清除互評自評成績
        dbDel('WM_qti_' . QTI_which . '_result_score', "exam_id in ({$_POST['lists']})");
        // 清除評量表互評自評成績
        dbDel('WM_qti_' . QTI_which . '_result_eva', "exam_id in ({$_POST['lists']})");
        // 清除評分lock
        dbDel('WM_qti_' . QTI_which . '_result_action', "exam_id in ({$_POST['lists']})");
    }
}
/* #56148 (E) [MOOCs] */
// 同步刪除成績 end

wmSysLog($sysSession->cur_func, $course_id, 0, 0, 'auto', $_SERVER['PHP_SELF'], 'remove ' . QTI_which . ' results and grades:' . $_POST['lists']);
if (@chdir($source))
    foreach (explode(',', $_POST['lists']) as $eid) {
        $eid = sprintf('%09u', $eid);
        if (is_dir("./$eid"))
            exec("rm -rf './$eid'");
    }
    
    $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which);
			 
	if (@chdir($xml_path))
	foreach(explode(',', $_POST['lists']) as $eid)
	{
		$eid = sprintf('%09u', $eid);
		if (is_dir("./$eid")) exec("rm -rf './$eid'");
	}

if (basename($_SERVER['PHP_SELF']) == 'exam_reset.php') // 清除作答紀錄
    {
    header('Location: exam_maintain.php' . ($_POST['referer'] ? "?{$_POST['referer']}" : ''));
    exit;
}

// 同步刪除行事曆 begin

    $calendar_begin_type = QTI_which . '_begin';
    $calendar_end_type   = QTI_which . '_end';
    $calendar_delay_type = QTI_which . '_delay';
    $calendar_ids        = $sysConn->GetCol("select idx from WM_calendar where (relative_type='{$calendar_begin_type}' or relative_type='{$calendar_end_type}' or relative_type='{$calendar_delay_type}') and relative_id in ({$_POST['lists']})");
    if (is_array($calendar_ids) && count($calendar_ids))
        dbDel('WM_calendar', 'idx in (' . implode(',', $calendar_ids) . ')');

// 同步刪除行事曆 end

// 同步刪除 ACL  begin
$acl_lists = array();
switch (QTI_which) {
    case 'homework':
        $acl_lists = dbGetCol('WM_acl_list', 'acl_id', "function_id in (1700400200,1700300100) and unit_id={$course_id} and instance in ({$_POST['lists']})");
        break;
    
    case 'exam':
        $acl_lists = dbGetCol('WM_acl_list', 'acl_id', "function_id in (1600400200,1600300100) and unit_id={$course_id} and instance in ({$_POST['lists']})");
        break;
    
    case 'questionnaire':
        $acl_lists = dbGetCol('WM_acl_list', 'acl_id', "function_id=1800300200 and unit_id={$course_id} and instance in ({$_POST['lists']})");
        break;
    
    case 'peer':
        $acl_lists = dbGetCol('WM_acl_list', 'acl_id', "function_id in (1710400200,1710300100) and unit_id={$course_id} and instance in ({$_POST['lists']})");
        break;
}

if ($acl_lists) {
    dbDel('WM_acl_member', 'acl_id in (' . implode(',', $acl_lists) . ')');
    dbDel('WM_acl_list', 'acl_id in (' . implode(',', $acl_lists) . ')');
}
// 同步刪除 ACL  end


header('Location: exam_maintain.php' . ($_POST['referer'] ? "?{$_POST['referer']}" : ''));
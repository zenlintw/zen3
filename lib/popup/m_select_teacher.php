<?php
/**
 * 選擇講師 (列表)
 *
 * @since   2005/11/30
 * @author  Hubert
 * @version $Id: select_teacher.php,v 1.1 2009-06-25 09:27:28 edi Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lang/select_teacher.php');
require_once(sysDocumentRoot . '/lib/username.php');

if ($sysSession->env == 'academic') {
    if (!aclCheckRole($sysSession->username, ($sysRoles['manager']|$sysRoles['administrator']|$sysRoles['root']), $sysSession->school_id, false)){
        die('You are not the system manager.');
    }
}else if ($sysSession->env == 'teach') {
    if (!aclCheckRole($sysSession->username, ($sysRoles['teacher']), $sysSession->course_id, false)){
        die('You are not the system manager.');
    }
}else{
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// $sysConn->debug=true;
$lines     = sysPostPerPage;
// 計算總共有幾筆資料
$where     = '';
$isManager = ((aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id) > 0) && $sysSession->env === 'academic') ? true : false;
$sWord     = trim($_POST['keyword']);
if (isset($_GET['cid']) === true && strlen($_GET['cid']) === 8) {
    $cid = htmlspecialchars($_GET['cid']);
} else {
    $cid = htmlspecialchars($sysSession->course_id);
}
if (isset($sWord) && strcmp($sWord, $MSG['msg_title05'][$sysSession->lang]) != 0) {
    // 搜尋所有該課學生或是所有帳號
    $table = '`WM_term_major` as m INNER JOIN `WM_user_account` as a ON m.`username` = a.`username` AND m.`course_id` = ' . $cid;
    if ($_POST['stype'] === '0') {
        // 取得該門課學生
        if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
            $where = sprintf('and (UPPER(CONCAT(a.`last_name`, a.`first_name`)) like "%%%s%%"', strtoupper(escape_LIKE_query_str(addslashes($sWord))));
        } else {
            $where = sprintf('and (UPPER(CONCAT(a.`first_name`, " ", a.`last_name`)) like "%%%s%%"', strtoupper(escape_LIKE_query_str(addslashes($sWord))));
        }
        $where .= ' OR UPPER(a.username) like "%' . strtoupper(escape_LIKE_query_str(addslashes($sWord))) . '%") ';
    } else if ($_POST['stype'] === '1') {
        $table = '`WM_user_account` as a left join WM_term_major m on a.username = m.username AND m.course_id = ' . $cid;
        if ($isManager) {
            // 管理員可用 like 查詢
            if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
                $where = sprintf('and (UPPER(CONCAT(a.`last_name`, a.`first_name`)) like "%%%s%%"', strtoupper(escape_LIKE_query_str(addslashes($sWord))));
            } else {
                $where = sprintf('and (UPPER(CONCAT(a.`first_name`, " ", a.`last_name`)) like "%%%s%%"', strtoupper(escape_LIKE_query_str(addslashes($sWord))));
            }
            $where .= ' OR UPPER(a.username) like "%' . strtoupper(escape_LIKE_query_str(addslashes($sWord))) . '%") ';
        } else {
            // 教師對所有帳號需完整帳號查詢
            $where = 'AND a.username = "' . addslashes($sWord) . '" ';
        }
    }
    
    
    
    list($total_msg) = dbGetStSr($table, 'count(*) AS total', 'a.`username` !="' . sysRootAccount . '" ' . $where, ADODB_FETCH_NUM);
    
    // 計算總共分幾頁
    $total_page = ceil($total_msg / $lines);
    
    // 產生下拉換頁選單
    $all_page    = range(0, $total_page);
    $all_page[0] = $MSG['all'][$sysSession->lang];
    
    // 設定下拉換頁選單顯示第幾頁
    $page_no = (isset($_POST['page']) && $_POST['page'] != '' && $_POST['page'] != '0') ? intval($_POST['page']) : 1;
    if (($page_no < 0) || ($page_no > $total_page))
        $page_no = $total_page;
    
    if ($page_no > 0) {
        $limit = ' limit ' . (($page_no - 1) * $lines) . ',' . $lines;
    }
    
    if ($table != '') {
        $RS = dbGetStMr($table, 'a.`username`, a.`first_name`, a.`last_name`, m.role&64 isAssistant, m.role&128 isInstructor, m.role&512 isTeacher', 'a.`username` != "' . sysRootAccount . '" ' . $where . ' order by username asc ' . $limit, ADODB_FETCH_ASSOC);
    }
    if ($RS) {
        while (!$RS->EOF) {
            $realname           = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
            $data['username']   = $RS->fields['username'];
            $data['realname']   = htmlspecialchars($realname);
            $data['first_name'] = $RS->fields['first_name'];
            $data['last_name']  = $RS->fields['last_name'];

//            echo '<pre>';
//            var_dump($_GET['func']);
//            var_dump($RS->fields['isAssistant']);
//            var_dump($RS->fields['isInstructor']);
//            var_dump($RS->fields['isTeacher']);
//            echo '</pre>';
            switch ($_GET['func']) {
                case 'setAssistantValue':
                    if ($RS->fields['isTeacher'] >= '1' || $RS->fields['isInstructor'] >= '1') {
                        $data['chkFlag'] = '0';
                    } else {
                        $data['chkFlag'] = '1';
                    }
                    break;
                
                case 'setInstructorValue':
                    if ($RS->fields['isTeacher'] >= '1') {
                        $data['chkFlag'] = '0';
                    } else {
                        $data['chkFlag'] = '1';
                    }
                    break;
                
                case 'setTeacherValue':
                    $data['chkFlag'] = '1';
                    break;
            }
            
            $userData[]         = $data;
            $RS->MoveNext();
        }
    }
}


// 開啟 select_teacher 前的預設值
if (isset($_POST['selected_items']) && $_POST['selected_items'] !== '') {
    $_POST["save_tags"] = $_POST['selected_items'];
}
// 之前選取過的使用者
$savedUser = json_decode(urldecode($_POST["save_tags"]));

if (count($savedUser) > 0) {
    foreach ($savedUser as $key => $val) {
        $selectedUser[] = $val->value;
    }
}

$smarty->assign('total_page', $total_page);
$smarty->assign('total_msg', $total_msg);
$smarty->assign('page_no', $page_no);
$smarty->assign('lines', $lines);
$smarty->assign('sWord', $sWord);
$smarty->assign('sType', (intval($_POST['stype']) !== '') ? intval($_POST['stype']) : 0);
$smarty->assign('isManager', ($isManager === true) ? 'true' : 'false');

$smarty->assign('selectedUser', $selectedUser);
$smarty->assign('saveTags', $_POST["save_tags"]);
$smarty->assign('data', $userData);
$smarty->assign('frmAction', $_SERVER['REQUEST_URI']);
$smarty->display('common/tiny_header.tpl');
$smarty->display('common/m_select_teacher.tpl');
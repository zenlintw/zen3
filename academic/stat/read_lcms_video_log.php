<?php
/**
 * 平台統計資料 - 閱讀lcms影片教材行為紀錄
 *
 * 建立日期：2017/11/8
 * @author：cch
 * @version $Id: read_lcms_video_log.php, v 1.0 2017/11/8 東穎 Exp $
 * @copyright 2017 SUNNET
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/sch_statistics.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

$sysSession->cur_func = '1500200100';
$sysSession->restore();
if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable, visible, readable, writable, modifiable, uploadable, removable'))) {
}

//$ticket = md5(sysTicketSeed . $sysSession->username . 'cour_material_stat' . $sysSession->ticket);

$icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
$icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

// rd測試用
//$_POST['course_id'] = '10000035';
//$_POST['username'] = 'root';
//$_POST['activity_id'] = 'SCO_10000035_1509968680417155';

$isParameterError = FALSE;

// 驗證學習節點輸入值
if (mb_strlen($_POST['course_id'], 'utf-8') === 0) {
    echo sprintf('<div>缺少課程編號參數，請傳 $_POST[\'course_id\']</div>');
    $isParameterError = TRUE;
}

// 驗證學習節點輸入值
if (mb_strlen($_POST['username'], 'utf-8') === 0) {
    echo sprintf('<div>缺少學員帳號參數錯誤，請傳 $_POST[\'username\']</div>');
    $isParameterError = TRUE;
}

// 驗證學習節點輸入值
if (mb_strlen($_POST['activity_id'], 'utf-8') === 0) {
    echo sprintf('<div>缺少學習節點參數，請傳 $_POST[\'activity_id\']</div>');
    $isParameterError = TRUE;
}

if ($isParameterError === TRUE) {
    echo '<input type="button" value="回上一頁" id="go_back" class="cssBtn" onclick="do_fun(5);" title="回上一頁">';
    exit();
}

// 驗證課程編號輸入值
if (preg_match('/^\d{8}$/', $_POST['course_id']) === 0) {
    echo sprintf('<div>課程編號參數錯誤：%s</div>', $_POST['course_id']);
    $isParameterError = TRUE;
}

// 驗證學員帳號輸入值
//echo '<pre>';
//var_dump(Account_format);
//var_dump(sysAccountMinLen);
//var_dump(sysAccountMaxLen);
//echo '</pre>';
if (!preg_match(Account_format, $_POST['username']) || strlen($_POST['username']) < sysAccountMinLen || strlen($_POST['username']) > sysAccountMaxLen) {
    echo sprintf('<div>學員帳號參數錯誤：%s</div>', $_POST['username']);
    $isParameterError = TRUE;
}

if ($isParameterError === TRUE) {
    echo '<input type="button" value="回上一頁" id="go_back" class="cssBtn" onclick="do_fun(5);" title="回上一頁">';
    exit();
}

$ta     = array(
    '',
    'begin_time',
    'action_id'
);
$sortby = min(2, max(1, $_POST['sortby']));
$sortby = $ta[$sortby];

$order = ($_POST['order'] == 'ASC') ? 'ASC' : 'DESC';
$query = 'target_type = "asset" AND target_id >=1 AND action_id IN ("seekbar", "reload", "pause", "ended") ';

if (empty($_POST['page_num'])) {
    $page_num = sysPostPerPage;
} else {
    $page_num = intval($_POST['page_num']);
}

if (!empty($_POST['course_id'])) {
    $temp = escape_LIKE_query_str(addslashes(trim($_POST['course_id'])));
    $query .= " AND course_id = '" . $temp . "'";
    
    list($caption) = dbGetStSr('WM_term_course', 'caption', "course_id = '" . $temp . "'", ADODB_FETCH_NUM);
    $lang = unserialize($caption);
    $courseName = $lang[$sysSession->lang];
//    echo '<pre>';
//    var_dump($courseName);
//    echo '</pre>';
}

if (!empty($_POST['username'])) {
    $temp = escape_LIKE_query_str(addslashes(trim($_POST['username'])));
    $query .= " AND username = '" . $temp . "'";
    
    list($studentName) = dbGetStSr('WM_all_account', 'first_name', "username = '" . $temp . "'", ADODB_FETCH_NUM);
//    echo '<pre>';
//    var_dump($studentName);
//    echo '</pre>';
}

if (!empty($_POST['activity_id'])) {
    $temp = trim($_POST['activity_id']);
    $query .= " AND activity_id = '" . $temp . "'";
    
    list($courseContent) = dbGetStSr('WM_term_path', 'content', "course_id = '" . (escape_LIKE_query_str(addslashes(trim($_POST['course_id'])))) . "' AND content LIKE '%" . $temp . "%' ORDER BY update_time DESC LIMIT 1", ADODB_FETCH_NUM);
//    echo '<pre>';
//    var_dump($courseContent);
//    var_dump(htmlentities($courseContent));
//    echo '</pre>';
    if (preg_match('/\<item.*identifier=\"I_' . $temp . '\".*identifierref=\"' . $temp . '\"\>.*\<title\>(.*)\<\/title\>.*\<\/item\>/', $courseContent, $matches)) {
//        echo '<pre>';
//        var_dump($matches);
//        var_dump($matches[1]);
//        var_dump(str_replace(array("\s", "\t"), '', $matches[1]));
//        echo '</pre>';
        
        // 本案沒有多語系
        $nodeName = str_replace(array("\s", "\t"), '', $matches[1]);
    } else {
        $nodeName = '';
    }
//    echo '<pre>';
//    var_dump($nodeName);
//    echo '</pre>';
}

list($all_page) = dbGetStSr('LM_read_video_log', 'COUNT(course_id)', $query, ADODB_FETCH_NUM);

$total_page = ceil($all_page / max(1, $page_num));

if ($_POST['page_no'] == '') {
    if ($total_page > 0) {
        $cur_page = 1;
        
        $limit_begin = (($cur_page - 1) * $page_num);
        $limit_str   = ' LIMIT ' . $limit_begin . ',' . $page_num;
        
    } else if ($total_page == 0) {
        $cur_page = 0;
    }
    
} else {
    if (($_POST['page_no'] > 0)) {
        $cur_page = intval($_POST['page_no']);
        if ($cur_page < 0 || $cur_page > $total_page)
            $cur_page = 1;
        $limit_begin = (($cur_page - 1) * $page_num);
        $limit_str   = ' LIMIT ' . $limit_begin . ',' . $page_num;
    } else if ($_POST['page_no'] == 0) {
        $cur_page  = 0;
        $limit_str = '';
    }
}

$query .= " ORDER BY $sortby $order";

$RS = dbGetStMr('LM_read_video_log', 'begin_time, action_id', $query . $limit_str, ADODB_FETCH_ASSOC);

//if ($_POST['ticket'] != $ticket)
//    die($MSG['illegal_access'][$sysSession->lang]);

$js = <<< EOF
var theme          = "{$sysSession->theme}";
var ticket         = "{$ticket}";
var lang           = "{$lang}";
var cur_page       = {$cur_page};
var total_page     = {$total_page};

function page(n) {
    var obj = document.getElementById("selfQuery");
    if ((typeof(obj) != "object") || (obj == null)) return false;
    obj.page_no.value = n;
    switch (n) {
        case -1:
            obj.page_no.value = 1;
            break;
        case -2:
            obj.page_no.value = (cur_page - 1);
            break;
        case -3:
            obj.page_no.value = (cur_page + 1);
            break;
        case -4:
            obj.page_no.value = (total_page);
            break;
        default:
            var page_no = parseInt(n);
    }
    obj.action = 'read_lcms_video_log.php';

    window.onunload = function() {};

    obj.submit();
}


/*
 * 標題排序
 */
function chgPageSort(val) {
    var obj = document.getElementById("selfQuery");
    if ((typeof(obj) != "object") || (obj == null)) return false;
    obj.order.value = obj.order.value == 'ASC' ? 'DESC' : 'ASC';
    obj.sortby.value = val;
    window.onunload = function() {};
    obj.submit();
}

var orgload = window.onload;
window.onload = function() {
    orgload();

    var txt1 = '';

    obj = document.getElementById("toolbar1");

    if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

    obj = document.getElementById("toolbar2");
    if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
};

EOF;

showXHTML_head_B($MSG['title4'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('include', 'sch_statistics.js');
    showXHTML_script('inline', $js);
showXHTML_head_E('');

showXHTML_body_B('');

    showXHTML_table_B('width="650" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
        showXHTML_tr_B('');
        showXHTML_td_B('');
        $ary[] = array(
            '學習行為分析報表 - 影片觀看行為分析',
            'tabs'
        );
        showXHTML_tabs($ary, 1);
        showXHTML_td_E('');
        showXHTML_tr_E('');

        showXHTML_tr_B('');
        showXHTML_td_B('valign="top" id="CGroup" ');

        showXHTML_table_B('id ="mainTable" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
            showXHTML_input('hidden', 'sortby', $_POST['sortby'], '', '');
            showXHTML_input('hidden', 'order', $order, '', '');

            // 課程資訊
            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td_B('colspan="4" id="" style="line-height: 2em;"');
                    echo sprintf('<div>課程名稱：%s</div><div>學生姓名：%s</div><div>節點名稱：%s</div>', $courseName, $studentName, $nodeName);
                showXHTML_td_E('');
            showXHTML_tr_E('');

            // 翻頁
            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td_B('colspan="4" id="toolbar1"');

                $ary = array(
                    $MSG['all'][$sysSession->lang]
                );
                echo $MSG['page'][$sysSession->lang];

                for ($j = 0; $j <= $total_page; $j++) {
                    if ($j == 0) {
                        $P[$j] = $MSG['all'][$sysSession->lang];
                    } else {
                        $P[$j] = $j;
                    }
                }

                showXHTML_input('select', '', $P, $cur_page, 'size="1" onchange="page(this.value);"');
                echo '&nbsp;&nbsp;';
                showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page == 1) || ($cur_page == 0)) ? 'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['switch_page'][$sysSession->lang]);
                showXHTML_input('button', 'prevBtn1', $MSG['prev'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page == 1) || ($cur_page == 0)) ? 'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['switch_page1'][$sysSession->lang]);
                showXHTML_input('button', 'nextBtn1', $MSG['next'][$sysSession->lang], '', 'id="nextBtn1" class="cssBtn" ' . ((($cur_page == 0) || ($cur_page == $total_page)) ? 'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['switch_page2'][$sysSession->lang]);
                showXHTML_input('button', 'lastBtn1', $MSG['last1'][$sysSession->lang], '', 'id="lastBtn1" class="cssBtn" ' . ((($cur_page == 0) || ($cur_page == $total_page)) ? 'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['switch_page3'][$sysSession->lang]);
                showXHTML_input('button', 'go_back', $MSG['title109'][$sysSession->lang], '', 'id="go_back" class="cssBtn" ' . 'onclick="do_fun(5);"' . ' title=' . $MSG['title109'][$sysSession->lang]);
                showXHTML_td_E('');
            showXHTML_tr_E('');

            // 標題
            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td('nowrap align="center"', $MSG['title121'][$sysSession->lang]);
                
                showXHTML_td_B(' align="center" nowrap="noWrap" onclick="chgPageSort(1);" title="' . $MSG['title108'][$sysSession->lang] . '"');
                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">', '觸發時間', (($sortby == 'begin_time') ? ($order == 'DESC' ? $icon_dn : $icon_up) : ''), '</a>';
                showXHTML_td_E('');

                showXHTML_td_B(' align="center" nowrap="noWrap" title="' . $MSG['title107'][$sysSession->lang] . '"');
                echo '操作行為';
                showXHTML_td_E('');
                
            showXHTML_tr_E('');
            
            // 資料
            // 序號
            $ser_no = $cur_page > 0 ? ($page_num * ($cur_page - 1) + 1) : 1;

            if ($RS->RecordCount() > 0) {
                
                // 操作行為清單
                $action = array(
                    'seekbar' => '快轉',
                    'reload' => '重播',
                    'pause' => '暫停',
                    'ended' => '觀看結束',
                );
                
                while ($fields = $RS->FetchRow()) {
                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                    showXHTML_tr_B($col);
                        showXHTML_td('align="center"', $ser_no++);
                        
                        // 觸發時間
                        showXHTML_td('nowrap align="center"', $fields['begin_time']);
                        
                        // 操作行為
                        showXHTML_td('nowrap align="center"', $action[$fields['action_id']]);
                    showXHTML_tr_E('');
                }
            } else {
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('align="cetner" colspan="4"', '無資料');
                showXHTML_tr_E('');
            }

            // 換頁與動作功能列 (function line)
            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td('colspan="4" nowrap id="toolbar2"', '&nbsp;');
            showXHTML_tr_E('');

        showXHTML_table_E('');

        showXHTML_td_E('');
        showXHTML_tr_E('');
    showXHTML_table_E('');

    showXHTML_form_B('action="" method="post" enctype="multipart/form-data" style="display:none" target="main"', 'selfQuery');
        showXHTML_input('hidden', 'ticket', $ticket, '', '');
//        showXHTML_input('hidden', 'cour_query', htmlspecialchars(stripslashes(trim($_POST['cour_query']))), '', '');
//        showXHTML_input('hidden', 'tea_query', trim($_POST['tea_query']), '', '');
        showXHTML_input('hidden', 'page_num', $page_num, '', '');
        showXHTML_input('hidden', 'page_no', '', '', '');
        showXHTML_input('hidden', 'course_id', htmlspecialchars(stripslashes(trim($_POST['course_id']))), '', '');
        showXHTML_input('hidden', 'username', htmlspecialchars(stripslashes(trim($_POST['username']))), '', '');
        showXHTML_input('hidden', 'activity_id', htmlspecialchars(stripslashes(trim($_POST['activity_id']))), '', '');
        showXHTML_input('hidden', 'sortby', $_POST['sortby'], '', '');
        showXHTML_input('hidden', 'order', $order, '', '');
    showXHTML_form_E('');

showXHTML_body_E('');
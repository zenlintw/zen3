<?php
/**
 * 學習統計
 * $Id: learn_stat.php,v 1.1 2010/02/24 02:39:05 saly Exp $
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/lib_statistics.php');
require_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lang/learn_stat.php');

if ($sysSession->username == 'guest') {
    header("LOCATION: /mooc/index.php");
    exit;
}

// {{{ 函數宣告 begin
/**
 * 轉換秒數為天、時、分與秒
 * @param integer $sec : 秒數
 * @param boolean $show_day : 是否要顯示天數
 * @param string  $str : 自訂顯示的格式 (預設：'%d days, %2$02d:%3$02d:%4$02d')
 * @return 格式化後的字串
 **/
function sec2time($sec, $show_day = true, $str = '')
{
    global $sysSession, $MSG;
    
    $tmp = intval($sec);
    $sec = $tmp % 60;
    $tmp = floor($tmp / 60);
    $min = $tmp % 60;
    $tmp = floor($tmp / 60);
    if ($show_day) {
        $hou = $tmp % 24;
        $day = floor($tmp / 24);
        if (empty($str))
            $str = $MSG['days'][$sysSession->lang] . $MSG['time_str'][$sysSession->lang];
    } else {
        $hou = $tmp;
        $day = 0;
        if (empty($str))
            $str = $MSG['time_str'][$sysSession->lang];
    }
    return sprintf($str, $day, $hou, $min, $sec);
}
// }}} 函數宣告 end

// {{{ 主程式 begin
$sysSession->cur_func = '1500200100';
$sysSession->restore();

if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

//更新學員的學習記錄
setPersonalRecrd(date("Y-m-d"), $sysSession->username);

// 顯示個人資訊Start
$lt = intval($myConfig->getValues('login_info', 'login_times'));
$tt = intval($myConfig->getValues('login_info', 'total_time'));
$ll = trim($myConfig->getValues('login_info', 'last_login'));
$li = trim($myConfig->getValues('login_info', 'last_ip'));
if (empty($ll))
    $ll = $MSG['msg_first_login'][$sysSession->lang];
if (empty($li))
    $li = $MSG['msg_first_login'][$sysSession->lang];

$showday = ($tt >= 86400);
$tt      = sec2time($tt, $showday);

$info = array(
    'count' => sprintf($MSG['login_count'][$sysSession->lang], '<span class="cssFont01">' . $lt . '</span>'),
    'last' => sprintf($MSG['login_last'][$sysSession->lang], '<span class="cssFont01">' . $ll . '</span>'),
    'from' => sprintf($MSG['login_from'][$sysSession->lang], '<span class="cssFont01">' . $li . '</span>'),
    'sum' => sprintf($MSG['login_time_sum'][$sysSession->lang], '<span class="cssFont01">' . $tt . '</span>')
);
$smarty->assign('loginInfo', $info);
// 顯示個人資訊End

$cour_sort = array(
    1 => 'CS.caption',
    2 => 'MJ.last_login',
    3 => 'MJ.login_times',
    4 => 'MJ.post_times',
    5 => 'MJ.dsc_times',
    6 => 'rss',
    7 => 'page'
);

/** 排序 */
$_POST['sortby'] = intval($_POST['sortby']);
$sortby          = $cour_sort[$_POST['sortby']];
if (empty($sortby))
    $sortby = 'MJ.last_login';

$order = trim($_POST['order']);
if (!in_array($order, array(
    'asc',
    'desc'
)))
    $order = 'desc';
$smarty->assign('sort', $sortby);
$smarty->assign('order', $order);

$sqls = str_replace('%USERNAME%', $sysSession->username, $Sqls['get_student_total_courselist']);
$sqls .= ' order by ' . $sortby . " $order";

chkSchoolId('WM_term_major');
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$datalist         = array();
if ($rs = $sysConn->Execute($sqls)) {
    while ($fields = $rs->FetchRow()) {
        $titles            = unserialize($fields['caption']);
        $fields['caption'] = $titles[$sysSession->lang];
        $fields['rss']     = zero2gray(sec2timestamp(intval($fields['rss'])));
        $datalist[]        = $fields;
    }
}

// assign
$smarty->assign('post', $_POST);
$smarty->assign('MSG', $MSG);
$smarty->assign('sysSession', $sysSession);
$smarty->assign('datalist', $datalist);
$smarty->assign('csrfToken', md5($sysSession->idx));

// output
$smarty->display('user/learn_stat.tpl');
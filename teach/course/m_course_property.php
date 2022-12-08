<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/editor.php');
require_once(sysDocumentRoot . '/academic/course/course_lib.php');
require_once(sysDocumentRoot . '/lib/multi_lang.php');
require_once(sysDocumentRoot . '/lang/course_manage.php');
require_once(sysDocumentRoot . '/lang/mooc_teach.php');
require_once(sysDocumentRoot . '/lib/character_class.php');
require_once(sysDocumentRoot . '/lib/username.php');
if (sysEnableAppCoursePicture) {
    // APP課程圖片模組有啟用
    require_once(sysDocumentRoot . '/lang/app_course_manage.php');
}
require_once(sysDocumentRoot . '/lib/quota.php');

$sysSession->cur_func = '700300200';
$sysSession->restore();

//print(portal_school_id);

#======== function ===========
/**
是否已達課程限量
@return bollean
*/
function isReachCourseLimit()
{
    if (sysCourseLimit == 0)
        return false; //無課程上限
    list($nowCourseNum) = dbGetStSr('WM_term_course', 'count(*)', 'kind = "course" and status != 9', ADODB_FETCH_NUM);
    if ($nowCourseNum >= sysCourseLimit)
        return true;
    return false;
}
#======== main ===============
if (!aclVerifyPermission(700300200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$actType = '';
$today   = date('Y-m-d');
$title   = '';
$teacher = '';
$status  = 5;
$cont_id = '';
$book    = ''; // 書籍
//$url     = 'http://'; // 參考連結
//$intro   = ''; // 簡介
$credit  = ''; // 學分
$n_limit = ''; // 正式生人數
$a_limit = ''; // 旁聽生人數
$usage   = 0; // 使用率
$quota   = ''; // Quota
if (!isset($contents))
    $contents = '';
$default_ta_can_sets = array(
    'caption',
    'status',
    'content_id',
    'en_begin',
    'st_begin',
    'review',
    'n_limit',
    'a_limit',
    'fair_grade',
//    'cparent'
    'content',
    'texts',
); // 允許教師更改的欄位

$ta_can_sets = array(); // 允許教師更改的欄位

if (sysEnableAppCoursePicture) {
    // APP課程圖片模組有啟用
    $appPictureInfoFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/appCoursePictureData.txt', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
    if (is_file($appPictureInfoFile)) {
        unlink($appPictureInfoFile);
    }
}

// 新增課程
if (empty($_POST['ticket'])) {
    $actType = 'Create';
    if (isReachCourseLimit()) {
        header("Location: /academic/course/course_limit.php");
        exit;
    }
    $title = $MSG['title_add_course'][$sysSession->lang];
    
    $quota      = getDefaultQuota();
    $lang       = array(
        'Big5' => '',
        'GB2312' => '',
        'en' => '',
        'EUC-JP' => '',
        'user_define' => ''
    );
    $fair_grade = 60;
    
    $ta_can_sets = array(
        'caption',
        'status',
        'content_id',
        'st_begin',
        'st_end',
        'content',
        'texts',
        'n_limit',
        'a_limit',
        'fair_grade'
    );
    
    if (sysEnableAppCoursePicture) {
        // APP課程圖片模組有啟用
        // 課程圖片設定 - Begin
        $appPictureInfoFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/appCoursePictureData.txt', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
        if (is_file($appPictureInfoFile)) {
            unlink($appPictureInfoFile);
        }
        $csid    = 99999999; // 建立新課程給予一個假的課程編號
        $frameId = 'main'; // 管理者的frame id 是 main，用於後續jQuery
        // 課程圖片設定 - End
    }
    $appNewCourse = true; // 建立新課程
}

// 修改課程
$ticket = md5($sysSession->school_id . $sysSession->school_name . 'Edit' . $sysSession->username);
if (trim($_POST['ticket']) == $ticket) {
    $actType = 'Edit';
    if (defined('ENV_TEACHER')) {
        $title         = $MSG['tabs_course_set'][$sysSession->lang];
        $csid          = $sysSession->course_id;
        $_POST['csid'] = sysEncode($csid);
        
        if (sysEnableAppCoursePicture) {
            // APP課程圖片模組有啟用
            $frameId = 'c_main'; // 教師辦公室的frame id 是 c_main，用於後續jQuery
        }
    } else {
        $title = $MSG['title_modify_course'][$sysSession->lang];
        $csid  = trim($_POST['csid']);
        $csid  = intval(sysDecode($csid));
        
        if (sysEnableAppCoursePicture) {
            // APP課程圖片模組有啟用
            $frameId = 'main'; // 管理者的frame id 是 main，用於後續jQuery
        }
    }
    $appNewCourse = false; // 非建立新課程
    
    $RS          = getCourseData($csid);
    $ta_can_sets = explode(',', $RS['ta_can_sets']); // 允許教師更改的欄位
    
    if (intval($RS['st_begin']) != 0) {
        $arr_st_begin         = explode('-', $RS['st_begin']);
        $RS['st_begin_year']  = $arr_st_begin[0];
        $RS['st_begin_month'] = intval($arr_st_begin[1]);
        $RS['st_begin_day']   = intval($arr_st_begin[2]);
    }
    
    if (intval($RS['st_end']) != 0) {
        $arr_st_end         = explode('-', $RS['st_end']);
        $RS['st_end_year']  = $arr_st_end[0];
        $RS['st_end_month'] = intval($arr_st_end[1]);
        $RS['st_end_day']   = intval($arr_st_end[2]);
    }
    
    if (intval($RS['en_begin']) != 0) {
        $arr_en_begin         = explode('-', $RS['en_begin']);
        $RS['en_begin_year']  = $arr_en_begin[0];
        $RS['en_begin_month'] = intval($arr_en_begin[1]);
        $RS['en_begin_day']   = intval($arr_en_begin[2]);
    }
    
    if (intval($RS['en_end']) != 0) {
        $arr_en_end         = explode('-', $RS['en_end']);
        $RS['en_end_year']  = $arr_en_end[0];
        $RS['en_end_month'] = intval($arr_en_end[1]);
        $RS['en_end_day']   = intval($arr_en_end[2]);
    }
    
    // 課程類型
    $CourseStatusList = array(
        5 => $MSG['param_prepare'][$sysSession->lang],
        1 => $MSG['param_open_a'][$sysSession->lang],
        2 => $MSG['param_open_a_date'][$sysSession->lang],
        3 => $MSG['param_open_n'][$sysSession->lang],
        4 => $MSG['param_open_n_date'][$sysSession->lang],
        0 => $MSG['param_close'][$sysSession->lang]
    );
    $RS['show_type']  = $CourseStatusList[$RS['status']];
    
    
    $RS['en_option'] = (($RS['en_begin'] == null || $RS['en_begin'] == '0000-00-00') && ($RS['en_end'] == null || $RS['en_end'] == '0000-00-00')) ? '0' : '1';
    $RS['st_option'] = (($RS['st_begin'] == null || $RS['st_begin'] == '0000-00-00') && ($RS['st_end'] == null || $RS['st_end'] == '0000-00-00')) ? '0' : '1';
    
    if ($RS['fee'] != null && $RS['fee'] != '') {
        $RS['fees1'] = 'checked';
    } else {
        $RS['fees0'] = 'checked';
    }
    
    if ($RS['allow_comment'] > 0) {
        $RS['allow_comment1'] = 'checked';
    } else {
        $RS['allow_comment0'] = 'checked';
    }
    
    if ('' != $RS['is_use']) {
        $arr_use = getCaption($RS['is_use']); // 啟用否
        if (count($arr_use) > 0) {
            foreach ($arr_use as $key => $value) {
                $RS['chk_' . $value] = 'checked';
            }
        }
    } else {
        $arr_use = array();
        /* $RS['fair_grade'] = 60;
        $RS['chk_formal_score'] = 'checked'; */
    }
    
    $RS['lang'] = old_getCaption($RS['caption']); // 課程名稱
    if ('' != $RS['goal']) {
        $RS['goal'] = getCaption($RS['goal']); // 目標
    } else {
        $RS['goal'][0] = '';
    }
    
    if ('' != $RS['audience']) {
        $RS['audience'] = getCaption($RS['audience']); // 聽眾
    } else {
        $RS['audience'][0] = '';
    }
    
    if ('' != $RS['ref_title']) {
        $RS['ref_title'] = getCaption($RS['ref_title']); // 參考資料-標題
        
        $RS['show_ref'] = true;
    } else {
        $RS['ref_title'][0] = '';
        
        $RS['show_ref'] = false;
    }
    
    if ('' != $RS['ref_url']) {
        $RS['ref_url'] = getCaption($RS['ref_url']); // 參考資料-網址
    } else {
        $RS['ref_url'][0] = '';
    }
    
    
    $RS['formal'] = getCaption($RS['formal_pass']); // 一般生通過條件
    
    $RS['gallery'] = getCaption($RS['gallery_pass']); // 旁聽生通過條件
    
    $teacher = $RS['teacher']; // 教師
    
    // 查詢教材的狀態不為 disable
    list($content_exist) = dbGetStSr('WM_content', 'count(*)', 'content_id=' . $RS['content_id'] . ' and status!="disable"', ADODB_FETCH_NUM);
    if ($content_exist > 0)
        $cont_id = $RS['content_id']; // 教材
    else
        $cont_id = ''; // 教材
    
    $status            = $RS['status']; // 狀態
    $book              = $RS['texts']; // 書籍
//    $url               = $RS['url']; // 參考連結
//    $intro             = $RS['content']; // 簡介
    $credit            = $RS['credit']; // 學分
    $n_limit           = $RS['n_limit']; // 正式生人數
    $a_limit           = $RS['a_limit']; // 旁聽生人數
    $usage             = $RS['quota_used'];
    $quota             = $RS['quota_limit'];
    $RS['quota_limit'] = $RS['quota_limit'] / 1024;
    $rteacher          = is_array($RS['real_teacher']['teacher']) ? implode(', ', $RS['real_teacher']['teacher']) : '&nbsp;'; // 教師
    $rinstructor       = is_array($RS['real_teacher']['instructor']) ? implode(', ', $RS['real_teacher']['instructor']) : '&nbsp;'; // 講師
    $rassistant        = is_array($RS['real_teacher']['assistant']) ? implode(', ', $RS['real_teacher']['assistant']) : '&nbsp;'; // 助教
    $fair_grade        = $RS['fair_grade']; // 及格成績
    
    // 取得此課程屬於那些群組
    $csParent = getCourseParents($csid);
    $tmp      = array();
    foreach ($csParent as $key => $val) {
        $tmp[] = $val[$sysSession->lang];
        $arr_gp[] = '{"value": "' . $key . '" , "text": "' . $val[$sysSession->lang] . '"}';
    }
    $gps      = implode(', ', $tmp);
    //$selgpids = '' . implode(',', array_keys($csParent)) . '';
    if (is_array($arr_gp)) {
        $selgpids = implode(',', $arr_gp);
    }
    
    // 取得此課程屬於那些平台群組
    $csParent2 = getCoursePortalParents($csid);
    $tmp2      = array();
    foreach ($csParent2 as $key => $val) {
        $tmp2[] = $val[$sysSession->lang];
    }
    $gps2      = implode(', ', $tmp2);
    if (is_array($csParent2)) {
        $selgpids2 = '' . implode(',', array_keys($csParent2)) . '';
    }
    
    $tech_obj  = new WMteacher();
    // 取得此門課老師
    $teachers  = $tech_obj->listMember($csid, $sysRoles['teacher']);
    $arr_teach = array();
    foreach ($teachers as $key => $value) {
        if ($key == '')
            continue;
        $origin_teach[] = $key;
        $user           = getUserDetailData($key);
        $arr_teach[]    = '{"value": "' . $key . '" , "text": "' . $key . (!empty($user['realname']) ? sprintf(' (%s)', $user['realname']) : '') . '"}';
    }
    
    $oriteach = (count($origin_teach) > 0) ? implode(',', $origin_teach) : '';
    $selteach = implode(',', $arr_teach);
    
    
    // 取得此門課助教
    $assistants        = $tech_obj->listMember($csid, $sysRoles['assistant']);
    $arr_assistant     = array();
    $origin_assistants = array();
    foreach ($assistants as $key => $value) {
        if ($key == '')
            continue;
        $origin_assistants[] = $key;
        $user                = getUserDetailData($key);
        $arr_assistant[]     = '{"value": "' . $key . '" , "text": "' . $key . (!empty($user['realname']) ? sprintf(' (%s)', $user['realname']) : '') . '"}';
    }
    
    $oriassistant = implode(',', $origin_assistants);
    $selassistant = implode(',', $arr_assistant);
    
    // 取得此門課講師
    $instructors        = $tech_obj->listMember($csid, $sysRoles['instructor']);
    $arr_instructor     = array();
    $origin_instructors = array();
    foreach ($instructors as $key => $value) {
        if ($key == '')
            continue;
        $origin_instructors[] = $key;
        $user                 = getUserDetailData($key);
        $arr_instructor[]     = '{"value": "' . $key . '" , "text": "' . $key . (!empty($user['realname']) ? sprintf(' (%s)', $user['realname']) : '') . '"}';
    }
    
    $oriinstructor = implode(',', $origin_instructors);
    $selinstructor = implode(',', $arr_instructor);
    
}

if (sysEnableAppCoursePicture) {
    // APP課程圖片模組有啟用
    $appCsid = base64_encode($csid); // 編碼課號給後續app設定用
}

if (empty($actType))
    die($MSG['msg_access_deny'][$sysSession->lang]);

// 設定車票
// setTicket();
$ticket = md5($actType . $sysSession->ticket . $sysSession->school_id . $sysSession->school_name . $sysSession->username);

if ($appNewCourse) {
    $appNewCourseForJS = 'true';
} else {
    $appNewCourseForJS = 'false';
}

$courseFolder          = sprintf("/base/%d/course/%d/content/public", $sysSession->school_id, $csid);
$videoFileAbsolutePath = realpath(sysDocumentRoot . $courseFolder) . "/course_introduce.mp4";

if (file_exists($videoFileAbsolutePath)) {
    $smarty->assign('videoFileAbsolutePath', $videoFileAbsolutePath);
}

if (sysEnableAppCoursePicture) {
    // APP課程圖片模組有啟用
    // 課程圖片 -- Begin
    $removeButtonDisable = 'disabled';
    if (isset($csid)) {
        list($isExistPicture) = dbGetStSr('CO_course_picture', 'count(*)', "course_id = '{$csid}'", ADODB_FETCH_NUM);
        
        if ($isExistPicture > 0) {
            $removeButtonDisable = '';
        }
    }
    // 課程圖片 -- End
}

// 編輯權限 (預留1, 2，助教及講師如以後有特殊權限)
$editLimit = 0;
if ($sysSession->env == 'academic') {
    if (aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id)) {
        $editLimit = 2;
    }
} elseif ($sysSession->env == 'teach') {
    if (aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $csid)) {
        $editLimit = 1;
    }
}

// 課程分類群組
function getCGList($pid, $indent)
{
    global $sysSession;
    
    $txt = '';
    $ary = getCoursesList($pid, 'group');
    if (count($ary) > 0) {
        $txt .= '<ul style="list-style:none;">';
        foreach ($ary as $key => $val) {
            $eid  = $key;
            $icon = '<img src="/public/images/course_set/-.png" align="absmiddle" border="0">';
            if ($val[2] == 'group') {
                if (intval($val[3]) > 0)
                    $icon = '<img src="/public/images/course_set/+.png" align="absmiddle" border="0" id="micon_' . $eid . '" alt="' . $MSG['cs_tree_expand'][$sysSession->lang] . '" title="' . $MSG['cs_tree_expand'][$sysSession->lang] . '" onclick="Expand(\'' . $eid . '\');">';
            } else {
                $icon = '';
            }
            
            $uid = uniqid('gp_');
            $txt .= '<li class="group-li"><div>' . $icon . '</div>　<div class="checkbox_style"><input class="teach-checkbox" type="checkbox" value="' . $eid . '" name="kinds[]" id="m' . $uid . '"><label for="m' . $uid . '"><span><span></span></span></label></div>　<div>' . $val[1][$sysSession->lang] . '</div></li>';
            if ($val[2] == 'group') {
                $txt .= getCGList($val[0], $indent + 1);
            }
        }
        $txt .= '</ul>';
    }
    if (!empty($txt)) {
        $eid     = $pid;
        $display = ($indent == 0) ? '' : ' style="display:none;"';
        $txt     = '<div id="mGroup_' . $eid . '"' . $display . '>' . $txt . '</div>';
    }
    
    return $txt;
    
}
// 平台課程分類群組
function getPGList($pid, $indent)
{
    global $sysSession;
    
    $txt = '';
    $ary = getCoursesList($pid, 'group', '', false, true);
    if (count($ary) > 0) {
        $txt .= '<ul style="list-style:none;">';
        foreach ($ary as $key => $val) {
            $eid  = $key;
            $icon = '<img src="/public/images/course_set/-.png" align="absmiddle" border="0">';
            if ($val[2] == 'group') {
                if (intval($val[3]) > 0)
                    $icon = '<img src="/public/images/course_set/+.png" align="absmiddle" border="0" id="picon_' . $eid . '" alt="' . $MSG['cs_tree_expand'][$sysSession->lang] . '" title="' . $MSG['cs_tree_expand'][$sysSession->lang] . '" onclick="pExpand(\'' . $eid . '\');">';
            } else {
                $icon = '';
            }
            
            $uid = uniqid('gp_');
            $txt .= '<li class="group-li"><div>' . $icon . '</div>　<div class="checkbox_style"><input class="teach-checkbox" type="checkbox" value="' . $eid . '" name="pkinds[]" id="p' . $uid . '"><label for="p' . $uid . '"><span><span></span></span></label></div>　<div>' . $val[1][$sysSession->lang] . '</div></li>';
            if ($val[2] == 'group') {
                $txt .= getPGList($val[0], $indent + 1);
            }
        }
        $txt .= '</ul>';
    }
    if (!empty($txt)) {
        $eid     = $pid;
        $display = ($indent == 0) ? '' : ' style="display:none;"';
        $txt     = '<div id="pGroup_' . $eid . '"' . $display . '>' . $txt . '</div>';
    }
    
    return $txt;
    
}

/*
$show_group = getCGList(10000000, 0);
$smarty->assign('group_html', $show_group);
$show_pgroup = getPGList(10000000, 0);
$smarty->assign('pgroup_html', $show_pgroup);
*/

$basePath = sysNewEncode($csid);

// 改為給予有效路徑，而不在img給予on error，避免consolelog出現error訊息
$coursePhotoPath = sprintf('/base/%05d/course/%08d/content/public/course_introduce.jpg', $sysSession->school_id, $csid);

if (file_exists(sysDocumentRoot . $coursePhotoPath) === false) {
    $coursePhotoPath = '/theme/default/app/default-course-picture.jpg';
}

$modJSGpList = <<< BOF
    function modCGTreeCallBack(ary) {
        if (!ary instanceof Array) {
            alert("param is not Array");
            return false;
        }
        selGpIDs = ary[0];
        var obj = document.getElementById("div_cparent");
        var tmp = [];
        var txt = "";
        if (obj) {
            for (var i = 0; i < ary[1].length; i++) {
                tmp[tmp.length] = ary[1][i][1];
            }
            obj.innerHTML = tmp.join(", ");
        }
    }
BOF;

$smarty->display('common/tiny_header.tpl');
// 產生萬年曆的物件，並且設定所需的語系
$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
// 載入萬年曆所需的程式
$calendar->load_files();

/*
$modTree = new modCGTree();
$modTree->add_js_callback($modJSGpList);
$modTree->show();
*/

// 編輯權限
$smarty->assign('editLimit', $editLimit);
$smarty->assign('is_teacher', aclCheckRole($sysSession->username, $sysRoles['teacher'], $csid) ? 'Y' : 'N');

// 回管理員課程列表
$smarty->assign('returnList', array(
    'gid' => $_POST['gid'],
    'page' => $_POST['page'],
    'sortby' => $_POST['sortby'],
    'keyword'=> $_POST['query_btn']== '0' ? "": $_POST['keyword']
));

// 修課審核
$review_val    = getReviewSerial($csid);
if ($review_val < 0) $review_val = 2;
$reviewOptions = getReviewRuleList($csid);
$review_text   = isset($reviewOptions[$review_val]) ? $reviewOptions[$review_val] : $reviewOptions[1];

$smarty->assign('actType', $actType);
if ($actType == 'Create') {
    $ticket = md5('Create' . $sysSession->ticket . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
    $smarty->assign('data', array(
        'en_option' => 0,
        'st_option' => 0,
        'fair_grade' => $fair_grade,
        'quota_limit' => $quota / 1024
    ));
} else {
    $smarty->assign('data', $RS);
}

// 是否同步行事曆
$calendar_begin_type = 'course_begin';
$calendar_end_type   = 'course_end';
$begin_cal_idx       = $sysConn->GetOne("select idx from WM_calendar where relative_type='{$calendar_begin_type}' and relative_id={$csid}");
$end_cal_idx         = $sysConn->GetOne("select idx from WM_calendar where relative_type='{$calendar_end_type}' and relative_id={$csid}");
$smarty->assign('use_calander', false);
if ($begin_cal_idx && $begin_cal_idx) {
    $smarty->assign('use_calander', true);
} 

$smarty->assign('ticket', $ticket);
$smarty->assign('csid', $_POST['csid']);
$smarty->assign('basePath', $basePath);
$smarty->assign('photoPath', $coursePhotoPath);
$smarty->assign('appCsid', $appCsid);
$smarty->assign('frameId', $frameId);
$smarty->assign('show_kind', $gps);
$smarty->assign('selgpids', $selgpids);
$smarty->assign('show_pkind', $gps2);
$smarty->assign('pselgpids', $selgpids2);
$smarty->assign('oriteach', $oriteach);
$smarty->assign('oriassistant', $oriassistant);
$smarty->assign('oriinstructor', $oriinstructor);
$smarty->assign('review_val', $review_val);
$smarty->assign('reviews', $reviewOptions);
$smarty->assign('review_text', $review_text);
// 空間限制與使用
$smarty->assign('quota_limit_text', format_size($quota));
$smarty->assign('quota_usage_text', format_size($usage));
$smarty->assign('quota_usage_rate', (intval($quota) === 0) ? '0' : round($usage / intval($quota), 4) * 100);

// 教材使用
$val = $cont_id;
list($content_caption) = dbGetStSr('WM_content', 'caption', 'content_id=' . $val, ADODB_FETCH_NUM);
$content_lang = unserialize($content_caption);
$content_val  = $content_lang[$sysSession->lang];

$smarty->assign('content_id', $cont_id);
$smarty->assign('content_caption', $content_val);
$smarty->assign('sid', $sysSession->school_id);
$smarty->assign('cid', $csid);


// 權限設定
$smarty->assign('selteach', $selteach);
$smarty->assign('selassistant', $selassistant);
$smarty->assign('selinstructor', $selinstructor);

$smarty->assign('appNewCourseForJS', $appNewCourseForJS);
$smarty->assign('sysEnableAppCoursePicture', sysEnableAppCoursePicture);
$smarty->assign('msg_save_fail', $MSG['msg_save_fail'][$sysSession->lang]);
$smarty->assign('msg_save_success', $MSG['msg_save_success'][$sysSession->lang]);

// 環境
$smarty->assign('env', $sysSession->env);

// 預設可以編輯的欄位
$smarty->assign('default_ta_can_sets', $default_ta_can_sets);

// 實際可以編輯的欄位
$smarty->assign('ta_can_sets', $ta_can_sets);

$smarty->display('teach/course_property.tpl');
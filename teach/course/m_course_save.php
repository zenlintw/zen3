<?php
/**
 * 儲存課程
 *
 * 建立日期：2002/09/09
 * @author  ShenTing Lin
 * @version $Id: course_save.php,v 1.1 2010/02/24 02:38:20 saly Exp $
 **/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/academic/course/course_lib.php');
require_once(sysDocumentRoot . '/lib/multi_lang.php');
require_once(sysDocumentRoot . '/lang/course_manage.php');
require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/character_class.php');
require_once(sysDocumentRoot . '/lib/Hongu.php');
require_once(sysDocumentRoot . '/lang/hongu_validate_msg.php');
require_once(sysDocumentRoot . '/lang/mooc_teach.php');
require_once(sysDocumentRoot . '/lib/quota.php');

if (sysEnableAppCoursePicture) {
    // APP課程圖片模組有啟用
    require_once(sysDocumentRoot . '/lang/app_course_manage.php');
}

$actType = '';
$title   = '';

$lang            = array();
$teacher         = '';
$status          = 5;
$cont_id         = '';
$book            = ''; // 書籍
$url             = 'http://'; // 參考連結
$intro           = ''; // 簡介
$credit          = ''; // 學分
$n_limit         = ''; // 正式生人數
$a_limit         = ''; // 旁聽生人數
$usage           = 0; // 使用率
$quota           = ''; // Quota
$ta_can_sets     = ''; // 允許教師更改的欄位
$ta_can_sets_ary = array(); // 允許教師更改的欄位

// 新增課程
$ticket = md5('Create' . $sysSession->ticket . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
if (trim($_POST['ticket']) == $ticket) {
    $actType = 'Create';
}

// 修改課程
$ticket = md5('Edit' . $sysSession->ticket . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
if (trim($_POST['ticket']) == $ticket) {
    $actType = 'Edit';
    if (defined('ENV_TEACHER')) {
        $title = $MSG['tabs_course_set'][$sysSession->lang];
    } else {
        $title = $MSG['title_modify_course'][$sysSession->lang];
    }
}

if (empty($actType))
    die($MSG['msg_access_deny'][$sysSession->lang]);
if ($actType == 'Create')
    $sysSession->cur_func = '700400100';
else
    $sysSession->cur_func = defined('ENV_TEACHER') ? '700400700' : '700400200';
$sysSession->restore();

if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

// 目前所有的課程狀態，延續 WM2 的屬性
$CourseStatusList = array(
    5 => $MSG['param_prepare'][$sysSession->lang],
    1 => $MSG['param_open_a'][$sysSession->lang],
    2 => $MSG['param_open_a_date'][$sysSession->lang],
    3 => $MSG['param_open_n'][$sysSession->lang],
    4 => $MSG['param_open_n_date'][$sysSession->lang],
    0 => $MSG['param_close'][$sysSession->lang]
    /* 6 => $MSG['param_adminstrator'][$sysSession->lang] */
);

// 取得所有可編輯的欄位
chkSchoolId('WM_term_course');
$ta_can_sets_fields_schema = $sysConn->GetRow("show columns from WM_term_course where Field='ta_can_sets'");

if (isset($ta_can_sets_fields_schema['Field'])){
    preg_match_all("/'(.*)'/U",$ta_can_sets_fields_schema['Type'],$matches);
    if (is_array($matches[1])&&count($matches[1])){
        $all_ta_can_sets = $matches[1];
    }
}

// 預設開放老師編輯的欄位
if (isset($ta_can_sets_fields_schema['Default'])){
    $default_ta_can_sets = explode(',',$ta_can_sets_fields_schema['Default']);
}


// 如果是老師環境，移除沒有允許的欄位
if ($sysSession->env === 'teach') {
    // 取實際開放老師編輯的欄位
    $tmp_id = sysDecode($_POST['csid']);
    list($ta_can_sets) = dbGetStSr('WM_term_course', 'ta_can_sets', 'course_id=' . $tmp_id, ADODB_FETCH_NUM);
    $ta_can_sets_ary = explode(',', $ta_can_sets);

    // 因應老師可修改的欄位，僅判斷 ta_can_sets 有設定的欄位
    // 有勾選報名期間代表有勾選報名起迄兩個數值
    // 有勾選上課期間代表有勾選上課起迄兩個數值
    if (in_array('en_begin', $ta_can_sets_ary) && !(in_array('en_end', $ta_can_sets_ary))) {
        $ta_can_sets_ary[] = 'en_end';
    }

    if (in_array('st_begin', $ta_can_sets_ary) && !(in_array('st_end', $ta_can_sets_ary))) {
        $ta_can_sets_ary[] = 'st_end';
    }
    
    $ta_cant_sets_ary = array_diff($all_ta_can_sets, $ta_can_sets_ary);

    // 移除沒有開放的欄位
    if (empty($_COOKIE['show_me_info']) === false) {
        echo '<pre>';
        var_dump('全部開放老師編輯的欄位', $all_ta_can_sets);
        var_dump('實際開放老師編輯的欄位', $ta_can_sets_ary);
        var_dump('差集', $ta_cant_sets_ary);
        var_dump('傳送過來的陣列', $_POST);
        echo '</pre>';
    }
    foreach($ta_cant_sets_ary as $v) {
        unset($_POST[$v]);
    }
    if (empty($_COOKIE['show_me_info']) === false) {
        echo '<pre>';
        var_dump('除掉沒有開放的欄位後的陣列', $_POST);
        echo '</pre>';
    }
} 
 
// 驗證表單數值

//if (defined('ENV_TEACHER')) {
//    $tmp_id = sysDecode($_POST['csid']);
//    list($ta_can_sets) = dbGetStSr('WM_term_course', 'ta_can_sets', 'course_id=' . $tmp_id, ADODB_FETCH_NUM);
//    $ta_can_sets_ary = explode(',', $ta_can_sets);
//}

$messages = _formValidation();


$rtn = array(
    'flag' => true,
    'id' => 0,
    'text' => ''
);

if (count($messages) >= 1) {
    $errMsg = array();
    for ($i = 0, $size = count($messages); $i < $size; $i++) {
        $errMsg[] = $messages[$i];
    }
    $rtn = array(
        'flag' => false,
        'error' => $errMsg
    );
    echo json_encode($rtn);
    die();
}

/**
 * 表單驗證函數
 */
function _formValidation()
{
    global $sysSession, $MSG;
    $hongu = new Hongu();
    $rule  = new Hongu_Validate_Rule();
    foreach ($_POST as $key => $value) {
        switch ($key) {
            case 'Big5':
            case 'GB2312':
            case 'content':
                $rules[$key] = array(
                    $rule->MAKE_RULE('Required', null, $MSG['hv_msg_required'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'credit':
            case 'fee':
            case 'fair_grade':
                $rules[$key] = array(
                    $rule->MAKE_RULE('HalfNumber', null, $MSG['hv_msg_positive_integer'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'formal':
            case 'gallery':
                foreach ($value as $k => $v) {
                    switch ($k) {
                        case 'percent':
                            $int_range                      = $MSG['hv_msg_integer_range'][$sysSession->lang];
                            $int_range                      = str_replace("%min%", 0, $int_range);
                            $int_range                      = str_replace("%max%", 100, $int_range);
                            $rules2[$key . '[' . $k . ']']  = array(
                                $rule->MAKE_RULE('Float', null, $MSG['hv_msg_float'][$sysSession->lang]),
                                $rule->MAKE_RULE('IntRangeEqualerThan', array(
                                    'min' => 0,
                                    'max' => 100
                                ), $int_range)
                            );
                            $params2[$key . '[' . $k . ']'] = $v;
                            break;
                        case 'time':
                            $rules2[$key . '[' . $k . ']']  = array(
                                $rule->MAKE_RULE('Float', null, $MSG['hv_msg_float'][$sysSession->lang])
                            );
                            $params2[$key . '[' . $k . ']'] = $v;
                            break;
                        default:
                            break;
                    }
                }
                break;
            default:
                break;
        }

        if (intval($_POST['en_option']) == 1){
            $en_begin = date('Y-m-d',strtotime($_POST['en_begin']));
            $en_end = date('Y-m-d',strtotime($_POST['en_end']));
            if (strlen($en_begin) === 10 && strlen($en_end) === 10 && $en_begin > $en_end) {
                $rules['en_begin'] = array(
                    $rule->MAKE_RULE('Date', null, $MSG['during_registration'][$sysSession->lang] . $MSG['date_unreasonable'][$sysSession->lang])
                );
            }
        }else{
            $en_begin = null;
            $en_end = null;
        }

        if (intval($_POST['st_option']) == 1){
            $st_begin = date('Y-m-d',strtotime($_POST['st_begin']));
            $st_end = date('Y-m-d',strtotime($_POST['st_end']));
            if (strlen($st_begin) === 10 && strlen($st_end) === 10 && $st_begin > $st_end) {
                $rules['st_begin'] = array(
                    $rule->MAKE_RULE('Date', null, $MSG['during_counseling'][$sysSession->lang] . $MSG['date_unreasonable'][$sysSession->lang])
                );
            }
        }else{
            $st_begin = null;
            $st_end = null;
        }
    }

    $params = $_POST;
    $valid  = $hongu->getValidator();
    if (!empty($rules)) {
        $rtn = $valid->check($params, $rules);
    }
    
    if (!empty($rules2)) {
        // 處理二維的資料
        if (!empty($rtn)) {
            $rtn = array_merge($rtn, $valid->check($params2, $rules2));
        } else {
            $rtn = $valid->check($params2, $rules2);
        }
    }
    
    if (!empty($rtn)) {
        return $rtn;
    } else {
        return array();
    }
}

/**
 * 儲存課程資訊的步驟
 *     1. 儲存課程資訊到     WM_term_course
 *     2. 建立第一個教材路徑 WM_term_path
 *     3. 建立課程群組關聯   WM_term_group
 *     4. 建立課程討論板     WM_bbs_boards
 *     5. 建立課程公告板     WM_bbs_boards
 *     6. 將課程討論板與課程公告板的 board_id 儲存到 WM_term_course
 *     7.
 **/
// while (list($key, $val) = each($_POST)) {
foreach ($_POST as $key => $val) {
    if (preg_match('/^ckta_(.+)$/', $key, $regs)) {
        $ta_can_sets_ary[] = $regs[1];
    } else {
        if (is_string($_POST[$key])) {
            switch ($key) {
                case 'credit':
                    $_POST['credit'] = preg_match('/^\d+$/', $_POST['credit']) ? intval($_POST['credit']) : 'NULL';
                    break;
                case 'fair_grade':
                case 'status':
                case 'n_limit':
                case 'a_limit':
                case 'content_id':
                case 'review':
                    $_POST[$key] = intval($val);
                    break;
                case 'quota_limit':
                    $_POST[$key] = intval($val) * 1024;
                    break;
                case 'Big5':
                case 'GB2312':
                case 'en':
                case 'EUC-JP':
                case 'user_define':
                    $lang[$key] = Filter_Spec_char(stripslashes(trim($val)));
                    break;
                case 'texts':
                case 'url':
                case 'content':
                    break;
                default:
                    $_POST[$key] = Filter_Spec_char(trim($val));
            }
        } // End if (is_string($_POST[$key]))
    }
}

// 因應老師可修改的欄位，僅判斷 ta_can_sets 有設定的欄位
// 有勾選報名期間代表有勾選報名起迄兩個數值
// 有勾選上課期間代表有勾選上課起迄兩個數值
if (in_array('en_begin', $ta_can_sets_ary) && !(in_array('en_end', $ta_can_sets_ary))) {
    $ta_can_sets_ary[] = 'en_end';
}

if (in_array('st_begin', $ta_can_sets_ary) && !(in_array('st_end', $ta_can_sets_ary))) {
    $ta_can_sets_ary[] = 'st_end';
}

if (count($ta_can_sets_ary) > 0) {
    $ta_can_sets = implode(',', $ta_can_sets_ary);
} else if (defined('ENV_TEACHER')) {
    $tmp_id = sysDecode($_POST['csid']);
    list($ta_can_sets) = dbGetStSr('WM_term_course', 'ta_can_sets', 'course_id=' . $tmp_id, ADODB_FETCH_NUM);
    $ta_can_sets_ary = explode(',', $ta_can_sets);
}

// 教材編號
if (defined('ENV_TEACHER')) {
    if (in_array('content_id', $ta_can_sets_ary)) {
        if (isset($_POST['ck_content_id'])) {
            $content_id = ($_POST['content_id'] == 0) ? '""' : $_POST['content_id'];
        } else {
            $content_id = '0';
        }
    }
} else {
    if (isset($_POST['ck_content_id'])) {
        $content_id = ($_POST['content_id'] == 0) ? '""' : $_POST['content_id'];
    } else {
        $content_id = '0';
    }
}

// 多語系的欄位
$caption = addslashes(serialize($lang));

// 處理日期Begin
$new  = '';
$modi = '';

// 管理者或可編輯欄位
if ($sysSession->env === 'academic' || in_array('st_begin', $ta_can_sets_ary)) {
    if (trim($_POST['st_begin'])) {
        $st_begin = date('Y-m-d',strtotime($_POST['st_begin']));
        $new .= "'{$st_begin}',";
        $modi .= "`st_begin`='{$st_begin}',";
    } else {
        $st_begin = 'NULL';
        $new .= "{$st_begin},";
        $modi .= "`st_begin`={$st_begin},";
    }

    if (trim($_POST['st_end']) != '') {
        $st_end = date('Y-m-d',strtotime($_POST['st_end']));
        $new .= "'{$st_end}',";
        $modi .= "`st_end`='{$st_end}',";
    } else {
        $st_end = 'NULL';
        $new .= "{$st_end},";
        $modi .= "`st_end`={$st_end},";
    }
}

if (intval($_POST['st_option']) == 1) {
    $modi .= "`st_limit`=1,";
}

// 管理者或可編輯欄位
if ($sysSession->env === 'academic' || in_array('en_begin', $ta_can_sets_ary)) {
    if (isset($_POST['en_begin']) && (trim($_POST['en_begin']) != '')) {
        $en_begin = date('Y-m-d',strtotime($_POST['en_begin']));
        $new .= "'{$en_begin}',";
        $modi .= "`en_begin`='{$en_begin}',";
    } else {
        $en_begin = 'NULL';
        $new .= "{$en_begin},";
        $modi .= "`en_begin`={$en_begin},";
    }

    if (isset($_POST['en_end']) && trim($_POST['en_end'])) {
        $en_end = date('Y-m-d',strtotime($_POST['en_end']));
        $new .= "'{$en_end}',";
        $modi .= "`en_end`='{$en_end}',";
    } else {
        $en_end = 'NULL';
        $new .= "{$en_end},";
        $modi .= "`en_end`={$en_end},";
    }
}

if (intval($_POST['st_option']) == 1) {
    $modi .= "`st_limit`=1,";
}

if (!empty($new)) $new  = substr($new, 0, -1);
if (!empty($modi)) $modi = substr($modi, 0, -1);
// 處理日期End

$fair_grade = ($_POST['fair_grade'] == '') ? 60 : intval($_POST['fair_grade']);

if ($actType == 'Create') {

    if ('ajax' == $_POST['method']) {
        $ta_can_sets = implode(',',$default_ta_can_sets);
    }

    if (empty($_POST['quota_limit'])){
        $_POST['quota_limit'] = getDefaultQuota();
    }
    
    // 先將資料儲存到資料庫中
    $fields = '`content_id`, `caption`, `teacher`, `kind`, `st_begin`, `st_end`, `en_begin`, `en_end`, `status`, ' . '`texts`, `url`, `content`, `credit`, `discuss`, `bulletin`, `n_limit`, `a_limit`, ' . '`quota_used`, `quota_limit`, `path`, `login_times`, `post_times`, `dsc_times`, `fair_grade`,`ta_can_sets`, `creator`';
    
    $value = "{$content_id}, '{$caption}', '{$_POST['teacher']}', 'course', " . "$new" . ", {$_POST['status']}, " . "'{$_POST['texts']}', '{$_POST['url']}', '{$_POST['content']}', " . "{$_POST['credit']}, NULL,  NULL, '{$_POST['n_limit']}', '{$_POST['a_limit']}', " . "0, '{$_POST['quota_limit']}', '', 0, 0, 0, {$fair_grade} ,'{$ta_can_sets}', '{$sysSession->username}'";

    $RS = dbNew('WM_term_course', $fields, $value);
    
    if ($sysConn->Affected_Rows()) {
        $csid      = $sysConn->Insert_ID();
        // 建立課程目錄
        $SchCourse = "/base/{$sysSession->school_id}/course";
        @mkdir(sysDocumentRoot . $SchCourse, 0755);
        $CoursePath = "/base/{$sysSession->school_id}/course/{$csid}";
        @mkdir(sysDocumentRoot . $CoursePath, 0755);
        @mkdir(sysDocumentRoot . $CoursePath . '/chat', 0755);
        @mkdir(sysDocumentRoot . $CoursePath . '/board', 0755);
        @mkdir(sysDocumentRoot . $CoursePath . '/quint', 0755);
        @mkdir(sysDocumentRoot . $CoursePath . '/content', 0755);
        
        // 將課程的目錄儲存到資料庫
        dbSet('WM_term_course', "path='{$CoursePath}'", "course_id={$csid}");
        
        // 建立第一個教材路徑
        addTermPath($csid);
        
        // 建立課程討論板
        $bname     = $MSG['discuss'];
        $board_id1 = addBoards($csid, $bname);
        if (!$board_id1)
            $board_id1 = 'NULL';
        
        // 建立課程公告板
        $bname     = $MSG['bulletin'];
        $board_id2 = addBoards($csid, $bname);
        if (!$board_id2)
            $board_id2 = 'NULL';
        
        // 儲存討論板的 board_id
        dbSet('WM_term_course', "discuss={$board_id1}, bulletin={$board_id2}", "course_id={$csid}");
        
        // 設定審核規則 (begin)
        dbNew('WM_review_sysidx', 'discren_id, flow_serial', "'{$csid}','{$_POST['review']}'");
        
        // ********************** (BEGIN)
        // 新增預設討論室
        $chat_open     = '0000-00-00 00:00:00';
        $chat_close    = '0000-00-00 00:00:00';
        $chat_media    = 'disable';
        $chat_protocol = 'TCP';
        $chat_login    = 'N';
        
        $dd    = array(
            'title' => serialize($MSG['sync_chat_room']), // Bug#1388 修改「新開討論室」為「同步討論室」 by Small 2006/09/07
            'limit' => 0,
            'exitAct' => 'forum',
            'jump' => 'deny',
            'status' => 'open',
            'visibility' => 'visible',
            'media' => $chat_media,
            'ip' => '',
            'port' => 0,
            'protocol' => $chat_protocol,
            'host' => '',
            'login' => $chat_login
        );
        $rid   = uniqid('');
        $owner = $csid;
        dbNew('WM_chat_setting', '`rid`, `owner`, `title`, `host` , `get_host`, ' . '`maximum`, `exit_action`, `jump`, `open_time`, `close_time`, ' . '`state`, `visibility`, `media`, `ip`, `port`, `protocol`', "'{$rid}', '{$owner}', '{$dd['title']}', '{$dd['host']}', '{$dd['login']}', " . "{$dd['limit']}, '{$dd['exitAct']}', '{$dd['jump']}', '{$chat_open}', '{$chat_close}', " . "'{$dd['status']}', '{$dd['visibility']}', '{$dd['media']}', '{$dd['ip']}', {$dd['port']}, '{$dd['protocol']}'");
        // ********************** (END)
        $st_begin_str = $st_begin != 'NULL' && preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $st_begin) ? "'{$st_begin}'" : 'NULL';
        $st_end_str   = $st_end != 'NULL' && preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $st_end) ? "'{$st_end}'" : 'NULL';
        // 新增的預設點名
        dbNew('WM_roll_call', 'course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,' . 'begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc', "{$csid}, 0, 0, 'disable', 'student','lesson', 'off', 'greater', '7', 'week', 'Saturday', {$st_begin_str}, {$st_end_str}, '{$MSG['roll_call_mail_subject_default1'][$sysSession->lang]}', '{$MSG['roll_call_mail_content_default1'][$sysSession->lang]}', '', 0");
        
        dbNew('WM_roll_call', 'course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,' . 'begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc', "{$csid}, 0, 0, 'disable', 'student','exam', 'no', 'greater_equal', '1', 'week', 'Saturday', {$st_begin_str}, {$st_end_str}, '{$MSG['roll_call_mail_subject_default2'][$sysSession->lang]}', '{$MSG['roll_call_mail_content_default2'][$sysSession->lang]}', '', 0");
        
        dbNew('WM_roll_call', 'course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,' . 'begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc', "{$csid}, 0, 0, 'disable', 'student','homework', 'no', 'greater_equal', '1', 'week', 'Saturday', {$st_begin_str}, {$st_end_str}, '{$MSG['roll_call_mail_subject_default3'][$sysSession->lang]}', '{$MSG['roll_call_mail_content_default3'][$sysSession->lang]}', '', 0");
        
        dbNew('WM_roll_call', 'course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,' . 'begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc', "{$csid}, 0, 0, 'disable', 'student','questionnaire', 'no', 'greater_equal', '1', 'week', 'Saturday', {$st_begin_str}, {$st_end_str}, '{$MSG['roll_call_mail_subject_default4'][$sysSession->lang]}', '{$MSG['roll_call_mail_content_default4'][$sysSession->lang]}', '', 0");
        
        if ('ajax' == $_POST['method']) {
            dbNew('WM_term_major', 'username,course_id,role,add_time', "'$sysSession->username',$csid,512,now()");
        }
        
        $isSuccess = true;
    } else {
        $isSuccess = false;
    }
    
    $rtn['flag'] = $isSuccess;
    $rtn['id']   = sysEncode($csid);
    $rtn['text'] = md5($sysSession->school_id . $sysSession->school_name . 'Edit' . $sysSession->username);
    $msg         = json_encode($rtn);
    echo $msg;
    exit;
}

if ($actType == 'Edit') {
    $csid = trim($_POST['csid']);
    $csid = sysDecode($csid);
    
    if (isset($_POST['step']) && trim($_POST['step']) == 0) {
        
    } else if (isset($_POST['step']) && trim($_POST['step']) == 1) {
        
        if (!in_array($_POST['status'], array(
            0,
            1,
            2,
            3,
            4,
            5
        ))) {
            $_POST['status'] = 0;
        }
        
        $isGpSuccess = false;
        // 儲存課程群組 (Begin)
        if (empty($_POST['course_kind'])){
            $cparent = array();
        }else{
            $cparent = explode(',', trim($_POST['course_kind']));
        }

        $arows       = setCourse2Group($csid, $cparent);
        if ($arows > 0) {
            $isGpSuccess = true;
        } else {
            $isGpSuccess = false;
        }
        // 儲存課程群組 (End)
        
        if ($_POST['fees'] == 0) {
            $_POST['fee'] = 'NULL';
        }
        
        // 資料庫：非輔導期間是否對學員開放
        $_POST['nolimit'] = $_POST['st_option'];

        // 預設要更新的所有欄位
        $defaultFields = array(
            'caption' => sprintf("'%s'", $caption),
            'status' => $_POST['status'],
            
            // "NULL" 要用雙引號，不可以用單引號
            'en_begin' => (($en_begin === NULL)||($en_begin == 'NULL')) ? "NULL" : sprintf("'%s'", $en_begin),
            'en_end' => (($en_end === NULL)||($en_end == 'NULL')) ? "NULL" : sprintf("'%s'", $en_end),
            'st_begin' => (($st_begin === NULL)||($st_begin == 'NULL')) ? "NULL" : sprintf("'%s'", $st_begin),
            'st_end' => (($st_end === NULL)||($st_end == 'NULL')) ? "NULL" : sprintf("'%s'", $st_end),
            
            'credit' => $_POST['credit'],
            'fee' => $_POST['fee'],
            'st_limit' => $_POST['nolimit'],
            'n_limit' => $_POST['n_limit'],
            'a_limit' => $_POST['a_limit'],
            'fair_grade' => $fair_grade,
            'content_id' => $content_id
        );
        
        if ($sysSession->env === 'teach') {
            // 移除沒有權限的欄位
            // $k 不可以拿掉
//            echo '<pre>';
//            var_dump($_POST);
//            var_dump($ta_can_sets_ary);
//            var_dump($ta_cant_sets_ary);
//            echo '</pre>';
            foreach($ta_cant_sets_ary as $k => $v) {
                unset($defaultFields[$v]);
                if ($v == 'en_begin') {
                    unset($defaultFields['en_end']);
                }
                if ($v == 'st_begin') {
                    unset($defaultFields['st_end']);
                }
            }
        }
        
        // 沒有權限設定上課期間時，則不更新 非輔導期間是否對學員開放 欄位
        if (isset($_POST['st_option']) === false) {
            unset($defaultFields['st_limit']);
        }
        
//        echo '<pre>';
//        var_dump($defaultFields);
//        echo '</pre>';
        
        // 組要更新的欄位字串
        $fields = '';
        foreach($defaultFields as $k => $v) {
            $fields .= sprintf("`%s` = %s, ", $k, $v);
        }
        
        // 移除字串尾「, 」
        $fields = substr($fields, 0, strlen($fields) - 2);

        if ($sysSession->env === 'academic') {
            if (empty($_POST['quota_limit'])){
                $_POST['quota_limit'] = getDefaultQuota();
            }
            $fields .= sprintf(", `teacher`='%s', `quota_limit`=%d", $_POST['teacher'], $_POST['quota_limit']);
        }

        // 設定審核規則 (begin)
        if ($sysSession->env === 'academic' || in_array('review', $ta_can_sets_ary)) {
            if (getReviewSerial($csid) == -1) {
                dbNew('WM_review_sysidx', 'discren_id, flow_serial', "'{$csid}','{$_POST['review']}'");
            } else {
                dbSet('WM_review_sysidx', "flow_serial='{$_POST['review']}'", "discren_id={$csid}");
            }
        }

        if ($sysConn->Affected_Rows() > 0 || $r === true) {
            $isSuccess = true || $isSuccess;
        } else {
            $isSuccess = false || $isSuccess;
        }
        // 設定審核規則 (end)
        
    } else if (isset($_POST['step']) && trim($_POST['step']) == 2) {
        
        // 預設要更新的所有欄位
        $defaultFields = array(
            'content' => sprintf("'%s'", $_POST['content']),
            'texts' => sprintf("'%s'", $_POST['texts']),
            'subhead' => sprintf("'%s'", strip_scr($_POST['subhead'])),
        );
        
        if ($sysSession->env === 'teach') {
            // 移除沒有權限的欄位
            foreach($ta_cant_sets_ary as $v) {
                unset($defaultFields[$v]);
            }
        }
        
        // 組要更新的欄位字串
        $fields = '';
        foreach($defaultFields as $k => $v) {
            $fields .= sprintf('`%s` = %s, ', $k, $v);
        }
        
        // 移除字串尾「, 」
        $fields = substr($fields, 0, strlen($fields) - 2);
    } else if (isset($_POST['step']) && trim($_POST['step']) == 3) {
        
        $_POST['goal']     = array_diff($_POST['goal'], array(
            '',
            ' '
        ));
        $_POST['audience'] = array_diff($_POST['audience'], array(
            '',
            ' '
        ));
        
        $goal     = addslashes(serialize($_POST['goal']));
        $audience = addslashes(serialize($_POST['audience']));
        $formal   = addslashes(serialize($_POST['formal']));
        $gallery  = addslashes(serialize($_POST['gallery']));
        
        if (!empty($_POST['r_title'])) {
            $_POST['r_title'] = array_diff($_POST['r_title'], array(
                '',
                ' '
            ));
            $r_title          = addslashes(serialize($_POST['r_title']));
        }
        if (!empty($_POST['r_addr'])) {
            $_POST['r_addr'] = array_diff($_POST['r_addr'], array(
                '',
                ' '
            ));
            $r_addr          = addslashes(serialize($_POST['r_addr']));
        }
        
        if (empty($_POST['is_use']))
            $_POST['is_use'] = array();
        if (empty($_POST['sel_formal']))
            $_POST['sel_formal'] = array();
        if (empty($_POST['sel_gallery']))
            $_POST['sel_gallery'] = array();
        
        $_POST['is_use'] = array_merge($_POST['is_use'], $_POST['sel_formal'], $_POST['sel_gallery']);
        $is_use          = addslashes(serialize($_POST['is_use']));
        
        
        $fields = "`fair_grade`='{$_POST['fair_grade']}',`goal`='{$goal}',`audience`='{$audience}',`ref_title`='{$r_title}',`ref_url`='{$r_addr}',`formal_pass`='{$formal}',`gallery_pass`='{$gallery}',`is_use`='{$is_use}'";
    } else if (isset($_POST['step']) && trim($_POST['step']) == 5) {
        $tmp      = getCourseData($csid);
        // 檢查開課必填項目
        $openErr  = false;
        // 至少有一課程內容(課程內容 = 學習路徑)
        $pContent = dbGetOne($useDb . '`WM_term_path`', '`content`', '`course_id` =' . $csid . ' ORDER BY `update_time` desc ');
        if (false == strpos($pContent, 'item')) {
            $openErr   = true;
            $openMsg[] = $MSG['msg_no_course_content'][$sysSession->lang];
        }
        // 課程簡介
        if ($tmp['content'] == null || $tmp['content'] == '') {
            $openErr   = true;
            $openMsg[] = $MSG['msg_no_course_description'][$sysSession->lang];
        }
        // 輔導課時需有開課起訖日(自學不用)
        if ($tmp['course_type'] == 'coach') {
            if ($tmp['st_begin'] == '' || $tmp['st_end'] == '') {
                $openErr   = true;
                $openMsg[] = $MSG['msg_coach_need_date'][$sysSession->lang];
            }
        }
        // 費用(模組有開時)
        if (($tmp['fee'] != NULL || $tmp['fee'] != '') && $tmp['fee'] <= 0) {
            $openErr   = true;
            $openMsg[] = $MSG['msg_need_fill_paid'][$sysSession->lang];
        }
        if ($openErr != true) {
        } else {
            $openMsg = implode(',', $openMsg);
        }
        
    }
    
    if (isset($_POST['step']) && trim($_POST['step']) == 6) {
        $tech_obj = new WMteacher();
        if (isset($_POST['assistant_auth'])) {
            $arr_assistant    = array();
            $arr_assistant    = explode(',', $_POST['assistant_auth']);
            $arr_oriassistant = array();
            $arr_oriassistant = explode(',', $_POST['oriassistant']);
            foreach ($arr_oriassistant as $key => $val) {
                if (!in_array($val, $arr_assistant)) {
                    $tech_obj->remove($val, $sysRoles['assistant'], $csid);
                }
            }
            for ($i = 0; $i < count($arr_assistant); $i++) {
                if ($arr_assistant[$i] == '')
                    continue;
                if (!aclCheckRole($arr_assistant[$i], $sysRoles['teacher'], $csid)) {
                    $tech_obj->assign($arr_assistant[$i], $sysRoles['assistant'], $csid);
                }
            }
        }
        
        if (isset($_POST['instructor_auth'])) {
            $arr_instructor    = array();
            $arr_instructor    = explode(',', $_POST['instructor_auth']);
            $arr_oriinstructor = array();
            $arr_oriinstructor = explode(',', $_POST['oriinstructor']);
            foreach ($arr_oriinstructor as $key => $val) {
                if (!in_array($val, $arr_instructor)) {
                    $tech_obj->remove($val, $sysRoles['instructor'], $csid);
                }
            }
            for ($i = 0; $i < count($arr_instructor); $i++) {
                if ($arr_instructor[$i] == '')
                    continue;
                if (!aclCheckRole($arr_instructor[$i], $sysRoles['teacher'], $csid)) {
                    $tech_obj->assign($arr_instructor[$i], $sysRoles['instructor'], $csid);
                }
            }
        }
        if (isset($_POST['teach_auth'])) {
            $arr_teach    = array();
            $arr_teach    = explode(',', $_POST['teach_auth']);
            $arr_oriteach = array();
            $arr_oriteach = explode(',', $_POST['oriteach']);
            foreach ($arr_oriteach as $key => $val) {
                if (!in_array($val, $arr_teach)) {
                    $tech_obj->remove($val, $sysRoles['teacher'], $csid);
                }
            }
            for ($i = 0; $i < count($arr_teach); $i++) {
                if ($arr_teach[$i] == '')
                    continue;
                $tech_obj->assign($arr_teach[$i], $sysRoles['teacher'], $csid);
            }
        }
        
        // 管理者環境更新老師可編輯欄位
        if ($sysSession->env === 'academic') {
            $fields = "ta_can_sets='{$ta_can_sets}'";
        }
        dbSet('WM_term_course', $fields, "course_id={$csid}");
    } else {
        dbSet('WM_term_course', $fields, "course_id={$csid}");
    }
    
    if ($sysConn->Affected_Rows() >= 0) {
        $isSuccess = true;
    } else {
        $isSuccess = false;
    }
    
}

// 與行事曆同步 start
//$sysConn->debug=true;
$calendar_begin_type = 'course_begin';
$calendar_end_type   = 'course_end';
$begin_cal_idx       = $sysConn->GetOne("select idx from WM_calendar where relative_type='{$calendar_begin_type}' and relative_id={$csid}");
$end_cal_idx         = $sysConn->GetOne("select idx from WM_calendar where relative_type='{$calendar_end_type}' and relative_id={$csid}");
$username            = $csid;
$type                = 'course';
$repeat              = 'none';
$repeat_begin        = '0000-00-00';
$repeat_end          = '0000-00-00';
$alertType           = "email";
$alertBefore         = "3";
$ishtml              = "text";
if (isset($_POST['ck_sync_st_end']))
    $_POST['ck_sync_st_begin'] = 1;
if (isset($_POST['ck_sync_st_begin'])) {
    if ($actType == 'Edit' && $begin_cal_idx) {
        //刪除舊的行事曆
        dbDel('WM_calendar', 'idx=' . $begin_cal_idx);
    }
    $memo_date = $st_begin;
    $timeBegin = 'NULL';
    $timeEnd   = 'NULL';
    $subject   = $MSG['th_study_begin'][$sysSession->lang];
    $content   = "";
    $fields    = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, ' . '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' . '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
    $values    = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" . ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" . ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_begin_type}','{$csid}'";
    dbNew('WM_calendar', $fields, $values);
} else {
    if ($actType == 'Edit' && $begin_cal_idx) {
        //刪除舊的行事曆
        dbDel('WM_calendar', 'idx=' . $begin_cal_idx);
    }
}

if (isset($_POST['ck_sync_st_end'])) {
    if ($actType == 'Edit' && $end_cal_idx) {
        //刪除舊的行事曆
        dbDel('WM_calendar', 'idx=' . $end_cal_idx);
    }
    $memo_date = $st_end;
    $timeBegin = 'NULL';
    $timeEnd   = 'NULL';
    $subject   = $MSG['th_study_end'][$sysSession->lang];
    $content   = "";
    $fields    = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, ' . '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' . '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
    $values    = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" . ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" . ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_end_type}','{$csid}'";
    dbNew('WM_calendar', $fields, $values);
} else {
    if ($actType == 'Edit' && $end_cal_idx) {
        //刪除舊的行事曆
        dbDel('WM_calendar', 'idx=' . $end_cal_idx);
    }
}

//$sysConn->debug=false;
// 與行事曆同步 end

if (sysEnableAppCoursePicture) {
    // APP課程圖片模組有啟用
    // 更新課程代表圖 (Begin)
    $appPictureInfoFile = sysDocumentRoot . sprintf('/user/%1s/%1s/%s/appCoursePictureData.txt', substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
    if ($actType === 'Create' && is_file($appPictureInfoFile) && file_get_contents($appPictureInfoFile) !== '') {
        $appCoursePictureInfo = explode('#', file_get_contents($appPictureInfoFile));
        
        if (is_file($appCoursePictureInfo[0])) {
            $pictureContent = addslashes(base64_encode(file_get_contents($appCoursePictureInfo[0])));
            
            $mimeFileType = $appCoursePictureInfo[1];
            
            $table  = 'CO_course_picture';
            $fields = '`course_id`, `picture`, `mime_type`';
            $values = "{$csid}, '{$pictureContent}', '{$mimeFileType}'";
            dbNew($table, $fields, $values);
            
            // 新增完畢則刪除暫存檔
            unlink($appPictureInfoFile);
        }
    }
    // 更新課程代表圖 (End)
}

$msg = ($actType == 'Edit') ? $MSG['title_modify_course'][$sysSession->lang] : $MSG['title_add_course'][$sysSession->lang];
$msg .= ($isSuccess || $isGpSuccess) ? $MSG['save_successed'][$sysSession->lang] : $MSG['save_failed'][$sysSession->lang];
wmSysLog($sysSession->cur_func, defined('ENV_TEACHER') ? $csid : $sysSession->school_id, 0, 0, 'auto', $_SERVER['PHP_SELF'], $msg . $csid);

if (isset($_POST['step']) && trim($_POST['step']) == 5) {
    if ($openErr != true) {
        $msg = $MSG['btn_open_success'][$sysSession->lang];
    } else {
        $isSuccess = false;
        $msg       = $openMsg;
    }
}

$rtn['flag'] = $isSuccess;
$rtn['id']   = $csid;
$rtn['text'] = $msg;
$msg         = json_encode($rtn);
echo $msg;
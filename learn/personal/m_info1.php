<?php
/**
 * 儲存個人設定
 *
 * 建立日期：2014/01/21
 * @author  Spring
 * @version $Id: info1.php,v 1.1 2010/02/24 02:39:10 saly Exp $
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
require_once(sysDocumentRoot . '/lib/username.php'); // CheckRealname
require_once(sysDocumentRoot . '/breeze/global.php');
require_once(sysDocumentRoot . '/breeze/doUpdatePwd.php');
require_once(sysDocumentRoot . '/mooc/models/school.php'); //使用 getSchoolStudentMooc
require_once(sysDocumentRoot . '/lib/Hongu.php');
require_once(sysDocumentRoot . '/lang/hongu_validate_msg.php');
require_once(sysDocumentRoot . '/lang/personal.php');

$sysSession->cur_func = '400400500';
$sysSession->restore();
if (!aclVerifyPermission(400400500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

if (!isset($DIRECT_MEMBER) || empty($username)) {
    $username   = $sysSession->username;
    $uri_target = 'info.php';
    $uri_parent = 'about:blank';
}
// 檢查 ticket 是不是吻合
$ticket = md5($username . $sysSession->school_id . $sysSession->ticket);
if ($ticket != trim($_POST['ticket'])) {
    echo 'Access deny.';
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'others', $_SERVER['PHP_SELF'], '拒絕存取!');
    exit();
}


// 驗證表單數值

$messages = _formValidation();

$result = array(
    'success' => true,
    'id' => 0,
    'ticket' => '',
    'message' => ''
);

if (count($messages) >= 1) {
    $errMsg = array();
    for ($i = 0, $size = count($messages); $i < $size; $i++) {
        $errMsg[] = $messages[$i];
    }
    $result = array(
        'error' => $errMsg,
        'imgerror' => ''
    );
    echo json_encode($result);
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
            case 'password':
            case 'opassword':
            case 'repassword':
                $rules[$key] = array(
                    $rule->MAKE_RULE('Required', null, $MSG['hv_msg_required'][$sysSession->lang]),
                    $rule->MAKE_RULE('PasswordHalfChar', null, $MSG['hv_msg_pwd_format'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'last_name':
            case 'first_name':
                $text_range  = $MSG['hv_msg_text_range'][$sysSession->lang];
                $text_range  = str_replace("%min%", 1, $text_range);
                $text_range  = str_replace("%max%", 32, $text_range);
                $rules[$key] = array(
                    $rule->MAKE_RULE('Required', null, $MSG['hv_msg_required'][$sysSession->lang]),
                    $rule->MAKE_RULE('Length', array(
                        'min' => 1,
                        'max' => 32
                    ), $text_range),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'gender':
                $rules[$key] = array(
                    $rule->MAKE_RULE('Required', null, $MSG['hv_msg_required'][$sysSession->lang]),
                    $rule->MAKE_RULE('InValues', array(
                        'M',
                        'F',
                        'N'
                    ), $MSG['hv_msg_value_list'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'email':
                $rules[$key] = array(
                    $rule->MAKE_RULE('Required', null, $MSG['hv_msg_required'][$sysSession->lang]),
                    $rule->MAKE_RULE('Email', null, $MSG['hv_msg_email_format'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'birthday':
                if ($value == '0000-00-00') {
                    break;
                }
                $rules[$key] = array(
                    $rule->MAKE_RULE('Date', null, $MSG['hv_msg_date_format'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'company':
                $text_range  = $MSG['hv_msg_text_range'][$sysSession->lang];
                $text_range  = str_replace("%min%", 0, $text_range);
                $text_range  = str_replace("%max%", 255, $text_range);
                $rules[$key] = array(
                    $rule->MAKE_RULE('Length', array(
                        'min' => 0,
                        'max' => 255
                    ), $text_range),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'department':
                $text_range  = $MSG['hv_msg_text_range'][$sysSession->lang];
                $text_range  = str_replace("%min%", 0, $text_range);
                $text_range  = str_replace("%max%", 64, $text_range);
                $rules[$key] = array(
                    $rule->MAKE_RULE('Length', array(
                        'min' => 0,
                        'max' => 64
                    ), $text_range),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'title':
                $text_range  = $MSG['hv_msg_text_range'][$sysSession->lang];
                $text_range  = str_replace("%min%", 0, $text_range);
                $text_range  = str_replace("%max%", 32, $text_range);
                $rules[$key] = array(
                    $rule->MAKE_RULE('Length', array(
                        'min' => 0,
                        'max' => 32
                    ), $text_range),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'cell_phone':
                $_POST[$key] = trim($_POST[$key]);
                $rules[$key] = array(
                    $rule->MAKE_RULE('CellPhone', null, $MSG['hv_msg_cellphone_format'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'home_tel':
            case 'home_fax':
            case 'office_tel':
            case 'office_fax':
                $rules[$key] = array(
                    $rule->MAKE_RULE('Telephone', null, $MSG['hv_msg_tel_format'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'home_address':
            case 'office_address':
                $text_range  = $MSG['hv_msg_text_range'][$sysSession->lang];
                $text_range  = str_replace("%min%", 0, $text_range);
                $text_range  = str_replace("%max%", 255, $text_range);
                $rules[$key] = array(
                    $rule->MAKE_RULE('Length', array(
                        'min' => 0,
                        'max' => 255
                    ), $text_range),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'language':
                $default_lang = array(
                    'Big5' => '繁體中文',
                    'GB2312' => '簡體中文',
                    'en' => '英文'
                );
                removeUnAvailableChars($default_lang);
                foreach ($default_lang as $k => $v) {
                    $vArray[] = $k;
                }
                $rules[$key] = array(
                    $rule->MAKE_RULE('InValues', $vArray, $MSG['hv_msg_value_list'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'msg_reserved':
                $rules[$key] = array(
                    $rule->MAKE_RULE('InValues', array(
                        0,
                        1
                    ), $MSG['hv_msg_value_list'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'hid':
                $rules[$key] = array(
                    $rule->MAKE_RULE('HalfNumber', null, $MSG['hv_msg_positive_integer'][$sysSession->lang]),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
                break;
            case 'tagtitle':
                $text_range  = $MSG['hv_msg_text_range'][$sysSession->lang];
                $text_range  = str_replace("%min%", 0, $text_range);
                $text_range  = str_replace("%max%", 255, $text_range);
                $rules[$key] = array(
                    $rule->MAKE_RULE('Required', null, $MSG['hv_msg_required'][$sysSession->lang]),
                    $rule->MAKE_RULE('Length', array(
                        'min' => 0,
                        'max' => 255
                    ), $text_range),
                    $rule->MAKE_RULE('XssAttack', null, $MSG['hv_msg_xss'][$sysSession->lang])
                );
            case 'tagline':
            case 'picture':
            case 'country':
            case 'user_status':
            case 'education':
            default:
                break;
        }
    }
    
    $params = $_POST;
    $valid  = $hongu->getValidator();
    if (!empty($rules)) {
        return $valid->check($params, $rules);
    } else {
        return array();
    }
    
}

// 串 SQL 字串，並處理資料
$sqls = '';
foreach ($_POST as $key => $value) {
	
    if ($key == 'picture' || $key == 'ticket')
        continue;
    $val = '';
    switch ($key) {
        case 'password':
            $val = trim($_POST[$key]);
            if (!empty($val))
                $val = md5($val);
            break;
        case 'birthday':
            $val = trim($_POST['birthday']);
            break;
        case 'hid':
            if (is_array($_POST[$key])) {
                $val = array_sum($_POST[$key]);
            } else {
                $val = 0;
            }
            $hid = $val;
            break;
        case 'last_name':
        case 'first_name':
            $val = Filter_Spec_char(trim($_POST[$key]));
            break;
        default:
            if (!is_array($_POST[$key])) {
                $val = trim($_POST[$key]);
            }
    }
    if (($key == 'password') && empty($val))
        continue;
    $sqls .= "{$key}='{$val}', ";
}

$sqls = substr($sqls, 0, -2);
if (isset($_POST['change_pic'])) $sqls = '';

// 判斷密碼
if (!empty($_POST['password']) && !empty($_POST['repassword'])) {
    if (trim($_POST['password']) == trim($_POST['repassword'])) {
        $pwd       = dbGetOne('`WM_user_account`', '`password`', "username='{$username}'");
        $val       = md5(trim($_POST['password']));
        $pwdErrMsg = array();
        if (!empty($val) && $pwd == md5(trim($_POST['opassword']))) {
            $sqls = "password='{$val}'";
            if (breeze == 'Y') {
                doUpdateBreezePwd($username, substr($val, 0, 10));
            }
            $result['message'] = $MSG['chg_pwd_successful'][$sysSession->lang];
        } else {
            $pwdErrMsg[] = array(
                'id' => 'opassword',
                'message' => $MSG['pwd_incorrect'][$sysSession->lang],
                'rule' => 'Custom_pwd_rule'
            );
            $result      = array(
                'error' => $pwdErrMsg,
                'imgerror' => ''
            );
            echo json_encode($result);
            die();
        }
    } else {
        $pwdErrMsg[] = array(
            'id' => 'repassword',
            'message' => $MSG['confirm_pwd_different'][$sysSession->lang],
            'rule' => 'Custom_pwd_rule'
        );
        $result      = array(
            'error' => $pwdErrMsg,
            'imgerror' => ''
        );
        echo json_encode($result);
        die();
    }
}
/*
$val = trim($_POST['password']);
if (!empty($val)) {
$val = md5($val);
$sqls = "password='{$val}', {$sqls}";
if (breeze == 'Y')
{
doUpdateBreezePwd($username, substr($val,0,10));
}
}
*/
// $rsSchool = new school();
// 不能隱藏的欄位

// 更新個人資料 (Begin)
//$sysConn->BeginTrans();
if ($username == sysRootAccount && $sysSession->username != sysRootAccount) {
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 2, 'others', $_SERVER['PHP_SELF'], '"' . sysRootAccount . '" account only can be modified by himself.');
    die('"' . sysRootAccount . '" account only can be modified by himself.');
}
/*
if ($sqls != '') {
$RS = dbSet('WM_user_account', $sqls, "username='{$username}'");
wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], '更新個人設定!');
}

if ($RS) {
$RS = dbSet('WM_all_account', $sqls, "username='{$username}'");
}
* 
*/
// 直接存入 WM_all_account
if ($sqls != '') {
    $RS = dbSet('WM_all_account', $sqls, "username='{$username}'");
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 0, 'others', $_SERVER['PHP_SELF'], '更新個人設定!');
}

// 顯示的值對應
$enableValue        = array(
    'picture' => 32,
    'gender' => 4,
    'email' => 0,
    'birthday' => 8,
//    'country' => 262144,
//    'user_status' => 524288,
//    'education' => 1048576,
    'company' => 32768,
    'department' => 65536,
    'title' => 131072,
    'cell_phone' => 16384,
    'home_tel' => 256,
    'home_fax' => 512,
    'home_address' => 1024,
    'office_tel' => 2048,
    'office_fax' => 4096,
    'office_address' => 8192
);
// 使用者願意顯示的資料
$userRS             = dbGetStSr('`WM_user_account`', '*', "username='{$username}'");
$hhid               = $userRS['hid'];
$userRS['gender']   = ($userRS['gender'] == 'N') ? $MSG['not_marked'][$sysSession->lang] : (($userRS['gender'] == 'M') ? $MSG['male'][$sysSession->lang] : $MSG['female'][$sysSession->lang]);
$userRS['birthday'] = ($userRS['birthday'] == '0000-00-00' || $userRS['birthday'] == '') ? $MSG['not_marked'][$sysSession->lang] : $userRS['birthday'];

if ($userRS['user_status'] == 'S') {
    $userRS['user_status'] = $MSG['student'][$sysSession->lang];
} else if ($userRS['user_status'] == 'W') {
    $userRS['user_status'] = $MSG['at_work'][$sysSession->lang];
} else {
    $userRS['user_status'] = $MSG['not_marked'][$sysSession->lang];
}

if ($userRS['country'] == 'TW') {
    $userRS['country'] = $MSG['TW'][$sysSession->lang];
} else if ($userRS['country'] == 'CH') {
    $userRS['country'] = $MSG['CH'][$sysSession->lang];
} else if ($userRS['country'] == 'JA') {
    $userRS['country'] = $MSG['JA'][$sysSession->lang];
} else if ($userRS['country'] == 'IN') {
    $userRS['country'] = $MSG['IN'][$sysSession->lang];
} else if ($userRS['country'] == 'US') {
    $userRS['country'] = $MSG['US'][$sysSession->lang];
} else if ($userRS['country'] == 'AS') {
    $userRS['country'] = $MSG['AS'][$sysSession->lang];
} else if ($userRS['country'] == 'O') {
    $userRS['country'] = $MSG['other'][$sysSession->lang];
} else {
    $userRS['country'] = $MSG['not_marked'][$sysSession->lang];
}

if ($userRS['education'] == 'P') {
    $userRS['education'] = $MSG['elementary_school'][$sysSession->lang];
} else if ($userRS['education'] == 'H') {
    $userRS['education'] = $MSG['junior_high_school'][$sysSession->lang];
} else if ($userRS['education'] == 'S') {
    $userRS['education'] = $MSG['high_school'][$sysSession->lang];
} else if ($userRS['education'] == 'U') {
    $userRS['education'] = $MSG['university'][$sysSession->lang];
} else if ($userRS['education'] == 'M') {
    $userRS['education'] = $MSG['masters_degree'][$sysSession->lang];
} else if ($userRS['education'] == 'D') {
    $userRS['education'] = $MSG['doctoral_degree'][$sysSession->lang];
} else if ($userRS['education'] == 'O') {
    $userRS['education'] = $MSG['other'][$sysSession->lang];
} else {
    $userRS['education'] = $MSG['not_marked'][$sysSession->lang];
}

$enableValueCnt    = 0;
// 取得使用者大頭照連結
$enc               = @mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $username, 'ecb');
$ids               = base64_encode($enc);
$userRS['picture'] = 'showpic.php?a=' . $ids . '&' . uniqid('');
foreach ($enableValue as $key => $val) {
    if ($key == 'picture') {
        $enableData[$enableValueCnt] = array(
            'name' => 'picture',
            'title' => '',
            'value' => ''
        );
    }
    if (!((int)$hhid&$val)) {
        $enableData[$enableValueCnt]['name']  = $key;
        $enableData[$enableValueCnt]['title'] = $MSG[$key][$sysSession->lang];
        $enableData[$enableValueCnt]['value'] = $userRS[$key];
    }
    $enableValueCnt++;
}
// 必顯示資料first_name
$realname                      = checkRealname($userRS['first_name'], $userRS['last_name']);
$enableData[$enableValueCnt++] = array(
    'name' => 'realname',
    'title' => '',
    'value' => $realname
);
// 如果使用者有更新 first_name 或 last_name，將使用者 sysSession->realname 更新
if (!empty($_POST['first_name']) || !empty($_POST['last_name']) || !empty($_POST['email'])) {
    $sysSession->realname = $realname;
    $sysSession->email    = $userRS['email'];
    dbSet('WM_session', sprintf("realname='%s', email='%s'", $realname, $userRS['email']), "idx='{$_COOKIE['idx']}' AND username='{$sysSession->username}'");
}

if ($enableData !== null) {
    $result['show'] = $enableData;
}

// 更新個人照片 (Begin)
// $img_error = 0;
if (is_uploaded_file($_FILES['picture']['tmp_name'])) {
    switch ($_FILES['picture']['type']) {
        case 'image/gif':
        case 'image/bmp':
        case 'image/jpeg':
        case 'image/png':
        case 'image/pjpeg':
            if ($_FILES['picture']['size'] < 51200) {
                $filename = $_FILES['picture']['tmp_name'];
                
                $pic = file_get_contents($filename);
                dbNew('WM_user_picture', 'username, picture', "'{$username}', empty_blob()");
                dbNew('WM_user_picture', 'username, picture', "'{$username}', null");
                $sysConn->UpdateBlob('WM_user_picture', 'picture', $pic, "username='{$username}'");
            } else {
                // $img_error = 2;
                $imgErrMsg   = array();
                $imgErrMsg[] = array(
                    'id' => 'picture',
                    'message' => $MSG['pic_size_large'][$sysSession->lang],
                    'rule' => 'Custom_pic_rule'
                );
                $result      = array(
                    'error' => '',
                    'imgerror' => $imgErrMsg
                );
                echo json_encode($result);
                die();
            }
            break;
        default:
            $imgErrMsg   = array();
            $imgErrMsg[] = array(
                'id' => 'picture',
                'message' => $MSG['pic_format_illegal'][$sysSession->lang],
                'rule' => 'Custom_pic_rule'
            );
            $result      = array(
                'error' => '',
                'imgerror' => $imgErrMsg
            );
            echo json_encode($result);
            die();
            // $img_error = 1;
    }
}
// 更新個人照片 (End)

// 更新簽名檔
if (!empty($_POST['serial'])) {
    foreach ($_POST['serial'] as $key => $val) {
        $val     = intval($val);
        $content = rtrim($_POST['tagline'][$key]);
        $layout  = stripslashes($content);
        $title   = htmlspecialchars(rtrim(strip_tags($_POST['tagtitle'][$key])));
        // $ctype   = ($_POST['isHTML' . $val] == '1') ? 'html' : 'text';
        $ctype   = 'html'; // 新版沒有 text，直接指定 html
        if ($ctype == 'html') {
            $content = strip_scr($layout);
        } else {
            $patterns = array(
                "/(http:\/\/[^\s]+)/",
                "/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/"
            );
            $replace  = array(
                "<a href=\"\\1\" target=\"_blank\" class=\"cssAnchor\">\\1</a>",
                "<a href=\"mailto:\\1\">\\1</a>"
            );
            $content  = nl2br(preg_replace($patterns, $replace, htmlspecialchars($layout, ENT_QUOTES)));
            // $layout = '<pre>' . preg_replace($patterns, $replace, htmlspecialchars($layout, ENT_QUOTES)) . '</pre>';
        }
        if (($val < 0) || empty($val)) {
            if (!empty($title) || !empty($content)) {
                dbNew('WM_user_tagline', 'username, title, ctype, tagline', "'{$sysSession->username}', '{$title}', '{$ctype}', '{$content}'");
                wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 0, 'others', $_SERVER['PHP_SELF'], 'new tagline');
            }
        } else {
            dbSet('WM_user_tagline', "title='{$title}', ctype='{$ctype}', tagline='{$content}'", "serial={$val} AND username='{$sysSession->username}'");
            wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 0, 'others', $_SERVER['PHP_SELF'], 'update tagline');
        }
    }
}

// 如果有變更語系，回傳 reload 來判斷是否刷新網頁
$result['reload'] = false;
if ($_POST['language'] && in_array($_POST['language'], $sysAvailableChars)) {
    if ($_POST['language'] != $sysSession->lang) {
        $sysSession->lang = $_POST['language'];
        $sysSession->restore();
        
	if ($_SERVER['HTTPS']){
            $http_secure = true;
	}else{
            $http_secure = false;
	}
	setcookie('wm_lang', $sysSession->lang, time()+86400, '/', '', $http_secure);
        
        $result['reload'] = true;
    }
}
//$sysConn->CommitTrans($RS);

echo json_encode($result);

/*
$userinfo = dbGetStSr('WM_user_account', '*', "username='{$username}'");
// 移除舊的 sysSession
dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
// 建立新的 sysSession
$idx = $sysSession->init($userinfo);
$_COOKIE['idx'] = $idx;
$sysSession->restore();
*/
// 更新個人資料 (End)

// 為了可以馬上看到更新後的結果，所以所有的訊息顯示皆移到底下，包含訊息的載入
/*
require_once(sysDocumentRoot .'/lang/personal.php');
$MyData = array(
'password'       => $MSG['password'][$sysSession->lang],
'last_name'      => $MSG['last_name'][$sysSession->lang],
'first_name'     => $MSG['first_name'][$sysSession->lang],
'gender'         => $MSG['gender'][$sysSession->lang],
'birthday'       => $MSG['birthday'][$sysSession->lang],
'personal_id'    => $MSG['personal_id'][$sysSession->lang],
'picture'        => $MSG['picture'][$sysSession->lang],
'email'          => $MSG['email'][$sysSession->lang],
'homepage'       => $MSG['homepage'][$sysSession->lang],
'home_tel'       => $MSG['home_tel'][$sysSession->lang],
'home_fax'       => $MSG['home_fax'][$sysSession->lang],
'home_address'   => $MSG['home_address'][$sysSession->lang],
'office_tel'     => $MSG['office_tel'][$sysSession->lang],
'office_fax'     => $MSG['office_fax'][$sysSession->lang],
'office_address' => $MSG['office_address'][$sysSession->lang],
'cell_phone'     => $MSG['cell_phone'][$sysSession->lang],
'company'        => $MSG['company'][$sysSession->lang],
'department'     => $MSG['department'][$sysSession->lang],
'title'          => $MSG['title'][$sysSession->lang],
'language'       => $MSG['language'][$sysSession->lang],
'msg_reserved'   => $MSG['msg_reserved'][$sysSession->lang],
'theme'          => $MSG['theme'][$sysSession->lang],
'hid'            => ''
);

$lang = strtolower($sysSession->lang);
$js = <<< BOF
function go() {
var obj = document.getElementById("actFm");
if ((typeof(obj) != "object") || (obj == null)) return false;
obj.submit();
}

lang = "{$lang}";
window.onload = function () {
if ("{$blnReload}" == true) {
var cid = ("{$sysSession->course_id}" == '' || "{$sysSession->course_id}" == '0') ? '10000000' :  "{$sysSession->course_id}";
var gEnv = 1;
switch("{$sysSession->env}") {
case 'learn'   : gEnv = 1; break;
case 'teach'   : gEnv = 2; break;
case 'direct'  : gEnv = 3; break;
case 'academic': gEnv = 4; break;
}
parent.chgCourse(cid, 0, gEnv, 'SYS_06_01_003');
}
// rebMenu(lang);
};

BOF;
*/

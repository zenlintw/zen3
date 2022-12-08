<?php
/**
 * 個人資料儲存
 *
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lib/Hongu.php');
require_once(sysDocumentRoot . '/lang/hongu_validate_msg.php');

if ($sysSession->username == 'guest') {
	die('illeage Access.');
}

if ((checkusername($sysSession->username) != 2)&&(checkusername($sysSession->username) != 4)) {
	die('illeage Access.');
}

$username = $sysSession->username;

// 檢查 ticket 是不是吻合
$ticket = md5($username . $sysSession->school_id . $sysSession->ticket);
if ($ticket != trim($_POST['ticket'])) {
    echo 'Access deny.';
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'others', $_SERVER['PHP_SELF'], '拒絕存取!');
    exit();
}

// root只允許自行更新
if ($username == sysRootAccount && $sysSession->username != sysRootAccount) {
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 2, 'others', $_SERVER['PHP_SELF'], '"' . sysRootAccount . '" account only can be modified by himself.');
    die('"' . sysRootAccount . '" account only can be modified by himself.');
}

// $GLOBALS['sysConn']->debug = true;
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
            case 'repassword':
                $rules[$key] = array(
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

// 客製密碼 3/26帶調整，root不家此判斷
// if($sysSession->username != sysRootAccount  && !empty($_POST['password']) ){
//     $password = $_POST['password'];
//     $saveStatus = array_fill_keys(array('lower', 'upper', 'number', 'specal'), 0);
//     for($i=0; $i<strlen($password); $i++){
//         $encode = ord($password[$i]);
//         if($encode >= 97 && $encode <= 122){
//             $saveStatus['lower'] ++;
//         }else if ($encode >= 65 && $encode <= 90){
//             $saveStatus['upper'] ++;
//         }else if($encode >= 48 && $encode <= 57){
//             $saveStatus['number'] ++;
//         }else{
//             $saveStatus['specal'] ++;
//         }
//     }
//     $status = 0;
//     foreach ($saveStatus as $key => $value) {
//         if($value != 0){
//             $status ++;
//         }
//     }
//     if($status < 3){
//         $result = array(
//             'error' => 'password error',
//             'imgerror' => ''
//         );
//         echo json_encode($result);
//         die();
//     }
// }
    if(!empty($_POST['password'])){
        $passwordResult = isPassPasswordCondition($_POST['password']);
        if($passwordResult == 'password error'){
            $result = array(
                'error' => 'password error',
                'imgerror' => ''
            );
            echo json_encode($result);
            die();
        }
        if(IsWithPassrordHistory($sysSession->username, $_POST['password'])){
            $result = array(
                'error' => 'password history redeclare',
                'imgerror' => ''
            );
            echo json_encode($result);
            die();
        }
    }
    

// 更換大頭照
if ($_POST['action'] == 'changeMyPhoto') {
	if (is_uploaded_file($_FILES['myphoto']['tmp_name'])) {
	    switch ($_FILES['myphoto']['type']) {
	        case 'image/gif':
	        case 'image/jpeg':
	        case 'image/png':
	        case 'image/pjpeg':
	            if ($_FILES['myphoto']['size'] < 5120000) {
	                $filename = $_FILES['myphoto']['tmp_name'];
	                list($width, $height) = getimagesize($filename);
	                $maxImageSize = max($width, $height);
	                $minImageSize = min($width, $height);
	                if ($maxImageSize > 168) {
	                	$resizeRatio = 168/$minImageSize;
	                	$newWidth = floor($resizeRatio*$width);
	                	$newHeight = floor($resizeRatio*$height);
	                	echo 'newWidth:'.$newWidth;
	                	echo 'newHeight:'.$newHeight;
	                	// exit;
	                	$thumb = imagecreatetruecolor($newWidth, $newHeight);
	                	$target = imagecreatetruecolor(168, 168);
	                	switch($_FILES['myphoto']['type']) {
	                		case 'image/jpeg':
	                		case 'image/pjpeg':
		                		$source = imagecreatefromjpeg($filename);
		                		// Resize
		                		@imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			                	if ($newWidth > 168) {
			                		@imagecopyresized($target, $thumb, 0, 0, floor(($newWidth-168)/2), 0, 168, 168, 168, 168);
			                	}else if ($newHeight > 168) {
			                		@imagecopyresized($target, $thumb, 0, 0, 0, floor(($newHeight-168)/2), 168, 168, 168, 168);
			                	}else{
			                		$target = $thumb;
			                	}
			                	imagejpeg($target, $filename, 100);
	                			break;
	                		case 'image/png':
	                			$source = imagecreatefrompng($filename);
		                		// Resize
			                	@imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			                	if ($newWidth > 168) {
			                		@imagecopyresized($target, $thumb, 0, 0, floor(($newWidth-168)/2), 0, 168, 168, 168, 168);
			                	}else if ($newHeight > 168) {
			                		@imagecopyresized($target, $thumb, 0, 0, 0, floor(($newHeight-168)/2), 168, 168, 168, 168);
			                	}else{
			                		$target = $thumb;
			                	}
			                	imagepng($target, $filename, 0);
			                	break;
			                case 'image/gif':
	                			$source = imagecreatefromgif($filename);
		                		// Resize
			                	@imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			                	if ($newWidth > 168) {
			                		@imagecopyresized($target, $thumb, 0, 0, floor(($newWidth-168)/2), 0, 168, 168, 168, 168);
			                	}else if ($newHeight > 168) {
			                		@imagecopyresized($target, $thumb, 0, 0, 0, floor(($newHeight-168)/2), 168, 168, 168, 168);
			                	}else{
			                		$target = $thumb;
			                	}
			                	imagegif($target, $filename);
	                		break;
	                	}
	                }

	                $pic = file_get_contents($filename);
	                dbNew('WM_user_picture', 'username, picture', "'{$sysSession->username}', empty_blob()");
	                dbNew('WM_user_picture', 'username, picture', "'{$sysSession->username}', null");
	                $sysConn->UpdateBlob('WM_user_picture', 'picture', $pic, "username='{$sysSession->username}'");
	            }else{
	            	$imgErrMsg   = array();
		            $imgErrMsg[] = array(
		                'id' => 'picture',
		                'message' => 'pic_format_illegal',
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
	                'message' => 'pic_format_illegal',
	                'rule' => 'Custom_pic_rule'
	            );
	            $result      = array(
	                'error' => '',
	                'imgerror' => $imgErrMsg
	            );
	            echo json_encode($result);
	            die();
	    }
        // 回列表
        header('LOCATION: /mooc/user/personal.php');
	}
}else if ($_POST['action'] == 'update') {  // 儲存個人資料

    // 判斷密碼
    if (!empty($_POST['password']) && !empty($_POST['repassword'])) {
        if (trim($_POST['password']) != trim($_POST['repassword'])) {
            $_POST['password'] = '';
            $_POST['repassword'] = '';
        }
    }

    // 串 SQL 字串，並處理資料
    $sqls = '';
    $isUpdatePassword = false;
    foreach ($_POST as $key => $value) {
        if ($key == 'action' || $key == 'ticket')
            continue;
        $val = '';
        switch ($key) {
            case 'password':
                $val = trim($_POST[$key]);
                if (!empty($val)){
                    $val = md5($val);
                    $isUpdatePassword = true;
                }
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
        if ($key == 'repassword') continue;
        if (($key == 'password') && empty($val)) continue;
        $sqls .= "{$key}='{$val}', ";
    }
    $sqls = substr($sqls, 0, -2);

    // 直接存入 WM_all_account
    if ($sqls != '') {
        $RS = dbSet('WM_all_account', $sqls, "username='{$username}'");
        wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 0, 'others', $_SERVER['PHP_SELF'], '更新個人設定!');

        if($isUpdatePassword){
            // 客製密碼紀錄
            dbNew("CO_password_recond", "username,s_id,password", vsprintf('"%s",%d,"%s"', array($sysSession->username, $sysSession->school_id, md5($_POST['password']))));

        }
    }

    if ($sysConn->Affected_Rows() && (strcmp($sysSession->realname, checkRealname($_POST['first_name'], $_POST['last_name'])) != 0)) {
    	dbSet('WM_session',sprintf("realname='%s'",checkRealname($_POST['first_name'], $_POST['last_name'])),sprintf("idx='%s'",$_COOKIE['idx']));

    }
    
    echo json_encode($result);
    die();
    
}else if($_POST['action'] == 'view'){
	// 修改對外顯示與否
	$code = intval($_POST['val']);
	// 判斷顯示或不顯示
	$type = $_POST['type'];
	if($code == 0) die('error');
	$hid = dbGetOne(sysDBname . '.WM_all_account', 'hid', "username='{$sysSession->username}'");
	if($type == 'hide') $hid += $code;
	else if($type == 'show')  $hid -= $code;
	else die('error');
	dbSet(sysDBname . '.WM_all_account',"hid={$hid}", "username='{$sysSession->username}'");
	die("success");
}else{
	header('HTTP/1.0 403 Forbidden');
	exit;
}
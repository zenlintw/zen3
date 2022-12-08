<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lib/co_chkform.php');// 補強表單驗證
require_once(sysDocumentRoot . '/lib/co_mooc.php');// mooc專用函數
require_once(sysDocumentRoot . '/mooc/models/user.php');
require_once(sysDocumentRoot . '/lang/register.php'); //語系
require_once(sysDocumentRoot . '/message/collect.php');// 發信

// 未開放註冊時，導向 mooc 首頁
if (!in_array('Y', $canRegister) && !in_array('C', $canRegister) && !in_array('FB', $canRegister)) {
    header('Location: /mooc/index.php');
    exit;
}

// 已登入者，不能使用
if ($sysSession->username != 'guest') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$ticket = md5($sysSession->ticket . 'WriteUserData' . $sysSession->username . $sysSession->school_id .
        $sysSession->school_host);

if (trim($_POST['ticket']) != $ticket) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// 檢查帳號
if (strlen($_POST['username']) <= 1 || strlen($_POST['username']) >= 21) {
    $user_limit2 = str_replace(array('%MIN%', '%MAX%'),
                               array(sysAccountMinLen, sysAccountMaxLen),
                               $MSG['msg_js_02'][$sysSession->lang]);
    $tmpMessage[] = $user_limit2;
} else {
    // 檢查帳號是否已經有人使用了
    $error_no = checkUsername($_POST['username']);
    if ($error_no > 0) {
        if ($error_no == 1) {
            $tmpMessage[] = $MSG['system_reserved'][$sysSession->lang];
        } else if ($error_no == 2) {
            $tmpMessage[] = $MSG['used'][$sysSession->lang];
        } else if ($error_no == 3) {
            $user_limit3 = str_replace('%FIRSTCHR%', $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang], $MSG['msg_js_03'][$sysSession->lang]);
            $tmpMessage[] = $user_limit3;
        } else if ($error_no == 4) {
            $tmpMessage[] = $MSG['system_reserved'][$sysSession->lang];
        } else if ($error_no == 5) {
            $tmpMessage[] = $MSG['msg_fill_username'][$sysSession->lang];
        }
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $_POST['username']);
    }
}

// 檢查密碼
if ($_POST['password'] === null || $_POST['password'] === '') {
    $tmpMessage[] = $MSG['msg_js_04'][$sysSession->lang];
} elseif (strlen($_POST['password']) <= 5) {
    $tmpMessage[] = $MSG['msg_js_05'][$sysSession->lang];
}

// 確認密碼
if ($_POST['repassword'] === null || $_POST['repassword'] === '') {
    $tmpMessage[] = $MSG['msg_js_06'][$sysSession->lang];
}

if ($_POST['password'] !== $_POST['repassword']) {
    $tmpMessage[] = $MSG['msg_js_07'][$sysSession->lang];
}

// 檢查名字
if ($_POST['first_name'] === null || $_POST['first_name'] === '') {
    $tmpMessage[] = $MSG['msg_js_26'][$sysSession->lang];
}

// 檢查電子信箱
if ($_POST['email'] === null || $_POST['email'] === '') {
    $tmpMessage[] = $MSG['msg_js_21'][$sysSession->lang];
} else {
    // 檢查電子信箱是否已經有人使用了
    $error_no = checkUserEmail($_POST['email']);
    if ($error_no > 0) {
        if ($error_no == 2) {
//            $tmpMessage[] = $MSG['email_used'][$sysSession->lang];
        } else if ($error_no == 3) {
            $tmpMessage[] = $MSG['msg_js_20'][$sysSession->lang];
        } else if ($error_no == 4) {
            $tmpMessage[] = $MSG['msg_js_21'][$sysSession->lang];
        }
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $_POST['email']);
    }
}

// 檢查圖形驗證碼
if (defined('sysEnableCaptcha') && sysEnableCaptcha) {
    session_start();
    if (empty($_POST['captcha']) || ($_SESSION['captcha'] != $_POST['captcha'])) {
        if (session_id()) {
            session_destroy();
        }
        $tmpMessage[] = $MSG['incorrect_captcha'][$sysSession->lang];
    }
}
if (session_id()) {
    session_destroy();
}

if (count($tmpMessage) >= 1) {
    // 組錯誤訊息
    foreach ($tmpMessage as $v) {
        $message .= '<div>' . $v . '</div>';
    }
} else {
    // 將資料寫進資料庫中
    foreach ($_POST as $key => $val) {
        if ($key == 'password' || $key == 'repassword') {
            $data[$key] = md5(trim($val));
        } else {
            $data[$key] = trim($val);
        }
        if ($key === 'day') {
            if ($data['month'] >= 1 && $data['day'] >= 1) {
                $data['birthday'] = $data['year'] . '-' . $data['month'] . '-' . $data['day'];
                unset($data['year']);
                unset($data['month']);
                unset($data['day']);
            } else {
                $data['birthday'] = null;
            }
        }
    }

    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        // 讀取標題
        $tmp_subject = $sysSession->school_name . $MSG['add_mooc_account_subject'][$sysSession->lang];
        // 讀取內容
        $tmp_body = $MSG['add_mooc_account_body'][$sysSession->lang];
    }else{
        // 郵件的原始信件檔案
        $target = sysDocumentRoot . "/base/$sysSession->school_id/add_account.mail";

        if (file_exists($target)) {
            // 郵件的原始信件檔案  的夾檔路徑
            $att_file_path  = sysDocumentRoot . "/base/$sysSession->school_id/attach/add_account";

            // ========== 1.先讀取檔案中的原始信件檔案(每封信件共用資訊) ==========
            // 先讀取 郵件的原始信件檔案 資料
            $fd = fopen($target, 'r');

            // 讀取標題
            $temp = fgets($fd, 1024);
            $tmp_subject = $temp;

            /*
              讀取內容
              $tmp_body 為尚未置換特殊符號的本文
            **/
            while (!feof ($fd)) {
                $tmp_body .= fgets($fd, 4096);
            }

            fclose($fd);
        } else {
            // 讀取標題
            $tmp_subject = $sysSession->school_name . $MSG['add_mooc_account_subject'][$sysSession->lang];
            // 讀取內容
            $tmp_body = $MSG['add_mooc_account_body'][$sysSession->lang];
        }
    }

    // ========== 2.取出信件夾檔名稱(每封信件共用資訊) ==========
    if (is_dir($att_file_path)) {
        // 取得所有附加檔案名稱
        $att_files		= getAllFile($att_file_path);
    }

    // 註冊需要管理者審核
    if (in_array('C', $canRegister)) {
        // $res_no = addUser($_POST['username'], $data);
        $rsUser = new user();
        $res_no = $rsUser->addMoocUser($_POST['username'], $data, $_POST['lang']);
    }else{
        $activeCode = md5(uniqid(rand()));
        if ($activeCode !== '') {
            $rsUser = new user();
            $res_no = $rsUser->addMoocUser($_POST['username'], $data, $_POST['lang']);
            setEmailValidCode($_POST['username'], $_POST['email'], $activeCode);
        }
    }

    // 送信
    if ($res_no <= 0) {

        $real_name = checkRealname($_POST['first_name'], $_POST['last_name']);
        
        // 註冊成功，但需要管理者審核
        if (in_array('C', $canRegister)) {
            $body = $MSG['letter_02'][$sysSession->lang];
            $message = $MSG['need_confirm'][$sysSession->lang];
            $subject = $sysSession->school_name . $MSG['letter_subject_02'][$sysSession->lang];
            
            $body = strtr($body,
                array(
                    '%%NAME%%'        =>  trim($_POST['last_name'] . ' ' . $_POST['first_name']),
                    '%%SCHOOL_NAME%%' =>  $sysSession->school_name,
                    '%%USERNAME%%'    =>  trim($_POST['username']),
                    '%%PASSWORD%%'    =>  trim($_POST['password']),
                    '%%SERVER_NAME%%' =>  $_SERVER['HTTP_HOST']
                )
            );
            
            // 每次進入都必須重新宣告一個新的 mail 類別
            $mail = buildMail('', $subject, $body, 'html', '', '', '', '', false);
        
        } else {
            $body = $tmp_body;
            $message = $MSG['msg_14'][$sysSession->lang];

            $subject = $tmp_subject;

            // 每次進入都必須重新宣告一個新的 mail 類別
            $mail = buildMail('', $subject, $body, 'html', '', '', '', '', false);

            $body = strtr($body,
                  array(
                    '%SCHOOL_NAME%' =>  $sysSession->school_name,
					'%USERNAME%'    =>  trim($_POST['username']),
					'%EMAIL%'       =>  trim($_POST['email']),
                    '%REALNAME%'    =>  $real_name,
                    '%REG_TIME%'    =>  date('Y-m-d H:i:s'),
                    '%VALID_URL%'   =>  $baseUrl . '/mooc/active.php?idx=' . $activeCode
                    )
                 );

            // ========== 處理附加檔案 ==========
            $att_count		= count($att_files);

            for ($j=0; $j<$att_count; $j++){
                $attach		= $att_file_path . DIRECTORY_SEPARATOR . $att_files[$j];
                $data = file_get_contents($attach);
                // 5.信件夾檔
                $mail->add_attachment($data,$att_files[$j]);
            }


        }

        list($school_name,$school_mail) = dbGetStSr('WM_school', 'school_name,school_mail', "school_id={$sysSession->school_id} and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

        if (empty($school_mail)){
            $school_mail = 	'webmaster@'. $_SERVER['HTTP_HOST'];
        }
        $mail->from = mailEncFrom($school_name,$school_mail);

        $mail->body = $body;
        $mail->to = trim($_POST['email']);
        $mail->send();

        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], 'new user account', $_POST['username']);
        
        noCacheRedirect($baseUrl . '/mooc/message.php?type=1&email=' . $_POST['email']);
    } else {
        // 新增使用者到資料庫中失敗
        $message = $MSG['msg_24'][$sysSession->lang];
        $error_no = 1;

        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $_POST['username']);
    }
}

$smarty->assign('message', $message);

// XSS 防護
if (is_array($_POST)) {
    foreach ($_POST as $k=>$v) {
        if (is_array($v)) {
            foreach ($v as $k1=> $v1) {
                if (!empty($_POST[$k][$k1])) $_POST[$k][$k1]=htmlspecialchars($v1, ENT_QUOTES);
            }
        }else{
            if (!empty($_POST[$k])) $_POST[$k] = htmlspecialchars($v, ENT_QUOTES);
        }
    }
}
$smarty->assign('post', $_POST);
$smarty->display('register_p.tpl');

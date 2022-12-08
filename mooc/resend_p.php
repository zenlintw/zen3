<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/user.php');
require_once(sysDocumentRoot . '/lib/co_chkform.php');// 補強表單驗證
require_once(sysDocumentRoot . '/lib/co_mooc.php');// mooc專用函數
require_once(sysDocumentRoot . '/lang/register.php'); //語系
require_once(sysDocumentRoot . '/message/collect.php');// 發信

// 已登入者，不能使用
if ($sysSession->username != 'guest') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$username = $_POST['resendto'];
$url = $_SERVER[HTTP_REFERER];
$info = @parse_url($url);
$path = basename($info['path']);
$rsUser = new user();

// 檢查帳號
if (strlen($username) <= 1 || strlen($username) >= 21) {
    $user_limit2 = str_replace(array('%MIN%', '%MAX%'),
                               array(sysAccountMinLen, sysAccountMaxLen),
                               $MSG['msg_js_02'][$sysSession->lang]);
    $tmpMessage[] = $user_limit2;
} else {
    // 檢查帳號是否已經有人使用了
    $error_no = checkUsername($username);
    if ($error_no > 0) {
        if ($error_no == 1) {
            $tmpMessage[] = $MSG['system_reserved'][$sysSession->lang];
        } else if ($error_no == 3) {
            $user_limit3 = str_replace('%FIRSTCHR%', $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang], $MSG['msg_js_03'][$sysSession->lang]);
            $tmpMessage[] = $user_limit3;
        } else if ($error_no == 4) {
            $tmpMessage[] = $MSG['system_reserved'][$sysSession->lang];
        } else if ($error_no == 5) {
            $tmpMessage[] = $MSG['msg_fill_username'][$sysSession->lang];
        }
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $username);
    }
}

// 檢查電子信箱（適用於重發電子信箱驗證信，忘記密碼不用判斷有無電子信箱）
if ($path === 'resend.php') {
    // 檢查電子信箱是否已經有人使用了
    if ($_POST['email'] === null || $_POST['email'] === '') {
        $tmpMessage[] = $MSG['msg_js_21'][$sysSession->lang];
    } else {
        // 先判斷所改的email 有沒有人使用
        if (isset($_POST['oemail'])) {
            // 從入口2(輸入正確帳密，尚未驗証)進來
            if ($_POST['encemail'] == urlencode(md5('xliame'.$_POST['oemail']))) {
                $error_no = checkUserEmail($_POST['email'], false, $username);
                if ($error_no==0) {
                    if ($_POST['oemail'] !== $_POST['email']) {
                        // 將原來的 email 改為使用者修正後的 email co_mooc_account 及 co_user_verify 
                        $rsUser->setEmailByUsername($username, $_POST['email']);
                    }
                    $error_no = 6;
                } else if ($error_no==2) {
                    // 使用者要修改的 email 已有人使用
                    $error_no = 5; 
                }
            } else {
                // 錯誤的驗證碼
                $tmpMessage[] = '錯誤的驗證碼';
            }

        } else {
            $error_no = checkUserEmail($_POST['email']);
        }
        // 檢查電子信箱是否已經有人使用了
        if ($error_no > 0) {
            if ($error_no == 2) {
                // 檢查帳號與電子信箱有沒有配對正確(co_mooc_account)
                $r = $rsUser->getTmpProfileByUsername($username);
                if ($r['email'] !== $_POST['email']) {
                    // 檢查帳號與電子信箱有沒有配對正確(WM_all_account)
                    $r = $rsUser->getSimpleProfileByUsername($username);
                    if ($r['email'] == $_POST['email']) {
                        $tmpMessage[] = $MSG['msg_account_verified'][$sysSession->lang];
                        $verified = "true";
                    } else {
                        $tmpMessage[] = $MSG['msg_27'][$sysSession->lang];
                    }
                }
            } else if ($error_no == 3) {
                $tmpMessage[] = $MSG['msg_js_20'][$sysSession->lang];
            } else if ($error_no == 4) {
                $tmpMessage[] = $MSG['msg_js_21'][$sysSession->lang];
//            } else if ($error_no == 5) {
//                $tmpMessage[] = $MSG['email_used'][$sysSession->lang];
//                $changed = "true";
            } else if ($error_no == 6) {
                
            }
            wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $_POST['email']);
        } else if ($error_no == 0) {
            // 回傳0時，找無此信箱
            $tmpMessage[] = $MSG['msg_27'][$sysSession->lang];
        }
    }
}

if (count($tmpMessage) >= 1) {
    // 組錯誤訊息
    foreach ($tmpMessage as $v) {
        $message .= '<div>' . $v . '</div>';
    }
} else {

    // 郵件的原始信件檔案
    $target	= sysDocumentRoot . "/base/$sysSession->school_id/add_account.mail";

    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        // 讀取標題
        $tmp_subject = $sysSession->school_name . $MSG['add_mooc_account_subject'][$sysSession->lang];
        // 讀取內容
        $tmp_body = $MSG['add_mooc_account_body'][$sysSession->lang];
    }else{
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

    $activeCode = md5(uniqid(rand()));
    if ($activeCode !== '') {
        $user = $rsUser->getTmpProfileByUsername($username);
        // 將註冊時間改為 NOW()
        $regTime = $rsUser->resetRegtimeByUsername($username);
        $res_no = setEmailValidCode($user['username'], $user['email'], $activeCode);
    }

    // 送信
    if ($res_no === 1) {

        $regStatus = getSchoolRegStatus();

        // 註冊成功，不需要管理者審核
        if ($regStatus === 'Y') {
            $body = $tmp_body;
            $message = $MSG['msg_14'][$sysSession->lang];

            $subject = $tmp_subject;

            // 每次進入都必須重新宣告一個新的 mail 類別
            $mail = buildMail('', $subject, $body, 'html', '', '', '', '', false);

            $body = strtr($body,
                  array(
                    '%SCHOOL_NAME%' =>  $sysSession->school_name,
                    '%USERNAME%'    =>  trim($user['username']),
                    '%EMAIL%'       =>  trim($user['email']),
                    '%REALNAME%'    =>  $user['realname'],
                    '%REG_TIME%'    =>  $regTime,
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

        // 註冊成功，但需要管理者審核
        } else {
            $body = $tmp_body;
            $message = $MSG['msg_14'][$sysSession->lang];
            $subject = $tmp_subject . '(' . $sysSession->school_name . ')';

            $body = strtr($body,
                  array(
                    '%SCHOOL_NAME%' =>  $sysSession->school_name,
                    '%USERNAME%'    =>  trim($user['username']),
                    '%EMAIL%'       =>  trim($user['email']),
                    '%REALNAME%'    =>  $user['realname'],
                    '%REG_TIME%'    =>  $regTime,
                    '%VALID_URL%'   =>  $baseUrl . '/mooc/active.php?idx=' . $activeCode
                  )
                );

            // 每次進入都必須重新宣告一個新的 mail 類別
            $mail = buildMail('', $subject, $body, 'html', '', '', '', '', false);
        }

        list($school_name,$school_mail) = dbGetStSr('WM_school', 'school_name,school_mail', "school_id={$sysSession->school_id} and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

        if (empty($school_mail)){
            $school_mail = 	'webmaster@'. $_SERVER['HTTP_HOST'];
        }
        $mail->from = mailEncFrom($school_name,$school_mail);

        $mail->body = $body;
        $mail->to = trim($user['email']);
        $mail->send();

        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], 'new user account', $user['username']);
    } else {
        // 新增使用者到資料庫中失敗
        $message = $MSG['msg_24'][$sysSession->lang];
        $error_no = 1;

        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $_POST['username']);
    }

    noCacheRedirect($baseUrl . '/mooc/message.php?type=6&email=' . $user['email']);
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

// 如果是驗證過的帳號，按鈕改為登入
if (isset($verified) && $verified !== '') {
    $smarty->assign('verified', $verified);
} else if (isset($changed) && $changed !=='' ) {
    $smarty->assign('emailused', $email_used);
    $resendurl = urlencode(base64_encode($username))."+".urlencode(base64_encode($_POST['oemail']))."+".$_POST['encemail'];
    $smarty->assign('resendurl', $resendurl);
}


$smarty->display('resend_p.tpl');
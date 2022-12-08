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

if (!isset($_POST['token']) || ($_POST['token'] != md5($sysSession->idx))) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// 帳號與電子信箱都必須填寫
if ((!(isset($_POST['username'])) || $_POST['username'] === '') || (!(isset($_POST['email'])) || $_POST['email'] === '')) {
    $tmpMessage[] = $MSG['msg_js_22'][$sysSession->lang];
}
$_POST['username'] = trim($_POST['username']);
$_POST['email'] = trim($_POST['email']);

if ($_POST['username'] !== '') {
    // 檢查帳號
    if (strlen($_POST['username']) <= 1 || strlen($_POST['username']) >= 21) {
        $user_limit2 = str_replace(array('%MIN%', '%MAX%'),
                                   array(sysAccountMinLen, sysAccountMaxLen),
                                   $MSG['msg_js_02'][$sysSession->lang]);
        $tmpMessage[] = $user_limit2;
        $_POST['username'] = "";
    } else {
        // 檢查帳號是否已經有人使用了
        $error_no = checkUsername($_POST['username']);
        if ($error_no > 0) {
            if ($error_no == 1) {
                $tmpMessage[] = $MSG['system_reserved'][$sysSession->lang];
            } else if ($error_no == 2) {  // 帳號使用中，驗證email是否相符
                $udetail = getUserDetailData($_POST['username']);
                if ($udetail['email'] != $_POST['email']){
                    $tmpMessage[] = $MSG['msg_js_27'][$sysSession->lang];
                }
            } else if ($error_no == 3) {
                $user_limit3 = str_replace('%FIRSTCHR%', $MSG['msg_account_firstchr_' . Account_firstchr][$sysSession->lang], $MSG['msg_js_03'][$sysSession->lang]);
                $tmpMessage[] = $user_limit3;
                // 填寫帳號錯誤，為避免是植入XSS字串，因此把$_POST['username']清空
                $_POST['username'] = "";
            } else if ($error_no == 4) {
                $tmpMessage[] = $MSG['system_reserved'][$sysSession->lang];
            } else if ($error_no == 5) {
                $tmpMessage[] = $MSG['msg_fill_username'][$sysSession->lang];
            }
        } else {
            $tmpMessage[] = $MSG['msg_js_23'][$sysSession->lang];
        }
    }
}


$smarty->assign('resend', 'N');
$smarty->assign('page', 'forget.php');

// 有錯誤訊息
if (is_array($tmpMessage) && count($tmpMessage)) {
    foreach ($tmpMessage as $v) {
        $message .= '<div>' . $v . '</div>';
    }
    $smarty->assign('message', $message);
    if (isset($_POST['email'])) {
        if (!preg_match(sysMailRule, $_POST['email'])) {
            $_POST['email'] = "";
        }
    }
    $smarty->assign('post', $_POST);
    $smarty->display('forget_p.tpl');
    exit;
}

// 取中文姓名、電子信箱
if ($_POST['username'] !== '') {
    $rsUser = new user();
    $user = $rsUser->getSimpleProfileByUsername($_POST['username']);
}

// 判斷電子信箱驗證了沒，如果沒有，轉往重發驗證信事件
if (count($user) >= 1) {
    $rsUser = new user();
    $pass = $rsUser->isEmailValidCodePass($user['email']);

    if ($pass === 'N') {
        $tmpMessage[] = $MSG['resend'][$sysSession->lang];
        $smarty->assign('resend', 'Y');// 是否需要重寄驗證信
        $smarty->assign('page', 'resend_p.php');// 導向頁面
        $smarty->assign('resendto', $user['username']);// 收件者
    }

    if (count($user) === 0 && count($tmpMessage) >= 1) {
        // 組錯誤訊息
        foreach ($tmpMessage as $v) {
            $message .= '<div>' . $v . '</div>';
        }
    } else {

        if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
            // 讀取標題
            $tmp_subject = $sysSession->school_name . $MSG['forget_mooc_password_subject'][$sysSession->lang];
            // 讀取內容
            $tmp_body = $MSG['forget_mooc_password_body'][$sysSession->lang];
        }else{
            // 郵件的原始信件檔案
            $target = sysDocumentRoot . "/base/$sysSession->school_id/forget_pwd.mail";

            if (file_exists($target)) {
                // 郵件的原始信件檔案  的夾檔路徑
                $att_file_path  = sysDocumentRoot . "/base/$sysSession->school_id/attach/forget";

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
                $tmp_subject = $sysSession->school_name . $MSG['forget_mooc_password_subject'][$sysSession->lang];
                // 讀取內容
                $tmp_body = $MSG['forget_mooc_password_body'][$sysSession->lang];

            }
        }

        // ========== 2.取出信件夾檔名稱(每封信件共用資訊) ==========
        if (is_dir($att_file_path)) {
            // 取得所有附加檔案名稱
            $att_files		= getAllFile($att_file_path);
        }

        $activeCode = md5(uniqid(rand()));
        if ($activeCode !== '') {
            $res_no = setForgetValidCode($user['username'], $user['email'], $activeCode);
        }

        // 送信
        if ($res_no == 1) {

            // 忘記密碼通知信
            $body = $tmp_body;
            $message = $MSG['msg_25'][$sysSession->lang];

            $subject = $tmp_subject;

            // 每次進入都必須重新宣告一個新的 mail 類別
            $mail = buildMail('', $subject, $body, 'html', '', '', '', '', false);

            $body = strtr($body,
                  array(
                    '%SCHOOL_NAME%' =>  $sysSession->school_name,
                    '%REALNAME%'    =>  $user['realname'] . '(' . $user['username'] . ')',
                    '%VALID_URL%'   =>  '<a href="'.$baseUrl . '/mooc/resetpwd.php?idx=' . $activeCode.'" target="_blank">'.$baseUrl . '/mooc/resetpwd.php?idx=' . $activeCode.'</a>'
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

            list($school_name,$school_mail) = dbGetStSr('WM_school', 'school_name,school_mail', "school_id={$sysSession->school_id} and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

            if (empty($school_mail)){
                $school_mail = 	'webmaster@'. $_SERVER['HTTP_HOST'];
            }
            $mail->from = mailEncFrom($school_name,$school_mail);

            $mail->body = $body;
            $mail->to = trim($user['email']);
            $mail->send();

            wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], 'forget_password', $user['username']);
        } else {
            // 新增使用者到資料庫中失敗
            $message = $MSG['msg_26'][$sysSession->lang];
            $error_no = 1;

            wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , $error_no, 'others', $_SERVER['PHP_SELF'], $message, $user['username']);
        }
        noCacheRedirect($baseUrl . '/mooc/message.php?type=7&email=' . trim($user['email']));
    }

    $smarty->assign('message', $message);
    $smarty->assign('post', $_POST);
    $smarty->display('forget_p.tpl');
} else {
    noCacheRedirect($baseUrl . '/mooc/message.php?type=13');
}
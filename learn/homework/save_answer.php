<?php
/**************************************************************************************************
 *                                                                                                *
 *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *        Programmer: Wiseguy Liang                                                         *
 *        Creation  : 2004/01/19                                                            *
 *        work for  : list all available exam(s)                                            *
 *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/learn_homework.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/quota.php');
require_once(sysDocumentRoot . '/lib/file_api.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lang/exam_teach.php');
require_once(sysDocumentRoot . '/lib/exam_lib.php');
require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');

// 強制在背景執行完畢
ignore_user_abort(1);
set_time_limit(0);

// 教師試做不做任何事情
if (isset($_POST['isForTA']) && $_POST['isForTA'] == 1)
    die('<script>location.replace("' . QTI_which . '_list.php");</script>');

//ACL begin
$sysSession->cur_func = QTI_which == 'homework' ? '1700400200' : '1800300200';
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}
//ACL end

// 偵測上傳的檔案大小是否超過限制
if (detectUploadSizeExceed()) {
    showXHTML_script('inline', 'alert("' . $MSG['upload_file_error'][$sysSession->lang] . '");');
    die();
}

// 依據測驗編號取課程編號
$examId = htmlspecialchars($_POST['exam_id']);
$QTI_which = QTI_which;
$cid = $sysConn->GetOne("select course_id from WM_qti_{$QTI_which}_test where exam_id = {$examId}");

if ($_POST['ticket'] != md5((defined('forGuestQuestionnaire') ? $_SERVER['HTTP_HOST'] : sysTicketSeed) . $_POST['exam_id'] . $_POST['time_id'])) {
    wmSysLog($sysSession->cur_func, $cid , $_POST['exam_id'] , $_POST['time_id'], 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket! '.$_POST['ticket'].' '.sysTicketSeed);
}

// 如果放棄作答
if ($_SERVER['argv'][0] == 'over') {
    $school_q = $_SERVER['argv'][1] == 'school' ? '?school' : '';
    wmSysLog($sysSession->cur_func, $cid, $_POST['exam_id'], 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . ' give up!');
    die('<script type="text/javascript">location.replace("' . QTI_which . '_list.php' . $school_q . '");</script>');
}

$_POST['exam_id'] = intval($_POST['exam_id']);
$QTI_which        = QTI_which;
chkSchoolId("WM_qti_{$QTI_which}_test");
$school_q = $sysConn->GetOne("select course_id={$sysSession->school_id} from WM_qti_{$QTI_which}_test where exam_id={$_POST['exam_id']}") ? true : false;

$course_id = $school_q ? $sysSession->school_id : $cid; //10000000;

if (QTI_which=='homework') {
    
    $rsTest = $sysConn->GetRow(sprintf('select content, close_time, delay_time, setting from WM_qti_%s_test where exam_id=%d', QTI_which, $_POST['exam_id']));
    $exam = $rsTest['content'];
    // 進入時間
    $start_time = strtotime($_POST['start_time']);

    // 作答關閉日期（253402185600 表示沒有設定作答關閉日期）
    $closetime = strtotime($rsTest['close_time']);

    // 補繳期限（253402185600 表示沒有設定補繳期限）
    $delaytime = strtotime($rsTest['delay_time']);

    // 上傳附件規則
    $setting = $rsTest['setting'];
    // 當勾選必須有附檔時，檢查有沒有上傳檔案
    if (strpos($setting, 'required') !== false) {
        // 目前的已存在檔案個數
        $new_path = sprintf('%s/base/%05d/course/%08d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, $cid, QTI_which, $_POST['exam_id'], $sysSession->username);
        $files = glob_recursive($new_path . '*');
        $readyExistsFilesCnt = count($files);
        //var_dump($readyExistsFilesCnt);

        // 準備刪除的檔案個數
        $readyDeleteFilesCnt = count($_POST['rm_files']);
        //var_dump($readyDeleteFilesCnt);

        // 準備用表單上傳的檔案大小
        $readyFormUploadFilesSize = array_sum($_FILES[QTI_which . '_files']['size']);
        //var_dump($readyFormUploadFilesSize);

        // 準備用html5 file元件上傳的檔案個數
        $readyHtml5UploadFilesSize = count($_POST['html5_files']);
        //var_dump($readyHtml5UploadFilesSize);
        if ($readyExistsFilesCnt - $readyDeleteFilesCnt + $readyFormUploadFilesSize + $readyHtml5UploadFilesSize === 0) {
            showXHTML_script('inline', 'alert("' . $MSG['file_required'][$sysSession->lang] . '");');
            printf('<script>history.back();</script>');
            die();
        }
    }
} else {
    $exam = $sysConn->GetOne(sprintf('select content from WM_qti_%s_test where exam_id=%d', QTI_which, $_POST['exam_id']));
}

// 取得所有題目的配分
if (preg_match_all('!<item\b[^>]*\bid="([^"]+)"[^>]*(\bscore="([^"]+)"([^>]*)?)?>[^<]*</item>!isU', $exam, $regs, PREG_PATTERN_ORDER)) {
    $total = count($regs[0]);
    eval('$scores = array(' . substr(vsprintf(vsprintf(str_repeat("'%s' => %%.2f,", $total), $regs[1]), $regs[3]), 0, -1) . ');');
}

// 取得所有題目的 XML
$items = $sysConn->GetAssoc(sprintf('select ident, content from WM_qti_%s_item where ident in ("%s")', QTI_which, implode('","', array_unique($regs[1]))));
//    echo '<pre>';
//    var_dump($items);
//    echo '</pre>';
    foreach ($items as $k =>$v) {
        // 解決發生題庫的ident和content裡的ident不一致問題
        preg_match('/\sident="(WM_ITEM[0-9_]*)"/', $v, $matches);
        $items[$k] = str_replace($matches[1], $k, $v);
    }
//    echo '<pre>';
//    var_dump($items);
//    echo '</pre>';

eval('$replaces = array(' . substr(vsprintf(vsprintf(str_repeat("'%s' => \$items['%%s'],", $total), $regs[1]), $regs[1]), 0, -1) . ');');

// 有答案的填進去
if (is_array($_POST['ans']))
    foreach ($_POST['ans'] as $item_id => $item_ans) {
        $xmlstr = preg_replace('#<decvar[^>]*/>#isU', sprintf('<decvar vartype="Integer" defaultval="%.2f" />', $scores[$item_id]), $items[$item_id]) . '<item_result ident_ref="' . $item_id . '">';
        foreach ($item_ans as $resp_id => $resp_ans) {
            // 判斷是否有html tag，沒有代表不是用編輯器，背景增加換行符號
            if($string === strip_tags($string)) {
                /**BUG#030691 begin     mars 20130923 **/
                $resp_ans = str_replace(chr(13), "<br/>", $resp_ans);
                /**BUG#030691 End     mars 20130923 **/
            }
            $resp_ans = is_array($resp_ans) ? explode(chr(27), htmlspecialchars(stripslashes(implode(chr(27), $resp_ans)))) : htmlspecialchars(stripslashes($resp_ans));
            $xmlstr .= '<response ident_ref="' . $resp_id . '">' . '<num_attempts>1</num_attempts>' . '<response_value>' . (is_array($resp_ans) ? removenonprintable(implode('</response_value><response_value>', $resp_ans)) : removenonprintable($resp_ans)) . '</response_value>' . '</response>';
        }
        $xmlstr .= '</item_result>';
        
        $replaces[$item_id] = $xmlstr; // 丟回 item 的 xml
        unset($xmlstr);
    }

$xmlstr = preg_replace(array(
    '/\bxmlns(:\w+)?\s*=\s*"[^"]*"/isU',
    '/\s+>/'
), array(
    '',
    '>'
), str_replace($regs[0], $replaces, trim($exam)));
$xmlstr = str_replace('<questestinterop', '<questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster"', $xmlstr);

$rsCourse = new course();
$xmlstr = $rsCourse->transform_LATEX($xmlstr);
//echo '<pre>';
//var_dump($xmlstr);
//var_dump(htmlspecialchars($xmlstr));
//echo '</pre>';

$save_attachment = false;

if (defined('forGuestQuestionnaire')) {
    $forGuestSerial = intval($sysConn->GetOne('select max(time_id) from WM_qti_questionnaire_result where exam_id=' . $_POST['exam_id'] . ' and examinee="guest"'));
    $maxlimit       = 100;
    do {
        dbNew('WM_qti_questionnaire_result', 'exam_id,examinee,time_id,status,begin_time,submit_time,comment,content', sprintf('%d, "guest", %u, "submit", now(), now(), "%s", %s', $_POST['exam_id'], ++$forGuestSerial, $_SERVER['REMOTE_ADDR'], $sysConn->qstr($xmlstr)));
    } while ($sysConn->ErrorNo() == 1062 && --$maxlimit); // 如果跟別人重覆則最多重試一百次
    
    $result_msg      = $MSG['writing complete'][$sysSession->lang];
    $save_attachment = (bool) $maxlimit;
    wmSysLog($sysSession->cur_func, $cid, $_POST['exam_id'], $save_attachment, 'classroom', $_SERVER['PHP_SELF'], QTI_which . 'guest finish(new)!');
} else {
    if (QTI_which=='homework') {
        if ($closetime !== 253402185600 && $start_time <= $closetime && time() > $closetime) {
            $status = 'overtime_closetime';
        } else if ($closetime !== 253402185600 && $start_time > $closetime && $rsTest['delay_time']!='0000-00-00 00:00:00' && time() < $delaytime) {
            $status = 'payback';
        } else if ($rsTest['delay_time']!='0000-00-00 00:00:00' && isset($rsTest['delay_time']) === TRUE && time() >= $delaytime) {
            $status = 'overtime_delaytime';
        }
        $xmlstr = str_replace('</questestinterop>', '<wm:submit_status>' . $status . '</wm:submit_status></questestinterop>', $xmlstr);
    }
                
    $where = sprintf('exam_id=%d and examinee="%s" and time_id=1', $_POST['exam_id'], $sysSession->username);
    list($isSubmited) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', $where, ADODB_FETCH_NUM);
    if ($isSubmited) // 已經交過了
        {
        dbSet('WM_qti_' . QTI_which . '_result', "status=CASE status WHEN 'revised' THEN 'revised' WHEN 'publish' THEN 'publish' ELSE 'submit' END,submit_time=now(),content=" . $sysConn->qstr($xmlstr), $where);
        if ($sysConn->ErrorNo()) {
            $result_msg = sprintf('%s(%d) %s', $MSG['update_fault'][$sysSession->lang], $sysConn->ErrorNo(), $sysConn->ErrorMsg());
        } else {
            $result_msg      = $MSG['update_success'][$sysSession->lang];
            $save_attachment = true;
        }
        wmSysLog($sysSession->cur_func, $cid, $_POST['exam_id'], 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' finish!');
    } else // 第一次交作業
        {
        dbNew('WM_qti_' . QTI_which . '_result', 'exam_id,examinee,time_id,status,begin_time,submit_time,content', sprintf('%d, "%s", 1, "submit", now(), now(), %s', $_POST['exam_id'], $sysSession->username, $sysConn->qstr($xmlstr)));
        if ($sysConn->ErrorNo()) {
            $result_msg = sprintf('%s(%d) %s', $MSG['create_fault'][$sysSession->lang], $sysConn->ErrorNo(), $sysConn->ErrorMsg());
        } else {
            $result_msg      = $MSG['submit_success'][$sysSession->lang];
            $save_attachment = true;
        }
        wmSysLog($sysSession->cur_func, $cid, $_POST['exam_id'], 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' finish(new)!');
    }
    
    // 增加答案LOG與EMAIL通知機制
    // 以追蹤QTI 答案XML格式錯誤的可能原因
    // 只記錄讀出後PARSE失敗的
    // log_qti_answer(QTI_which, $sysSession->school_id, $sysSession->course_id, htmlspecialchars($_POST['exam_id']), $sysSession->username, $xmlstr, $sysConn->qstr($xmlstr));
}

if ($school_q)
    if (defined('forGuestQuestionnaire')) {
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09d/%s/', $sysSession->school_id, QTI_which, $_POST['exam_id'], 'guest' . $forGuestSerial);
    } else {
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09d/%s/', $sysSession->school_id, QTI_which, $_POST['exam_id'], $sysSession->username);
    } else {
    if (defined('forGuestQuestionnaire')) {
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09d/%s/', $sysSession->school_id, $cid, QTI_which, $_POST['exam_id'], 'guest' . $forGuestSerial);
    } else {
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09d/%s/', $sysSession->school_id, $cid, QTI_which, $_POST['exam_id'], $sysSession->username . $forGuestSerial);
    }
}

$freeQuota = getRemainQuota($school_q ? $sysSession->school_id : $cid);
$msgQuota  = str_replace('%TYPE%', $MSG[$school_q ? 'school' : 'course'][$sysSession->lang], $MSG['quota_full'][$sysSession->lang]);

// 刪掉舊檔
if ($save_attachment && is_array($_POST['rm_files']))
    foreach ($_POST['rm_files'] as $item)
        @unlink($save_path . basename(rawurldecode($item)));
        
function glob_recursive($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
      $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}

if ($save_attachment && $freeQuota > 0) // 儲存上傳作業
    {
    $isMobile = isMobileBrowser() ? '1' : '0';

    // 行動裝置，由於上傳的圖片和影片名稱固定，所以需要重新給予檔名
    // 取已經存在的檔名的最大號碼
    if ($isMobile === '1') {
        $movieNum = 1;
        $imageNum = 1;

        // 讀取指令路徑有哪些檔案
        $files = glob_recursive($save_path . '*');
        foreach ($files as $v) {
            if (preg_match("/MOVIE\((\d+)\).MOV$/", pathinfo($v, PATHINFO_BASENAME), $match)) {
                if ($match[1] >= $movieNum) {
                    $movieNum = $match[1] + 1;
                }
            }
            if (preg_match("/IMAGE\((\d+)\).JPEG$/", pathinfo($v, PATHINFO_BASENAME), $match)) {
                if ($match[1] >= $imageNum) {
                    $imageNum = $match[1] + 1;
                }
            }
        }
    }
    
    // 確定繳交
    // 搬移HTML5上傳的檔案，由temp放到課程個人作業區
    if ($school_q) {
        $ori_path = sprintf('%s/base/%05d/temp/course/%d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, $cid, QTI_which, $_POST['exam_id'], $sysSession->username);
        if (defined('forGuestQuestionnaire')) {
            $new_path = sprintf('%s/base/%05d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, QTI_which, $_POST['exam_id'], 'guest'.$forGuestSerial);
        } else {
            $new_path = sprintf('%s/base/%05d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, QTI_which, $_POST['exam_id'], $sysSession->username);
        }
    } else {
        $ori_path = sprintf('%s/base/%05d/temp/course/%d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, $cid, QTI_which, $_POST['exam_id'], $sysSession->username);
        if (defined('forGuestQuestionnaire')) {
            $new_path = sprintf('%s/base/%05d/course/%08d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, $cid, QTI_which, $_POST['exam_id'], 'guest'.$forGuestSerial);
        } else {
            $new_path = sprintf('%s/base/%05d/course/%08d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, $cid, QTI_which, $_POST['exam_id'], $sysSession->username);
        }
    }

    if (is_dir($new_path) === false) {
        mkdir($new_path, 0755, true);
    }

    if (QTI_which=='homework') {
        // 開始----正常繳交檔名不異動----關閉----逾時（沒有設定補繳期限時）或補繳（有設定補繳期限時）----補繳期限----逾時補繳
        // 逾時
        // 補繳
        // 逾時補繳
        //echo '<pre>';
        //var_dump(date('Y-m-d H:i:s'), ($rsTest['close_time']), ($rsTest['delay_time']));
        //var_dump(time(), $closetime, $delaytime);
        //echo '</pre>';
        $prefixFilename = $sysSession->username . '_';
        
        if ($closetime !== 253402185600 && $start_time <= $closetime && time() > $closetime) {
            $prefixFilename .= $MSG['overtime_closetime'][$sysSession->lang] . '_';
        } else if ($closetime !== 253402185600 && $start_time > $closetime && $rsTest['delay_time']!='0000-00-00 00:00:00' && time() < $delaytime) {
            $prefixFilename .= $MSG['payback'][$sysSession->lang] . '_';
        } else if ($rsTest['delay_time']!='0000-00-00 00:00:00' && time() >= $delaytime) {
            $prefixFilename .= $MSG['overtime_delaytime'][$sysSession->lang] . '_';
        }
        //echo '<pre>';
        //var_dump($prefixFilename);
        //echo '</pre>';
    }
        
        if (count($_POST['html5_files']) > 0) {
            foreach($_POST['html5_files'] as $key => $val) {
                $name = preg_replace('/[\\/:\*\?"<>\|%#+]+/i', '_', $val);
                while (strpos($name, '.') === 0) {
                    $name = substr($name,1);
                }
                
                // 修正作業無法上傳單引號檔案（）
                $name = str_replace(array('.php','..',"\'"), array('.phps','',"'"), basename($name));
                $_POST['html5_files'][$key] = preg_replace('/[\\/:\*\?"<>\|%#+]+/i', '_', $name);
            }
        }
    
    // 先將temp所有檔案搬移到目的資料夾
    if (is_dir($ori_path)){
        exec("/bin/mv {$ori_path}* {$new_path}");
    }
    

    // 讀取目的路徑有哪些檔案
    $files = glob_recursive($new_path . '*');
    
    foreach ($files as $v) {
        // 稽核是否為檔案
        if (is_file($v)) {
            // 如果在上傳名單中，進行rename
            if (is_array($_POST['html5_files']) && in_array(pathinfo($v, PATHINFO_BASENAME), $_POST['html5_files'])) {
                @rename($v,$new_path . $prefixFilename . pathinfo($v, PATHINFO_BASENAME));
            }
        }
    }
        
    // 判斷temp是否有檔案沒搬移到，有則記log
    $tmp_files = glob_recursive($ori_path . '*');
    if (count($tmp_files)>0) {
        wmSysLog(1700400201, $cid, $_POST['exam_id'], 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' mv file fail!');    
    }
        
    // 儲存新檔
    $isFirst = true;
    if (is_array($_FILES['homework_files']['tmp_name'])) {
        if (($freeQuota = $freeQuota - (array_sum($_FILES['homework_files']['size']) / 1024)) > 0) {
            foreach ($_FILES['homework_files']['tmp_name'] as $i => $file) {
                // $realfname = iconv('UTF-8', $sysSession->lang, $_FILES['homework_files']['name'][$i]);
                $realfname = mb_convert_encoding($_FILES['homework_files']['name'][$i], 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
                if (empty($realfname) || strpos($realfname, '.') === 0)
                    $realfname = ereg_replace('^.*/', md5(uniqid(rand(), true)) . '.', $_FILES['homework_files']['name'][$i]);
                
                // MIS#037774:繳交作業時，作業附檔自動變更檔名，在[原檔案名]前，加上[上傳者]的[學號]
                $realfname = $sysSession->username . '_' . $realfname;
                
                // MIS#037774:若是已超過截止日期，則加上補交兩字
                if (isNowOverHomeworkEndTime($_POST['exam_id'])) {
                    $realfname = $MSG['pre_over_time_word'][$sysSession->lang] . $realfname;
                }
                
                if (is_uploaded_file($file)) {
                    if ($isFirst && !is_dir($save_path))
                        exec("mkdir -p '$save_path'");
                        
                    // 行動裝置，影片與圖片重新給予檔名
                    if ($isMobile === '1') {
                        if (preg_match("/^trim.[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}.MOV$/", $realfname) || preg_match("/^capturedvideo.MOV$/", $realfname)) {
                            $realfname = $prefixFilename . 'MOVIE(' . $movieNum . ').MOV'; 
                            $movieNum++;
                        }
                        if (preg_match("/^image.jp[e]?g$/", $realfname)) {
                            $realfname = $prefixFilename . 'IMAGE(' . $imageNum .').JPEG'; 
                            $imageNum++;
                        }
                    }
                        
                    if (move_uploaded_file($file, $save_path . $realfname))
                        // 行動裝置給予更名後的檔名
                        if ($isMobile === '1') {
                            $result_msg .= sprintf("\\nsave file : %s", $realfname);
                        } else {
                            $result_msg .= sprintf("\\nsave file : %s", $_FILES['homework_files']['name'][$i]);
                        }
                }
            }
        } else {
            $result_msg .= '\\n' . $msgQuota;
        }
    }
} else if ($freeQuota <= 0 && is_array($_FILES['homework_files']['tmp_name']))
    $result_msg .= '\\n' . $msgQuota;

// 更新quota資訊
getCalQuota($school_q ? $sysSession->school_id : $cid, $quota_used, $quota_limit);
setQuota($school_q ? $sysSession->school_id : $cid, $quota_used);

if (QTI_which=='homework') {
    function genTicket($var, $times, $username = '')
    {
        global $sysSession;
        return sprintf('%s+%s+%s', $var, $times, md5(sysTicketSeed . $var . $times . $username . $_COOKIE['idx']));
    }
    if ($profile['isPhoneDevice']) {
        printf('<script>alert("%s"); window.close();window.opener.location.reload();</script>', htmlspecialchars($result_msg, ENT_COMPAT, 'UTF-8'));
    } else {
        printf('<script>location.replace("view_exemplar.php?%s+%s+personal+1");</script>',  genTicket($_POST['exam_id'], 1, $sysSession->username), $sysSession->username);
    }
} else {
    if ($profile['isPhoneDevice']) {
        printf('<script>alert("%s"); window.close();</script>', htmlspecialchars($result_msg, ENT_COMPAT, 'UTF-8'));
    } else {
        printf('<script>alert("%s"); location.replace("%s_list.php%s");</script>', htmlspecialchars($result_msg, ENT_COMPAT, 'UTF-8'), QTI_which, ($school_q) ? '?school' : '');
    }
}
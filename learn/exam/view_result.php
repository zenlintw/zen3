<?php

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/exam_teach.php');
    require_once(sysDocumentRoot . '/lib/attach_link.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    $sysSession->cur_func='1600400300';
    $sysSession->restore();
    if (!aclVerifyPermission(1600400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    // 不公布
    list($announce_type, $testContent, $close_time, $announce_time) = dbGetRow('WM_qti_exam_test','announce_type, content, close_time, announce_time',sprintf('exam_id=%u',$_SERVER['argv'][0]),ADODB_FETCH_NUM);
    if ($announce_type == 'never') {
        die($MSG['score_publish_type0'][$sysSession->lang]);
    }

    // 可否看答案
//    echo '<pre>';
//    var_dump('公布類型', $announce_type);
//    echo '</pre>';
    $announceEnable = false;
    switch ($announce_type) {
        case 'now':
            // 有效繳交才能進入本頁面
            $submitTimes = dbGetOne('WM_qti_exam_result','count(exam_id)',sprintf('exam_id=%u and examinee="%s" AND status IN ("submit", "revised")', $_SERVER['argv'][0], $sysSession->username), ADODB_FETCH_ASSOC);
//            echo '<pre>';
//            var_dump('有效（submit, revised）繳交筆數', (int)$submitTimes);
//            echo '</pre>';
            if ((int)$submitTimes >= 1) {
                $announceEnable = true;
            }
            break;
        case 'close_time':
            $now = date('Y-m-d H:i:s');
//            echo '<pre>';
//            var_dump('關閉作答日期', $close_time);
//            echo '</pre>';            
            if ($close_time <= $now) {
                $announceEnable = true;
            }
            break;
//        case 'delay_time':
//            $now = date('Y-m-d H:i:s');
//            echo '<pre>';
//            var_dump('補繳截止日期', $delay_time);
//            echo '</pre>';           
//            if ($delay_time <= $now) {
//                $announceEnable = true;
//            }
//            break;
        case 'user_define':
//            echo '<pre>';
//            var_dump('自訂公布時間', $announce_time);
//            echo '</pre>';           
            if ($announce_time != '9999-12-31 00:00:00' && strtotime($announce_time) <= time()) {
                $announceEnable = true;
            }
            break;
    }
//    echo '<pre>';
//    var_dump('可否觀看答案', $announceEnable);
//    echo '</pre>';
    if ($announceEnable === FALSE) {
        return;
    }

    // 為相容之前的試卷，因此預設為顯示完整記錄
    $answer_publish_type = 'complete';
    if (preg_match('/\bscore_publish_type="([^"]*)"/', $testContent, $regs)) {
        $answer_publish_type = $regs[1];
    }
    
    $submit_times = dbGetOne('WM_qti_exam_result','count(*)',sprintf('exam_id=%u and examinee="%s" and time_id=%d and status!="break"', $_SERVER['argv'][0], $sysSession->username, $_SERVER['argv'][1]), ADODB_FETCH_ASSOC);
    if ($submit_times==0 && $answer_publish_type == 'complete') {
        $answer_publish_type = 'detailed';
    }

    switch($answer_publish_type) {
        case 'simple':
            define('QTI_DISPLAY_OUTCOME',  false);
            define('QTI_DISPLAY_RESPONSE', false);
            define('QTI_DISPLAY_ANSWER',   false);
            break;
        case 'detailed':
            define('QTI_DISPLAY_OUTCOME',  false);
            define('QTI_DISPLAY_RESPONSE', true);
            define('QTI_DISPLAY_ANSWER',   false);
            break;
        case 'complete':
            define('QTI_DISPLAY_OUTCOME',  true); // 是否顯示答案
            define('QTI_DISPLAY_RESPONSE', true); // 是否顯示得分
            define('QTI_DISPLAY_ANSWER',   true); // 是否顯示作答答案
            break;
        default:
            define('QTI_DISPLAY_OUTCOME',  false);
            define('QTI_DISPLAY_RESPONSE', false);
            define('QTI_DISPLAY_ANSWER',   false);
    }

    require_once(sysDocumentRoot . '/teach/exam/exam_preview.php');
    header('Content-type: text/html'); // 因為 exam_preview.php 會輸出 text/xml header 所以在此糾正回來 #1239

    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    if ($profile['isPhoneDevice']) {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
        echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
        echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
    }
    if ($profile['isPhoneDevice']) {
        require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
        $smarty->display('phone/learn/exam_style.tpl');
    }
    showXHTML_head_E();
    showXHTML_body_B();

    if ($_SERVER['argv'][2] != md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']))
    {
           wmSysLog($sysSession->cur_func, 1, 'auto', $_SERVER['PHP_SELF'], 'Fake ticket');
        die('Fake ticket !');
    }

    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;

    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
    // 取得最小的time_id 
    $tid = dbGetStSr('WM_qti_exam_result','min(time_id) as min ',sprintf('exam_id=%u and examinee="%s"', $_SERVER['argv'][0], $sysSession->username), ADODB_FETCH_ASSOC);
    $minid = $tid['min'];
    $time_id = $_SERVER['argv'][1];

    list($score, $comment, $content, $ref_url, $status) = dbGetStSr('WM_qti_exam_result', 'score, comment, content, ref_url, status', sprintf('exam_id=%u and examinee="%s" and time_id=%d', $_SERVER['argv'][0], $sysSession->username, $_SERVER['argv'][1]), ADODB_FETCH_NUM);
    // 如果$content是空的，表示沒有time_id=1的測驗分數 => 被教師刪掉第一次的測驗結果 
    // $content便會是最小的time_id的測驗結果 
    if (empty($content)) {

       $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
                               $sysSession->school_id,
                               $sysSession->course_id,
                               QTI_which,
                               $_SERVER['argv'][0],
                               $sysSession->username);
       $file =     $time_id.'.xml';          

       $full_path = $xml_path.$file;
       if (is_file($full_path)) {
           $content = file_get_contents($full_path);
       } else {
           $time_id = $minid;
           list($score, $comment, $content, $ref_url, $status) = dbGetStSr('WM_qti_exam_result', 'score, comment, content, ref_url, status', sprintf('exam_id=%u and examinee="%s" and time_id=%d', $_SERVER['argv'][0], $sysSession->username, $minid), ADODB_FETCH_NUM);
           if (empty($content)) {
               $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
                                       $sysSession->school_id,
                                       $sysSession->course_id,
                                       QTI_which,
                                       $_SERVER['argv'][0],
                                       $sysSession->username);
               $file =     $time_id.'.xml';          
        
               $full_path = $xml_path.$file;
               if (is_file($full_path)) {
                   $content = file_get_contents($full_path);
               }
           }
           
       }
    }

    if ($topDir == 'academic')
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/%s/ref/%09u/',
                               $sysSession->school_id,
                               QTI_which,
                               $_SERVER['argv'][0],
                               $sysSession->username,
                               $_SERVER['argv'][1]);
    else
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/ref/%09u/',
                               $sysSession->school_id,
                               $sysSession->course_id,
                               QTI_which,
                               $_SERVER['argv'][0],
                               $sysSession->username,
                               $_SERVER['argv'][1]);

    $save_uri = substr($save_path, strlen(sysDocumentRoot));
    $ref_files = '';
    if ($d = @dir($save_path))
    {
        while (false !== ($entry = $d->read()))
        {
            if (is_file($save_path . $entry))
            {
                $ref_files .= genFileLink($save_uri, $entry);
            }
        }
        $d->close();
    }
    if ($ref_files || $ref_url || $comment)
    {
        showXHTML_tabFrame_B(array(array($MSG['ref_data'][$sysSession->lang])));
            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="box01"');
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td('', $MSG['reference_file'][$sysSession->lang]);
                    showXHTML_td('', $ref_files);
                showXHTML_tr_E();
            
                showXHTML_tr_B('class="cssTrOdd"');
                    showXHTML_td('', $MSG['reference_url'][$sysSession->lang]);
                    showXHTML_td('', sprintf('<a href="%s" target="_blank">%s</a>', $ref_url, $ref_url));
                showXHTML_tr_E();
                
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td('', $MSG['tech_comments'][$sysSession->lang]);
                    showXHTML_td('', $comment);
                showXHTML_tr_E();
                
            showXHTML_table_E();
        showXHTML_tabFrame_E();
    }

    if (QTI_which == 'exam')
    {
                // 判斷是否內嵌在frame中，目前批改試卷頁面是設定0
                if (SINGLE === '0') {
                    $width = 800;
                } else if ($profile['isPhoneDevice']) {
                    $width = '100%';
                } else {
                    // 因應 ipad mini 最大先設定到950
                    $width = 950;
                }
        showXHTML_tabFrame_B(array(array($MSG['tab_exam_times'][$sysSession->lang])));
            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="' . $width . '" class="cssTable"');
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td_B('');
                      $t = dbGetCol('WM_qti_exam_result', 'time_id', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' order by time_id");
                      $tt = array();
                      foreach($t as $v)
                      {
                          $tt[$v] = sprintf('%d+%d+%s', $_SERVER['argv'][0], $v, md5(sysTicketSeed . $_SERVER['argv'][0] . $v . $_COOKIE['idx']));
                          if ($v == $_SERVER['argv'][1]) $x = $tt[$v];
                      }
                      
                      if (count($tt)) {
                          echo $MSG['title_exam_time1'][$sysSession->lang];
                          showXHTML_input('select', '', array_flip($tt), $x, 'class="cssInput" onchange="location.replace(\'view_result.php?\' +this.value)"');
                          echo $MSG['title_exam_time2'][$sysSession->lang];
                      }else{
                          echo "<span style=\"color:red\">{$MSG['exam_result_not_found'][$sysSession->lang]}</span>";
                      }
                    showXHTML_td_E('');
                showXHTML_tr_E();
            showXHTML_table_E();
        showXHTML_tabFrame_E();
    }

    if (empty($content)) {
        $errMsg = $sysConn->ErrorMsg();
        wmSysLog($sysSession->cur_func, 2, 'auto', $_SERVER['PHP_SELF'], $errMsg);
    }else{
        // 準備好夾檔
        if ($xmlDoc = domxml_open_mem($content)) {
            $ids = array();
            $nodes = $xmlDoc->get_elements_by_tagname('item');
            foreach($nodes as $item)
                $ids[] = $item->get_attribute('ident');
            if ($ids) {
                $idents = 'ident in ("' . implode('","', $ids) . '")';
                $a = dbGetAssoc('WM_qti_' . QTI_which . '_item', 'ident, attach', $idents);
                foreach($a as $k => $v)
                    if (preg_match('/^a:[0-9]+:{/', $v))
                        $GLOBALS['attachments'][$k] = unserialize($v);
            }
        }
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/Q/',
        $sysSession->school_id,
        $sysSession->course_id,
        QTI_which);
        $save_uri = substr($save_path, strlen(sysDocumentRoot));
        if (QTI_DISPLAY_RESPONSE || QTI_DISPLAY_ANSWER) {
            ob_start();
            parseQuestestinterop($content);
            $exam_content = ob_get_contents();
            ob_end_clean();
            echo preg_replace('/<form [^>]* action="save_answer.php".*<!/isU', '<form style="display: inline;"><!', $exam_content);
        }
    }

    if (QTI_which == 'exam')
    {
        $ec = $sysConn->GetOne("select content from WM_qti_exam_test where exam_id={$_SERVER['argv'][0]}");
        if (preg_match('/\bthreshold_score="([^"]*)"/', $ec, $regs))
            $threshold_score = ($regs[1] == '') ? 'false' : floatval($regs[1]);
        else
            $threshold_score = 'false';

        $m1 = $MSG['title_exam_result'][$sysSession->lang];
        $m2 = $MSG['title_noblock'][$sysSession->lang];
        $m3 = '<span style="color: green">'.$MSG['title_pass'][$sysSession->lang].'</span>';
        $m4 = '<span style="color: red">'.$MSG['title_nopass'][$sysSession->lang].'</span>';
        $m5 = $MSG['title_standard'][$sysSession->lang];
        $m5 = $MSG['title_standard'][$sysSession->lang];
        if ($status=='break' || $status=='submit') {
            $m6 = 0;
        } else {
            $m6 = 1;
        }
        
        showXHTML_script('inline', "
var correct_score = '{$score}';
var status  = '{$m6}';
    
var ss = document.getElementsByTagName('input');
var total_score = 0.0;
var threshold_score = {$threshold_score};
var examTimes = ".count($tt).";

if (examTimes > 0) {
    if (correct_score == '')
    {
        for(var i=0; i<ss.length; i++)
        {
            if (ss[i].type=='text' && ss[i].name.indexOf('item_scores[') === 0) total_score += parseFloat(ss[i].value);
        }
    }
    else
        total_score = parseFloat(correct_score);
}else{
    total_score = 'N/A';
}

if (status == '0') {
    total_score = '{$MSG['not_yet_revised'][$sysSession->lang]}';
}

var tb = document.getElementsByTagName('table')[2];
tb.insertRow(0);
tb.rows[0].insertCell(0);
tb.rows[0].cells[0].colSpan = '4';
tb.rows[0].cells[0].className = 'cssTrHead';
tb.rows[0].cells[0].innerHTML = '{$MSG['total_score'][$sysSession->lang]} = ' +
                                total_score + '<br>{$m5}' +
                                (threshold_score === false ? '$m2' : threshold_score) + '<br>{$m1}' +
                                ((threshold_score === false || status == '0') ? 'N/A' : (threshold_score <= total_score ? '$m3' : '$m4')) +
                                '<br>{$MSG['score_depend'][$sysSession->lang]}';

");
    }
    showXHTML_body_E();
?>

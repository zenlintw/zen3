<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2004/01/19                                                            *
	 *		work for  : list all available exam(s)                                            *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/learn_homework.php');
    require_once(sysDocumentRoot . '/lang/peer_learn.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/quota.php');
    require_once(sysDocumentRoot . '/lib/file_api.php');

    // 強制在背景執行完畢
    ignore_user_abort(1);
    set_time_limit(0);

    // 教師試做不做任何事情
    if (isset($_POST['isForTA']) && $_POST['isForTA'] == 1) die('<script>location.replace("/learn/homework/homework_list.php");</script>');

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

    if ($_POST['ticket'] != md5((defined('forGuestQuestionnaire') ? $_SERVER['HTTP_HOST'] : sysTicketSeed) . $_POST['exam_id'] . $_POST['time_id'])) {
       wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['exam_id'] , $_POST['time_id'], 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket! '.$_POST['ticket'].' '.sysTicketSeed);
    }

    // 如果放棄作答
    if ($_SERVER['argv'][0] == 'over') {
        $school_q = $_SERVER['argv'][1] == 'school' ? '?school' : '';
        wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['exam_id'] , 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . ' give up!');
        die('<script type="text/javascript">location.replace("/learn/homework/homework_list.php'.$school_q.'");</script>');
    }
    
    showXHTML_head_B('');
    showXHTML_head_E();
    
    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");

    // 系統訊息
    function rtnMsg($msg)
    {
        echo '<div class="container esn-container">
                  <div class="panel block-center">
                      <form class="well form-horizontal message-pull-center">
                          <fieldset>
                              <div class="input block-center">
                                  <div class="row">&nbsp;</div>
                                  <div class="control-group">
                                      <div class="message">
                                          <div id="message">
                                              <div>' . $msg. '</div>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="row">&nbsp;</div>
                                  <div class="control-group">
                                      <div class="controls">
                                          <div class="lcms-left">
                                              <a href="/learn/homework/homework_list.php" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnForget">' . $MSG['back_to_list'][$sysSession->lang] . '</a>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </fieldset>
                      </form>
                  </div>
              </div>';
        die();
    }

    // 判斷繳交的作業筆數，至少要有一個
    if (defined('forGuestQuestionnaire')) {
        $forGuestSerial = intval($sysConn->GetOne('select max(time_id) from WM_qti_questionnaire_result where exam_id=' . $_POST['exam_id'] . ' and examinee="guest"'));
    }

    // 作業檔案路徑
    if ($school_q)
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09d/%s/',
                             $sysSession->school_id,
                             QTI_which,
                             $_POST['exam_id'],
                             $sysSession->username);
    else
        $save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09d/%s/',
                             $sysSession->school_id,
                             $sysSession->course_id,
                             QTI_which,
                             $_POST['exam_id'],
                             $sysSession->username . $forGuestSerial);

    // 現有的檔案群
    $existsFiles = glob($save_path.'/*.*',GLOB_BRACE);
    // 本次上傳的檔案路徑
    $newFile = implode($_FILES['homework_files']['tmp_name'], '');
    // 本次刪除的檔案群
    $deleteFiles = $_POST['rm_files'];
    // 預先計算增減後的檔案筆數
    
    if (count($existsFiles) + (($newFile !== '') ? 1 : 0) - count($deleteFiles) <= 0) {
        rtnMsg('至少要上傳一個檔案');
    }

    $_POST['exam_id'] = intval($_POST['exam_id']);
    $QTI_which = QTI_which;
    chkSchoolId("WM_qti_{$QTI_which}_test");
    $school_q = $sysConn->GetOne("select course_id={$sysSession->school_id} from WM_qti_{$QTI_which}_test where exam_id={$_POST['exam_id']}") ? true : false;

    $course_id = $school_q ? $sysSession->school_id : $sysSession->course_id; //10000000;

    $exam = $sysConn->GetOne(sprintf('select content from WM_qti_%s_test where exam_id=%d', QTI_which, $_POST['exam_id']));

    // 取得所有題目的配分
    if (preg_match_all('!<item\b[^>]*\bid="([^"]+)"[^>]*(\bscore="([^"]+)"([^>]*)?)?>[^<]*</item>!isU', $exam, $regs, PREG_PATTERN_ORDER))
    {
        $total = count($regs[0]);
        eval('$scores = array(' . substr(vsprintf(vsprintf(str_repeat("'%s' => %%.2f,", $total), $regs[1]), $regs[3]), 0, -1) . ');');
    }

    // 取得所有題目的 XML
    $items = $sysConn->GetAssoc(sprintf('select ident, content from WM_qti_%s_item where ident in ("%s")', QTI_which, implode('","', array_unique($regs[1]))));

    eval('$replaces = array(' . substr(vsprintf(vsprintf(str_repeat("'%s' => \$items['%%s'],", $total), $regs[1]), $regs[1]), 0, -1) . ');');

    // 有答案的填進去
    if (is_array($_POST['ans']))
        foreach($_POST['ans'] as $item_id => $item_ans) {
            $xmlstr = preg_replace('#<decvar[^>]*/>#isU', sprintf('<decvar vartype="Integer" defaultval="%.2f" />', $scores[$item_id]),
                                    $items[$item_id]
                                   )
                       . '<item_result ident_ref="' . $item_id . '">';
                foreach($item_ans as $resp_id => $resp_ans){
                    $resp_ans = is_array($resp_ans) ? explode(chr(27), htmlspecialchars(stripslashes(implode(chr(27), $resp_ans)))) : htmlspecialchars(stripslashes($resp_ans));
                    $xmlstr .= '<response ident_ref="' . $resp_id . '">' .
                               '<num_attempts>1</num_attempts>' .
                               '<response_value>' .
                               (is_array($resp_ans) ? implode('</response_value><response_value>', $resp_ans) : $resp_ans ).
                               '</response_value>' .
                               '</response>';
                }
            $xmlstr .= '</item_result>';

            $replaces[$item_id] = $xmlstr;  // 丟回 item 的 xml
            unset($xmlstr);
        }

    $xmlstr = preg_replace( array('/\bxmlns(:\w+)?\s*=\s*"[^"]*"/isU', '/\s+>/'),
                            array('','>'),
                            str_replace($regs[0], $replaces, trim($exam)));
    $xmlstr = str_replace('<questestinterop', '<questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster"', $xmlstr);

    $save_attachment  = false;

    if (defined('forGuestQuestionnaire')) {
        $maxlimit = 100;
        do{
            dbNew('WM_qti_questionnaire_result', 'exam_id,examinee,time_id,status,begin_time,submit_time,comment,content',
                  sprintf('%d, "guest", %u, "submit", now(), now(), "%s", %s',
                          $_POST['exam_id'], ++$forGuestSerial, $_SERVER['REMOTE_ADDR'], $sysConn->qstr($xmlstr)
                         )
                 );
        } while($sysConn->ErrorNo() == 1062 && --$maxlimit); // 如果跟別人重複則最多重試一百次

        $result_msg = $MSG['writing complete'][$sysSession->lang];
        $save_attachment = (bool)$maxlimit;
        wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['exam_id'] , $save_attachment, 'classroom', $_SERVER['PHP_SELF'], QTI_which . 'guest finish(new)!');
    } else {
        $where = sprintf('exam_id=%d and examinee="%s" and time_id=1', $_POST['exam_id'], $sysSession->username);
        list($isSubmited) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', $where, ADODB_FETCH_NUM);
        if ($isSubmited) // 已經交過了
        {
            dbSet('WM_qti_' . QTI_which . '_result', "status=CASE status WHEN 'revised' THEN 'revised' WHEN 'publish' THEN 'publish' ELSE 'submit' END,submit_time=now(),content=" . $sysConn->qstr($xmlstr), $where);
            if ($sysConn->ErrorNo()) {
                $result_msg = sprintf('%s(%d) %s', $MSG['update_fault'][$sysSession->lang],$sysConn->ErrorNo(), $sysConn->ErrorMsg());
            } else {
                $result_msg = $MSG['update_success'][$sysSession->lang];
                $save_attachment  = true;
            }
            wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['exam_id'] , 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' finish!');
        } else	// 第一次交作業
        {
            dbNew('WM_qti_' . QTI_which . '_result', 'exam_id,examinee,time_id,status,begin_time,submit_time,content',
                  sprintf('%d, "%s", 1, "submit", now(), now(), %s',
                          $_POST['exam_id'], $sysSession->username, $sysConn->qstr($xmlstr)
                         )
                 );
            if ($sysConn->ErrorNo())
            {
                $result_msg = sprintf('%s(%d) %s', $MSG['create_fault'][$sysSession->lang],$sysConn->ErrorNo(), $sysConn->ErrorMsg());
            }
            else
            {
                $result_msg = $MSG['submit_success'][$sysSession->lang];
                $save_attachment  = true;
            }
            wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['exam_id'] , 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' finish(new)!');
        }
    }
    $freeQuota = getRemainQuota($school_q ? $sysSession->school_id : $sysSession->course_id);
    $msgQuota = str_replace('%TYPE%', $MSG[$school_q ? 'school' : 'course'][$sysSession->lang], $MSG['quota_full'][$sysSession->lang]);

    // 刪掉舊檔
    if ($save_attachment && is_array($_POST['rm_files']))
        foreach($_POST['rm_files'] as $item) {
            @unlink($save_path . (rawurldecode($item)));
        }
        
    function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
          $files = array_merge($files, $this->glob_recursive($dir.'/'.basename($pattern), $flags));
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
                
        // 儲存新檔
        $isFirst = true;
        if (is_array($_FILES['homework_files']['tmp_name'])) {
            if (($freeQuota = $freeQuota - (array_sum( $_FILES['homework_files']['size'] )/1024)) > 0) {
                foreach($_FILES['homework_files']['tmp_name'] as $i => $file)
                {
                    $realfname = mb_convert_encoding($_FILES['homework_files']['name'][$i], 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win');
                    if (empty($realfname) || strpos($realfname, '.') === 0)
                        $realfname = ereg_replace('^.*/', md5(uniqid(rand(), true)) . '.', $_FILES['homework_files']['name'][$i]);
                    if (is_uploaded_file($file))
                    {
                        if ($isFirst && !is_dir($save_path)) exec("mkdir -p '$save_path'");
                        
                        // 行動裝置，影片與圖片重新給予檔名
                        if ($isMobile === '1') {
                            if (preg_match("/^trim.[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}.MOV$/", $realfname) || preg_match("/^capturedvideo.MOV$/", $realfname)) {
                                $realfname = 'MOVIE(' . $movieNum . ').MOV'; 
                                $movieNum++;
                            }
                            if (preg_match("/^image.jp[e]?g$/", $realfname)) {
                                $realfname = 'IMAGE(' . $imageNum .').JPEG'; 
                                $imageNum++;
                            }
                        }
                        
                        if (move_uploaded_file($file, $save_path . $realfname)) {
                            // 行動裝置給予更名後的檔名
                            if ($isMobile === '1') {
                                $result_msg .= sprintf("\\nsave file : %s", $realfname);
                            } else {
                                $result_msg .= sprintf("\\nsave file : %s", $_FILES['homework_files']['name'][$i]);
                            }
                        }
                    }
                }
            } else {
                $result_msg .= '\\n' . $msgQuota;
            }
        }
    }
    else if ($freeQuota <= 0 && is_array($_FILES['homework_files']['tmp_name']))
        $result_msg .= '\\n' . $msgQuota;

    // 更新quota資訊
    getCalQuota($school_q ? $sysSession->school_id : $sysSession->course_id, $quota_used, $quota_limit);
    setQuota($school_q    ? $sysSession->school_id : $sysSession->course_id, $quota_used);

    printf('<script>alert("%s"); location.replace("%s_list.php%s");</script>', htmlspecialchars($result_msg, ENT_COMPAT, 'UTF-8'), '/learn/homework/homework', ($school_q) ? '?school' : '');
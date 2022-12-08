<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2003/04/10                                                            *
     *        work for  : 列出某考生對某次測驗的所有答案卷                                      *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    if (sysEnableAppCourseExam === true) {
        require_once(sysDocumentRoot . '/lang/app_exam.php');
    }

    //ACL begin
    if (QTI_which == 'exam') {
        $sysSession->cur_func='1600400300';
    }
    else if (QTI_which == 'homework') {
        include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

        $sysSession->cur_func = '1700400300';
    }
    else if (QTI_which == 'questionnaire') {
        $sysSession->cur_func = '1800300300';
        } else if (QTI_which == 'peer') {
            include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
            $sysSession->cur_func = '1710400300';
        }
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

    }

    //ACL end

    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;

    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

    if (!isset($_SERVER['argv'][0])) {    // 檢查 ticket 是否存在
       wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
       die('Access denied.');
    }
    $ticket_head = sysTicketSeed . $course_id . $_SERVER['argv'][1] . $_SERVER['argv'][2];
    if (md5($ticket_head) != $_SERVER['argv'][0]) {    // 檢查 ticket
       wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
       die('Fake ticket.');
    }

    $statuses = array('break'    => $MSG['status_break'][$sysSession->lang],
                      'submit'    => $MSG['status_submit'][$sysSession->lang],
                      'revised'    => $MSG['status_revised'][$sysSession->lang],
                      'publish'    => $MSG['status_publish'][$sysSession->lang],
                      'chgWin'  => $MSG['status_submit'][$sysSession->lang]        // 切換視窗強制交卷 by Small 2012/02/03
                     );

    function getTimestamp($sec){
        $seconds = intval($sec);
        $ret = '';

        $tmp = $seconds % 60; $ret = sprintf(':%02d', $tmp) . $ret;
        $seconds = (int)floor($seconds / 60);
        $tmp = $seconds % 60; $ret = sprintf(':%02d', $tmp) . $ret;
        $seconds = (int)floor($seconds / 60);
        return sprintf('%02d%s', $seconds , $ret);
    }

    /**
     * 
     * 取得應考生的IP
     * @param string $user 帳號
     * @param int $courseId 課程編號
     * @param int $examId 試卷編號
     * @param string $st 考試時間
     * @param string $et 結束時間
     */
    function getApplyExamIP($user, $courseId, $examId, $st, $et)
    {
        //未繳卷
        if (empty($et)) {
            $ip = dbGetOne(
                    'WM_log_classroom', '
                    remote_address', 
                    sprintf("username='%s' and department_id=%d and instance=%d and log_time >= '%s' order by log_time",
                        mysql_escape_string($user), $courseId, $examId, $st, $et
                    )
                  );
        }else{
            //正常繳卷、強迫繳卷
            $ip = dbGetOne(
                    'WM_log_classroom', '
                    remote_address', 
                    sprintf("username='%s' and department_id=%d and instance=%d and (log_time between '%s' and '%s')",
                        mysql_escape_string($user), $courseId, $examId, $st, $et
                    )
                  );
        }
        if (empty($ip)) return false;
        return $ip;
    }
    
    if (QTI_which == 'homework' && isAssignmentForGroup($_SERVER['argv'][1], $course_id, QTI_which))
    {
        $sqls = 'SELECT R.time_id,R.status,R.begin_time,R.submit_time,R.score,G.caption,D.username ' .
                'FROM WM_student_div AS D ' .
                'INNER JOIN WM_qti_homework_result AS R ON D.username = R.examinee ' .
                'INNER JOIN WM_student_group as G ' .
                'ON D.course_id=G.course_id and D.group_id=G.group_id and D.team_id=G.team_id ' .
                'WHERE D.course_id =' . $sysSession->course_id .
                ' AND D.group_id ='   . $_SERVER['argv'][2] .
                ' AND D.team_id ='    . $_SERVER['argv'][3] .
                ' AND R.exam_id ='    . $_SERVER['argv'][1] .
                ' AND G.course_id ='  . $sysSession->course_id .
                ' limit 1';
        chkSchoolId('WM_student_div');
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $RS = $sysConn->Execute($sqls);
    }
    /* #56126 [MOOCs] 互評作業顯示加權後的總分 By Spring */
    else if (QTI_which == 'peer') {
        $RS = dbGetStMr('WM_qti_' . QTI_which . '_result', 'time_id,status,begin_time,submit_time,score teacher_score, total_score score,content, comment_txt', "exam_id={$_SERVER['argv'][1]} and examinee='{$_SERVER['argv'][2]}' order by time_id", ADODB_FETCH_ASSOC);
    }
    /* #56126 [MOOCs] */
    else
    {
        $RS = dbGetStMr('WM_qti_' . QTI_which . '_result', 'time_id,status,begin_time,submit_time,score,content', "exam_id={$_SERVER['argv'][1]} and examinee='{$_SERVER['argv'][2]}' order by time_id", ADODB_FETCH_ASSOC);
    }

    $RS1 = dbGetStMr('WM_qti_' . QTI_which . '_test', 'ctrl_timeout, do_interval', "exam_id={$_SERVER['argv'][1]}", ADODB_FETCH_ASSOC);
    // $RS = dbGetStMr('WM_qti_' . QTI_which . '_result A left join WM_qti_' . QTI_which . '_test B on A.exam_id = B.exam_id', 'A.time_id,A.status,A.begin_time,A.submit_time,A.score, B.ctrl_timeout, B.do_interval', "A.exam_id={$_SERVER['argv'][1]} and A.examinee='{$_SERVER['argv'][2]}' order by A.time_id");
    if ($RS && $RS1){
        // 開始 output HTML
        showXHTML_head_B('Exam Times List');
          showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
          $scr = <<< EOB

function chBgc(obj, mode){
    obj.style.backgroundColor = mode ? '#CCFFCC' : '';
}

function pickExam(times, ticket){
    parent.rbottom.location.replace('exam_correct_content.php?' + ticket + '+{$_SERVER['argv'][1]}+{$_SERVER['argv'][2]}+' + times);
}

/*Chrome*/
function removeTest(times, ticket){
    if (!confirm('{$MSG['delete_confirm'][$sysSession->lang]}')) return;
    parent.parent.empty.location.replace('exam_correct_remove.php?' + ticket + '+{$_SERVER['argv'][1]}+{$_SERVER['argv'][2]}+' + times);
}

window.onload=function()
{
    parent.rbottom.document.body.innerHTML='';
    parent.rbottom.document.write('<h2 align=center>{$MSG['pick_times_first'][$sysSession->lang]}</h2><script src="/lib/disable.js" type="text/javascript"></script>');
    var t =document.getElementById('displayPanel');
    if (t.rows.length > 2)
    {
        t.rows[t.rows.length-1].scrollIntoView(false);
        if (t.rows[t.rows.length-1].click)
            t.rows[t.rows.length-1].click();
    }
};

EOB;
          showXHTML_script('inline', $scr);
        showXHTML_head_E();
        showXHTML_body_B('style="margin-top: 0; margin-bottom: 0"');
          showXHTML_table_B('id="userTable" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
            showXHTML_tr_B();
              showXHTML_td_B();
                $ary[] = array($MSG['exim_times_list'][$sysSession->lang], 'tabsSet',  '');
                showXHTML_tabs($ary, 1);
              showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B();
              showXHTML_td_B('valign="top" class="bg01"');

              showXHTML_table_B('id="displayPanel" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');
                showXHTML_tr_B('class="bg03 font01" ');
                  if (QTI_which == 'homework' && isAssignmentForGroup($_SERVER['argv'][1], $course_id, QTI_which))
                    showXHTML_td('colspan="8"', sprintf('(%s) <b>%s</b> by <i>%s</i>', $_SERVER['argv'][2], fetchTitle($RS->fields['caption']), $RS->fields['username']));
                  else
                  {
                    list($f, $l) = dbGetStSr('WM_user_account', 'first_name, last_name', 'username="' . $_SERVER['argv'][2] . '"', ADODB_FETCH_NUM);
                      showXHTML_td('colspan="8"', sprintf('(%s) %s', $_SERVER['argv'][2], checkRealname($f, $l)));
                  }
                showXHTML_tr_E();
                showXHTML_tr_B('class="bg02 font01" ');
                  showXHTML_td('align="center" nowrap', $MSG['serial_no'][$sysSession->lang]);
                  showXHTML_td('align="center" nowrap', $MSG['enable_duration'][$sysSession->lang]);
                  showXHTML_td('align="center" nowrap', $MSG['exam_duration'][$sysSession->lang]);
                  showXHTML_td('align="center" nowrap', $MSG['exam_state'][$sysSession->lang]);
                  showXHTML_td('align="center" nowrap', $MSG['do_exam_ip'][$sysSession->lang]);
                  showXHTML_td('align="center" nowrap', $MSG['grade'][$sysSession->lang]);
                  showXHTML_td('align="center" nowrap', $MSG['drop'][$sysSession->lang]);
                  showXHTML_td('align="center" nowrap', $MSG['note'][$sysSession->lang]);
                showXHTML_tr_E();

                $do_interval = $RS1->fields['do_interval'] * 60;
                $chk_timeout = $RS1->fields['ctrl_timeout'] == 'mark' ? true : false;
                $serial_no   = 1;
        while(!$RS->EOF){
            $col = $col == 'class="bg03 font01"' ? 'class="bg04 font01"' : 'class="bg03 font01"';

                showXHTML_tr_B($col . ' onmouseover="chBgc(this, true);" onmouseout="chBgc(this, false);" onclick="pickExam(\'' . $RS->fields['time_id'] . (isSet($_SERVER['argv'][3]) ? "+{$_SERVER['argv'][3]}" : '') . '\',\'' . md5($ticket_head . $RS->fields['time_id']) . '\');" style="cursor: hand"');
                  showXHTML_td('nowrap', $serial_no++);
                  showXHTML_td('nowrap', (QTI_which == 'homework') ? $RS->fields['submit_time'] : ($RS->fields['begin_time'] . '<br>' . $RS->fields['submit_time']));
                  showXHTML_td('nowrap', empty($RS->fields['submit_time']) ? '-' : getTimestamp(strtotime($RS->fields['submit_time']) - strtotime($RS->fields['begin_time']) + 1));
                  showXHTML_td('nowrap', $statuses[$RS->fields['status']]);
                  showXHTML_td('nowrap', getApplyExamIP($_SERVER['argv'][2], $sysSession->course_id, $_SERVER['argv'][1], $RS->fields['begin_time'], $RS->fields['submit_time']));
                  showXHTML_td('nowrap', $RS->fields['score']);
                  showXHTML_td_B('');
                    showXHTML_input('button', '', $MSG['remove'][$sysSession->lang], '', sprintf('onclick="event.cancelBubble=true; removeTest(' . $RS->fields['time_id'] . ',\'' . md5($ticket_head . $RS->fields['time_id']) . '\');" class="cssBtn"', $RS->fields['time_id']));
                  showXHTML_td_E();

                  $note = '&nbsp;';
                  if (empty($RS->fields['content']))
                  {
                      $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
                                               $sysSession->school_id,
                                               $sysSession->course_id,
                                               QTI_which,
                                               $_SERVER['argv'][1],
                                               $_SERVER['argv'][2]);
                      $file =     $RS->fields['time_id'].'.xml';          
                
                      $full_path = $xml_path.$file;
                      if (is_file($full_path)) {
                          $RS->fields['content'] = file_get_contents($full_path);
                      }
                  }
                  if (isset($RS->fields['content']) && preg_match('/<wm:submit_status>(.*)<\/wm:submit_status>/isU', $RS->fields['content'], $match))
                  {
                        switch($match[1])
                        {
                          // MIS#23458 by Small 2011/12/16
                          // 已經沒有放棄作答的按鈕，因此這個over的狀態來源只剩下是『切換視窗』而強迫交卷
                              // case 'over'   : $note = $MSG['msg_exam_quit'][$sysSession->lang]   ; break;    // 放棄作答
                            case 'over'   : $note = $MSG['msg_exam_chgWin'][$sysSession->lang]   ; break;    // 切換視窗，強制交卷
                            case 'mark'   : $note = $MSG['out_of_time'][$sysSession->lang]     ; break;    // 作答逾時
                            case 'timeout': $note = $MSG['msg_exam_timeout'][$sysSession->lang]; break;    // 作答時間到自動交卷
                            case 'chgWin'   : $note = $MSG['msg_exam_chgWin'][$sysSession->lang]   ; break;    // 切換視窗，強制交卷 by Small 2012/02/03
                            case 'appSubmit': $note = $MSG['exam_app_answer'][$sysSession->lang]; break;     // 透過APP作答
                            case 'continue'   : $note = $MSG['msg_exam_continue'][$sysSession->lang]   ; break;    // 續考交卷
                            case 'overtime_closetime': $note = $MSG['overtime_closetime'][$sysSession->lang]; break;	 
                            case 'payback': $note = $MSG['payback'][$sysSession->lang]; break;
                            case 'overtime_delaytime': $note = $MSG['overtime_delaytime'][$sysSession->lang]; break;                          
                        }
                  }
                  else if ($chk_timeout)
                  {
                        $exam_duration = (strtotime($RS->fields['submit_time']) - strtotime($RS->fields['begin_time']));
                        if ($exam_duration > $do_interval) $note = $MSG['out_of_time'][$sysSession->lang];
                  }
                  showXHTML_td('nowrap', $note);
                showXHTML_tr_E();

            $RS->MoveNext();
        }
                  showXHTML_table_E();
                showXHTML_td_E();
              showXHTML_tr_E();
            showXHTML_table_E();
          showXHTML_body_E();

    }
    else
        echo "<h2>No Entry.</h2>\n";
?>

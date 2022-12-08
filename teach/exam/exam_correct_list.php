<?php
    /**
     * 批改列表
     * $Id: exam_correct_list.php,v 1.1 2010/02/24 02:40:25 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
    
    //ACL begin
        include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
        $assignmentsForGroup = getAssignmentsForGroup(null, QTI_which);
    if (QTI_which == 'exam') {
        $sysSession->cur_func='1600300100';
    }
    else if (QTI_which == 'homework') {
        $sysSession->cur_func='1700300100';
    }
    else if (QTI_which == 'questionnaire') {
        $sysSession->cur_func='1800300100';
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

    $random_seat = md5(uniqid(rand(), true));
    $ticket = md5(sysTicketSeed . $course_id . $random_seat);
    $exam_types = array($MSG['exam_type1'][$sysSession->lang],
                        $MSG['exam_type2'][$sysSession->lang],
                        $MSG['exam_type3'][$sysSession->lang],
                        $MSG['exam_type4'][$sysSession->lang],
                        $MSG['exam_type5'][$sysSession->lang]);
    $links = 'onmouseover="this.style.color=\'#0000FF\'; this.style.textDecoration=\'underline\';" onmouseout="this.style.color=\'\'; this.style.textDecoration=\'none\';" onclick="location.replace(\'exam_correct_list.php?sort=%s\')" style="cursor: pointer"';

    showXHTML_head_B($MSG['exam_correct'][$sysSession->lang]);
      showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
      $scr = <<< EOB

function chBgc(obj, mode){
    obj.style.backgroundColor = mode ? '#FFFFCC' : '';
}

function goto_correct(id){
    var obj = document.getElementById('procForm');
    obj.lists.value = id;
    obj.submit();
}

function view_result(id){
    var obj = document.getElementById('procForm');
    obj.action = 'exam_statistics_result.php';
    obj.lists.value = id;
    obj.submit();
}

EOB;
    showXHTML_script('inline', $scr);
    showXHTML_head_E();
    showXHTML_body_B();
      $ary[] = array($MSG['exam_correct'][$sysSession->lang]);
      $icon_hand = '<img src="/theme/' . $sysSession->theme . '/teach/icon_hand.gif">';
      $icon_currect = '<img src="/theme/' . $sysSession->theme . '/teach/icon_currect.gif">';
      echo "<div align=\"center\">\n";
      showXHTML_tabFrame_B($ary);
          showXHTML_table_B('id="displayPanel" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');
            showXHTML_tr_B('class="bg04 font01"');
                // #47360 Chrome  表格右邊框線消失
                // #47383 Chome 右上角框線不見
                if (QTI_which == 'exam')
                    showXHTML_td_B('colspan="7"');
                else
                    showXHTML_td_B('colspan="6"');
                
                    echo  $icon_currect.$MSG['already_correct'][$sysSession->lang]."&nbsp&nbsp&nbsp".$icon_hand.$MSG['wait_correct'][$sysSession->lang];
                showXHTML_td_E('');
            showXHTML_tr_E();
            showXHTML_tr_B('class="bg02 font01"');
                showXHTML_td('align="center" width="50" ' . sprintf($links, 'exam_id'), $MSG['exam_serial_number'][$sysSession->lang]); // Bug#1509-加上序號 by Small 2006/12/14
                showXHTML_td('align="center" width="410"', $MSG['exam_name'][$sysSession->lang]);
                if (QTI_which == 'exam')
                      showXHTML_td('align="center" width="80" ' . sprintf($links, 'type'), $MSG['exam_use'][$sysSession->lang]);
                else
                    showXHTML_td('align="center" width="80"',  $MSG['assignment type'][$sysSession->lang]);
                showXHTML_td('align="center" width="120"', $MSG['exam_duration'][$sysSession->lang]);
                showXHTML_td('align="center" width="50"', $MSG['correct_completed'][$sysSession->lang]);
                showXHTML_td('align="center" width="50"', $MSG['correct'][$sysSession->lang]);
                if (QTI_which == 'exam') showXHTML_td('align="center" width="50"', $MSG['statistics_table'][$sysSession->lang]);
            showXHTML_tr_E();

    $sort = ereg('^(type|exam_id)$', $_GET['sort']) ? $_GET['sort'] : 'exam_id';

    chkSchoolId('WM_qti_' . QTI_which . '_test');
    if (QTI_which == 'exam')
        $random_generatings = $sysConn->GetCol('select exam_id from WM_qti_exam_test where type IN (1, 2, 3, 4) AND course_id=' . $course_id . ' and LOCATE("<wm_immediate_random_generate_qti", content)');
    else
        $random_generatings = array();

    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


                $is_role = array(
                                          'auditor'        =>    16,
                                          'student'        =>    32,
                                          'assistant'      =>    64,
                                          'instructor'     =>   128,
                                          'teacher'        =>   512 
                                         );
    // 取得尚有未批改之測驗
    $my_exams     = $sysConn->GetCol('select exam_id from WM_qti_' . QTI_which . '_test where type IN (1, 2, 3, 4) AND course_id=' . $course_id);
//        $notCorrected = $sysConn->GetCol('select distinct r.exam_id from WM_qti_' . QTI_which . '_result as r, WM_term_major as m where r.status in ("submit","break","chgWin") and r.exam_id in (' . implode(',',$my_exams) . ') and r.examinee = m.username and role != 0 and m.course_id = '. $course_id);
        
        // 檢查各作業是否有繳交未批改
        $notCorrected = array();
        foreach ($my_exams as $v) {
            // 增加判斷如果是群組作業，到底有沒有繳交
            if (isset($assignmentsForGroup[$v]) === true) {
                $payTimes = isAlreadySubmittedAssignmentForGroup($v, null, $sysSession->course_id) ? 1 : 0;
            } else {
                $payTimes = $sysConn->Getone('select count(r.exam_id) from WM_qti_' . QTI_which . '_result as r, WM_term_major as m where r.status in ("submit","break","chgWin") and r.exam_id = ' . $v . ' and r.examinee = m.username and role != 0 and m.course_id = '. $course_id . ' AND m.role&' . ($sysRoles['auditor'] | $sysRoles['student']));
            }
            if ((int)$payTimes >= 1) {
                $notCorrected[] = $v;
            }
        }

    if(is_array($notCorrected)) {
        $qti_fun_id = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200);
        // 取得本門課所有帳號的身分
        $user_role_ary = $sysConn->GetAssoc('select username, role from WM_term_major where course_id = '. $course_id . ' AND WM_term_major.role&' . ($sysRoles['auditor'] | $sysRoles['student']));
        foreach ($notCorrected as $key => $exam_id) {
            // 取得指定的ACL帳號
            $sqls = 'select distinct M.member from WM_qti_' . QTI_which . '_test as T left join WM_acl_list as L on T.course_id = L.unit_id and T.exam_id = L.instance and L.function_id = ' . $qti_fun_id[QTI_which] . ' left join WM_acl_member as M on L.acl_id = M.acl_id where type IN (1, 2, 3, 4) AND T.course_id =' . $course_id . ' and T.exam_id = '. $exam_id . ' and M.member not like "@%" and M.member not like "#%"';
            $acl_member_ary = $sysConn->GetCol($sqls);

            // 取得未批改資料的填寫者帳號
            $not_exist_user_ary = $sysConn->GetCol('select distinct examinee from WM_qti_' . QTI_which . '_result where status in ("submit","break","chgWin") and exam_id = '. $exam_id);

            // 在未批改的資料中，若有指定ACL帳號，但填寫者帳號並不在ACL名單內，則視為已批改
            if (is_array($acl_member_ary) && count($acl_member_ary)) {
                // 排除不存在於ACL帳號的帳號
                foreach ($not_exist_user_ary as $idx => $user) {
                    if (!in_array($user, $acl_member_ary)) unset($not_exist_user_ary[$idx]);
                }
            }
            // 在未批改的資料中，若有指定ACL群組，但填寫者帳號並不在ACL群組內，則視為已批改
            else {
                // 取得指定的ACL群組
                $sqls = 'select distinct SUBSTRING(M.member, 2) from WM_acl_list as L left join WM_acl_member as M on L.acl_id = M.acl_id where L.unit_id =' . $course_id . ' and L.instance = '. $exam_id . ' and L.function_id = '. $qti_fun_id[QTI_which] .' and M.member like "#%"';
                $acl_group_ary = $sysConn->GetCol($sqls);

                if (is_array($acl_group_ary) && count($acl_group_ary)) {
                    // 排除不存在於ACL群組的帳號

                    foreach ($not_exist_user_ary as $idx => $user) {
                $temp_check=0;
                   foreach($acl_group_ary as $group_idx =>$ary_role){

                    if($sysRoles[$ary_role] & $user_role_ary[$user]){
                        $temp_check++;
                    }
                }
                 if ($temp_check==0) unset($not_exist_user_ary[$idx]);
                    }
                }
            }
            
            // 若沒有未批改的帳號，則視為已批改，從未批改的資料中移除
            if(!count($not_exist_user_ary)) unset($notCorrected[$key]);
        }
    }

    // 取得全部測驗
    $RS = dbGetStMr('WM_qti_' . QTI_which . '_test',
                    'exam_id,title,type,begin_time,close_time',
                    'type IN (1, 2, 3, 4) AND course_id=' . $course_id . ' order by sort,' . $sort . ' DESC',
                    ADODB_FETCH_ASSOC);

    if ($sysConn->ErrorNo() > 0) {
       $errMsg = $sysConn->ErrorMsg();
       wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
       die($errMsg);
    }
    $exam_num = 1;
    if ($RS)
    while($fields = $RS->FetchRow()){
            $exam_cnt = dbGetOne('WM_qti_' . QTI_which . '_result left join WM_term_major ON WM_qti_' . QTI_which . '_result.examinee = WM_term_major.username','count(0)',"exam_id = {$fields['exam_id']} AND WM_term_major.course_id = $course_id AND WM_term_major.role&" . ($sysRoles['auditor'] | $sysRoles['student']));
            $ans_cnt=0;
            if (in_array($fields['exam_id'], $notCorrected)) {
                $RS_user = dbGetStMr('WM_qti_' . QTI_which . '_result', 'examinee,exam_id,time_id,content', 'exam_id=' . $fields['exam_id'] . ' and `status` ="submit"', ADODB_FETCH_ASSOC);
                if (QTI_which == 'exam') {
                    while (!$RS_user->EOF) {
                        $username = $RS_user->fields['examinee'];
                        $time_id  = $RS_user->fields['time_id'];
                        $content  = $RS_user->fields['content'];
                        if (empty($content))
                        {
                           $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
                                                   $sysSession->school_id,
                                                   $sysSession->course_id,
                                                   QTI_which,
                                                   $fields['exam_id'],
                                                   $username);
                           $file =     $time_id.'.xml';          
                    
                           $full_path = $xml_path.$file;
                           if (is_file($full_path)) {
                               $content = file_get_contents($full_path);
                           }
                        }
            
                        if (!$dom = domxml_open_mem($content)) {
                            die('Error while parsing the document.');
                        }
                                                
                        if (empty($_COOKIE['VKcxpNwu5XXAHfSf']) === FALSE) {                                           
                            echo '<pre>';
                            var_dump('測驗編號', $RS_user->fields['exam_id']);
                            var_dump('簡答題（1有0無）', preg_match('/\sprompt=\"Box\"\srows=\"[\d]+\" /', $content));
                            echo '</pre>';
                        }  
                                                
                        // 若有簡答題，不處理
                        if (preg_match('/\sprompt=\"Box\"\srows=\"[\d]+\" /', $content)) {
                        } else {
                            $ctx = xpath_new_context($dom);
                            // 計算得分
                            define('QTI_DISPLAY_RESPONSE', true); // 顯示作答答案
                            define('QTI_DISPLAY_ANSWER', true); // 是否顯示答案
                            define('QTI_DISPLAY_OUTCOME', true); // 是否顯示得分
                            include_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
                            ob_start();
                            parseQuestestinterop($dom->dump_mem(false));
                            $result_html = ob_get_contents();
                            ob_end_clean();

                            if (preg_match_all('/<input\b[^>]*\bname="item_scores\b[^>]*\bvalue="(-?[0-9.]*)"/', $result_html, $regs))
                                $total_score = array_sum($regs[1]);
                            else
                                $total_score = 0;

                            //                        echo $total_score.'<BR>';

                            // 判斷是否都是是非選擇題
                            $ret1 = $ctx->xpath_eval('count(//item/presentation//render_choice)+count(//item/presentation//response_grp/render_extension)+count(//item/presentation//response_str/render_fib)');

                            $ret2   = $ctx->xpath_eval('count(//item/presentation)');
                            $status = (intval($ret1->value) < intval($ret2->value)) ? 'submit' : 'revised';

                            $ret3 = $ctx->xpath_eval('//item/presentation//response_str/render_fib[@prompt="Box"]');

                            //判斷題目中是否有填充題，有則修改狀態與成績
                            if (count($ret3->nodeset) == 0) {
                                if ($status == 'revised') {
                                    dbSet('WM_qti_' . QTI_which . '_result', 'status="' . $status . '",score=' . $total_score, "exam_id={$fields['exam_id']} and time_id={$time_id} and examinee='{$username}'");

                                    reCalculateQTIGrade($username, $fields['exam_id'], QTI_which);
                                }
                            }
                        }
                        $RS_user->MoveNext();
                    }
                }

            }

            $col = $col == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"';
            showXHTML_tr_B($col . ' onmouseover="chBgc(this, true);" onmouseout="chBgc(this, false);"');
                $title = (strpos($fields['title'], 'a:') === 0) ? getCaption($fields['title']) : array(
                    'Big5' => $fields['title'],
                    'GB2312' => $fields['title'],
                    'en' => $fields['title'],
                    'EUC-JP' => $fields['title'],
                    'user_define' => $fields['title']
                );
                showXHTML_td('align="center" width="50"', $exam_num++); // Bug#1509-加上序號 by Small 2006/12/14
                showXHTML_td('nowrap title="' . htmlspecialchars($title[$sysSession->lang]) . '"', sprintf('<span style="width:320px; overflow: hidden">%s</span>', htmlspecialchars($title[$sysSession->lang]) . (in_array($fields['exam_id'], $random_generatings) ? '<span title="random generate" style="position: relative; top: -3px">&#174;</span>' : '')));
                if (QTI_which == 'exam')
                    showXHTML_td('align="center"', $exam_types[$fields['type']]);
                else
                    showXHTML_td('align="center"', isset($assignmentsForGroup[$fields['exam_id']]) ? $MSG['for group'][$sysSession->lang] : $MSG['for personal'][$sysSession->lang]);
                showXHTML_td('style="font-size: 10"', ($MSG['from'][$sysSession->lang] . (strpos($fields['begin_time'], '0000') === 0 ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($fields['begin_time']))) . '<br>' . $MSG['to2'][$sysSession->lang] . (strpos($fields['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($fields['close_time'])))));
                showXHTML_td('align="center"', ($exam_cnt != 0) ? ('<img src="/theme/' . $sysSession->theme . '/teach/' . (in_array($fields['exam_id'], $notCorrected) ? 'icon_hand.gif' : 'icon_currect.gif') . '" align="absmiddle" width="20" height="23">') : '');
                showXHTML_td_B('align="center"');
                    showXHTML_input('button', '', $MSG['toolbtm06'][$sysSession->lang], '', 'onclick="goto_correct(' . $fields['exam_id'] . ');"');
                showXHTML_td_E();
                if (QTI_which == 'exam') {
                    showXHTML_td_B('align="center"');
                        if (in_array($fields['exam_id'], $random_generatings))
                            echo '&nbsp;';
                        else
                            showXHTML_input('button', '', $MSG['view'][$sysSession->lang], '', 'onclick="view_result(' . $fields['exam_id'] . ');"');
                    showXHTML_td_E();
                }
            showXHTML_tr_E();
    }
        showXHTML_table_E();
    showXHTML_tabFrame_E();
    echo "</div>\n";
    showXHTML_form_B('method="POST" action="exam_correct.php"', 'procForm');
        showXHTML_input('hidden', 'ticket', $ticket);
        showXHTML_input('hidden', 'referer', $random_seat);
        showXHTML_input('hidden', 'lists', '');
    showXHTML_form_E();

    showXHTML_body_E();
?>

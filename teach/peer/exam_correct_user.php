<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                         *
     *      Creation  : 2003/04/10                                                            *
     *      work for  : 列出測驗的應試員                                                      *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    set_time_limit(0);
    // function array_intersect_key
    if (!function_exists('array_intersect_key'))
    {
           function array_intersect_key ($isec, $arr2)
           {
               $argc = func_num_args();

               for ($i = 1; !empty($isec) && $i < $argc; $i++)
               {
                 $arr = func_get_arg($i);

                 foreach ($isec as $k => $v)
                    if (!isset($arr[$k]))
                         unset($isec[$k]);
               }

               return $isec;
           }
    }

    //ACL begin
    include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
    $sysSession->cur_func='1710400100';
    $sysSession->restore();
    if (!aclVerifyPermission(1600200400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

    }
    //ACL end

    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;

    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

    if (!isset($_SERVER['argv'][0])) {  // 檢查 ticket 是否存在
       wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
       die('Access denied.');
    }
    $ticket_head = sysTicketSeed . $course_id . $_SERVER['argv'][1];
    if (md5($ticket_head) != $_SERVER['argv'][0]) { // 檢查 ticket
       wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
       die('Fake ticket.');
    }

    $qti_fun_id = array('peer' => 1710400200);
    if (QTI_which == 'peer' && isAssignmentForGroup($_SERVER['argv'][1], $course_id, 'peer'))
    {
        // 取得 ACL 所指派的組別
        $sqls = 'select M.member ' .
                'from WM_acl_list as L,WM_acl_member as M ' .
                "where L.function_id={$qti_fun_id[QTI_which]} " .
                "and L.unit_id={$sysSession->course_id} " .
                "and L.instance={$_SERVER['argv'][1]} " .
                'and L.acl_id=M.acl_id';
        $assigned_groups = $sysConn->GetCol($sqls);

        if (count($assigned_groups) && preg_match('/@(\d+)\.\d+/', $assigned_groups[0], $match))
        {
            $team_id = $match[1];
        }
    }

    // 整批作業開放觀摩 Begin
    if (QTI_which == 'peer' &&
        strtolower($_SERVER['REQUEST_METHOD']) == 'post' &&
        in_array($_POST['batchPublish'], array('revised', 'all')))
    {
        if ($_POST['batchPublish'] == 'all')
        {
            $result_rs = dbGetStMr('WM_qti_peer_result', 'examinee,time_id,content', 'exam_id=' . intval($_SERVER['argv'][1]) . ' and status != "revised" and status != "publish"');
            if ($result_rs && $result_rs->RecordCount())
            {
                define('QTI_DISPLAY_ANSWER',   true);   // 定義常數；顯示標準答案
                define('QTI_DISPLAY_OUTCOME',  true);   // 定義常數；顯示批改結果及得分
                define('QTI_DISPLAY_RESPONSE', true);   // 定義常數；顯示學生答案
                include_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
                include_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
                while ($row = $result_rs->FetchRow())
                {
                    ob_start();
                    parseQuestestinterop($row['content']);
                    $result_html = ob_get_contents();
                    ob_end_clean();
                    if (preg_match_all('/<input\b[^>]*\bname="item_scores\b[^>]*\bvalue="(-?[0-9.]*)"/', $result_html, $regs))
                        $total_score = array_sum($regs[1]);
                    else
                        $total_score = 0;
                    dbSet('WM_qti_peer_result', "score={$total_score}, status='revised'" , "exam_id={$_SERVER['argv'][1]} and examinee='{$row['examinee']}' and time_id={$row['time_id']}");
                    reCalculateQTIGrade($row['examinee'], $_SERVER['argv'][1], 'peer', null, isset($team_id) && $team_id > 0 ? $team_id : null);
                }
                reCalculateGrades();
            }
        }

        $result = (bool) dbSet('WM_qti_peer_result', 'status="publish"', 'exam_id=' . intval($_SERVER['argv'][1]) . ' and status="revised"');
        $onload_js = 'alert("' . ($result ? $MSG['msg_public_success'][$sysSession->lang] : $MSG['msg_public_fail'][$sysSession->lang]) . '");';
    }
    // 整批作業開放觀摩 End

    if (QTI_which == 'peer' && isAssignmentForGroup($_SERVER['argv'][1], $course_id, 'peer'))
    {
        if ($team_id)
        {
            $sqls = 'select distinct D.group_id, D.team_id, G.caption,R.status, R.examinee ' .
                    ' from WM_student_div as D ' .
                    ' left join WM_qti_peer_result as R on D.username=R.examinee and R.exam_id=' . $_SERVER['argv'][1] .
                    ' inner join WM_student_group as G on G.course_id = D.course_id and G.group_id = D.group_id and G.team_id = D.team_id ' .
                    ' where D.course_id=' . $sysSession->course_id .
                    ' and D.team_id=' . $team_id .
                    ' and G.course_id=' . $sysSession->course_id .
                    ' and G.team_id=' . $team_id .
                    ' order by G.group_id,R.status';
            $keep = $ADODB_FETCH_MODE;
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
            $RS = $sysConn->GetAssoc($sqls);
            $ADODB_FETCH_MODE = $keep;
        }
        else
        {
            $team_id = 0;
            $RS = array();
        }
    }
    else
    {
        $role = intval($_POST['role']);
        if (!in_array($role, $sysRoles)) $role = $sysRoles['all'];

        $submit_statuss  = array('', '', 'having total>0 '   , 'having total=0 ');
        $correct_statuss = array('', '', 'and uncorrected=0 ', 'and uncorrected>0 ');
        $submit_status   = ereg('^[123]$', $_POST['submit_status'])  ? intval($_POST['submit_status'])  : 2;
        $correct_status  = ereg('^[123]$', $_POST['correct_status']) ? intval($_POST['correct_status']) : 1;
        $condition       = preg_replace('/^and/', 'having', $submit_statuss[$submit_status] . $correct_statuss[$correct_status]);

        $sqls            = 'select M.username, sum(if(isnull(R.status),0,1)) as total, ' .
                           'SUM(IF(ISNULL(R.score), IF(ISNULL(R.comment_txt), if(R.status IN ("revised", "submit"), 1, 0), 0), 0)) AS uncorrected ' .
                           'from WM_term_major as M left join WM_qti_' . QTI_which . '_result as R ' .
                           'on M.username = R.examinee and R.exam_id=' . $_SERVER['argv'][1] .
                           " where M.course_id={$course_id} and M.role & {$role} " .
                           'group by M.username ' . $condition . ' order by M.username';
        $RS              = $sysConn->GetAssoc($sqls);

        // 判斷ACL開始
        $acl_ids = $sysConn->GetCol('select acl_id from WM_acl_list where function_id="' . $qti_fun_id[QTI_which] . '" and unit_id="' . $course_id . '" and instance="' . $_SERVER['argv'][1] . '"');
        if (is_array($acl_ids) && count($acl_ids)) {
            $can_do = array();
            foreach ($acl_ids as $acl_id)
                $can_do = array_merge($can_do, aclGetMembersByAcl($acl_id, $course_id));
            $can_do = array_unique($can_do);
        }
        else {
            $can_do = $sysConn->GetCol('select username from WM_term_major where course_id=' . $course_id . ' and role & ' . $sysRoles['student']);
        }

        $RS = array_intersect_key($RS, array_flip($can_do));
        // 判斷ACL結束
    }

    $total_item      = count($RS);
    $item_per_page   = max((int)$_POST['ipp'], sysPostPerPage);
    $total_page      = max(ceil($total_item / $item_per_page), 1);
    $curr_page       = min(max((int)$_POST['cp'], 1), $total_page);
    $pages           = range(0, $total_page); unset($pages[0]);
    if ($total_item > $item_per_page){
        /**
         * Bug#1554-教師辦公室→作業管理→作業批改：分組作業無法正常顯示 by Small 2006/12/27
         * 因為array_slice在PHP4.4.4沒有支援將原來的key保留住
         * 因此,另寫分隔array的程式,以達到分頁的目的
         **/
        // Small 寫的還是錯誤，改以下列兩行取代原本的 array_slice()
        $temp_RS = array_chunk($RS, $item_per_page, true);
        $RS      = $temp_RS[$curr_page - 1];

        // $RS = array_slice($RS, (($curr_page-1)*$item_per_page), $item_per_page);
    }

    // 開始 output HTML
    showXHTML_head_B('Examinee List');
      showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
      $scr = <<< EOB
var T={$total_page};

function resizeFrame(){
    parent.document.getElementById('fs1').cols = (document.getElementById('userTable').clientWidth + 42) + ',*';
}

function chBgc(obj, mode){
    obj.style.backgroundColor = mode ? '#FFFFCC' : '';
}

function pickUser(user, ticket){
    parent.rtop.location.replace('exam_correct_times.php?' + ticket + '+{$_SERVER['argv'][1]}+' + user);
}
function HW_TarAttaches(title,hwid)
{
    if (confirm('{$MSG['msg1'][$sysSession->lang]}'+title+'{$MSG['msg2'][$sysSession->lang]}'))
    {
        window.open('/teach/peer/TarAttach.php?'+hwid, 'winTar', "width=350,height=80,resizable=0,status=0,titlebar=0,toolbar=0,menubar=0,scrollbars=0");
    }
}

function test2(hwid){
    window.parent.hw_all(hwid);
}

function handler(){
    window.open('/teach/homework/homework_{$sysSession->lang}.html', '', 'width=820, height=450, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}

window.onload=function()
{
    {$onload_js}
    resizeFrame();

    parent.rbottom.document.body.innerHTML='';
    parent.rbottom.document.write('<h2 align=center>{$MSG['pick_times_first'][$sysSession->lang]}</h2><script src="/lib/disable.js" type="text/javascript"></script>');
    parent.rbottom.document.close();
    if(parent.rtop.location == 'about:blank') {
        parent.rtop.document.body.innerHTML='';
        parent.rtop.document.write('<h2 align=center>{$MSG['pick_student_first'][$sysSession->lang]}</h2><script src="/lib/disable.js" type="text/javascript"></script>');
        parent.rtop.document.close();
    }
};

function batchPublish(type, msg)
{
    if (confirm(msg))
    {
        var obj = document.getElementById('listFM');
        if (obj && obj.batchPublish)
        {
            obj.batchPublish.value = type;
            obj.submit();
        }
    }
}
EOB;
      showXHTML_script('inline', $scr);
    showXHTML_head_E();
    showXHTML_body_B('style="margin-top: 0; margin-bottom: 0"');
      showXHTML_table_B('id="userTable" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
        showXHTML_tr_B();
          showXHTML_td_B();
            $ary[] = array($MSG['examinee_list'][$sysSession->lang]);
            showXHTML_tabs($ary, 1);
          showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_tr_B();
          showXHTML_td_B('valign="top" class="bg01"');

          showXHTML_form_B('method="POST" action="' . $_SERVER['REQUEST_URI'] . '" style="display: inline" id="listFM"');
          showXHTML_input('hidden', 'batchPublish', '');
          showXHTML_table_B('id="displayPanel" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');
            showXHTML_tr_B('class="bg04 font01"');
              $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
              list($title, $announce_type, $announce_time) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'title, announce_type, announce_time', 'exam_id=' . $_SERVER['argv'][1], ADODB_FETCH_NUM);
              $titles = unserialize($title);
              showXHTML_td('colspan="5"', $MSG['exam_name'][$sysSession->lang] . ' : ' . htmlspecialchars($titles[$sysSession->lang]));
            showXHTML_tr_E();

            if (QTI_which != 'peer' || !isAssignmentForGroup($_SERVER['argv'][1], $course_id, 'peer'))
            {
            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td_B('colspan="5" nowrap');
              if (QTI_which == 'exam')
              {
                  echo $MSG['exam_whether_attended'][$sysSession->lang];
                  showXHTML_input('select', 'submit_status', array(1 => $MSG['all'][$sysSession->lang],
                                                                   2 => $MSG['examinee_title4'][$sysSession->lang],
                                                                   3 => $MSG['exam_not_attended'][$sysSession->lang]
                                                                  ), $submit_status);
              }
              else
              {
                  echo $MSG['hw_whether_attended'][$sysSession->lang];
                  showXHTML_input('select', 'submit_status', array(1 => $MSG['all'][$sysSession->lang],
                                                                   2 => $MSG['examinee_title6'][$sysSession->lang],
                                                                   3 => $MSG['hw_not_attended'][$sysSession->lang]
                                                                  ), $submit_status);
              }
              echo '&nbsp;&nbsp;', $MSG['whether_revised'][$sysSession->lang];
              showXHTML_input('select', 'correct_status', array(1 => $MSG['all'][$sysSession->lang],
                                                                2 => $MSG['revised'][$sysSession->lang],
                                                                3 => $MSG['examinee_title5'][$sysSession->lang]
                                                               ), $correct_status);
              showXHTML_input('submit', '', $MSG['search'][$sysSession->lang], '', 'class="cssBtn"');
              showXHTML_td_E();
            showXHTML_tr_E();
            }
            showXHTML_tr_B('class="bg04 font01"');
              showXHTML_td_B('colspan="5" nowrap');
              echo $MSG['page'][$sysSession->lang];
              showXHTML_input('select', '', $pages, $curr_page, 'onchange="this.form.cp.value=this.value; this.form.submit();"');
              echo '&nbsp;&nbsp;', $MSG['each_page'][$sysSession->lang];
              showXHTML_input('select', '', array(sysPostPerPage => $MSG['default'][$sysSession->lang],
                                                  20             => 20,
                                                  50             => 50,
                                                  100            => 100,
                                                  200            => 200,
                                                  400            => 400
                                                 ), $item_per_page, 'onchange="this.form.ipp.value=this.value; this.form.submit();"');
              echo $MSG['s'][$sysSession->lang];
              showXHTML_input('hidden', 'ipp', $item_per_page);
              showXHTML_input('hidden', 'cp' , $curr_page);
              showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td_B('id="toolbar1" colspan="5"');
              showXHTML_input('button', '', $MSG['page_first'][$sysSession->lang], '', 'class="cssBtn" ' . ($curr_page == 1           ? 'disabled' : 'onclick="this.form.cp.value=1; this.form.submit();"'));
              showXHTML_input('button', '', $MSG['page_prev'][$sysSession->lang],  '', 'class="cssBtn" ' . ($curr_page == 1           ? 'disabled' : 'onclick="this.form.cp.value--; this.form.submit();"'));
              showXHTML_input('button', '', $MSG['page_next'][$sysSession->lang],  '', 'class="cssBtn" ' . ($curr_page == $total_page ? 'disabled' : 'onclick="this.form.cp.value++; this.form.submit();"'));
              showXHTML_input('button', '', $MSG['page_last'][$sysSession->lang],  '', 'class="cssBtn" ' . ($curr_page == $total_page ? 'disabled' : 'onclick="this.form.cp.value=T; this.form.submit();"'));
              showXHTML_td_E();
            showXHTML_tr_E();

    $location = ($curr_page - 1) * $item_per_page;
    $i = $location + 1;

    if (QTI_which == 'peer' && isAssignmentForGroup($_SERVER['argv'][1], $course_id, 'peer'))
    {
            showXHTML_tr_B('class="bg02 font01"');
              showXHTML_td('align="center" nowrap', 'No.');
              showXHTML_td('colspan="2" nowrap', $MSG['group_name'][$sysSession->lang]);

              // 作業 => 顯示 已寫 , 測驗 及 問卷 => 已考
              showXHTML_td('align="center" nowrap', $MSG['examinee_title6'][$sysSession->lang]);
              showXHTML_td('align="center" nowrap', $MSG['examinee_title5'][$sysSession->lang]);
            showXHTML_tr_E();

        if (is_array($RS) && count($RS))
            foreach($RS as $group_id => $fields)
            {
                $col = $col == 'class="bg03 font01"' ? 'class="bg04 font01"' : 'class="bg03 font01"';
                // 有指派才顯示 link
                if (in_array("@{$team_id}.{$group_id}", $assigned_groups))
                {
                    showXHTML_tr_B($col . ' onmouseover="chBgc(this, true);" onmouseout="chBgc(this, false);"' . (($fields['status'] && $fields['status'] != 'break') ? (' onclick="pickUser(\'' . $fields['examinee'] . "', '" . md5($ticket_head . $fields['examinee']) . '\');" style="cursor: pointer'):'" style="cursor: default') . '"');
                    showXHTML_td('align="right" nowrap', sprintf('%u. ', $i++));
                    showXHTML_td('colspan="2" nowrap', '<span style="width: 140px; overflow: hidden" onmouseover="this.title=this.innerHTML;">' . htmlspecialchars(fetchTitle($fields['caption']), ENT_NOQUOTES) . '</span>');
                    showXHTML_td('align="right" nowrap', ($fields['status'] && $fields['status'] != 'break') ? '1' : '0');
                    showXHTML_td('align="right" nowrap', $fields['status'] == 'submit' ? '1' : '0');
                    showXHTML_tr_E();
                }
                else
                {
                    showXHTML_tr_B($col . ' onmouseover="chBgc(this, true);" onmouseout="chBgc(this, false);" style="cursor: default; text-decoration: line-through"');
                    showXHTML_td('align="right" nowrap', sprintf('%u. ', $i++));
                    showXHTML_td('colspan="2" nowrap', '<span style="width: 140px; overflow: hidden" onmouseover="this.title=this.innerHTML;">' . htmlspecialchars(fetchTitle($fields['caption']), ENT_NOQUOTES) . '</span>');
                    showXHTML_td('align="center" nowrap', '');
                    showXHTML_td('align="center" nowrap', '');
                    showXHTML_tr_E();
                }
            }
    }
    else
    {
            showXHTML_tr_B('class="bg02 font01"');
              showXHTML_td('align="center" nowrap', 'No.');
              showXHTML_td('align="center" nowrap', $MSG['examinee_title2'][$sysSession->lang]);
              showXHTML_td('align="center" nowrap', $MSG['examinee_title3'][$sysSession->lang]);

              // 作業 => 顯示 已寫 , 測驗 及 問卷 => 已考
              showXHTML_td('align="center" nowrap', $MSG['examinee_title6'][$sysSession->lang]);
              showXHTML_td('align="center" nowrap', $MSG['examinee_title5'][$sysSession->lang]);
            showXHTML_tr_E();

        if (is_array($RS) && count($RS))
        {
            $names = $sysConn->GetAssoc('select username, if(first_name REGEXP "^[0-9A-Za-z _-]*$" && last_name REGEXP "^[0-9A-Za-z _-]*$", concat(IFNULL(`first_name`,""), " ", IFNULL(`last_name`,"")), concat(IFNULL(`last_name`,""), IFNULL(`first_name`,""))) from WM_user_account where username in ("' . implode('","', array_keys($RS)) . '")');
            foreach($RS as $username => $fields)
            {
                $col = $col == 'class="bg03 font01"' ? 'class="bg04 font01"' : 'class="bg03 font01"';
                    showXHTML_tr_B($col . ' onmouseover="chBgc(this, true);" onmouseout="chBgc(this, false);"' . ($fields['total'] ? (' onclick="pickUser(\'' . $username . "', '" . md5($ticket_head . $username) . '\');" style="cursor: pointer'):'" style="cursor: default') . '"');
                    showXHTML_td('align="right" nowrap', sprintf('%u. ', $i++));
                    showXHTML_td('nowrap', $username);
                    showXHTML_td('nowrap', '<span style="width: 100px; overflow: hidden">' . htmlspecialchars($names[$username], ENT_NOQUOTES) . '</span>');
                    showXHTML_td('align="right" nowrap', $fields['total']);
                    showXHTML_td('align="right" nowrap', $fields['uncorrected']);
                    showXHTML_tr_E();
            }
        }
    }
            showXHTML_tr_B('class="bg03 font01"');
              showXHTML_td_B('id="toolbar2" colspan="5"', '');
            showXHTML_tr_E();
            showXHTML_script('inline',
              "document.getElementById('toolbar2').innerHTML = document.getElementById('toolbar1').innerHTML;"
            );

        $hw_path = sysDocumentRoot . sprintf('/base/%5u/course/%8u/peer/A/%09u', $sysSession->school_id, $sysSession->course_id, $_SERVER['argv'][1]);
        if (is_dir($hw_path) && (bool)exec("find '$hw_path' -type f | wc -l"))
        {
            showXHTML_tr_B('class="bg03 font01"');
                showXHTML_td_B('colspan="5" align="center"');
                    showXHTML_input('button', '', $MSG['attach'][$sysSession->lang]   , '', 'id="demo2" class="cssBtn" onclick="test2(\''.sprintf('%09u', $_SERVER['argv'][1]).'\');"');
                    //showXHTML_input('button', '', $MSG['attach'][$sysSession->lang]   , '', 'class="cssBtn" onclick="HW_TarAttaches(\''.htmlspecialchars(addslashes($titles[$sysSession->lang])).'\',\''.sprintf('%09u', $_SERVER['argv'][1]).'\');"');
                showXHTML_td_E('');
            showXHTML_tr_E();
        }
              showXHTML_table_E();
              showXHTML_form_E();
            showXHTML_td_E();
          showXHTML_tr_E();
        showXHTML_table_E();
        // 整批作業開放觀摩
         showXHTML_form_B('method="POST" id="formDownloadAll" name="formDownloadAll" action="co_downloadAllfiles.php" ');
              showXHTML_input('hidden', 'examinee', '' ,'','id="examinee"');
              showXHTML_input('hidden', 'exam_id',  '' ,'','id="exam_id"');
              showXHTML_input('hidden', 'exam_size',  '' ,'','id="exam_size"');
             showXHTML_form_E();
        if ($announce_type == 'never')
        {
            echo '<div class="font01" style="margin-top: 8px; color: blue;">' . $MSG['msg_never_publish'][$sysSession->lang] . '</div>';
        }
        else
        {
            switch($announce_type)
            {
                case 'now'        : $replace = $MSG['open_time_now'][$sysSession->lang]  ; break;
                case 'close_time' : $replace = $MSG['open_time_close'][$sysSession->lang]; break;
                default:            $replace = $announce_time;
            }
            $revised_msg = str_replace('%OPEN_TIME%', $replace, $MSG['batch_revised_publish_hint'][$sysSession->lang]);
            $all_msg     = str_replace('%OPEN_TIME%', $replace, $MSG['batch_publish_hint'][$sysSession->lang]);
            echo '<div style="margin-top: 8px;">';
            showXHTML_input('button', '', $MSG['batch_revised_publish'][$sysSession->lang]   , '', 'class="cssBtn" onclick="batchPublish(\'revised\', \'' . $revised_msg . '\');"');
            echo '</div><div style="margin-top: 8px;">';
            showXHTML_input('button', '', $MSG['batch_publish'][$sysSession->lang]   , '', 'class="cssBtn" onclick="batchPublish(\'all\', \'' . $all_msg . '\');"');
            echo '</div>';
        }
        echo '<div style="margin-top: 8px;">';
        echo '<font color="red" size="2px">' . $MSG['tar_hw_tip'][$sysSession->lang] . '</font>';
        echo '</div>';
      showXHTML_body_E();
?>

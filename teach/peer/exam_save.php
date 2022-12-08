<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2002/10/04                                                            *
     *        work for  : save a new exam or a modified exam                                    *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    // 日期是否設定
    function hasSetDate($date)
    {
        return ($date != '' &&
                $date != '0000-00-00 00:00:00' &&
                $date != '9999-12-31 00:00:00'
               ) ? true : false;
    }
    //ACL begin
        $sysSession->cur_func = isset($_POST['exam_id']) ? '1710200200' : '1710200100';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

    }
    //ACL end

    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;

    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

    /**
     * 驗證 ticket 是否正確
     */
    function verify(){
        if (empty($_POST['ticket'])) return false;
        return ($_POST['ticket'] == md5(sysTicketSeed . $_COOKIE['idx'] . $_POST['exam_id']) ?
               true :
               false) ;
    }

    /**
     * 開始處理
     */
    if ($_POST['modifiable'] != 'Y') $_POST['modifiable'] = 'N';

    if ($_POST['rdoPublish'] == 1 && QTI_which !== 'peer') {    // 不發布或準備中
        $begin_time = '0000-00-00 00:00:00';
        $close_time = '9999-12-31 00:00:00';
    }
    else {
        $begin_time = (isset($_POST['ck_begin_time'])) ? $_POST['begin_time'].':00' : '0000-00-00 00:00:00';
        $close_time = (isset($_POST['ck_close_time'])) ? $_POST['close_time'].':00' : '9999-12-31 00:00:00';
    }
    $announce_time = ($_POST['announce_type'] == 'user_define') ? $_POST['announce_time'].':00' : 'NULL';


    foreach(array('Big5','GB2312','en','EUC-JP','user_define') as $charset)
       $_POST['title'][$charset] = stripslashes($_POST['title'][$charset]);

    $title = is_array($_POST['title']) ? addslashes(serialize($_POST['title'])) : addslashes(serialize($_POST['title']));

    $exam_perm = array(array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200, 'peer' => 1710400200),
                       array('homework' => 1700300100, 'exam' => 1600300100, 'questionnaire' => 0, 'peer' => 1710300100));

    // 資料防駭
    foreach(array('do_times', 'do_interval', 'item_per_page', 'random_pick') as $i) $_POST[$i] = intval($_POST[$i]);
    if (!in_array($_POST['modifiable']   , array('N', 'Y')))                                    $_POST['modifiable']    = 'N';
    if (!in_array($_POST['publish']      , array('prepare','action','close')))                  $_POST['publish']       = 'prepare';
    if (!in_array($_POST['count_type']   , array('none','first','last','max','min','average'))) $_POST['count_type']    = 'first';
    if (!in_array($_POST['ctrl_paging']  , array('none','can_return','lock')))                  $_POST['ctrl_paging']   = 'none';
    if (!in_array($_POST['ctrl_window']  , array('none','lock')))                               $_POST['ctrl_window']   = 'none';
    if (!in_array($_POST['ctrl_timeout'] , array('none','mark','auto_submit')))                 $_POST['ctrl_timeout']  = 'none';
    if (!in_array($_POST['announce_type'], array('never','now','close_time','user_define')))    $_POST['announce_type'] = 'never';
    $_POST['item_cramble'] = implode(',', array_intersect(array('enable','choice','item','section','random_pick'), explode(',', $_POST['item_cramble'])));
    $_POST['percent'] = floatval($_POST['percent']);
    if (isset($_POST['threshold_score']) && !ereg('^([0-9]+(\.[0-9])?)?$', $_POST['threshold_score'])) $_POST['threshold_score'] = '';
    $setting = preg_replace('/,$/', '', ($_POST['setting']['upload']    ? 'upload,'    : '') .
                                        ($_POST['setting']['anonymity'] ? 'anonymity,' : ''));

    if ($xmlstr = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', stripslashes($_POST['content']))))
    {
        if (!$dom = domxml_open_mem($xmlstr)) die('XML containing incorrect char(s).<textarea style="display: hidden">' . $xmlstr . '</textarea>');
        $_POST['content'] = addslashes($xmlstr);
    }
        if (isset($_POST['ck_rating_begin_time']) == false) {
            $start_date = '0000-00-00 00:00:00';
        } else {
            $start_date = $_POST['rating_begin_time'];
        }
        if (isset($_POST['ck_rating_close_time']) == false) {
            $end_date = '9999-12-31 00:00:00';
        } else {
            $end_date = $_POST['rating_close_time'];
        }
        $access_type = array();
        if (isset($_POST['ck_peer_assessment']) === false || $_POST['peer_percent'] === '0') {
            $peer_percent = 0;
            $peer_times = 3;
        } else {
            $peer_percent = (int)$_POST['peer_percent'];
            $access_type[] = 'peer';
            $peer_times = (int)$_POST['peer_times'];
        }
        if (isset($_POST['ck_self_assessment']) === false || $_POST['self_percent'] === '0') {
            $self_percent = 0;
        } else {
            $self_percent = (int)$_POST['self_percent'];
            $access_type[] = 'self';
        }
        $teacher_percent = 100 - $peer_percent - $self_percent;
        if ($teacher_percent >= 0.01) {
            $access_type[] = 'teacher';
        }
        if (count($access_type) >= 1) {
            $access_type = implode(',', $access_type);
        } else {
            $access_type = '';// 不應該發生
        }
        // 成績結果
        if (isset($_POST['ck_score_begin_time']) == false) {
            $publish_begin = '0000-00-00 00:00:00';
        } else {
            $publish_begin = $_POST['score_begin_time'];
        }
        if (isset($_POST['ck_score_close_time']) == false) {
            $publish_end = '9999-12-31 00:00:00';
        } else {
            $publish_end = $_POST['score_close_time'];
        }
        if ($_POST['rdoScorePublish'] === '1') {
            $publish_begin = '0000-00-00 00:00:00';
            $publish_end = '0000-00-00 00:00:00';
        }
        // 優先權
        if ($peer_percent === 0 || $self_percent === 0) {
            $assess_relation = '0';
        } else {
            $assess_relation = $_POST['assess_relation'];
        }
        $now = date('Y-m-d H:i:s');

    if (isset($_POST['exam_id'])){    // 修改試卷
        $type = array('homework' => 1700200200, 'exam' => 1600200200, 'questionnaire' => 1800200200, 'peer' => 1710200200);
        if (!aclVerifyPermission($type[QTI_which], 16)) aclPermissionDeny();
        if (!verify()) die('Fake data !');

        $old_title = $sysConn->GetOne('select title from WM_qti_' . QTI_which . '_test where exam_id=' . $_POST['exam_id']);

            dbSet('WM_qti_' . QTI_which . '_test',
                     "title        ='$title',
                      type         ={$_POST['ex_type']},
                      modifiable   ='{$_POST['modifiable']}',
                      publish       ='{$_POST['rdoPublish']}',
                      begin_time   ='$begin_time',
                      close_time   ='$close_time',
                      count_type   ='{$_POST['count_type']}',
                      percent      ='{$_POST['percent']}',
                      do_times     ='{$_POST['do_times']}',
                      do_interval  ='{$_POST['do_interval']}',
                      item_per_page='{$_POST['item_per_page']}',
                      ctrl_paging  ='{$_POST['ctrl_paging']}',
                      ctrl_window  ='{$_POST['ctrl_window']}',
                      ctrl_timeout ='{$_POST['ctrl_timeout']}',
                      announce_type='{$_POST['announce_type']}',
                      announce_time='$announce_time',
                      item_cramble ='{$_POST['item_cramble']}',
                      random_pick  ={$_POST['random_pick']},
                      setting      ='{$setting}',
                      notice       ='{$_POST['notice_1']}',
                      assess       ='{$_POST['rating_criteria_1']}',
                      start_date   ='{$start_date}',
                      end_date     ='{$end_date}',
                      assess_type  ='{$access_type}',
                      peer_percent ='{$peer_percent}',
                      self_percent ='{$self_percent}',
                      teacher_percent ='{$teacher_percent}',
                      peer_times   ='{$peer_times}',
                      assess_way   ='{$_POST['assess_way']}',
                      assess_relation ='{$assess_relation}',
                      content      ='{$_POST['content']}',
                      operator     ='{$sysSession->username}',
                      upd_time     ='{$now}'",
                     'exam_id      =' . $_POST['exam_id']
                    );

        $isModified = $sysConn->Affected_Rows();
        $instance = $_POST['exam_id'];

        // 代換學習路徑節點的 <title> begin
        if (($new_title = stripslashes($title)) != $old_title)
        {
            $manifest = new SyncImsmanifestTitle(); // 本類別定義於 db_initialize.php
            $manifest->replaceTitleForImsmanifest((QTI_which == 'exam' ? 3 : (QTI_which == 'homework' ? 2 : 4 )),
                                                  $instance,
                                                  $manifest->convToNodeTitle($new_title));
            $manifest->restoreImsmanifest();
        }
        // 代換學習路徑節點的 <title> end


        // 判斷資料是否存在
        $grade_id = $sysConn->GetOne('select grade_id from WM_grade_list where source=4 and course_id=' . $course_id . ' and property=' . $_POST['exam_id']);
        if ($grade_id === false) {
            // 新增成績管理（避免無資料）
            dbNew('WM_grade_list',
                     "course_id,
                      source,
                      property",
                     "$course_id,
                      '4',
                      '{$_POST['exam_id']}'"
                     );
        }
            
        // 更新成績管理
            dbSet('WM_grade_list',
                     "title         ='$title',
                      source        ='4',
                      percent        ='{$_POST['percent']}',
                      publish_begin ='$publish_begin',
                      publish_end   ='$publish_end'",
                     'course_id =' . $course_id . ' and property = ' . $_POST['exam_id'] . ' and source = 4'
                    );
            $sysConn->Affected_Rows();
    }
    else{
            dbNew('WM_qti_' . QTI_which . '_test',// 新增試卷
                     "course_id,
                      title,
                      type,
                      modifiable,
                      publish,
                      begin_time,
                      close_time,
                      count_type,
                      percent,
                      do_times,
                      do_interval,
                      item_per_page,
                      ctrl_paging,
                      ctrl_window,
                      ctrl_timeout,
                      announce_type,
                      announce_time,
                      item_cramble,
                      random_pick,
                      setting,
                      notice,
                      assess,
                      start_date,
                      end_date,
                      assess_type,
                      peer_percent,
                      self_percent,
                      teacher_percent,
                      peer_times,
                      assess_way,
                      assess_relation,
                      content,
                      creator,
                      create_time",
                     "$course_id,
                      '$title',
                      {$_POST['ex_type']},
                      '{$_POST['modifiable']}',
                      '{$_POST['rdoPublish']}',
                      '$begin_time',
                      '$close_time',
                      '{$_POST['count_type']}',
                      '{$_POST['percent']}',
                      '{$_POST['do_times']}',
                      '{$_POST['do_interval']}',
                      '{$_POST['item_per_page']}',
                      '{$_POST['ctrl_paging']}',
                      '{$_POST['ctrl_window']}',
                      '{$_POST['ctrl_timeout']}',
                      '{$_POST['announce_type']}',
                      '$announce_time',
                      '{$_POST['item_cramble']}',
                      {$_POST['random_pick']},
                      '{$setting}',
                      '{$_POST['notice_1']}',
                      '{$_POST['rating_criteria_1']}',
                      '{$start_date}',
                      '{$end_date}',
                      '{$access_type}',
                      '{$peer_percent}',
                      '{$self_percent}',
                      '{$teacher_percent}',
                      '{$peer_times}',
                      '{$_POST['assess_way']}',
                      '{$assess_relation}',
                      '{$_POST['content']}',
                      '{$sysSession->username}',
                      '{$now}'"
                     );
        $isModified = $sysConn->Affected_Rows();
        $type = array('homework' => 1700200100, 'exam' => 1600200100, 'questionnaire' => 1800200100, 'peer' => 2700200100);
        $instance = $sysConn->Insert_ID();

        // 更新成績管理
        dbNew('WM_grade_list',
                 "course_id,
                  title,
                  source,
                  property,
                  percent,
                  publish_begin,
                  publish_end",
                 "$course_id,
                  '$title',
                  '4',
                  '{$instance}',
                  '{$_POST['percent']}',
                  '$publish_begin',
                  '$publish_end'"
                 );
        $sysConn->Affected_Rows();
    }

    if ($sysConn->ErrorNo()) {
       $errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
       wmSysLog($sysSession->cur_func, $course_id , $instance , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
       die($errMsg);
    }
    else
    {
        if (isset($_POST['exam_id']))
        {
            wmSysLog($sysSession->cur_func, $course_id , $instance , 0, 'auto', $_SERVER['PHP_SELF'], 'Modify ' . QTI_which);

            // 與成績系統同步比例及公布日期 start
            if ($isModified)
            {
               switch($_POST['announce_type'])
               {
                   case 'never':       $d = '9999-12-31 00:00:00';
                       break;
                   case 'now':         $d = $begin_time;
                       break;
                   case 'close_time':  $d = $close_time;
                       break;
                   case 'user_define': $d = $announce_time;
                       break;
               }
               $grade_types = array('homework' => 1, 'exam' => 2, 'questionnaire' => 3, 'peer' => 4);
               dbSet('WM_grade_list',
                     "percent={$_POST['percent']}",
                     'source=' . $grade_types[QTI_which] . ' and property=' . $_POST['exam_id']);
            }
            // 與成績系統同步比例及公布日期 end

            // 與行事曆同步 start
            if (QTI_which == 'exam' && $_POST['rdoPublish'] == '2')
            {
                $save_tpl = <<< EOB
<manifest>
    <ticket />
    <action>save</action>
    <calEnv>teach</calEnv>
    <year>%u</year>
    <month>%u</month>
    <day>%u</day>
    <idx>%s</idx>
    <time_begin>%u:%u</time_begin>
    <time_end>%u:%u</time_end>
    <repeat>none</repeat>
    <repeat_endY />
    <repeat_endM />
    <repeat_endD />
    <subject>%s</subject>
    <alert_type>login,email</alert_type>
    <alert_before>%u</alert_before>
    <content type="text">%s</content>
</manifest>
EOB;
                $idx = $sysConn->GetCol('select calendar_id from WM_calendar_exam where exam_id=' . $instance . ' order by calendar_id');
                $date1 = getdate(strtotime($begin_time));
                $date2 = getdate(strtotime($close_time));
                if(strncmp($begin_time, $close_time, 10) == 0) // 起始同一天
                {
                    if (hasSetDate($begin_time))
                    {
                        $save_xml = sprintf($save_tpl, $date1['year'], $date1['mon']-1, $date1['mday'], $idx[0],
                                            $date1['hours'], $date1['minutes'], $date2['hours'], $date2['minutes'],
                                            $MSG['hold_exam_today'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['right_quote'][$sysSession->lang], 4,
                                            $MSG['hold_something_today'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['attention_please1'][$sysSession->lang]
                                           );
                        $ret = saveMemo(domxml_open_mem($save_xml), 'course');
                        if (empty($idx[0]) && preg_match('/<manifest id="(\d+)"/', $ret, $regs))
                            dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$instance}");

                    }
                    elseif ($idx[0]) // 原本有日期改為不限，要刪掉)
                    {
                        dbDel('WM_calendar_exam', 'calendar_id=' . $idx[0]);
                        dbDel('WM_calendar', 'idx=' . $idx[0]);
                    }


                    if ($idx[1]) // 不同天改為同一天 (就會多一筆設定出來，要刪掉)
                    {
                        dbDel('WM_calendar_exam', 'calendar_id=' . $idx[1]);
                        dbDel('WM_calendar', 'idx=' . $idx[1]);
                    }
                }
                else
                {
                    if (hasSetDate($begin_time))
                    {
                        $save_xml = sprintf($save_tpl, $date1['year'], $date1['mon']-1, $date1['mday'], $idx[0],
                                            $date1['hours'], $date1['minutes'], 23, 55,
                                            $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['begin_to_exam'][$sysSession->lang], 4,
                                            $MSG['hold_something_today'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['attention_please1'][$sysSession->lang]
                                           );
                        $ret = saveMemo(domxml_open_mem($save_xml), 'course');
                        if (empty($idx[0]) && preg_match('/<manifest id="(\d+)"/', $ret, $regs))
                            dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$instance}");

                        if ($idx[1] && !hasSetDate($close_time)) // 有第二筆行事曆，卻沒設結束日期
                        {
                            dbDel('WM_calendar_exam', 'calendar_id=' . $idx[1]);
                            dbDel('WM_calendar', 'idx=' . $idx[1]);
                        }
                    }

                    if (hasSetDate($close_time))
                    {
                        $save_xml = sprintf($save_tpl, $date2['year'], $date2['mon']-1, $date2['mday'], $idx[1],
                                            0, 0, $date2['hours'], $date2['minutes'],
                                            $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['stop_to_exam'][$sysSession->lang], 1,
                                            $MSG['today_is'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['attention_please2'][$sysSession->lang]
                                           );
                        $ret = saveMemo(domxml_open_mem($save_xml), 'course');
                        if (empty($idx[1]) && preg_match('/<manifest id="(\d+)"/', $ret, $regs))
                            dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$instance}");

                        if ($idx[0] && !hasSetDate($begin_time)) // 有第一筆行事曆，卻沒設開始日期
                        {
                            dbDel('WM_calendar_exam', 'calendar_id=' . $idx[0]);
                            dbDel('WM_calendar', 'idx=' . $idx[0]);
                        }
                    }
                }
                unset($date1, $date2, $save_tpl, $save_xml, $ret, $regs, $idx);
            }
            elseif (QTI_which == 'exam' && $_POST['rdoPublish'] == '1')
            {
                $calendar_ids = $sysConn->GetCol("select calendar_id from WM_calendar_exam where exam_id={$_POST['exam_id']} limit 2");
                if (is_array($calendar_ids) && count($calendar_ids))
                    dbDel('WM_calendar', 'idx in (' . implode(',', $calendar_ids) . ')');
                dbDel('WM_calendar_exam', "exam_id={$_POST['exam_id']}");
            }
            // 與行事曆同步 end
         }
         else
         {
             wmSysLog($sysSession->cur_func, $course_id , $instance , 0, 'auto', $_SERVER['PHP_SELF'], 'New ' . QTI_which);

            // 與行事曆同步 start
            if (QTI_which == 'exam' && $_POST['rdoPublish'] == '2')
            {
                $save_tpl = <<< EOB
<manifest>
    <ticket />
    <action>save</action>
    <calEnv>teach</calEnv>
    <year>%u</year>
    <month>%u</month>
    <day>%u</day>
    <idx />
    <time_begin>%u:%u</time_begin>
    <time_end>%u:%u</time_end>
    <repeat>none</repeat>
    <repeat_endY />
    <repeat_endM />
    <repeat_endD />
    <subject>%s</subject>
    <alert_type>login,email</alert_type>
    <alert_before>%u</alert_before>
    <content type="text">%s</content>
</manifest>
EOB;
                $date1 = getdate(strtotime($begin_time));
                $date2 = getdate(strtotime($close_time));
                if(strncmp($begin_time, $close_time, 10) == 0) // 起始同一天
                {
                    if (hasSetDate($begin_time))
                    {
                        $save_xml = sprintf($save_tpl, $date1['year'], $date1['mon']-1, $date1['mday'],
                                            $date1['hours'], $date1['minutes'], $date2['hours'], $date2['minutes'],
                                            $MSG['hold_exam_today'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['right_quote'][$sysSession->lang], 4,
                                            $MSG['hold_something_today'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['attention_please1'][$sysSession->lang]
                                           );
                        $ret = saveMemo(domxml_open_mem($save_xml), 'course');
                        if (preg_match('/<manifest id="(\d+)"/', $ret, $regs))
                            dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$instance}");
                    }
                }
                else
                {
                    if (hasSetDate($begin_time))
                    {
                        $save_xml = sprintf($save_tpl, $date1['year'], $date1['mon']-1, $date1['mday'],
                                            $date1['hours'], $date1['minutes'], 23, 55,
                                            $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['begin_to_exam'][$sysSession->lang], 4,
                                            $MSG['hold_something_today'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['attention_please1'][$sysSession->lang]
                                           );
                        $ret = saveMemo(domxml_open_mem($save_xml), 'course');
                        if (preg_match('/<manifest id="(\d+)"/', $ret, $regs))
                            dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$instance}");
                    }

                    if (hasSetDate($close_time))
                    {
                        $save_xml = sprintf($save_tpl, $date2['year'], $date2['mon']-1, $date2['mday'],
                                            0, 0, $date2['hours'], $date2['minutes'],
                                            $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['stop_to_exam'][$sysSession->lang], 1,
                                            $MSG['today_is'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['attention_please2'][$sysSession->lang]
                                           );
                        $ret = saveMemo(domxml_open_mem($save_xml), 'course');
                        if (preg_match('/<manifest id="(\d+)"/', $ret, $regs))
                            dbNew('WM_calendar_exam', 'calendar_id,exam_id', "{$regs[1]},{$instance}");
                    }
                }
                unset($date1, $date2, $save_tpl, $save_xml, $ret, $regs);
            }
            // 與行事曆同步 end
         }
    }


    // 處理 ACL

    // 如果是開放型問卷，則新增的時候，要順便加一個 ACL
    if ($_POST['forGuest'])
    {
        $noGuestAcl = true;
        if ($_POST['exam_id'])
        {
            $re = dbGetOne('WM_acl_list AS L, WM_acl_member AS M',
                           'count(*)',
                           "L.function_id=1800300200 AND L.unit_id={$course_id} AND L.instance={$_POST['exam_id']} AND L.acl_id=M.acl_id AND M.member='guest'");
            $noGuestAcl = !$re; // 如果已經有 ACL 了就不用再加
        }

        if ($noGuestAcl)
        {
            $t = array('Big5'        => 'for Guest',
                       'GB2312'      => 'for Guest',
                       'en'          => 'for Guest',
                       'EUC-JP'      => 'for Guest',
                       'user_define' => 'for Guest');
            $titles = serialize(array_reverse($t));
            dbNew('WM_acl_list', 'permission,caption,function_id,unit_id,instance',
                  sprintf("'enable','%s',1800300200,%u,%u",
                            addslashes($titles),
                            $course_id,
                            $instance
                         )
                 );
            if ($sysConn->ErrorNo() === 0){
                $new_id = $sysConn->Insert_ID();
                dbNew('WM_acl_member', 'acl_id,member', $new_id . ',"guest"');
            }
        }
    }
    else
    {
        // 計算有無修改新增或刪除 acl
        $acl_update_row = 0;

        foreach(explode(chr(12), get_magic_quotes_gpc() ? stripslashes($_POST['acl_lists']) : $_POST['acl_lists']) as $k => $acl_slice){
            $acl_lists = explode("\n", $acl_slice);

            // 取出 user 送過來的 acl
            $cur_lists = array();
            $new_lists = array();
            foreach($acl_lists as $item){
                $x = explode(chr(8), $item, 2);
                if (preg_match('/^[0-9]+$/', $x[0])) $cur_lists[] = intval($x[0]);
                elseif($x[0] == '*new*') $new_lists[] = $x[1];
            }
            // 取出資料庫的舊 acl
            $old_lists = aclGetAclIdByInstance($exam_perm[$k][QTI_which], $course_id, $instance);

            // 處理要刪掉的 ACL
            $will_rm = implode(',', array_diff($old_lists,$cur_lists));

            if ($will_rm != ''){
                dbDel('WM_acl_member', sprintf('acl_id in (%s)', $will_rm));
                dbDel('WM_acl_list', sprintf('acl_id in (%s)', $will_rm));

                if ($sysConn->Affected_Rows() > 0){
                    ++$acl_update_row;
                }
            }

            // 處理要新增的 ACL
            foreach($new_lists as $item){
                $elements = explode(chr(8), $item);
                $t = array();
                list($t['Big5'], $t['GB2312'], $t['en'], $t['EUC-JP'], $t['user_define']) = explode(chr(9), (get_magic_quotes_gpc() ? stripslashes($elements[0]) : $elements[0]));
                $titles = serialize(array_reverse($t));
                dbNew('WM_acl_list', 'permission,caption,function_id,unit_id,instance',
                      sprintf("%d,'%s',%d,%d,%d",
                                $elements[1],
                                addslashes($titles),
                                $exam_perm[$k][QTI_which],
                                $course_id,
                                $instance
                             )
                     );
                if ($sysConn->ErrorNo() === 0){
                    $new_id = $sysConn->Insert_ID();
                    $users = preg_split('/\s+/', $elements[3], -1, PREG_SPLIT_NO_EMPTY);
                    foreach(explode(',', aclBitmap2Roles($elements[2])) as $role) if ($role) $users[] = '#' . $role;
                    foreach($users as $user) dbNew('WM_acl_member', 'acl_id,member', $new_id . ',"' . $user . '"');
                    // 若沒有對象，預設為本課程所有正式生
                    if(!count($users)) dbDel('WM_acl_list', "acl_id={$new_id}");;

                    if ($sysConn->Affected_Rows() > 0){
                        ++$acl_update_row;
                    }
                }
                else {
                    $errMsg = sprintf("Creating ACL Error: No=%d, Msg=%s", $sysConn->ErrorNo(), $sysConn->ErrorMsg());
                    wmSysLog($sysSession->cur_func, $course_id , $instance , 2, 'auto', $_SERVER['PHP_SELF'], $errMsg);
                    die($errMsg);
                }
            }

            // 修改仍存在的 ACL
            $still_works_list = array_intersect($old_lists,$cur_lists);
            if ($still_works_list){
                foreach($acl_lists as $item)
                {
                    $x = explode(chr(8), $item, 2);
                    if (in_array($x[0], $still_works_list))
                    {
                        $elements = explode(chr(8), $x[1]);
                        $t = array();
                        list($t['Big5'], $t['GB2312'], $t['en'], $t['EUC-JP'], $t['user_define']) = explode(chr(9), (get_magic_quotes_gpc() ? stripslashes($elements[0]) : $elements[0]));
                        $titles = serialize(array_reverse($t));
                        dbSet('WM_acl_list', sprintf("permission=%d,caption='%s'", $elements[1], addslashes($titles)), "acl_id={$x[0]}");

                        if ($sysConn->ErrorNo() == 0){
                            dbDel('WM_acl_member', "acl_id={$x[0]}");
                            $users = preg_split('/\s+/', $elements[3], -1, PREG_SPLIT_NO_EMPTY);
                            foreach(explode(',', aclBitmap2Roles($elements[2])) as $role) if ($role) $users[] = '#' . $role;
                            foreach($users as $user) dbNew('WM_acl_member', 'acl_id,member', $x[0] . ',"' . $user . '"');
                            // 若沒有對象，預設為本課程所有正式生
                            if(!count($users)) dbDel('WM_acl_list', "acl_id={$x[0]}");;

                            if ($sysConn->Affected_Rows() > 0){
                                ++$acl_update_row;
                            }
                        }
                        else
                            die(sprintf('ERROR: %u: %s', $sysConn->ErrorNo(), $sysConn->ErrorMsg()));
                    }
                }
            }
            unset($cur_lists, $new_lists);
        }

        if ($acl_update_row > 0){
            // Flush (delete) any cached recordsets for the SQL statement
            $sysConn->CacheFlush();
        }
    }

    header('Location: exam_maintain.php');
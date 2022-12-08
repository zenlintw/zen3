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
    require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
        
    // 日期是否設定
    function hasSetDate($date)
    {
        return ($date != '' &&
                $date != '0000-00-00 00:00:00' &&
                $date != '9999-12-31 00:00:00'
               ) ? true : false;
    }

    //ACL begin
    if (QTI_which == 'exam') {
        include_once(sysDocumentRoot . '/lib/lib_calendar.php');
        include_once(sysDocumentRoot . '/lang/exam_teach.php');
        $sysSession->cur_func = isset($_POST['exam_id']) ? '1600200200' : '1600200100';
    }
    else if (QTI_which == 'homework') {
        $sysSession->cur_func = isset($_POST['exam_id']) ? '1700200200' : '1700200100';
    }
    else if (QTI_which == 'questionnaire') {
        $sysSession->cur_func = isset($_POST['exam_id']) ? '1800200200' : '1800200100';
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

    if ($_POST['rdoPublish'] == 1) {    // 不發布
		// 愛上互動的話將開始時間設定為現在
		if (intval($_POST['ex_type']) === 5) {
			$begin_time = date("Y-m-d H:i:s");
			$close_time = '9999-12-31 00:00:00';
		} else {
			$begin_time = '0000-00-00 00:00:00';
			$close_time = '9999-12-31 00:00:00';
		}
            $delay_time = 'NULL';
    }
    else {
        $begin_time = (isset($_POST['ck_begin_time'])) ? $_POST['begin_time'].':00' : '0000-00-00 00:00:00';
        $close_time = (isset($_POST['ck_close_time'])) ? $_POST['close_time'].':00' : '9999-12-31 00:00:00';
        $delay_time = (isset($_POST['ck_delay_time'])) ? $_POST['delay_time'].':00' : 'NULL';
    }
    $announce_time = ($_POST['announce_type'] == 'user_define') ? $_POST['announce_time'].':00' : 'NULL';


    foreach(array('Big5','GB2312','en','EUC-JP','user_define') as $charset)
       $_POST['title'][$charset] = stripslashes($_POST['title'][$charset]);

    $title = is_array($_POST['title']) ? addslashes(serialize($_POST['title'])) : addslashes(serialize($_POST['title']));
    
    $_POST['title'] = co_lang_default($_POST['title']);

    $exam_perm = array(array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200),
                       array('homework' => 1700300100, 'exam' => 1600300100, 'questionnaire' => 0));

    // 資料防駭
    foreach(array('do_times', 'do_interval', 'item_per_page', 'random_pick') as $i) $_POST[$i] = intval($_POST[$i]);
    if (!in_array($_POST['qti_support_app'], array('N', 'Y')))                                  $_POST['qti_support_app'] = 'N';
    if (!in_array($_POST['ex_type']   , array(1, 2, 3, 4, 5)))                                  $_POST['ex_type']    = 1;
    if (!in_array($_POST['modifiable']   , array('N', 'Y')))                                    $_POST['modifiable']    = 'N';
    if (!in_array($_POST['publish']      , array('prepare','action','close')))                  $_POST['publish']       = 'prepare';
    if (!in_array($_POST['count_type']   , array('none','first','last','max','min','average'))) $_POST['count_type']    = 'first';
    if (!in_array($_POST['ctrl_paging']  , array('none','can_return','lock')))                  $_POST['ctrl_paging']   = 'none';
    if (!in_array($_POST['ctrl_window']  , array('none','lock','lock2')))                               $_POST['ctrl_window']   = 'none';
    if (!in_array($_POST['ctrl_timeout'] , array('none','mark','auto_submit')))                 $_POST['ctrl_timeout']  = 'none';
    if (!in_array($_POST['announce_type'], array('never','now','close_time', 'delay_time','user_define')))    $_POST['announce_type'] = 'never';
    $_POST['item_cramble'] = implode(',', array_intersect(array('enable','choice','item','section','random_pick'), explode(',', $_POST['item_cramble'])));
    $_POST['percent'] = floatval($_POST['percent']);
    if (isset($_POST['threshold_score']) && !ereg('^([0-9]+(\.[0-9])?)?$', $_POST['threshold_score'])) $_POST['threshold_score'] = '';
    $setting = preg_replace('/,$/', '', ($_POST['setting']['upload']    ? 'upload,'    : '') .
                        ($_POST['setting']['anonymity'] ? 'anonymity,' : '') .
                        ($_POST['ck_attachment_required'] ? 'required,' : ''));
        
    // 資料防駭 over
    $_POST['content']= preg_replace('/[\r\n\t]+/', '', $_POST['content']);

    if (QTI_which == 'exam')
    {
        if (strpos($_POST['content'], 'threshold_score=') !== FALSE)
            $_POST['content'] = preg_replace(array('/\bthreshold_score=\\\\"[^"]*\\\\"/', '/\bthreshold_score="[^"]*"/'),
                                             array('threshold_score=\\"' . $_POST['threshold_score'] . '\\"', 'threshold_score="' . $_POST['threshold_score'] . '"'),
                                             $_POST['content']
                                            );
        else
            if (($i=strpos($_POST['content'], '>')) !== FALSE)
            {
                if (substr($_POST['content'], $i-1, 1) == '/') $i--;    // 如果 XML 只有 < ... /> 的話
            
                $_POST['content'] = substr($_POST['content'], 0, $i) .
                                    (' threshold_score=\\"' . $_POST['threshold_score'] . '\\"') .
                                    substr($_POST['content'], $i);
            }

        if (strpos($_POST['content'], 'score_publish_type=') !== FALSE)
            $_POST['content'] = preg_replace(array('/\bscore_publish_type=\\\\"[^"]*\\\\"/', '/\bscore_publish_type="[^"]*"/'),
                                             array('score_publish_type=\\"' . $_POST['score_publish_type'] . '\\"', 'score_publish_type="' . $_POST['score_publish_type'] . '"'),
                                             $_POST['content']
                                            );
        else
            if (($i=strpos($_POST['content'], '>')) !== FALSE)
            {
                if (substr($_POST['content'], $i-1, 1) == '/') $i--;    // 如果 XML 只有 < ... /> 的話
            
                $_POST['content'] = substr($_POST['content'], 0, $i) .
                                    (' score_publish_type=\\"' . $_POST['score_publish_type'] . '\\"') .
                                    substr($_POST['content'], $i);
            }
    }
    
    if ($xmlstr = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', stripslashes($_POST['content']))))
    {
        if (!$dom = domxml_open_mem($xmlstr)) die('XML containing incorrect char(s).<textarea style="display: hidden">' . $xmlstr . '</textarea>');
        $_POST['content'] = addslashes($xmlstr);
    }
        
    // 移除沒有大題文字的TAG，避免IE排列與配分時無法顯示
    $_POST['content'] = str_replace('<presentation_material><flow_mat><material><mattext> </mattext></material></flow_mat></presentation_material></section>', '</section>', $_POST['content']);

    if (isset($_POST['exam_id'])) {    // 修改試卷
        $type = array('homework' => 1700200200, 'exam' => 1600200200, 'questionnaire' => 1800200200);
                if (!aclVerifyPermission($type[QTI_which], 16)) {
                    aclPermissionDeny();
                }
        if (!verify()) die('Fake data !');
        
        // 取原測驗名稱與記方方式
        $rsQtiTest = $sysConn->GetRow('select title, count_type from WM_qti_' . QTI_which . '_test where exam_id=' . $_POST['exam_id']);
        $old_title = $rsQtiTest['title'];
        $oldCountType = $rsQtiTest['count_type'];

        // 補繳時間
        $fieldDelayTimes = '';
        if (QTI_which === 'homework') {
            $fieldDelayTimes = sprintf('delay_time = "%s", ', $delay_time);
        }
        
        dbSet('WM_qti_' . QTI_which . '_test',
                 "title        ='$title',
                  type         ={$_POST['ex_type']},
                  modifiable   ='{$_POST['modifiable']}',
                  publish       ='{$_POST['rdoPublish']}',
                  begin_time   ='$begin_time',
                  close_time   ='$close_time',
                  " . $fieldDelayTimes . "
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
                  notice       ='{$_POST['notice']}',
                  content      ='{$_POST['content']}'",
                 'exam_id=' . $_POST['exam_id']
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
    } else {
                // 補繳時間（限作業）
                $columnDelayTimes = '';
                $valueDelayTimes = '';
                if (QTI_which === 'homework') {
                    $columnDelayTimes = 'delay_time,';
                    $valueDelayTimes = sprintf('"%s", ', $delay_time);
                }
                
                // 新增試卷
                if (QTI_which === 'homework') {
                    $now = date('Y-m-d H:i:s');
                    dbNew('WM_qti_' . QTI_which . '_test', 
                                              "course_id,
                                              title,
                                              type,
                                              modifiable,
                                              publish,
                                              begin_time,
                                              close_time,
                                              " . $columnDelayTimes . "
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
                                              content,
                                              create_time", 
                                              "$course_id,    
                                              '$title',
                                              {$_POST['ex_type']},
                                              '{$_POST['modifiable']}',
                                              '{$_POST['rdoPublish']}',
                                              '$begin_time',
                                              '$close_time',
                                              " . $valueDelayTimes . "
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
                                              '{$_POST['notice']}',
                                              '{$_POST['content']}',
                                              '{$now}'"
                                              );
                } else {
                    dbNew('WM_qti_' . QTI_which . '_test', 
                                              "course_id,
                                              title,
                                              type,
                                              modifiable,
                                              publish,
                                              begin_time,
                                              close_time,
                                              " . $columnDelayTimes . "
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
                                              content", 
                                              "$course_id,
                                              '$title',
                                              {$_POST['ex_type']},
                                              '{$_POST['modifiable']}',
                                              '{$_POST['rdoPublish']}',
                                              '$begin_time',
                                              '$close_time',
                                              " . $valueDelayTimes . "
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
                                              '{$_POST['notice']}',
                                              '{$_POST['content']}'"
                                              );
                }
        $isModified = $sysConn->Affected_Rows();
        $type = array('homework' => 1700200100, 'exam' => 1600200100, 'questionnaire' => 1800200100);
        $instance = $sysConn->Insert_ID();
    }
    /* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
    if($instance < 100000000){
        $instance_auto = $instance + 100000000;
        dbSet('WM_qti_' . QTI_which . '_test',"exam_id = '{$instance_auto}'","exam_id = {$instance}");        
        $sysConn->Execute('ALTER TABLE WM_qti_' . QTI_which . '_test AUTO_INCREMENT ='.($instance_auto+1).';');
        $instance = $instance_auto;
    }
    /* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
    // 在有開啟行動測驗或問卷模組的情況下，判斷本測驗是否支援行動測驗或問卷 => 儲存 Begin
    if (QTI_which === 'exam' && sysEnableAppCourseExam == true) {
        $qtiWhich = QTI_which;
        list($appSupportCount) = dbGetStSr('APP_qti_support_app', 'count(*)', "exam_id = {$instance} AND type='{$qtiWhich}' AND course_id={$course_id}", ADODB_FETCH_NUM);
        if ($appSupportCount == 0) {
            dbNew('APP_qti_support_app', "exam_id, type, course_id, support", "{$instance}, '{$qtiWhich}', {$course_id},'{$_POST['qti_support_app']}'");
        } else {
            dbSet('APP_qti_support_app', "support='{$_POST['qti_support_app']}'", "exam_id = {$instance} AND type='{$qtiWhich}' AND course_id={$course_id}");
        }
    }
    // 取得本測驗是否支援行動測驗 => 儲存 End

    if ($sysConn->ErrorNo()) {
       $errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
       wmSysLog($sysSession->cur_func, $course_id , $instance , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
       die($errMsg);
    } else {
        if (isset($_POST['exam_id']))
        {
            wmSysLog($sysSession->cur_func, $course_id , $instance , 0, 'auto', $_SERVER['PHP_SELF'], 'Modify ' . QTI_which);

            // 與成績系統同步比例及公布日期 start
            if ($isModified)
            {
                $grade_types = array('homework' => 1, 'exam' => 2, 'questionnaire' => 3);
                list($grade_pb) = dbGetStSr('WM_grade_list','publish_begin,publish_end','source=' . $grade_types[QTI_which] . ' and property=' . $_POST['exam_id']);
                switch($_POST['announce_type'])
                {
                    case 'never':
                        $d = '9999-12-31 00:00:00';
                        break;
                    case 'now':
                        // $d = $begin_time;
                        $d = '1970-01-01 00:00:00';
                        break;
                    case 'close_time':
                        $d = $close_time;
                        break;
                    case 'delay_time':
                        $d = $delay_time;
                        break;
                    case 'user_define':
                        $d = $announce_time;
                        break;
                }
                if($_POST['announce_type']!='never')
                {
                    // 若答案要公布，則『答案時間小於成績起始時間』或『成績原為不公布』，就要異動成績公布時間
                    $exam_pb_time = strtotime($d);
                    $grade_pb_time = strtotime($grade_pb);
                    if(($exam_pb_time<=$grade_pb_time) || ($grade_pb=='0000-00-00 00:00:00'))
                    {
                        dbSet('WM_grade_list',
                              "title = '{$title}',percent={$_POST['percent']}, publish_begin='{$d}' , publish_end='9999-12-31 00:00:00'",
                              'source=' . $grade_types[QTI_which] . ' and property=' . $_POST['exam_id']);
                    }else{
                        dbSet('WM_grade_list',
                              "title = '{$title}',percent={$_POST['percent']}",
                              'source=' . $grade_types[QTI_which] . ' and property=' . $_POST['exam_id']);
                    }
                }else{
                        dbSet('WM_grade_list',
                              "title = '{$title}',percent={$_POST['percent']}",
                              'source=' . $grade_types[QTI_which] . ' and property=' . $_POST['exam_id']);
                }
            }
            // 與成績系統同步比例及公布日期 end


         }
         else
         {
             wmSysLog($sysSession->cur_func, $course_id , $instance , 0, 'auto', $_SERVER['PHP_SELF'], 'New ' . QTI_which);
         }

        // 與行事曆同步 start
        //$sysConn->debug=true;
        $calendar_begin_type=QTI_which.'_begin';
        $calendar_end_type=QTI_which.'_end';
        $calendar_delay_type=QTI_which.'_delay';
        if ( $_POST['rdoPublish'] == '2') {
            
            // 開始時間的行事曆編號
            $begin_cal_idx = array();
            $rsBeginCalIdx = dbGetStMr('WM_calendar', 'idx', "relative_type='{$calendar_begin_type}' AND relative_id={$instance}");
            if ($rsBeginCalIdx) {
                while (!$rsBeginCalIdx->EOF) {
                    $begin_cal_idx[] = $rsBeginCalIdx->fields['idx'];
                    
                    $rsBeginCalIdx->MoveNext();
                }
            }   
            
            // 結束時間的行事曆編號
            $end_cal_idx = array();
            $rsEndCalIdx = dbGetStMr('WM_calendar', 'idx', "relative_type='{$calendar_end_type}' AND relative_id={$instance}");
            if ($rsEndCalIdx) {
                while (!$rsEndCalIdx->EOF) {
                    $end_cal_idx[] = $rsEndCalIdx->fields['idx'];
                    
                    $rsEndCalIdx->MoveNext();
                }
            }   
            
            // 補繳期限
            $delay_cal_idx = array();
            $rsDelayCalIdx = dbGetStMr('WM_calendar', 'idx', "relative_type='{$calendar_delay_type}' AND relative_id={$instance}");
            if ($rsDelayCalIdx) {
                while (!$rsDelayCalIdx->EOF) {
                    $delay_cal_idx[] = $rsDelayCalIdx->fields['idx'];
                    
                    $rsDelayCalIdx->MoveNext();
                }
            }  
            if ($topDir == 'academic') {
                $username=$sysSession->school_id;
                $type='school';
            } else {
                $username=$sysSession->course_id;
                $type='course';
            }
            $repeat = 'none';
            $repeat_begin='0000-00-00';
            $repeat_end='0000-00-00';
            if ($_POST['alert_check']=='1') {
                $arr_type = array();
                if ($_POST['alert_login']==1) $arr_type[] = 'login';
                if ($_POST['alert_email']==1) $arr_type[] = 'email';
                $alertType = implode(',',$arr_type);
                $alertBefore= $_POST['alert_before'];
            } else {
                $alertType="";
                $alertBefore= 0;
            }
            if ($_POST['alert_check1']=='1') {
                $arr_type1 = array();
                if ($_POST['alert_login1']==1) $arr_type1[] = 'login';
                if ($_POST['alert_email1']==1) $arr_type1[] = 'email';
                $alertType1 = implode(',',$arr_type1);
                $alertBefore1= intval($_POST['alert_before1']);
            } else {
                $alertType1="";
                $alertBefore1= 0;
            }
            $ishtml = "text";
            $date1 = getdate(strtotime($begin_time));
            $date2 = getdate(strtotime($close_time));
            $date3 = getdate(strtotime($delay_time));
            
            if(strncmp($begin_time, $close_time, 10) == 0) { // 起始同一天
                // 刪除相同屬性的資料
                if ( isset($_POST['exam_id']) && is_array($begin_cal_idx) && count($begin_cal_idx) >= 1) {
                    dbDel('WM_calendar', 'idx IN (' . implode(', ', $begin_cal_idx) . ')');
                }
                if ( isset($_POST['exam_id']) && is_array($end_cal_idx) && count($end_cal_idx) >= 1) {
                    dbDel('WM_calendar', 'idx IN (' . implode(', ', $end_cal_idx) . ')');
                }
                $memo_date=substr($begin_time,0,10);
                
                $timeBegin = "'".substr($begin_time,10)."'";
                $timeEnd   = "'".substr($close_time,10)."'";
                
                $subject = $MSG[QTI_which][$sysSession->lang].$MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['begin_to_'.QTI_which][$sysSession->lang];
                $content = $MSG[QTI_which.'_attention_please1'][$sysSession->lang];
                $fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
                    '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
                    '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
                $values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
                    ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                    ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_begin_type}','{$instance}'";
                dbNew('WM_calendar', $fields, $values);
                
 
                // 補繳期限
                if ( !hasSetDate($delay_time) && isset($_POST['exam_id']) && is_array($delay_cal_idx) && count($delay_cal_idx) >= 1) { // 關閉作答日期沒有限制時要刪除舊的行事曆
                    dbDel('WM_calendar', 'idx IN (' . implode(', ', $delay_cal_idx) . ')');
                }
                
                if ( hasSetDate($delay_time) && isset($_POST['ck_sync_delay_time']) )
                {
                    if( isset($_POST['exam_id']) && is_array($delay_cal_idx) && count($delay_cal_idx) >= 1) {
                        //刪除舊的行事曆
                        dbDel('WM_calendar', 'idx IN (' . implode(', ', $delay_cal_idx) . ')');
                    }
                    $memo_date=substr($delay_time,0,10);
                    
                    $timeBegin = 'NULL';
                    $timeEnd   = "'".substr($delay_time,10)."'";
                    
                    $subject = $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['delay_to_'.QTI_which][$sysSession->lang];
                    $content = $MSG[QTI_which.'_attention_please3'][$sysSession->lang];
                    $fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
                        '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
                        '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
                    $values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
                        ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                        ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_delay_type}','{$instance}'";
                    dbNew('WM_calendar', $fields, $values);
                }
            } else { // 起始不同天
                // 開始日期
                if ( !hasSetDate($begin_time) && isset($_POST['exam_id']) && is_array($begin_cal_idx) && count($begin_cal_idx) >= 1) { // 開放作答日期沒有限制時要刪除舊的行事曆
                    dbDel('WM_calendar', 'idx IN (' . implode(', ', $begin_cal_idx) . ')');
                }
                if ( hasSetDate($begin_time) && isset($_POST['ck_sync_begin_time'])) {
                    if( isset($_POST['exam_id']) && is_array($begin_cal_idx) && count($begin_cal_idx) >= 1 ) {
                        //刪除舊的行事曆
                        dbDel('WM_calendar', 'idx IN (' . implode(', ', $begin_cal_idx) . ')');
                    }
                    $memo_date=substr($begin_time,0,10);
                    
                    $timeBegin = "'".substr($begin_time,10)."'";                    
                    $timeEnd   = 'NULL';
                    
                    $subject = $MSG[QTI_which][$sysSession->lang].$MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['begin_to_'.QTI_which][$sysSession->lang];
                    $content = $MSG[QTI_which.'_attention_please1'][$sysSession->lang];
                    $fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
                        '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
                        '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
                    $values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
                        ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                        ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_begin_type}','{$instance}'";
                    dbNew('WM_calendar', $fields, $values);
                }
                
                // 結束日期
                if ( !hasSetDate($close_time) && isset($_POST['exam_id']) && is_array($end_cal_idx) && count($end_cal_idx) >= 1) { // 關閉作答日期沒有限制時要刪除舊的行事曆
                    dbDel('WM_calendar', 'idx IN (' . implode(', ', $end_cal_idx) . ')');
                }
                if ( hasSetDate($close_time) && isset($_POST['ck_sync_end_time']) )
                {
                    if( isset($_POST['exam_id']) && is_array($end_cal_idx) && count($end_cal_idx) >= 1) {
                        //刪除舊的行事曆
                        dbDel('WM_calendar', 'idx IN (' . implode(', ', $end_cal_idx) . ')');
                    }
                    $memo_date=substr($close_time,0,10);
                    
                    $timeBegin = 'NULL';
                    $timeEnd   = "'".substr($close_time,10)."'";
                    
                    $subject = $MSG[QTI_which][$sysSession->lang].$MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['stop_to_'.QTI_which][$sysSession->lang];
                    $content = $MSG[QTI_which.'_attention_please2'][$sysSession->lang];
                    $fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
                        '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
                        '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
                    $values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
                        ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                        ", '{$alertType1}', {$alertBefore1}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_end_type}','{$instance}'";
                    dbNew('WM_calendar', $fields, $values);
                }
                
                // 補繳期限
                if ( !hasSetDate($delay_time) && isset($_POST['exam_id']) && is_array($delay_cal_idx) && count($delay_cal_idx) >= 1) { // 關閉作答日期沒有限制時要刪除舊的行事曆
                    dbDel('WM_calendar', 'idx IN (' . implode(', ', $delay_cal_idx) . ')');
                }
                
                if ( hasSetDate($delay_time) && isset($_POST['ck_sync_delay_time']) )
                {
                    if( isset($_POST['exam_id']) && is_array($delay_cal_idx) && count($delay_cal_idx) >= 1) {
                        //刪除舊的行事曆
                        dbDel('WM_calendar', 'idx IN (' . implode(', ', $delay_cal_idx) . ')');
                    }
                    $memo_date=substr($delay_time,0,10);
                    
                    $timeBegin = 'NULL';
                    $timeEnd   = "'".substr($delay_time,10)."'";
                    
                    $subject = $MSG['left_quote'][$sysSession->lang] . htmlspecialchars($_POST['title'][$sysSession->lang]) . $MSG['delay_to_'.QTI_which][$sysSession->lang];
                    $content = $MSG[QTI_which.'_attention_please3'][$sysSession->lang];
                    $fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
                        '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
                        '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`,`relative_type`,`relative_id`';
                    $values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
                        ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                        ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL,'{$calendar_delay_type}','{$instance}'";
                    dbNew('WM_calendar', $fields, $values);
                }                
            }
        } elseif ( isset($_POST['exam_id']) && $_POST['rdoPublish'] == '1') {
            $calendar_ids = $sysConn->GetCol("select idx from WM_calendar where (relative_type='{$calendar_begin_type}' or relative_type='{$calendar_end_type}') and relative_id='{$instance}' limit 2");
            if (is_array($calendar_ids) && count($calendar_ids)) dbDel('WM_calendar', 'idx in (' . implode(',', $calendar_ids) . ')');
        }
        //$sysConn->debug=end;
        // 與行事曆同步 end
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
    } else {
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
//                        $titles = serialize(array_reverse($t));
                        $titles = serialize(($t));
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
        
    // 如果測驗計分方式有變動，重新計算學員成績
    if (isset($_POST['exam_id']) && QTI_which === 'exam') {
        $rsQTIResult = dbGetStMr('WM_qti_' . QTI_which . '_result', 'examinee', "exam_id = {$_POST['exam_id']}");
        if ($rsQTIResult) {
            
            $forceOverwrite = 'N';
            if ($oldCountType !== $_POST['count_type']) {
                // 強制重新計分
                $forceOverwrite = 'Y';
            }
            while (!$rsQTIResult->EOF) {
                $examinee = $rsQTIResult->fields['examinee'];
                reCalculateQTIGrade($examinee, $_POST['exam_id'], QTI_which, null, null, $forceOverwrite);

                $rsQTIResult->MoveNext();
            }
        }
    }

    header('Location: exam_maintain.php');
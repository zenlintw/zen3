<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/calendar.php');
    //$sysConn->debug=true;
    $result=new stdClass();
    $ticket = md5($sysSession->username . 'newCalendar' . $sysSession->ticket . $sysSession->school_id);
    if ($_POST['ticket'] != $ticket) {
        $result->error="Access Fail";
        die(json_encode($result));
    }
    $idx=$_POST['idx'];
    $action=$_POST['action'];
    if($action=="delete"){
        if(intval($idx) > 0){
            $affected_row = 0;
            // 檢驗是否為子節點 (取得父節點編號)(週期事件)
            $RS  = dbGetStSr('WM_calendar','`parent_idx`,`repeat`', "idx={$idx}", ADODB_FETCH_ASSOC);
            $parent_idx = $RS['parent_idx'];
            $repeat     = $RS['repeat'];	// 原先的週期設定
            if($repeat=='none') {			// 原先就不是週期事件
                dbDel('WM_calendar',"idx={$idx}");
                $affected_row = $sysConn->Affected_Rows();
            } else {
                if($parent_idx > 0) {		// 不是父節點
                    dbDel('WM_calendar', "parent_idx={$parent_idx}");
                    $affected_row = $sysConn->Affected_Rows();
                    dbDel('WM_calendar', "idx={$parent_idx}");
                    $affected_row += $sysConn->Affected_Rows();
                } else {			// 是父節點
                    dbDel('WM_calendar', "parent_idx={$idx}");
                    $affected_row = $sysConn->Affected_Rows();
                    dbDel('WM_calendar', "idx={$idx}");
                    $affected_row += $sysConn->Affected_Rows();
                }
            }
            if($affected_row==0){
                $result->error="msg_del_fail";

            }
        } else {
            $result->error="msg_del_fail";
        }
        die(json_encode($result));
    }
    $interface=$_POST['calendar_type'];
    switch($interface) {
        case 'school' :
            $username = $sysSession->school_id;
            $type     = 'school';
            break;
        case 'course'    :
            $username = $sysSession->course_id;
            $type     = 'course';
            break;
        case 'person':
            $username = $sysSession->username;
            $type     = 'person';
            break;
    }
    if ($_POST['time_choice']==0) {
        $timeBegin = 'NULL';
        $timeEnd   = 'NULL';
    } else {
        $timeBegin = "'" . $_POST['time_begin'].":00" . "'";
        $timeEnd   = "'" . $_POST['time_end'].":00" . "'";
    }
    $memo_date=$_POST['memo_date'];
    $repeat_begin=$_POST['repeat_choice']==1?$_POST['memo_date']:'0000-00-00';
    if ($_POST['alert_check']==1) {
    	$arr_type = array();
    	if ($_POST['alert_login']==1) $arr_type[] = 'login';
    	if ($_POST['alert_email']==1) $arr_type[] = 'email';
    	$alertType = implode(',',$arr_type);
    } else {
    	$alertType = "";
    }

    $alertBefore=$_POST['alert_before'];
    $repeat=$_POST['repeat_choice']==0?"none":$_POST['repeat_frequency'];
    $repeat_end = $_POST['repeat_choice']==0?'0000-00-00':$_POST['repeat_end'];
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);
    $ishtml = "text";
    if($action=="create"){
            $fields = '`username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
                '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
                '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`';
            $values = "'{$username}', '{$type}','{$memo_date}', {$timeBegin}, {$timeEnd}" .
                ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL";
            $msg .= "[$fields][$values]";
            dbNew('WM_calendar', $fields, $values);
            if( $sysConn->Affected_Rows()==0 ){
                $result->error="msg_add_fail";
                die(json_encode($result));
            }
            $idx    = $sysConn->Insert_ID();
    }else{
        // 檢查是否具有父節點
        list($parent_idx) = dbGetStSr('WM_calendar','parent_idx', "idx={$idx}", ADODB_FETCH_NUM);
        if($parent_idx) { // 找到真正節點 (以父節點取代本身)
            $idx = $parent_idx;
        }
        $relative_type = ($_POST['relative_type'] && $_POST['relative_type'] !== 'null') ? "'{$_POST['relative_type']}'" : "NULL";
        $relative_id   = ($_POST['relative_id'] && $_POST['relative_id'] !== 'null') ? "'{$_POST['relative_id']}'" : "NULL";
        $sqls = "`memo_date`='{$memo_date}', `time_begin`={$timeBegin}, `time_end`={$timeEnd}" .
            ", `repeat`='{$repeat}', `repeat_begin`='{$repeat_begin}', `repeat_end`='{$repeat_end}' " .
            ", `alert_type`='{$alertType}', `alert_before`='{$alertBefore}', `ishtml`='{$ishtml}'" .
            ", `subject`='{$subject}', `content`='{$content}', `upd_time`=Now(),`relative_type`={$relative_type},`relative_id`={$relative_id}";
        dbSet('WM_calendar', $sqls, "idx={$idx}");
        if( $sysConn->Affected_Rows()==0 ){
            $result->error="msg_update_fail";
            die(json_encode($result));
        }
        dbDel('WM_calendar',"parent_idx={$idx}");

    }
    // 週期紀錄
    if($idx>0){
        $parent_idx = $idx;
        $rpt_from = strtotime("{$repeat_begin}");
        $rpt_end  = strtotime($repeat_end);
        $fields   = '`parent_idx`, `username`, `type`, `memo_date`, `time_begin`, `time_end`, '.
            '`repeat`, `repeat_freq`, `repeat_begin`, `repeat_end`, ' .
            '`alert_type`, `alert_before`, `ishtml`, `subject`, `content`, `upd_time`';
        switch($repeat) {
            case 'day':
                $interval = 86400;
                for($thedate=$rpt_from+$interval;$thedate<=$rpt_end;$thedate+=$interval)
                {
                    $values = "{$parent_idx},'{$username}', '{$type}','". Date('Y-m-d',$thedate). "', {$timeBegin}, {$timeEnd}" .
                        ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                        ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL";
                    dbNew('WM_calendar', $fields, $values);
                }
                break;
            case 'week':
                $interval = 86400*7;
                for($thedate=$rpt_from+$interval;$thedate<=$rpt_end;$thedate+=$interval)
                {
                    $values = "{$parent_idx},'{$username}', '{$type}','". Date('Y-m-d',$thedate). "', {$timeBegin}, {$timeEnd}" .
                        ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                        ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL";
                    dbNew('WM_calendar', $fields, $values);
                }
                break;
            case 'month':
                $year = date('Y', strtotime($repeat_begin));
                $month = date('m', strtotime($repeat_begin));
                $day = date('d', strtotime($repeat_begin));
                for($y=$year,$m=$month+1;strtotime("$y-$m-$day")<=$rpt_end;$m++){
                    if($m>12){
                        $y++;
                        $m=1;
                    }
                    $values = "{$parent_idx},'{$username}', '{$type}','{$y}-{$m}-{$day}', {$timeBegin}, {$timeEnd}" .
                        ", '{$repeat}',0,'{$repeat_begin}','{$repeat_end}'" .
                        ", '{$alertType}', {$alertBefore}, '{$ishtml}', '{$subject}', '{$content}', NULL";
                    dbNew('WM_calendar', $fields, $values);
                }
                break;
        }
    }
    die(json_encode($result));
?>
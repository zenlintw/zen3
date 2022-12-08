<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lang/calendar.php');
    $calEnv=$_POST['env'];
    $calLmt=$_POST['calLmt'];
    switch ($calEnv){
        case 'academic':
            $ownerid = $sysSession->school_id;
            $editable = 'school';
            break;
        case 'teach':
            $ownerid = $sysSession->course_id;
            $editable = 'course';
            break;
        default:
            $ownerid = $sysSession->username;
            $calEnv	= 'learn';
            $editable = 'person';
            break;
    }
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }
    $smarty->assign('calLmt',$calLmt);
    $rawdata=$_POST['rawdata'];
    $rawdata['subject'] = stripslashes($rawdata['subject']);
    $rawdata['content'] = stripslashes($rawdata['content']); 
    $action=$_POST['act'];
    $smarty->assign('action',$action);
    if($action=="create"){
        $rawdata['school_id']=$sysSession->school_id;
        $rawdata['type']=$editable;
        $rawdata['memo_date']=$_POST['date'];
        $rawdata['repeat']="none";
        $rawdata['alert_type']="";
        $rawdata['alert_before']=1;
        $rawdata['time_begin']="";
        $rawdata['time_end']="";
    }
    if( $rawdata['repeat']!="none" && $rawdata['repeat_begin']!="0000-00-00" ) $rawdata['memo_date']=$rawdata['repeat_begin'];
    $smarty->assign('editable',$editable);
    $smarty->assign('rawdata',$rawdata);

    if (strpos($rawdata['relative_type'],'exam')!==false) {
    	$show_mseeege = str_replace('%test%',$MSG['exam'][$sysSession->lang],$MSG['tip'][$sysSession->lang]);
    } else if (strpos($rawdata['relative_type'],'homework')!==false) {
    	$show_mseeege = str_replace('%test%',$MSG['homework'][$sysSession->lang],$MSG['tip'][$sysSession->lang]);
    }

    $smarty->assign('show_mseeege',$show_mseeege);
    
    $calendarType=array("person"=>$MSG['flag_personal'][$sysSession->lang],"course"=>$MSG['flag_course'][$sysSession->lang],"school"=>$MSG['flag_school'][$sysSession->lang]);
    $smarty->assign('calendarType',$calendarType[$rawdata['type']]);
    $repeatTypes = array(
        "day"=>$MSG['title_repeat_day1'][$sysSession->lang],
        "week" =>$MSG['title_repeat_week1'][$sysSession->lang],
        "month"=>$MSG['title_repeat_month1'][$sysSession->lang]);
    $smarty->assign('repeatTypes',$repeatTypes);
    $beforeAry=array(
        "{$MSG['zero_day'][$sysSession->lang]}",
        "1{$MSG['msg_alert_before1'][$sysSession->lang]}",
        "2{$MSG['msg_alert_before1'][$sysSession->lang]}",
        "3{$MSG['msg_alert_before1'][$sysSession->lang]}",
        "4{$MSG['msg_alert_before1'][$sysSession->lang]}",
        "5{$MSG['msg_alert_before1'][$sysSession->lang]}",
        "6{$MSG['msg_alert_before1'][$sysSession->lang]}",
        "7{$MSG['msg_alert_before1'][$sysSession->lang]}");
    $smarty->assign('beforeAry',$beforeAry);
    if( $rawdata['alert_type']!="" ) $smarty->assign('eventBeforeString',$beforeAry[$rawdata['alert_before']]);
    
    $eventDateTime = '';
    if (($rawdata['time_begin']&&$rawdata['time_begin']!="null")&&($rawdata['time_end']&&$rawdata['time_end']!="null")) {
    	$eventDateTime = substr($rawdata['time_begin'],0,5).$MSG['to'][$sysSession->lang].substr($rawdata['time_end'],0,5);
    } else if (($rawdata['time_begin']&&$rawdata['time_begin']!="null") && $rawdata['time_end']=="null") {
    	$eventDateTime = substr($rawdata['time_begin'],0,5).$MSG['start'][$sysSession->lang];
    } else if ($rawdata['time_begin']=="null" && ($rawdata['time_end']&&$rawdata['time_end']!="null")) {
    	$eventDateTime = substr($rawdata['time_end'],0,5).$MSG['end'][$sysSession->lang];
    }    
    
    $smarty->assign('eventDateTime',$eventDateTime);
    $eventAllDay=$rawdata['time_begin']&&$rawdata['time_begin']!="null"?false:true;
    $smarty->assign('eventAllDay',$eventAllDay);
    $smarty->assign('editBtnText',$action=="create"?$MSG['btn_create'][$sysSession->lang]:$MSG['btn_save'][$sysSession->lang]);
    $ticket = md5($sysSession->username . 'newCalendar' . $sysSession->ticket . $sysSession->school_id);
    $smarty->assign('ticket',$ticket);
    
    // 是否為行動裝置
    $detect = new Mobile_Detect;
    $isMobile = $detect->isMobile() ? '1' : '0';
    $smarty->assign('isMobile', $isMobile);
    $smarty->display('calendar.tpl');
?>
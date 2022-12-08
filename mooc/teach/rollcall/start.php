<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lang/rollcall.php');

    if ($sysSession->username=='guest') {
        header('Location: /mooc/teach/rollcall/login.php?goto='.$_GET['goto']);
        die();
    }

    if (empty($_GET['goto'])){
        header('Location: /mooc/index.php');
        die();
    }


    $goto = sysNewDecode($_GET['goto'],'wm5IRS');
    if ($goto === false){
        header('LOCATION: /mooc/teach/rollcall/message.php?goto='.sysNewEncode(serialize(array('code'=>'1')), 'wm5IRS'));
        exit;
    }

    $gotoData = unserialize($goto);
    // 驗證參數值
    if (strlen($gotoData['course_id']) != 8)
    {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    $course_id = intval($gotoData['course_id']);
    $rollcall_id = intval($gotoData['rollcall_id']);

    if (!aclCheckRole($sysSession->username, $sysRoles['student'], $course_id)){
        if (aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] | $sysRoles['auditor'], $course_id)){
            header('LOCATION: /mooc/teach/rollcall/message.php?goto='.sysNewEncode(serialize(array('code'=>'3')), 'wm5IRS'));
        }else{
            header('LOCATION: /mooc/teach/rollcall/message.php?goto='.sysNewEncode(serialize(array('code'=>'2')), 'wm5IRS'));
        }
        exit;
    }

    // 取得點名資料
    $rollcallData = dbGetRow('APP_rollcall_base','*',sprintf("rid=%d",$rollcall_id), ADODB_FETCH_ASSOC);
    if (is_array($rollcallData)&&count($rollcallData)){
        if (time()>strtotime($rollcallData['end_time'])){
            header('LOCATION: /mooc/teach/rollcall/message.php?goto='.sysNewEncode(serialize(array('code'=>'4')), 'wm5IRS'));
            exit;
        }
    }else{
        header('LOCATION: /mooc/teach/rollcall/message.php?goto='.sysNewEncode(serialize(array('code'=>'1')), 'wm5IRS'));
        exit;
    }

    // 是否已經報到過
    $isExists = dbGetOne('APP_rollcall_record','count(*)',sprintf("rid=%d and username='%s' and rollcall_status>0",$rollcall_id, $sysSession->username));
    if ($isExists){
        //header('LOCATION: /mooc/teach/rollcall/message.php?goto='.sysNewEncode(serialize(array('code'=>'7')), 'wm5IRS'));
        //exit;
    } else {
	    $isExistsUser = dbGetOne('APP_rollcall_record','count(*)',sprintf("rid=%d and username='%s'",$rollcall_id, $sysSession->username));
	    if ($isExistsUser==0) {
	    	dbNew(
	            'APP_rollcall_record',
	            '`rid`, `username`, `rollcall_time`, `rollcall_status`',
	            sprintf("%d,'%s',NOW(),1", $rollcall_id, $sysSession->username)
		    );
	    }  else {
		    // 寫入報到的資料
		    dbset(
		        'APP_rollcall_record',
		        "rollcall_time=NOW(),rollcall_status=1",
		        sprintf("rid=%d and username='%s'",$rollcall_id,$sysSession->username)
		    );
	    }
    }
    
    $rollcall_time = dbGetOne('APP_rollcall_record','rollcall_time',sprintf("rid=%d and username='%s'",$rollcall_id, $sysSession->username));

    $smarty->assign('showMessage', str_replace('%TIME%',$rollcall_time,$MSG['msg_rollcall_success'][$sysSession->lang]));
    $smarty->display('teach/rollcall/message.tpl');
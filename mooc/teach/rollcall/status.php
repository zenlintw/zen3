<?php

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lang/rollcall.php');

    $rid = intval($_POST['rollcall_id']);
    $color = array('#815cb4', '#3aabdd', '#278f7f', '#ff7d13', '#e8483f', '#e3a729', '#42b6b5', '#81a41c', '#5e92fc', '#cf4aab');

    switch($_POST['action']) {
        case "getQrcodeUrl": //取得Qrcode
            $parseurl = parse_url($_SERVER['SCRIPT_URI'] );
            $host = $parseurl['scheme'] . '://' . $parseurl['host'];
            $goto = sysNewEncode(serialize(array('course_id'=>$sysSession->course_id, 'rollcall_id'=>$rid)), 'wm5IRS');
            $url = sprintf('%s/mooc/teach/rollcall/start.php?goto=%s',$host,$goto);
            $urlpath = getQrcodePath($url,'1','L', 10,470,470);
            $data['code'] = 1;
            $data['url'] = $urlpath;
            $msg = json_encode($data);
            break;

        case "get_status":  // 取得目前點名狀態
            
            $allstudents = dbGetCol('WM_term_major A left join APP_rollcall_record B ON A.username=B.username and B.rid='.$rid,'A.username',sprintf("A.course_id=%d and A.role&%d and B.username is NULL",$sysSession->course_id,$sysRoles['student']));

            for($i=0, $size=count($allstudents); $i<$size; $i++) {
                dbNew(
                    'APP_rollcall_record',
                    '`rid`, `username`, `rollcall_time`, `rollcall_status`',
                    sprintf("%d,'%s','0000-00-00 00:00:00',0", $rid, $allstudents[$i])
                );
            }

            $data = array();
            // 取得此次點名應到人數
            $data['major'] = dbGetOne('APP_rollcall_record','count(*)',sprintf("rid=%d",$rid));
            $data['submit'] = dbGetOne('APP_rollcall_record','count(*)',sprintf("rid=%d and rollcall_status>0",$rid));

            if($data['major'] > 0) {
                $data['submit_rate'] = round($data['submit']/$data['major']*100);
            } else {
                $data['submit_rate'] = '0';
            }

            $msg = json_encode($data);
            break;

        case "start_active":  //開始進行點名
        	$rid_notend = dbGetOne('APP_rollcall_base','rid',sprintf("course_id=%d and end_time='9999-12-31 00:00:00'",$sysSession->course_id));
        	if ($rid_notend == '') {
	            // 建立點名清單
	            dbNew(
	                'APP_rollcall_base',
	                '`course_id`, `creator`, `create_time`, `begin_time`, `end_time`',
	                sprintf("%d,'%s',NOW(),NOW(),'9999-12-31 00:00:00'", $sysSession->course_id, $sysSession->username)
	            );
	
	            if ($sysConn->Affected_Rows()){
	                $rid = $sysConn->Insert_ID();
	                // 取得目前選修的所有正式生
	                $allstudents = dbGetCol('WM_term_major','username',sprintf("course_id=%d and role&%d",$sysSession->course_id,$sysRoles['student']));
	                for($i=0, $size=count($allstudents); $i<$size; $i++) {
	                    dbNew(
	                        'APP_rollcall_record',
	                        '`rid`, `username`, `rollcall_time`, `rollcall_status`',
	                        sprintf("%d,'%s','0000-00-00 00:00:00',0", $rid, $allstudents[$i])
	                    );
	                }
	                $data['code'] = 1;
	                $data['rid'] = $rid;
	            }else{
	                $data['code'] = 0;
	                $data['errorMsg'] = $MSG['errmsg_insert_rollcall_fail'][$sysSession->lang];
	            }
        	} else {
        		$data['code'] = 0;
	            $data['errorMsg'] = $MSG['status_in'][$sysSession->lang];
        	}
            $msg = json_encode($data);
            break;

        case "over_active":  //結束點名
            $rtn = dbSet('APP_rollcall_base',"end_time=NOW()",'rid=' . $rid);
            $isModified = $sysConn->Affected_Rows();
            if ($isModified) {
                $data['code'] = 1;
            } else {
                $data['code'] = 0;
            }
            $msg = json_encode($data);
            break;

        case "get_people":  //取得目前正式生各別報到的情況
            $arr_major = array();
            $arr_select = array();
            $allUsers = dbGetAll(
                'APP_rollcall_record as T1 JOIN WM_user_account as T2 ON T1.username=T2.username',
                'T1.username, CONCAT(T2.last_name,T2.first_name) as name',
                sprintf("rid=%d",$rid)
            );
            for($i=0, $size=count($allUsers); $i<$size; $i++) {
                $arr_major[$allUsers[$i]['username']] = $allUsers[$i]['name'];
                $arr_select[] = $allUsers[$i]['username'];
            }
            $arr_submit = array();
            $submitUsers = dbGetAll(
                'APP_rollcall_record as T1 JOIN WM_user_account as T2 ON T1.username=T2.username',
                'T1.username, CONCAT(T2.last_name,T2.first_name) as name',
                sprintf("rid=%d and rollcall_status=1",$rid)
            );
            for($i=0, $size=count($submitUsers); $i<$size; $i++) {
                $arr_submit[] = $submitUsers[$i]['username'];
            }

            $no_submit = array_diff($arr_select,$arr_submit);
            $html = '';
            $i = 0;
            foreach ($arr_submit as $val) {
                if ($i==10) $i = 0;
                $html .= '<div class="col-md-2 img-circle people" style="background-color:'.$color[$i].'">'.$arr_major[$val].'</div>';
                $i++;
            }

            foreach ($no_submit as $val) {
                $html .= '<div class="col-md-2 img-circle people">'.$arr_major[$val].'</div>';
            }

            $data['html'] = $html;
            $data['submit'] = count($arr_submit);
            $data['nosubmit'] = count($no_submit);

            $msg = json_encode($data);
            break;

    }

    if ($msg != '') {
        echo $msg;
    }
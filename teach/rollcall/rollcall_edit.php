<?php
    /**
     * 點名歷程記錄
     *
     * @since   2018/03/30
     * @author  Jeff Wang
     * @version $Id: rollcall_manage.php $
     * @copyright Wisdom Master 5.1(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lang/rollcall.php');

    $sysSession->cur_func = '300100600';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    if($_POST['active']=='save'){
        $rollcall_id=$_POST['rollcall_id'];
        $rollcallData = dbGetAll('APP_rollcall_base as T1 left join APP_rollcall_record as T2 on T1.rid=T2.rid',
            '*', sprintf("T1.rid=%d order by username",$rollcall_id)
        );
        for($i=0, $size=count($rollcallData); $i<$size; $i++) {
            $thisUsername = $rollcallData[$i]['username'];
            if (($rollcallData[$i]['rollcall_status'] != $_POST['status'][$thisUsername]) || 
                ($rollcallData[$i]['memo'] != $_POST['ps'][$thisUsername]))
            {
                dbSet(
                    'APP_rollcall_record',
                    sprintf("rollcall_status=%d,memo='%s',modifier='%s',modify_time=NOW()",$_POST['status'][$thisUsername],$_POST['ps'][$thisUsername],$sysSession->username),
                    sprintf("rid=%d and username='%s'",$rollcall_id,$thisUsername)
                );
            }
        }
        echo "<script>alert('".$MSG['save_success'][$sysSession->lang]."');</script>";
        unset($rollcall_id);
        unset($rollcallData);
    }
    
    $page = ($_GET['page'])?$_GET['page']:$_POST['page'];
    
    $sb = ($_GET['s']) ? 'rollcall_status' : 'username';
    $dir = ($_GET['d']) ? 'DESC' : 'ASC' ;

    $rollcall_id=($_GET['rid'])?$_GET['rid']:$_POST['rollcall_id'];
    $named=dbGetRow('APP_rollcall_base','*','rid='.htmlspecialchars(stripslashes(trim($rollcall_id))));
    $rollcallData = dbGetAll('APP_rollcall_base as T1 left join APP_rollcall_record as T2 on T1.rid=T2.rid',
        '*', sprintf("T1.rid=%d order by $sb $dir",$rollcall_id)
    );

    $P_status_db=dbGetStMr('WM_div_master','*',"type_id='rollcall_status' order by show_order asc");
    while (!$P_status_db->EOF)
    {
        $P_status[$P_status_db->fields['value']]=$P_status_db->fields['value_name'];

        $P_status_db->MoveNext();
    }

    if($_POST['active']=="export_csv"){

        $fname = urlencode($sysSession->course_name)."course.csv";
        header('Content-Disposition: attachment; filename="' . $fname . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/csv; name="' . $fname . '"');

        $content = $MSG['name_time'][$sysSession->lang].':'.$named['create_time']."\n\n";
        $content .= $MSG['number'][$sysSession->lang].','.$MSG['msg_username'][$sysSession->lang].','.$MSG['real_name'][$sysSession->lang].','.$MSG['name_time'][$sysSession->lang].','.$MSG['Attendance_status'][$sysSession->lang].','.$MSG['ps'][$sysSession->lang].','.$MSG['manager'][$sysSession->lang].','.$MSG['last_update_time'][$sysSession->lang]."\n";

        $i=1;
        foreach ($rollcallData as $k=>$val){
            $detail = getUserDetailData($val['username']);
            if (!isset($userDetailData[$val['modifier']])){
                getUserDetailData($val['modifier']);
            }
            $count = dbGetOne('WM_term_major', 'count(*)', "username='{$val['username']}' and role&32 and course_id={$sysSession->course_id}", ADODB_FETCH_ASSOC);
            $detail['realname'] = $detail['realname'].($count==0?'('.$MSG[not_major][$sysSession->lang].')':'');
            if ($val['rollcall_time'] == '0000-00-00 00:00:00') $val['rollcall_time'] = '-';
            if ($val['modify_time'] == '0000-00-00 00:00:00') $val['modify_time'] = '-';
            $content.= $i.",".$val['username'].",".$detail['realname'].",".$val['rollcall_time'].",".$P_status[$val['rollcall_status']].",\"".$val['memo']."\",".$userDetailData[$val['modifier']]['realname'].",".$val['modify_time']."\n";
            $i++;
        }

        echo mb_convert_encoding($content,'Big5','UTF-8');
        exit;
    }
    $d = intval($_GET['d']) ^ 1;
$js = <<< BOF

    var MSG_SORT_CONFIRM  = "{$MSG['alert_sort'][$sysSession->lang]}";
    var MSG_EXPORT_CONFIRM  = "{$MSG['alert_export'][$sysSession->lang]}";
    var MSG_BACK_CONFIRM  = "{$MSG['alert_back'][$sysSession->lang]}";
    
    var isMZ             = (navigator.userAgent.toLowerCase().indexOf('firefox') > -1);
    var wtuc_get_keypress = false;	/* 是否有輸入或點 input 的欄位 */
    var btn = false;
    var csv_btn = false;

    if (isMZ) window.captureEvents(Event.KEYPRESS);

	function captureKey(e){
		if((event.keyCode > 0) && (!wtuc_get_keypress)){
			wtuc_get_keypress = true;
		}
	}
	document.onkeypress=captureKey;

    function save(){
        btn = true;
        document.mainfm.active.value='save';
        document.mainfm.submit();
    }

    function export_csv(){
        if ((wtuc_get_keypress && confirm(MSG_EXPORT_CONFIRM)) || !wtuc_get_keypress) {
            var obj = document.actForm;
            obj.action = '';
            obj.active.value='export_csv';
            obj.rollcall_id.value='{$rollcall_id}';
            obj.submit();
            if (wtuc_get_keypress) {
                csv_btn = true;
            }
        }
    }
    
    function sort(){
        if ((wtuc_get_keypress && confirm(MSG_SORT_CONFIRM)) || !wtuc_get_keypress) {
            location.replace('rollcall_edit.php?rid={$rollcall_id}&s=1&d=$d');
        }
	}
	
	function goback(){
        if ((wtuc_get_keypress && confirm(MSG_BACK_CONFIRM)) || !wtuc_get_keypress) {
            location.replace('rollcall_manage.php?page={$page}');
            btn = true;
        }
	}
	
	function edit() {
	    wtuc_get_keypress = true;
	}
	
	window.onbeforeunload=function(){
		if(wtuc_get_keypress && !btn) {
		    if (csv_btn) {
                csv_btn = false;
                return;  
		    }
			return "{$MSG['alert_exit'][$sysSession->lang]}";
		}
	};

BOF;

    // 開始呈現 HTML
    showXHTML_head_B($MSG['title'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B();
    $tmp_colspan = 8;
    echo '<div align="center">';
    showXHTML_table_B('width="960" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
        showXHTML_tr_B();
            showXHTML_td_B();
                $ary[] = array($MSG['record_record'][$sysSession->lang], 'tabs');
                showXHTML_tabs($ary, 1);
            showXHTML_td_E();
        showXHTML_tr_E();
    showXHTML_tr_B();
        showXHTML_td_B('valign="top" id="CGroup" ');
            showXHTML_form_B('style="display:inline;" method="post" name="mainfm" action="rollcall_edit.php"', '');
            showXHTML_input('hidden', 'active' , '', '', '');
            showXHTML_input('hidden', 'rollcall_id', $rollcall_id, '', '');
            showXHTML_input('hidden', 'page', $page, '', '');

            showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="TeacherList" class="cssTable"');
            showXHTML_tr_B('class="cssTrHead"');
                showXHTML_td_B('colspan="' . $tmp_colspan . '" ');
                echo $MSG['name_time'][$sysSession->lang].':'.$named['create_time'];
                showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td_B('colspan="' . $tmp_colspan . '"  id="toolbar1"');
                showXHTML_input('button', ''         , $MSG['store'][$sysSession->lang], '', 'class="cssBtn" onclick="save();"');
                showXHTML_input('button', ''         , $MSG['title6'][$sysSession->lang], '', 'class="cssBtn" onclick="goback();"');
                showXHTML_input('button', ''         , $MSG['csv'][$sysSession->lang], '', 'class="cssBtn" onclick="export_csv('.$named.');"');
                showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrHead"');
                showXHTML_td(' align="center" width="5%" nowrap="noWrap"', $MSG['number'][$sysSession->lang]);
                showXHTML_td(' align="center" width="15%" nowrap="noWrap"', $MSG['msg_username'][$sysSession->lang]);
                showXHTML_td(' align="center" width="15%" nowrap="noWrap"', $MSG['real_name'][$sysSession->lang]);
                showXHTML_td(' align="center" width="15%" nowrap="noWrap"', $MSG['name_time'][$sysSession->lang]);
                showXHTML_td(' align="center" width="10%" nowrap="noWrap"', '<a class="cssAnchor" href="javascript:sort()">' .$MSG['Attendance_status'][$sysSession->lang].($_GET['s']? sprintf('<img src="/theme/default/learn/dude07232001%s.gif" border="0" align="absmiddl">', $d ? 'up' : 'down'):'') . '</a>');
                showXHTML_td(' align="center" nowrap="noWrap"', $MSG['ps'][$sysSession->lang]);
                showXHTML_td(' align="center" width="10%" nowrap="noWrap"', $MSG['manager'][$sysSession->lang]);
                showXHTML_td(' align="center" width="15%" nowrap="noWrap"', $MSG['last_update_time'][$sysSession->lang]);
            showXHTML_tr_E();

            if (count($rollcallData) > 0){
                foreach ($rollcallData as $k=>$val){
                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                    showXHTML_tr_B($col);
                    showXHTML_td('align="center"', ($k+1));
                    showXHTML_td('', $val['username']);
                    $detail = getUserDetailData($val['username']);
                    $count = dbGetOne('WM_term_major', 'count(*)', "username='{$val['username']}' and role&32 and course_id={$sysSession->course_id}", ADODB_FETCH_ASSOC);
                    showXHTML_td('', $detail['realname'].($count==0?'<font color=red>('.$MSG[not_major][$sysSession->lang].')</font>':''));
                    if ($val['rollcall_time'] == '0000-00-00 00:00:00'){
                        showXHTML_td('align="center"', '-');
                    }else{
                        showXHTML_td('align="center"', substr($val['rollcall_time'],11));
                    }
                    showXHTML_td_B('align="center"');
                        showXHTML_input('select', sprintf('status[%s]',$val['username']), $P_status, $val['rollcall_status'], 'size="1"  class="cssInput" onchange="edit();" ');
                    showXHTML_td_E();
                    showXHTML_td_B('align="center"');
                        showXHTML_input('text', sprintf('ps[%s]',$val['username']), $val['memo'], '', '  class="cssInput"');
                    showXHTML_td_E();
                    if ($val['modify_time'] == '0000-00-00 00:00:00'){
                        showXHTML_td('align="center"','-');
                        showXHTML_td('align="center"','-');
                    }else{
                        if (!isset($userDetailData[$val['modifier']])){
                            getUserDetailData($val['modifier']);
                        }
                        showXHTML_td('align="center"',$userDetailData[$val['modifier']]['realname']);
                        showXHTML_td('align="center"',$val['modify_time']);
                    }

                    showXHTML_tr_E();
                }
            }else{
                showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td('align="center" colspan="' . $tmp_colspan . '"  id="toolbar2"', $MSG['no_keyword'][$sysSession->lang]);
                showXHTML_tr_E();
            }

            showXHTML_table_E();
            showXHTML_form_E();

        showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_table_E();
        echo '</div>';
 

    showXHTML_form_B('method="post"', 'actForm');
    showXHTML_input('hidden', 'ticket'  , '', '', '');
    showXHTML_input('hidden', 'active', '', '', '');
    showXHTML_input('hidden', 'rollcall_id'    , '', '', '');
    showXHTML_form_E();


    showXHTML_body_E();

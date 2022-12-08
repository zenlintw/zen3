<?php
    /**
     * 點名歷程
     *
     * @since   2018/03/30
     * @author  Jeff Wang
     * @version $Id: rollcall_manage.php $
     * @copyright Wisdom Master 5.1(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/lstable.php');
    require_once(sysDocumentRoot . '/lang/rollcall.php');

    //引入表單後端動作程式
    // require_once('adv_handler.php');

    //接收資料處理動作
    switch ($_POST['act']) {
        //刪除
        case 'rm':
            if (aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id)){
                $delRollid = intval($_POST['nodeids']);
                $isDelRollidExists = dbGetOne('APP_rollcall_base','count(rid)',sprintf("rid=%d and course_id=%d",$delRollid, $sysSession->course_id));
                if ($isDelRollidExists){
                    dbDel('APP_rollcall_base',sprintf("rid=%d",$delRollid));
                    dbDel('APP_rollcall_record',sprintf("rid=%d",$delRollid));
                }
            }
            break;
    }

    /**
     * 顯示順序編號
     *
     * @return string
     */
    function showNum() {
        global $myTable;
        return $myTable->get_index();
    }
    
    function showPercent($val){
    	if ($val<50) {
    		return '<font color=red>'.$val.'%</font>';
    	} else {
            return $val.'%';
    	}
    }
    /**
     * 顯示按鈕資訊
     *
     * @param int $advId, string $url
     * @return string
     */
    function showView($rid,$end) {
        global $MSG,$sysSession;
        if (isset($_GET['page'])) {
        	$page = intval($_GET['page']);
        } else {
        	$page = 1;
        }
        
        if ($end=='9999-12-31 00:00:00') {
        	return '<input type="button" value="' . $MSG['status_in'][$sysSession->lang] .
               '" class="cssBtn" onclick="doPublishRollcall('.$rid.');"/> ';
        } else {
            return '<input type="button" value="' . $MSG['view'][$sysSession->lang] .
               '" class="cssBtn" onclick="location=\'rollcall_edit.php?page='.$page.'&rid='.$rid.'\'"/> ';
        }
    }

    /**
     * 顯示按鈕資訊
     *
     * @param int $advId, string $url
     * @return string
     */
    function showDelete($rid,$time,$end) {
        global $MSG,$sysSession;
        
        $disable = '';
        if ($end=='9999-12-31 00:00:00') $disable = 'disabled';
        
        return '<input type="button" value="' . $MSG['delete'][$sysSession->lang] .
               '" class="cssBtn" '.$disable.' onclick="doDelete('.$rid.',\''.$time.'\');"/> ';
    }
    

    $rid_notend = dbGetOne('APP_rollcall_base','rid',sprintf("course_id=%d and end_time='9999-12-31 00:00:00'",$sysSession->course_id));
    if ($rid_notend=='') $rid_notend = 0;

    $js = <<< EOB
    var MSG_RM_CONFIRM  = "{$MSG['confirm_delete'][$sysSession->lang]}";
    var MSG_NOT_STUDENT = "{$MSG['msg_not_student'][$sysSession->lang]}";

    function doDelete(rid,time) {
        if (confirm(MSG_RM_CONFIRM.replace("%s", time))){
            document.mainFm.nodeids.value = rid;
            document.mainFm.act.value = 'rm';
            document.mainFm.submit();
        }
    }

    /**
     * 秀出訊息：目前課程沒有正式生
     */
    function alertNoStudent(){
        alert(MSG_NOT_STUDENT);
    }

    /*發佈，進行互動*/
    function doPublishRollcall(rid){
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var w = parseInt(screen.availWidth/3*2);
        if (w < 1280) w = 1280;
        var h = parseInt(screen.availHeight/3*2);
        if (h < 668) h = 668;
        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;

        if (rid!=undefined) {
            winISunFunDon = window.open('/mooc/teach/rollcall/publish.php?rid='+rid, 'iSunFunDo', 'scrollbars=yes,resizable=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
        } else {
            if ({$rid_notend} > 0) {
                if (confirm('{$MSG['msg_in'][$sysSession->lang]}')) {
                    winISunFunDon = window.open('/mooc/teach/rollcall/publish.php?rid='+{$rid_notend}, 'iSunFunDo', 'scrollbars=yes,resizable=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
                } else {
                    return;
                }
            } else {
                winISunFunDon = window.open('/mooc/teach/rollcall/publish.php', 'iSunFunDo', 'scrollbars=yes,resizable=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
            }
        }
    }


    function chgPageSort() {
        return "";
    }
EOB;


    // 開始頁面展現

    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('include', '/lib/dragLayer.js');
        showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B();
            $rollids = dbGetCol('APP_rollcall_base','rid',sprintf("course_id=%d",$sysSession->course_id));
            $ary = array(
                    array($MSG['manage_title'][$sysSession->lang], 'tabsSet1',  '')
                );
            echo '<div align="center">';
            showXHTML_tabFrame_B($ary, 1, 'mainFm', '', 'action=""  method="post" style="display:inline;"');
            showXHTML_input('hidden', 'nodeids', '', '', 'id="nodeids"');
            showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'savePermute' . $_COOKIE['idx']), '', '');
            showXHTML_input('hidden', 'act', '', '', 'id="act"');
            $myTable = new table();
            $myTable->display['page'] = true;
            if (is_array($rollids) && count($rollids)){
	            // 排序
	            $myTable->add_sort('begin_time'  , '`begin_time` ASC', '`begin_time` DESC');
	            $myTable->add_sort('allCount'  , 'allCount ASC', 'allCount DESC');
	            $myTable->add_sort('yesCount'  , 'yesCount ASC', 'yesCount DESC');
	            $myTable->add_sort('notCount'  , 'notCount ASC', 'notCount DESC');
	            $myTable->add_sort('yesRatio'  , 'yesRatio ASC', 'yesRatio DESC');
	            $myTable->set_sort(true, 'begin_time', 'desc', 'chgPageSort()');
            }
            $myTable->extra = 'border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable" style="width:960px"';

            // 工具列
            $toolbar = new toolbar();
            $majorCount = dbGetOne('WM_term_major','count(*)',sprintf("course_id=%d and role&%d",$sysSession->course_id,$sysRoles['student']));
            if ($majorCount == 0){
                $toolbar->add_input('button', '', $MSG['add'][$sysSession->lang]  , '', 'class="cssBtn" onclick="alertNoStudent();return false;" id="btn_new"');
            }else{
                $toolbar->add_input('button', '', $MSG['add'][$sysSession->lang]  , '', 'class="cssBtn" onclick="doPublishRollcall();return false;" id="btn_new"');
            }
            $myTable->add_toolbar($toolbar);

            // 資料
            $myTable->add_field($MSG['number'][$sysSession->lang],      '', '', ''     , 'showNum'    , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['name_time'][$sysSession->lang]  , '', 'begin_time'    , '%begin_time', ''   , 'width="160px"');
            $myTable->add_field($MSG['all_number'][$sysSession->lang]  , '', 'allCount'    , '%allCount'   , ''   , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['is_number'][$sysSession->lang]  , '', 'yesCount'    , '%yesCount'   , ''   , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['no_number'][$sysSession->lang]  , '', 'notCount'    , '%notCount'   , ''   , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['attendance_rate'][$sysSession->lang], '', 'yesRatio', '%yesRatio', 'showPercent' , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['record_record'][$sysSession->lang], '', '', '%rid,%end_time', 'showView' , 'align="center" nowrap="noWrap"');
            $myTable->add_field($MSG['Delete'][$sysSession->lang]    , '', '', '%rid,%begin_time,%end_time' , 'showDelete'  , 'align="center" nowrap="noWrap"' );

            
            if (is_array($rollids) && count($rollids)){
                $rollidstr = implode(',', $rollids);
                $tab = sprintf("APP_rollcall_base as T1 
                                INNER JOIN (select rid,count(*) as allCount from APP_rollcall_record where rid in (%s) group by rid) as T2 on T1.rid=T2.rid 
                                LEFT JOIN (select rid,count(*) as yesCount from APP_rollcall_record where rid in (%s) and rollcall_status in (%s) group by rid) as T3 on T1.rid=T3.rid 
                                ",$rollidstr,$rollidstr,'1,3,4');
                $fields = "T1.*,T2.allCount,IF(T3.yesCount,T3.yesCount,0) AS yesCount,(T2.allCount - IF(T3.yesCount,T3.yesCount,0)) as notCount, round(( IF(T3.yesCount,T3.yesCount,0)/T2.allCount * 100 ),0) as yesRatio";
                $where  = sprintf('T1.rid in (%s)', $rollidstr);
            }else{
                $tab = "APP_rollcall_base";
                $fields = '*';
                $where = sprintf("course_id=%d",$sysSession->course_id);
            }
            $myTable->set_sqls($tab, $fields, $where);
            $myTable->show();
        showXHTML_tabFrame_E();
        echo '</div>';

    showXHTML_body_E();

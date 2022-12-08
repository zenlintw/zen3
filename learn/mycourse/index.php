<?php
    /**
     * 我的課程
     *
     * @since   2003/06/05
     * @author  ShenTing Lin
     * @version $Id: index.php,v 1.1 2010/02/24 02:39:08 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/learn/mycourse/lib.php');
    require_once(sysDocumentRoot . '/lang/mycourse.php');
    require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/lib_cal_alert.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/models/school.php');
    
    // $sysSession->cur_func='700700100';
    // $sysSession->restore();
    if (!aclVerifyPermission(700700100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }
    
    // 我的課程呈現方式 - 設定為文字模式時，就切換到文字模式的程式
    $rsSchool = new school();
    if ($rsSchool->getMyCourseView($sysSession->school_id) == 'G') {
        header('LOCATION: /mooc/mycourse.php');
        exit;
    }
    
    
    // 老師的身份
    $teacher_level = array(
        $sysRoles['assistant']  => $MSG['assistant'][$sysSession->lang],
        $sysRoles['instructor'] => $MSG['instructor'][$sysSession->lang],
        $sysRoles['teacher']    => $MSG['teacher'][$sysSession->lang]
    );
    
    // 課程狀態
    $cs_status = array(
        0 => $MSG['cs_state_close'][$sysSession->lang],
        1 => $MSG['cs_state_open_a'][$sysSession->lang],
        2 => $MSG['cs_state_open_a_date'][$sysSession->lang],
        3 => $MSG['cs_state_open_n'][$sysSession->lang],
        4 => $MSG['cs_state_open_n_date'][$sysSession->lang],
        5 => $MSG['cs_state_prepare'][$sysSession->lang]
    );
    
    // 檢查目前功能編號 (Begin)
    /**
     * 0 : 初始化或重新載入
     * 1 : 換群組
     * 2 : 換 tabs
     * 3 : 翻頁
     **/

    $status = -1;
    
    // 判斷是否允許 guest 登入
    // list($guest_login) = dbGetStSr('WM_school','guest','school_id=' . $sysSession->school_id . ' and school_host="' . $_SERVER['HTTP_HOST'] . '"');
    $guest_login = 'Y';
    
    if (isset($_GET['tabs'])&&!isset($_POST['tabs'])) {
        $_POST['tabs'] = intval($_GET['tabs']);
    }
    
    if ($sysSession->username != 'guest'){    // 已登入平台 在 個人區->我的課程->全校課程
        
        $isTeacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
        
        if (!isset($_POST['tabs'])) {
            if (!in_array($sysSession->cur_func, $sys_func_id)) {
                if ($isTeacher) {
                    $tabs = 2;
                    $sysSession->cur_func = $sys_func_id[1];
                }else{
                    $tabs = 1;
                    $sysSession->cur_func = $sys_func_id[0];
                }
            } else {
                $res = array_search($sysSession->cur_func, $sys_func_id);
                if ($res === false) $tabs = 1;
                else $tabs = $res+1;
            }
            $status = 0;
    
            // 如果user沒選任何課程，則直接導向到全校課程
            $selectCourseCount = dbGetCourses('count(*)', $sysSession->username, $sysRoles['auditor']|$sysRoles['student']|$sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']);
            if (!$selectCourseCount) {
                $tabs = 1;
                $sysSession->cur_func = $sys_func_id[2];
            }
    
        } else {
            $tabs = intval($_POST['tabs']);
            if ((intval($tabs) < 1) || (intval($tabs) > 6)) $tabs = 1;
            $sysSession->cur_func = $sys_func_id[intval($tabs) - 1];
            $status = 2;
        }
    }else {    //  尚未登入平台 在 首頁->搜尋&選課->全校課程
        if (!isset($_POST['tabs'])) {
            $sysSession->cur_func = $sys_func_id[2];
            $tabs = 3;
            $status = 0;
        }else{
            $tabs = intval($_POST['tabs']);
            $status = 2;
        }
    }
    
    dbSet('WM_session', "cur_func='{$sysSession->cur_func}'", "idx='{$_COOKIE['idx']}'");
    $smarty->assign('tabs', $tabs);
    // 檢查目前功能編號 (End)

    if (isset($_POST['chgp'])) $status = 1;   // 更換群組
    if (isset($_POST['page'])) $status = 3;   // 翻頁
    
    // 檢查設定檔存不存在
    chkSetting();
    
    $isTreeLoad = ($status == 2) ? 'true' : 'false';
    
    // 行事曆通知
    $alert = 'false';
    
    if (!isset($_COOKIE['cal_alert']) || $_COOKIE['cal_alert']==0) {
        $cal_type = array('personal','course','class','school');
        $cal_num  = count($cal_type);
        $cal_msg  = GetCalendarAlert();
        $doc       = domxml_open_mem(preg_replace('/\sxmlns\s*=\s*"[^"]*"\s/U', ' ', $cal_msg));
        $xpath       = @xpath_new_context($doc);
        
        for ($i = 0;$i < $cal_num;$i++){
            $obj = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '[@num > 0]/memo');
            $p_nodeset = $obj->nodeset;
            $t_p_count = count($p_nodeset);
            
            if ($t_p_count > 0) {
                $alert = 'true';
                break;
            }
        }
        $now = strtotime(date("Y-m-d H:i:s"));
        $end = strtotime(date('Y-m-d',strtotime('+1 day')).' 00:00:00');
        $diff = $end-$now;
        setcookie('cal_alert', 1, time()+$diff, '/', '', $http_secure);
    }
    
    $js = <<< BOF
    var MSG_NOW              = "{$MSG['msg_now'][$sysSession->lang]}";
    var MSG_FOREVER             = "{$MSG['forever'][$sysSession->lang]}";
    var MSG_FROM2            = "{$MSG['from2'][$sysSession->lang]}";
    var MSG_TO2              = "{$MSG['to2'][$sysSession->lang]}";
    var MSG_FROM             = "{$MSG['msg_from'][$sysSession->lang]}";
    var MSG_TO               = "{$MSG['msg_to'][$sysSession->lang]}";
    var MSG_FAVORITE_SUCCESS = "{$MSG['msg_favorite_success'][$sysSession->lang]}";
    var MSG_FAVORITE_FAIL    = "{$MSG['msg_favorite_fail'][$sysSession->lang]}";
    var MSG_FAVORITE_EXIST   = "{$MSG['msg_favorite_exist'][$sysSession->lang]}";
    var MSG_TARGET           = "{$MSG['msg_target'][$sysSession->lang]}";
    var MSG_SRC_COURSE       = "{$MSG['msg_source'][$sysSession->lang]}";
    var MSG_SYS_ERROR        = "{$MSG['msg_system_error'][$sysSession->lang]}";
    var MSG_SEL_COURSE       = "{$MSG['msg_elective_source'][$sysSession->lang]}";
    var MSG_SELECT_ALL       = "{$MSG['msg_select_all'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL    = "{$MSG['msg_select_cancel'][$sysSession->lang]}";
    
    var MSG_CORRECT_DATE     = "{$MSG['msg_course9'][$sysSession->lang]}";
    var MSG_CORRECT_DATE1    = "{$MSG['msg_course10'][$sysSession->lang]}";
    var MSG_CORRECT_DATE2    = "{$MSG['msg_course11'][$sysSession->lang]}";
    var MSG_CORRECT_DATE3    = "{$MSG['msg_course12'][$sysSession->lang]}";
    var MSG_CORRECT_DATE4    = "{$MSG['msg_course13'][$sysSession->lang]}";
    var MSG_CORRECT_DATE5    = "{$MSG['msg_course14'][$sysSession->lang]}";
    var MSG_ENROLL_DATE      = "{$MSG['msg_enroll_date'][$sysSession->lang]}";
    var MSG_STUDY_DATE       = "{$MSG['msg_study_date'][$sysSession->lang]}";
    
    var isTreeLoad = {$isTreeLoad};
    var currTabId  = {$tabs};
    var ticket = "";
    var alert  = "{$alert}";
    
    function defaultSort()
    {
        var obj = document.getElementById('actFm');
        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.sortby.value = 'course_id';
        obj.order.value = 'desc';
        obj.submit();
    }
    
    $(function () {
        if (alert=='true') {
            $.fancybox({
                margin:40,
                href: "/learn/calender_alert.php",
                type: "ajax",
                ajax: {
                    type: "POST",
                    data: {
        
                    }
                },
                helpers: {
                    overlay : {closeClick: false}
                }
            });
        }

    });
    
    if (parent == self) document.write('<p align=center><input type=button value=\"{$MSG['goto_homepage'][$sysSession->lang]}\" onclick=\"top.location.replace(\'/logout.php\');\" class=cssBtn></p>');
BOF;
    $smarty->assign('inlineJS', $js);
    
    $isquery = trim($_POST['isquery']);
    $advqy   = trim($_POST['advqy']);

    switch ($tabs) {
        case 1: include_once('major.php');     break;   // 課程教室
        case 2:    // 課程辦公室
            if ($isTeacher) include_once('teacher.php');   
            break;
        case 3: include_once('school.php');    break;   // 全校課程
    }
    
    // assign
    $smarty->assign('post', $_POST);
    $smarty->assign('MSG', $MSG);
    $smarty->assign('sysSession', $sysSession);
    $smarty->assign('isTeacher', $isTeacher?1:0);
    
    // output
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('learn/mycourse/index.tpl');
    $smarty->display('common/tiny_footer.tpl');

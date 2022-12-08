<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2003/06/09                                                            *
     *        work for  : 人員管理 - 學員統計                                                                      *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *      $Id: stud_info.php,v 1.1 2010/02/24 02:40:31 saly Exp $:                                                                                          *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/teach_student.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/lib_logs.php');
    require_once(sysDocumentRoot . '/lib/username.php');

    $sysSession->cur_func = '1500200100';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    /**
    * 顯示身分
    * @param string $val : 身分別
    * @return string : 要顯示的文字
    **/
    function showRole($val) {
        global $MSG, $sysSession, $sysRoles;
        static $course_roles;

        if (!isset($course_roles)) $course_roles = array_reverse(array_slice($sysRoles, 1, 9));

        $user = ''; $val = intval($val);
        foreach ($course_roles as $role => $role_value)
            if ($val & $role_value)
                $user .= $MSG[$role][$sysSession->lang] . ' & ';

        return substr($user, 0, -3);
    }

    // 取得身份
    $role = 'student';
    if (isset($_GET['role']))
        $role = trim($_GET['role']);
    else if (isset($_POST['role']))
        $role = trim($_POST['role']);
    // 檢查身份是否是規定的數值
    if (!in_array($role, array_keys($sysRoles)))
        $role = 'student';

    $role_val = $sysRoles[$role];

    $icon_up = sprintf('<img src="/theme/%s/%s/dude07232001up.gif" border="0" align="absmiddl">'  , $sysSession->theme, $sysSession->env);
    $icon_dn = sprintf('<img src="/theme/%s/%s/dude07232001down.gif" border="0" align="absmiddl">', $sysSession->theme, $sysSession->env);

    /*
    * 排序
    */
    $sort_ary = array('',
        'T.username',
        'CONCAT(U.last_name,U.first_name)',
        'S.login_times',
        'S.last_login',
        'T.login_times',
        'T.last_login',
        'T.post_times',
        'T.dsc_times',
        'rss',
        'page',
        'T.role'
    );

    $_POST['sortby'] = min(11, max(1, $_POST['sortby']));
    $sortby = $sort_ary[$_POST['sortby']];

    if (($order = trim($_POST['order'])) != 'asc' && $order != 'desc') $order = 'asc';

    // query WM_master 的 WM_sch4user sort array
    $sort_master = array('login_times','last_login');

    $course_id = defined('Course_ID') ? Course_ID : $sysSession->course_id;
    $sqls = 'select T.*,U.first_name,U.last_name,U.email,sum(unix_timestamp(P.over_time)-unix_timestamp(P.begin_time)+1) as rss,' .
            'count(P.username) as page,S.login_times as Slogin_times,S.last_login as Slast_login ' .
            'from WM_term_major as T ' .
            'left join WM_user_account as U ' .
            'on T.username=U.username ' .
            'left join WM_record_reading as P ' .
            'on T.course_id=P.course_id  and T.username = P.username ' .
            'left join ' . sysDBname . '.WM_sch4user as S ' .
            'on S.school_id=' . $sysSession->school_id . ' and S.username=T.username ' .
            'where T.course_id=' . $course_id . ($role == 'all' ? ' ' : (' and (T.role & ' . $role_val . ') ')) .
            'group by T.username ' .
            'order by ' . $sortby . ' ' . $order;

    chkSchoolId('WM_term_major');
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    $uRS = $sysConn->Execute($sqls);
    if ($sysConn->ErrorNo()) die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());

    $sysCL = array('Big5'=>'zh-tw','en'=>'en','GB2312'=>'zh-cn');
    $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
    if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';

    // 開始 output HTML
    showXHTML_head_B($MSG['student_info'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
    $scr = <<< EOB

var html_lang = "{$ACCEPT_LANGUAGE}";
var MSG_SELECT_CANCEL = "{$MSG['select_cancel'][$sysSession->lang]}";
var MSG_SELECT_ALL = "{$MSG['select_all'][$sysSession->lang]}";
var role = "{$role}";

function mailto(){
    var obj = document.getElementById('studentList');
    if (obj == null) window.location.replace('stud_info.php');
    var nodes = obj.getElementsByTagName('input');
    var receiver = '';
    for(var i=1; i<nodes.length; i++){
        attr = nodes[i].getAttribute("exclude");
        if (nodes[i].type == 'checkbox' && (attr == null) && nodes[i].checked){
            receiver += nodes[i].value + ',';
        }
    }
    if (receiver){
        obj = document.getElementById('mailForm');
        obj.to.value = receiver.replace(/,$/, '');
        obj.submit();
    }
    else{
        alert("{$MSG['select_mem'][$sysSession->lang]}");
    }
}

function exportInfo(){

    var obj = document.getElementById('studentList');
    if (obj == null) window.location.replace('stud_info.php');
    var xmlMode = obj.doctype[0].checked;

    var doc = '',col = '';

    obj = document.getElementById('studentListTable');

    var nodes = obj.getElementsByTagName('input');
    if (nodes.length == 1)
    {
        var all = confirm("{$MSG['export_stud'][$sysSession->lang]}");
        if (!all) return;
    }

    var total_rows = obj.rows.length;

    var role_msg = '';

    if (role == "all")
    {
        var xmlColumns = new Array('','serial','account', 'realname', 'capacity','login_times', 'last_login', 'studying_times', 'last_studying', 'post_times', 'discuss_times', 'read_time', 'read_pages', 'study_gradation');
        var temp_title = new Array("","{$MSG['serial'][$sysSession->lang]}","{$MSG['account'][$sysSession->lang]} ","{$MSG['realname'][$sysSession->lang]} ","{$MSG['capacity'][$sysSession->lang]}","{$MSG['login_times'][$sysSession->lang]} ","{$MSG['last_login'][$sysSession->lang]} ","{$MSG['class_times'][$sysSession->lang]} ","{$MSG['last_class'][$sysSession->lang]} ","{$MSG['post_count'][$sysSession->lang]} ","{$MSG['chat_count'][$sysSession->lang]} ","{$MSG['read_time'][$sysSession->lang]} ","{$MSG['read_page'][$sysSession->lang]} ");
        var column = 13;

    }
    else
    {
        var xmlColumns = new Array('','serial','account', 'realname','login_times', 'last_login', 'studying_times', 'last_studying', 'post_times', 'discuss_times', 'read_time', 'read_pages', 'study_gradation');
        var temp_title = new Array("","{$MSG['serial'][$sysSession->lang]}","{$MSG['account'][$sysSession->lang]} ","{$MSG['realname'][$sysSession->lang]} ","{$MSG['login_times'][$sysSession->lang]} ","{$MSG['last_login'][$sysSession->lang]} ","{$MSG['class_times'][$sysSession->lang]} ","{$MSG['last_class'][$sysSession->lang]} ","{$MSG['post_count'][$sysSession->lang]} ","{$MSG['chat_count'][$sysSession->lang]} ","{$MSG['read_time'][$sysSession->lang]} ","{$MSG['read_page'][$sysSession->lang]} ");
        var column = 12;

        role_msg = "{$MSG['export_role'][$sysSession->lang]}";
        switch (role)
        {
            case 'auditor':
                role_msg += "{$MSG['auditor'][$sysSession->lang]}";
                break;
            case 'student':
                role_msg += "{$MSG['student'][$sysSession->lang]}";
                break;
            case 'assistant':
                role_msg += "{$MSG['assistant'][$sysSession->lang]}";
                break;
            case 'instructor':
                role_msg += "{$MSG['instructor'][$sysSession->lang]}";
                break;
            case 'teacher':
                role_msg += "{$MSG['teacher'][$sysSession->lang]}";
                break;
        }
    }


    if (!xmlMode)
    {
        col = (col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        doc = '<tr ' + col + '>' +
              '<td colspan="'+ column + '">'+"{$MSG['student_info'][$sysSession->lang]}"+'&nbsp;&nbsp;&nbsp;'+ role_msg + '</td>' +
              '</tr>';

        col = (col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        doc += '<tr ' + col + '>';
        for (var j=1;j < column;j++)
        {
            doc += '<td>' + temp_title[j] + '</td>';
        }
        doc += '</tr>';

        for(var i=3; i<total_rows -1; i++)
        {
            col = (col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

            doc += '<tr ' + col + '>';
            for (var j=1; j< column; j++)
            {
                temp = obj.rows[i].cells[j].innerHTML;
                temp = temp.replace(/<A [^>]*>/gi,'');
                temp = temp.replace(/<\/A[^>]*>/gi,'');
                doc += '<td>'+temp+'</td>';
            }

            doc += '</tr>';
        }

        doc = '<html>'+
              '<head>'+
              '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >'+
              '<meta http-equiv="Content-Language" content="' + html_lang + '" > '+
              '<title>' + "{$MSG['student_info'][$sysSession->lang]}" + '</title>'+
              '<style type="text/css">'+
              '.cssTrEvn {' +
              '    font-size: 12px;' +
              '    line-height: 16px;' +
              '    text-decoration: none;' +
              '    letter-spacing: 2px;' +
              '    color: #000000;' +
              '    background-color: #FFFFFF;' +
              '    font-family: Tahoma, "Times New Roman", Times, serif;' +
              '}' +
              '.cssTrOdd {' +
              '    font-size: 12px;' +
              '    line-height: 16px;' +
              '    text-decoration: none;' +
              '    letter-spacing: 2px;' +
              '    color: #000000;' +
              '    background-color: #EBF3DA;' +
              '    font-family: Tahoma, "Times New Roman", Times, serif;' +
              '}' +
              '.cssTable {'+
              'background-color: #ECECEC; '+
              'border: 1px solid #7EAB4E; '+
              '} '+
              '</style>'+
              '</head>' +
              '<body  >' +
              '<table id="stud_info" border="0" cellspacing="1" cellpadding="3" style="display:block;width:max-content;width:-moz-max-content;" class="cssTable" >' +
              doc +
              '</table>'+
              '</' + 'body>' +
              '</html>';

        obj = document.getElementById('zipForm');
        obj.export_data.value =  stringToBase64(encodeURIComponent(doc));
        obj.export_file.value = 'htm';
        obj.submit();
    }

    if (xmlMode)
    {
        doc = '<function_name>' + "{$MSG['student_info'][$sysSession->lang]} " +'</function_name>';

        if (role != 'all') {
            doc += '<query_role>' + role_msg  +'</query_role>';
        }

        doc += '<items>\\n<item type="title">\\n';
        for(var k=1; k<column; k++){
            doc += '<' + xmlColumns[k] + '>' + temp_title[k] + '</' + xmlColumns[k] + '>\\n';
        }
        doc += '</item>';

        for(var i=3; i<total_rows -1; i++){
            nodes = obj.rows[i].cells[1].getElementsByTagName('input');
            // if (nodes[0].checked || (all && !nodes[0].disabled) ){
                doc += '<item>\\n';

                var temp_column = 0;

                for(var j=1; j<column; j++){
                    var temp = '';
                    temp = obj.rows[i].cells[j].innerHTML.replace(/^\\s+|\\s+$/g, '');

                    temp = temp.replace(/<FONT [^>]*>/gi,'');
                    temp = temp.replace(/<\/FONT[^>]*>/gi,'');

                    if (role == "all")
                    {
                        if (j == 4)
                        {
                            temp = temp.replace(/<DIV [^>]*>/gi,'');
                            temp = temp.replace(/<\/DIV[^>]*>/gi,'');
                        }
                    }

                    temp_column = j;
                    doc += '<' + xmlColumns[temp_column] + '>' + temp +'</' + xmlColumns[temp_column] + '>\\n';
                }
                doc += '</item>\\n';
            // }
        }

        obj = document.getElementById('zipForm');
        obj.export_data.value = stringToBase64(encodeURIComponent('<?xml version="1.0" encoding="UTF-8" ?>\\n' + '<student_info>\\n' + doc + '</items></student_info>'));
        obj.export_file.value = 'xml';
        obj.submit();
    }
}

/**
 * 同步全選或全消的按鈕與 checkbox
 * @version 1.0
 **/
var nowSel = false;
function selected_box() {
    var obj  = document.getElementById("ck");
    var btn1 = document.getElementById("btnSel1");
    var btn2 = document.getElementById("btnSel2");
    if ((obj == null) || (btn1 == null) ) return false;
    nowSel = !nowSel;
    obj.checked = nowSel;

    btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
    btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

    var obj = document.getElementById('studentList');
    var nodes = obj.getElementsByTagName('input');
    for(var i = 1; i < nodes.length; i++)
    {
        if (nodes[i].type == 'checkbox' && !nodes[i].disabled)
            nodes[i].checked = nowSel;
    }
}

/**
 * 單獨點選人員
 **/
function selPeo(val)
{
    var obj = document.getElementById('studentList');
    var selAll = true;
    nodes = obj.getElementsByTagName('input');
    for (var i = 0; i < nodes.length; i++) 
    {
        attr = nodes[i].getAttribute("exclude");
        if ((nodes[i].type == "checkbox") && (attr == null))
        {
            if (!nodes[i].disabled && !nodes[i].checked)
            {
                selAll = false;
                break;
            }
        }
    }
    
    document.getElementById("ck").checked = selAll;
    document.getElementById("btnSel1").value = selAll ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;;
    document.getElementById("btnSel2").value = document.getElementById("btnSel1").value;
    nowSel = selAll;
}

function sort_data(val)
{
    var obj = document.sortFm;
    if (obj)
    {
        obj.order.value = (obj.order.value == 'asc') ? 'desc' : 'asc';
        obj.sortby.value = val;
        obj.submit();
    }
}

function changeRadio(radio_value)
{
    var obj = null,cnt = 0;

    obj = document.getElementsByTagName("input");
    cnt = obj.length;

    for (var i = 0; i < cnt; i++)
    {
        if ((obj[i].type == "radio") && (obj[i].value == radio_value))
        {
            obj[i].checked = true;
        }
    }
}

function viewDetail(cal,val)
{
    var detail_window = window.open("stud_detail.php?type=" + cal + "&user=" + val + "&course_id={$course_id}", "_blank", "width=700,height=480,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1");
}

function viewLog(val)
{
    var log_window = window.open("stud_log.php?user=" + val + "&course_id={$course_id}", "_blank", "width=500,height=400,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1");
}

EOB;
    // SHOW_PHONE_UI 常數定義於 /mooc/academic/stud/stud_query.php
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1) {
        require_once(sysDocumentRoot . '/lang/learn_ranking.php');
        $datas = array();
        if ($uRS)
        {
            $rank = 1;
            while ($users = $uRS->FetchRow())
            {
                $users['rank'] = $rank++;
                $users['realname'] = htmlspecialchars(checkRealname($users['first_name'], $users['last_name']));
                $temp_show = $users['username'] . ' (' . $users['realname'] . ')';
                $users['userShow'] = $temp_show;
                $users['rss'] = zero2gray(sec2timestamp(intval($users['rss'])));
                $users['role'] = htmlspecialchars(showRole($users['role']));
                $datas[] = $users;
            }
        }
        $smarty->assign('sort', $sortby);
        $smarty->assign('sortVal', $_POST['sortby']);
        $smarty->assign('order', $order);
        $smarty->assign('role', $role);

        $lasttime = getCronDailyLastExecuteTime();
        if ($lasttime == 0)
        {
            $msgUpdate = '<font color="red">' . $MSG['msg_cron_daily_fail'][$sysSession->lang] . '</font>';
        }else{
            $msgUpdate = $MSG['msg_last_updated_time'][$sysSession->lang].'<font color="red">'.$lasttime.'</font>';
        }
        // assign
        $smarty->assign('post', $_POST);
        $smarty->assign('MSG', $MSG);
        $smarty->assign('sysSession', $sysSession);
        $smarty->assign('msgUpdate', $msgUpdate);
        $smarty->assign('inlineJS', $js);
        $smarty->assign('datalist', $datas);
        $smarty->assign('userLearnData', $datas[0]);

        $smarty->assign('datas', $datas);
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/course_header.tpl');
        $smarty->display('phone/teach/student/stud_info.tpl');
        $smarty->display('common/tiny_footer.tpl');
        exit;
    }
    $colspan = ($role == 'all' ? 14 : 13);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('inline',  $scr);
    showXHTML_script('include', '/lib/base64.js');
    showXHTML_head_E();
    showXHTML_body_B();
        showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;min-width:80%"');
        showXHTML_tr_B();
            showXHTML_td_B();
            $ary[] = array($MSG['student_info'][$sysSession->lang], 'tabsSet',  '');
            showXHTML_tabs($ary, 1);
            showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_tr_B();
            showXHTML_td_B('valign="top" ');
        showXHTML_form_B('style="display: inline"', 'studentList');

            showXHTML_table_B('id="studentListTable" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
        showXHTML_tr_B('class="cssTrEvn font01"');
        showXHTML_td_B('colspan="' . $colspan . '"');
        $lasttime = getCronDailyLastExecuteTime();
        if ($lasttime == 0)
        {
            echo $MSG['msg_cron_daily_fail'][$sysSession->lang];
        }else{
            echo $MSG['msg_last_updated_time'][$sysSession->lang].'<font color="red">'.$lasttime.'</font>';
        }
        if (defined('Course_ID'))
        {
            list($caption) = dbGetStSr('WM_term_course', 'caption', 'course_id='.$course_id, ADODB_FETCH_NUM);
            $caption = unserialize($caption);
            echo '<br /><font color="red"><b>', $MSG['course_name'][$sysSession->lang], htmlspecialchars($caption[$sysSession->lang]), '</b></font>';
        }
        showXHTML_td_E();
        showXHTML_tr_E();
            showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td_B('colspan="' . $colspan . '" nowrap id="toolbar1"');
                showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', ' id="btnSel1" class="cssBtn" onclick="selected_box();"');
                echo $MSG['select_role'][$sysSession->lang];

                $roles = array(
                    'auditor'    => $MSG['auditor'][$sysSession->lang],
                    'student'    => $MSG['student'][$sysSession->lang],
                    'assistant'  => $MSG['assistant'][$sysSession->lang],
                    'instructor' => $MSG['instructor'][$sysSession->lang],
                    'teacher'    => $MSG['teacher'][$sysSession->lang],
                    'all'        => $MSG['all'][$sysSession->lang]
                );
                showXHTML_input('select', 'role', $roles, $role , 'onchange="location.replace(\''.$_SERVER['PHP_SELF'].'?role=\' + this.value + \'&course_id='.$course_id.'\');"');

                showXHTML_input('button', '', $MSG['mail_picked'][$sysSession->lang], '', 'class="cssBtn" onclick="mailto();"');
                showXHTML_input('button', '', $MSG['export_info'][$sysSession->lang], '', 'class="cssBtn" onclick="exportInfo();"');
                showXHTML_input('radio', 'doctype', array('xml' => 'XML','htm' => 'HTML'), 'xml','onclick="changeRadio(this.value);"');

                showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrHead"');
                showXHTML_td_B();
                    showXHTML_input('checkbox', 'ck', '', '', '" onclick="selected_box();" exclude="true"' . 'id="ck" title=' . $MSG['select_all'][$sysSession->lang]);
                showXHTML_td_E();

                showXHTML_td('nowrap', $MSG['serial'][$sysSession->lang]);

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(1);" title="' . $MSG['account'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['account'][$sysSession->lang];
                    echo ($sortby == 'T.username') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(2);" title="' . $MSG['realname'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['realname'][$sysSession->lang];
                    echo ($sortby == 'U.first_name,U.last_name') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                if ($role == 'all') {
                    showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(11);" title="' . $MSG['capacity'][$sysSession->lang] . '"');
                        echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                        echo $MSG['capacity'][$sysSession->lang];
                        echo ($sortby == 'T.role') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                        echo '</a>';
                    showXHTML_td_E('');
                }

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(3);" title="' . $MSG['login_times'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['login_times'][$sysSession->lang];
                    echo ($sortby == 'S.login_times') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(4);" title="' . $MSG['last_login'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['last_login'][$sysSession->lang];
                    echo ($sortby == 'S.last_login') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(5);" title="' . $MSG['class_times'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['class_times'][$sysSession->lang];
                    echo ($sortby == 'T.login_times') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(6);" title="' . $MSG['last_class'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['last_class'][$sysSession->lang];
                    echo ($sortby == 'T.last_login') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(7);" title="' . $MSG['post_count'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['post_count'][$sysSession->lang];
                    echo ($sortby == 'T.post_times') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(8);" title="' . $MSG['chat_count'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['chat_count'][$sysSession->lang];
                    echo ($sortby == 'T.dsc_times') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(9);" title="' . $MSG['read_time'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['read_time'][$sysSession->lang];
                    echo ($sortby == 'rss') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td_B('align="center" nowrap style="font-weight: bold" onclick="sort_data(10);" title="' . $MSG['learn_times'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['learn_times'][$sysSession->lang];
                    echo ($sortby == 'page') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E('');

                showXHTML_td('nowrap', $MSG['online_operate'][$sysSession->lang]);
            showXHTML_tr_E();

            // 導師環境 - 成員管理 - 到課統計
            if (defined('Course_ID')) {
                $class_member = dbGetCol('WM_class_member', 'username', 'class_id='.$sysSession->class_id);
            }

            $count = 1;
            if ($uRS)
            {    // if ($uRS)   begin
                while ($users = $uRS->FetchRow())
                {
                    if (defined('Course_ID') && !in_array($users['username'], $class_member)) continue;    // 導師環境中, 若不是此班級人員則不秀
                    //  while begin
                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                    showXHTML_tr_B($col);
                        showXHTML_td_B('nowrap');
                            showXHTML_input('checkbox', 'target[]', $users['username'], '', empty($users['email'])?' onclick="selPeo(this.checked);" disabled':'onclick="selPeo(this.checked);" ');
                        showXHTML_td_E();

                        showXHTML_td('nowrap', $count++);
                        showXHTML_td('nowrap', $users['username']);

                        $realname = checkRealname($users['first_name'], $users['last_name']);
                        showXHTML_td('style="width: 150px"', htmlspecialchars($realname));

                        if ($role == 'all')
                        {
                            showXHTML_td_B('align="left" nowrap');
                                $temp_role = htmlspecialchars(showRole($users['role']));
                                echo '<div style="width: 100px; overflow:hidden;" title="' . $temp_role . '">' . $temp_role . '</div>';
                            showXHTML_td_E('');
                        }

                        $href = ($users['Slogin_times'] == 0) ? $users['Slogin_times'] : '<a href="javascript:viewDetail(1,\'' . $users['username'] . '\');" class="cssAnchor" >' . $users['Slogin_times'] . '</a>';
                        showXHTML_td('align="right" nowrap', $href);

                        showXHTML_td('nowrap', $users['Slast_login']);
                        $href = ($users['login_times'] == 0) ? $users['login_times'] : '<a href="javascript:viewDetail(2,\'' . $users['username'] . '\');" class="cssAnchor" >' . $users['login_times'] . '</a>';
                        showXHTML_td('align="right" nowrap', $href);
                        showXHTML_td('nowrap', $users['last_login']);
                        $href = ($users['post_times'] == 0) ? $users['post_times']:'<a href="javascript:viewDetail(3,\'' . $users['username'] . '\');" class="cssAnchor" >' . $users['post_times'] . '</a>';
                        showXHTML_td('align="right" nowrap', $href);
                        showXHTML_td('align="right" nowrap', $users['dsc_times']);
                        $href = ($users['rss'] == 0) ? zero2gray(sec2timestamp(intval($users['rss']))) : '<a href="javascript:viewDetail(4,\'' . $users['username'] . '\');" class="cssAnchor" >' . zero2gray(sec2timestamp(intval($users['rss']))) . '</a>';
                        showXHTML_td('align="right" nowrap', $href);
                        $href = ($users['page'] == 0) ? $users['page'] : '<a href="javascript:viewDetail(5,\'' . $users['username'] . '\');" class="cssAnchor" >' . $users['page'] . '</a>';
                        showXHTML_td('align="right" nowrap', $href);
                        $href = '<a href="javascript:viewLog(\'' . $users['username']  . '\');" class="cssAnchor" >View</a>';
                        showXHTML_td('nowrap', $href);
                    showXHTML_tr_E();
                }    // while end
            }    // if ($uRS)   end

                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td_B('colspan="' . $colspan . '" nowrap');
                    showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', ' id="btnSel2" class="cssBtn" onclick="selected_box();"');

                    echo $MSG['select_role'][$sysSession->lang];
                    $roles = array(
                        'auditor'    => $MSG['auditor'][$sysSession->lang],
                        'student'    => $MSG['student'][$sysSession->lang],
                        'assistant'  => $MSG['assistant'][$sysSession->lang],
                        'instructor' => $MSG['instructor'][$sysSession->lang],
                        'teacher'    => $MSG['teacher'][$sysSession->lang],
                        'all'        => $MSG['all'][$sysSession->lang]
                    );

                    showXHTML_input('select', 'role', $roles, $role , 'onchange="location.replace(\''.$_SERVER['PHP_SELF'].'?role=\' + this.value + \'&course_id='.$course_id.'\');"');

                    showXHTML_input('button', '', $MSG['mail_picked'][$sysSession->lang], '', 'class="cssBtn" onclick="mailto();"');
                    showXHTML_input('button', '', $MSG['export_info'][$sysSession->lang], '', 'class="cssBtn" onclick="exportInfo();"');
                    showXHTML_input('radio', 'doctype1', array('xml' => 'XML','htm' => 'HTML'), 'xml','onclick="changeRadio(this.value);"');
                    showXHTML_td_E();
                showXHTML_tr_E();

            showXHTML_table_E();
            showXHTML_form_E();
            showXHTML_td_E();
        showXHTML_tr_E();
        showXHTML_table_E();

        showXHTML_form_B('method="POST" action="stud_mail.php"', 'mailForm');
        showXHTML_input('hidden', 'to');
        showXHTML_form_E();

        showXHTML_form_B('method="POST" action="stud_zip.php" target="empty"', 'zipForm');
        showXHTML_input('hidden', 'export_data');
        showXHTML_input('hidden', 'export_file');
        showXHTML_form_E();

        //  排序
        showXHTML_form_B('action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline"', 'sortFm');
            showXHTML_input('hidden', 'sortby', intval($_POST['sortby']));
            showXHTML_input('hidden', 'order' , $order );
            showXHTML_input('hidden', 'role'  , $role  );
            if (defined('Course_ID')) {
                showXHTML_input('hidden', 'course_id'  , $course_id);
            }
        showXHTML_form_E();
    showXHTML_body_E();

?>

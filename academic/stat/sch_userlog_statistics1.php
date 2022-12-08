<?php
    /**
     * 學校統計資料 - User log 統計 - 動作代號
     *
     * 建立日期：2004/08/16
     * @author  Amm Lee
     * @version $Id: sch_userlog_statistics1.php,v 1.1 2010/02/24 02:38:43 saly Exp $
     * @copyright 2003 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/sch_statistics.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

        set_time_limit(0);

    $sysSession->cur_func = '1500200100';
    $sysSession->restore();
    if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $envAry = array('WM_log_classroom', 'WM_log_director', 'WM_log_manager', 'WM_log_teacher', 'WM_log_others');
    //  查詢那個 log 的 table
    $qtable      = intval($_POST['query_table']);
    $query_table = $envAry[$qtable];
    $query       = ' B.username != "" ';
    $sort_key    = array(null, 'B.username','B.log_time', 'B.function_id','A.caption', 'B.remote_address');
    $s           = min(max(intval($_POST['s']), 1), 6);
    $d           = intval($_POST['d']) ^ 1;

    // 降冪 或 升冪
    if ($d){
        $key_state = 'up';
    }else{
        $key_state = 'down';
    }

    $query_user  = trim($_POST['cond_username']);
    $cond_from   = trim($_POST['cond_from']);
    $cond_to     = trim($_POST['cond_to']);
    $cond_ip     = trim($_POST['cond_ip']);
    $function_id = trim($_POST['function_id']);

    if (is_string($_POST['cond'])){
        $cond_str   = $_POST['cond'];
        $cond_array = explode(',',$_POST['cond']);
        $cond_num   = count($cond_array);
    }else if (is_array($_POST['cond'])) {
        $cond_str   = implode(',',$_POST['cond']);
        $cond_array = $_POST['cond'];
        $cond_num   = count($cond_array);
    }

    if ($cond_num > 0){
        for ($i = 0;$i < $cond_num;$i++){
            switch ($cond_array[$i]){
                case 1:  // 帳號
                    $query_user = escape_LIKE_query_str(trim($_POST['cond_username']));
                    $query .= ' and B.username like "%' . $query_user . '%" ';

                    break;
            case 2:        // 時間
                    // 判斷日期格式是否合法
                    if (ereg('^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}$',$cond_from)
                        &&
                        ereg('^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}$',$cond_to)
                       ){

                        $query .= ' and B.log_time  ' .
                                  ' between "' . trim($cond_from) . ' 00:00:00" ' .
                                  ' and "' .
                                    trim($cond_to) . ' 23:59:59" ';
                    }

                    break;
                case 3:        // 來自哪個位址

                    $query .= ' and B.remote_address like "%' . $cond_ip . '%" ';
                    break;
                case 4:     //  功能編號

                    if (is_numeric($function_id)){
                        $query .= ' and B.function_id =' . $function_id;
                    }
                    break;
            }
        }

        $query .= ' and result_id = 0'; // 只秀出成功log
    }

    if (isset($_POST['page_num'])){
        $page_num = intval($_POST['page_num']);
    }else{
        $page_num = sysPostPerPage;
    }

    $sqls1 = 'select count(*) as num ' .
             ' from WM_acl_function as A ,' . $query_table . ' as B ' .
             '  where A.function_id = B.function_id ' .
             '  and ' . $query;

    chkSchoolId('WM_acl_function');
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    $RS = $sysConn->Execute($sqls1);

    if ($RS->RecordCount() > 0){
        while ($row_rs = $RS->FetchRow()){
            $all_page = $row_rs['num'];
        }
    }else{
        $all_page = 0;
    }

    $total_page = ceil($all_page / $page_num);

    if (! isset($_POST['page_no'])){
        if ($total_page > 0){
            $cur_page    = 1;
            $limit_begin = (($cur_page -1)* $page_num);
            $limit_str   = ' limit ' . $limit_begin . ',' . $page_num;
        }else if ($total_page == 0){
            $cur_page    = 0;
        }

    }else{
        if (($_POST['page_no'] >  0)){
            $cur_page    = intval($_POST['page_no']);
            if ($cur_page < 0 || $cur_page > $total_page) $cur_page = 1;
            $limit_begin = (($cur_page -1)* $page_num);
            $limit_str   = ' limit ' . $limit_begin . ',' . $page_num;
        }else if ($_POST['page_no'] == 0){
            $cur_page    = 0;
            $limit_str   = '';

        }
    }

    $sqls = 'select B.function_id,A.caption,B.username,B.log_time,B.remote_address ' .
            ' from WM_acl_function as A ,' . $query_table . ' as B ' .
            '  where A.function_id = B.function_id ' .
            '  and ' . $query .
            '  order by ' . $sort_key[$s] .
            (($key_state == 'up') ? ' asc ': ' desc');

    if ($cur_page > 0){
        $RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);
    }else if ($cur_page == 0){
        $RS = $sysConn->Execute($sqls);
    }

    // 記錄到 WM_log_manager
    // $msg = $sysSession->username . ' delete ' . $user[$i];
    // wmSysLog('1100400200',$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$msg);

    $js = <<< EOF

    var MSG_SYS_ERROR = "{$MSG['msg_system_error'][$sysSession->lang]}";
    var theme         = "{$sysSession->theme}";
    var ticket        = "{$ticket}";
    var lang          = "{$lang}";
    var cur_page      = {$cur_page};
    var total_page    = {$total_page};
    var sort_key      = {$s};
    var sort_state    = {$d};

    function s(s){
        var obj           = document.getElementById("CodeFm");
        obj.s.value       = s;
        obj.d.value       = sort_state;
        obj.page_no.value = cur_page;
        obj.action        = 'sch_userlog_statistics1.php';
        window.onunload   = function () {};
        obj.submit();
    }

    function page(n){
        var obj = document.getElementById("CodeFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.s.value = sort_key;
        obj.page_no.value = n;
        switch(n){
            case -1:
                obj.page_no.value = 1;
                break;
            case -2:
                obj.page_no.value = (cur_page-1);
                break;
            case -3:
                obj.page_no.value = (cur_page+1);
                break;
            case -4:
                obj.page_no.value = (total_page);
                break;
            default:
                var page_no = parseInt(n);
        }
        window.onunload = function () {};
        obj.submit();
    }


    var orgload = window.onload;

    window.onload = function () {
        orgload();

        var obj = document.getElementById("toolbar1");
        var txt1 = '';

        if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

        obj = document.getElementById("toolbar2");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
    };

EOF;

    showXHTML_head_B($MSG['title102'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('include', 'sch_statistics.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E('');

    showXHTML_body_B('');

        showXHTML_table_B('width="600" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
            showXHTML_tr_B('');
                showXHTML_td_B('');
                    $ary[] = array($MSG['title102'][$sysSession->lang], 'tabs');
                    showXHTML_tabs($ary, 1);
                showXHTML_td_E('');
            showXHTML_tr_E('');

            showXHTML_tr_B('');
                showXHTML_td_B('valign="top" id="CGroup" ');
                    showXHTML_form_B('action="sch_userlog_statistics1.php" method="post" enctype="multipart/form-data" target="_self" style="display:inline"', 'CodeFm');
                        showXHTML_table_B('width="100%" id ="mainTable" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                            showXHTML_input('hidden', 's', '', '', '');
                            showXHTML_input('hidden', 'd', '', '', '');
                            showXHTML_input('hidden', 'page_no', '', '', '');
                            showXHTML_input('hidden', 'query_table', $qtable, '', '');
                            showXHTML_input('hidden', 'cond', $cond_str, '', '');
                            showXHTML_input('hidden', 'cond_username', $query_user, '', '');
                            showXHTML_input('hidden', 'cond_from', $cond_from, '', '');
                            showXHTML_input('hidden', 'cond_to', $cond_to, '', '');
                            showXHTML_input('hidden', 'cond_ip', $cond_ip, '', '');
                            showXHTML_input('hidden', 'function_id', $function_id, '', '');
                            showXHTML_input('hidden', 'page_num', $page_num, '', '');
                            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                            showXHTML_tr_B($col);
                                showXHTML_td_B('colspan="5" id="toolbar1"');
                                    echo $MSG['page'][$sysSession->lang];
                                    $P = $total_page > 0 ? @array_merge((array)$MSG['all'][$sysSession->lang], (array)range(1,$total_page)) : array($MSG['all'][$sysSession->lang]);
                                    showXHTML_input('select', '', $P, $cur_page, 'class="cssInput" onchange="page(this.value);"');
                                    echo '&nbsp;&nbsp;';
                                    showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang]  , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0)) ?          'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['switch_page'][$sysSession->lang]);
                                    showXHTML_input('button', 'prevBtn1' , $MSG['prev'][$sysSession->lang]    , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?           'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['switch_page1'][$sysSession->lang]);
                                    showXHTML_input('button', 'nextBtn1' , $MSG['next'][$sysSession->lang]    , '', 'id="nextBtn1" class="cssBtn" '  . ((($cur_page==0) || ($cur_page==$total_page))? 'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['switch_page2'][$sysSession->lang]);
                                    showXHTML_input('button', 'lastBtn1' , $MSG['last1'][$sysSession->lang]   , '', 'id="lastBtn1" class="cssBtn" '  . ((($cur_page==0) || ($cur_page==$total_page))? 'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['switch_page3'][$sysSession->lang]);
                                    showXHTML_input('button', 'go_back'  , $MSG['title149'][$sysSession->lang], '', 'id="go_back" class="cssBtn" '   . 'onclick="do_fun(6);" ' . ' title=' . $MSG['title149'][$sysSession->lang]);
                                showXHTML_td_E('');
                            showXHTML_tr_E('');

                            $topics = array($MSG['title130'][$sysSession->lang],$MSG['title145'][$sysSession->lang],$MSG['title134'][$sysSession->lang],$MSG['title146'][$sysSession->lang], $MSG['title147'][$sysSession->lang]);

                            showXHTML_tr_B('class="cssTrHead"');
                                foreach($topics as $x => $item){
                                        showXHTML_td('align="center" style="font-weight: bold" nowrap', sprintf('<a href="javascript:s(%d)">%s%s</a>', $x+1, $item, ($x+1==$s ? sprintf('<img src="/theme/default/learn/dude07232001%s.gif" border="0" align="absmiddl">', $d ? 'up' : 'down'):'')));
                                  }
                            showXHTML_tr_E('');

                            if ($RS->RecordCount() > 0){
                                while ($RS1 = $RS->FetchRow()){
                                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                                    showXHTML_tr_B($col);
                                        showXHTML_td('', $RS1['username']);
                                        showXHTML_td('', $RS1['log_time']);
                                        showXHTML_td('', $RS1['function_id']);
                                        showXHTML_td('', $RS1['caption']);
                                        showXHTML_td('', $RS1['remote_address']);
                                    showXHTML_td_E('');
                                }
                            }else{
                                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                                showXHTML_tr_B($col);
                                    showXHTML_td('colspan="5" align="center"', $MSG['title141'][$sysSession->lang]);
                                showXHTML_tr_E('');
                            }

                            // 換頁與動作功能列 (function line)
                            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                            showXHTML_tr_B($col);
                                showXHTML_td('colspan="5" nowrap id="toolbar2"', '&nbsp;');
                            showXHTML_tr_E('');

                        showXHTML_table_E('');
                    showXHTML_form_E('');
                showXHTML_td_E('');
            showXHTML_tr_E('');
        showXHTML_table_E('');

    showXHTML_body_E('');
?>

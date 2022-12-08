<?php
    /**************************************************************************************************
    *                                                                                                 *
    *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                              *
    *                                                                                                 *
    *		Programmer: cch
    *       SA        : saly                                                                         *
    *		Creation  : 2014/5/21                                                                      *
    *		work for  : 評量表列表                                 *
    *		work on   : Apache 1.3.41, MySQL 5.1.59 , PHP 4.4.9
    *                                 *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/teacher_settutor.php');
    require_once(sysDocumentRoot . '/lang/peer_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/username.php');

    $sysSession->cur_func = '172100600';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    // 設定車票 (set ticket)
    setTicket();

    $ticket_create = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Create' . $sysSession->username);
    $ticket_edit   = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit'   . $sysSession->username);
    $ticket_delete = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Delete' . $sysSession->username);

    //  每頁有幾筆
    $page_num = max(sysPostPerPage, 1);

    $icon_up = '<img src="/theme/default/teach/dude07232001up.gif" border="0" align="absmiddl">';
    $icon_dn = '<img src="/theme/default/teach/dude07232001down.gif" border="0" align="absmiddl">';

    $lang = strtolower($sysSession->lang);

    $tmp_colspan = 8;

    $_POST['sby1'] = intval($_POST['sby1']);
    switch ($_POST['sby1']) {
        case 1 :
            $sortby = 'c.caption';
            break;
        case 2 :
            $sortby = 'concat(a.first_name, a.last_name)';
            break;
        case 4 :
            $sortby = 'c.enable';
            break;
        default:
            $_POST['sby1'] = 3;
            $sortby = 'c.create_time';
    }
    $_POST['oby1'] = trim($_POST['oby1']);
    $orderby = (in_array($_POST['oby1'], array('asc', 'desc'))) ? $_POST['oby1'] : 'asc';

    $self_level = aclCheckRole($sysSession->username, ($sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant']), $sysSession->course_id, true) &
                  ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
    $self_level = array_search($self_level, $sysRoles);

    $sqls = 'select c.eva_id, c.caption, c.enable, c.creator, c.create_time, a.first_name, a.last_name from WM_term_major as b, WM_evaluation as c ,WM_user_account as a where b.course_id = %COURSE_ID% and a.username = c.creator and a.username = b.username and b.role&' .
		($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);

    $sqls = str_replace('%COURSE_ID%', $sysSession->course_id, $sqls);

    if ($_POST['cond_type'] != '') {

        $type = intval($_POST['cond_type']);

        $query_txt = trim($_POST['queryTxt']);
        $query_txt1 = htmlspecialchars(stripslashes($query_txt));

        if ($query_txt != '' && $query_txt != $MSG['query_teacher'][$sysSession->lang]) {
            $query_txt = escape_LIKE_query_str(addslashes($query_txt));

            switch ($type){
                case 0:// 名稱
                    $query = " and c.caption like '%" . $query_txt . "%' ";
                    break;
                case 1:// 帳號
                    $query = " and c.creator like '%" . $query_txt . "%' ";
                    break;
                case 2:// 姓名
                    $query = ' and if(a.first_name REGEXP "^[0-9A-Za-z _-]$" && a.last_name REGEXP "^[0-9A-Za-z _-]$", concat(a.first_name, " ", a.last_name), concat(a.last_name, a.first_name)) LIKE "%' . $query_txt . '%" ';
                    break;
            }
        }
    }
    // 顯示使用
    $sqls .= "{$query} order by {$sortby} {$orderby}";

    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    $RS = $sysConn->Execute($sqls);
    $total_item  = $RS ? $RS->RecordCount() : 0;
    $total_page  = max(1, ceil($total_item / $page_num));
    $cur_page    = isSet($_POST['page_no']) ? max(0, min($_POST['page_no'], $total_page)) : min(1, $total_page);
    $limit_begin = (($cur_page-1) * $page_num);
    if ($cur_page != 0) $RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);

    $js = <<< BOF
 // //////////////////////////////////////////////////////////////////////////
    var theme = "{$sysSession->theme}";
    var ticket2 = "{$ticket_edit}";
    var ticket3 = "{$ticket_delete}";

    var lang = "{$lang}";
    var nowIdx = 0, maxIdx = 0;
    var nowIdx2 = 0;
    var listNum = 10;   // 每頁列出幾筆資料

    var cur_page = {$cur_page};
    var total_page = {$total_page};
    var queryTxt = "{$query_txt}";

 // //////////////////////////////////////////////////////////////////////////

    function checkData(type) {
        var obj = document.delForm;
        var nodes = obj.getElementsByTagName('input');
        var ret = '';
        var msg = '';

        obj.state.value = type;
        msg = "{$MSG['checklist_del_nochoice'][$sysSession->lang]}";

        if (obj == null) return false;

        //  檢查是否有勾選資料 (begin)
        for(var i=1; i<nodes.length-1; i++){
           if (nodes.item(i).type == 'checkbox' && nodes.item(i).checked){
               if (nodes.item(i).value != ''){

                  ret += (nodes.item(i).value + ',');
               }
            }
        }
        //  檢查是否有勾選資料 (end)

        if (ret.length == 0){
            alert(msg);
            return false;
        }else{
            ret = ret.replace(/,$/, '');
            obj.user_id.value=ret;
            obj.ticket.value = ticket3;
        }

        if (confirm("{$MSG['title41'][$sysSession->lang]}")){
            obj.action = 'checklist_save.php';
            obj.submit();
        }

    }

    var MSG_SELECT_ALL    = "{$MSG['msg_select_all'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL = "{$MSG['msg_cancel_all'][$sysSession->lang]}";
    /**
     * 同步全選或全消的按鈕與 checkbox
     * @version 1.0
     **/
    var nowSel = false;
    function selected_box() {
        var obj  = document.getElementById("ckbox");
        var btn1 = document.getElementById("btnSel1");
        if ((obj == null) || (btn1 == null) ) return false;
        nowSel = !nowSel;

        obj.checked = nowSel;
        btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

        nodes = document.getElementsByTagName("input");
        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("exclude");
            if ((nodes[i].type == "checkbox") && (attr == null))
                nodes[i].checked = nowSel;
      }

       var obj = document.getElementById('TeacherList');
       obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
    }

    function chgPageSort(val) {
        var obj = document.delForm;

        if (obj.sby1.value == val)
        {
            obj.oby1.value = (obj.oby1.value == "asc") ? "desc" : "asc";
        }
        else
        {
            obj.sby1.value = val;
        }

		obj.page_no.value = cur_page;

        obj.action = "checklist_list.php";
        obj.submit();
    }

    function editChecklist(val) {

        var type = 'Teacher';
        var obj = document.getElementById("actForm");
        if (obj == null) return false;

        obj.ticket.value = ticket2;
        obj.evaid.value = val;

        obj.submit();
    }

    function selUser(val){
        var obj = null,nodes = null;
        var total_num = 0,cnt = 0,attr = null;

        obj = document.getElementById("TeacherList");
        nodes = obj.getElementsByTagName("input");

        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("exclude");
            if ((nodes[i].type == "checkbox") && (attr == null)) {
                total_num++;
                if (nodes[i].checked) cnt++;
            }
        }

        nowSel = (total_num == cnt);
        document.getElementById("ckbox").checked = nowSel;

        var btn1 = document.getElementById("btnSel1");
        btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

        var obj = document.getElementById('TeacherList');
        obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
    }

    function QueryTA(){
        var obj = document.delForm;
        obj.action = 'checklist_list.php';
        obj.submit();

    }

    function act(val) {

        var obj = document.delForm;

        switch(val){
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
                obj.page_no.value = parseInt(val);
        }
        obj.action = 'checklist_list.php';
        obj.submit();
    }

    function checkWhetherAll()
    {
        var obj   = document.getElementById('delForm');
        var nodes = obj.getElementsByTagName('input');
        var btn1  = document.getElementById("btnSel1");

        var on=0, off=0;
        for(var i=1; i<nodes.length; i++){
            if (nodes.item(i).type == 'checkbox' && nodes.item(i).name == 'ckEvaid[]')
                if (nodes.item(i).checked) on++; else off++;
        }

        if (on > 0 && off == 0) { // 全選
            selectItem(true);
            nowSel = true
        }
        else
        {
            if (off > 0){		//   未全選所有的 checkbox
                obj  = document.getElementById("ckbox");
                obj.checked = false;
                nowSel = false;
                if (btn1 != null) btn1.value = MSG_SELECT_ALL;

                var obj = document.getElementById('TeacherList');
                obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
            }
        }
    }

    function selectItem(selAll)
    {
        var obj = document.getElementById('TeacherList');
        var nodes = obj.getElementsByTagName('input');
        for(var i=15; i<nodes.length; i++){
            if (nodes.item(i).type == 'checkbox')
                nodes.item(i).checked = selAll;
        }
        var obj1  = document.getElementById("ckbox");
        if (obj1  != null) {
            obj1.checked = selAll;
        }
        var btn1 = document.getElementById("btnSel1");
        if (btn1 != null) btn1.value = selAll ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

        var obj = document.getElementById('TeacherList');
        obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
        // obj.rows[(obj.rows.length-1)].cells[1].innerHTML = obj.rows[1].cells[1].innerHTML;
    }

    window.onload = function () {
        var obj = document.getElementById('TeacherList');
        obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
    };

    $(document).ready(function() {
        $("#TeacherList tbody tr td").click(function(){
            if ($(this).index() !== 0) {
                if ($(this).parent().find("input[name='ckEvaid[]']").attr('checked') !== 'checked') {
                    $(this).parent().find("input[name='ckEvaid[]']").attr('checked', true);
                } else {
                    $(this).parent().find("input[name='ckEvaid[]']").attr('checked', false);
                }
            }
        });
    });

BOF;

    // 開始呈現 HTML
    showXHTML_head_B($MSG['title'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B();
        echo "<div align=\"center\">\n";
        showXHTML_table_B('width="950" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
            showXHTML_tr_B();
                showXHTML_td_B();
                    $ary[] = array($MSG['rating_scale_management'][$sysSession->lang], 'tabs');
                    showXHTML_tabs($ary, 1);
                showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B();
                showXHTML_td_B('valign="top" id="CGroup" ');

                    showXHTML_form_B('style="display:inline;" method="post" ', 'delForm');
                        showXHTML_input('hidden', 'ticket' , '', '', '');
                        showXHTML_input('hidden', 'sby1'   , $_POST['sby1'], '', 'id="sby1"');
                        showXHTML_input('hidden', 'oby1'   , $orderby, '', 'id="oby1"');
                        showXHTML_input('hidden', 'user_id', '', '', '');
                        showXHTML_input('hidden', 'state'  , '', '', '');
                        showXHTML_input('hidden', 'page_no', '', '', '');

                        showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="TeacherList" class="cssTable"');
                            showXHTML_tr_B('class="cssTrHead"');
                                showXHTML_td_B('colspan="' . $tmp_colspan . '" ');
                                    showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
                                        //  查詢教師
                                        showXHTML_tr_B();
                                            echo $MSG['teacher_query'][$sysSession->lang];

                                            if ($query_txt == '') $query_txt1 = $MSG['query_teacher'][$sysSession->lang];
                                            showXHTML_input('text', 'queryTxt', $query_txt1, '', 'id="queryTxt" class="cssInput" onclick="this.value=\'\'"');

                                            $role_array = array(0=>$MSG['exam_name'][$sysSession->lang],
                                                                1=>$MSG['user_account'][$sysSession->lang],
                                                                2=>$MSG['real_name'][$sysSession->lang]);

                                            if (strlen($type) == 0) $type = 0;
                                            echo '&nbsp;';

                                            showXHTML_input('select', 'cond_type', $role_array, $type, 'size="1" class="cssInput" ');
                                            echo '&nbsp;';

                                            showXHTML_input('button', '', $MSG['query'][$sysSession->lang], '', 'style="24" class="cssBtn" onclick="QueryTA()"');
                                            echo '&nbsp;';
                                            showXHTML_input('button', '', $MSG['add'][$sysSession->lang], '', 'id="addBtn1" onclick="location.replace(\'checklist_new.php\')" class="cssBtn"');
                                        showXHTML_tr_E();
                                    showXHTML_table_E();
                                showXHTML_td_E();
                            showXHTML_tr_E();

                            showXHTML_tr_B('class="cssTrEvn"');
                                showXHTML_td_B('colspan="' . $tmp_colspan . '"  id="toolbar1"');
                                    showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', ' id="btnSel1" class="cssBtn" onclick="selected_box();"');

                                    echo $MSG['page3'][$sysSession->lang] , '&nbsp;';

                                    $P = range(0, $total_page);
                                    $P[0] = $MSG['all'][$sysSession->lang];

                                    showXHTML_input('select', '', $P, $cur_page, 'size="1" onchange="act(this.value);" class="cssInput"');
                                    showXHTML_input('button', 'firstBtn1', $MSG['first'][$sysSession->lang] , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?                     'disabled="true" ' : 'onclick="act(-1);"'));
                                    showXHTML_input('button', 'prevBtn1' , $MSG['prev'][$sysSession->lang]  , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?                     'disabled="true" ' : 'onclick="act(-2);"'));
                                    showXHTML_input('button', 'nextBtn1' , $MSG['next'][$sysSession->lang]  , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==0) ||  ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="act(-3);"'));
                                    showXHTML_input('button', 'lastBtn1' , $MSG['last'][$sysSession->lang]  , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==0) ||  ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="act(-4);"'));
                                    showXHTML_input('button', ''         , $MSG['delete'][$sysSession->lang], '', 'class="cssBtn" onclick="checkData(\'D\');"');
                                showXHTML_td_E();
                            showXHTML_tr_E();

                            showXHTML_tr_B('class="cssTrHead"');
                                showXHTML_td_B('align="left" nowrap="noWrap" ');
                                    showXHTML_input('checkbox', '', '', '', 'id = "ckbox" onclick="selected_box();" " exclude="true"' . 'title=' . $MSG['select_all'][$sysSession->lang]);
                                showXHTML_td_E();

                                /*名稱*/
                                showXHTML_td_B(' align="center" id="user_account" nowrap="noWrap" title="' . $MSG['exam_name'][$sysSession->lang] . '"');
                                    echo '<a class="cssAnchor" href="javascript:chgPageSort(1);" >',
                                          $MSG['exam_name'][$sysSession->lang],
                                          ($_POST['sby1'] == 1 ? ($orderby == 'desc' ? $icon_dn : $icon_up) : ''),
                                         '</a>';
                                showXHTML_td_E();

                                /*建立者*/
                                showXHTML_td_B(' align="center" id="user_account" nowrap="noWrap" title="' . $MSG['creator'][$sysSession->lang] . '"');
                                    echo '<a class="cssAnchor" href="javascript:chgPageSort(2);" >',
                                          $MSG['creator'][$sysSession->lang],
                                          ($_POST['sby1'] == 2 ? ($orderby == 'desc' ? $icon_dn : $icon_up) : ''),
                                         '</a>';
                                showXHTML_td_E();

                                /*建立時間*/
                                showXHTML_td_B(' align="center" id="user_account" nowrap="noWrap" title="' . $MSG['create_date'][$sysSession->lang] . '"');
                                    echo '<a class="cssAnchor" href="javascript:chgPageSort(3);" >',
                                          $MSG['create_date'][$sysSession->lang],
                                          ($_POST['sby1'] == 3 ? ($orderby == 'desc' ? $icon_dn : $icon_up) : ''),
                                         '</a>';
                                showXHTML_td_E();

                                /*狀態*/
                                showXHTML_td_B(' align="center" id="user_account" nowrap="noWrap" title="' . $MSG['th_status'][$sysSession->lang] . '"');
                                    echo '<a class="cssAnchor" href="javascript:chgPageSort(4);" >',
                                          $MSG['th_status'][$sysSession->lang],
                                          ($_POST['sby1'] == 4 ? ($orderby == 'desc' ? $icon_dn : $icon_up) : ''),
                                         '</a>';
                                showXHTML_td_E();

                                /*被引用*/
                                showXHTML_td_B(' align="center" id="real_name" nowrap="noWrap" title="' . $MSG['reference_count'][$sysSession->lang] . '"');
                                    echo $MSG['reference_count'][$sysSession->lang];
                                showXHTML_td_E();

                                /*被評分*/
                                showXHTML_td_B('align="center" id="msg_status" nowrap="noWrap" title="' . $MSG['rating_count'][$sysSession->lang] . '"');
                                    echo $MSG['rating_count'][$sysSession->lang];
                                showXHTML_td_E();

                                showXHTML_td('align="center" nowrap ', $MSG['modify'][$sysSession->lang]);
                            showXHTML_tr_E();

                            if ($RS->RecordCount() > 0) {
                                while ($RS1 = $RS->FetchRow()){
                                    $RS1['level'] = array_search($RS1['level'], $sysRoles);
                                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

                                    showXHTML_tr_B($col);

                                        // 取被引用份數
                                        $sql = 'select exam_id from WM_qti_peer_test where assess_way = \'' . $RS1['eva_id'] . '\'';
                                        $rs = $sysConn->Execute($sql);
                                        $reference_count  = $rs ? $rs->RecordCount() : 0;

                                        // 取被評分份數
                                        $sql = 'select distinct exam_id from WM_qti_peer_result_eva where eva_id = \'' . $RS1['eva_id'] . '\'';
                                        $rs = $sysConn->Execute($sql);
                                        $rating_count  = $rs ? $rs->RecordCount() : 0;

                                        showXHTML_td_B('width="10"');
                                            // 被評分份數為0時才可以刪除
                                            if ($rating_count === 0) {
                                                showXHTML_input('checkbox', 'ckEvaid[]', $RS1['eva_id'], '', 'onclick="checkWhetherAll(this);"');
                                            }
                                        showXHTML_td_E();
                                        showXHTML_td('', sprintf('<div style="overflow: auto; width: 400px; word-wrap: break-word;">%s</div>', htmlspecialchars($RS1['caption'])));
                                        $realname = checkRealname($RS1['first_name'],$RS1['last_name']);
                                        showXHTML_td('', $RS1['creator'] . '(' . $realname . ')');
                                        showXHTML_td('align="center"', $RS1['create_time']);

                                        switch ($RS1['enable']) {
                                            case '0':
                                                $viewStatus = $MSG['th_disable'][$sysSession->lang];
                                                break;

                                            case '1':
                                                $viewStatus = $MSG['th_enable'][$sysSession->lang];
                                                break;

                                            case '2':
                                                $viewStatus = $MSG['th_modifying'][$sysSession->lang];
                                                break;
                                        }
                                        showXHTML_td('', $viewStatus);
                                        showXHTML_td('align="center"', $reference_count);
                                        showXHTML_td('align="center"', $rating_count);

                                        showXHTML_td_B('');
                                            showXHTML_input('button', 'ModifyBtn', $MSG['modify'][$sysSession->lang], '', 'onclick="editChecklist(\'' . $RS1['eva_id'] . '\')"');
                                        showXHTML_td_E();
                                    showXHTML_tr_E();


                                }
                            } else {
                                showXHTML_tr_B('class="cssTrEvn"');
                                    showXHTML_td('align="center" colspan="' . $tmp_colspan . '"  id="toolbar2"', $MSG['no_keyword'][$sysSession->lang]);
                                showXHTML_tr_E();
                            }

                            showXHTML_tr_B('class="cssTrEvn"');
                                showXHTML_td_B('colspan="' . $tmp_colspan . '"  id="toolbar2"');
                                showXHTML_td_E();
                            showXHTML_tr_E();
                        showXHTML_table_E();
                    showXHTML_form_E();

                showXHTML_td_E();
            showXHTML_tr_E();
        showXHTML_table_E();
        echo "</div>\n";
        showXHTML_form_B('action="checklist_modify.php" method="post"', 'actForm');
            showXHTML_input('hidden', 'ticket', '', '', '');
            showXHTML_input('hidden', 'evaid', '', '', '');
            showXHTML_input('hidden', 'page_no', $cur_page, '', '');
        showXHTML_form_E();

    showXHTML_body_E();
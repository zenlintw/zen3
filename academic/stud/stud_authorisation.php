<?php
    /**
     * 審核帳號
     * $Id: stud_authorisation.php,v 1.2 2010/05/24 05:55:33 small Exp $
     *
     *    程式架構：一次查詢 10 筆資料，將勾選的checkbox資料帶到下一頁中
     *    進入下一頁之前將變數紀錄到sysbar的頁面，進入下一頁之後再從sysbar中
     *  取出已經夠選過的資料做一比對。
     *
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/stud_account.php');
    require_once(sysDocumentRoot . '/lang/stud_authorisation.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/username.php');

    $sysSession->cur_func = '0400400400';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    // 尋找人員的陣列
    $search_ary = array(
        'real'    => $MSG['realname'][$sysSession->lang],
        'account' => $MSG['username'][$sysSession->lang],
        'email'   => $MSG['email'][$sysSession->lang],
    );
    
    if (isset($_POST['sType'])) {
        $sType = trim($_POST['sType']);
        $sWord = trim($_POST['sWord']);
    }
    
    if (!empty($sType) && isset($sWord)) {
        switch ($sType) {
            case 'real'    :    // 姓名
                if (isset($sWord)){
                    if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312') {
                        $where = sprintf('and CONCAT(IFNULL(`last_name`,""), IFNULL(`first_name`,"")) like "%%%s%%"', escape_LIKE_query_str(addslashes($sWord)));
                    } else {
                        $where = sprintf('and CONCAT(IFNULL(`first_name`,""), " ", IFNULL(`last_name`,"")) like "%%%s%%"', escape_LIKE_query_str(addslashes($sWord)));
                    }
                }
                break;
            case 'account' :    // 帳號
                if (isset($sWord)){
                    $where = 'AND T1.username like "%' . escape_LIKE_query_str(addslashes($sWord)) . '%" ';
                }
                break;
            case 'email'   :    // E-mail
                if (isset($sWord)){
                    $where = 'AND T1.email like "%' . escape_LIKE_query_str(addslashes($sWord)) . '%" ';
                }
                break;
            default:
                $where = 'AND 1=1';
        }
    }    
    // 設定車票
    setTicket();

    $mail_pattern  = sysDocumentRoot . "/base/$sysSession->school_id/door/verify_acccount_" . $sysSession->lang . ".mail";    // 信件範本
    $att_file_path = sysDocumentRoot . "/base/$sysSession->school_id/door/verify_acccount";                                // 附檔路徑
    // 排序的 icon
    $icon_up = '&nbsp;<img border="0" align="asbmiddle" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/dude07232001up.gif">';
    $icon_dw = '&nbsp;<img border="0" align="asbmiddle" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/dude07232001down.gif">';

    $current_page = 1;

    /*
    * 排序
    */
    // 預設為註冊時間倒序排列
    if (!isset($_POST['sortby'])) {
        $_POST['sortby'] = 6;
        $_POST['order'] = 'desc';
    }
    $sortby    = min(6, max(1, $_POST['sortby']));
    $sortbyArr = array('', 'T1.username', 'realname', 'gender', 'email', 'birthday', 'reg_time');
    $order     = $_POST['order'] == 'desc' ? 'desc' : 'asc';

    if ($sortbyArr[$sortby] == 'realname')
    {
        $cond_order = sprintf(' order by first_name %s,last_name %s ', $order, $order);
    }
    else
    {
        $cond_order = " order by {$sortbyArr[$sortby]} {$order} ";
    }

    # ===================================================================================
    #    主程式開始
    # ===================================================================================

        # ===================================================================================
        #    Javascript 程式碼開始
        # ===================================================================================

        $js = <<< EOB

        var MSG_SELECT_CANCEL = "{$MSG['disp_all'][$sysSession->lang]}";
        var MSG_SELECT_ALL = "{$MSG['select_all'][$sysSession->lang]}";

        function chk(val){        // 核可或是刪除時之對應動作(傳送資料到下一頁)
            var ml = '';
            var ss = /,$/;

            document.f1.mode.value = (val=='0')?'check_in':'check_out';

            var msg = (val=='0')?"{$MSG['pass3'][$sysSession->lang]}":"{$MSG['deline2'][$sysSession->lang]}";

            /**
            * 判斷是否有勾選資料
            **/
            var obj = document.getElementById('stud_auth');
            var nodes = obj.getElementsByTagName('input');
            for(var i=0; i<nodes.length; i++){
                if ((nodes.item(i).type == 'checkbox') && (nodes.item(i).checked == true) && (nodes.item(i).value.length > 0)){
                    ml += nodes.item(i).value + ',';
                }
            }
            ml = ml.replace(ss, '');

            if (ml.length == 0){
                alert(msg);
                return false;
            }

            /**
            * 審核不通過並刪除
            **/
            if (val=='1'){
                if (!confirm("{$MSG['deline3'][$sysSession->lang]}")){
                    return false;
                }
            }

            /**
            *  送出核可或是刪除動作前，先將此陣列清空 (delete)
            **/
            document.f1.userarray.value = ml;
            document.f1.action = 'showmessage.php';
            document.f1.submit();
        }


        /***********************
        * 按下全選/全消時的對應對作
        ***********************/
        function editmail(){
            document.f2.submit();
        }

        /***********************
        * 換頁(含按鍵與select物件)
        ***********************/
        function change_page(val){
            var currpage = parseInt(document.f1.pages.value);
            var totalpage = parseInt(document.f1.total_pages.value);
            switch (val){
                case 'top':
                    document.f1.pages.value = 1;
                    document.f1.submit();
                    break;
                case 'up':                // 顯示上一頁
                    currpage --;
                    document.f1.pages.value = (currpage <= 1)?1:currpage;
                    document.f1.submit();
                    break;
                case 'down':            // 顯示下一頁
                    currpage ++;
                    document.f1.pages.value = (currpage > totalpage)?document.f1.total_pages.value:currpage;
                    document.f1.submit();
                    break;
                case 'last':            // 顯示最後一頁
                    document.f1.pages.value = document.f1.total_pages.value;
                    document.f1.submit();
                    break;
                default:                // 以 select 選擇
                    document.f1.pages.value = val;
                    document.f1.submit();
                    break;
                return false;
            }

        }

        /*******************************
        * 處理checkbox被選取及被取消時的動作
        *******************************/
        function deal_chks(obj){
            var nodes = null, attr = null;
            var isSel = "false";
            var cnt = 0;
            var m = 0;

            var obj2 = document.getElementById("f1");
            var nodes = obj2.getElementsByTagName('input');
            if ((nodes == null) || (nodes.length == 0)) return false;

            for(var i=1; i<nodes.length-1; i++){
               if ((nodes.item(i).type == 'checkbox') && (nodes.item(i).value != '')){
                   m++;
                   if (nodes.item(i).checked) cnt++;

                }
            }

            nowSel = (m == cnt);
            // m = (m > 0) ? m - 1 : 0;
           document.getElementById("ckbox").checked = nowSel;

           /*    全選    */
         var btn1 = document.getElementById("btnSel1");
         btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

          var obj = document.getElementById('stud_auth');
          obj.rows[(obj.rows.length-2)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
        }

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

            var nodes        = document.getElementsByTagName('input');
            var nodes_cnt    = nodes.length;

            if (nodes == null) return false;
            for (var i=0; i<nodes_cnt; i++) {
                if (nodes[i].type == "checkbox"){
                    nodes[i].checked = nowSel;
                }
            }

          var obj = document.getElementById('stud_auth');
          obj.rows[(obj.rows.length-2)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
        }

        /*
        * 標題排序
        */
        function chgPageSort(val) {
            var obj = document.getElementById("f1");
            if ((typeof(obj) != "object") || (obj == null)) return false;

            obj.sortby.value = val;
            obj.order.value  = obj.order.value == 'asc' ? 'desc' : 'asc';
            document.f1.submit();
        }

        /*****************************
        * 表單起始時，初始化checkbox的狀態
        *****************************/
        function show_checkbox(){
            /*  button  */
            var obj = document.getElementById('stud_auth');
            obj.rows[(obj.rows.length-2)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
        }

EOB;

    $msgnum         = sysPostPerPage;
    list($cnt)      = dbGetStSr(sprintf('%s.CO_mooc_account',sysDBname), 'count(*) as cnt', "enable='N' ".$where, ADODB_FETCH_NUM);
    $totalpage      = max(1, ceil(intval($cnt) / $msgnum)); // 總頁面數
    $_POST['pages'] = intval($_POST['pages']);
    if ($_POST['pages'] == -1)
    { // 全部顯示
        $cur_page = $_POST['pages'];
        $RS = dbGetStMr(sprintf('%s.CO_mooc_account as T1 left join CO_user_verify as T2 on T1.username=T2.username',sysDBname), 'T1.*,T2.reg_time', "enable='N' {$where} $cond_order", ADODB_FETCH_ASSOC);
    }
    else
    { // 分頁顯示
        $cur_page       = min($totalpage, max(1, $_POST['pages']));
        $_POST['pages'] = $cur_page;
        $start          = ($cur_page - 1) * $msgnum;                                        // 起始頁
        $RS             = dbGetStMr(sprintf('%s.CO_mooc_account as T1 left join CO_user_verify as T2 on T1.username=T2.username',sysDBname), 'T1.*,T2.reg_time', "enable='N' {$where} {$cond_order} limit {$start}, {$msgnum}", ADODB_FETCH_ASSOC);
    }

    showXHTML_head_B($MSG['verify_account'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B('onload="show_checkbox()"');

    $arry[] = array($MSG['verify_account'][$sysSession->lang], 'addTable1');

    showXHTML_table_B('width="960" border="0" cellspacing="0" cellpadding="0"');
        showXHTML_tr_B();
            showXHTML_td_B();
                showXHTML_tabs($arry, 1);
            showXHTML_td_E();
        showXHTML_tr_E();
    showXHTML_table_E();

    showXHTML_form_B('action="' . $PHP_SELF . '" method="post" style="display:inline"', 'f1');
    showXHTML_input('hidden', 'mode', 'dispall', '', '');
    showXHTML_input('hidden', 'total_pages', $totalpage, '', '');            // 紀錄總共有幾頁
    showXHTML_input('hidden', 'pages', $_POST['pages'], '', '');            // 紀錄目前在第幾頁
    showXHTML_input('hidden', 'userarray', '', '', '');                        // 紀錄審核者帳號(以字串傳到下一頁)
    showXHTML_input('hidden', 'sortby', $sortby, '', '');
    showXHTML_input('hidden', 'order', $order, '', '');
        showXHTML_table_B('id="stud_auth" width="960" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
        showXHTML_tr_B('class="cssTrOdd"');
            showXHTML_td_B(' colspan="9"');
            echo $MSG['search_keyword'][$sysSession->lang];
            showXHTML_input('select', 'sType', $search_ary, $sType, 'id="sType" class="cssInput"');
            
            echo $MSG['inside'][$sysSession->lang];
            showXHTML_input('text', 'sWord', htmlspecialchars(stripslashes($sWord)), '', 'id="sWord" size="20"  class="cssInput" onclick="this.value=\'\'"');
            echo $MSG['inside1'][$sysSession->lang] . '&nbsp;&nbsp;';
            
            showXHTML_input('button', '', $MSG['confirm'][$sysSession->lang], '', 'class="cssBtn" onclick="this.form.pages.value=1;this.form.submit()"');
            
            showXHTML_td_E();
        showXHTML_tr_E();
        
            showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td_B(' colspan="9"');
                    showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', ' id="btnSel1" class="cssBtn" onclick="selected_box();"');

                    echo $MSG['page'][$sysSession->lang];
                    echo '<select onchange="change_page(this.value)">';
                    echo '<option value="-1">'.$MSG['all'][$sysSession->lang].'</option>';
                    for ($p=1; $p<$totalpage+1; $p++){
                        if ($p == $_POST['pages'])
                            echo '<option value="'.$p.'" selected>'.$p.'</option>';
                        else
                            echo '<option value="'.$p.'">'.$p.'</option>';
                    }
                    echo '</select>';

                    showXHTML_input('button', 'firstBtn1', $MSG['top'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==-1)) ?          'disabled="true" ' : 'onclick="change_page(\'top\');"') . ' title=' . "{$MSG['top'][$sysSession->lang]}");
                    showXHTML_input('button', 'prevBtn1', $MSG['up'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==-1))?          'disabled="true" ' : 'onclick="change_page(\'up\');"') . ' title=' . "{$MSG['up'][$sysSession->lang]}");
                    showXHTML_input('button', 'nextBtn1', $MSG['down'][$sysSession->lang], '', 'id="nextBtn1" class="cssBtn" ' . ((($cur_page==-1) || ($cur_page==$totalpage))?          'disabled="true" ' : 'onclick="change_page(\'down\');"') . ' title=' . "{$MSG['down'][$sysSession->lang]}");
                    showXHTML_input('button', 'lastBtn1', $MSG['last'][$sysSession->lang], '', 'id="lastBtn1" class="cssBtn" ' . ((($cur_page==-1) || ($cur_page==$totalpage))?          'disabled="true" ' : 'onclick="change_page(\'last\');"') . ' title=' . "{$MSG['last'][$sysSession->lang]}");
                    echo '&nbsp;&nbsp;&nbsp;';
                    showXHTML_input('button', '', $MSG['edit_mail'][$sysSession->lang], '', 'class="cssBtn" onclick="editmail()"');
                showXHTML_td_E();
            showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrHead"');
                showXHTML_td_B('width="20"');
                    showXHTML_input('checkbox', '', '', '', ' id = "ckbox" onclick="selected_box();" " exclude="true"' . 'title=' . $MSG['select_all_cancel'][$sysSession->lang]);
                showXHTML_td_E();
                showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(1);" title="' . $MSG['account'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['account'][$sysSession->lang];
                    echo ($sortby == 1) ? ($order == 'desc' ? $icon_dw : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E();
                showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(2);" title="' . $MSG['name'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['name'][$sysSession->lang];
                    echo ($sortby == 2) ? ($order == 'desc' ? $icon_dw : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E();

                showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(3);" title="' . $MSG['gender'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['gender'][$sysSession->lang];
                    echo ($sortby== 3) ? ($order == 'desc' ? $icon_dw : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E();
                
                showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(6);" title="' . $MSG['reg_time'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['reg_time'][$sysSession->lang];
                    echo ($sortby== 6) ? ($order == 'desc' ? $icon_dw : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E();

                showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(4);" title="' . $MSG['email'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                    echo $MSG['email'][$sysSession->lang];
                    echo ($sortby == 4) ? ($order == 'desc' ? $icon_dw : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E();

                showXHTML_td('align="center" ', $MSG['mobil'][$sysSession->lang]);

                showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(5);" title="' . $MSG['birthday'][$sysSession->lang] . '"');
                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">', $MSG['birthday'][$sysSession->lang];
                    echo ($sortby == 5) ? ($order == 'desc' ? $icon_dw : $icon_up) : '';
                    echo '</a>';
                showXHTML_td_E();

                showXHTML_td('align="center" nowrap', $MSG['detail'][$sysSession->lang]);
            showXHTML_tr_E();

        if ($RS->EOF) {        // 如果沒有審核資料的時候
            showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
                showXHTML_td('colspan="9" align="center"', $MSG['data_empty'][$sysSession->lang]);
            showXHTML_tr_E();
        }

        while (!$RS->EOF) {
            $TD_cnt ++;                    // 每印一筆資料，TD的計數器加一
            showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
                showXHTML_td_B('width="20"');
                    showXHTML_input('checkbox', 'usernames[]', $RS->fields['username'], '', 'onclick="deal_chks(this);"');
                showXHTML_td_E();
                showXHTML_td(' ','<div style="width: 100px; overflow:hidden;" title="' . $RS->fields['username'] . '">' . $RS->fields['username'] . '</div>');
                // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
                $realname = checkRealname($RS->fields['first_name'],$RS->fields['last_name']);
                showXHTML_td('','<div style="width: 100px; word-break: break-all;" title="' . $realname . '">' . $realname . '</div>');
                showXHTML_td_B('align="center"');
                    $img = ($RS->fields['gender'] == 'F') ? 'female.gif' : 'male.gif';
                    echo '<img src="/theme/', $sysSession->theme, '/academic/', $img, '" width="24" height="24" border="0" align="absmiddle">';
                showXHTML_td_E();
                
                if ($RS->fields['reg_time']) {
                    $regTime = $RS->fields['reg_time'];
                } else {
                    $regTime = dbGetOne('WM_log_others', 'max(log_time) log_time', 'function_id = 400200100 AND username = "' . $RS->fields['username'] . '" AND instance = 0 AND department_id = ' . $sysSession->school_id, ADODB_FETCH_NUM);
                }
                showXHTML_td('', $regTime);
                showXHTML_td('','<a href="mailto:' . $RS->fields['email'] . '">' . $RS->fields['email'] . '</a>');
                showXHTML_td('',$RS->fields['cell_phone']);
                showXHTML_td('nowrap',$RS->fields['birthday']);
                showXHTML_td_B('align="center"');
                    echo "<a href='userinfo.php?username=", $RS->fields['username'], "' >",
                         '<img src="/theme/', $sysSession->theme, '/academic/icon_folder.gif" border="0" align="absmiddle">',
                         '</a>';
                showXHTML_td_E();
            showXHTML_tr_E();
            $RS->MoveNext();
        }

            showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td_B(' colspan="9"');
                showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B('class="bg04"');
                showXHTML_td_B('colspan="9" align="center"');
                    showXHTML_input('button', '', $MSG['passed'][$sysSession->lang], '', 'class="cssBtn" onclick="chk(\'0\')"');
                    showXHTML_input('button', '', $MSG['delete'][$sysSession->lang], '', 'class="cssBtn" onclick="chk(\'1\')"');
                showXHTML_td_E('&nbsp;');
            showXHTML_tr_E();
        showXHTML_table_E();
    showXHTML_form_E();
    showXHTML_form_B('action="auth_mail.php" method="post" style="display:inline" ', 'f2');
    showXHTML_form_E();
    showXHTML_body_E();
?>
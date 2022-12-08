<?php
    /**
     * 新增帳號 - 顯示設定的結果
     * $Id: stud_account1.php,v 1.1 2010/02/24 02:38:44 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lang/stud_account.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');

    $sysSession->cur_func = '400300100';
    $sysSession->restore();
    if (!aclVerifyPermission(400300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    /**
     * 安全性檢查
     *     1. 身份的檢查
     *     2. 權限的檢查
     *     3. .....
     **/

    // 設定車票
    setTicket();

    $js = <<< BOF
    // 秀日曆的函數(checkbox)
    function showDateInput(objName, state) {
        var obj = document.getElementById(objName);
        if (obj != null) {
            obj.style.display = state ? "" : "none";
        }
    }

    // 秀日曆的函數
    function Calendar_setup(ifd, fmt, btn, shtime) {
        Calendar.setup({
            inputField  : ifd,
            ifFormat    : fmt,
            showsTime   : shtime,
            time24      : true,
            button      : btn,
            singleClick : true,
            weekNumbers : false,
            step        : 1
        });
    }

    window.onload = function() {
        Calendar_setup("begin_date", "%Y-%m-%d", "begin_date", false);
        Calendar_setup("end_date"  , "%Y-%m-%d", "end_date"  , false);
    };

    function check_field() {
        var sdate = '', edate = '';
        // date field
        if (document.forms[0].ck_begin_date.checked){
            obj = document.getElementById("begin_date");
            sdate = obj.value;
        }
        if (document.forms[0].ck_end_date.checked){
            obj = document.getElementById("end_date");
            edate = obj.value;
        }
        if ((sdate.length > 0) && (edate.length > 0)){
            if (sdate >= edate){
                alert("{$MSG['title88'][$sysSession->lang]}");
                return false;
            }
        }
        return true;
    }

BOF;

    $header    = str_replace('%', '%%', trim($_POST['header']));
    $tail    = str_replace('%', '%%', trim($_POST['tail']));
    $first    = intval($_POST['first']);
    $last    = intval($_POST['last']);
    $len    = intval($_POST['len']);

    $fmt1 = "{$header}%0{$len}d{$tail}";

    $fmt2 = '<span class="color01">' . $header . '</span><span class="color02">%0' .
        $len .'d</span><span class="color03">' . $tail . '</span>';

    // 帳號註冊上限：設定可註冊人數
    if (sysMaxUser > 0)
    {
        list($now_maxuser) = dbGetStSr('WM_user_account','count(*)','1', ADODB_FETCH_NUM);
        if ($now_maxuser >= sysMaxUser)
        {
            $canRegisterNum = 0;
        }else{
            $canRegisterNum = sysMaxUser - $now_maxuser;
        }
        list($admin_email) = dbGetStSr(sysDBname.'.WM_school','school_mail',"school_id='{$sysSession->school_id}'", ADODB_FETCH_NUM);
        $msg_overMaxUser = str_replace(array('%max_register_user%', '%admin_email%'),
                                       array(sysMaxUser, 'mailto:'.$admin_email),
                                       $MSG['overMaxUser'][$sysSession->lang]);
    }

    $succ = 0;
    $msg = array();
    $cnt = 0;
    $overMaxUser = false;   //是否超過可註冊人數

    // 找出所有相似的帳號
    $account = dbGetCol('WM_all_account', 'username', 'username like "'.$header.'%'.$tail.'"');

    // 拿第一個欲新增的帳號去比對格式以及是否有保留字...等
    $compare = sprintf($fmt1, $first);
    $res_compare = checkUsername($compare);

    // 將格式問題或是保留字的錯誤代碼寫成array
    $errorNumArray = array(1,3,4);

    for($i = $first; $i <= $last; $i++) {

        $temp = sprintf($fmt1, $i);

        // $res = checkUsername($temp);
        /**
         * case 1:第一筆格式有問題 => 其他的就不需要再去比對 => 一定與第一筆的格式一樣
         * case 2:第一筆格式無問題 => 比對其他帳號是否在類似的帳號中
         **/
        if ($i==$first){
             // 因為第一筆已經拿去比對過了,這邊只做記錄
            $res = $res_compare;
        }
        else{
            // 如果格式(/保留字)沒有問題,則比對是否在類似的帳號中
            if (!in_array($res_compare,$errorNumArray)){
                (in_array($temp,$account))? $res=2 : $res=0;
            }
            else{
                // 如果第一筆格式(/保留字)有問題,其他帳號也一定是有問題的 => 結果與第一筆一樣
                $res = $res_compare;
            }
        }
        $resString .= $res.',';

        $msg[$cnt][0] = sprintf($fmt2, $i);
        if (sysMaxUser > 0)   //有註冊人數限制
        {
            --$canRegisterNum;    // 取得目前可註冊人數
            if ($canRegisterNum < 0)
            {
                $overMaxUser = true;
            }
        }
        if ($overMaxUser)  //已超過註冊上限
        {
            $msg[$cnt][1] = "<span class=\"color04\">{$msg_overMaxUser}</span>";
        }else if ($res == 0) {
                $msg[$cnt][1] = '&nbsp;';
                $succ++;
        } else {
            if ($res == 1) {
                $msg[$cnt][1] = "<span class=\"color04\">{$MSG['system_reserved'][$sysSession->lang]}</span>";
            }
            if ($res == 2) {
                $msg[$cnt][1] = "<span class=\"color04\">{$MSG['account_used'][$sysSession->lang]}</span>";
            }
            if ($res == 3) {
                $msg[$cnt][1] = "<span class=\"color04\">" . "{$MSG['format_not_match'][$sysSession->lang]}</span>";
            }

            if ($res == 4) {
                $msg[$cnt][1] = "<span class=\"color04\">" . "{$MSG['system_reserved'][$sysSession->lang]}</span>";
            }
        }
        $cnt++;
    }
    $resString = substr($resString,0,strlen($resString));

    showXHTML_head_B($MSG['create_account'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('inline', $js);
    $calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
    $calendar->load_files();
    showXHTML_head_E();
    showXHTML_body_B();
    $arry[] = array($MSG['create_serial_account'][$sysSession->lang], 'addTable1');
        showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
            showXHTML_tr_B();
                showXHTML_td_B();
                    showXHTML_tabs($arry, 1);
                showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B();
                showXHTML_td_B('valign="top" ');
                    // 新增連續帳號
                    showXHTML_form_B('action="stud_account2.php" method="post" onsubmit="return check_field()"', 'addFm');
                    $ticket = md5($sysSession->username . $sysSession->ticket. 'Auto' . $sysSession->school_id);
                    showXHTML_input('hidden', 'ticket', $ticket, '', '');
                    showXHTML_input('hidden', 'header', $header, '', '');
                    showXHTML_input('hidden', 'tail'  , $tail  , '', '');
                    showXHTML_input('hidden', 'first' , $first , '', '');
                    showXHTML_input('hidden', 'last'  , $last  , '', '');
                    showXHTML_input('hidden', 'len'   , $len   , '', '');
                    showXHTML_input('hidden', 'resString', $resString, '', '');

                    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="addTable1" class="cssTable"');
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('colspan="2" ', $MSG['account_list'][$sysSession->lang]);
                        showXHTML_tr_E();
                        $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
                        showXHTML_tr_B($col);
                            if ($succ > 0) {
                                showXHTML_td_B('colspan="2"');
                                    showXHTML_input('submit', '', $MSG['ok_add'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
                                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_account.php\');"');
                                showXHTML_td_E('&nbsp;');

                            } else {
                                showXHTML_td_B('colspan="2" ');
                                    echo $MSG['no_account'][$sysSession->lang];
                                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_account.php\');"');
                                showXHTML_td_E('&nbsp;');
                            }
                        showXHTML_tr_E();

                        $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';

                        showXHTML_tr_B($col);
                            showXHTML_td_B('colspan="2"');

                            echo $MSG['title87'][$sysSession->lang];
                            echo "<br /> {$MSG['first'][$sysSession->lang]}" . $MSG['title1'][$sysSession->lang];
                            showXHTML_input('checkbox', 'ck_begin_date', 'begin_date', '', 'id="ck_begin_date" onclick="showDateInput(\'span_begin_date' . '\', this.checked)"');
                            echo $MSG['msg_date_start'][$sysSession->lang];
                            echo '<span id="span_begin_date" style="display: none;">';
                            showXHTML_input('text', 'begin_date', date('Y-m-d'), '', 'id="begin_date" readonly="readonly" class="cssInput"');
                            echo '</span>';
                            echo '<br />'.$MSG['last'][$sysSession->lang] , $MSG['title1'][$sysSession->lang];
                            showXHTML_input('checkbox', 'ck_end_date', 'begin_date', '', 'id="ck_end_date" onclick="showDateInput(\'span_end_date' . '\', this.checked)"');
                            echo $MSG['msg_date_stop'][$sysSession->lang];
                            echo '<span id="span_end_date" style="display: none;">';
                            showXHTML_input('text', 'end_date', date('Y-m-d'), '', 'id="end_date" readonly="readonly" class="cssInput"');
                            echo '</span>';

                            showXHTML_td_E();
                        showXHTML_tr_E();

                        for ($i = 0; $i < $cnt; $i++) {
                            $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
                            showXHTML_tr_B($col);
                                showXHTML_td('', $msg[$i][0]);
                                showXHTML_td('', $msg[$i][1]);
                            showXHTML_tr_E();
                        }
                        $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
                        showXHTML_tr_B($col);
                            if ($succ > 0) {
                                showXHTML_td_B('colspan="2"');
                                    showXHTML_input('submit', '', $MSG['ok_add'][$sysSession->lang], '', 'id="btn_submit2" class="cssBtn"');
                                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_account.php\');"');
                                showXHTML_td_E('&nbsp;');
                            } else {
                                showXHTML_td_B('colspan="2" ');
                                    echo $MSG['no_account'][$sysSession->lang];
                                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_account.php\');"');
                                showXHTML_td_E('&nbsp;');
                            }
                        showXHTML_tr_E();
                    showXHTML_table_E();
                    showXHTML_form_E();

                showXHTML_td_E();
            showXHTML_tr_E();
        showXHTML_table_E();

    showXHTML_body_E();
?>

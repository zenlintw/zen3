<?php
    /**************************************************************************************************
    *                                                                                                 *
    *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
    *                                                                                                 *
    *        Programmer: Amm Lee                                                                       *
    *        Creation  : 2003/09/23                                                                    *
    *        work for  : 檢視成績                                                                      *
    *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
    *       $Id: view_grade.php,v 1.1 2010/02/24 02:38:15 saly Exp $                                                                                          *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
     require_once(sysDocumentRoot . '/lang/view_grade.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    
    $sysSession->cur_func='2400300900';
    $sysSession->restore();

    if (!aclVerifyPermission(2400100500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

    }

    $lang = strtolower($sysSession->lang);

    if ($_GET['a'] != ''){
        $class_id = intval($_GET['a']);
    }else{
        $class_id = 0;
    }

    $lines = sysPostPerPage;

    $js = <<< EOF
    var theme    = "{$sysSession->theme}";
    var lang     = "{$lang}";
    var groupIdx = 0;
    // 每頁列出幾筆資料
    var listNum  = {$lines};
    // 目前在第幾頁
    var pageIdx  = 1;
    var pageNum  = 1;

    // 訊息
    var msg02                = "{$MSG['title18'][$sysSession->lang]}";
    var msg07                = "{$MSG['title23'][$sysSession->lang]}";
    var MSG_TARGET           = "{$MSG['title26'][$sysSession->lang]}";
    var MSG_SRC_COURSE       = "{$MSG['title21'][$sysSession->lang]}";
    var NO_KEYWORD           = "{$MSG['no_keyword'][$sysSession->lang]}";
    var NO_KEYWORD2          = "{$MSG['title77'][$sysSession->lang]}";
    var MSG_SRC_COURSE2      = "{$MSG['title79'][$sysSession->lang]}";
    var school_name          = "{$sysSession->school_name}";
    var MSG_DEL              = "{$MSG['title122'][$sysSession->lang]}";
    var MSG_MAIL             = "{$MSG['title145'][$sysSession->lang]}";
    var msg1                 = "{$MSG['title133'][$sysSession->lang]}";
    var msg2                 = "{$MSG['msg_date_error'][$sysSession->lang]}";
    var msg3                 = "{$MSG['msg_date_error1'][$sysSession->lang]}";
    var msg4                 = "{$MSG['msg_date_error2'][$sysSession->lang]}";
    var msg5                 = "{$MSG['msg_date_error3'][$sysSession->lang]}";
    var msg6                 = "{$MSG['msg_date_error4'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL    = "{$MSG['msg_select_cancel'][$sysSession->lang]}";
    var MSG_SELECT_ALL       = "{$MSG['msg_select_all'][$sysSession->lang]}";;
    var class_id             = {$class_id};
    var unlimit              = "{$MSG['title24'][$sysSession->lang]}";
    var msg_title34          = "{$MSG['title34'][$sysSession->lang]}";
    var msg_title132         = "{$MSG['title132'][$sysSession->lang]}";
    var msg_title124         = "{$MSG['title124'][$sysSession->lang]}";
    var msg_title125         = "{$MSG['title125'][$sysSession->lang]}";
    var msg_title115         = "{$MSG['title115'][$sysSession->lang]}";
    var msg_keyword          = "{$MSG['title36'][$sysSession->lang]}";
    var MSG_page_exceed      = "{$MSG['go_page_input_error'][$sysSession->lang]}";
    var MSG_page_exceed_org  = "{$MSG['go_page_input_error'][$sysSession->lang]}";
    var MSG_page_range_error = "{$MSG['go_page_input_error2'][$sysSession->lang]}";
    
    var icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
    var icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

    var stud_sort = new Array('',
                            'username',
                            'total_course',
                            'greater',
                            'smaller',
                            'total_avg'
                        );

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

    var orgload = window.onload;
    window.onload = function () {
        orgload();
        // java script 的 date
        Calendar_setup('sdate', '%Y-%m-%d', 'sdate', false);
        Calendar_setup('edate', '%Y-%m-%d', 'edate', false);

    }
EOF;

    $calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
    $calendar->load_files();

    showXHTML_head_B($MSG['title27'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('include', 'view_grade.js');
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E('');

    showXHTML_body_B('');

        showXHTML_table_B('width="600" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
            showXHTML_tr_B('');
                showXHTML_td_B('');
                    $ary[] = array($MSG['title27'][$sysSession->lang], 'tabs');
                    showXHTML_tabs($ary, 1);
                showXHTML_td_E('');
            showXHTML_tr_E('');
            showXHTML_tr_B('');
                showXHTML_td_B('valign="top" id="CGroup"');
                    showXHTML_input('hidden', 'gpName'    , '', '', 'id="gpName"');
                    showXHTML_input('hidden', 'sby1'      , '', '', 'id="sby1"');
                    showXHTML_input('hidden', 'oby1'      , '', '', 'id="oby1"');
                    // 目前正在第幾頁
                    showXHTML_input('hidden', 'where_page', '', '', 'id="where_page"');
                    showXHTML_input('hidden', 'query_btn' , '', '', 'id="query_btn"');
                    showXHTML_table_B('width="600" border="0" cellspacing="1" cellpadding="3" id="ClassList" class="cssTable"');
                        // 所在位置
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('colspan="10"', $sysSession->school_name . '<span id="gpName2"></span>');
                        showXHTML_tr_E('');
                        // 查詢對象
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="10"');
                                echo $MSG['title32'][$sysSession->lang];
                                echo "<select name='searchkey' class='cssInput' id='searchkey'>
                                      <option value='real'>{$MSG['title33'][$sysSession->lang]}</option>
                                      <option value='account'>{$MSG['title34'][$sysSession->lang]}</option>
                                      <option value='email'>{$MSG['title35'][$sysSession->lang]}</option>
                                      </select>";
                                echo $MSG['title37'][$sysSession->lang];
                                showXHTML_input('text', 'keyword', $MSG['title36'][$sysSession->lang], '', 'id="keyword" size="20"  width="30" class="cssInput" onclick="this.value=\'\'"');
                                echo $MSG['title38'][$sysSession->lang];

                            showXHTML_td_E('');
                        showXHTML_tr_E('');

                        // 修課期間
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="10" ');
                                echo "{$MSG['title128'][$sysSession->lang]}<br>";

                                echo $MSG['from'][$sysSession->lang] . iconv('Big5','UTF-8','：');

                                showXHTML_input('text', 'sdate', '','', 'id="sdate" size="23" width="30" class="cssInput" readonly="readonly"');

                                echo $MSG['title114'][$sysSession->lang]  . iconv('Big5','UTF-8','：');

                                showXHTML_input('text', 'edate', '','', 'id="edate" size="23" width="30" class="cssInput" readonly="readonly"');

                                showXHTML_input('button', '', $MSG['title39'][$sysSession->lang], '', 'style="24" class="cssBtn" onclick="queryClass()"');
                                showXHTML_input('button', '', $MSG['cancel_btn'][$sysSession->lang], '', 'id="appendBtn1" class="cssBtn" onclick="cancel_btn()"');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');

                        // 換頁與動作功能列
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="10" nowrap id="toolbar1"');
                                showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
                                    showXHTML_tr_B('class="cssTrEvn"');
                                        showXHTML_td_B('nowrap');
                                            showXHTML_input('button', 'btnSel1', $MSG['msg_select_all'][$sysSession->lang], '', 'class="cssBtn" id="btnSel1" onclick="sel_button_func()"');
                                            $ary = array($MSG['title23'][$sysSession->lang]);
                                            echo $MSG['page'][$sysSession->lang];
                                            echo '<span id="spanSel1">';
                                            showXHTML_input('select', 'selBtn1', $ary, '1', 'id="selBtn1" class="cssInput"');
                                            echo '</span>';
                                            echo '&nbsp;';
                                            // 手動輸入 page 的數目
                                            echo $MSG['go_page_no'][$sysSession->lang];
                                            showXHTML_input('text', 'input_page', '', '', 'id="input_page" size="3" class="cssInput"');
                                            echo $MSG['go_page_title'][$sysSession->lang];
                                            showXHTML_input('button', 'btn_go_page1', 'Go', '', 'class="cssBtn" id="btn_go_page1" onclick="go_page_btn(this);"');
                                            echo '&nbsp;';
                                            // 每頁顯示幾筆
                                            echo $MSG['title146'][$sysSession->lang];
                                            $page_array = array(10 => $MSG['title148'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);
                                            showXHTML_input('select', 'page_num', $page_array,$page_num, 'class="cssInput" id="page_num" onChange="Page_Row(this.value)" ');
                                            echo $MSG['title147'][$sysSession->lang];

                                            showXHTML_input('button', 'firstBtn1', $MSG['first'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" onclick="goPage(-1)" title=' . $MSG['title40'][$sysSession->lang]);
                                            showXHTML_input('button', 'prevBtn1',  $MSG['prev'][$sysSession->lang],  '', 'id="prevBtn1"  class="cssBtn" onclick="goPage(-2)" title=' . $MSG['title41'][$sysSession->lang]);
                                            showXHTML_input('button', 'nextBtn1',  $MSG['next'][$sysSession->lang],  '', 'id="nextBtn1"  class="cssBtn" onclick="goPage(-3)" title=' . $MSG['title42'][$sysSession->lang]);
                                            showXHTML_input('button', 'lastBtn1',  $MSG['last'][$sysSession->lang],  '', 'id="lastBtn1"  class="cssBtn" onclick="goPage(-4)" title=' . $MSG['title43'][$sysSession->lang]);
                                        showXHTML_td_E('');
                                        showXHTML_td_B('align="right" nowrap');
                                            showXHTML_input('button', '', $MSG['title122'][$sysSession->lang], '', 'id="appendBtn1" class="cssBtn" onclick="doFunc(1)"');
                                        showXHTML_td_E('');
                                    showXHTML_tr_E('');
                                showXHTML_table_E('');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');

                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td_B('align="center"');
                                showXHTML_input('checkbox', 'ck', '', '', 'id="ckbox" onclick="sel_button_func();" exclude="true"' . 'title=' . $MSG['select_all'][$sysSession->lang]);
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" id="title34" nowrap="noWrap" title="' . $MSG['title34'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:chgPageSort(1);" >';
                                echo $MSG['title34'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" nowrap="noWrap" ');
                                echo $MSG['title33'][$sysSession->lang];
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" id="title132" nowrap="noWrap" title="' . $MSG['title132'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:chgPageSort(3);" >';
                                echo $MSG['title132'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" id="title124" nowrap="noWrap" title="' . $MSG['title124'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:chgPageSort(4);" >';
                                echo $MSG['title124'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" id="title125" nowrap="noWrap" title="' . $MSG['title125'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:chgPageSort(5);" >';
                                echo $MSG['title125'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" id="title115" nowrap="noWrap" title="' . $MSG['title115'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:chgPageSort(6);" >';
                                echo $MSG['title115'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td('align="center" nowrap', $MSG['title123'][$sysSession->lang]);
                        showXHTML_tr_E('');

                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td('align="center" colspan="10"' , $MSG['loading'][$sysSession->lang]);
                        showXHTML_tr_E('');

                        // 換頁與動作功能列
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="10" nowrap id="toolbar2"');
                            showXHTML_td_E('&nbsp;');
                        showXHTML_tr_E('');
                    showXHTML_table_E('');
                showXHTML_td_E('');
            showXHTML_tr_E('');
        showXHTML_table_E('');

    showXHTML_form_B('action="sysbar.php" method="post" enctype="multipart/form-data" style="display:none" target="esBar"', 'sysbarFm');
        showXHTML_input('hidden', 'csid', '0', '', '');
    showXHTML_form_E('');

    //  學員資訊
    showXHTML_form_B('action="detail_grade.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
        showXHTML_input('hidden', 'user', '', '', '');
        showXHTML_input('hidden', 'class_id', '', '', '');
        showXHTML_input('hidden', 'sdate', '', '', '');
        showXHTML_input('hidden', 'edate', '', '', '');
    showXHTML_form_E();

    //  寄信
    $ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);
    showXHTML_form_B('action="send_class_grade_mail.php" method="post" enctype="multipart/form-data" style="display:none"', 'mailFm');
        showXHTML_input('hidden', 'ticket', $ticket, '', '');
        showXHTML_input('hidden', 'send_user', '', '', '');
        showXHTML_input('hidden', 'class_id', '', '', '');
    showXHTML_form_E('');

    showXHTML_body_E('');
?>

<?php
    /**************************************************************************************************
    *                                                                                                 *
    *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
    *                                                                                                 *
    *        Programmer: Amm Lee                                                                       *
    *        Creation  : 2003/09/23                                                                    *
    *        work for  : 人員管理                                                                      *
    *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
    *       $Id: content_package_manager.php,v 1.1 2010/02/24 02:38:17 saly Exp $
    *                                                                                                 *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
     require_once(sysDocumentRoot . '/lang/content_lang.php');
     require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/common.php');

    $sysSession->cur_func = '2400300900';
    $sysSession->restore();
    if (!aclVerifyPermission(2400300900, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
    {
    }

    $stud_sort = array(
        '',
        'username',
        'first_name,last_name',
        'gender',
        'role',
        'email'
    );

    $lang = strtolower($sysSession->lang);
    $content_id = isset($_GET['a']) ? intval($_GET['a']) : 0;

    // 設定車票
    setTicket();
    $ticket_create = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Create' . $sysSession->username);
    $ticket_edit   = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit'   . $sysSession->username);
    $ticket_delete = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Delete' . $sysSession->username);

    $lines = sysPostPerPage;
        $isMobile = isMobileBrowser() ? '1' : '0';
    $js = <<< EOF
    var theme = "{$sysSession->theme}";
    var lang  = "{$lang}";
    var groupIdx = 0;
    // 每頁列出幾筆資料
    var listNum = {$lines};
    // 目前在第幾頁
    var pageIdx = 1;
    var pageNum = 1;

    var ticket1 = "";
    var ticket2 = "{$ticket_edit}";
    var ticket3 = "{$ticket_delete}";

    // 訊息
    var msg02 = "{$MSG['title18'][$sysSession->lang]}";
    var msg03 = "{$MSG['title19'][$sysSession->lang]}";
    var msg04 = "{$MSG['title20'][$sysSession->lang]}";
    var msg05 = "{$MSG['title21'][$sysSession->lang]}";
    var msg06 = "{$MSG['title22'][$sysSession->lang]}";
    var msg07 = "{$MSG['title23'][$sysSession->lang]}";
    var msg08 = "{$MSG['title24'][$sysSession->lang]}";
    var msg09 = "{$MSG['title25'][$sysSession->lang]}";
    // Bug#1424 修改「請勾選要目的地群組」為「請勾選左邊的類別」 by Small 2006/09/15
    // var MSG_TARGET   = "{$MSG['title26'][$sysSession->lang]}";
    var MSG_TARGET      = "{$MSG['msg_select_left'][$sysSession->lang]}";
    var MSG_SRC_COURSE  = "{$MSG['title21'][$sysSession->lang]}";
    var NO_KEYWORD      = "{$MSG['no_keyword'][$sysSession->lang]}";
    var MSG_DEL         = "{$MSG['msg_sure_del'][$sysSession->lang]}";
    var NO_KEYWORD2     = "{$MSG['title77'][$sysSession->lang]}";
    var MSG_SRC_COURSE2 = "{$MSG['title79'][$sysSession->lang]}";
    var school_name     = "{$sysSession->school_name}";
    var content_id      = {$content_id};
    var msg_title34     = "{$MSG['title34'][$sysSession->lang]}";
    var msg_title33     = "{$MSG['title33'][$sysSession->lang]}";
    var msg_title35     = "{$MSG['title35'][$sysSession->lang]}";
    var msg_title55     = "{$MSG['title55'][$sysSession->lang]}";
    var msg_title59     = "{$MSG['title59'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL = "{$MSG['msg_select_cancel'][$sysSession->lang]}";
    var MSG_SELECT_ALL    = "{$MSG['msg_select_all'][$sysSession->lang]}";
    var MSG_keyword     = "{$MSG['title35'][$sysSession->lang]}";

    var icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
    var icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

    function Addcontent()
    {
        this.document.location.replace('content_property.php?gid='+groupIdx);
    }

    function editContent(val) {
        var obj = document.getElementById("actFm");
        obj.action = "content_property.php";
        obj.ticket.value = ticket2;
        obj.content_id.value = val;
        obj.submit();
    }

    function showContent(val) {
        var obj = document.getElementById("actFm");
        obj.action = "content_property_view.php";
        obj.ticket.value = ticket2;
        obj.content_id.value = val;
        obj.submit();
    }

    function Deletecontent() {
        var obj = document.getElementById("deletefm");
        var nodes = document.getElementsByTagName("input");
        var cnt = 0;
        if ((nodes == null) || (obj == null)) return false;
        for (i = 0; i < nodes.length; i++) {
            if ((nodes[i].getAttribute("type") == "checkbox")
                && nodes[i].checked && (nodes[i].name !='ck')) {
                cnt++;
            }
        }
        if (!cnt) {
            alert("{$MSG['msg_sel_delete'][$sysSession->lang]}");
            return false;
        }
        if (!window.confirm("{$MSG['msg_sure_del'][$sysSession->lang]}"))
            return false;
        obj.ticket.value = ticket3;
        obj.submit();
    }

    function WebFolder(content_id)
    {
        window.open('/academic/content/open_web_folder.php?{$sysSession->school_id}_'+content_id, 'upload_win', 'width=500,height=375,status=0,toolbar=1,menubar=1,resizable=1');
    }
                
        var isMobile = '{$isMobile}';        
EOF;

    showXHTML_head_B($MSG['title27'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('inline', $js);
    showXHTML_script('include', 'content_package_manager.js');
    showXHTML_head_E('');

    showXHTML_body_B('');
        showXHTML_form_B('style="display:inline;" action="content_delete.php" method="post" onKeyPress="if (window.event.keyCode == 13) { event.returnValue=false; event.cancel = true; querycontent();}"', 'deletefm');
        showXHTML_input('hidden', 'ticket', '', '', '');
        showXHTML_table_B('width="780" align="center" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
            showXHTML_tr_B('');
                showXHTML_td_B('');
                    $ary[] = array($MSG['title27'][$sysSession->lang], 'tabs');
                    showXHTML_tabs($ary, 1);
                showXHTML_td_E('');
            showXHTML_tr_E('');
            showXHTML_tr_B('');
                showXHTML_td_B('valign="top" id="CGroup"');
                    showXHTML_input('hidden', 'gpName', '', '', 'id="gpName"');
                    showXHTML_input('hidden', 'sby1', '', '', 'id="sby1"');
                    showXHTML_input('hidden', 'oby1', '', '', 'id="oby1"');
                    showXHTML_input('hidden', 'query_btn', '', '', 'id="query_btn"');
                    showXHTML_table_B('width="780" border="0" cellspacing="1" cellpadding="3" id="contentList" class="cssTable"');
                        // 所在位置
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('colspan="9"', $sysSession->school_name . '<span id="gpName2"></span>');
                        showXHTML_tr_E('');
                        // 查詢搜尋
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="9"');
                                echo $MSG['title32'][$sysSession->lang];
                                showXHTML_input('text', 'keyword', $MSG['title35'][$sysSession->lang], '', 'id="keyword" size="20" width="30" class="cssInput" onclick="this.value=\'\'"');
                                echo "<select name='searchkey' id='searchkey'>
                                      <option value='content_id'>{$MSG['title33'][$sysSession->lang]}</option>
                                      <option value='caption'>{$MSG['title34'][$sysSession->lang]}</option>
                                      </select>";

                                echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$MSG['title36'][$sysSession->lang];
                                echo "<select name='searchField' id='searchField'>
                                      <option value='none'>{$MSG['title37'][$sysSession->lang]}</option>
                                      <option value='digitization'>{$MSG['title38'][$sysSession->lang]}</option>
                                      <option value='traditional'>{$MSG['title38_1'][$sysSession->lang]}</option>
                                      </select>";

                                showXHTML_input('button', '', $MSG['title39'][$sysSession->lang], '', 'style="24" class="cssBtn" onclick="querycontent()"');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');

                        // 換頁與動作功能列
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="9" nowrap id="toolbar1"');
                                showXHTML_table_B('width="600" border="0" cellspacing="0" cellpadding="0"');
                                    showXHTML_tr_B('class="cssTrEvn"');
                                        showXHTML_td_B('nowrap');
                                            showXHTML_input('button', 'btnSel1', $MSG['msg_select_all'][$sysSession->lang], '', 'class="cssBtn" id="btnSel1" onclick="sel_button_func()"');
                                            $ary = array($MSG['title23'][$sysSession->lang]);
                                            echo '&nbsp;' , $MSG['page'][$sysSession->lang];
                                            echo '<span id="spanSel1">';
                                            showXHTML_input('select', 'selBtn1', $ary, '1', 'id="selBtn1" class="cssInput"');
                                            echo '</span>';
                                            // 每頁顯示幾筆 XUA
                                            echo '&nbsp;' . $MSG['every_page'][$sysSession->lang];
                                            $page_array = array(10=> $MSG['title136'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);
                                            showXHTML_input('select', 'page_num', $page_array,10, 'class="cssInput" id="page_num" onChange="Page_Row(this.value)" ');
                                            echo $MSG['page_record'][$sysSession->lang];
                                            showXHTML_input('button', 'firstBtn1', $MSG['first'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" onclick="goPage(-1)" title=' . $MSG['title40'][$sysSession->lang]);
                                            showXHTML_input('button', 'prevBtn1',  $MSG['prev'][$sysSession->lang],  '', 'id="prevBtn1"  class="cssBtn" onclick="goPage(-2)" title=' . $MSG['title41'][$sysSession->lang]);
                                            showXHTML_input('button', 'nextBtn1',  $MSG['next'][$sysSession->lang],  '', 'id="nextBtn1"  class="cssBtn" onclick="goPage(-3)" title=' . $MSG['title42'][$sysSession->lang]);
                                            showXHTML_input('button', 'lastBtn1',  $MSG['last'][$sysSession->lang],  '', 'id="lastBtn1"  class="cssBtn" onclick="goPage(-4)" title=' . $MSG['page_end'][$sysSession->lang]);
                                            showXHTML_input('button', '', $MSG['title28'][$sysSession->lang], '', 'class="cssBtn" onclick="Addcontent()"');
                                            showXHTML_input('button', '', $MSG['title29'][$sysSession->lang], '', 'id="delContent" class="cssBtn" onclick="Deletecontent();" disabled');
                                            showXHTML_input('button', '', $MSG['title43'][$sysSession->lang], '', 'id="appendBtn1" class="cssBtn" onclick="doFunc(1)" title=' . $MSG['title44'][$sysSession->lang]);
                                            showXHTML_input('button', '', $MSG['title46'][$sysSession->lang], '', 'id="delBtn11" class="cssBtn" onclick="doFunc(4)" title=' . $MSG['title50'][$sysSession->lang]);
                                        showXHTML_td_E('');
                                    showXHTML_tr_E('');
                                showXHTML_table_E('');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');

                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td_B('align="center"');
                                showXHTML_input('checkbox', 'ck', '', '', ' id="ckbox"  onclick="selFunc(this.checked);" exclude="true"' . ' title="' . $MSG['toolbtn16'][$sysSession->lang] . '"');
                            showXHTML_td_E('');
                            showXHTML_td(' align="center" nowrap  ', $MSG['title33'][$sysSession->lang]);
                            showXHTML_td(' align="center" nowrap  ', $MSG['title34'][$sysSession->lang]);
                            showXHTML_td(' align="center" nowrap  ', $MSG['title55'][$sysSession->lang]);
                            showXHTML_td(' align="center" nowrap  ', $MSG['title56'][$sysSession->lang]);
                            // 行動裝置不支援Web資料夾
                            if ($isMobile === '0') {
                                showXHTML_td(' align="center" nowrap ', $MSG['title57'][$sysSession->lang]);
                            }
                            showXHTML_td(' align="center" nowrap ', $MSG['title18'][$sysSession->lang]);
                        showXHTML_tr_E('');

                        showXHTML_tr_B('class="cssTrOdd"');
                            showXHTML_td('align="center" colspan="9" ', $MSG['loading'][$sysSession->lang]);
                        showXHTML_tr_E('');

                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        // 換頁與動作功能列
                        showXHTML_tr_B($col);
                            showXHTML_td_B('colspan="9" nowrap id="toolbar2"');
                            showXHTML_td_E('&nbsp;');
                        showXHTML_tr_E('');
                    showXHTML_table_E('');
                showXHTML_td_E('');
            showXHTML_tr_E('');
        showXHTML_table_E('');
        showXHTML_form_E();
    showXHTML_form_B('action="content_property.php" method="post"', 'actFm');
            showXHTML_input('hidden', 'ticket', '', '', '');
            showXHTML_input('hidden', 'content_id', '', '', '');
    showXHTML_form_E();
    showXHTML_body_E('');
?>

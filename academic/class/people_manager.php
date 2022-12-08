<?php
    /**************************************************************************************************
    *                                                                                                 *
    *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
    *                                                                                                 *
    *        Programmer: Amm Lee                                                                       *
    *        Creation  : 2003/09/23                                                                    *
    *        work for  : 人員管理                                                                      *
    *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
    *       $Id: people_manager.php,v 1.1 2010/02/24 02:38:15 saly Exp $
    *                                                                                                 *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
     require_once(sysDocumentRoot . '/lang/people_manager.php');
     require_once(sysDocumentRoot . '/lib/acl_api.php');

     $sysSession->cur_func = '2400300900';
    $sysSession->restore();

    if (!aclVerifyPermission(2400300900, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
    {
    }

    $lang     = strtolower($sysSession->lang);
    $class_id = (isset($_GET['a'])) ? intval($_GET['a']) : 0;
    $lines    = sysPostPerPage;

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
    var msg02 = "{$MSG['title18'][$sysSession->lang]}";
    var msg03 = "{$MSG['title19'][$sysSession->lang]}";
    var msg04 = "{$MSG['title20'][$sysSession->lang]}";
    var msg05 = "{$MSG['title21'][$sysSession->lang]}";
    var msg06 = "{$MSG['title22'][$sysSession->lang]}";
    var msg07 = "{$MSG['title23'][$sysSession->lang]}";
    var msg08 = "{$MSG['title24'][$sysSession->lang]}";
    var msg09 = "{$MSG['title25'][$sysSession->lang]}";
    var MSG_TARGET           = "{$MSG['title26'][$sysSession->lang]}";
    var MSG_SRC_COURSE       = "{$MSG['title21'][$sysSession->lang]}";
    var NO_KEYWORD           = "{$MSG['no_keyword'][$sysSession->lang]}";
    var NO_KEYWORD2          = "{$MSG['title77'][$sysSession->lang]}";
    var MSG_SRC_COURSE2      = "{$MSG['title79'][$sysSession->lang]}";
    var school_name          = "{$sysSession->school_name}";
    var MSG_DEL              = "{$MSG['title122'][$sysSession->lang]}";
    var MSG_MAIL             = "{$MSG['title124'][$sysSession->lang]}";
    var class_id             = {$class_id};
    var msg_title34          = "{$MSG['title34'][$sysSession->lang]}";
    var msg_title33          = "{$MSG['title33'][$sysSession->lang]}";
    var msg_title35          = "Email";
    var msg_title55          = "{$MSG['title55'][$sysSession->lang]}";
    var msg_title59          = "{$MSG['title59'][$sysSession->lang]}";
    var msg_belong_class     = "{$MSG['belong_class'][$sysSession->lang]}";
    var msg_explode          = "{$MSG['explode'][$sysSession->lang]}";
    var msg_close_explode    = "{$MSG['close_explode'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL    = "{$MSG['msg_select_cancel'][$sysSession->lang]}";
    var MSG_SELECT_ALL       = "{$MSG['msg_select_all'][$sysSession->lang]}";
    var MSG_keyword          = "{$MSG['title36'][$sysSession->lang]}";
    var MSG_page_exceed      = "{$MSG['go_page_input_error'][$sysSession->lang]}";
    var MSG_page_exceed_org  = "{$MSG['go_page_input_error'][$sysSession->lang]}";
    var MSG_page_range_error = "{$MSG['go_page_input_error2'][$sysSession->lang]}";
    var MSG_choose_role      = "{$MSG['msg_choose_role'][$sysSession->lang]}";

    var icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
    var icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

    var stud_sort = new Array('',
                            'username',
                            'first_name,last_name',
                             'gender',
                            'role',
                            'email'
                        );
EOF;

    showXHTML_head_B($MSG['title27'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('include', '/lib/dragLayer.js');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('inline', $js);
    showXHTML_script('include', 'people_manager.js');
    showXHTML_head_E('');

    showXHTML_body_B('');

        showXHTML_table_B('width="760" align="center" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
            showXHTML_tr_B('');
                showXHTML_td_B('');
                    $ary = array();
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
                    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="ClassList" class="cssTable"');
                        // 所在位置
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('colspan="9"', $sysSession->school_name . '<span id="gpName2"></span>');
                        showXHTML_tr_E('');
                        // 查詢身份
                        showXHTML_tr_B('class="cssTrEvn" id="status_tr" style="display:none" ');
                            showXHTML_td_B('colspan="9"');
                                echo $MSG['title28'][$sysSession->lang];
                                // #47117 Chrome 給予id
                                echo "<select name='status' id='status'>
                                      <option value='all'>{$MSG['title23'][$sysSession->lang]}</option>";
/*<!--
                                      <option value='guest'>{$MSG['title61'][$sysSession->lang]}</option>
                                      <option value='senior'>{$MSG['title62'][$sysSession->lang]}</option>
                                      <option value='paterfamilias'>{$MSG['title63'][$sysSession->lang]}</option>
                                      <option value='auditor'>{$MSG['title65'][$sysSession->lang]}</option>
-->*/
                                echo "<option value='student'>{$MSG['title66'][$sysSession->lang]}</option>
                                      <option value='assistant'>{$MSG['title67'][$sysSession->lang]}</option>
                                      <option value='director'>{$MSG['title70'][$sysSession->lang]}</option>
                                      </select>";
                            showXHTML_td_E('');
                        showXHTML_tr_E('');

                        // 查詢搜尋
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="9"');
                                echo $MSG['title32'][$sysSession->lang];
                                echo "<select name='searchkey' id='searchkey'>
                                      <option value='real'>{$MSG['title33'][$sysSession->lang]}</option>
                                      <option value='account'>{$MSG['title34'][$sysSession->lang]}</option>
                                      <option value='email'>Email</option>
                                      </select>";
                                echo $MSG['title37'][$sysSession->lang];
                                showXHTML_input('text', 'keyword', $MSG['title36'][$sysSession->lang], '', 'id="keyword" size="20" width="30" class="cssInput" onclick="this.value=\'\'"');
                                echo $MSG['title38'][$sysSession->lang];

                                showXHTML_input('button', '', $MSG['title39'][$sysSession->lang], '', 'style="24" class="cssBtn" onclick="queryClass()"');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');


                        // 換頁與動作功能列
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('colspan="9" nowrap id="toolbar1"');
                                showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0"');
                                    // Bug#915 修正翻頁介面 - Begin by Small 2006/10/4
                                    showXHTML_tr_B('class="cssTrEvn" align="right"');
                                        showXHTML_td_B('nowrap');
                                            echo $MSG['title49'][$sysSession->lang];
                                            showXHTML_input('button', '', $MSG['title43'][$sysSession->lang], '', 'id="appendBtn1" class="cssBtn" onclick="doFunc(1)" title=' . $MSG['title44'][$sysSession->lang]);
                                            showXHTML_input('button', '', $MSG['title45'][$sysSession->lang], '', ' class="cssBtn" onclick="doFunc(2)" title=' . $MSG['title45'][$sysSession->lang]);
                                            showXHTML_input('button', '', $MSG['title47'][$sysSession->lang], '', ' id="moveBtn1"  class="cssBtn" disabled="true" onclick="doFunc(3)" title=' . $MSG['title51'][$sysSession->lang]);
                                            showXHTML_input('button', '', $MSG['title46'][$sysSession->lang], '', 'id="delBtn11" class="cssBtn" disabled="true" onclick="doFunc(4)" title=' . $MSG['title50'][$sysSession->lang]);
                                            showXHTML_input('button', 'status1Btn', $MSG['title48'][$sysSession->lang], '', 'id="status1Btn" class="cssBtn" disabled="true" onclick="doFunc(5)" title=' . $MSG['title52'][$sysSession->lang]);
                                        showXHTML_td_E('');
                                    showXHTML_tr_E('');
                                    // Bug#915 修正翻頁介面 - End by Small 2006/10/4
                                    showXHTML_tr_B('class="cssTrEvn"');
                                        showXHTML_td_B('nowrap');
                                            showXHTML_input('button', 'btnSel1', $MSG['msg_select_all'][$sysSession->lang], '', 'class="cssBtn" id="btnSel1" onclick="sel_button_func()"');
                                            $ary = array();
                                            echo '&nbsp;' , $MSG['page'][$sysSession->lang], '<span id="spanSel1">';
                                            showXHTML_input('select', 'selBtn1', $ary, '1', 'id="selBtn1" class="cssInput"');
                                            echo '</span>&nbsp;';
                                            // 手動輸入 page 的數目
                                            echo $MSG['go_page_no'][$sysSession->lang];
                                            showXHTML_input('text', 'input_page', '', '', 'id="input_page" size="3" class="cssInput"');
                                            echo $MSG['go_page_title'][$sysSession->lang];
                                            showXHTML_input('button', 'btn_go_page1', 'Go', '', 'class="cssBtn" id="btn_go_page1" onclick="go_page_btn(this)"');
                                            // 每頁顯示幾筆
                                            echo '&nbsp;' . $MSG['title134'][$sysSession->lang];
                                            $page_array = array(10=> $MSG['title136'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);
                                            showXHTML_input('select', 'page_num', $page_array,10, 'class="cssInput" id="page_num" onChange="Page_Row(this.value)" ');
                                            echo $MSG['title135'][$sysSession->lang];

                                            showXHTML_input('button', 'firstBtn1', $MSG['first'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" onclick="goPage(-1)" title=' . $MSG['title40'][$sysSession->lang]);
                                            showXHTML_input('button', 'prevBtn1',  $MSG['prev'][$sysSession->lang],  '', 'id="prevBtn1"  class="cssBtn" onclick="goPage(-2)" title=' . $MSG['title41'][$sysSession->lang]);
                                            showXHTML_input('button', 'nextBtn1',  $MSG['next'][$sysSession->lang],  '', 'id="nextBtn1"  class="cssBtn" onclick="goPage(-3)" title=' . $MSG['title42'][$sysSession->lang]);
                                            showXHTML_input('button', 'lastBtn1',  $MSG['last'][$sysSession->lang],  '', 'id="lastBtn1"  class="cssBtn" onclick="goPage(-4)" title=' . $MSG['page_end'][$sysSession->lang]);
                                        showXHTML_td_E('');
                                    showXHTML_tr_E('');
                                showXHTML_table_E('');
                            showXHTML_td_E('');
                        showXHTML_tr_E('');

                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td_B('align="center"');
                                showXHTML_input('checkbox', 'ck', '', '', ' id="ckbox"  onclick="sel_button_func();" exclude="true"' . ' title="' . $MSG['toolbtn16'][$sysSession->lang] . '"');
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" width="100" id="title34" nowrap="noWrap" title="' . $MSG['title34'][$sysSession->lang] . '"');
                                echo '<a align="center" class="cssAnchor" href="javascript:chgPageSort(1);" >';
                                echo $MSG['title34'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" id="title33" width="100" nowrap="noWrap" title="' . $MSG['title33'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:chgPageSort(2);" >';
                                echo $MSG['title33'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" width="40" id="title55" nowrap="noWrap" title="' . $MSG['title55'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:chgPageSort(3);" >';
                                echo $MSG['title55'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td_B('align="center" width="60" id="hi_status" nowrap="noWrap" title="' . $MSG['title59'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:chgPageSort(4);" >';
                                echo $MSG['title59'][$sysSession->lang];
                                echo '</a>';
                            showXHTML_td_E('');

                            showXHTML_td(' align="center" nowrap  ', $MSG['title56'][$sysSession->lang]);
                            showXHTML_td(' align="center" nowrap ', $MSG['title57'][$sysSession->lang]);
                            showXHTML_td(' align="center" nowrap ', $MSG['title58'][$sysSession->lang]);
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

    showXHTML_form_B('action="sysbar.php" method="post" enctype="multipart/form-data" style="display:none" target="esBar"', 'sysbarFm');
        showXHTML_input('hidden', 'csid', '0', '', '');
    showXHTML_form_E('');

    // 變換身份  begin
    $ary2 = array();
    $ary2[] = array($MSG['title48'][$sysSession->lang], 'divSettings');

    showXHTML_tabFrame_B($ary2, 1, 'fmSetting', 'divSettings', ' method="post" action="switch_status.php" style="display:inline;" ', true);
        showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
            showXHTML_tr_B('class="cssTrOdd"');
                   showXHTML_td_B('width="100"');

//                  $array_role[$sysRoles['guest']]            = $MSG['title61'][$sysSession->lang];
//                  $array_role[$sysRoles['senior']]            = $MSG['title62'][$sysSession->lang];
//                  $array_role[$sysRoles['paterfamilias']]    = $MSG['title63'][$sysSession->lang];
                  $array_role[$sysRoles['student']]            = $MSG['title66'][$sysSession->lang];
//                  $array_role[$sysRoles['auditor']]            = $MSG['title65'][$sysSession->lang];
                  $array_role[$sysRoles['assistant']]        = $MSG['title67'][$sysSession->lang];
                  $array_role[$sysRoles['director']]        = $MSG['title70'][$sysSession->lang];

                     showXHTML_input('radio', 'role', $array_role, '', 'id="role"','<br>');

                   showXHTML_td_E();
               showXHTML_tr_E();

            showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td_B('colspan="3" align="center" nowrap');
                    showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="change_status();"');
                    showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="actionLayer(\'divSettings\', false)"');
                showXHTML_td_E('');
            showXHTML_tr_E('');
        showXHTML_table_E('');
    showXHTML_tabFrame_E();
    // 變換身份  end

    //  學員資訊
    showXHTML_form_B('action="stud_info.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
        showXHTML_input('hidden', 'msgtp', '', '', '');
        showXHTML_input('hidden', 'user', '', '', '');
        showXHTML_input('hidden', 'class_id', '', '', '');
    showXHTML_form_E();

    //  調動
    showXHTML_form_B('action="move_member.php" method="post" enctype="multipart/form-data" style="display:none"', 'modify_belong');
        showXHTML_input('hidden', 'old_class', '', '', '');
        showXHTML_input('hidden', 'student', '', '', '');
        showXHTML_input('hidden', 'new_class', '', '', '');
    showXHTML_form_E();

    //  寄信
    $ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);
    showXHTML_form_B('action="send_class_mail.php" method="post" enctype="multipart/form-data" style="display:none"', 'mailFm');
        showXHTML_input('hidden', 'ticket', $ticket, '', '');
        showXHTML_input('hidden', 'send_user', '', '', '');
        showXHTML_input('hidden', 'class_id', '', '', '');
    showXHTML_form_E('');

    showXHTML_body_E('');
?>

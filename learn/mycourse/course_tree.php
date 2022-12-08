<?php
    /**
     * 我的課程 - 課程群組
     *
     * @since   2003/06/05
     * @author  ShenTing Lin
     * @version $Id: course_tree.php,v 1.1 2010/02/24 02:39:08 saly Exp $
     * @copyright 2003 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/learn/mycourse/lib.php');
    require_once(sysDocumentRoot . '/lang/mycourse.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    // $sysSession->cur_func='700300400';
    // $sysSession->restore();
    if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    if (!isset($_POST['tabs'])) {
        if (!in_array($sysSession->cur_func, $sys_func_id)) {
            $tabs = 1;
        } else {
            $res = array_search($sysSession->cur_func, $sys_func_id);
            if ($res === false) $tabs = 1;
            else $tabs = $res + 1;
        }
    } else {
        $tabs = intval($_POST['tabs']);
        if ((intval($tabs) < 1) || (intval($tabs) > 6)) $tabs = 1;
    }

    // 我的最愛
    $favorite = getSetting('favorite');
    if (empty($favorite) || ($favorite == 'false')) {
        $tabs = 1;
        $favorite = 'false';
    }


    $ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->ticket . $sysSession->school_id);
    $lang   = str_replace('-', '_', $sysSession->lang);

    $js = <<< EOF
    var MSG_SCHOOL_NAME   = "{$sysSession->school_name}";
    var MSG_FAVORITE      = "{$MSG['tabs_favorites'][$sysSession->lang]}";
    var MSG_SYS_ERROR     = "{$MSG['msg_system_error'][$sysSession->lang]}";
    var MSG_LOADING       = "{$MSG['msg_loading'][$sysSession->lang]}";
    var MSG_EXPAND        = "{$MSG['msg_expend'][$sysSession->lang]}";
    var MSG_COLLECT       = "{$MSG['msg_collect'][$sysSession->lang]}";
    var MSG_ALL_SCHOOL    = "{$MSG['msg_show_all_school'][$sysSession->lang]}";
    var MSG_ALL_MAJOR     = "{$MSG['msg_show_all_study'][$sysSession->lang]}";
    var MSG_ALL_TEACH     = "{$MSG['msg_show_all_teach'][$sysSession->lang]}";
    var MSG_ALL_FAVORITE  = "{$MSG['msg_show_all_favorite'][$sysSession->lang]}";
    var MSG_TARGET        = "{$MSG['msg_sel_target'][$sysSession->lang]}";
    var MSG_SEL_COURSE    = "{$MSG['msg_sel_elective'][$sysSession->lang]}";
    var MSG_COURSE_ADD    = "{$MSG['msg_add_course'][$sysSession->lang]}";
    var MSG_COURSE_DEL    = "{$MSG['msg_del_course'][$sysSession->lang]}";
    var MSG_SUCCESS       = "{$MSG['msg_success'][$sysSession->lang]}";
    var MSG_FAIL          = "{$MSG['msg_fail'][$sysSession->lang]}";
    var MSG_NO_ELECTIVE   = "{$MSG['msg_no_elective_list'][$sysSession->lang]}";
    var MSG_SEND_SUCCESS  = "{$MSG['msg_send_success'][$sysSession->lang]}";
    var MSG_SEND_FAIL     = "{$MSG['msg_send_fail'][$sysSession->lang]}";
    var MSG_EDIT_FOLDER   = "{$MSG['btn_edit_folder'][$sysSession->lang]}";
    var MSG_ALT_EDFOLDER  = "{$MSG['btn_alt_edit_folder'][$sysSession->lang]}";
    var MSG_NOT_MV_UP     = "{$MSG['msg_cant_up'][$sysSession->lang]}";
    var MSG_NOT_MV_DOWN   = "{$MSG['msg_cant_down'][$sysSession->lang]}";
    var MSG_SAME_COURSE   = "{$MSG['msg_has_same_course'][$sysSession->lang]}";
    var MSG_SAME_SRC_TGT  = "{$MSG['msg_same_source_target'][$sysSession->lang]}";
    var MSG_DROP_SUCCESS  = "{$MSG['drop_elective_success'][$sysSession->lang]}";
    var MSG_DROP_FAIL  = "{$MSG['drop_elective_fail'][$sysSession->lang]}";

    // 課程狀態
    var cs_status = new Array(
        "{$MSG['cs_state_close'][$sysSession->lang]}",
        "{$MSG['cs_state_open_a'][$sysSession->lang]}",
        "{$MSG['cs_state_open_a_date'][$sysSession->lang]}",
        "{$MSG['cs_state_open_n'][$sysSession->lang]}",
        "{$MSG['cs_state_open_n_date'][$sysSession->lang]}",
        "{$MSG['cs_state_prepare'][$sysSession->lang]}");

    var theme = "{$sysSession->theme}/{$sysSession->env}";
    var ticket = "{$ticket}";
    var lang = "{$lang}";
    var favorite = {$favorite};
    var obj = window.scrollbars;
    if ((typeof(obj) == "object") && (obj.visible == true)) {
        obj.visible = false;
    }

    window.onload = function () {
        document.body.scroll = "no";
        chkBrowser();
        do_func("list_group", "{$tabs}");
        winExpand(true);
    };

    window.onunload = function () {
        window.onresize = function () {};
        document.body.scroll = "no";
        top.FrameExpand(0, false, '');
    };
EOF;

    $css = <<< EOF
    .cssTbBlur{
       background-color:#ECECEC !important;
       color:black !important;
       border-color:#ECECEC !important;
       font-size: 1em !important;
    }
    .cssTbFocus{
       background-color:#ECECEC !important;
       color: #FF7800 !important;
       border-color:#ECECEC !important;
       font-size: 1em !important;
       font-weight: bold;
    }
EOF;
    showXHTML_head_B($MSG['title_course_group'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_CSS('inline', $css);
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', './course_tree.js?'.time());
    showXHTML_script('inline', $js);
    showXHTML_head_E('');
    showXHTML_body_B('class="cssTbBodyBg" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" style="background-color: #ececec;"');
if(preg_match('/(?i)msie [5-8]/',$_SERVER['HTTP_USER_AGENT']))
{
    // if IE<=8
    echo '<table width="100%" height="1024"><tr><td>&nbsp;</td></tr></table>';
}
    showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="right"');
        showXHTML_tr_B('');
            showXHTML_td_B('style="padding: 6px 5px 0px 0px;"');
                echo '<a href="javascript:;" onclick="return winExpand(true)" id="IconExpand" style="display:none"><img src="/public/images/icon_open_hr.png" border="0" alt="' . $MSG['msg_expend'][$sysSession->lang] . '" title="' . $MSG['msg_expend'][$sysSession->lang] . '"></a>';
                echo '<a href="javascript:;" onclick="return winExpand(false)" id="IconCollection" style="display:block"><img src="/public/images/icon_close_hr.png" border="0" alt="' . $MSG['msg_collect'][$sysSession->lang] . '" title="' . $MSG['msg_collect'][$sysSession->lang] . '"></a>';
            showXHTML_td_E('');
        showXHTML_tr_E('');
    showXHTML_table_E('');

    echo '<div id="ToolBar" class="cssToolbar" style="clip: auto; bottom:0px; top:42px; background-color:#ECECEC">';
    showXHTML_table_B('width="150" border="0" cellspacing="0" cellpadding="0"');
        showXHTML_tr_B('');
            showXHTML_td_B('');
                showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
if(!preg_match('/(?i)msie [5-8]/',$_SERVER['HTTP_USER_AGENT']))
{
    // if IE<=8
                    showXHTML_tr_B('class="cssTrEvn"');
                        // 版面問題，所以自己輸出
                        echo '<td width="3" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/academic/cl2.gif" width="3" height="3" border="0"></td>';
                        echo '<td align="right" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/academic/cl3.gif" width="3" height="3" border="0"></td>';
                    showXHTML_tr_E('');
}
                    showXHTML_tr_B('class="cssTrEvn" onclick="showRoot();"');
                        showXHTML_td_B('colspan="2" nowrap="nowrap" id="allCSTitle"');
                            echo '&nbsp;<img src="/theme/' . $sysSession->theme . '/academic/icon_book.gif" width="22" height="12" border="0" align="absmiddle" onclick="do_func(\'favorite\', \'\'); event.cancelBubble = true;">&nbsp;';
                            echo '<a href="javascript:;" class="cssTbHead" id="sname">' . $sysSession->school_name . '</a>';
                            echo '<span id="editDir">&nbsp;</span>';
                        showXHTML_td_E('');
                    showXHTML_tr_E('');
                showXHTML_table_E('');
            showXHTML_td_E('');
        showXHTML_tr_E('');
        showXHTML_tr_B('');
            showXHTML_td_B('');
                showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" class="cssTbTable"');
                    showXHTML_tr_B('class="cssTbTr"');
                        showXHTML_td_B('colspan="2" class="cssTbTd" nowrap id="CGroup" style="background-color:#ECECEC;border-color:#ECECEC;"');
                        showXHTML_td_E('');
                    showXHTML_tr_E('');
                showXHTML_table_E('');
            showXHTML_td_E('');
        showXHTML_tr_E('');
    showXHTML_table_E('');
    echo '</div>';
    echo '<div id="ToolBar" class="cssTbBugIE5" style="background-color:#ECECEC;">&nbsp;</div>';
    showXHTML_body_E('');
?>

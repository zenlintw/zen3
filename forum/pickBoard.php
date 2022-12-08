<?php
    /**
     * 討論版 - 挑選課程
     *
     * @since   2004/09/10
     * @author  KuoYang Tsao
     * @version $Id: pickBoard.php,v 1.1 2010/02/24 02:39:00 saly Exp $
     * @copyright 2004 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/forum.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '700300400';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $tabs   = 2;
    $ticket = md5(sysTicketSeed . $sysSession->username . 'pickboard' . $sysSession->board_id);
    $lang   = str_replace('_', '-', strtolower($sysSession->lang));

    $js = <<< EOF
    var MSG_SYS_ERROR     = "{$MSG['msg_system_error'][$sysSession->lang]}";
    var MSG_LOADING       = "{$MSG['msg_loading'][$sysSession->lang]}";
    var MSG_EXPAND        = "{$MSG['msg_expend'][$sysSession->lang]}";
    var MSG_COLLECT       = "{$MSG['msg_collect'][$sysSession->lang]}";
    var MSG_ALL_TEACH     = "{$MSG['msg_show_all_teach'][$sysSession->lang]}";
    var MSG_SUCCESS       = "{$MSG['successful'][$sysSession->lang]}";
    var MSG_COLLECT       = "{$MSG['msg_collect'][$sysSession->lang]}";
    var MSG_NO_BOARD      = "{$MSG['cannot_find'][$sysSession->lang]}{$MSG['board'][$sysSession->lang]}";

    var theme  = "{$sysSession->theme}";
    var ticket = "{$ticket}";
    var lang   = "{$lang}";

    var SchoolName     = '{$sysSession->school_name}';
    var TitleName      = '{$sysSession->school_name}';
    var title_template = '<a href=\'javascript:void(null);\' onClick=\'%f%;return false;\' class=\'cssTbHead\'>%n%</a>';
    var cur_act        = '';

    var obj = window.scrollbars;
    if ((typeof(obj) == "object") && (obj.visible == true)) {
        obj.visible = false;
    }

    window.onload = function () {
        document.body.scroll = "no";
        chkBrowser();
        do_func("list_group", "{$tabs}");
    };

    window.onunload = function () {
        window.onresize = function () {};
        document.body.scroll = "no";
    };

    function getTitle(func, name) {
        var title = title_template.replace('%f%', func);
        return title.replace('%n%',name);
    };

    function replaceTitle(title) {
        oTitle = document.getElementById('title');
        oTitle.innerHTML = title;
    }

EOF;
    showXHTML_head_B($MSG['repost'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', './pickBoard.js?' . time());
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E('');
    showXHTML_body_B('class="cssTbBodyBg" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0"');


    echo '<div id="ToolBar" class="cssToolbar" style="top:2;left:2;width: 250px; height: 240px; overflow: auto;">';
    showXHTML_table_B('width="250" border="0" cellspacing="0" cellpadding="0"');
        showXHTML_tr_B('');
            showXHTML_td_B('');
                showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
                    showXHTML_tr_B('class="cssTrEvn"');
                        // 版面問題，所以自己輸出
                        echo '<td width="3" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/academic/cl2.gif" width="3" height="3" border="0"></td>';
                        echo '<td align="right" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/academic/cl3.gif" width="3" height="3" border="0"></td>';
                    showXHTML_tr_E('');
                    showXHTML_tr_B('class="cssTrEvn"'); //  onclick="showRoot();"
                        showXHTML_td_B('colspan="2" nowrap="nowrap" id="allCSTitle"');
                            echo '&nbsp;<img src="/theme/' . $sysSession->theme . '/academic/icon_book.gif" width="22" height="12" border="0" align="absmiddle" event.cancelBubble = true;">&nbsp;';
                            echo '<span id="title"><a href=\'javascript:void(null);\' onClick=\'do_func("group",10000000);\' class="cssTbHead">' . $sysSession->school_name . '</a></span>';
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
                        showXHTML_td_B('colspan="2" class="cssTbTd" nowrap id="CGroup"');
                        showXHTML_td_E('');
                    showXHTML_tr_E('');
                showXHTML_table_E('');
            showXHTML_td_E('');
        showXHTML_tr_E('');
        showXHTML_tr_B('');
            showXHTML_td_B('');
                showXHTML_input('button','btnClose',$MSG['close_window'][$sysSession->lang],'','onClick="window.close();"');
            showXHTML_td_E('');
        showXHTML_tr_E('');
    showXHTML_table_E('');
    echo '</div>';
    echo "</body>";
?>

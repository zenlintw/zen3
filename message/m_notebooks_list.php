<?php
    /**
     * 筆記本列表
     *
     * 建立日期：2015/4/27
     * @author  CCH
     * @version $Id: m_notebook_list.php, v 1.0 2015/4/27 edi Exp $
     * @copyright 2015 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
//    require_once(sysDocumentRoot . '/lang/msg_center.php');
    require_once(sysDocumentRoot . '/lang/mooc_notebook.php');
    require_once(sysDocumentRoot . '/message/lib.php');

    // 筆記本→筆記管理→寫筆記
    $sysSession->cur_func = '2600200100';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $ticket = md5($sysSession->username . 'Message' . $sysSession->ticket . $sysSession->school_id);
    $lang   = strtolower($sysSession->lang);

    $folder_id = getFolderId();
//    if ($sysSession->cur_func == $msgFuncID['notebook']) {
            $title  = $MSG['tabs_notebook_title'][$sysSession->lang];
            $target = 'notebook.php';
            $isNB   = 'true';
//    } else {
//            $title  = $MSG['title'][$sysSession->lang];
//            $target = 'index.php';
//            $isNB   = 'false';
//    }
    
    $js = <<< EOF
    var cticket = "{$_COOKIE['idx']}";
    var ticket    = "{$ticket}";
    var theme     = "{$sysSession->theme}/{$sysSession->env}";
    var lang      = "{$lang}";
    var folder_id = "{$folder_id}";
    var targetf   = "{$target}";
    var isNB      = {$isNB};
//    var bodyHeight = 0, bodyWidth = 0;
//    var obj = window.scrollbars;
//    if ((typeof(obj) == "object") && (obj.visible == true)) {
//            obj.visible = false;
//    }

//    var MSG_TITLE         = "{$MSG['title'][$sysSession->lang]}";
//    var MSG_HELP          = "{$MSG['mage_help'][$sysSession->lang]}";
//    var MSG_SYS_ERROR     = "{$MSG['mage_sys_error'][$sysSession->lang]}";
//    var MSG_NEW_FOLDER    = "{$MSG['mage_new_folder'][$sysSession->lang]}";
//    var MSG_CANT_MOVE     = "{$MSG['mage_not_move'][$sysSession->lang]}";
//    var MSG_SEL_MOVE_NODE = "{$MSG['mage_sel_move'][$sysSession->lang]}";
//    var MSG_CANT_DEL      = "{$MSG['mage_not_del'][$sysSession->lang]}";
//    var MSG_SYS_CANT_DEL  = "{$MSG['mage_sys_folder'][$sysSession->lang]}";
//    var MSG_SEL_DEL_NODE  = "{$MSG['mage_sel_del'][$sysSession->lang]}";
//    var MSG_SEL_CP_NODE   = "{$MSG['mage_sel_copy'][$sysSession->lang]}";
//    var MSG_SEL_CUT_NODE  = "{$MSG['mage_sel_cut'][$sysSession->lang]}";
//    var MSG_SEL_PSE_NODE  = "{$MSG['mage_sel_post'][$sysSession->lang]}";
//    var MSG_CLIP_EMPTY    = "{$MSG['mage_clip_empty'][$sysSession->lang]}";
//    var MSG_CANT_EDIT     = "{$MSG['mage_not_edit'][$sysSession->lang]}";
//    var MSG_SEL_EDIT_NODE = "{$MSG['mage_sel_edit'][$sysSession->lang]}";
//    var MSG_CONFIRM_DEL   = "{$MSG['mage_confirm_del'][$sysSession->lang]}";
//    var MSG_CONFIRM_SAVE  = "{$MSG['mage_confirm_save'][$sysSession->lang]}";
//    var MSG_SAVE_SUCCESS  = "{$MSG['mage_save_succ'][$sysSession->lang]}";
//    var MSG_SAVE_FAIL     = "{$MSG['mage_save_fail'][$sysSession->lang]}";
//    var MSG_EXPAND        = "{$MSG['mage_expand'][$sysSession->lang]}";
//    var MSG_COLLECT       = "{$MSG['mage_collect'][$sysSession->lang]}";
// /////////////////////////////////////////////////////////////////////////////
//    function getSelFolder() {
//            var nodes = null;
//            nodes = document.getElementsByTagName("input");
//            if ((nodes == null) || (nodes.length <= 0)) return false;
//            for (var i = 0; i < nodes.length; i++) {
//                    if ((nodes[i].type == "radio") && nodes[i].checked) {
//                            return nodes[i].value;
//                    }
//            }
//            return '';
//    }

//    window.onresize = function () {
//            var obj = document.getElementById("ToolBar");
//            if (obj == null) return false;
//            bodyHeight = (isIE) ? document.body.clientHeight : window.innerHeight;
//            bodyHeight = Math.max(parseInt(bodyHeight) - 30, 0);
//            bodyWidth  = (isIE) ? document.body.clientWidth : window.innerWidth;
//            bodyWidth  = Math.max(parseInt(bodyWidth) - 12, 0);
//
//            obj.style.height = parseInt(bodyHeight);
//            if (parseInt(bodyWidth) <= 30) {
//                    bodyWidth = 20;
//                    winFolderExpand(false);
//            }
//            obj.style.width = bodyWidth;
//            obj.firstChild.style.width = bodyWidth;
//    };

    window.onload = function () {
        // document.body.scroll = "no";
        chkBrowser();
        xmlHttp = XmlHttp.create();
        xmlVars = XmlDocument.create();
        do_func("list_folder", "");
        // parent.FrameExpand(1, true, 0);
    };

//    window.onunload = function () {
//            document.body.scroll = "no";
//            parent.FrameExpand(0, false, 0);
//    };
EOF;
//echo '<pre>';
//var_dump($_GET);
//echo '</pre>';
    $smarty->assign('cticket', $cticket);
    $smarty->assign('msg', $MSG);

    $smarty->display('common/tiny_header.tpl');
    $smarty->display('notebook/notebooks_list.tpl');
    $smarty->display('common/tiny_footer.tpl');
    
    showXHTML_head_B($title);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('inline', $js);
    showXHTML_script('include', 'm_lib.js');
    showXHTML_head_E('');
    
//    showXHTML_body_B('class="cssTbBodyBg" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0"');
//    showXHTML_body_B('');
//    showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="right"');
//        showXHTML_tr_B('');
//                showXHTML_td_B('class="cssTbBtn"');
//                        echo '<a href="javascript:;" onclick="return winFolderExpand(true)" id="IconExpand" style="display:none"><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_expand.gif" border="0" alt="' . $MSG['mage_expand'][$sysSession->lang] . '"></a>';
//                        echo '<a href="javascript:;" onclick="return winFolderExpand(false)" id="IconCollection" style="display:block"><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_collection.gif" border="0" alt="' . $MSG['mage_collect'][$sysSession->lang] . '"></a>';
//                showXHTML_td_E('');
//        showXHTML_tr_E('');
//    showXHTML_table_E('');

//    echo '<div id="ToolBar" class="cssToolbar" style="width: 190px; height: 200px; overflow: auto;">';
//        showXHTML_table_B('width="190" border="0" cellspacing="0" cellpadding="0"');
//        showXHTML_table_B('');
//            showXHTML_tr_B('');
//                showXHTML_td_B('');
//                    showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
//                        showXHTML_tr_B('class="cssTrEvn"');
//                            // 版面問題，所以自己輸出
//                            echo '<td width="3" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/cl2.gif" width="3" height="3" border="0"></td>';
//                            echo '<td align="right" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/cl3.gif" width="3" height="3" border="0"></td>';
//                        showXHTML_tr_E('');
//
//                        showXHTML_tr_B('class="cssTrEvn"');
//                            showXHTML_td_B('colspan="2" nowrap="nowrap"');
//                                echo '&nbsp;<img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_book.gif" width="22" height="12" border="0" align="absmiddle">';
//                                if ($sysSession->cur_func == $msgFuncID['notebook']) {
//                                    $txt = '<a href="javascript:;" onclick="mouseEvent(this, 5); return false;" MyAttr="sys_notebook" class="cssTbHead">' . $title . '</a>';
//                                    showXHTML_input('radio', 'actTarget', array('sys_notebook'=>$txt), '', '');
//                                    echo '<br />', str_repeat('&nbsp;', 8);
//                                } else {
//                                    echo '&nbsp;<span class="cssTbHead">' . $title . '</span>';
//                                    echo '<br />', str_repeat('&nbsp;', 5);
//                                }
//                                echo '<a href="javascript:;"  onclick="do_func(\'manage\', \'\'); return false;" class="cssAnchor" title="' . $MSG['mage_folder_mage_help'][$sysSession->lang] . '">' . $MSG['mage_folder_mage'][$sysSession->lang] . '</a>';
//                            showXHTML_td_E('');
//                        showXHTML_tr_E('');
//                    showXHTML_table_E('');
//                showXHTML_td_E('');
//            showXHTML_tr_E('');
//            showXHTML_tr_B('');
//                showXHTML_td_B('');
//                    showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" class="cssTbTable"');
//                        showXHTML_tr_B('style="cursor: default;" class="cssTbTr"');
//                            showXHTML_td_B('colspan="2" class="cssTbTd" nowrap id="Folder"');
//                            showXHTML_td_E('');
//                        showXHTML_tr_E('');
//                    showXHTML_table_E('');
//                showXHTML_td_E('');
//            showXHTML_tr_E('');
//        showXHTML_table_E('');
//    echo '</div>';
//    showXHTML_body_E('');
<?php
    /**
     * WM3 的工具列
     *
     * @since   2004/04/06
     * @author  ShenTing Lin
     * @version $Id: wm_toolbar.php,v 1.1 2010/02/24 02:39:34 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/wm_toolbar.php');
    require_once(sysDocumentRoot . '/lib/common.php');

    function showXHTML_toolbar($title, $title_extra, $btns, $js, $selRang=false, $rangFunc='', $icon='icon_book.gif', $showIcon=true, $headTitle='') {
        global $sysSession, $MSG;
                $isMobile = isMobileBrowser() ? '1' : '0';

        // 基本的 JavaScript
        $tb_js = <<< EOF
    var iconLeft = -10;
    var obj = window.scrollbars;
        var isMobile = '{$isMobile}';
    if ((typeof(obj) == "object") && (obj.visible == true)) {
        obj.visible = false;
    }

    function winExpand(val) {
        var obj1 = document.getElementById("IconExpand");
        var obj2 = document.getElementById("IconCollection");
        var obj3 = document.getElementById("ToolBar"); // Tools");
        if ((obj3 == 'undefined')&&(obj3 == null)) {
            obj3 = document.getElementById("Tools");
        }
        var obj4 = document.getElementById("tlTitle");
        if ((obj1 == null) || (obj2 == null) || (obj3 == null) || (obj4 == null))
            return false;

        if (val) {
            obj4.style.visibility = "visible";
            obj3.style.position = '';
            obj3.style.visibility = 'visible';
            obj3.style.overflow = 'auto';
            obj3.style.left = "5px";
            obj1.style.display = "none";
            obj2.style.display = "";
            if (obj2.offsetParent != null) obj2.offsetParent.style.padding = "6px 5px 0px 0px";
            top.FrameExpand(1, false, '');
        } else {
            obj4.style.visibility = "hidden";
            obj3.style.position = 'relative';
            obj3.style.visibility = 'hidden';
            obj3.style.overflow = 'hidden';
            obj3.style.left = iconLeft;
            obj1.style.display = "";
            obj2.style.display = "none";
            if (obj1.offsetParent != null) obj1.offsetParent.style.padding = "6px 0px 0px 0px";
            /*#47351 Chrome [管理者/問卷管理/問卷維護] 左邊的問卷維護工具列出現scroll bar：調整縮小時的版面*/
            top.FrameExpand(2, false, '35');
        }
        return false;
    }

    function selRang() {
        var ary = new Array(0, 0);
        var obj1 = null,
            obj2 = null;
        var val1 = 0,
            val2 = 0;

        obj1 = document.getElementById("idFrom");
        obj2 = document.getElementById("idTo");
        if ((obj1 == null) || (obj2 == null)) {
            // alert("System Error!");
            return false;
        }
        val1 = parseInt(obj1.value);
        ary[0] = isNaN(val1) ? 0 : val1;
        val1 = ary[0];

        val2 = parseInt(obj2.value);
        ary[1] = isNaN(val2) ? 0 : val2;
        val2 = ary[1]; {$rangFunc}
    }

    function rangAct() {
        var ary = new Array(0, 0);
        var obj = null;
        var val1 = 0,
            val2 = 0;
        val1 = prompt("{$MSG['tbmsg_select_range'][$sysSession->lang]}\\n{$MSG['tbmsg_select_from'][$sysSession->lang]}", "");
        if (val1 == null) return false;
        val1 = parseInt(val1);
        obj = document.getElementById("idFrom");
        obj.value = isNaN(val1) ? 0 : val1;
        val1 = obj.value;
        ary[0] = val1;

        val2 = prompt("{$MSG['tbmsg_select_range'][$sysSession->lang]}\\n{$MSG['tbmsg_select_to'][$sysSession->lang]}", "");
        if (val2 == null) return false;
        val2 = parseInt(val2);
        obj = document.getElementById("idTo");
        obj.value = isNaN(val2) ? 0 : val2;
        val2 = obj.value;
        ary[1] = val2; {$rangFunc}
    }

    var isIE = false,
        isMZ = false;

    function chkBrowser() {
        var re = new RegExp("MSIE", "ig");
        if (re.test(navigator.userAgent)) {
            isIE = true;
        }

        re = new RegExp("Gecko", "ig");
        if (re.test(navigator.userAgent)) {
            isMZ = true;
        }
    }          
            
    window.onresize = function() {
            // 關閉選單，重新載入相同URL，CHROME移除 onload, unonload 事件，IE11仍保留
            if (document.getElementById("tlTitle").style.visibility === 'hidden') {
                top.FrameExpand(2, false, '35');
            } else {
                top.FrameExpand(1, false, '');
            }            
            
        var obj = document.getElementById("ToolBar");
        if (obj == null) return false;
        bodyHeight = (isIE) ? document.body.clientHeight : window.innerHeight;
        /*#47351 Chtome[管理者/問卷管理/問卷維護] 左邊的問卷維護工具列出現scroll bar：調整版面垂直捲軸*/
        bodyHeight = Math.max(parseInt(bodyHeight) - 30, 0) - 50;
        bodyWidth = (isIE) ? document.body.clientWidth : window.innerWidth;
//            console.log('bw1:'+bodyWidth);
        bodyWidth = Math.max(parseInt(bodyWidth) - 12, 0)+5;
            
            /* 小於0時，ie8出現「引數錯誤」*/
            if (parseInt(bodyHeight) <= -1) {
                bodyHeight = 0;
            }

        obj.style.height = parseInt(bodyHeight);
            // 2016/8/15 因為edge對於視窗寬度判斷有bug，明明大於30，卻回報只有0或者20
        if (parseInt(bodyWidth) <= 30 && detectIE() !== 13) {
            bodyWidth = 20;
            winExpand(false);
        }

        if (document.getElementById("tlTitle").style.visibility == "hidden") bodyWidth = 30;
            
            /* 行動裝置不重新設定寬度，避免左側工具欄不斷加寬 */
            if (isMobile === '0') {
                obj.style.width = bodyWidth;
            }
        obj.firstChild.style.width = bodyWidth;
    };

    window.onload = function() {
            if (window.console) {console.log('onload');}        
        chkBrowser();
        var nodes = document.getElementsByTagName("img");
        var dleft = 0;
        parent.FrameExpand(1, false, '');
        document.body.scroll = "no";
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].getAttribute("icon") != null) {
                // 讓 IE 與 Mozilla 有相同的版面 (Let IE and Mozilla have same layout)
                dleft = (typeof(event) == "object") ? 0 : 2;
                iconLeft = dleft - (parseInt(nodes[i].offsetParent.offsetLeft) + parseInt(nodes[i].offsetLeft));
                break;
            }
        }
        top.FrameExpand(1, false, '');
        document.body.scroll = "no";
    };

    window.onunload = function() {
            if (window.console) {console.log('onunload');}           
        document.body.scroll = "no";
        top.FrameExpand(0, false, '');
    };
EOF;

    $css = <<< BOF
    .split {
        width: 100%; border: 1px inset #FFFFFF;
        height: 0px;
        line-height: 0px;
        margin: 4px 0px 0px 0px;
    }

    .split[moz] {
        margin: 3px 0px 3px 0px;
    }

    .space {
        width: 100%;
        height: 12px;
        line-height: 0px;
        margin: 4px 0px 0px 0px;
    }
BOF;

        showXHTML_head_B($headTitle);
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
        showXHTML_css('inline', $css);
        showXHTML_script('inline', $tb_js . $js);
        showXHTML_head_E();
        showXHTML_body_B('class="cssTbBodyBg" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0"');
        showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="right"');
            showXHTML_tr_B();
                showXHTML_td_B('class="cssTbBtn"');
                    echo '<a href="javascript:;" onclick="return winExpand(true)" id="IconExpand" style="display:none"><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_expand.gif" border="0" alt="' . $MSG['msg_toolbar_expand'][$sysSession->lang] . '" title="' . $MSG['msg_toolbar_expand'][$sysSession->lang] . '"></a>';
                    echo '<a href="javascript:;" onclick="return winExpand(false)" id="IconCollection" style="display:block"><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_collection.gif" border="0" alt="' . $MSG['msg_toolbar_collect'][$sysSession->lang] . '" title="' . $MSG['msg_toolbar_collect'][$sysSession->lang] . '"></a>';
                showXHTML_td_E();
            showXHTML_tr_E();
        showXHTML_table_E();

        echo '<div id="ToolBar" class="cssToolbar" style="width: 220px; overflow: auto; min-height: 213px;">';
        showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="Tools" style="width: 190px;"');
            showXHTML_tr_B('id="tlTitle"');
                showXHTML_td_B();
                    /*#47351 Chrome[管理者/問卷管理/問卷維護] 左邊的問卷維護工具列出現scroll bar：調整版面水平捲軸*/
                    showXHTML_table_B('width="98%" border="0" cellspacing="0" cellpadding="0"');
                        showXHTML_tr_B('class="cssTrEvn"');
                            // 版面問題，所以自己輸出
                            echo '<td width="3" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/cl2.gif" width="3" height="3" border="0"></td>';
                            echo '<td align="right" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/cl3.gif" width="3" height="3" border="0"></td>';
                        showXHTML_tr_E();
                        showXHTML_tr_B('class="cssTrEvn"');
                            showXHTML_td_B('class="cssTd" colspan="2" nowrap="nowrap"');
                                if ($showIcon) {
                                    if (empty($icon)) $icon = 'icon_book.gif';
                                    echo '&nbsp;<img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/' . $icon . '" width="22" height="12" border="0" align="absmiddle">&nbsp;';
                                }
                                echo '<span id="tlTitle" class="cssTbHead">' . $title . '</span>';
                                echo $title_extra;
                                // echo '<a href="javascript:;"  onclick="do_func(\'list\', \'\'); return false;" class="cssAnchor" title="' . $MSG['btn_alt_return'][$sysSession->lang] . '">' . $MSG['btn_return'][$sysSession->lang] . '</a>';
                            showXHTML_td_E();
                        showXHTML_tr_E();
                    showXHTML_table_E();
                showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B();
                showXHTML_td_B();
                    /*#47351 Chrome[管理者/問卷管理/問卷維護] 左邊的問卷維護工具列出現scroll bar：調整版面水平捲軸*/
                    showXHTML_table_B('width="98%" border="0" cellspacing="0" cellpadding="0" class="cssTbTable" style="margin-top: 0.5em;"');
                        if (is_array($btns) && (count($btns) > 0))
                        {
                            foreach($btns as $btn){
                                showXHTML_tr_B('class="cssTbTd" style="' . $btn[3] . '" onclick="' . $btn[2] . '"');
                                    if ($btn[0] == '-') {
                                        showXHTML_td_B('align="center" style="height: 0px; line-height: 0px; margin: 0px; padding: 0px; ' . $btn[4] . '"');
                                            echo '<div class="split" moz="moz">&nbsp;</div>';
                                        showXHTML_td_E();
                                                                        } else if ($btn[0] == 'SPACE') {
                                        showXHTML_td_B('align="center" style="height: 0px; line-height: 0px; margin: 0px; padding: 0px; ' . $btn[4] . '"');
                                            echo '<div class="space">&nbsp;</div>';
                                        showXHTML_td_E();
                                    } else {
                                        showXHTML_td_B('nowrap style="' . $btn[4] . '"');
                                            echo '<div class="cssTbBlur" onmouseover="this.className=\'cssTbFocus\'" onmouseout="this.className=\'cssTbBlur\'" style="height: auto;">';
                                            echo '<img src="/theme/' , $sysSession->theme , '/academic/' , $btn[1] , '" width="16" height="16" border="0" icon="icon" align="absmiddle" alt="&nbsp;' ,
                                                $btn[0] , '" title="&nbsp;' , $btn[0] , '">&nbsp;', $btn[0];
                                            echo '</div>';
                                        showXHTML_td_E();
                                    }
                                showXHTML_tr_E();
                            }
                        }

                        if ($selRang) {
                            showXHTML_tr_B('class="cssTbTd"');
                                showXHTML_td_B('nowrap');
                                    echo '<div class="cssTbBlur" onmouseover="this.className=\'cssTbFocusFromTo\'" onmouseout="this.className=\'cssTbBlur\'" style="margin-top: 0.5em;" style="height: auto;">';
                                    echo '<span style="width:16px" onclick="rangAct()" ' ,
                                        'alt="' , $MSG['tbmsg_select_range'][$sysSession->lang] , '" title="' , $MSG['tbmsg_select_range'][$sysSession->lang] , '">' ,
                                        '<img src="/theme/' , $sysSession->theme , '/academic/icon_all_i.gif" width="16" height="16" border="0" align="absmiddle" ' ,
                                        ' style="visibility: hidden;" onclick="rangAct()" ' ,
                                        'alt="' , $MSG['tbmsg_select_range'][$sysSession->lang] , '" title="' , $MSG['tbmsg_select_range'][$sysSession->lang] , '">' ,
                                        '&nbsp;</span>' , $MSG['tbmsg_select_from'][$sysSession->lang];
                                    showXHTML_input('text', 'idFrom', '', '', 'id="idFrom" class="cssInput" style="width:26px;"');
                                    echo $MSG['tbmsg_select_to'][$sysSession->lang];
                                    showXHTML_input('text', 'idTo', '', '', 'id="idTo" class="cssInput" style="width:26px;"');
                                    showXHTML_input('button', '', $MSG['btn_select'][$sysSession->lang], '', 'class="cssBtn" onclick="selRang()"');
                                    echo '</div>';
                                    echo '<p>';
                                showXHTML_td_E();
                            showXHTML_tr_E();
                        }

                    showXHTML_table_E();
                showXHTML_td_E();
            showXHTML_tr_E();
        showXHTML_table_E();
        echo '</div>';

        showXHTML_body_E();
    }
?>

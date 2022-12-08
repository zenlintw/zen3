<?php
    /**
     * 管理者的環境
     * $Id: index.php,v 1.1 2010/02/24 02:38:39 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/index.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/common.php');

    $sysSession->cur_func = '100500100';
    $sysSession->restore();
    if (!aclVerifyPermission(100500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    function denyLayout($title, $msg) {
        global $sysSession, $MSG;

        $js = <<< BOF
    var xmlHttp = null;

    function goto_learn() {
        window.location.replace("/learn/");
    }

    window.onload = function () {
        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
    };

    // 判斷是否按下F5
    var reloadKey = false;
    function checkReloadKey() {
        reloadKey = (event.keyCode == 116) ? true : false;
    }
    window.document.onkeydown = checkReloadKey;

    /**
     * 自動登出
     **/
    window.onbeforeunload = function (evnt) {
        if (reloadKey) return;
        if (typeof evnt == "object") {
            if ((evnt.rangeParent == null) && (navigator.userAgent.search(/MSIE [567]/) < 0)) {
                window.onbeforeunload = null;    // 把此函式onbeforreunload disable掉
                xmlHttp.open("POST", "/logout.php", false);
                xmlHttp.send(null);
            }
        } else {
            if ((event.clientY < 0 && event.clientY > -150) && (document.body.clientWidth - event.clientX) < 24 || event.altKey) {
                window.onbeforeunload = null;    // 把此函式onbeforreunload disable掉
                xmlHttp.open("POST", "/logout.php", false);
                xmlHttp.send(null);
            }
        }
    };

BOF;
        showXHTML_head_B('');
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
        showXHTML_script('include', '/lib/xmlextras.js');
        showXHTML_script('inline', $js);
        showXHTML_head_E();
        showXHTML_body_B();
            $ary = array();
            $ary[] = array($title, 'tabs1');
            // $colspan = 'colspan="2"';
            echo '<div align="center">';
            showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
                showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                    showXHTML_tr_B('class="cssTrEvn"');
                        showXHTML_td('', $msg);
                    showXHTML_tr_E();
                    showXHTML_tr_B('class="cssTrOdd"');
                        showXHTML_td_B('align="center"');
                            showXHTML_input('button', 'btnLearn', $MSG['btn_goto_learn'][$sysSession->lang], '', 'onclick="goto_learn();" class="cssBtn"');
                        showXHTML_td_E();
                    showXHTML_tr_E();
                showXHTML_table_E();
            showXHTML_tabFrame_E();
            echo '</div>';
        showXHTML_body_E();
    }

    // 檢查IP限制
    if (checkIPLimit($sysSession->username, 'academic', $sysSession->school_id) < 1) {
        $msg = sprintf($MSG['msg_not_allow_ip'][$sysSession->lang], $_SERVER['REMOTE_ADDR']);
        denyLayout($MSG['tabs_deny_ip'][$sysSession->lang], $msg);
        wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'manager', $_SERVER['PHP_SELF'], $msg);
        die();
    }

    // 檢查是否具備管理者權限
    $level = getAdminLevel($sysSession->username);
    if (!($level & $sysRoles['manager'] ||
          $level & $sysRoles['administrator'] ||
          $level & $sysRoles['root'])
       ) {
        denyLayout($MSG['tabs_deny_ip'][$sysSession->lang], $MSG['not_admin'][$sysSession->lang]);
        wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,2, 'manager', $_SERVER['PHP_SELF'], '不具備管理者的權限');
        die();
    }

    $sysSession->env = 'academic';
    $sysSession->restore();

    // 紀錄
    wmSysLog('100500100',$sysSession->school_id,0,'0', 'manager', '', 'Goto academic');

    /**
     * 展開或隱藏 Frame
     * FrameExpand(val, resize, extra);
     * @pram val : 展開或隱藏
     *         0 : 隱藏
     *         1 : 展開
     *         2 : 自訂，自己決定要顯示多大
     * @pram resize : 要不要捲動軸
     *         true  : 可以變動 frame 的大小
     *         false : 不可變動 frame 的大小
     *
     **/
    $js = <<< BOF

        /*BUG(B) #032225 mars 20140213*/
    function refresh() {
        var resize_zoom=detectZoom();
        var new_sysbar_zoom = (sysbar_zoom * resize_zoom)/100 +',*';
        $('#envAcademic').attr('rows',new_sysbar_zoom);
    }
    var sysbar_zoom = 0;
    $(document).ready(function () {
        ua = navigator.userAgent.toLowerCase();
        sysbar_zoom = ($('#envAcademic').attr('rows').split(','))[0];
        /*if( ~ua.indexOf('chrome') ){
                refresh();
                $(window).on('resize', refresh);
        }*/
    });
        /*BUG(E)#032225 mars 20140213*/
    var isIE = false, isMZ = false;

    function chkBrowser() {
        if (navigator.userAgent.indexOf('MSIE') > -1) {
            isIE = true;
        }

        if (navigator.userAgent.indexOf('Gecko') > -1) {
            isMZ = true;
        }
    }

    function FrameExpand(val, resize, extra) {
        var obj = null;
        obj = document.getElementById("catalog");
        if (obj != null) {
            if (obj.noResize == resize) obj.noResize = !resize;
        }

        obj = document.getElementById("workarea");
        if (obj != null) {
            switch (val) {
                case 0 : obj.cols = "0,*"; break;
                case 2 : obj.cols = extra + ",*"; break;
                default:
                    obj.cols = "200,*";
            }
        }
    }

    /**
     * logout
     **/
    function logout() {
        if(typeof(main.notSave) == 'boolean' && main.notSave) {
            if (!confirm(main.MSG_EXIT)) {
                return;
            }
            else
                main.notSave = false;
        }
        window.location.replace("/logout.php");
    }

    function showOnline(val, v1, v2, v3) {
        if (typeof(sysbar) == "object") {
            if (typeof(sysbar.showSysTime) == "function") sysbar.showSysTime(val);
            if (typeof(sysbar.showOnline)  == "function") sysbar.showOnline(v1, v2, v3);
        }
    }

    var env = "envStudent";
    function chgEnv(val) {
        env = (val == 2) ? "envTeacher" : "envStudent";
    }

    /**
     * @param int val course_id
     * @param int nEnv 無意義(與另外兩個環境一致統一呼叫chgCourse功能)
     * @param int gEnv 要到的環境
     * @param string func 到新環境時預設執行的功能
     * ps: 這裡不判斷nEnv與gEnv，因為管理者環境進入課程一定是要切換環境，所以不作多餘判斷。
     */
    function chgCourse(val, nEnv, gEnv, func) {
        sysbar.goEnv(val, gEnv, func);
    }

    window.onload = function () {
        chkBrowser();
    };
BOF;

    showXHTML_head_B($sysSession->school_name);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
        showXHTML_script('include', '/lib/jquery/jquery.min.js');
        showXHTML_script('include', '/lib/detectzoom.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E('');
    echo <<< BOF
<frameset rows="*,0,0,0,0" framespacing="0" frameborder="No">
    <frameset rows="90,*" frameborder="No" id="envAcademic">
        <frame src="sysbar.php" name="sysbar" frameborder="No" id="sysbar" scrolling="No" noresize>
        <frameset cols="0,*" framespacing="3" frameborder="Yes" border="3" id="workarea" bordercolor="#FFFFFF">
            <frame src="about:blank" name="catalog" id="catalog" frameborder="No" scrolling="Yes">
            <frame src="about:blank" name="main" id="main" frameborder="No" scrolling="Auto">
        </frameset>
    </frameset>
    <frame src="/online/online.php" name="session" id="session" frameborder="No" scrolling="No" noresize>
    <frame name="record" id="record" frameborder="No" scrolling="No" noresize>
    <frame name="empty" id="empty" frameborder="No" scrolling="No" noresize>
    <noframes>
        <body>
            <p>{$MSG['not_support'][$sysSession->lang]}</p>
        </body>
    </noframes>
</frameset>
</html>
BOF;
?>

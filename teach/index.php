<?php
    /**
     * 教室跟辦公室的環境
     * v1.40 以後將教室與辦公室分離
     * $Id: index.php,v 1.1 2010/02/24 02:40:26 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/index.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    $sysSession->cur_func = '1300500100';
    $sysSession->restore();
    if (!aclVerifyPermission(1300500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

    // 取得此身份具不具備教師或助教身份
    $csid = intval($sysSession->course_id);
    $isTeacher = false;
    $isTeacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $csid);
    if (!$isTeacher || $csid < 10000001) {
        // 導回學生環境
        header('Location: /learn/index.php');
        die();
    }

    $sysSession->env = 'teach';
    $src = '/teach/sysbar.php';
    $sysSession->restore();

    $js = <<< BOF
    /*BUG(B) #032225 mars 20140213*/
    function refresh() {
        var resize_zoom=detectZoom();
        var new_sysbar_zoom = (sysbar_zoom * resize_zoom)/100 +',*';        
        $('#envTeacher').attr('rows',new_sysbar_zoom);
    }
    var sysbar_zoom = 0;
    $(document).ready(function () {
        ua = navigator.userAgent.toLowerCase();
        sysbar_zoom = ($('#envTeacher').attr('rows').split(','))[0];
        /*if( ~ua.indexOf('chrome') ){
                refresh();
                $(window).on('resize', refresh);
        }*/
    });
    /*BUG(E)#032225 mars 20140213*/
    var xmlHttp = null;

    /**
     * 展開或隱藏 Frame
     * FrameExpand(val, resize, extra);
     * @pram integer val : 展開或隱藏
     *         0 : 隱藏
     *         1 : 展開
     *         2 : 自訂，自己決定要顯示多大
     * @pram boolean resize : 要不要捲動軸
     *         true  : 可以變動 frame 的大小
     *         false : 不可變動 frame 的大小
     *
     **/
    function FrameExpand(val, resize, extra) {
        var obj = null;
        var objName = "";
        obj = document.getElementById("c_catalog");
        if (obj != null) {
            if (obj.noResize == resize) obj.noResize = !resize;
        }

        obj = document.getElementById("envCourse");
        if (obj != null) {
            switch (val) {
                case 0 : obj.cols = "0,*"; break;
                case 2 : obj.cols = extra + ",*"; break;
                default:
                    obj.cols = "220,*";
            }
        }
    }

    /**
     * 重建選單
     **/
    function rebuildMenu(lang) {
        this.c_sysbar.lang = lang;
        chgCourse(csid, 2, 2);
    }

    window.onload = function () {
        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
    };
    
    // 判斷是否按下F5
    var reloadKey = false;
    /*
    function checkReloadKey() {
        reloadKey = (event.keyCode == 116) ? true : false;
    }
    window.document.onkeydown = checkReloadKey;
    */
    window.document.onkeydown = function (evnt) {
        if (typeof evnt == "object") event = evnt;
        reloadKey = (event.keyCode == 116) ? true : false;
    };

    /**
     * 自動登出
     **/
    window.onbeforeunload = function (evnt) {
        if (reloadKey) return;        
        if (typeof evnt == "object") {
            if ((evnt.rangeParent == null) && (navigator.userAgent.search(/MSIE [567]/) < 0)) {
                return;
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

    /**
     * 登出
     **/
    function logout() {
        if(typeof(c_main.notSave) == 'boolean' && c_main.notSave) {
            if (!confirm(c_main.MSG_EXIT)) {
                return;
            }
            else 
                c_main.notSave = false;
        }
        window.location.replace("/logout.php");
    }
    
    /**
     * 切換課程
     * @param int val course_id
     * @param int nEnv 現在的環境 1: 教室 ; 2: 辦公室
     * @param int gEnv 要到的環境
     * @param string func 到新環境時預設執行的功能
     * 備註 : 若nEnv == gEnv 則只切換課程(sysbar,course_id),反之切換環境(導到對應環境的index.php)
     **/
    var csid = '{$sysSession->course_id}' == '' ? 10000000 : parseInt({$sysSession->course_id});
    function chgCourse(val, nEnv, gEnv, func) {
        if(typeof(c_main.notSave) == 'boolean' && c_main.notSave) {
            if (!confirm(c_main.MSG_EXIT)) {
                if (csid != val) 
                    parent.c_sysbar.document.getElementById('selcourse').value = csid;
                return;
            }
            else c_main.notSave = false;
        }
        if (nEnv == gEnv) {
            // 辦公室
            if ((typeof(c_sysbar) == "object") && (typeof(c_sysbar.goCourse) == "function")) {
                window.onbeforeunload = null;
                c_main.location.replace("about:blank");
                c_catalog.location.replace("about:blank");
                c_sysbar.goCourse(val, "", func);
            }
            csid = parseInt(val);
            if (isNaN(csid) || (csid < 10000000) || (csid > 100000000)) {
                csid = 10000000;
            }
            c_sysbar.updateCourseName();
        }  
        else {
            window.onbeforeunload = null;
            c_sysbar.goEnv(val, gEnv, func);
        }
    }
    
    function showOnline(val, v1, v2, v3) {
        // 辦公室 (Office)
        if (typeof(c_sysbar) == "object") {
            if (typeof(c_sysbar.showSysTime) == "function") c_sysbar.showSysTime(val);
            if (typeof(c_sysbar.showOnline)  == "function") c_sysbar.showOnline(v1, v2, v3);
        }
    }
BOF;

    showXHTML_head_B($sysSession->school_name);
        showXHTML_script('include', '/lib/jquery/jquery.min.js');
        showXHTML_script('include', '/lib/detectzoom.js');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E('');

    // 輸出 frame
    echo <<< BOF
<frameset rows="*,0,0,0" framespacing="0" frameborder="No" id="envAll">
    <frameset rows="140,*" framespacing="0" frameborder="No" id="envTeacher">
        <frame src="/teach/sysbar.php" name="c_sysbar" frameborder="No" id="c_sysbar" scrolling="no" noresize>
        <frameset cols="0,*" framespacing="3" frameborder="Yes" border="3" id="envCourse" bordercolor="#FFFFFF">
            <frame src="about:blank" name="c_catalog" id="c_catalog" frameborder="No" scrolling="Auto">
            <frame src="about:blank" name="c_main" id="c_main" frameborder="No" scrolling="Auto" onload="document.body.focus();">
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

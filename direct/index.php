<?php
    /**
     * 導師環境
     *
     * @since   2003/09/30
     * @author  ShenTing Lin
     * @version $Id: index.php,v 1.1 2010/02/24 02:38:58 saly Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lang/index.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '2400400100';
    $sysSession->restore();
    if (!aclVerifyPermission(2400400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    // 檢查身份 (Begin)
        // 拒絕 guest
    if ($sysSession->username == 'guest') {
        header('Location: /learn/index.php');
        wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'director', $_SERVER['PHP_SELF'], '拒絕存取!');
        die();
    }
        // 必須是使用中的帳號
    $res = checkUsername($sysSession->username);
    if (intval($res) != 2) {
        header('Location: /learn/index.php');
        wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'director', $_SERVER['PHP_SELF'], '非使用中的帳號!');
        die();
    }
        // 具不具備導師或助教的身份
    $cnt = aclCheckRole($sysSession->username, $sysRoles['director'] | $sysRoles['assistant']);
    if (!$cnt) {
        header('Location: /learn/index.php');
        wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 3, 'director', $_SERVER['PHP_SELF'], '不具備導師或助教的身份!');
        die();
    }
    // 檢查身份 (End)

    $sysSession->env = 'direct';
    $sysSession->restore();

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
        var new_sysbar_zoom  = (sysbar_zoom * resize_zoom)/100 +',*';        
        $('#envDirect').attr('rows',new_sysbar_zoom);
    }
    var sysbar_zoom = 0;
    $(document).ready(function () {
        ua = navigator.userAgent.toLowerCase();
        sysbar_zoom = ($('#envDirect').attr('rows').split(','))[0];
        /*if( ~ua.indexOf('chrome') ){
                refresh();
                $(window).on('resize', refresh);
        }*/
    });
    /*BUG(E)#032225 mars 20140213*/
    
    var isIE = false, isMZ = false;
    var xmlHttp = null;

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
     * ps: 這裡不判斷nEnv與gEnv，因為導師環境進入課程一定是要切換環境，所以不作多餘判斷。
     */
    function chgCourse(val, nEnv, gEnv, func) {
        window.onbeforeunload = null;
        sysbar.goEnv(val, gEnv, func);
    }

    window.onload = function () {
        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        chkBrowser();
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

    showXHTML_head_B($sysSession->school_name);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/direct/wm.css");
    showXHTML_script('include', '/lib/jquery/jquery-1.7.2.min.js');
    showXHTML_script('include', '/lib/detectzoom.js');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E('');
    echo <<< BOF
<frameset rows="*,0,0,0,0" framespacing="0" frameborder="No">
    <frameset rows="90,*" frameborder="No" id="envDirect">
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

<?php
    /**
     * 教室跟辦公室的環境
     * v1.40 以後將教室與辦公室分離
     * $Id: index.php,v 1.1 2010/02/24 02:39:05 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
    // 判斷使用者是否使用行動裝置
    $detect = new Mobile_Detect;
    if($detect->isMobile() && !$detect->isTablet()){
        header("LOCATION: /learn/path/m_pathtree.php");
        exit;
    }

    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/index.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/mooc/models/school.php');


    $sysSession->cur_func = '1300500100';
    $sysSession->env = 'learn';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }
        $rsSchool = new school();
        $studentMooc = $rsSchool->getSchoolStudentMooc($sysSession->school_id);

        $count = $sysConn->GetOne('select count(*) from WM_term_course where course_id=' . $sysSession->course_id . ' and (status=1 or (status=2 and (isnull(st_begin) or st_begin<=CURDATE()) and (isnull(st_end) or st_end>=CURDATE())))');

        $guest = dbGetOne(sysDBname.'.`WM_school`', '`guest`',"`school_id` = 10001 and school_host='$_SERVER[HTTP_HOST]'");
        if (($sysSession->username === 'guest') && ($guest!='Y' || $count==0) && defined('sysEnableMooc') && (sysEnableMooc > 0)) {
            header('Location: /mooc/login.php');
            exit();
        }
    $src = 'about:blank';

    $js = <<< BOF
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
        obj = document.getElementById("s_catalog");
        if ((obj != null) && (obj.noResize == resize)) {
            obj.noResize = !resize;
        }

        obj = document.getElementById("envClassRoom");
        if (obj != null) {
            switch (val) {
                case 0 : obj.cols = "0,*"; break;
                case 2 : obj.cols = extra + ",*"; break;
                default:
                    obj.cols = "200,*";
            }
        }
    }

    function toggleSidebar(visible) {
        var elem = document.getElementById("envMooc");

        if (elem !== null) {
            if (visible === undefined) {
                if (elem.cols === '250,*') {
                    elem.cols = '0,*';
                } else {
                    elem.cols = '250,*';
                }
            } else {
                elem.cols = visible ? '250,*' : '0,*';
            }
            setTimeout(
                function () {document.getElementById("s_sysbar").contentWindow.showToggleArrow()},
                500
            );
        }
    }

    /**
     * 重建選單
     **/
    function rebuildMenu(lang) {
        this.s_sysbar.lang = lang;
        chgCourse(csid, 1, 1);
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
        return;
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

    /**
     * 登出
     **/
    function logout() {
        if(typeof(s_main.notSave) == 'boolean' && s_main.notSave) {
            if (!confirm(s_main.MSG_EXIT)) {
                return;
            }
            else
                s_main.notSave = false;
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
    var csid = '{$sysSession->course_id}' == '' ? 10000000 : parseInt({$sysSession->course_id}, 10);
    function chgCourse(val, nEnv, gEnv, func) {
            // add try catch prevent cross-origin frame js error
            try {
                if (typeof(s_main.notSave) == 'boolean' && s_main.notSave) {
                    if (!confirm(s_main.MSG_EXIT)) {
                        if (csid != val) {
                           if (typeof(moocSysbar.setSelCourseIdVal) == "function") {
                               moocSysbar.setSelCourseIdVal(val);
                           }else{
                               moocSysbar.contentWindow.document.getElementById('selcourse').value = val;
                           }
                        }
                        return;
                    } else s_main.notSave = false;
                }
            } catch (e) {}

        if (nEnv == gEnv) {
            // 學生
            if ((typeof(s_sysbar) == "object") && (typeof(s_sysbar.goCourse) == "function")) {
                window.onbeforeunload = null;
                s_main.location.replace("about:blank");
                s_catalog.location.replace("about:blank");
               if (typeof(moocSysbar.setSelCourseIdVal) == "function") {
                   moocSysbar.setSelCourseIdVal(val);
               }else{
                   moocSysbar.contentWindow.document.getElementById('selcourse').value = val;
               }
                s_sysbar.goCourse(val, "", func);
            }
            csid = parseInt(val);
            if (isNaN(csid) || (csid < 10000000) || (csid > 100000000)) {
                csid = 10000000;
            }
        }
        else {
            window.onbeforeunload = null;
            s_sysbar.goEnv(val, gEnv, func);
        }
    }

    function co_chgCourse(val, gEnv, func, gLan) {
        if(typeof(s_main.notSave) == 'boolean' && s_main.notSave) {
            if (!confirm(s_main.MSG_EXIT)) {
                if (csid != val){
                   if (typeof(moocSysbar.setSelCourseIdVal) == "function") {
                       moocSysbar.setSelCourseIdVal(val);
                   }else{
                       moocSysbar.contentWindow.document.getElementById('selcourse').value = val;
                   }
                }
                return;
            }
            else s_main.notSave = false;
        }

        window.onbeforeunload = null;
        s_sysbar.co_goEnv(val, gEnv, func, gLan);
    }

    function showOnline(val, v1, v2, v3) {
        // 學生 (Student)
        if (typeof(s_sysbar) == "object") {
            if (typeof(s_sysbar.showSysTime) == "function") s_sysbar.showSysTime(val);
            if (typeof(s_sysbar.showOnline)  == "function") s_sysbar.showOnline(v1, v2, v3);
        }
    }

BOF;
    showXHTML_head_B($sysSession->school_name);
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/lib/detectzoom.js');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('inline', $js);
    showXHTML_script('include', 'path/SCORM_adapter131.js?' . time());
    showXHTML_script('include', 'path/SCOPlayerWrapper.js');
    $scr = <<< EOB

    /*BUG(B) #032225 mars 20140213*/
    function refresh() {
        var resize_zoom=detectZoom();
        var new_sysbar_zoom  = (sysbar_zoom * resize_zoom)/100 +',*';
        $('#envStudent').attr('rows',new_sysbar_zoom);
    }
    var sysbar_zoom = 0;
    var original_sysbar_zoom = 0;
    $(document).ready(function () {
        ua = navigator.userAgent.toLowerCase();
        sysbar_zoom = ($('#envStudent').attr('rows').split(','))[0];
        original_sysbar_zoom = sysbar_zoom;
        /*if( ~ua.indexOf('chrome') ){
                refresh();
                $(window).on('resize', refresh);
        }*/
    });
    /*BUG(E)#032225 mars 20140213*/

    var previousActivity = '{$previousActivity}';

    var errorcode12 = null;
    var result = "";

    /**************************************************************************
    **
    ** Function: API Object
    ** Inputs:  Catch all for 1.2 SCO calls
    ** Return:  none
    **
    ** Description:
    ** APIObject is a JavaScript Object that acts as the SCORM 1.2 api and is
    ** used to catch all the SCORM 1.2 calls prior to sending the calls onto
    ** the LMS.
    **
    **************************************************************************/
    function APIObject()
    {
       this.LMSInitialize = LMSInitialize;
       this.LMSFinish = LMSFinish;
       this.LMSGetValue = LMSGetValue;
       this.LMSSetValue = LMSSetValue;
       this.LMSCommit = LMSCommit;
       this.LMSGetLastError = LMSGetLastError;
       this.LMSGetErrorString = LMSGetErrorString;
       this.LMSGetDiagnostic = LMSGetDiagnostic;
    }

    /**************************************************************************
    **
    ** Function: LMSInitialize()
    ** Inputs:  None
    ** Return:  A string indicating whether or not the function completed
    **          successfully (true or false)
    **
    ** Description:
    ** Initialize communication with LMS by calling the Initialize
    ** function which will be implemented by the LMS. The result is returned
    ** by the SCOPlayerWrapper.js file.
    **
    **************************************************************************/
    function LMSInitialize(para)
    {
       // CALL INITIALIZE
        if (para !== '') {
            result = doLMSInitialize(para);
            return result.toString();
        }  
    }

    /**************************************************************************
    **
    ** Function: LMSFinish()
    ** Inputs:  None
    ** Return:  A string indicating whether or not the function completed
    **          successfully (true or false)
    **
    ** Description:
    ** LMSFinish comunicates with the LMS via SCOPlayerWrapper, converting the
    ** LMSFinish call to Terminate and passing on to the LMS.
    **
    **************************************************************************/
    function LMSFinish(para)
    {
       // CALL TERMINATE
       result = doLMSFinish(para);
       return result.toString();
    }

    /**************************************************************************
    **
    ** Function: LMSGetValue()
    ** Inputs:  Name of Element to retrieve
    ** Return:  Result of the getValue call
    **
    ** Description:
    ** This funciton takes in the name of the element that is attempting to be
    ** set, passes the element along to the SCOPlayerWrapper.js and in return
    ** the SCOPlayerWrapper.js file returns the appropriate SCORM 1.2 conformant
    ** value.
    **
    **************************************************************************/
    function LMSGetValue(name)
    {
       result = doLMSGetValue(name);
       return (result == null) ? '' : result.toString();
    }

    /**************************************************************************
    **
    ** Function: LMSSetValue()
    ** Inputs:  Element name and value to set it to
    ** Return:  A string indicating whether or not the function completed
    **          successfully (true or false)
    **
    ** Description:
    ** LMSSetValue accepts the value and element name to set, passing them on
    ** to doLMSSetValue within SCOPlayerWrapper.js to complete the conversion
    ** process communicating with the SCORM 2004 LMS.
    **
    ***************************************************************************/
    function LMSSetValue(name, value)
    {
       result = doLMSSetValue(name, value);
       return result.toString();
    }

    /**************************************************************************
    **
    ** Function: LMSCommit()
    ** Inputs:  None
    ** Return:  A string indicating whether or not the function completed
    **          successfully (true or false)
    **
    ** Description:
    ** LMSCommit calls doLMSCommit within SCOPlayerWrapper.js file passing the
    ** call on to the SCORM 2004 LMS.
    **
    ***************************************************************************/
    function LMSCommit(para)
    {
       var result = doLMSCommit(para);
       return result.toString();
    }

   /***************************************************************************
    **
    ** Function doLMSGetLastError()
    ** Inputs:  None
    ** Return:  The error code that was set by the last LMS function call
    **
    ** Description:
    ** Calls the doLMSGetLastError function, located in SCOPlayerWrapper.js
    ** which converts the SCORM 2004 error code provided by the LMS to
    ** SCORM 1.2.
    **
    ***************************************************************************/
    function LMSGetLastError()
    {
       if ( _InternalErrorCode == 1 )
       {
          // There is no error the APIWrapper caught the last call and did not
          // comunicate with the LMS
          return 0;
       }
       else
       {
          errorcode12 = doLMSGetLastError();
          return errorcode12;
       }
    }

    /***************************************************************************
    **
    ** Function doLMSGetErrorString(errorCode)
    ** Inputs:  errorCode - Error Code
    ** Return:  The textual description that corresponds to the input error code
    **
    ** Description:
    ** Calls doLMSGetErrorString function located within SCOPlayerWrapper.js.
    **
    ***************************************************************************/
    function LMSGetErrorString(errorCode)
    {
       var errString = doLMSGetErrorString(errorCode);
       return errString;
    }

    /***************************************************************************
    **
    ** Function doLMSGetDiagnostic(errorCode)
    ** Inputs:  errorCode - Error Code(integer format), or null
    ** Return:  The vendor specific textual description that corresponds to the
    **          input error code
    **
    ** Description:
    ** Calls doLMSGetDiagnostic function located within SCOPlayerWrapper.js.n
    **
    ***************************************************************************/
    function LMSGetDiagnostic(errorCode)
    {
       var errString = getErrorString(errorCode);
       return errString;
    }

    // JavaScript API object defined
    // var API = new APIObject();
    var API, API_1484_11;

EOB;
    showXHTML_script('inline', $scr);

    showXHTML_head_E('');

    // 輸出 frame
    echo <<< BOF
<frameset rows="*,0,0,0,0,0,0,0,0" framespacing="0" frameborder="No" id="envAll">
BOF;

    if ((defined('sysEnableMooc') && (sysEnableMooc > 0)) || $studentMooc > 0) {
        $uri = ($sysSession->username === 'guest' && defined('sysEnableMooc') && (sysEnableMooc > 0)) ? '../mooc/mycourse.php' : 'about:blank';

        if((strpos($_SERVER['HTTP_USER_AGENT'], 'Android') || strpos($_SERVER['HTTP_USER_AGENT'], 'Linux')) && strpos($_SERVER['HTTP_USER_AGENT'],"Chrome")){
            $row = 120;
        } else {
            $row = 50;
        }
        echo <<< BOF
    <frameset cols="0,*" frameborder="No" id="envMooc">
        <frame src="mooc_sysbar.php" name="mooc_sysbar" frameborder="No" id="moocSysbar" scrolling="no" noresize>
        <frameset rows="{$row},*" frameborder="No" id="envStudent">
        <frame src="mooc_header.php" name="s_sysbar" frameborder="No" id="s_sysbar" scrolling="no" noresize>
            <frameset cols="0,*" framespacing="0" frameborder="yes" border="0" name="envClassRoom" id="envClassRoom" bordercolor="#FFFFFF">
                <frame src="about:blank" name="s_catalog" id="s_catalog" frameborder="No" scrolling="no">
                <frame src="{$uri}" name="s_main" id="s_main" frameborder="No" scrolling="Auto">
            </frameset>
        </frameset>
    </frameset>
BOF;
    } else {
        echo <<< BOF
    <frameset rows="90,*" frameborder="No" id="envStudent">
        <frame src="sysbar.php" name="s_sysbar" frameborder="No" id="s_sysbar" scrolling="no" noresize>
        <frameset cols="0,*" framespacing="3" frameborder="Yes" border="3" id="envClassRoom" bordercolor="#FFFFFF">
            <frame src="about:blank" name="s_catalog" id="s_catalog" frameborder="No" scrolling="Auto">
            <frame src="about:blank" name="s_main" id="s_main" frameborder="No" scrolling="Auto">
        </frameset>
    </frameset>
BOF;
    }
echo <<< BOF
    <frame src="/online/online.php" name="session" id="session" frameborder="No" scrolling="No" noresize>
    <frame name="record" id="record" frameborder="No" scrolling="No" noresize>
    <frame src="" name="empty" id="empty" frameborder="No" scrolling="No" noresize>
    <!-- SCORM2004 Start -->
    <frame name="sequencing" src="">
    <frame name="tocstatus" src="">
    <frame name="check" src="">
    <frame name="functions" src="">
    <frame name="engine" src="">
    <!-- SCORM2004 End -->
    <noframes>
        <body>
            <p>{$MSG['not_support'][$sysSession->lang]}</p>
        </body>
    </noframes>
</frameset>
</html>
BOF;
?>

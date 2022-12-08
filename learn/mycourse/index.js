    /**
     * index.php  JavaScript
     *
     * @since   2003/06/12
     * @author  ShenTing Lin
     * @version $Id: index.js,v 1.1 2010/02/24 02:39:08 saly Exp $
     * @copyright 2003 SUNNET
     **/
    function getTarget(m) {
        var obj = null;
        if (typeof(m) != 'undefined' && this.name == '') return self.s_catalog;
        switch (this.name) {
            case "s_main": obj = parent.s_catalog; break;
            case "c_main": obj = parent.c_catalog; break;
            case "main"  : obj = parent.catalog;   break;
            case "s_catalog": obj = parent.s_main; break;
            case "c_catalog": obj = parent.c_main; break;
            case "catalog"  : obj = parent.main;   break;
        }
        return obj;
    }

    function getSysbar() {
        var obj = null;
        switch (this.name) {
            case "s_main"    :
            case "s_catalog" :
                obj = parent.s_sysbar;
                break;
            case "c_main"    :
            case "c_catalog" :
                obj = parent.c_sysbar;
                break;
            case "main"    :
            case "catalog" :
                obj = parent.sysbar;
                break;
        }
        return obj;
    }

    /**
     * Change Tabs
     * @param integer val : tabs id
     * @return
     **/
    function chgTabs(val) {
        var obj = document.getElementById("chgFm");
        if ((typeof(obj) == "object") && (obj != null)) {
            obj.tabs.value = val;
            window.onunload = function () {};
            obj.submit();
        }
    }

    /**
     * reload Course Group tree
     **/
    function resetTree() {
        var obj = null;
        var tabs = 0;

        obj = document.getElementById("chgFm");
        if ((typeof(obj) == "object") && (obj != null)) {
            tabs = parseInt(obj.tabs.value);
        }
        obj = getTarget();
        if ((typeof(obj) == "object") && (obj != null)) {
            if (typeof(obj.do_func) == "function") obj.do_func("list_group", tabs);
        }
    }

    /**
     * reload index.php
     **/
    function reloadSelf() {
        var obj = document.getElementById("gpFm");
        if ((typeof(obj) == "object") && (obj != null)) {
            window.onunload = function () {};
            obj.submit();
        }
    }

    /**
     * change page
     * @param integer n : action type or page number
     * @return
     **/
    function go_page(n){
        /*
         * course name keyword
        */
        var obj = null;
        var cour_key = "", teacher_key = "", en_begin = "", en_end = "", st_begin = "", st_end = "";
        obj  = document.getElementById("cour_keyword");
        if (obj != null) cour_key = obj.value;
        /*
         * teacher name keyword
        */
        obj  = document.getElementById("teacher");
        if (obj != null) teacher_key = obj.value;

        /*
         * 報名日期(起) (enroll begin)
        */
        obj  = document.getElementById("en_begin");
        if (obj != null) en_begin = obj.value;

        /*
         * 報名日期(訖) (enroll end)
        */
        obj  = document.getElementById("en_end");
        if (obj != null) en_end = obj.value;

        /*
         * 上課日期(起) (study begin)
        */
        obj  = document.getElementById("st_begin");
        if (obj != null) st_begin = obj.value;

        /*
         * 上課日期(訖) (study end)
        */
        var obj  = document.getElementById("st_end");
        var st_end = (obj != null) ? obj.value : "";

        obj  = document.getElementById("actFm");

        if ((typeof(obj) != "object") || (obj == null)) return '';
        obj.course_name.value = cour_key;
        obj.teacher.value = teacher_key;
        obj.en_begin.value = en_begin;
        obj.en_end.value = en_end;
        obj.st_begin.value = st_begin;
        obj.st_end.value = st_end;

        switch(n){
            case -1:    // 第一頁
                obj.page.value = 1;
                break;
            case -2:    // 前一頁
                obj.page.value = parseInt(obj.page.value) - 1;
                if (parseInt(obj.page.value) == 0) obj.page.value = 1;
                break;
            case -3:    // 後一頁
                obj.page.value = parseInt(obj.page.value) + 1;
                break;
            case -4:    // 最末頁
                obj.page.value = parseInt(total_page);
                break;
            default:    // 指定某頁
                obj.page.value = parseInt(n);
                break;
        }
        window.onunload = function () {};
        obj.submit();
    }

    /**
     * show course detail info
     * @param
     * @return
     **/
    function chgDetailStatus(val) {
        var obj = null;
        obj = document.getElementById("DetailTable");
        if (obj != null) obj.style.display = (val) ? "block" : "none";
        obj = document.getElementById("ListTable");
        if (obj != null) obj.style.display = (val) ? "none" : "block";
    }

    function showDetail(csid) {
        var obj = null, res = null;
        var idx = "";
        var enroll = new Array("", ""), study = new Array("", "");

        obj = getTarget(true);
        if ((obj == null) || (typeof(obj.do_func) != "function"))  return false;

        res = obj.do_func("detail", csid);
        for (var i in res) {
            switch (i) {
                case "enroll_begin":
                    enroll[0] = (res[i].length <= 0) ? (MSG_FROM2+MSG_NOW) : (MSG_FROM2 + res[i]);
                    break;
                case "enroll_end":
                    enroll[1] = (res[i].length <= 0) ? (MSG_TO2+MSG_FOREVER) : MSG_TO2 + res[i];
                    break;
                case "study_begin":
                    study[0] = (res[i].length <= 0) ? (MSG_FROM2+MSG_NOW) : MSG_FROM2 + res[i];
                    break;
                case "study_end":
                    study[1] = (res[i].length <= 0) ? (MSG_TO2+MSG_FOREVER) : MSG_TO2 + res[i];
                    break;

                default :
                    obj = document.getElementById("cs_" + i);
                    if (obj != null) obj.innerHTML = res[i];
            }
            //alert(i + " - " + res[i]);
        }

        obj = document.getElementById("cs_enroll");
        if (obj != null) obj.innerHTML = enroll.join("&nbsp;<br />&nbsp;");
        obj = document.getElementById("cs_study");
        if (obj != null) obj.innerHTML = study.join("&nbsp;<br />&nbsp;");

        // show course detail info
        chgDetailStatus(true);
    }

    /**
     * close course detail info
     **/
    function closeDetail() {
        chgDetailStatus(false);
    }

    /**
     * add course to my favorite
     * @param string csid : course id
     **/
    function add_favorite(csid) {
        var obj = null;
        var res = 0;

        obj = getTarget();
        if ((obj == null) || (typeof(obj.do_func) != "function"))  return false;
        res = obj.do_func("add_favorite", csid);
        switch (parseInt(res)) {
            case 0 : alert(MSG_FAVORITE_SUCCESS); break;
            case 1 : alert(MSG_FAVORITE_FAIL); break;
            case 2 : alert(MSG_FAVORITE_EXIST); break;
            default:
                alert(MSG_FAVORITE_FAIL);
        }
    }

    /**
     * 取得勾選的課程
     **/
    function getCheckedCourse() {
        var nodes = null, attr = null, obj = null;
        var cnt = 0;
        var idx = new Array();

        nodes = document.getElementsByTagName("input");
        cnt = nodes.length;
        obj = getTarget();

        if ((obj == null) || (typeof(obj.objCkbox) != "object")) {
            for (var i = 0; i < cnt; i++) {
                if ((nodes[i].type == "checkbox") && nodes[i].checked && (nodes[i].value.length != 0)) {
                    idx[idx.length] = nodes[i].value;
                }
            }
        } else {
            for (var i = 0; i < cnt; i++) {
                if ((nodes[i].type == "checkbox") && nodes[i].checked && (nodes[i].value.length != 0)) {
                    idx[idx.length] = nodes[i].value;
                    obj.objCkbox[nodes[i].value] = true;
                } else if (nodes[i].value.length != 0) {
                    obj.objCkbox[nodes[i].value] = nodes[i].checked;
                }
            }
        }
        return idx;
    }

    /**
     * reselect checkbox
     **/
    function reCheckCourse() {
        var nodes = null, obj = null;
        var cnt = 0;
        var idx = new Array();

        nodes = document.getElementsByTagName("input");
        cnt = nodes.length;
        obj = getTarget();
        if ((obj == null) || (typeof(obj.objCkbox) != "object")) return false;
        for (var i = 0; i < cnt; i++) {
            if ((nodes[i].type == "checkbox") && obj.objCkbox[nodes[i].value]) {
                nodes[i].checked = true;
            }
        }
        chgCheckbox();
        return false;
    }

    function SelCourse(node) {
        var obj = null;

        obj = getTarget();
        if ((obj == null) || (typeof(obj.objCkbox) != "object")) return false;
        obj.objCkbox[node.value] = node.checked;
        chgCheckbox();
        return false;
    }

    function select_func(val) {
        // 這個應該不用到了 (not need)
        if (val) {
            do_func('sel_all');
        } else {
            do_func('sel_clean');
        }
    }

    function do_func(act) {
        var obj = null, nodes = null;
        var ary = new Array();

        switch (act) {
            case "major_add"   :
            case "major_del"   :
                nodes = getCheckedCourse();
                if (nodes.length <= 0) {
                    alert(MSG_SEL_COURSE);
                    return false;
                }

                obj = getTarget();
                if ((obj != null) && (typeof(obj.do_func) == "function")) {
                    obj.do_func(act, nodes);
                    return true;
                }
                alert(MSG_SYS_ERROR);
                break;

            case "major_reset" :
                obj = getTarget();
                if ((obj != null) && (typeof(obj.do_func) == "function")) {
                    obj.do_func(act, "");
                    reloadSelf();
                    return true;
                }
                alert(MSG_SYS_ERROR);
            break;

            case "elective" :    // 送出選課清單 (submit elective list)
                nodes = document.getElementsByTagName("input");
                for (var i = 0; i < nodes.length; i++) {
                    if ((nodes[i].type == "checkbox") && (nodes[i].value != "") && nodes[i].checked) {
                        ary[ary.length] = nodes[i].value;
                    }
                }
                obj = getTarget();
                if ((obj != null) && (typeof(obj.do_func) == "function")) {
                    if (!obj.do_func(act, ary)) {
                        return false;
                    }
                    // reloadSelf();
                    if (this.name == 's_main' || this.name == 's_catalog')
                    {
                        obj = getSysbar();
                        obj.updateCourseList();
                    }
                    chgTabs(6);
                    return true;
                }
                break;

            case "sel_all" :
            case "sel_clean" :
                nodes = document.getElementsByTagName("input");
                if (nodes != null) {
                    for (var i = 0; i < nodes.length; i++) {
                        if (nodes[i].type == "checkbox")
                            nodes[i].checked = (act == "sel_all") ? true : false;
                    }
                }
                getCheckedCourse();
                break;

            case "append" :
            case "move" :
            case "delete" :
            case "up" :
            case "down" :
                nodes = getCheckedCourse();
                if (nodes.length <= 0) {
                    alert(MSG_SRC_COURSE);
                    return false;
                }

                obj = getTarget();
                if ((obj != null) && (typeof(obj.do_func) == "function"))
                    obj.do_func(act, nodes);
                break;
        }
    }

    /**
     * 切換全選或全消的 checkbox
     * @version 1.0
     **/
    function chgCheckbox() {
        var bol = true;
        var obj  = document.getElementById("ck");
        var btn1 = document.getElementById("btnSel1");
        var btn2 = document.getElementById("btnSel2");
        var nodes = document.getElementsByTagName("input");
        if ((nodes == null) || (nodes.length <= 0)) return false;
        var total_checkbox = 0;

        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
            if (nodes[i].checked == false) bol = false;
            if ((nodes[i].type == "checkbox") && (nodes[i].id != "ck")) total_checkbox++;
        }

        if (total_checkbox > 0){
            nowSel = bol;
        }else{
            bol = false;    // none course checkbox default false
        }
        if (obj  != null) obj.checked = bol;
        if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
        if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
        synBtn();
    }

    /**
     * 同步全選或全消的按鈕與 checkbox
     * @version 1.0
     **/
    var nowSel = false;
    function selfunc() {
        var obj  = document.getElementById("ck");
        var btn1 = document.getElementById("btnSel1");
        var btn2 = document.getElementById("btnSel2");
        if ((obj == null) || (btn1 == null) || (btn2 == null)) return false;
        nowSel = !nowSel;
        obj.checked = nowSel;
        // alert(nowSel);
        btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
        btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
        // select_func(!nowSel);
        nowSel ? do_func("sel_all") : do_func("sel_clean");
        synBtn();
    }

    /**
     * 同步按鈕的狀態
     **/
    function synBtn() {
        var btn1 = document.getElementById("btnAdd1");
        var btn2 = document.getElementById("btnAdd2");
        var nodes = document.getElementsByTagName("input");
        if ((nodes == null) || (nodes.length <= 0)) return false;
        for (var i = 0, j = 0; i < nodes.length; i++) {
            if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
            if (nodes[i].checked) j++;
        }
        if (btn1 != null) btn1.disabled = !(j > 0);
        if (btn2 != null) btn2.disabled = !(j > 0);
        var btn1 = document.getElementById("btnDel1");
        var btn2 = document.getElementById("btnDel2");
        if (btn1 != null) btn1.disabled = !(j > 0);
        if (btn2 != null) btn2.disabled = !(j > 0);
    }

    /*
     * 去掉空格
     */
    function trim(str){
         if(str.charAt(0) == " "){
              str = str.slice(1);
              str = Trim(str);
         }
         return str;
    }

    /*
     * 是否為潤年
     */
     function isLeapYear(year){
         if((year%4==0&&year%100!=0)||(year%400==0)) {
             return true;
         }
         return false;
    }

    /*
     * 檢查日期是否合法
     */
    function checkDate(val){
        var datetime;
        var year,month,day;
        var gone,gtwo;
        var date_reg = /^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}$/;

        var datetime=trim(val);
        if(datetime.length > 0){

            if (datetime.search(date_reg) == -1){
                   return 1;
               }

              var temp = datetime.split('-');

            var year = parseInt(temp[0]);
            var month = parseInt(temp[1].replace(/^0/ig,''));
            var day = parseInt(temp[2]);

            if (year == 0){
                return 1;
            }
            if (day == 0){
                return 1;
            }
            if(month<1||month>12) {
                   return 2;
            }
            if(month==2){
                if(isLeapYear(year)&&day>29){
                       return 3;
                 }
                 if(!isLeapYear(year)&&day>28){
                       return 4;
                 }
             }

            if((month==1||month==3||month==5||month==7||month==8||month==10||month==12)&&(day>31)){
                   return 6;
            }

            if((month==4||month==6||month==9||month==11)&&(day>30)){
                return 5;
               }

        }
    }

    /**
     * 加入課程名稱排序
     *
     **/
     
     function SortCourseName(val)
     {
        // 加入課程名稱排序的條件
        obj = document.getElementById("CourseName_SORT");
        obj.value = val;
        queryCourse();
     }
    
    /**
     * 搜尋課程
     *
     **/
    function queryCourse() {
        var obj = null;
        // course name keyword
        obj = document.getElementById("cour_keyword");
        var cour_key = (obj == null) ? "" : obj.value;
        
        obj  = document.getElementById("actFm");
        obj.course_name.value = cour_key;
        obj.isquery.value = "true";
        window.onunload = function () {};
        obj.submit();
    }

    /*
     * 流程：首頁 -> 搜尋& 選課 -> 允許 guest -> 進入 [個人區 - 我的課程 - 全校課程]
     *       選完課之後 -> 幫 user login -> 送出選課清單
     */
    function login() {
        var obj = null, nodes = null;
        var ary = new Array();
        var loginDailog2 = null;

        nodes = getCheckedCourse();

        obj = getTarget();

        var obj = document.getElementById('course_ids');
        obj.value = nodes;

        if ((loginDailog2 != null) && !loginDailog2.closed) {
            loginDailog2.focus();
        } else {
            var rnd = Math.ceil(Math.random() * 100000);
            loginDailog2 = window.open("/learn/login.php", "win" + rnd, "top=250,left=350,width=300,height=150,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1,scrollbar=1");
        }
    }

    window.onload = function () {
        var re = /\/learn\/mycourse\/course_tree\.php$/;
        var obj = null, obj1 = null, obj2 = null;
        if (currTabId == 3) {
            obj = getTarget();
            if ((typeof(obj) == "object") && (obj != null)) {
                if (obj.location.href.match(re) == null) {
                    obj.location.replace("course_tree.php");
                } else if (isTreeLoad) {
                    resetTree();
                }
            }
        }else{
            obj = getTarget();
            if (obj.location.href.match(re) != null) {
                obj.location.replace("about:blank");
                parent.FrameExpand(0, false, '');
            }
        }
                
                if (detectIE() === 13) {
                    $('.title-bar2,.content .subject td').css('border-radius', '0 0 0 0');
                }
    };

    window.onunload = function () {
        var obj = null;
        obj = getTarget();
        if ((typeof(obj) == "object") && (obj != null)) obj.location.replace("about:blank");
    };

    /**
     * 目的 : 退選課程用function
     * @param string act : 使用動作
     * @param string course : 課程編號
     *
     * @return int result : (false,動作成功、true,動作失敗)
     */
    
    function drop_elective(userTicket, act,course) {
        
        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
        if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();

        txt  = "<manifest>";
        txt += "<ticket>" + userTicket + "</ticket>";
        txt += "<action>" + act + "</action>";
        txt += "<course_id>" + parseInt(course) + "</course_id>";
        txt += "</manifest>";

        res = xmlVars.loadXML(txt);
        if (!res) {
            alert(MSG_SYS_ERROR);
            return false;
        }
        xmlHttp.open("POST", "do_function.php", false);
        xmlHttp.send(xmlVars);
        if (!xmlVars.loadXML(xmlHttp.responseText)) {
            alert(MSG_SYS_ERROR);
            return false;
        }
        ticket = getNodeValue(xmlVars.documentElement, "ticket");
        res = getNodeValue(xmlVars.documentElement, "result");
        txt = (parseInt(res)) ? MSG_DROP_SUCCESS : MSG_DROP_FAIL;
        if (window.console) {console.log('txt', txt);}
        location.href = "/learn/mycourse/index.php";
    }
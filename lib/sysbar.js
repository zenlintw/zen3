///////////////////////////////////////////////////////////////////////////////
    document.oncontextmenu = function () {
        return false;
    };
    document.ondragstart = function () {
        return false;
    };
    document.onselectstart = function () {
        return false;
    };
    document.onmousedown = function (e) {
        if (e && e.target && (e.target.tagName.toLowerCase() == "select")) {
            return true;
        }
        return false;
    };
    document.onkeypress = function () {
        return false;
    };
///////////////////////////////////////////////////////////////////////////////
    var isIE = false, isMZ = false, isFF = false;
    var baseUri = "";
    var isResized = false;

    /**
     * 檢查瀏覽器的版本
     **/
    function chkBrowser() {
        var re = new RegExp("MSIE","ig");
        if (re.test(navigator.userAgent)) {
            isIE = true;
        }

        re = new RegExp("Gecko","ig");
        if (re.test(navigator.userAgent)) {
            isMZ = true;
            re = new RegExp("Firefox","ig");
            if (re.test(navigator.userAgent)) {
                isFF = true;
            }
        }
    }

    /**
     * 取得目的地的 Frame
     * @return 目的地的 Frame
     **/
    function getTargetName() {
        var txt = "_blank";
        switch (this.name) {
            case "s_sysbar": txt = "s_main"; break;
            case "c_sysbar": txt = "c_main"; break;
            case "sysbar"  : txt = "main";   break;
        }
        return txt;
    }

    /**
     * 取得另外的視窗
     * @return Object 另外的視窗 (other frame)
     **/
    function getTarget() {
        var obj = null;
        switch (this.name) {
            case "s_main"   : obj = parent.s_catalog; break;
            case "c_main"   : obj = parent.c_catalog; break;
            case "main"     : obj = parent.catalog;   break;
            case "s_catalog": obj = parent.s_main; break;
            case "c_catalog": obj = parent.c_main; break;
            case "catalog"  : obj = parent.main;   break;
            case "s_sysbar" : obj = parent.s_main; break;
            case "c_sysbar" : obj = parent.c_main; break;
            case "sysbar"   : obj = parent.main;   break;
        }
        return obj;
    }

    /**
     * 同步功能種類
     * @param obj : 對象物件
     * @param val : 功能
     **/
    function syncValue(obj, val) {
        if ((typeof(obj) != "object") || (obj == null)) return false;
        if (val == "") {
            obj.selectedIndex = 0;
            return true;
        }
        for (var i = 0; i < obj.length; i++) {
            if (val == obj.options[i].value) {
                obj.selectedIndex = i;
                return true;
            }
        }
        return false;
    }

    /**
     * 清除 HTML 的 Tag
     * @param string str : 要清除的文字
     * @return string : 清除後的結果
     **/
    function strip_tags(str) {
        var re = /<[\/\!]*.?[^<>]*.?>/ig;
        var txt = "";
        txt = str.replace(re, "");
        txt = txt.replace("&amp;", "&");
        txt = txt.replace("&nbsp;", " ");
        return txt;
    }

///////////////////////////////////////////////////////////////////////////////
    /**
     * 顯示時間 (show System Time)
     **/
    function showSysTime(val) {
        var obj = document.getElementById("tdSysTime");
        if (obj != null) {
            obj.innerHTML = val;
        }
    }

    function showOnline(v1, v2, v3) {
        var obj = null;
        obj = document.getElementById("spanSchool");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = v1;
        obj = document.getElementById("spanOnline");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = v2;
        obj = document.getElementById("spanCourse");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = v3;
        rePositionOther();
    }

    var userWin = null;
    function showUserList(url) {
        if (url == null) url = "/online/userlist.php";
        if ((userWin != null) && !userWin.closed) {
            userWin.focus();
        } else {
            // Bug#1257 無法使用右鍵（因為showDialog沒有右鍵功能），改用window.open去開啟視窗 by Small 2006/9/20
            // userWin = showDialog("/online/index.php", false , "", true, "200px", "200px", "660px", "400px", "status=0, resizable=1, scrollbars=1");
            userWin = window.open(url, "_blank", "width=660,height=400,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1");
        }
    }
///////////////////////////////////////////////////////////////////////////////
    /**
     * 顯示子選單
     **/
    function showSubMenu(idx, action) {
        // 先判斷目前功能是否有未存檔的操作
        var tmp = eval('parent.'+getTargetName());
        if (typeof(tmp.notSave) == 'boolean' && tmp.notSave)
            if (!confirm(tmp.MSG_EXIT)) return;
        else
            tmp.notSave = false;

        var obj1 = document.getElementById("mngp_" + menuMIdx);
        var obj2 = document.getElementById("mngp_" + idx);
        if (obj2 != null) {
            if ((menuMIdx != 10000) && (obj1 != null))
                obj1.style.display = "none";
            obj2.style.display = "block";

            obj1 = document.getElementById("mnItem_" + menuMIdx);
            if (obj1 != null) obj1.className = "MMenuItemOut";
            obj2 = document.getElementById("mnItem_" + idx);
            if (obj2 != null) obj2.className = "MMenuItemOver";

            menuMIdx = idx;
            // 重設子選單的移動按鈕 (reset child menu move button)
            reSSclBtn();
            // 自動執行第一個子項目 (auto run first item)
            // over
            if ((username == 'guest') && (typeof(action) != 'undefined')) {
                action = "SYS_07_01_001";
                // 需考慮進入課程後的狀態 (check course status)
            } else {
                // if ((typeof(action) == 'undefined') || (action == 0) || (action == '')) {
                if ((typeof(action) == 'undefined') || (action == 0) || (action == ''))
                    action = getSysGotoLabel();
                if (action == "") {
                    obj1 = document.getElementById("mngp_" + idx);
                    if (obj1 == null) return false;
                    obj2 = obj1.getElementsByTagName("a");
                    if (obj2 == null || obj2.length < 1) return false;
                    action = obj2[0].getAttribute("sysid");
                }
            }
            if (typeof sysGotoLabel == "string") sysGotoLabel = "";
            chgMenuItem(action, true);
        }
    }

    /**
     * 主選單的特效
     **/
    function mmMouseEvent(evnt) {
        var obj = null;
        if (isIE) {
            evnt = event;
            obj = evnt.srcElement;
        } else {
            obj = evnt.currentTarget;
        }
        obj = obj.offsetParent;
        if (obj.id == ("mnItem_" + menuMIdx)) return false;
        switch (evnt.type) {
            case "mouseover": if (obj != null) obj.className = "MMenuItemOver"; break;
            case "mouseout" : if (obj != null) obj.className = "MMenuItemOut"; break;
        }
    }

    /**
     * 子選單的特效
     **/
    function smMouseEvent(evnt) {
        var obj = null, node = null;
        var val = null;

        if (isIE) {
            evnt = event;
            obj = evnt.srcElement;
        } else {
            obj = evnt.currentTarget;
        }

        if (obj.id == ("smnItem_" + menuSIdx)) return true;
        switch (evnt.type) {
            case "mouseover": if (obj != null) obj.className = "SMenuItemOver"; break;
            case "mouseout" : if (obj != null) obj.className = "SMenuItemOut"; break;
            case "click"    :
                isResized = true;
                var x = getTargetName();
                if (x == 's_main')
                {
                    eval('var menuFrame = parent.' + x.substring(0,x.lastIndexOf('_'))+'_catalog;');
                    var sysid = obj.getAttribute('sysid');
                    if (sysid != 'SYS_04_01_002' && menuFrame.location.pathname == '/learn/path/manifest.php')
                    {
                        parent.FrameExpand(0,false,0);
                        var objFrame = menuFrame.document.getElementById("pathtree");
                        if (objFrame                                            != null &&
                            objFrame.contentWindow.fetchResourceForm            != null &&
                            typeof(objFrame.contentWindow.fetchResourceForm.href) != 'undefined' &&
                            objFrame.contentWindow.fetchResourceForm.href.value != 'about:blank')
                        {
                            objFrame.contentWindow.doUnload();
                        }
                    }
                    else if (menuFrame.location.pathname == '/learn/scorm/InitialSCORM.php') {
                        parent.FrameExpand(0, false, 0);
                        menuFrame.doUnload();
                    }
                }

                node = document.getElementById("smnItem_" + menuSIdx);
                if (node != null) {
                    node.className = "SMenuItemOut";
                }

                if (obj != null) {
                    obj.className = "SMenuItemOver";
                    val = obj.id.split("_");
                    menuSIdx = val[1];
                }
                /* 
                if (x == 'main') {
                    parent.FrameExpand(0, false, 0);
                    if (typeof(parent.catalog)=='object') {
                        parent.catalog.location.href='about:blank';
                        if (parent.catalog.src != 'undefined') {
                            parent.catalog.src='about:blank';
                        }
                        parent.catalog.document.body.scroll = "no";
                    }
                }

                                
                if (x == 'c_main') {
                    parent.FrameExpand(0, false, 0);
                    if (typeof(parent.c_catalog) == 'object') {
                        parent.c_catalog.location.href = 'about:blank';
                        if (parent.c_catalog.src != 'undefined') {
                            parent.c_catalog.src = 'about:blank';
                        }
                        parent.c_catalog.document.body.scroll = "no";
                    }
                }
                                */
                break;
        }
    }
///////////////////////////////////////////////////////////////////////////////
    var MDefWidth = 425, SDefWidth = 425;
    var Timer = null;
    var aryPosition = new Array();

    function reposenv()
    {
        var bodyWidth = 796;
        bodyWidth =  (isIE) ? document.body.clientWidth : window.innerWidth;
        obj = document.getElementById("SysHelp");
        if (obj != null) obj.style.left = bodyWidth - obj.offsetWidth - 5;
    }

    function env2short()
    {
        var obj = document.getElementById("SysHelp");
        if (obj == null) {
            return;
        }
        var nodes = obj.getElementsByTagName("div");
        var nw = 0; nl = 1;
        if (parseInt(obj.clientWidth) > 160)
        {
            if (nodes.length > 0)
            {
                nw = parseInt(115 / nodes.length);
                for (var i = 0; i < nodes.length; i++)
                {
                    if (parseInt(nodes[i].offsetWidth) <= nw) continue;
                    nodes[i].setAttribute("orgWidth", parseInt(nodes[i].offsetWidth));
                    nodes[i].setAttribute("srtWidth", parseInt(nw));
                    nodes[i].style.width = nw + "px";
                    nodes[i].style.overflow = "hidden";
                    nodes[i].onmouseout = function ()
                    {
                        // this.style.width = "px";
                        this.style.width = this.getAttribute("srtWidth") + "px";
                        reposenv();
                    };
                    nodes[i].onmouseover = function ()
                    {
                        this.style.width = this.getAttribute("orgWidth") + "px";
                        reposenv();
                    };
                }
            }
        }
    }

    /**
     * 重新設定所有 Layer 的位置
     **/
    function rePosition() {
        var bodyWidth = 796; // 取得可用的寬度 (get width)
        var MSclLeft = 600, SSclLeft = 600; // 定義選單左右移動的位置 (define vertically position)
        var OtherLeft = 0;
        var obj = null;
        /* 修正新套版的位移 */
        if('undefined' != typeof(replace_position)) {
            MSclLeft = replace_position[1];
            SSclLeft = replace_position[1];
        }
        aryPosition = new Array(); // 清除所有的位置 (clearn position)
        MDefWidth = 390, SDefWidth = 390; // 定義選單預設的寬度 (define default width)
        bodyWidth =  (isIE) ? document.body.clientWidth : window.innerWidth;
        if (bodyWidth < 796) bodyWidth = 796;
        if (bodyWidth > 796) {
            // 選單 (menu)
            MDefWidth += (bodyWidth - 796);
            SDefWidth += (bodyWidth - 796);
            // 左右移動按鈕 (button)
            MSclLeft += (bodyWidth - 796);
            SSclLeft += (bodyWidth - 796);
        }
        //　預設的版面 給 Mozilla 用的 (for Mozilla)
        obj = document.getElementById("SysLayout");
        if (obj != null) {
            obj.width = bodyWidth;
            if (isMZ) {    // 在 Mozilla 1.5 中呈現上會有錯誤，故需要此步驟 (Mozilla 1.5 need do it)
                obj.style.display = "none";
                obj.style.display = "";
            }
        }

        // 選單 (menu)
        obj = document.getElementById("MContainer");
        if (obj != null) obj.style.width = MDefWidth;
        obj = document.getElementById("SContainer");
        if (obj != null) obj.style.width = SDefWidth;
        // 左右移動按鈕 (button)
        obj = document.getElementById("MScroll");
        if (obj != null) obj.style.left = MSclLeft;
        obj = document.getElementById("SScroll");
        if (obj != null) obj.style.left = SSclLeft;
        // 線上人數 (online)
        obj = document.getElementById("SysOnline");
        if (obj != null) {
            if('undefined' != typeof(replace_position)) {
                obj.style.left = bodyWidth - obj.offsetWidth - replace_position[2];
            } else {
                obj.style.left = bodyWidth - obj.offsetWidth - 5;
            }
        }
        // 說明，登出，辦公室 (help, logout, office)
        obj = document.getElementById("SysHelp");
        if (obj != null) obj.style.left = bodyWidth - obj.offsetWidth - 5;
        // 系統時間 (system time)
        obj = document.getElementById("SysTime");
        if (obj != null) obj.style.left = bodyWidth - obj.offsetWidth - 5;
        // 移動按鈕 (move button)
        obj = document.getElementById("MLeft");
        if (obj != null) obj.style.visibility = "hidden";
        obj = document.getElementById("MRight");
        if (obj != null) obj.style.visibility = "hidden";
        obj = document.getElementById("SLeft");
        if (obj != null) obj.style.visibility = "hidden";
        obj = document.getElementById("SRight");
        if (obj != null) obj.style.visibility = "hidden";
        reMSclBtn();
        reSSclBtn();
        setTimeout("rePositionOther()", 2000);
    }

    function rePositionOther() {
        var bodyWidth = 796; // 取得可用的寬度 (get width)
        var MSclLeft = 600, SSclLeft = 600; // 定義選單左右移動的位置 (define vertically position)
        var OtherLeft = 0;
        var obj = null;

        bodyWidth =  (isIE) ? document.body.clientWidth : window.innerWidth;
        if (bodyWidth < 796) bodyWidth = 796;
        //　預設的版面 給 Mozilla 用的 (for Mozilla)
        obj = document.getElementById("SysLayout");
        if (obj != null) {
            obj.width = bodyWidth;
            if (isMZ) {    // 在 Mozilla 1.5 中呈現上會有錯誤，故需要此步驟 (Mozilla 1.5 need do it)
                obj.style.display = "none";
                obj.style.display = "";
            }
        }

        // 線上人數 (online)
        obj = document.getElementById("SysOnline");
        if (obj != null) {
            if('undefined' != typeof(replace_position)) {
                obj.style.left = bodyWidth - obj.offsetWidth - replace_position[2];
            } else {
                obj.style.left = bodyWidth - obj.offsetWidth - 5;
            }
        }
        // 說明，登出，辦公室 (help, logout, office)
        obj = document.getElementById("SysHelp");
        if (obj != null) obj.style.left = bodyWidth - obj.offsetWidth - 5;
        // 系統時間 (system time)
        obj = document.getElementById("SysTime");
        if (obj != null) obj.style.left = bodyWidth - obj.offsetWidth - 5;
        // 主選單加入圖示後偏移的修正 (Main Menu)
        obj = document.getElementById("MMenu");
        if (obj != null) {
            if('undefined' != typeof(replace_position)) {
                obj.offsetParent.style.top = replace_position[0] - parseInt(obj.offsetHeight);
            } else {
                obj.offsetParent.style.top = 68 - parseInt(obj.offsetHeight);
            }
        }
    }

    /**
     * 重新設定主選單的移動按鈕
     **/
    function reMSclBtn() {
        var obj = null;
        var node = null;
        var nowWidth = 425;
        obj = document.getElementById("mngp_10000");
        if ((obj != null) && (obj.childNodes.length > 0)) {
            nowWidth = obj.childNodes[0].offsetWidth;
        }

        obj = document.getElementById("MMenu");
        if (obj != null) {
            obj.style.left = 0;
        }
        obj = document.getElementById("MLeft");
        if (obj != null) obj.style.visibility = "hidden";
        obj = document.getElementById("MRight");
        if (nowWidth > MDefWidth) {
            if (obj != null) obj.style.visibility = "visible";
            Calculate(0);
        } else {
            if (obj != null) obj.style.visibility = "hidden";
        }
    }

    /**
     * 重新設定子選單的移動按鈕
     **/
    function reSSclBtn() {
        var obj = null;
        var node = null;
        var nowWidth = 425;
        if (menuMIdx == 10000) return false;
        obj = document.getElementById("mngp_" + menuMIdx);
        if ((obj != null) && (obj.childNodes.length > 0)) {
            nowWidth = obj.childNodes[0].offsetWidth;
        }

        obj = document.getElementById("SMenu");
        if (obj != null) {
            obj.style.left = 0;
        }
        obj = document.getElementById("SLeft");
        if (obj != null) obj.style.visibility = "hidden";
        obj = document.getElementById("SRight");
        if (nowWidth > SDefWidth) {
            if (obj != null) obj.style.visibility = "visible";
            Calculate(parseInt(menuMIdx) - 10000);
        } else {
            if (obj != null) obj.style.visibility = "hidden";
        }
    }

    /**
     * 計算選單左右移動的定點
     **/
    function Calculate(idx) {
        var defWidth = 0, newWidth = 0;
        var menuIndex = 0, aryIdx = 2, i = 0;
        var obj = null, nodes = null;
        var leftOffset = 0;

        menuIndex = 10000 + parseInt(idx);
        obj = document.getElementById("mngp_" + menuIndex);
        if (obj == null) return false;

        nodes = obj.getElementsByTagName("td");
        if (nodes == null) return false;

        defWidth = (idx == 0) ? MDefWidth : SDefWidth;
        newWidth = (idx == 0) ? MDefWidth : SDefWidth;

        if (typeof(aryPosition[idx]) != "undefined") {
            aryPosition[idx][0] = 1;
            return true;
        }
        if (idx == 0) leftOffset = 3;
        aryPosition[idx] = new Array();
        aryPosition[idx][0] = 1;
        aryPosition[idx][1] = 0;
        for (i = 0; i < nodes.length; i++) {
            if (parseInt(nodes[i].offsetLeft) > newWidth) {
                aryPosition[idx][aryIdx] = parseInt(nodes[i - 1].offsetLeft) - leftOffset;
                newWidth = defWidth + nodes[i - 1].offsetLeft - leftOffset;
                aryIdx++;
            }
        }
        i = nodes.length - 1;
        if ((parseInt(nodes[i].offsetLeft) + parseInt(nodes[i].offsetWidth)) >  newWidth) {
            aryPosition[idx][aryIdx] = parseInt(nodes[i].offsetLeft) - leftOffset;
        }
    }

    /**
     * 左右移動選單
     **/
    function ScrollMenu(idx) {
        var obj = null, node = null;
        var menuIndex = parseInt(menuMIdx) - 10000;
        var aryIndex = 1;

        if (Timer != null) return false;
        if (idx < 3) {
            menuIndex = 0;
            obj = document.getElementById("MMenu");
        } else {
            obj = document.getElementById("SMenu");
        }
        if (obj == null) return false;
        if (typeof(aryPosition[menuIndex]) == "undefined") {
            return false;
        }

        aryIndex = aryPosition[menuIndex][0];
        switch (parseInt(idx)) {
            case 1 :
            case 3 :
                aryIndex--;
                if (aryIndex <= 1) {
                    aryIndex = 1;
                    node = (idx == 1) ? document.getElementById("MLeft") : document.getElementById("SLeft");
                    if (node != null) node.style.visibility = "hidden";
                }
                aryPosition[menuIndex][0] = aryIndex;

                node = (idx == 1) ? document.getElementById("MRight") : document.getElementById("SRight");
                if (node != null) node.style.visibility = "visible";
                break;
            case 2 :
            case 4 :
                aryIndex++;
                if (aryIndex >= (aryPosition[menuIndex].length - 1)) {
                    aryIndex = aryPosition[menuIndex].length - 1;
                    node = (idx == 2) ? document.getElementById("MRight") : document.getElementById("SRight");
                    if (node != null) node.style.visibility = "hidden";
                }
                aryPosition[menuIndex][0] = aryIndex;

                node = (idx == 2) ? document.getElementById("MLeft") : document.getElementById("SLeft");
                if (node != null) node.style.visibility = "visible";
                break;
        }
        // 沒有動畫的移動選單 (no anime)
        //obj.style.left = 0 - parseInt(aryPosition[menuIndex][aryIndex]);
        // 使用動畫的移動選單 (use anime)
        Timer = setInterval ('AnimeMenu("' + obj.id + '", ' + (0 - parseInt(aryPosition[menuIndex][aryIndex])) + ')', 10);
    }

    // 移動選單會有動畫 (anime menu)
    function AnimeMenu(objName, xTo) {
        var xFrom, xOffset;
        myObj = document.getElementById(objName);
        xFrom = parseInt(myObj.style.left);

        if (Math.abs(xFrom - xTo) <= 0) {
            if (Timer != null) {
                clearInterval(Timer);
                Timer = null;
            }
            return;
        }

        if (xFrom != xTo) {
            xOffset = Math.ceil( Math.abs( xTo - xFrom ) / 20 );
            if (xTo < xFrom) xOffset = -xOffset;
            myObj.style.left = parseInt (myObj.style.left) + xOffset;
        }
    }
///////////////////////////////////////////////////////////////////////////////
    function monitorAct(val) {
        var txt = "";
        var res = false;

        return false;
        txt  = "<manifest>";
        txt += "<ticket>" + ticket + "</ticket>";
        txt += "<id>" + nowMenuId + "</id>";
        txt += "</manifest>";
        res = xmlVars.loadXML(txt);
        if (!res) {
            alert(MSG_SysError);
            return false;
        }

        xmlHttp.open("POST", "sysbar_monitor.php", false);
        xmlHttp.send(xmlVars);
    }
///////////////////////////////////////////////////////////////////////////////
    var xmlHttp = null, xmlDocs = null, xmlVars = null;
    var menuMIdx = 10000;
    var menuSIdx = 10000;
    var ticket = "";
    var nowMenuId = "";

    /**
     * 建立一個選項
     **/
    function buildItem(indent, node) {
        var mnTitle = "", mnTarget = "", mnHref = "", mnKind = "", mnID = "";
        var nodes = null, newNode = null, attr = null;
        var val = 0;
        var re = new RegExp("^javascript:", "ig");

        if ((node == null) || !node.hasChildNodes()) return null;
        // 是否隱藏 (hidden ?)
        attr = node.getAttribute("hidden");
        if ((attr != null) && (attr == "true")) return null;

        if (!node.hasChildNodes()) return null;
        // 取出選單編號 (get menu id)
        attr    = node.getAttribute("id");
        mnID    = (attr == null) ? "" : attr;
        // 取出選單的標題 (get menu title)
        mnTitle = getCaption(node, 'title');
        if (mnTitle == "") mnTitle = MSG_NoTitle;
        // 取出 href 節點 (get href node)
        nodes = node.childNodes;
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].nodeType == 1) && (nodes[i].tagName == "href")) {
                mnHref   = (nodes[i].hasChildNodes()) ? nodes[i].firstChild.data : 'about:blank';
                mnKind   = nodes[i].getAttribute("kind");
                mnTarget = nodes[i].getAttribute("target");
                if (mnTarget == "default") {
                    if (typeof(fmDefault) == "undefined") {
                        mnTarget = getTargetName();
                    } else {
                        mnTarget = fmDefault;
                    }
                }
                break;
            }
        }

        newNode = document.createElement("a");
        newNode.title = strip_tags(mnTitle);

        if (indent != 0) {
            // 判斷 node 的種類 (check node type)
            switch (parseInt(mnKind)) {
                case 1 :   // 功能 (function)
                case 8 :   // 外部連結 (out site link)
                    newNode.href = mnHref;
                    if (!re.test(mnHref)) {
                        newNode.target = mnTarget;
                    }
                    newNode.onclick = smMouseEvent;
                    break;
                case 2 :   // 教材 (course content)
                    newNode.href = "/" + sysbar_sid + "_" + sysbar_csid + mnHref;
                    if (!re.test(mnHref)) {
                        newNode.target = mnTarget;
                    }
                    newNode.onclick = function (evnt) {
                        smMouseEvent(evnt);
                        // return false;
                    };
                    break;
                case 3 :   // 作業 (homework)
                    newNode.href = "javascript:;";
                    newNode.onclick = function (evnt) {
                        smMouseEvent(evnt);
                        goQTI("hw", mnHref, mnTarget);
                        return false;
                    };
                    break;
                case 4 :   // 考試 (exam)
                    newNode.href = "javascript:;";
                    newNode.onclick = function (evnt) {
                        smMouseEvent(evnt);
                        goQTI("ex", mnHref, mnTarget);
                        return false;
                    };
                    break;
                case 5 :   // 問卷 (questionnaire)
                    newNode.href = "javascript:;";
                    newNode.onclick = function (evnt) {
                        smMouseEvent(evnt);
                        goQTI("qs", mnHref, mnTarget);
                        return false;
                    };
                    break;
                case 6 :   // 議題討論 (subject forum)
                case 9 :   // [群組] 議題討論 ([group] subject forum)
                    newNode.href = "javascript:;";
                    newNode.onclick = function (evnt) {
                        smMouseEvent(evnt);
                        // alert(mnTarget);
                        goBoard(mnHref, mnTarget);
                        return false;
                    };
                    break;
                case 7 :   // 線上討論 (online chat)
                case 10:   // [群組] 線上討論 ([group] online chat)
                    newNode.href = "javascript:;";
                    newNode.onclick = function (evnt) {
                        smMouseEvent(evnt);
                        goChatroom(mnHref);
                        return false;
                    };
                    break;
                default:
            }
            menuSIdx++;
            newNode.onmouseover = smMouseEvent;
            newNode.onmouseout = smMouseEvent;
            newNode.className = "SMenuItemOut";
            newNode.valign = "top";
            newNode.setAttribute("id", "smnItem_" + menuSIdx);
            mnTitle += '<button id="smnBtn_' + menuSIdx + '" style="display:none"></button>';
            // mnTitle  = '<span class="SMenuItemSplit">&nbsp;</span>' + mnTitle;
        } else {
            // 顯示主選單 (show main menu)
            val = menuMIdx + 1;
            newNode.href    = "javascript:;";
            newNode.onclick = function (evnt) {
                attr = this.getAttribute("sysid");
                nowMenuId = (attr == null) ? "" : attr;

                if (typeof(parent.frames[fmDefault])              != 'undefined' &&
                    typeof(parent.frames[fmDefault].sysGotoLabel) != 'undefined')
                    parent.frames[fmDefault].sysGotoLabel = ''; // 清除以避免無法切換大項 #1738

                showSubMenu(val);
                return false;
            };
            newNode.onmouseover = mmMouseEvent;
            newNode.onmouseout = mmMouseEvent;
            newNode.className = "MMenuItemFont";
            mnTitle += '<button id="mmnBtn_' + menuMIdx + '" style="display:none"></button>';
        }
        newNode.setAttribute("sysid", mnID);
        newNode.innerHTML = mnTitle;

        return newNode;
    }

    /**
     * 建立選單
     **/
    function buildMenu(indent, node) {
        var nodes = null, newNode = null, attr = null;
        var Menu = null;
        var obj = null;
        var idx = 0;

        if (indent > 1) return null;
        if ((node == null) || (!node.hasChildNodes())){
            return false;
        }

        nodes = node.childNodes;

        Menu = document.createElement("table");
        Menu.setAttribute("id", "mngp_" + menuMIdx);
        Menu.border = "0";
        Menu.cellSpacing = (indent == 0) ? "2" : "0";
        Menu.cellPadding = (indent == 0) ? "2" : "0";
        Menu.style.display = (indent == 0) ? "block" : "none";
        Menu.insertRow(0);
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].nodeType != 1) || (nodes[i].tagName != "item")) continue;
            newNode = buildItem(indent, nodes[i]);
            if (newNode == null) continue;

            if (indent == 0) menuMIdx++;
            buildMenu(indent + 1, nodes[i]);

            idx = Menu.rows[0].cells.length;
            Menu.rows[0].insertCell(idx);
            Menu.rows[0].cells[idx].noWrap = true;
            if (indent == 0) Menu.rows[0].cells[idx].setAttribute("id", "mnItem_" + menuMIdx);

            Menu.rows[0].cells[idx].className = (indent == 0) ? "MMenuItemOut" : "SMenuItemOut";
            if (indent == 0) {
                Menu.rows[0].cells[idx].className = "MMenuItemOut";
            } else {
                Menu.rows[0].cells[idx].className = "SMenuItemOut";
                Menu.rows[0].cells[idx].innerHTML = '<pre class="SMenuItemSplit">&nbsp;</pre>';
            }
            Menu.rows[0].cells[idx].appendChild(newNode);
        }
        if (indent == 0) {
            obj = document.getElementById("MMenu");
        } else {
            obj = document.getElementById("SMenu");
        }
        if (obj != null) {
            obj.appendChild(Menu);
            if (indent == 0) {
                // 主選單加入圖示後偏移的修正 (Main Menu)
                if('undefined' != typeof(replace_position)) {
                    obj.offsetParent.style.top = replace_position[0] - parseInt(obj.offsetHeight);
                } else {
                    obj.offsetParent.style.top = 68 - parseInt(obj.offsetHeight);
                }
                nodes = obj.offsetParent.getElementsByTagName("img");
                if ((nodes != null) && (nodes.length > 0)) {
                    for (var i = 0; i < nodes.length; i++) {
                        attr = nodes[i].getAttribute("align");
                        if ((attr == null) || (attr == "")) {
                            nodes[i].align = "absmiddle";
                            nodes[i].setAttribute("align", "absmiddle");
                        }
                    }
                }
            }
        }
    }

    function getSysGotoLabel() {
        /*
         * 自動切換為哪個功能項 add by Wiseguy 2004/11/09
         */
        var ii = new Array(1, 0);
        eval('var brother = parent.' + self.name.replace(/_sysbar$/, '_main') + ';');
        try
        {
            var xx = brother.location.href;
        }
        catch(e)
        {
            var xx = '';
        }
        var x = '';
        var bb = self.location.href.split('/');
        if (xx.search(/^http:\/\//) === 0) {
            var aa = xx.split('/');
            if ((aa[2] == bb[2]) && typeof(brother.sysGotoLabel) != 'undefined') {
                x = brother.sysGotoLabel;
            }
        }
        if ((x == "") && (typeof sysGotoLabel == "string") && (sysGotoLabel != "")) {
            x = sysGotoLabel;
        }
        return x;
    }

    /**
     * 解析 sysbar (parse sysbar)
     **/
    function parseSysbar(func) {
        var obj = null, node = null, nodes = null;

        if (xmlDocs == null) {
            alert(MSG_SysError);
            return false;
        }

        xmlDocs.setProperty("SelectionLanguage", "XPath");
        if (window.opera) {
            nodes = xmlDocs.getElementsByTagName("items");
            node = ((nodes != null) && (nodes.length > 0)) ? nodes[0] : null;
        } else {
            node = xmlDocs.selectSingleNode("//items");
        }
        // 建立選單 (build menu)
        if (node != null) {
            obj = document.getElementById("MMenu");
            if (obj != null) obj.innerHTML = "";
            obj = document.getElementById("SMenu");
            if (obj != null) obj.innerHTML = "";
            menuMIdx = 10000;
            menuSIdx = 10000;
            buildMenu(0, node);
            menuMIdx = 10000;
            menuSIdx = 10000;

            // over
            // showSubMenu(10001, func);
            var x = getSysGotoLabel();
            if ((typeof x == "undefined") || (x == ""))
            {
                if (typeof func != "undefined") sysGotoLabel = func;
            }
            else
            {
                func = x;
            }
            chgMenuItem(func, false);
            rePosition();
        }
    }

    var sysbar_env  = "";
    var sysbar_sid  = 0;
    var sysbar_csid = 0;
    var sysbar_caid = 0;
    function parseParam() {
        var obj = null, node = null, attr = null;
        var res = "";
        // 取得一些資料
        sysbar_env = getNodeValue(xmlDocs.documentElement, "env");

        res = getNodeValue(xmlDocs.documentElement, "school_id");
        sysbar_sid = parseInt(res);

        res = getNodeValue(xmlDocs.documentElement, "course_id");
        sysbar_csid = parseInt(res);

        res = getNodeValue(xmlDocs.documentElement, "class_id");
        sysbar_caid = parseInt(res);

        // 身份確認
        node = xmlDocs.selectSingleNode("//teach");
        obj = document.getElementById("admOffice");
        if (obj != null) {
            if (node == null) {
                obj.style.display = "none";
            } else {
                attr = node.getAttribute("have");
                obj.style.display = (attr == "true") ? "" : "none";
            }
            node = document.getElementById("admOffice1");
            if (node) node.style.display = obj.style.display;
        }
    }

    /**
     * 載入 sysbar 的 XML 設定 (Load sysbar setting)
     **/
    function loadSysbar(uri, extra, func) {
        var txt = "";
        var res = false;
        var err = null, obj = null;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
        if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
        if (uri == "") return false;
        txt = "<manifest><ticket>" + ticket + "</ticket>" + extra + "</manifest>";
        res = xmlVars.loadXML(txt);
        if (!res) {
            alert(MSG_SysError);
            return false;
        }

        xmlHttp.open("POST", uri, false);
        xmlHttp.send(xmlVars);

        if (xmlHttp.responseText == "") {
            rePosition();
            return true;
        }

        txt = "";
        switch (xmlHttp.responseText) {
            case 'needVar'       : txt = MSG_NEED_VARS;     break;
            case 'DataError'     : txt = MSG_DATA_ERROR;    break;
            case 'IPLimit'       : txt = MSG_IP_DENY;       break;
            case 'AdminRole'     : txt = MSG_ADMIN_ROLE;    break;
            case 'DirectorRole'  : txt = MSG_DIRECTOR_ROLE; break;
            case 'TeacherRole'   : txt = MSG_TEACHER_ROLE;  break;
            case 'StudentRole'   : txt = MSG_STUEDNT_ROLE;  break;
            case 'SchoolIDError' : txt = MSG_SLID_ERROR;    break;
            case 'DirectIDError' : txt = MSG_CAID_ERROR;    break;
            case 'CourseIDError' : txt = MSG_CSID_ERROR;    break;
            case 'CourseDelete'  : txt = MSG_CS_DELTET;     break;
            case 'CourseClose'   : txt = MSG_CS_NOT_OPEN;   break;
        }

        if (txt != "") {
            obj = document.getElementById("MMenu");

            if (obj != null) obj.innerHTML = '<span class="sysErrorFont">' + txt + '</span>';
            obj = document.getElementById("SMenu");
            if (obj != null) obj.innerHTML = "";
            obj = getTarget();
            if (obj != null) obj.location.replace("about:blank");
            if (typeof(parent.FrameRows) == "string") {
                switch (this.name) {
                    case "s_sysbar": if (parent.FrameRows == "*,0,0,0,0") alert(txt); break;
                    case "c_sysbar": if (parent.FrameRows == "0,*,0,0,0") alert(txt); break;
                    default:
                        alert(txt);
                }
            }
            rePosition();
            return false;
        }

        res = xmlDocs.loadXML(xmlHttp.responseText);
        if (!res) {
            xmlDocs = XmlDocument.create();
            xmlDocs.loadXML('<manifest />');
            rePosition();
            return false;
        }
        // 尋找 xmlHttp.responseText  有無 <error_msg>StudentRole</error_msg>
        var error_msg = '';
        error_msg = getNodeValue(xmlDocs.documentElement, "error_msg");
        if (error_msg != "") alert(MSG_STUEDNT_ROLE);

        ticket = getNodeValue(xmlDocs.documentElement, "ticket");
        parseParam();
        parseSysbar(func);
        // rePosition();
        return true;
    }

///////////////////////////////////////////////////////////////////////////////
    function chgStyle() {
        return;
        var sty = document.getElementsByTagName("link");
        if (sty) {
            for (var i = sty.length - 1; i > 0; i--) {
                sty[i].parentNode.removeChild(sty[i]);
            }
            var nSty = document.createElement("link");
            nSty.setAttribute("rel", "stylesheet");
            nSty.setAttribute("type", "text/css");
            nSty.setAttribute("href", "/lib/sysbar_style.php");
            document.getElementsByTagName("head")[0].appendChild(nSty);
        }
    }
///////////////////////////////////////////////////////////////////////////////
    function chgMenuItem(sysid, blnClickSM) {

        var node = null, attr = null, nodes = null, obj = null, items = null;
        var pid = "";
        node = xmlDocs.selectSingleNode('//item[@id="' + sysid + '"]');
        if (node) {
            if (node.parentNode.nodeName == "items") {
                obj = document.getElementById("MMenu");
                nodes = obj.getElementsByTagName("a");
                for (var i = 0; i < nodes.length; i++) {
                    attr = nodes[i].getAttribute("sysid");
                    if (attr == null) continue;
                    if (attr == sysid) {
                        if (isIE) {
                            nodes[i].click();
                        } else {
                            items = nodes[i].getElementsByTagName("button");
                            if (items.length > 0) items[0].click();
                        }
                        break;
                    }
                }
            } else if (node.parentNode.nodeName == "item") {
                pid = node.parentNode.getAttribute("id");
                obj = document.getElementById("MMenu");
                nodes = obj.getElementsByTagName("a");
                for (var i = 0; i < nodes.length; i++) {
                    attr = nodes[i].getAttribute("sysid");
                    if (attr == null) continue;
                    if (attr == pid)
                    {
                        var targetIdx = nodes[i].parentNode.getAttribute("id").substring(7, 12);
                        if (targetIdx != menuMIdx)
                        {
                            if (menuMIdx != 10000)
                            {
                                var obj = document.getElementById("mngp_" + menuMIdx);
                                if (obj != null) obj.style.display = "none";
                                obj = document.getElementById("mnItem_" + menuMIdx);
                                if (obj != null) obj.className = "MMenuItemOut";
                            }

                            menuMIdx = targetIdx;
                            var obj = document.getElementById("mngp_" + menuMIdx);
                            if (obj != null) obj.style.display = "block";
                            obj = document.getElementById("mnItem_" + menuMIdx);
                            if (obj != null) obj.className = "MMenuItemOver";
                        }
                    }
                }

                obj = document.getElementById("SMenu");
                nodes = obj.getElementsByTagName("a");
                for (var i = 0; i < nodes.length; i++) {
                    attr = nodes[i].getAttribute("sysid");
                    if (attr == null) continue;
                    if (attr == sysid) {
                        if (isIE) {
                            nodes[i].click();
                        } else {
                            smMouseEvent({
                                "currentTarget" : nodes[i],
                                "type"          : "click"
                            });
                            var re = new RegExp("^javascript:", "ig");
                            if (re.test(nodes[i].href)) {
                                var str = nodes[i].href.replace(re, "");
                                eval(str);
                            } else {
                                if (nodes[i].target == "_blank") {
                                    window.open(nodes[i].href, nodes[i].target);
                                } else {
                                    eval("parent." + nodes[i].target + ".location.href = '" + nodes[i].href + "';");
                                }
                            }
                        }
                        break;
                    }
                }
                sysGotoLabel = '';
            }
        } else {
            node = xmlDocs.selectSingleNode('//items/item/item');
            if (node) chgMenuItem(node.getAttribute("id"), false);
            // alert("Menu item do not exist!");
        }
    }
///////////////////////////////////////////////////////////////////////////////
    function goCourse(csid, env, func) {
        var txt = "";
        var res = false;
        var selcourse, isDeny = true;

        if (csid == "") return false;
        if ((selcourse = document.getElementById('selcourse')) != null)
        {
            var options = selcourse.getElementsByTagName('option');
            for(var i=0; i<options.length; i++)
            {
                if (options[i].value == csid) {isDeny = false; break;}
            }
            if (isDeny) return false;
        }

        txt  = "<course_id>" + csid + "</course_id>";
        txt += "<env>" + env + "</env>";
        res = loadSysbar(baseUri + "goto_course.php", txt, func);
        if (res) {
            obj = document.getElementById("selcourse");
            if (obj != null) syncValue(obj, csid);
        }

        logoutChatroom();
        chgStyle();
        env2short();
        return res;
    }

    /**
     * 辦公室/教室 切換環境
     */
    function goEnv(csid, env, func) {
        // 先判斷目前功能是否有未存檔的操作
        var tmp = eval('parent.'+getTargetName());
        if (typeof(tmp.notSave) == 'boolean' && tmp.notSave)
            if (!confirm(tmp.MSG_EXIT)) return;
        else
            tmp.notSave = false;

        switch(env) {
            case 2 : env = 'teach'      ; break;
            case 3 : env = 'direct'     ; break;
            case 4 : env = 'academic'   ; break;
            default: env = 'learn';
        }

        if (csid == "") return false;

        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if (typeof(func) == 'undefined') func = '';
        txt = "<manifest><course_id>" + csid + "</course_id>" + "<env>" + env + "</env>"+ "<func>" + func + "</func>" + "</manifest>";
        res = xmlVars.loadXML(txt);
        if (!res) {
            alert(MSG_SysError);
            return false;
        }
        xmlHttp.open("POST", baseUri + "goto_env.php", false);
        xmlHttp.send(xmlVars);

        if (xmlHttp.responseText == 'true') {
            uri = '/' + env + '/index.php';
            parent.window.location.replace(uri);
        }
    }

    /**
     * 切換班級
     **/
    function goClass(caid) {
        var obj = null;
        var sty = "";
        var cnt = 0;
        var txt = "";

        if (caid == "") return false;
        txt = "<class_id>" + caid + "</class_id>";
        loadSysbar(baseUri + "goto_class.php", txt);
        // 同步課程下拉清單
        obj = document.getElementById("selcourse");
        if (obj != null) syncValue(obj, caid);
        env2short();
    }

    /**
     * 進討論板
     * @param string val : 討論板編號
     **/
    var boardWin = null;
    function goBoard(val, target) {
        var txt = "";
        var res = false;

        if (val == "") return false;
        if ((typeof(target) == "undefined") || (target == "")) target = fmDefault;
        txt = "<manifest><board_id>" + val + "</board_id></manifest>";
        res = xmlVars.loadXML(txt);
        if (!res) {
            alert(MSG_SysError);
            return;
        }

        xmlHttp.open("POST", baseUri + "goto_board.php", false);
        xmlHttp.send(xmlVars);
        txt = xmlHttp.responseText;
        if (txt != "") {
            switch (txt) {
                case "Bad_ID"   : alert(MSG_BAD_BOARD_ID);    break;
                case "Bad_Range": alert(MSG_BAD_BOARD_RANGE); break;
                case "board_notopen": alert(MSG_BOARD_NOTOPEN); break;
                case "board_close": alert(MSG_BOARD_CLOSE); break;
                case "board_disable": alert(MSG_BOARD_DISABLE); break;
                case "board_taonly": alert(MSG_BOARD_TAONLY); break;
                default:
            }
            return;
        }
        if (target == "_blank") {
            boardWin = window.open("/forum/index.php", "_blank", "width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1");
        } else {
            eval("parent." + target + ".location.replace('/forum/index.php')");
        }
    }

    /**
     * 進討論室
     * @param string val : 討論室編號
     * @return
     **/
    var chatWin = null;
    function goChatroom(val) {
        if (window.console) {console.log('sysbar.js goChatroom()');}
        var res = "";
        var node = null;

        if ((typeof chatWin == "object") && (chatWin != null) && !chatWin.closed) {
            alert(MSG_IN_CHAT_ROOM);
            chatWin.focus();
        } else {
            xmlVars.loadXML("<manifest><chat_id>" + val + "</chat_id></manifest>");
            xmlHttp.open("POST", baseUri + "goto_chat.php", false);
            xmlHttp.send(xmlVars);
            if (xmlVars.loadXML(xmlHttp.responseText)) {
                node = xmlVars.selectSingleNode("//msg");
                res = (node.hasChildNodes()) ? node.firstChild.nodeValue : "";
                if (res != "") {
                    alert(res);
                    return false;
                }
                node = xmlVars.selectSingleNode("//uri");
                res = (node.hasChildNodes()) ? node.firstChild.nodeValue : "about:blank";
                chatWin = window.open(res, "_blank", "width=800,height=500,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1");
            }
        }
    }

    /**
     * 切換課程/環境/班級時, 強制登出討論室
     */
    function logoutChatroom()
    {
        if ((typeof chatWin == "object") && (chatWin != null) && !chatWin.closed)
        {
            chatWin.focus();    // 先focus, 以讓跳出的訊息可以在上層
            chatWin.close();
        }
    }

    var qtiWin = null;
    function goQTI(tp, val, target) {
        var txt = "";

        txt = "/learn/goto_qti.php?tp=" + tp + "&v=" + val;
        if (target == "_blank") {
            qtidWin = window.open(txt, "_blank", "width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1");
        } else {
            eval("parent." + target + ".location.replace('" + txt + "')");
        }
    }

    var loginDailog = null;
    function login() {
        if ((loginDailog != null) && !loginDailog.closed) {
            loginDailog.focus();
        } else {
            var rnd = Math.ceil(Math.random() * 100000);
            loginDailog = window.open("login.php", "win" + rnd, "top=250,left=350,width=300,height=150,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1,scrollbar=1");
            // loginDailog = showDialog("/learn/login.php", false , "", true, "200px", "200px", "300px", "150px", "status=0, resizable=1, scrollbars=1");
        }
    }
///////////////////////////////////////////////////////////////////////////////
    /**
     * 初始化整個 sysbar (init sysbar)
     **/
    function initSysbar(val) {
        var res = false;
        var dsid;
        chkBrowser();
        var t = window.location.pathname.split("/");
        t[t.length - 1] = "";
        baseUri = t.join("/");

        // 檢查瀏覽器 (check browser)
        if (!isIE && !isMZ) {
            alert(MSG_NotSupportBrowser);
            // return false;
        }
        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
        if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
        res = loadSysbar(val, "");
        if (res) {
            dsid = getNodeValue(xmlDocs.documentElement, "dsid");
            obj = document.getElementById("selcourse");
            if (obj != null) syncValue(obj, dsid);
        }
        env2short();
    }

    window.onunload = function () {
        if ((chatWin != null) && !chatWin.closed) chatWin.close();
        if ((boardWin != null) && !boardWin.closed) boardWin.close();
    };

    window.onresize = function (e) {
        if (isMobile && isResized) {
            return;
        }
        isResized = true;
        rePosition();
    };

    function updateCourseList()
    {
        if (this.name != 's_sysbar') return;
        obj = document.getElementById("selcourse");
        if (obj != null)
        {
            xmlHttp.open("POST", 'getCourseList.php', false);
            xmlHttp.send();

            if (xmlHttp.responseText != '')
                obj.outerHTML = xmlHttp.responseText;
        }
    }

    setTimeout(function () {
        var b = document.body;
        if (typeof b.addEventListener !== "undefined") {
            b.addEventListener('touchstart', function () {
                window.focus();
            });
        }
    }, 1000);
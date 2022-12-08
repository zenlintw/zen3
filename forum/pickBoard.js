     /**
      * 課程群組顯示( 改自 /learn/mycourse/course_tree.js )
      *
      * @since   2004/09/10
      * @author  KuoYang Tsao
      * @version $Id: pickBoard.js,v 1.1 2010/02/24 02:39:00 saly Exp $
      * @copyright 2004 SUNNET
      **/

    var bodyHeight = 0, bodyWidth = 0;
    var xmlVars = null, xmlHttp = null, xmlDocs = null;

///////////////////////////////////////////////////////////////////////////////
    /**
     * 展開或收攏群組
     **/
    function gpExpand(val, indent) {
        var obj = document.getElementById("Group" + val);
        var icon = document.getElementById("Icon" + val);
        var attr = null;
        var txt = "";

        if (obj == null) return false;
        if (obj.style.display == "block") {
            obj.style.display = "none";
            if (icon != null) {
                icon.src = "/theme/" + theme + "/academic/plus.gif";
                icon.alt = MSG_EXPAND;
                icon.title = MSG_EXPAND;
            }
        } else {
            if (icon != null) {
                icon.src = "/theme/" + theme + "/academic/minus.gif";
                icon.alt = MSG_COLLECT;
                icon.title = MSG_COLLECT;
            }
            obj.style.display = "block";
            attr = obj.getAttribute("MyAttr");
            if ((attr != null) && (attr == "false")) {
                obj.innerHTML = MSG_LOADING;
                obj.setAttribute("MyAttr", "true");
                txt = buildGP(val, parseInt(indent));
                obj.innerHTML = txt;
                disableBubble();
            }
        }
        if (isIE) event.cancelBubble = true;
        return false;
    }

    /**
     * cancel event bubble
     * @param Event event
     * @return
     **/
    function eCancelBubble(evnt) {
        if (isMZ) {
            evnt.cancelBubble = true;
        } else if (isIE) {
            event.cancelBubble = true;
        }
    }

    /**
     * run action
     * @param object obj : object
     * @param integer val : what event
     * @return
     **/
    var preIdx = 'root';
    function mouseEvent(obj, val) {
        var idx = 0;
        var node = null;
        if ((typeof(obj) != "object") || (obj == null)) return false;
        idx = obj.getAttribute("MyAttr");
        switch (parseInt(val)) {
            case 1 : obj.className = "cssTbFocus"; break;   // Mouse Over
            case 2 : obj.className = "cssTbBlur"; break;   // Mouse Out
            case 3 :    // Mouse Click
            case 4 :    // Mouse Click
                node = document.getElementById("Div" + preIdx);
                if (node != null) {
                    node.className = "cssTbBlur";
                }
                obj.className = "cssTbFocus";
                preIdx = idx;
                if (parseInt(val) == 3) {
                    TitleName = obj.getAttribute("MyCaption");
                    if(cur_act=='group') { // 已在群組中
                        w = dialogArguments;
                        if(w)    // 顯示課程名稱
                            w.showCourseCaption(TitleName);

                        do_func('list_board', idx);
                    }    else
                        do_func("group", idx);
                }
                break;
        }
    }
///////////////////////////////////////////////////////////////////////////////
    function searchPoint() {
        var nodes = null;
        nodes = document.getElementsByTagName("input");
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].type == "radio") && nodes[i].checked) {
                return nodes[i].value;
            }
        }
        return "";
    }

    function getGroupNode(gid) {
        var nodes = null, attr = null;
        if (gid == "root" || gid==preIdx) return xmlDocs.documentElement;
        nodes = xmlDocs.getElementsByTagName("courses");
        if ((nodes == null) || (nodes.length <= 0)) return null;
        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("id");
            if ((attr != null) && (attr == gid)) return nodes[i];
        }
        return null;
    }

    /**
     * 建立子課程群組
     * @param string  gid   : 課程群組的編號
     * @param integer indent: 層級
     * @return string result: 呈現課程目錄的結果
     **/
    function buildGP(gid, indent) {
        var nodes = null, childs = null, node = null;
        var cnt = 0, idx = 0;
        var txt = "", result = "";

        node = getGroupNode(gid);
        if ((typeof(node) != "object") || (node == null))            return "";
        if (!node.hasChildNodes()) return "";
        nodes = node.childNodes;
        cnt = nodes.length;

        for (var i = 0; i < cnt; i++) {
            if (nodes[i].nodeType != 1) continue;
            if (nodes[i].nodeName == "courses") {
                idx = nodes[i].getAttribute("id");
                caption = htmlspecialchars(getCaption(nodes[i]));

                txt += '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
                txt += '<tr><td nowrap>';
                txt += '<div class="cssTbBlur" MyAttr="' + idx + '" MyCaption="' + caption + '" id="Div' + idx + '" onmouseover="mouseEvent(this, 1)" onmouseout="mouseEvent(this, 2)" onclick="mouseEvent(this, 3)">';
                // ident
                for (var j = (indent + 1); j > 0 ; j--)
                    txt += '<span style="width=15px">&nbsp;&nbsp;</span>';
                // icon
                childs = nodes[i].getElementsByTagName("courses");
                if ((childs != null) && (childs.length > 0))
                    txt += '<img src="/theme/' + theme + '/academic/plus.gif" width="9" height="15" border="0" align="absmiddle" alt="' + MSG_EXPAND + '" title="' + MSG_EXPAND + '" id="Icon' + idx + '" onclick="gpExpand(\'' + idx + '\', ' + (indent + 1) + ')">';
                else
                    txt += '<img src="/theme/' + theme + '/academic/dot.gif" width="9" height="15" border="0" align="absmiddle">';

                txt += caption;
                txt += '</div>';
                txt += '<div id="Group' + idx + '" MyAttr="false" style="display:none">' + result + '</div>';
                txt += '</td></tr>';
                txt += '</table>';
            }
        }
        return txt;
    }

    function disableBubble() {
        // 取消事件沸升 (Begin)
        if (isMZ) {
            nodes = document.getElementsByTagName("img");
            if (nodes == null) return false;
            cnt = nodes.length;
            for (var i = 0; i < cnt; i++) {
                if (nodes[i].getAttribute("id") != null) {
                    nodes[i].addEventListener("click", eCancelBubble, false);
                }
            }   // End for (var i = 0; i < cnt; i++)
        }   // End if (isMZ)

        nodes = document.getElementsByTagName("input");
        if (nodes == null) return false;
        cnt = nodes.length;
        for (var i = 0; i < cnt; i++) {
            if (nodes[i].type == "radio") {
                nodes[i].onclick = eCancelBubble;
            }
        }   // End for (var i = 0; i < cnt; i++)
        // 取消事件沸升 (End)
    }

    /**
     * 顯示課程群組
     * @return
     **/
    function showListFolder() {
        var obj = document.getElementById("CGroup");
        var nodes = null;
        var txt = "";

        replaceTitle(getTitle('do_func("group",10000000)',SchoolName));

        if ((typeof(obj) != "object") || (obj == null)) return false;
        txt = buildGP("root", 0);
        obj.innerHTML = txt;
        disableBubble();
        if(txt=='')
            do_func("group",10000000);
    }

    /**
     * 顯示群組中課程
     * @return
     **/
    function showGroupCourse() {
        var obj = document.getElementById("CGroup");
        var nodes = null;
        var txt = "";

        replaceTitle(getTitle('void(null)',TitleName));

        if ((typeof(obj) != "object") || (obj == null)) return false;

        txt = buildGP(preIdx, 0);
        obj.innerHTML = txt;
        disableBubble();
    }

    /**
     * 建立子課程群組
     * @param string  gid   : 課程群組的編號
     * @param integer indent: 層級
     * @return string result: 呈現課程目錄的結果
     **/
    function getBoardList() {
        var nodes = null, childs = null, node = null;
        var cnt = 0, idx = 0;
        var txt = "", result = "";

        node = xmlVars.documentElement;
        if ((typeof(node) != "object") || (node == null))    return "";
        if (!node.hasChildNodes()) return "";
        nodes = node.childNodes;
        cnt = nodes.length;
        for (var i = 0; i < cnt; i++) {
            if (nodes[i].nodeType != 1) continue;
            if (nodes[i].nodeName == "boards") {
                idx = nodes[i].getAttribute("id");
                caption = getCaption(nodes[i]);
                txt += idx + '\t' + caption + '\t\t';
            }
        }
        return txt;
    }
    /**
     * show all course
     **/
    function showRoot() {
        var obj = null;
        obj = document.getElementById("Div" + preIdx);
        if (obj != null) {
            obj.className = "cssTbBlur";
        }
        preIdx = 10000000;
        do_func('group', 10000000);
    }
///////////////////////////////////////////////////////////////////////////////
    /**
     * 重新定位物件的位置
     **/
    function resetWin() {
        var obj = document.getElementById("ToolBar");
        if (obj == null) return false;
        bodyHeight = (isIE) ? document.body.clientHeight : window.innerHeight;
        bodyHeight = Math.max(parseInt(bodyHeight) - 30, 0);
        bodyWidth  = (isIE) ? document.body.clientWidth : window.innerWidth;
        bodyWidth  = Math.max(parseInt(bodyWidth) - 10, 0);

        obj.style.height = parseInt(bodyHeight);
        if (parseInt(bodyWidth) <= 30) {
            bodyWidth = 20;
        }
        obj.style.width = bodyWidth;
        obj.firstChild.style.width = bodyWidth;
    }

    window.onresize = resetWin;

///////////////////////////////////////////////////////////////////////////////
    /**
     * 控制台
     * @param
     * @return
     **/
    function do_func(act, extra) {
        var obj = null, nodes = null, node = null;
        var txt = "";
        var res = 0;
        var csObj = null;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
        if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();

        cur_act = act;
        switch (act) {
            case "list_group" :

                if (parseInt(extra) >= 5) {
                    window.onresize = function () {};
                    return true;
                } else {
                    window.onresize = resetWin;
                }
                txt  = "<manifest>";
                txt += "<ticket>" + ticket + "</ticket>";
                txt += "<action>" + act + "</action>";
                txt += "</manifest>";

                res = xmlVars.loadXML(txt);
                if (!res) {
                    alert(MSG_SYS_ERROR);
                    return false;
                }
                xmlHttp.open("POST", "do_pickBoard.php", false);
                xmlHttp.send(xmlVars);
                res = xmlDocs.loadXML(xmlHttp.responseText);
                if (!res) {
                    alert(MSG_SYS_ERROR);
                    return false;
                }
                // ticket = getNodeValue(xmlDocs.documentElement, "ticket");
                preIdx = 0;
                txt = "";
                switch (parseInt(extra)) {
                    // case 1 : txt = MSG_ALL_MAJOR;    break;
                    case 2 : txt = MSG_ALL_TEACH;    break;
                    // case 3 : txt = MSG_ALL_SCHOOL;   break;
                    // case 4 : txt = MSG_ALL_FAVORITE; break;
                }
                obj = document.getElementById("allCSTitle");
                if (obj != null) obj.setAttribute("title", txt);
                showListFolder(); // display course group
                return true;
                break;

        case "group" :
                txt  = "<manifest>";
                txt += "<ticket>" + ticket + "</ticket>";
                txt += "<action>" + act + "</action>";
                txt += "<group_id>" + extra + "</group_id>";
                txt += "</manifest>";
                res = xmlVars.loadXML(txt);
                if (!res) {
                    alert(MSG_SYS_ERROR);
                    return false;
                }
                xmlHttp.open("POST", "do_pickBoard.php", false);
                xmlHttp.send(xmlVars);
                // alert(xmlHttp.responseText);
                res = xmlDocs.loadXML(xmlHttp.responseText);
                if (!res) {
                    alert(MSG_SYS_ERROR);
                    return false;
                }
                ticket = getNodeValue(xmlDocs.documentElement, "ticket");
                showGroupCourse(); // display course group
                break;

            case "list_board" :
                txt  = "<manifest>";
                txt += "<ticket>" + ticket + "</ticket>";
                txt += "<action>" + act + "</action>";
                txt += "<course_id>" + extra + "</course_id>";
                txt += "</manifest>";

                res = xmlVars.loadXML(txt);
                if (!res) {
                    alert(MSG_SYS_ERROR); // + "2\n" + txt);
                    return false;
                }
                xmlHttp.open("POST", "do_pickBoard.php", false);
                xmlHttp.send(xmlVars);
                res = xmlVars.loadXML(xmlHttp.responseText);
                if (!res) {
                    alert(MSG_SYS_ERROR); // + "2\n"+ xmlHttp.responseText);
                    return false;
                }
                ticket = getNodeValue(xmlVars.documentElement, "ticket");
                txt = getBoardList();
                if(txt=='') {
                    alert(MSG_NO_BOARD);
                    returnValue = false;
                } else {
                    //#47507 [全體/討論板/文章內容/轉貼][延伸問題]代入「任教課程」之後沒有把該課程的討論板代入下拉選單：chrome使用原始js方式塞回去
                    //#47549 [Safari][全體/討論板/文章內容/轉貼][延伸問題]代入「任教課程」之後沒有把該課程的討論板代入下拉選單：chrome使用原始js方式塞回去
                    var browser = 'ie';
                    if(navigator.userAgent.indexOf('MSIE')>0){
                        browser = 'ie';
                    }else if(navigator.userAgent.indexOf('Firefox')>0){
                        browser = 'ff';
                    }else if(navigator.userAgent.indexOf('Chrome')>0){
                        browser = 'chr';
                    }else if(navigator.userAgent.indexOf('Safari')>0){
                        browser = 'sf';
                    }else{
                        browser = 'op';
                    }            
                    if(browser == 'chr' || browser == 'sf' || browser == 'ff') {
                        opener.document.forms['form_repost'].repost_board.options.length=0;
                        var t1 = txt.split('\t\t');
                        for (i in t1) {
                            var oOption = document.createElement("OPTION");
                            var t2 = t1[i].split('\t');
                            oOption.text  = t2[1]; 
                            oOption.value = t2[0];
                            //非空值才寫入
                            if(oOption.value){
//                                opener.document.forms['form_repost'].repost_board.options.add(oOption,i);
                                
                                if (window.console) {console.log('do_func-list_board', oOption.value, 123, $(oOption).html(), 456);}
//                                opener.document.forms['form_repost'].repost_board.options.add('<option value="1000000141">課程討論板</option>',i);
                                $('#form_repost', opener.document).find('#repost_board').append('<option value="' + oOption.value + '">' + $(oOption).html() + '</option>');
                            }
                        }          
                    }else{                
                        returnValue = txt; 
                    }              
                }
                window.close();
                return true;
                break;
        }
        return true;
    }

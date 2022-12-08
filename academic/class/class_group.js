    /**
    * $Id: class_group.js,v 1.1 2010/02/24 02:38:14 saly Exp $
    **/

    var xmlDoc = null, xmlClip = null, xmlHttp = null;
    var langList = new Array("big5", "gb2312", "en", "euc-jp", "user-define");
    var idNum = 1;
    var editIdx = 0;
    var notSave = false;
    var istemp = false;

 // ////////////////////////////////////////////////////////////////////////
    /**
     * 將 XML 的節點以一般的文字呈現
     **/
    function xml(node) {
        if (node == null) return "";

        if (isIE) return node.xml;
        if (isMZ) return (new XMLSerializer()).serializeToString(node);

        return "";
    }
 // ////////////////////////////////////////////////////////////////////////
    /**
    * 將 html 的 tag 轉成 普通文字 顯示
    */
    function htmlspecialchars(str) {
        var re = /</ig;
        var val = str;
        val = val.replace(/&/ig, "&amp;");
        val = val.replace(/</ig, "&lt;");
        val = val.replace(/>/ig, "&gt;");
        val = val.replace(/'/ig, "&#039;");
        val = val.replace(/"/ig, "&quot;");
        return val;
    }
  // ////////////////////////////////////////////////////////////////////////
    /**
     * 在 Table 的最後插入一筆資料
     *     @param obj     : 要查入的 Table 物件
     *     @param cssName : 這一筆資料的背景顏色
     *     @param txt     : 要插入的資料
     **/
    function tbInsertRow(obj, cssName, txt) {
        var len = 0;
        if ((typeof(obj) != "object") || (obj == null))
            return false;
        len = obj.rows.length;
        obj.insertRow(len);
        obj.rows[len].className = cssName;
        obj.rows[len].insertCell(0);
    //  obj.rows[len].cells[0].className = "font01";
        obj.rows[len].cells[0].noWrap = true;
        obj.rows[len].cells[0].innerHTML = txt;
    }

    /**
     * 檢查是不是第一個節點
     *     @param node : 要檢查的節點
     *     @param tag  : 要檢查的 Tag
     *     @return true  : 是第一個節點
     *             false : 不是第一個節點
     **/
    function chkFirst(node, tag) {
        var newNode = null;

        if (node == null) return true;
        newNode = node.previousSibling;
        while (newNode != null) {
            if (newNode.tagName == tag) {
                return false;
            }
            newNode = newNode.previousSibling;
        }
        return true;
    }

    /**
     * 檢查是不是最後一個節點
     *     @param node : 要檢查的節點
     *     @param tag  : 要檢查的 Tag
     *     @return 1. true  : 是最後一個節點
     *             2. false : 不是最後一個節點
     **/
    function chkLast(node, tag) {
        var newNode = null;

        if (node == null) return true;
        newNode = node.nextSibling;
        while (newNode != null) {
            if (newNode.tagName == tag) {
                return false;
            }
            newNode = newNode.nextSibling;
        }
        return true;
    }

    /**
     * 建立整個課程群組
     **/
    var indx = 0;
    var col = "cssTrEvn";
    function buildGP(node, indent, obj) {
        var nodes = null, tmpNode = null, newNode = null;
        var txt = "", txt1 = "";

        if ((typeof(node) != "object") || (node == null)
            || (typeof(obj) != "object") || (obj == null))
            return false;

        if (!node.hasChildNodes()) return false;
        nodes = node.childNodes;
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].nodeType != 1) continue;
            if (nodes[i].nodeName == "classes") {
                col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
                indx++;
                txt = "";

                // 縮排 (ident)
                tmpNode = nodes[i].parentNode;
                txt1 = "";
                while ((tmpNode != null) && (tmpNode.tagName != "manifest")) {
                    if (chkLast(tmpNode, "classes")) {
                        txt1 = '<span style="width: 16px;">&nbsp;&nbsp;</span>' + txt1;
                    } else {
                        txt1 = '<img src="/theme/' + theme + '/academic/vertline.gif" width="16" height="18" border="0" align="absmiddle">' + txt1;
                    }
                    tmpNode = tmpNode.parentNode;
                }
                txt += txt1;
                txt += (chkLast(nodes[i], "classes")) ? '<img src="/theme/' + theme + '/academic/lastnode.gif" width="16" height="18" border="0" align="absmiddle">' : '<img src="/theme/' + theme + '/academic/node.gif" width="16" height="18" border="0" align="absmiddle">';

                txt += '<input type="checkbox" value="'+ nodes[i].getAttribute("id") + '">';
                txt += "" + indx + ".<a href=\"javascript:void(null)\" class=\"cssAnchor\" onclick=\"parent.main.displaySetPage(" + indx + "); return false;\">" + htmlspecialchars(getCaption(nodes[i])) + "</a>";
                tbInsertRow(obj, col, txt);
                buildGP(nodes[i], indent + 1, obj);
            }
        }
    }

    /**
     * Parse 整個課程群組的 XML
     **/
    function initGP() {
        var obj = document.getElementById('CGroup');
        var node = null;
        var txt = "";

        if (obj == null) return false;
        obj.innerHTML = "";
        indx = 0;
        col = "cssTrEvn";

        node = document.createElement("table");
        node.className = "cssTable";
        node.border = 0;
        node.cellspacing = 1;
        node.cellpadding = 3;
        node.width = "500";

        txt = MSG_HELP;
        tbInsertRow(node, "cssTrHead", txt);
        txt = '<input type="checkbox" disabled>' + school_name;
        tbInsertRow(node, "cssTrEvn", txt);

        if (xmlDoc.documentElement != null) {
            buildGP(xmlDoc.documentElement, 0, node);
        }

        obj.appendChild(node);
    }

    /**
     * 儲存整個課程群組的 XML
     **/
    function saveGP() {
        var xmlVars = null;
        var node = null;
        var msg = "";
        var txt = '';

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlDoc)  != "object") || (xmlDoc  == null)) xmlDoc  = XmlDocument.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        xajax_clean_temp(st_id);
        if (cut_classes.length > 0){
            if (confirm(MSG_cur_error)){
                xmlHttp.open("POST", "/academic/class/class_group_save.php", false);
                xmlHttp.send(xmlDoc);
                //  alert('xmlDoc.xml 192='+xmlDoc.xml);
                //  alert('xmlHttp.responseText 194='+xmlHttp.responseText);
                if (!xmlVars.loadXML(xmlHttp.responseText)) {
                    msg = MSG_SAVE_FAIL;
                }
                node = xmlVars.selectSingleNode('//result');
                if (node.hasChildNodes()){
                    /*
                     * 處理 有執行 [剪下班級] 但 未 [貼上班級]
                     */
                     txt = "<manifest><class_unpaste>" + cut_classes + "</class_unpaste></manifest>";
                     xmlDoc.loadXML(txt);
                     xmlHttp.open("POST", "/academic/class/class_unpaste.php", false);
                     xmlHttp.send(xmlDoc);
                     // alert('xmlHttp.responseText 207='+xmlHttp.responseText);
                     if (!xmlVars.loadXML(xmlHttp.responseText)) {
                          msg = MSG_cur_error2;
                     }
                     node = xmlVars.selectSingleNode('//result');
                     if (node.hasChildNodes()) {
                        if (node.firstChild.nodeValue == "0"){
                            msg = MSG_SAVE_SUCCESS;
                            /*
                             * 將 cut_classes 的值 清空
                             */
                            cut_classes = '';
                        }else{
                            msg = MSG_cur_error2;
                        }
                     } else {
                        msg = MSG_cur_error2;
                     }
               }else{
                  msg =  MSG_SAVE_FAIL;
               }
               notSave = false;
                alert(msg);
            }
        }else{
            xmlHttp.open("POST", "/academic/class/class_group_save.php", false);
            xmlHttp.send(xmlDoc);
            //  alert('xmlDoc.xml 192='+xmlDoc.xml);
            //  alert('xmlHttp.responseText 194='+xmlHttp.responseText);
            if (!xmlVars.loadXML(xmlHttp.responseText)) {
                msg = MSG_SAVE_FAIL;
            }
            node = xmlVars.selectSingleNode('//result');
            if (node.hasChildNodes()) {
                msg = (node.firstChild.nodeValue == "0") ? MSG_SAVE_SUCCESS : MSG_SAVE_FAIL;
            } else {
                msg = MSG_SAVE_FAIL;
            }
            notSave = false;
            alert(msg);
        }

        loadGP();
    }

    var editor = new Object();
    editor.setHTML = function(x)
    {
        xmlDoc.loadXML(x);
        initGP();
    };

    /**
     * 載入整個課程群組的 XML
     **/
    function loadGP() {
        var obj = null;
        var txt = "";

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlDoc)  != "object") || (xmlDoc  == null)) xmlDoc  = XmlDocument.create();

        txt  = "<manifest>";
        txt += "<ticket></ticket>";
        txt += "</manifest>";
        if (!xmlDoc.loadXML(txt)) {
            xmlDoc.loadXML("<manifest />");
            return false;
        }

        xmlHttp.open("POST", "/academic/class/class_group_get.php", false);
        xmlHttp.send(xmlDoc);

    //   alert('xmlHttp.responseText 226='+xmlHttp.responseText);

        if (!xmlDoc.loadXML(xmlHttp.responseText)) {
            xmlDoc.loadXML(txt);
        }
        initGP();

        xajax_check_temp(st_id, 'FCK.editor');
        window.setInterval(function(){if (notSave) xajax_save_temp(st_id, xmlDoc.xml);}, 100000);
    }
 // ////////////////////////////////////////////////////////////////////////
    /**
     * 搜尋要處理的節點
     **/
    function searchPoint() {
        var idx = new Array();
        var obj = null, nodes = null;
        try {
            obj = document.getElementById("CGroup");
            if (obj == null) throw(MSG_SYS_ERROR);
            nodes = obj.getElementsByTagName("input");
            if (nodes == null) throw(MSG_SYS_ERROR);
        }
        catch(ex) {
            alert(ex);
            return false;
        }
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].getAttribute("type") == "checkbox")
                && (nodes[i].checked) ) {
                idx[idx.length] = i;
            }
        }

        return idx;
    }

    /**
     * 選取或取消全部節點
     *     @param ActMode : 動作
     *         1 : 全部選取
     *         2 : 全部取消
     *         3 : 反向選取
     **/
    function selectPoint(ActMode) {
        var obj = null, nodes = null;
        try {
            obj = document.getElementById("CGroup");
            if (obj == null) throw(MSG_SYS_ERROR);
            nodes = obj.getElementsByTagName("input");
            if (nodes == null) throw(MSG_SYS_ERROR);
        }
        catch(ex) {
            alert(ex);
            return false;
        }
        for (var i = 1; i < nodes.length; i++) {
            if (nodes[i].getAttribute("type") == "checkbox") {
                switch (parseInt(ActMode)) {
                    case 1 : nodes[i].checked = true;  break;
                    case 2 : nodes[i].checked = false; break;
                    case 3 : nodes[i].checked = !nodes[i].checked; break;
                }
            }
        }
    }

    /**
     * 選取部分節點
     *     @param nFrom : 起始節點
     *     @param nTo   : 結束節點
     **/
    function selectRang(nFrom, nTo) {
        var idFrom = 0, idTo = 0;
        var obj = null, nodes = null;

        try {
            idFrom = parseInt(nFrom);
            idTo = parseInt(nTo);
            if (isNaN(idFrom) || isNaN(idTo)) throw "Fill Number!";
            obj = document.getElementById("CGroup");
            if (obj == null) throw(MSG_SYS_ERROR);
            nodes = obj.getElementsByTagName("input");
            if (nodes == null) throw(MSG_SYS_ERROR);
        }
        catch(ex) {
            alert(ex);
            return false;
        }
        if (idFrom > idTo) {
            idFrom = idTo;
            idTo = parseInt(nFrom);
        }
        selectPoint(2);
        for (var i = idFrom; i <= idTo; i++) {
            if (i == 0) continue;
            if (nodes[i] != null) nodes[i].checked = true;
        }
    }

    /**
     * 取得前一個老哥的 Node
     **/
    function getPrevNode(node, tag) {
        var prevNode = null;
        if ((typeof(node) != "object") || (node == null))
            return null;

        prevNode = node.previousSibling;
        while (prevNode != null) {
            if ((prevNode.nodeType == 1) && (prevNode.tagName == tag)) {
                return prevNode;
            }
            prevNode = prevNode.previousSibling;
        }
        return null;
    }

    /**
     * 取得下一個老弟的 Node
     **/
    function getNextNode(node, tag) {
        var nextNode = null;
        if ((typeof(node) != "object") || (node == null))
            return null;

        nextNode = node.nextSibling;
        while (nextNode != null) {
            if ((nextNode.nodeType == 1) && (nextNode.tagName == tag)) {
                return nextNode;
            }
            nextNode = nextNode.nextSibling;
        }
        return null;
    }

    /**
     * 交換 Node
     **/
    function swapNode(node1, node2) {
        if ((typeof(node1) != "object") || (node1 == null))
            return null;

        if ((typeof(node2) != "object") || (node2 == null))
            return null;

        node1.parentNode.insertBefore(node2.cloneNode(true), node1);
        node2.parentNode.insertBefore(node1.cloneNode(true), node2);
        node1.parentNode.removeChild(node1);
        node2.parentNode.removeChild(node2);
    }

    /**
     * 弟節點轉為子節點
     **/
    function Brother2child(node, tag){
        var cur = node.nextSibling;
        var newNode = null;
        while((cur != null) && (cur.tagName == tag)) {
            newNode = cur.cloneNode(true);
            node.appendChild(newNode);
            node.parentNode.removeChild(cur);
            cur = node.nextSibling;
        }
    }

    /*
     * 子節點轉為弟節點
     */
    function Child2Brother(node, tag){
        var nodes = node.getElementsByTagName(tag);
        var newNode, ref;
        if (nodes.length == 0) return;
        nodes = node.childNodes;
        for(var i=(nodes.length-1); i>=0; i--){
            if (nodes.item(i).tagName == tag){
                newNode = nodes.item(i).cloneNode(true);
                node.removeChild(nodes.item(i));
                ref = node.nextSibling;
                if (ref == null)
                    node.parentNode.appendChild(newNode);
                else
                    node.parentNode.insertBefore(newNode, ref);
            }
        }
    }

    /**
     * 重新點選 checkbox 的位置
     **/
    function rePoint(idx) {
        var obj = null, nodes = null;
        var cnt = 0;
        // 錯誤捕捉，避免發生 JavaScript 錯誤 (Begin)
        try {
            cnt = idx.length;
            if ((typeof(idx) != "object") || (cnt == 0))
                throw("");

            obj = document.getElementById("CGroup");
            if (obj == null) throw(MSG_SYS_ERROR);

            nodes = obj.getElementsByTagName("input");
            if (nodes == null) throw(MSG_SYS_ERROR);
        }
        catch(ex) {
            if ((typeof(ex) == "string") && (ex.length > 0))
                alert(ex);
            return false;
        }
        // 錯誤捕捉，避免發生 JavaScript 錯誤 (End)

        for (var i = 0; i < cnt; i++) {
            if (idx[i] == 0) continue;
            nodes[idx[i]].checked = true;
        }
    }

    /**
     * 建立一個課程群組的節點
     *     @param txt : 這個群組的名稱
     *     @return 課程群組的節點
     **/
    function buildNode(txt) {
        var node1 = null, node2 = null, node3 = null;
        var node4 = null, node5 = null, node6 = null,node7 = null,node8 = null;

        var code = "", str = "";
        var i = 0;

        if (typeof(lang) == 'undefined' || lang.search(/^(big5|gb2312|en|euc-jp|user-define)$/) < 0) {
            code = "big5";
        } else {
            code = lang;
        }

        node2 = xmlDoc.createElement("title");
        node2.setAttribute("default", code);

        for (i = 0; i < langList.length; i++) {
            //if (code == langList[i]) str = txt;
            //else str = "";
            str = txt;
            node3 = xmlDoc.createTextNode(str);
            node1 = xmlDoc.createElement(langList[i]);
            node1.appendChild(node3);
            node2.appendChild(node1);
        }

        // other tag (begin)
        node4 = xmlDoc.createElement("dep_id");
        node5 = xmlDoc.createElement("director");
        node8 = xmlDoc.createTextNode('0');
        node6 = xmlDoc.createElement("people_limit");
        node6.appendChild(node8);
        node8 = xmlDoc.createTextNode('102400');
        node7 = xmlDoc.createElement("quota_limit");
        node7.appendChild(node8);
        // other tag (end)

        node1 = xmlDoc.createElement("classes");
        node1.appendChild(node2);

        node1.appendChild(node4);
        node1.appendChild(node5);
        node1.appendChild(node6);
        node1.appendChild(node7);

    //  alert('node1.xml 473=' + node1.xml);

        idNum++;
        return node1;
    }

    /**
     * 新增一個節點
     * @pram isChild
     *    true： 表示新增一個子節點
     *    false：表示插入一個節點
     **/
    function addNode(isChild) {
        var node = null, newNode = null, nodes = null;
        var cnt = 0;
        var idx = new Array();

        idx = searchPoint();

        nodes = xmlDoc.getElementsByTagName('classes');
        cnt = idx.length;

        var newNodeTitle = MSG_NEW_GROUP + "_" + idNum;
        if ((cnt == 0) || (idx[0] == 0) || (nodes == null)) {
            // 新增在 Root 底下 (add as Root child)
            newNode = buildNode(newNodeTitle);
            editidx = nodes.length+1;
            xmlDoc.documentElement.appendChild(newNode);
        } else {
            node = nodes[parseInt(idx[0]) - 1];
            newNode = buildNode(newNodeTitle);
            if (isChild) {
                editidx = parseInt(idx[0])+1;
                node.appendChild(newNode);
                nodesAfterAdd = xmlDoc.getElementsByTagName('classes');
                for(var i=0; i<nodesAfterAdd.length; i++) {
                    if (old_getCaption(nodesAfterAdd[i]).trim() == newNodeTitle) {
                        editidx = i+1;
                        break;
                    }
                }
            } else {
                node.parentNode.insertBefore(newNode, node);
                editidx = parseInt(idx[0]);
            }
            for (var i = 0; i < cnt; i++) {
                if (isChild && (i == 0)) continue;
                idx[i]++;
            }
        }
        notSave = true;
        initGP();
        displaySetPage(editidx);
        rePoint(idx);
    }

    /**
     * 修改一個節點
     **/
    function editNode() {
        var obj = null, node = null, nodes = null,node4 = null;
        var txtNode= null, newNode = null;
        var idx = new Array();
        var cnt = 0;
        var isEmpty = true;
        var pattern = /^[0-9]*$/;
        var isDigit = true;
        var editIdx2 = 0;

        for (var i = 0; i < langList.length; i++) {
            obj = document.getElementById("GPName_" + langList[i]);
            if ((typeof(obj) != "object") || (obj == null)) continue;

            if (!Filter_Spec_char(obj.value)){
                alert(un_htmlspecialchars(MSG_class_error));
                return false;
            }

            if (obj.value != "") isEmpty = false;
        }
        if (isEmpty) {
            alert(MSG_FILL_TITLE);
            return false;
        }

        try {
            idx = searchPoint();

            if (editIdx == 0) {
                if ((typeof(idx) != "object") || (idx.length <= 0))
                    throw MSG_SEL_MODIFY;

                if (idx[0] == 0)
                    throw MSG_NOT_EDIT;
            }

            nodes = xmlDoc.getElementsByTagName('classes');
            if (nodes == null)
                throw MSG_SYS_ERROR;
        }
        catch (ex) {
            alert(ex);
            return false;
        }

        editIdx2 = editIdx;
        // if (editIdx == 0) editIdx = parseInt(idx[0]) - 1;
        editIdx = (editIdx == 0) ? parseInt(idx[0]) - 1 : editIdx - 1;

        node = nodes[editIdx];
        if (!node.hasChildNodes()) return false;

        nodes = node.getElementsByTagName("title");
        // check nodes null, length == 0

        if ((nodes == null) || (nodes.length == 0)) return false;
        node = nodes[0];

        for (var i = 0; i < langList.length; i++) {
            obj = document.getElementById("GPName_" + langList[i]);
            nodes = node.getElementsByTagName(langList[i]);

            if (obj != null) {
                if (nodes.length > 0) {
                    if (nodes[0].hasChildNodes()) {
                        nodes[0].firstChild.data = obj.value;
                    } else {
                        txtNode = xmlDoc.createTextNode(obj.value);
                        nodes[0].appendChild(txtNode);
                    }
                } else {
                    txtNode = xmlDoc.createTextNode(obj.value);
                    newNode = xmlDoc.createElement(langList[i]);
                    newNode.appendChild(txtNode);
                    node.appendChild(newNode);
                }
            }
        }

        node = node.parentNode;
        var dep_id = node.getElementsByTagName('dep_id');

        obj = document.getElementById('dep_id');

        if (dep_id.length > 0){
            if (dep_id[0].hasChildNodes()) {
                dep_id.item(0).text = obj.value;
            }else{
                txtNode = xmlDoc.createTextNode(obj.value);
                dep_id[0].appendChild(txtNode);
            }
        }else{
            node4 = xmlDoc.createElement("dep_id");
            txtNode = xmlDoc.createTextNode(obj.value);
            node4.appendChild(txtNode);
            node.appendChild(node4);
        }

        var director = node.getElementsByTagName('director');
        obj = document.getElementById('director');

        if (director.length > 0){
            if (director[0].hasChildNodes()) {
                director.item(0).text = obj.value;
            }else{
                txtNode = xmlDoc.createTextNode(obj.value);
                director[0].appendChild(txtNode);
            }
        }else{
            node4 = xmlDoc.createElement("director");
            txtNode = xmlDoc.createTextNode(obj.value);
            node4.appendChild(txtNode);
            node.appendChild(node4);
        }

        obj = document.getElementById('people_limit');
        var people_limit = node.getElementsByTagName('people_limit');
        if (people_limit.length == 0){
            node4 = xmlDoc.createElement("people_limit");
            txtNode = xmlDoc.createTextNode(obj.value);
            node4.appendChild(txtNode);
            node.appendChild(node4);
        }
        var people_limit = node.getElementsByTagName('people_limit');

        if (pattern.test(obj.value) == false){
                alert(MSG_title2);
                isDigit = false;
        }else if (people_limit[0].hasChildNodes()) {
                  people_limit.item(0).text = obj.value;
              }else{
                  txtNode = xmlDoc.createTextNode(obj.value);
                  people_limit[0].appendChild(txtNode);
              }


        obj = document.getElementById('quota_limit');

        var quota_limit = node.getElementsByTagName('quota_limit');

        if (quota_limit.length == 0){
            node4 = xmlDoc.createElement("quota_limit");
            txtNode = xmlDoc.createTextNode(obj.value);
            node4.appendChild(txtNode);
            node.appendChild(node4);
        }

        var quota_limit = node.getElementsByTagName('quota_limit');
        if (pattern.test(obj.value) == false){
            alert(MSG_title3);
            isDigit = false;
        }else if (quota_limit[0].hasChildNodes()) {
                  quota_limit.item(0).text = obj.value;
              }else{
                  txtNode = xmlDoc.createTextNode(obj.value);
                   quota_limit[0].appendChild(txtNode);
              }

        if (isDigit){

            notSave = true;
            editIdx = 0;

            actionLayer("divSettings", false);
            initGP();
            rePoint(idx);
        }else{
            editIdx = editIdx2;
        }
    }

    /**
     * 刪除勾選的節點
     * @param boolean val : 是否由其它 function 呼叫的
     *     true  : 是，不顯示確定刪除對話視窗
     *     false : 否，顯示確定刪除對話視窗
     **/
    function delNode(val) {
        var node = null, nodes = null, childs = null;
        var idx = new Array();
        var cnt = 0, indx = 0;

        idx = searchPoint();
        nodes = xmlDoc.getElementsByTagName('classes');

        try {
            if ((typeof(idx) != "object") || (nodes == null))
                throw MSG_SYS_ERROR;

            cnt = idx.length;
            if (cnt == 0)
                throw MSG_SEL_DELETE;

            if (idx[0] == 0)
                throw MSG_NOT_DELETE;
        }
        catch(ex) {
            alert(ex);
            return false;
        }
        if (!val && !confirm(MSG_CONFIRM_DEL)) return false;

        for (var i = (cnt - 1); i >= 0; i--) {
            indx = parseInt(idx[i]) - 1;

            childs = nodes[indx].getElementsByTagName('classes');
            if ((childs != null) && (childs.length > 0)) {
                Brother2child(childs[0], "classes");
                node = childs[0].cloneNode(true);
                nodes[indx].parentNode.replaceChild(node,nodes[indx]);
            } else {
                // 儲存被 cut 的班級 id (cut id)
                if (nodes[indx].getAttribute("id") != null){
                    cut_classes += nodes[indx].getAttribute("id") + ',';
                }

                nodes[indx].parentNode.removeChild(nodes[indx]);
            }
        }
        notSave = true;
        initGP();
    }

    /**
     * 刪除勾選的節點
     * @param boolean val : 是否由其它 function 呼叫的
     *     true  : 是，不顯示確定刪除對話視窗
     *     false : 否，顯示確定刪除對話視窗
     **/
    function C_delNode(val) {
        var node = null, nodes = null, childs = null;
        var idx = new Array();
        var cnt = 0, indx = 0;
        var ss = /,$/;

        idx = searchPoint();
        nodes2 = xmlDoc.getElementsByTagName('classes');
        var tmp = '';

        try {
            if ((typeof(idx) != "object") || (nodes2 == null))
                throw MSG_SYS_ERROR;

            cnt = idx.length;
            if (cnt == 0)
                throw MSG_SEL_DELETE;

            if (idx[0] == 0)
                throw MSG_NOT_DELETE;

            // checkbox begin
            obj = document.getElementById("CGroup");
            if (obj == null) throw(MSG_SYS_ERROR);

            nodes = obj.getElementsByTagName("input");
            if (nodes == null) throw(MSG_SYS_ERROR);
            // checkbox end
        }
        catch(ex) {
            alert(ex);
            return false;
        }

        if (!val && !confirm(MSG_CONFIRM_DEL)) return false;

        for (var i = nodes.length; i >= 0; i--) {

            if ((typeof(nodes[i]) != 'undefined') && (nodes[i].getAttribute("type") == "checkbox") && (nodes[i].checked) ) {

                // if (nodes.item(i).value  == null) begin

                if (nodes.item(i).value.length == 4){
                    //  尚未新增到資料庫節點的刪除法 (begin)
                    indx = i - 1;
                    childs = nodes2[indx].getElementsByTagName('classes');

                    if ((childs != null) && (childs.length > 0)) {
                        Brother2child(childs[0], "classes");
                        node = childs[0].cloneNode(true);
                        nodes2[indx].parentNode.replaceChild(node,nodes2[indx]);
                    } else {
                        nodes2[indx].parentNode.removeChild(nodes2[indx]);
                    }
                    //  尚未新增到資料庫節點的刪除法 (end)
                }else{
                    // 父節點 (parent)
                    var cur_node = xmlDoc.selectSingleNode('//classes[@id="' + nodes.item(i).value + '"]');
                    var parent_node = cur_node.parentNode;
                    var parent_id = parent_node.getAttribute("id");
                    if (parent_id == null){
                        parent_id = 1000000;
                    }
                    // 子節點 ( nodes.item(i).value )
                    // tmp = parent_id@child_id
                    tmp += parent_id + '@'+ nodes.item(i).value + ',';
                }

                // if (nodes.item(i).value  == null) end
            }
        }

        initGP();

  //      alert('tmp.length 836='+tmp.length);

        // 刪除 有 class_id 的班級 (begin)
        if (tmp.length > 0){

            notSave = false;

            tmp = tmp.replace(ss, '');

            // 刪除班級的資料 (delete class)
            txt = "<manifest><class_id>" + tmp + "</class_id></manifest>";

            xmlDoc.loadXML(txt);
            xmlHttp.open("POST", "/academic/class/class_del.php", false);
            xmlHttp.send(xmlDoc);

         //   alert('txt 852='+txt);

         //   alert('xmlHttp.responseText 858='+xmlHttp.responseText);

            if (!xmlDoc.loadXML(xmlHttp.responseText)) {
                msg = MSG_DEL_FAIL;
            }

            node = xmlDoc.selectSingleNode('//result');
            if (node.hasChildNodes()) {
                var tmp = node.firstChild.nodeValue;
                if (tmp.length == 1){
                    msg = MSG_DEL_FAIL;
                }else{
                    msg = node.firstChild.nodeValue;
                }
            } else {
                msg = MSG_DEL_FAIL;
            }
            alert(msg);

            xmlHttp.open("POST", "/academic/class/class_group_get.php", false);
            xmlHttp.send(xmlDoc);

    //      alert('xmlHttp.responseText='+xmlHttp.responseText);

            if (!xmlDoc.loadXML(xmlHttp.responseText)) {
                xmlDoc.loadXML(txt);
            }
            initGP();

        }
        // 刪除 有 class_id 的班級 (end)
    }

    /**
     * 移動節點
     * @pram ActMode
     *    1：上移
     *    2：下移
     *    3：左移
     *    4：右移
     **/
    function moveNode(ActMode) {
        var newNode = null, nextNode = null, prevNode = null;
        var nodes = null, childs = null, pNode = null;
        var idx = new Array();
        var cnt = 0, idex = 0, leng = 0;
        var lst = null;

        try {
            idx = searchPoint();
            if ((typeof(idx) != "object") || (idx.length <= 0))
                throw MSG_SEL_REMOVE_MOVE;

            if (idx[0] == 0)
                throw MSG_NOT_MOVE;
            cnt = idx.length;

            nodes = xmlDoc.getElementsByTagName('classes');
            if (nodes == null)
                throw MSG_SYS_ERROR;
        }
        catch (ex) {
            alert(ex);
            return false;
        }

        switch (parseInt(ActMode)) {
            case 1 :    // 上移 (Move Up)
                for (var i = 0; i < cnt; i++) {
                    if (idx[i] == 0) continue;
                    idex = parseInt(idx[i]) - 1;

                    prevNode = getPrevNode(nodes[idex], "classes");
                    if (prevNode != null) {
                        swapNode(nodes[idex], prevNode);
                        lst = prevNode.getElementsByTagName("classes");
                        idx[i] = idx[i] - (lst.length + 1);
                    } else {
                        alert(MSG_MV_UP_B + getCaption(nodes[idex]) + MSG_MV_UP_E);
                    }
                }
                break;

            case 2 :    // 下移 (Move Down)
                for (var i = (cnt - 1); i >= 0; i--) {
                    if (idx[i] == 0) continue;
                    idex = parseInt(idx[i]) - 1;

                    nextNode = getNextNode(nodes[idex], "classes");
                    if (nextNode != null) {
                        swapNode(nodes[idex], nextNode);
                        lst = nextNode.getElementsByTagName("classes");
                        idx[i] = idx[i] + (lst.length + 1);
                    } else {
                        alert(MSG_MV_DOWN_B + getCaption(nodes[idex]) + MSG_MV_DOWN_E);
                    }
                }
                break;

            case 3 :    // 左移 (Move Left)
                for (var i = (cnt - 1); i >= 0; i--) {
                    if (idx[i] == 0) continue;
                    idex = parseInt(idx[i]) - 1;

                    if (nodes[idex].parentNode.tagName == 'classes'){
                        Brother2child(nodes[idex]);
                        prevNode = nodes[idex].parentNode;
                        newNode = nodes[idex].cloneNode(true);
                        nodes[idex].parentNode.removeChild(nodes[idex]);

                        if (prevNode != null){
                            if (prevNode.nextSibling == null){
                                prevNode.parentNode.appendChild(newNode);
                            }
                            else{
                                prevNode.parentNode.insertBefore(newNode, prevNode.nextSibling);
                            }
                        }
                    }
                    else {
                alert(MSG_MV_LEFT_B + getCaption(nodes[idex]) + MSG_MV_LEFT_E);
                    }
                }
                break;

            case 4 :    // 右移 (Move Right)
                for (var i = (cnt - 1); i >= 0; i--) {
                    if (idx[i] == 0) continue;
                    idex = parseInt(idx[i]) - 1;

                    prevNode = getPrevNode(nodes[idex], "classes");
                    if (prevNode != null) {
                        newNode = nodes[idex].cloneNode(true);
                        nodes[idex].parentNode.removeChild(nodes[idex]);
                        prevNode.appendChild(newNode);
                        Child2Brother(newNode, "classes");
                    } else {
                        alert(MSG_MV_RIGHT_B + getCaption(nodes[idex]) + MSG_MV_RIGHT_E);
                    }
                }
                break;
            default:
        }

        notSave = true;
        initGP();
        rePoint(idx);
    }

    /**
     * 剪下或複製節點
     * @pram ActMode
     *    false：剪下
     *    true ：複製
     **/
    function cpmvNode(ActMode) {
        var node = null, nodes = null, childs = null;
        var idx = new Array();
        var cnt = 0, idex = 0;

        if (xmlClip == null) {
            xmlClip = XmlDocument.create();
            xmlClip.async = false;
        }
        xmlClip.loadXML("<manifest></manifest>");

        try {
            idx = searchPoint();

            if (ActMode){
                if ((typeof(idx) != "object") || (idx.length <= 0)){
                    throw MSG_SEL_COPY_MOVE;
                }
            }else{
                if ((typeof(idx) != "object") || (idx.length <= 0)){
                    throw MSG_SEL_CUT_MOVE;
                }
            }
            cnt = idx.length;

            nodes = xmlDoc.getElementsByTagName('classes');
            if (nodes == null)
                throw MSG_SYS_ERROR;
        }
        catch (ex) {
            alert(ex);
            return false;
        }

        for (var i = 0; i < cnt; i++) {
            if (idx[i] == 0) continue;
            idex = parseInt(idx[i]) - 1;
            node = nodes[idex].cloneNode(false);

            // if 為複製 則要將 id remove (begin)
            if (ActMode){
                node.removeAttribute("id");
            }
            // if 為複製 則要將 id remove (end)

            childs = nodes[idex].childNodes;
            for (var j = 0; j < childs.length; j++) {
                if ((childs[j].nodeType == 1) && (childs[j].nodeName == "title")) {
                    node.appendChild(childs[j].cloneNode(true));
                    break;
                }
            }
            xmlClip.documentElement.appendChild(node);
            if (ActMode) selectPoint(2);
        }
        if (!ActMode) delNode(true);
        notSave = true;
    }

    /**
     * 貼上節點
     * @pram ActMode
     *    false：剪下
     *    true ：複製
     **/
    function pasteNode() {
        var node = null, nodes = null, childs = null;
        var idx = new Array();
        var cnt = 0, idex = 0;

        try {
            if ((xmlClip == null) || (!xmlClip.documentElement.hasChildNodes()) )
                throw MSG_title84;

            idx = searchPoint();
            if ((typeof(idx) != "object") || (idx.length <= 0))
                throw MSG_SEL_MOVE;

            if (idx[0] == 0)
                throw MSG_NOT_MOVE;
            idex = parseInt(idx[0]) - 1;

            nodes = xmlDoc.getElementsByTagName('classes');
            if (nodes == null)
                throw MSG_SYS_ERROR;
            node = nodes[idex];
        }
        catch (ex) {
            // alert('ex='+ex);
            return false;
        }

        childs = xmlClip.documentElement.childNodes;
        cnt = childs.length;

        for (var i = 0; i < cnt; i++) {
            node.parentNode.insertBefore(childs[i].cloneNode(true), node);
            /* 如果有執行 [貼上班級] 的動作時, 要將被貼上班級的 id
               從 cut_classes 移除
             */
            if (childs[i].getAttribute("id") != null){
                var pastNode_id = childs[i].getAttribute("id") + ',';
                cut_classes = cut_classes.replace(pastNode_id,'');
            }
        }

        for (var i = 0; i < idx.length; i++) {
            if (idx[i] == 0) continue;
            idx[i] += cnt;
        }
        notSave = true;
        initGP();
        rePoint(idx);
    }
 // ////////////////////////////////////////////////////////////////////////
    /**
     * 顯示或隱藏對話框
     * state:
     *     true : 顯示
     *     false: 隱藏
     **/
    function actionLayer(objName, state) {
        layerAction(objName, state);
        if (!state) editIdx = 0;

    //  modify 2004-03-09 by amm
    //  notSave = istemp;

        notSave = true;
    }

    /**
     * 顯示設定群組名稱的對話框
     **/
    function displaySetPage(idex) {

        var obj = null, node = null, nodes = null;
        var idx = new Array();
        var orgLang = "";

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVar)  != "object") || (xmlDoc  == null)) xmlVar  = XmlDocument.create();

        idx = searchPoint();
        nodes = xmlDoc.getElementsByTagName("classes");

        if ((idex < 0) && (typeof(idx) == "object") && (idx.length > 0)) {
            idex = parseInt(idx[0]);
        }

        try {
            if (nodes == null) throw MSG_SYS_ERROR;
            if (idex < 0) throw MSG_SEL_MODIFY;
            if (idex == 0) throw MSG_NOT_EDIT;
        }
        catch (ex) {
            alert(ex);
            return false;
        }

        // 取得節點的名稱 (Begin)
        editIdx = idex;
        idex--;
        orgLang = lang;
        for (i = 0; i < langList.length; i++) {

            lang = langList[i];
            obj = document.getElementById("GPName_" + langList[i]);

            if (obj != null) {
               obj.value = old_getCaption(nodes[idex]);
            }

        }

        istemp = notSave;

        notSave = false;

        var q_class = nodes[idex].getAttribute("id");

        // if (q_class == null) begin
        if (q_class == null){
            obj = document.getElementById("ticket");
            if (obj != null) {
                obj.value = create;
            }

            obj = document.getElementById("dep_id");
            dep_id_node = nodes[idex].getElementsByTagName("dep_id");

            if (dep_id_node.length > 0){
                obj.value = dep_id_node.item(0).text;
            }else{
                obj.value = '';
            }

            obj = document.getElementById("director");
            director_node = nodes[idex].getElementsByTagName("director");

            if (director_node.length > 0){
                obj.value = director_node.item(0).text;
            }else{
                obj.value = '';
            }

            obj = document.getElementById("people_limit");
            people_limit_node = nodes[idex].getElementsByTagName("people_limit");

            if (people_limit_node.length > 0){
                obj.value = people_limit_node.item(0).text;
            }else{
                obj.value = '0';
            }

            obj = document.getElementById("quota_limit");
            quota_limit_node = nodes[idex].getElementsByTagName("quota_limit");

            if (quota_limit_node.length > 0){
                obj.value = quota_limit_node.item(0).text;
            }else{
                obj.value = '102400';
            }

        }else{
            // 查詢班級的資料
            txt = "<manifest><class_id>" + q_class + "</class_id></manifest>";
            xmlVar.loadXML(txt);
            xmlHttp.open("POST", "/academic/class/class_query.php", false);
            xmlHttp.send(xmlVar);

            xmlVar.loadXML(xmlHttp.responseText);

            // alert('xmlHttp.responseText='+xmlHttp.responseText);

            obj = document.getElementById("ticket");
            if (obj != null) {
                obj.value = edit;
            }

            // 取得節點的名稱 (Begin)
            obj = document.getElementById("class_id");
            if (obj != null) {
                obj.value = q_class;
            }
            // 部門代碼 (dep_id)
            var dep_id = xmlVar.selectSingleNode("//class/dep_id");
            obj = document.getElementById("dep_id");
            if (obj != null) {
                if (dep_id.hasChildNodes()){
                    if (dep_id.firstChild.nodeValue != 'N'){
                        obj.value = dep_id.firstChild.nodeValue;
                    }
                }else{
                    obj.value = '';
                }
            }
            // 部門代碼 (dep_id)
            obj = document.getElementById("dep_id");
            dep_id_node = nodes[idex].getElementsByTagName("dep_id");
            if (dep_id_node.length > 0){
                if (typeof(dep_id_node.item(0).text) != 'undefined')
                    obj.value = dep_id_node.item(0).text;
            }
            // 導師 (director)
            var director = xmlVar.selectSingleNode("//class/director");
            obj = document.getElementById("director");
            if (obj != null) {
                if (director.hasChildNodes){
                    if (director.firstChild.nodeValue != 'N'){
                        obj.value = director.firstChild.nodeValue;
                    }
                }else{
                    obj.value = '';
                }
            }
            obj = document.getElementById("director");
            director_node = nodes[idex].getElementsByTagName("director");
            if (director_node.length > 0){
                if (typeof(director_node.item(0).text) != 'undefined')
                    obj.value = director_node.item(0).text;
            }
            // 人數上限 (people limit)
            var people_limit = xmlVar.selectSingleNode("//class/people_limit");
            obj = document.getElementById("people_limit");
            if (obj != null) {
                if (people_limit.hasChildNodes){
                    obj.value = parseInt(people_limit.firstChild.nodeValue);
                }else{
                    obj.value = '0';
                }
            }
            obj = document.getElementById("people_limit");
            people_limit_node = nodes[idex].getElementsByTagName("people_limit");
            if (people_limit_node.length > 0){
                if (typeof(people_limit_node.item(0).text) != 'undefined')
                    obj.value = parseInt(people_limit_node.item(0).text);
            }
            // 空間上限 (quota limit)
            var quota_limit = xmlVar.selectSingleNode("//class/quota_limit");
            obj = document.getElementById("quota_limit");
            if (obj != null) {
                if (quota_limit.hasChildNodes){
                    obj.value = parseInt(quota_limit.firstChild.nodeValue);
                }else{
                    obj.value = '102400';
                }
            }
            obj = document.getElementById("quota_limit");
            quota_limit_node = nodes[idex].getElementsByTagName("quota_limit");

            if (quota_limit_node.length > 0){
                if (typeof(quota_limit_node.item(0).text) != 'undefined')
                    obj.value = quota_limit_node.item(0).text;
            }
            // 取得節點的名稱 (end)
        }
        // if (q_class == null) begin

        lang = orgLang;
        actionLayer("divSettings", true);

        // 取得節點的名稱 (End)
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 切換顯示的語系
     **/
    function chgLang(val) {
        var idx = -1;
        lang = val;
        idx = searchPoint();
        initGP();
        if (idx >= 0)
            rePoint(idx);
    }
////////////////////////////////////////////////////////////////////////////

/**
 * 檢查新增班級的資料
*/
function checkData(){
    var obj = document.getElementById("fmSetting");

    var re = /^\s*$/;
    var val;

    if (obj == null) return false;
    
    if (!chk_multi_lang_input(1, true, MSG_title, un_htmlspecialchars(MSG_class_error))) return false;

    val = obj.people_limit.value;
    for (i=0; i < val.length; i++){
        ch = val.charAt(i);
        if (!(ch >= '0' && ch <= '9')){
            alert(MSG_title2);
            obj.people_limit.focus();
            return false;
        }
    }

    val = obj.quota_limit.value;
    for (i=0; i < val.length; i++){
        ch = val.charAt(i);
        if (!(ch >= '0' && ch <= '9')){
            alert(MSG_title3);
            obj.quota_limit.focus();
            return false;
        }
    }

    editNode();
}

////////////////////////////////////////////////////////////////////////////

    /**
     * 刪除勾選的節點
     * @param boolean val : 是否由其它 function 呼叫的
     *     true  : 是，不顯示確定刪除對話視窗
     *     false : 否，顯示確定刪除對話視窗
     **/
    function delCNode(val) {
        var idx = new Array();
        var obj = null, nodes = null;
        var ss = /,$/;
        var msg = '';

        var xmlHttp = null;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlDoc)  != "object") || (xmlDoc  == null)) xmlDoc  = XmlDocument.create();

        try {
            obj = document.getElementById("CGroup");
            if (obj == null) throw(MSG_SYS_ERROR);

            nodes = obj.getElementsByTagName("input");
            if (nodes == null) throw(MSG_SYS_ERROR);

        }
        catch(ex) {
            alert(ex);
            return false;
        }

        var tmp = '';
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].getAttribute("type") == "checkbox")
                && (nodes[i].checked) ) {

                // 父節點 (parent)
                var cur_node = xmlDoc.selectSingleNode('//classes[@id="' + nodes.item(i).value + '"]');

                var parent_node = cur_node.parentNode;

                var parent_id = parent_node.getAttribute("id");

                if (parent_id == null){
                    parent_id = 1000000;
                }
                // 子節點 ( nodes.item(i).value )

                // tmp = parent_id@child_id

                tmp += parent_id + '@'+ nodes.item(i).value + ',';

            }
        }

        tmp = tmp.replace(ss, '');

     //   alert('tmp(class_group.js 730)='+tmp);

        cnt = tmp.length;

        if (cnt == 0){
          alert(MSG_SEL_DELETE);
        }else if (!val && !confirm(MSG_CONFIRM_DEL)) return false;

        // 刪除班級的資料 (delete class)
        txt = "<manifest><class_id>" + tmp + "</class_id></manifest>";

        xmlDoc.loadXML(txt);
        xmlHttp.open("POST", "/academic/class/class_del.php", false);
        xmlHttp.send(xmlDoc);

     //   alert('xmlHttp.responseText(class_group.js 736)='+xmlHttp.responseText);

        if (!xmlDoc.loadXML(xmlHttp.responseText)) {
            msg = MSG_DEL_FAIL;
        }

        node = xmlDoc.selectSingleNode('//result');
        if (node.hasChildNodes()) {
            var tmp = node.firstChild.nodeValue;
            if (tmp.length == 1){
               msg = MSG_DEL_FAIL;
            }else{
               msg = node.firstChild.nodeValue;
            }
        } else {
            msg = MSG_DEL_FAIL;
        }
        alert(msg);
        window.location.href='/academic/class/class_group.php';
    }
////////////////////////////////////////////////////////////////////////////
    function init(evnt) {
        var obj = null;
        // 載入工具列視窗 (Load toolbar)
        top.catalog.location.replace("/academic/class/class_group_tools.php");

        if ((typeof(isXmlExtras) == "undefined") || !isXmlExtras) {
            alert(MSG_NEED_LIB);
            return false;
        }
        chkBrowser();
    }

    window.onload = init;

    window.onunload = function () {
        top.catalog.location.href = "about:blank";
        top.FrameExpand(0, false, '');
    };

    window.onerror = function () {
        // return true;
    };
    
    window.onbeforeunload=function()
    {
        if (notSave) return MSG_EXIT;
    };

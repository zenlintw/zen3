	/**
	 * $Id: content_group.js,v 1.1 2010/02/24 02:38:16 saly Exp $
	 **/
	var xmlDoc = null,
	    xmlClip = null,
	    xmlHttp = null;
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
	    //	obj.rows[len].cells[0].className = "font01";
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
	    var nodes = null,
	        tmpNode = null,
	        newNode = null;
	    var txt = "",
	        txt1 = "";

	    if ((typeof(node) != "object") || (node == null) ||
	        (typeof(obj) != "object") || (obj == null))
	        return false;

	    if (!node.hasChildNodes()) return false;
	    nodes = node.childNodes;
	    for (var i = 0; i < nodes.length; i++) {
	        if (nodes[i].nodeType != 1) continue;
	        if (nodes[i].nodeName == "contents") {
	            col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
	            indx++;
	            txt = "";

	            // 縮排 (ident)
	            tmpNode = nodes[i].parentNode;
	            txt1 = "";
	            while ((tmpNode != null) && (tmpNode.tagName != "manifest")) {
	                if (chkLast(tmpNode, "contents")) {
	                    txt1 = '<span style="width: 16px;">&nbsp;&nbsp;</span>' + txt1;
	                } else {
	                    txt1 = '<img src="/theme/' + theme + '/academic/vertline.gif" width="16" height="18" border="0" align="absmiddle">' + txt1;
	                }
	                tmpNode = tmpNode.parentNode;
	            }
	            txt += txt1;
	            txt += (chkLast(nodes[i], "contents")) ? '<img src="/theme/' + theme + '/academic/lastnode.gif" width="16" height="18" border="0" align="absmiddle">' : '<img src="/theme/' + theme + '/academic/node.gif" width="16" height="18" border="0" align="absmiddle">';

	            txt += '<input type="checkbox" value="' + nodes[i].getAttribute("id") + '">';
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

	    if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
	    if ((typeof(xmlDoc) != "object") || (xmlDoc == null)) xmlDoc = XmlDocument.create();
	    if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

	    xmlHttp.open("POST", "/academic/content/content_group_save.php", false);

	    //	alert('xmlDoc.xml 192='+xmlDoc.xml);

	    xajax_clean_temp(st_id);
	    xmlHttp.send(xmlDoc);

	    //	alert('xmlHttp.responseText 194='+xmlHttp.responseText);

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

	    loadGP();
	}

	var editor = new Object();
	editor.setHTML = function(x) {
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
	    if ((typeof(xmlDoc) != "object") || (xmlDoc == null)) xmlDoc = XmlDocument.create();

	    txt = "<manifest>";
	    txt += "<ticket></ticket>";
	    txt += "</manifest>";
	    if (!xmlDoc.loadXML(txt)) {
	        xmlDoc.loadXML("<manifest />");
	        return false;
	    }

	    xmlHttp.open("POST", "/academic/content/content_group_get.php", false);
	    xmlHttp.send(xmlDoc);

	    //	alert('xmlHttp.responseText 226='+xmlHttp.responseText);

	    if (!xmlDoc.loadXML(xmlHttp.responseText)) {
	        xmlDoc.loadXML(txt);
	    }
	    initGP();

	    xajax_check_temp(st_id, 'FCK.editor');
	    window.setInterval(function() {
	        if (notSave) xajax_save_temp(st_id, xmlDoc.xml);
	    }, 100000);
	}
	// ////////////////////////////////////////////////////////////////////////
	/**
	 * 搜尋要處理的節點
	 **/
	function searchPoint() {
	    var idx = new Array();
	    var obj = null,
	        nodes = null;
	    try {
	        obj = document.getElementById("CGroup");
	        if (obj == null) throw (MSG_SYS_ERROR);
	        nodes = obj.getElementsByTagName("input");
	        if (nodes == null) throw (MSG_SYS_ERROR);
	    } catch (ex) {
	        alert(ex);
	        return false;
	    }
	    for (var i = 0; i < nodes.length; i++) {
	        if ((nodes[i].getAttribute("type") == "checkbox") &&
	            (nodes[i].checked)) {
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
	    var obj = null,
	        nodes = null;
	    try {
	        obj = document.getElementById("CGroup");
	        if (obj == null) throw (MSG_SYS_ERROR);
	        nodes = obj.getElementsByTagName("input");
	        if (nodes == null) throw (MSG_SYS_ERROR);
	    } catch (ex) {
	        alert(ex);
	        return false;
	    }
	    for (var i = 1; i < nodes.length; i++) {
	        if (nodes[i].getAttribute("type") == "checkbox") {
	            switch (parseInt(ActMode)) {
	                case 1:
	                    nodes[i].checked = true;
	                    break;
	                case 2:
	                    nodes[i].checked = false;
	                    break;
	                case 3:
	                    nodes[i].checked = !nodes[i].checked;
	                    break;
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
	    var idFrom = 0,
	        idTo = 0;
	    var obj = null,
	        nodes = null;

	    try {
	        idFrom = parseInt(nFrom);
	        idTo = parseInt(nTo);
	        if (isNaN(idFrom) || isNaN(idTo)) throw "Fill Number!";
	        obj = document.getElementById("CGroup");
	        if (obj == null) throw (MSG_SYS_ERROR);
	        nodes = obj.getElementsByTagName("input");
	        if (nodes == null) throw (MSG_SYS_ERROR);
	    } catch (ex) {
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
	function Brother2child(node, tag) {
	    var cur = node.nextSibling;
	    var newNode = null;
	    while ((cur != null) && (cur.tagName == tag)) {
	        newNode = cur.cloneNode(true);
	        node.appendChild(newNode);
	        node.parentNode.removeChild(cur);
	        cur = node.nextSibling;
	    }
	}

	/*
	 * 子節點轉為弟節點
	 */
	function Child2Brother(node, tag) {
	    var nodes = node.getElementsByTagName(tag);
	    var newNode, ref;
	    if (nodes.length == 0) return;
	    nodes = node.childNodes;
	    for (var i = (nodes.length - 1); i >= 0; i--) {
	        if (nodes.item(i).tagName == tag) {
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
	    var obj = null,
	        nodes = null;
	    var cnt = 0;
	    // 錯誤捕捉，避免發生 JavaScript 錯誤 (Begin)
	    try {
	        cnt = idx.length;
	        if ((typeof(idx) != "object") || (cnt == 0))
	            throw ("");

	        obj = document.getElementById("CGroup");
	        if (obj == null) throw (MSG_SYS_ERROR);

	        nodes = obj.getElementsByTagName("input");
	        if (nodes == null) throw (MSG_SYS_ERROR);
	    } catch (ex) {
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
	    var node1 = null,
	        node2 = null,
	        node3 = null;
	    var node4 = null,
	        node5 = null,
	        node6 = null,
	        node7 = null,
	        node8 = null;

	    var code = "",
	        str = "";
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

	    node1 = xmlDoc.createElement("contents");
	    node1.appendChild(node2);

	    //	alert('node1.xml 473=' + node1.xml);

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
	    var node = null,
	        newNode = null,
	        nodes = null;
	    var cnt = 0;
	    var idx = new Array();

	    idx = searchPoint();

	    nodes = xmlDoc.getElementsByTagName('contents');
	    cnt = idx.length;

	    if ((cnt == 0) || (idx[0] == 0) || (nodes == null)) {
	        // 新增在 Root 底下 (add as Root child)
	        newNode = buildNode(MSG_NEW_GROUP + "_" + idNum);
	        xmlDoc.documentElement.appendChild(newNode);
	    } else {
	        node = nodes[parseInt(idx[0]) - 1];
	        newNode = buildNode(MSG_NEW_GROUP + "_" + idNum);
	        if (isChild) {
	            node.appendChild(newNode);
	            // 新增子節點時，要重新點選原本的位置時，會有問題 (some error need check)
	        } else {
	            node.parentNode.insertBefore(newNode, node);
	        }
	        for (var i = 0; i < cnt; i++) {
	            if (isChild && (i == 0)) continue;
	            idx[i]++;
	        }
	    }
	    notSave = true;
	    initGP();
	    rePoint(idx);
	}

	/**
	 * 修改一個節點
	 **/
	function editNode() {
	    var obj = null,
	        node = null,
	        nodes = null,
	        node4 = null;
	    var txtNode = null,
	        newNode = null;
	    var idx = new Array();
	    var cnt = 0;
	    var isEmpty = true;
	    pattern = /^[0-9]*$/;
	    var isDigit = true;
	    var editIdx2 = 0;

	    for (var i = 0; i < langList.length; i++) {
	        obj = document.getElementById("GPName_" + langList[i]);
	        if ((typeof(obj) != "object") || (obj == null)) continue;

	        if (!Filter_Spec_char(obj.value)) {
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

	        nodes = xmlDoc.getElementsByTagName('contents');
	        if (nodes == null)
	            throw MSG_SYS_ERROR;
	    } catch (ex) {
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
	    if (isDigit) {

	        notSave = true;
	        editIdx = 0;

	        actionLayer("divSettings", false);
	        initGP();
	        rePoint(idx);
	    } else {
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
	    var node = null,
	        nodes = null,
	        childs = null;
	    var idx = new Array();
	    var cnt = 0,
	        indx = 0;

	    idx = searchPoint();
	    nodes = xmlDoc.getElementsByTagName('contents');

	    try {
	        if ((typeof(idx) != "object") || (nodes == null))
	            throw MSG_SYS_ERROR;

	        cnt = idx.length;
	        if (cnt == 0)
	            throw MSG_SEL_DELETE;

	        if (idx[0] == 0)
	            throw MSG_NOT_DELETE;
	    } catch (ex) {
	        alert(ex);
	        return false;
	    }
	    if (!val && !confirm(MSG_CONFIRM_DEL)) return false;

	    for (var i = (cnt - 1); i >= 0; i--) {
	        indx = parseInt(idx[i]) - 1;

	        childs = nodes[indx].getElementsByTagName('contents');
	        if ((childs != null) && (childs.length > 0)) {
	            Brother2child(childs[0], "contents");
	            node = childs[0].cloneNode(true);
	            nodes[indx].parentNode.replaceChild(node, nodes[indx]);
	        } else {
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
	    var node = null,
	        nodes = null,
	        childs = null;
	    var idx = new Array();
	    var cnt = 0,
	        indx = 0;
	    var ss = /,$/;

	    idx = searchPoint();
	    nodes2 = xmlDoc.getElementsByTagName('contents');
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
	        if (obj == null) throw (MSG_SYS_ERROR);

	        nodes = obj.getElementsByTagName("input");
	        if (nodes == null) throw (MSG_SYS_ERROR);
	        // checkbox end
	    } catch (ex) {
	        alert(ex);
	        return false;
	    }

	    if (!val && !confirm(MSG_CONFIRM_DEL)) return false;

	    // for (var i = 0; i < nodes.length; i++) {
	    for (var i = nodes.length; i >= 0; i--) {

	        if ((typeof(nodes[i]) != 'undefined') && (nodes[i].getAttribute("type") == "checkbox") && (nodes[i].checked)) {
	            // if (nodes.item(i).value  == null) begin

	            if (nodes.item(i).value.length == 4) {
	                //  尚未新增到資料庫節點的刪除法 (begin)
	                indx = i - 1;
	                childs = nodes2[indx].getElementsByTagName('contents');

	                // if ((childs != null) && (childs.length > 0)) {
	                if ((childs.length > 0)) {
	                    Brother2child(childs[0], "contents");
	                    node = childs[0].cloneNode(true);
	                    nodes2[indx].parentNode.replaceChild(node, nodes2[indx]);
	                } else {
	                    nodes2[indx].parentNode.removeChild(nodes2[indx]);
	                }
	                //  尚未新增到資料庫節點的刪除法 (end)
	            } else {
	                // 父節點 (parent)
	                var cur_node = xmlDoc.selectSingleNode('//contents[@id="' + nodes.item(i).value + '"]');
	                var parent_node = cur_node.parentNode;
	                var parent_id = parent_node.getAttribute("id");
	                if (parent_id == null) {
	                    parent_id = 100000;
	                }
	                // 子節點 ( nodes.item(i).value )
	                // tmp = parent_id@child_id
	                tmp += parent_id + '@' + nodes.item(i).value + ',';
	            }

	            // if (nodes.item(i).value  == null) end
	        }
	    }

	    initGP();

	    //      alert('tmp.length 836='+tmp.length);

	    // 刪除 有 content_id 的教材類別 (begin)
	    if (tmp.length > 0) {

	        notSave = false;

	        tmp = tmp.replace(ss, '');

	        // 刪除教材類別的資料 (delete class)
	        txt = "<manifest><content_id>" + tmp + "</content_id></manifest>";

	        xmlDoc.loadXML(txt);
	        xmlHttp.open("POST", "/academic/content/content_group_del.php", false);
	        xmlHttp.send(xmlDoc);

	        //   alert('txt 852='+txt);

	        //	  alert('xmlHttp.responseText 858='+xmlHttp.responseText);

	        if (!xmlDoc.loadXML(xmlHttp.responseText)) {
	            msg = MSG_DEL_FAIL;
	        }

	        node = xmlDoc.selectSingleNode('//result');
	        if (node.hasChildNodes()) {
	            var tmp = node.firstChild.nodeValue;
	            if (tmp.length == 1) {
	                msg = MSG_DEL_FAIL;
	            } else {
	                msg = node.firstChild.nodeValue;
	            }
	        } else {
	            msg = MSG_DEL_FAIL;
	        }
	        alert(msg);

	        xmlHttp.open("POST", "/academic/content/content_group_get.php", false);
	        xmlHttp.send(xmlDoc);

	        //		alert('xmlHttp.responseText='+xmlHttp.responseText);

	        if (!xmlDoc.loadXML(xmlHttp.responseText)) {
	            xmlDoc.loadXML(txt);
	        }
	        initGP();

	    }
	    // 刪除 有 content_id 的教材類別 (end)
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
	    var newNode = null,
	        nextNode = null,
	        prevNode = null;
	    var nodes = null,
	        childs = null,
	        pNode = null;
	    var idx = new Array();
	    var cnt = 0,
	        idex = 0,
	        leng = 0;
	    var lst = null;

	    try {
	        idx = searchPoint();
	        if ((typeof(idx) != "object") || (idx.length <= 0))
	            throw MSG_SEL_REMOVE_MOVE;

	        if (idx[0] == 0)
	            throw MSG_NOT_MOVE;
	        cnt = idx.length;

	        nodes = xmlDoc.getElementsByTagName('contents');
	        if (nodes == null)
	            throw MSG_SYS_ERROR;
	    } catch (ex) {
	        alert(ex);
	        return false;
	    }

	    switch (parseInt(ActMode)) {
	        case 1: // 上移 (Move Up)
	            for (var i = 0; i < cnt; i++) {
	                if (idx[i] == 0) continue;
	                idex = parseInt(idx[i]) - 1;

	                prevNode = getPrevNode(nodes[idex], "contents");
	                if (prevNode != null) {
	                    swapNode(nodes[idex], prevNode);
	                    lst = prevNode.getElementsByTagName("contents");
	                    idx[i] = idx[i] - (lst.length + 1);
	                } else {
	                    alert(MSG_MV_UP_B + getCaption(nodes[idex]) + MSG_MV_UP_E);
	                }
	            }
	            break;

	        case 2: // 下移 (Move Down)
	            for (var i = (cnt - 1); i >= 0; i--) {
	                if (idx[i] == 0) continue;
	                idex = parseInt(idx[i]) - 1;

	                nextNode = getNextNode(nodes[idex], "contents");
	                if (nextNode != null) {
	                    swapNode(nodes[idex], nextNode);
	                    lst = nextNode.getElementsByTagName("contents");
	                    idx[i] = idx[i] + (lst.length + 1);
	                } else {
	                    alert(MSG_MV_DOWN_B + getCaption(nodes[idex]) + MSG_MV_DOWN_E);
	                }
	            }
	            break;

	        case 3: // 左移 (Move Left)
	            for (var i = (cnt - 1); i >= 0; i--) {
	                if (idx[i] == 0) continue;
	                idex = parseInt(idx[i]) - 1;

	                if (nodes[idex].parentNode.tagName == 'contents') {
	                    Brother2child(nodes[idex]);
	                    prevNode = nodes[idex].parentNode;
	                    newNode = nodes[idex].cloneNode(true);
	                    nodes[idex].parentNode.removeChild(nodes[idex]);

	                    if (prevNode != null) {
	                        if (prevNode.nextSibling == null) {
	                            prevNode.parentNode.appendChild(newNode);
	                        } else {
	                            prevNode.parentNode.insertBefore(newNode, prevNode.nextSibling);
	                        }
	                    }
	                } else {
	                    alert(MSG_MV_LEFT_B + getCaption(nodes[idex]) + MSG_MV_LEFT_E);
	                }
	            }
	            break;

	        case 4: // 右移 (Move Right)
	            for (var i = (cnt - 1); i >= 0; i--) {
	                if (idx[i] == 0) continue;
	                idex = parseInt(idx[i]) - 1;

	                prevNode = getPrevNode(nodes[idex], "contents");
	                if (prevNode != null) {
	                    newNode = nodes[idex].cloneNode(true);
	                    nodes[idex].parentNode.removeChild(nodes[idex]);
	                    prevNode.appendChild(newNode);
	                    Child2Brother(newNode, "contents");
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
	    var node = null,
	        nodes = null,
	        childs = null;
	    var idx = new Array();
	    var cnt = 0,
	        idex = 0;

	    if (xmlClip == null) {
	        xmlClip = XmlDocument.create();
	        xmlClip.async = false;
	    }
	    xmlClip.loadXML("<manifest></manifest>");

	    try {
	        idx = searchPoint();

	        if (ActMode) {
	            if ((typeof(idx) != "object") || (idx.length <= 0)) {
	                throw MSG_SEL_COPY_MOVE;
	            }
	        } else {
	            if ((typeof(idx) != "object") || (idx.length <= 0)) {
	                throw MSG_SEL_CUT_MOVE;
	            }
	        }
	        cnt = idx.length;

	        nodes = xmlDoc.getElementsByTagName('contents');
	        if (nodes == null)
	            throw MSG_SYS_ERROR;
	    } catch (ex) {
	        alert(ex);
	        return false;
	    }

	    for (var i = 0; i < cnt; i++) {
	        if (idx[i] == 0) continue;
	        idex = parseInt(idx[i]) - 1;
	        node = nodes[idex].cloneNode(false);

	        // if 為複製 則要將 id remove (begin)
	        if (ActMode) {
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
	    var node = null,
	        nodes = null,
	        childs = null;
	    var idx = new Array();
	    var cnt = 0,
	        idex = 0;

	    try {
	        if ((xmlClip == null) || (!xmlClip.documentElement.hasChildNodes()))
	            throw MSG_title84;

	        idx = searchPoint();
	        if ((typeof(idx) != "object") || (idx.length <= 0))
	            throw MSG_SEL_MOVE;

	        if (idx[0] == 0)
	            throw MSG_NOT_MOVE;
	        idex = parseInt(idx[0]) - 1;

	        nodes = xmlDoc.getElementsByTagName('contents');
	        if (nodes == null)
	            throw MSG_SYS_ERROR;
	        node = nodes[idex];
	    } catch (ex) {
	        // alert('ex='+ex);
	        return false;
	    }

	    childs = xmlClip.documentElement.childNodes;
	    cnt = childs.length;
	    for (var i = 0; i < cnt; i++) {
	        node.parentNode.insertBefore(childs[i].cloneNode(true), node);
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
	    //	notSave = istemp;

	    notSave = true;
	}

	/**
	 * 顯示設定群組名稱的對話框
	 **/
	function displaySetPage(idex) {
	    var obj = null,
	        node = null,
	        nodes = null;
	    var idx = new Array();
	    var orgLang = "";

	    if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
	    if ((typeof(xmlVar) != "object") || (xmlDoc == null)) xmlVar = XmlDocument.create();

	    idx = searchPoint();
	    nodes = xmlDoc.getElementsByTagName("contents");

	    if ((idex < 0) && (typeof(idx) == "object") && (idx.length > 0)) {
	        idex = parseInt(idx[0]);
	    }

	    try {
	        if (nodes == null) throw MSG_SYS_ERROR;
	        if (idex < 0) throw MSG_SEL_MODIFY;
	        if (idex == 0) throw MSG_NOT_EDIT;
	    } catch (ex) {
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
	    if (q_class == null) {
	        obj = document.getElementById("ticket");
	        if (obj != null) {
	            obj.value = create;
	        }

	    } else {

	        // 查詢教材類別的資料
	        txt = "<manifest><content_id>" + q_class + "</content_id></manifest>";
	        xmlVar.loadXML(txt);
	        xmlHttp.open("POST", "/academic/content/content_query.php", false);
	        xmlHttp.send(xmlVar);

	        xmlVar.loadXML(xmlHttp.responseText);

	        //alert('xmlHttp.responseText='+xmlHttp.responseText);

	        obj = document.getElementById("ticket");
	        if (obj != null) {
	            obj.value = edit;
	        }

	        // 取得節點的名稱 (Begin)
	        obj = document.getElementById("content_id");
	        if (obj != null) {
	            obj.value = q_class;
	        }
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
	 * 檢查新增教材類別的資料
	 */
	function checkData() {
	    var obj = document.getElementById("fmSetting");

	    var re = /^\s*$/;
	    var val;

	    if (obj == null) return false;

	    val = obj.GPName_big5.value + obj.GPName_gb2312.value + obj.GPName_en.value;

	    val = val.replace(re, "");

	    if (val.length <= 0) {
	        alert(MSG_title);
	        return false;
	    }

	    val = obj.people_limit.value;
	    for (i = 0; i < val.length; i++) {
	        ch = val.charAt(i);
	        if (!(ch >= '0' && ch <= '9')) {
	            alert(MSG_title2);
	            obj.people_limit.focus();
	            return false;
	        }
	    }

	    val = obj.quota_limit.value;
	    for (i = 0; i < val.length; i++) {
	        ch = val.charAt(i);
	        if (!(ch >= '0' && ch <= '9')) {
	            alert(MSG_title3);
	            obj.quota_limit.focus();
	            return false;
	        }
	    }
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
	    var obj = null,
	        nodes = null;
	    var ss = /,$/;
	    var msg = '';

	    var xmlHttp = null;

	    if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
	    if ((typeof(xmlDoc) != "object") || (xmlDoc == null)) xmlDoc = XmlDocument.create();

	    try {
	        obj = document.getElementById("CGroup");
	        if (obj == null) throw (MSG_SYS_ERROR);

	        nodes = obj.getElementsByTagName("input");
	        if (nodes == null) throw (MSG_SYS_ERROR);

	    } catch (ex) {
	        alert(ex);
	        return false;
	    }

	    var tmp = '';
	    for (var i = 0; i < nodes.length; i++) {
	        if ((nodes[i].getAttribute("type") == "checkbox") &&
	            (nodes[i].checked)) {

	            // 父節點 (parent)
	            var cur_node = xmlDoc.selectSingleNode('//contents[@id="' + nodes.item(i).value + '"]');

	            var parent_node = cur_node.parentNode;

	            var parent_id = parent_node.getAttribute("id");

	            if (parent_id == null) {
	                parent_id = 100000;
	            }
	            // 子節點 ( nodes.item(i).value )

	            // tmp = parent_id@child_id

	            tmp += parent_id + '@' + nodes.item(i).value + ',';

	        }
	    }

	    tmp = tmp.replace(ss, '');

	    //   alert('tmp(class_group.js 730)='+tmp);

	    cnt = tmp.length;

	    if (cnt == 0) {
	        alert(MSG_SEL_DELETE);
	    } else if (!val && !confirm(MSG_CONFIRM_DEL)) return false;

	    // 刪除教材類別的資料 (delete class)
	    txt = "<manifest><content_id>" + tmp + "</content_id></manifest>";

	    xmlDoc.loadXML(txt);
	    xmlHttp.open("POST", "/academic/content/content_del.php", false);
	    xmlHttp.send(xmlDoc);

	    //   alert('xmlHttp.responseText(class_group.js 736)='+xmlHttp.responseText);

	    if (!xmlDoc.loadXML(xmlHttp.responseText)) {
	        msg = MSG_DEL_FAIL;
	    }

	    node = xmlDoc.selectSingleNode('//result');
	    if (node.hasChildNodes()) {
	        var tmp = node.firstChild.nodeValue;
	        if (tmp.length == 1) {
	            msg = MSG_DEL_FAIL;
	        } else {
	            msg = node.firstChild.nodeValue;
	        }
	    } else {
	        msg = MSG_DEL_FAIL;
	    }
	    alert(msg);
	    window.location.href = '/academic/content/course_group.php';
	}
	////////////////////////////////////////////////////////////////////////////
	function init(evnt) {
	    var obj = null;
	    // 載入工具列視窗 (Load toolbar)
	    window.top.catalog.location.replace("/academic/content/content_group_tools.php");

	    if ((typeof(isXmlExtras) == "undefined") || !isXmlExtras) {
	        alert(MSG_NEED_LIB);
	        return false;
	    }
	    chkBrowser();
	}

	window.onload = init;

	window.onunload = function() {
	    window.top.catalog.location.href = "about:blank";
	    window.top.FrameExpand(0, false, '');
	};

	window.onerror = function() {
	    // return true;
	};

	window.onbeforeunload = function() {
	    if (notSave) return MSG_EXIT;
	};
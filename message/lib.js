	/**
	 * 訊息中心的共用函式
	 *
	 * 建立日期：2003/04/22
	 * @author  ShenTing Lin
	 * @version $Id: lib.js,v 1.1 2010/02/24 02:40:17 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	var xmlVars = null, xmlHttp = null, xmlClip = null;
	var isIE = false, isMZ = false;
	var langList = new Array("big5", "gb2312", "en", "euc-jp", "user-define");
	var idNum = 1;
	var editIdx = 0;
	var notSave = false;

	if (typeof(isNB) == "undefined") {
		var isNB = false;
	}
///////////////////////////////////////////////////////////////////////////////
	function chkBrowser() {
		var re = new RegExp("MSIE","ig");
		if (re.test(navigator.userAgent)) {
			isIE = true;
		}

		re = new RegExp("Gecko","ig");
		if (re.test(navigator.userAgent)) {
			isMZ = true;
		}
	}

	/**
	 * show or hidden layer
	 * state:
	 *     true : show
	 *     false: hidden
	 **/
	function actionLayer(objName, state) {
		var obj = document.getElementById(objName);
		var sclTop = 0, oHeight = 0;
		if (obj == null) {
			alert(msg05);
			return false;
		}
		if (state) {
			sclTop = parseInt(document.body.scrollTop);
			oHeight = (isMZ) ? parseInt(window.innerHeight) : document.body.offsetHeight;
			if ((parseInt(obj.style.top) < sclTop) ||
				(parseInt(obj.style.top) > (sclTop + oHeight))) {
				obj.style.top = sclTop + 50;
			}
			obj.style.visibility = "visible";
		} else {
			obj.style.visibility = "hidden";
			editIdx = 0;
		}
		return true;
	}

	function getTarget() {
		var obj = null;
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

	function htmlspecialchars(str) {
		var re = /</ig;
		var val = str;
		val = val.replace(/&/ig, "&amp;");
		val = val.replace(/</ig, "&lt;");
		val = val.replace(/>/ig, "&gt;");
		return val;
	}
///////////////////////////////////////////////////////////////////////////////
	function getNodeValue(node, tag) {
		var childs = null;

		if ((typeof(node) != "object") || (node == null))
			return "";
		childs = node.getElementsByTagName(tag);
		if ((childs == null) || (childs.length <= 0)) return "";
		if (childs[0].hasChildNodes()) {
			return childs[0].firstChild.data;
		} else {
			return "";
		}
	}

	/**
	 * Check is or not first node
	 *     @param node : want check node
	 *     @param tag  : want check tag
	 *     @return true  : yse
	 *             false : no
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
	 * Check is or not  last node
	 *     @param node : want check node
	 *     @param tag  : want check tag
	 *     @return 1. true  : yse
	 *             2. false : no
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
	 * swap node
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
	 * get previous sibling node
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
	 * get next sibling node
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
	 * next sibling node change become child node
	 **/
	function Brother2child(node, tag){
		var cur = node.nextSibling;
		var newNode = null;
		while (cur != null) {
			if ((cur.nodeType == 1) && (cur.tagName == tag)) {
				newNode = cur.cloneNode(true);
				node.appendChild(newNode);
				node.parentNode.removeChild(cur);
				cur = node.nextSibling;
			} else {
				cur = cur.nextSibling;
			}
			
		}
	}

	/*
	 * child node become next sibling node change
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
///////////////////////////////////////////////////////////////////////////////
// for list
///////////////////////////////////////////////////////////////////////////////
	function winFolderExpand(val) {
		var obj1 = document.getElementById("IconExpand");
		var obj2 = document.getElementById("IconCollection");
		var obj3 = document.getElementById("ToolBar");
		if ((obj1 == null) || (obj2 == null) || (obj3 == null))
			return false;

		if (val) {
			obj1.style.display = "none";
			obj2.style.display = "block";
			obj3.style.visibility = "visible";
			if (obj2.offsetParent != null) obj2.offsetParent.style.padding = "6px 6px 0px 0px";
			top.FrameExpand(1, true, '');
		} else {
			obj1.style.display = "block";
			obj2.style.display = "none";
			obj3.style.visibility = "hidden";
			if (obj1.offsetParent != null) obj1.offsetParent.style.padding = "6px 6px 0px 0px";
			top.FrameExpand(2, false, '58');
		}
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
	var preIdx = '';
	function mouseEvent(obj, val) {
		var idx = 0;
		var node = null;
		if (typeof(obj) != "object") return false;
		idx = obj.getAttribute("MyAttr");
		if (idx == preIdx) {
			if (parseInt(val) == 3) do_func("folder", idx);
			return false;
		}
		switch (parseInt(val)) {
			case 1 : obj.className = "cssTbFocus"; break;   // Mouse Over
			case 2 : obj.className = "cssTbBlur";  break;   // Mouse Out
			case 3 :    // Mouse Click
			case 4 :    // Mouse Click
				node = document.getElementById("Div" + preIdx);
				if (node != null) {
					node.className = "cssTbBlur";
				}
				obj.className = "cssTbFocus";
				preIdx = idx;
				if (parseInt(val) == 3) do_func("folder", idx);
				break;
			case 5 :    // Mouse Click (not want to change color)
				node = document.getElementById("Div" + preIdx);
				if (node != null) {
					node.className = "cssTbBlur";
					// node.parentNode.className = "font05";
				}
				preIdx = idx;
				do_func("folder", idx);
				break;
		}
	}

	/**
	 * Expand or Collect folder
	 * @param string val : object id
	 * @return
	 **/
	function gpExpand(val) {
		var obj = document.getElementById("Group" + val);
		var icon = document.getElementById("Icon" + val);
		if (obj == null) return false;
		if (obj.style.display == "block") {
			obj.style.display = "none";
			if (icon != null) {
				icon.src = "/public/images/icon_expand_inc.png";
				icon.alt = MSG_EXPAND;
				icon.title = MSG_EXPAND;
			}
		} else {
			obj.style.display = "block";
			if (icon != null) {
				icon.src = "/public/images/icon_expand_dec.png";
				icon.alt = MSG_COLLECT;
				icon.title = MSG_COLLECT;
			}
		}
		if (isIE) event.cancelBubble = true;
		return false;
	}

	function parseListFolder(node, indent) {
            var nodes = null, childs = null;
            var cnt = 0, idx = 0, idnum;
            var txt = "", result = "";

            if ((typeof(node) != "object") || (node == null)) return "";
            if (!node.hasChildNodes()) return "";
            nodes = node.childNodes;
            cnt = nodes.length;
            for (var i = 0; i < cnt; i++) {
                if (nodes[i].nodeType != 1) continue;
                if (nodes[i].nodeName == "folder") {
                    idx = nodes[i].getAttribute("id");

                    txt += '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
                    txt += '<tr><td nowrap>';
                    // indent
                    for (var j = (indent + 1); j > 0 ; j--)
                            txt += '<pre style="width: 15px; display: inline;">&nbsp;&nbsp;</pre>';

                    childs = nodes[i].getElementsByTagName("folder");
                    if ((childs != null) && (childs.length > 0))
                            txt += '<img src="/public/images/icon_expand_inc.png" border="0" align="absmiddle" alt="' + MSG_EXPAND + '" title="' + MSG_EXPAND + '" id="Icon' + idx + '" onclick="gpExpand(\'' + idx + '\')">';
                    else
                            txt += '<img src="/public/images/icon_expand_dot.png" border="0" align="absmiddle">';
                    txt += '<input type="radio" name="actTarget" value="' + idx + '">';
                    txt += '<span class="cssTbBlur" MyAttr="' + idx + '" id="Div' + idx + '" onmouseover="mouseEvent(this, 1)" onmouseout="mouseEvent(this, 2)" onclick="mouseEvent(this, 3)">';
                    txt += htmlspecialchars(getCaption(nodes[i]));
                    txt += '</span>';
                    idnum++;
                    result = parseListFolder(nodes[i], indent + 1);
                    if (result != "") {
                            txt += '<div id="Group' + idx + '" style="display:none">' + result + '</div>';
                    }
                    txt += '</td></tr>';
                    txt += '</table>';
                }
            }
            return txt;
	}

	/**
	 * display massage center folder
	 * @return
	 **/
	function showListFolder() {
		var obj = document.getElementById("Folder");
		var nodes = null, pnode = null, node = null, attr = null;
		var txt = "";
		var re = /^Group/;

		if ((typeof(obj) != "object") || (obj == null)) return false;
		node = getRootNode();
		txt = parseListFolder(node, 0);
		obj.innerHTML = txt;
		// Cancel event bubbles (Begin) 
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
		// Cancel event bubbles (End)

		// Expand parent folder (Begin)
		node = document.getElementById("Div" + folder_id);
		if ((typeof(node) == "object") && (node != null)) {
			mouseEvent(node, 4); // Show last visit folder
			pnode = node.parentNode;
			while ((pnode != null) && (pnode.nodeType == 1)) {
				attr = pnode.getAttribute("id");
				if ((attr != null) && (re.test(attr))) {
					attr = attr.replace(re, "");
					if (pnode.style.display == "none")
						gpExpand(attr);
				}
				pnode = pnode.parentNode;
			}
		}
		// Expand parent folder (End)
	}

///////////////////////////////////////////////////////////////////////////////
// for manage
///////////////////////////////////////////////////////////////////////////////

	/**
	 * search node
	 **/
	function searchPoint() {
		var idx = new Array();
		var obj = null, nodes = null;
		try {
			obj = document.getElementById("Folder");
			if (obj == null) throw(msg05);
			nodes = obj.getElementsByTagName("input");
			if (nodes == null) throw(msg05);
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
	 * 重新點選 checkbox 的位置
	 **/
	function rePoint(idx) {
		var obj = null, nodes = null;
		var cnt = 0;
		// catch error (Begin)
		try {
			cnt = idx.length;
			if ((typeof(idx) != "object") || (cnt == 0))
				throw("");

			obj = document.getElementById("Folder");
			if (obj == null) throw(MSG_SYS_ERROR);

			nodes = obj.getElementsByTagName("input");
			if (nodes == null) throw(MSG_SYS_ERROR);
		}
		catch(ex) {
			if ((typeof(ex) == "string") && (ex.length > 0))
				alert(ex);
			return false;
		}
		// catch error (End)

		for (var i = 0; i < cnt; i++) {
			if (idx[i] == 0) continue;
			nodes[idx[i]].checked = true;
		}
	}

	/**
	 * select node
	 *     @param integer ActMode : action
	 *         1 : select all
	 *         2 : cancel select
	 *         3 : inverse select
	 **/
	function selectNode(ActMode) {
		var obj = null, nodes = null;
		try {
			obj = document.getElementById("Folder");
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
	 * select part node
	 *     @param nFrom : begin
	 *     @param nTo   : end
	 **/
	function selectRang(nFrom, nTo) {
		var idFrom = 0, idTo = 0;
		var obj = null, nodes = null;

		try {
			idFrom = parseInt(nFrom);
			idTo = parseInt(nTo);
			if (isNaN(idFrom) || isNaN(idTo)) throw "Fill Number!";
			obj = document.getElementById("Folder");
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
		selectNode(2);
		for (var i = idFrom; i <= idTo; i++) {
			if (i == 0) continue;
			if (nodes[i] != null) nodes[i].checked = true;
		}
	}

	function chkSysTrash(node) {
		var pNode = null, attr = null;
		var val = "";

		if ((typeof(node) != "object") || (node == null)) return -1;

		pNode = node.parentNode;
		while (pNode != null) {
			val = pNode.tagName;
			if (val == "manifest") return 0;
			else if (val == "folder") {
				attr = pNode.getAttribute("id");
				if ((attr != null) && ((attr == "sys_trash") || (attr == "sys_notebook_trash"))) return 1;
			}

			pNode = pNode.parentNode;
		}
		return 0;
	}

	function getSysTrash() {
		var nodes = null, attr = null;

		if (xmlVars == null) return null;
		nodes = xmlVars.documentElement.getElementsByTagName("folder");
		if ((nodes == null) || (nodes.length <= 0)) return null;
		for (var i = nodes.length - 1; i > 0; i--) {
			attr = nodes[i].getAttribute("id");
			if ((attr != null) && ((attr == "sys_trash") || (attr == "sys_notebook_trash"))) return nodes[i];
		}
		return null;
	}

	function getRootNode() {
		var node = null;
//                console.log(isNB);
//                console.log(xmlVars);
		if (isNB) {
			node = xmlVars.selectSingleNode("//folder[@id='sys_notebook']");
		} else {
			node = xmlVars.documentElement;
		}
//                console.log(node);
                
		return node;
	}

	/**
	 * move node
	 * @param integer ActMode
	 *    1：move up
	 *    2：move down
	 *    3：move left
	 *    4：move right
	 **/
	function moveNode(ActMode) {
		var newNode = null, nextNode = null, prevNode = null;
		var nodes = null, childs = null, pNode = null, attr = null;
		var idx = new Array();
		var cnt = 0, idex = 0, cout = 0;

		try {
			idx = searchPoint();
			if ((typeof(idx) != "object") || (idx.length <= 0))
				throw MSG_SEL_MOVE_NODE;

			if (idx[0] == 0)
				throw MSG_CANT_MOVE;
			cnt = idx.length;

			nodes = xmlVars.getElementsByTagName('folder');
			if (nodes == null)
				throw MSG_SYS_ERROR;
		}
		catch (ex) {
			alert(ex);
			return false;
		}

		switch (parseInt(ActMode)) {
			case 1 :    // move up
				for (var i = 0; i < cnt; i++) {
					if (idx[i] == 0) continue;
					idex = isNB ? parseInt(idx[i]) : parseInt(idx[i]) - 1;

					// reget nodes, i don't know why the nodes will lost.
					if (nodes.length < cnt) nodes = xmlVars.getElementsByTagName('folder');
					prevNode = getPrevNode(nodes[idex], "folder");
					if (prevNode != null) {
						childs = prevNode.getElementsByTagName("folder");
						cout = ((childs != null) && (childs.length > 0)) ? parseInt(childs.length) + 1 : 1;
						swapNode(nodes[idex], prevNode);
						idx[i] = idx[i] - cout;
					} else {
						alert(MSG_FOLDER + ' [' + getCaption(nodes[idex]) + '] ' + MSG_CANT_FORWARD);
					}
				}
				break;

			case 2 :    // move down
				for (var i = (cnt - 1); i >= 0; i--) {
					if (idx[i] == 0) continue;
					idex = isNB ? parseInt(idx[i]) : parseInt(idx[i]) - 1;
	
					nextNode = getNextNode(nodes[idex], "folder");
					if (nextNode != null) {
						childs = nextNode.getElementsByTagName("folder");
						cout = ((childs != null) && (childs.length > 0)) ? parseInt(childs.length) + 1 : 1;
						swapNode(nodes[idex], nextNode);
						idx[i] = idx[i] + cout;
						//idx[i]++;
					} else {
						alert(MSG_FOLDER + ' [' + getCaption(nodes[idex]) + '] ' + MSG_CANT_BACKWARD);
					}
				}
				break;

			case 3 :    // move left
				for (var i = (cnt - 1); i >= 0; i--) {
					if (idx[i] == 0) continue;
					idex = isNB ? parseInt(idx[i]) : parseInt(idx[i]) - 1;

					attr = nodes[idex].parentNode.getAttribute("id");
					if (isNB && (attr == "sys_notebook")) {
						alert(MSG_FOLDER + ' [' + getCaption(nodes[idex]) + '] ' + MSG_CANT_DEINDENT);
					} else if (nodes[idex].parentNode.tagName == "folder") {
						Brother2child(nodes[idex], "folder");
						prevNode = nodes[idex].parentNode;
						newNode = nodes[idex].cloneNode(true);
						nodes[idex].parentNode.removeChild(nodes[idex]);
						if (prevNode.nextSibling == null){
							prevNode.parentNode.appendChild(newNode);
						}
						else{
							prevNode.parentNode.insertBefore(newNode, prevNode.nextSibling);
						}
					}
					else {
						alert(MSG_FOLDER + ' [' + getCaption(nodes[idex]) + '] ' + MSG_CANT_DEINDENT);
					}
				}
				break;

			case 4 :    // move right
				for (var i = (cnt - 1); i >= 0; i--) {
				//for (var i = 0; i < cnt; i++) {
					if (idx[i] == 0) continue;
					idex = isNB ? parseInt(idx[i]) : parseInt(idx[i]) - 1;

					prevNode = getPrevNode(nodes[idex], "folder");
					if (prevNode != null) {
						newNode = nodes[idex].cloneNode(true);
						nodes[idex].parentNode.removeChild(nodes[idex]);
						prevNode.appendChild(newNode);
						Child2Brother(newNode, "folder");
					} else {
						alert(MSG_FOLDER + ' [' + getCaption(nodes[idex]) + '] ' + MSG_CANT_INDENT);
					}
				}
				break;
			default:
		}

		showManageFolder();
		rePoint(idx);
	}

	/**
	 * Create node
	 *     @param string txt : node name
	 *     @return node
	 **/
	function createNode(txt) {
		var node = null, node1 = null, node2 = null, node3 = null;
		var code = "", str = "";
		var i = 0;

		if (typeof(lang) == 'undefined' || lang.search(/^(big5|gb2312|en|euc-jp|user-define)$/) < 0) {
			code = "big5";
		} else {
			code = lang;
		}

		node = xmlVars.createElement("folder");

		node2 = xmlVars.createElement("setting");
		node.appendChild(node2);

		node2 = xmlVars.createElement("title");
		node2.setAttribute("default", code);
		for (i = 0; i < langList.length; i++) {
			str = txt;
			node3 = xmlVars.createTextNode(str);
			node1 = xmlVars.createElement(langList[i]);
			node1.appendChild(node3);
			node2.appendChild(node1);
		}
		node.appendChild(node2);

		node2 = xmlVars.createElement("help");
		for (i = 0; i < langList.length; i++) {
			str = txt;
			node3 = xmlVars.createTextNode(str);
			node1 = xmlVars.createElement(langList[i]);
			node1.appendChild(node3);
			node2.appendChild(node1);
		}
		node.appendChild(node2);

		idNum++;
		return node;
	}

	/**
	 * add node
	 * @param isChild
	 *    true： add as child node
	 *    false：inser node
	 **/
	function addNode(isChild) {
		var node = null, newNode = null, nodes = null;
		var cnt = 0, lst = -1;
		var idx = new Array();

		idx = searchPoint();
		nodes = xmlVars.getElementsByTagName('folder');
		cnt = idx.length;

		if ((cnt == 0) || (idx[0] == 0) || (nodes == null)) {
			// add on Root
			newNode = createNode(MSG_NEW_FOLDER + " (" + idNum + ")");
			node = getRootNode();
			node.appendChild(newNode);
			nodes = node.getElementsByTagName("folder");
			lst = nodes.length;
			//xmlVars.documentElement.appendChild(newNode);
		} else {
			node = isNB ? nodes[parseInt(idx[0])] : nodes[parseInt(idx[0]) - 1];
			newNode = createNode(MSG_NEW_FOLDER + " (" + idNum + ")");
			if (isChild) {
				node.appendChild(newNode);
				nodes = node.getElementsByTagName("folder");
				lst = parseInt(idx[0]) + nodes.length;
				// add child node happen some error when repoint
			} else {
				node.parentNode.insertBefore(newNode, node);
				lst = parseInt(idx[0]);
			}
			for (var i = 0; i < cnt; i++) {
				if (isChild && (i == 0)) continue;
				idx[i]++;
			}
		}

		showManageFolder();
		rePoint(idx);
		if (lst >= 0) {
			displaySetPage(lst);
		}
	}

	/**
	 * 修改一個節點
	 **/
	function editNode() {
		var obj = null, node = null, nodes = null;
		var txtNode= null, newNode = null;
		var xmlDocs = XmlDocument.create();
		var idx = new Array();
		var cnt = 0;
		var isEmpty = true;
		for (var i = 0; i < langList.length; i++) {
			obj = document.getElementById("GPName_" + langList[i]);
			if ((typeof(obj) != "object") || (obj == null)) continue;

			if (!Filter_Spec_char(obj.value)){
				alert(un_htmlspecialchars(MSG_TITLE_ERROR));
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
			if (editIdx < 0) {
				if ((typeof(idx) != "object") || (idx.length <= 0))
					throw MSG_SEL_EDIT_NODE;

				if (idx[0] == 0)
					throw MSG_CANT_EDIT;
			}

			nodes = xmlVars.getElementsByTagName("folder");
			if (nodes == null)
				throw MSG_SYS_ERROR;
		}
		catch (ex) {
			alert(ex);
			return false;
		}

		if (editIdx < 0) editIdx = parseInt(idx[0]) - 1;

		node = nodes[editIdx];
		if (!node.hasChildNodes()) return false;

		nodes = node.childNodes;
		cnt = nodes.length;
		for (var i = 0; i < cnt; i++) {
			if ((nodes[i].nodeType == 1) && (nodes[i].tagName == "title")) {
				node = nodes[i];
				break;
			}
		}

		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		for (var i = 0; i < langList.length; i++) {
			obj = document.getElementById("GPName_" + langList[i]);
			nodes = node.getElementsByTagName(langList[i]);
			if (obj != null) {
				if (nodes.length > 0) {
					if (nodes[0].hasChildNodes()) {
						nodes[0].firstChild.data = obj.value;
					} else {
						txtNode = xmlDocs.createTextNode(obj.value);
						nodes[0].appendChild(txtNode);
					}
				} else {
					txtNode = xmlDocs.createTextNode(obj.value);
					newNode = xmlDocs.createElement(langList[i]);
					newNode.appendChild(txtNode);
					node.appendChild(newNode);
				}
			}
		}
		notSave = true;
		editIdx = 0;
		showManageFolder();
		rePoint(idx);
	}

	/**
	 * delete node
	 **/
	function delNode(act) {
		var node = null, nodes = null, childs = null, attr = null;
		var xmlDocs = XmlDocument.create();
		var txt = "";
		var idx = new Array();
		var cnt = 0, indx = 0, val = 0;
		var re = /^sys_/;

		idx = searchPoint();
		nodes = xmlVars.getElementsByTagName('folder');
		xmlDocs.loadXML("<manifest></manifest>");

		try {
			if ((typeof(idx) != "object") || (nodes == null))
				throw MSG_SYS_ERROR;
			cnt = idx.length;
			if (cnt == 0) throw MSG_SEL_DEL_NODE;
			if (idx[0] == 0) throw MSG_CANT_DEL;
		}
		catch(ex) {
			if (!act) alert(ex);
			return false;
		}

		for (var i = (cnt - 1); i >= 0; i--) {
			indx = isNB ? parseInt(idx[i]) : parseInt(idx[i]) - 1;
			attr = nodes[indx].getAttribute("id");
			if ((attr != null) && re.test(attr)) {
				alert("[" + getCaption(nodes[indx]) + "] " + MSG_SYS_CANT_DEL);
				continue;
			}

            // 推播中心不允許刪除
            if ((attr != null) && attr == 'app_push_message') {
                alert("[" + getCaption(nodes[indx]) + "] " + MSG_SYS_CANT_DEL);
                continue;
            }

			if (!act) {
				val = chkSysTrash(nodes[indx]);
				if ((val < 0) ||
					((val > 0) && !confirm("[" + getCaption(nodes[indx]) + "] " + MSG_CONFIRM_DEL)))
					continue;
				else if (val == 0) {
					// move node to Trash (begin)
					node = nodes[indx].cloneNode(true);
					childs = node.getElementsByTagName("folder");
					for (var j = childs.length - 1; j > 0; j--) {
						node.removeChild(childs[j]);
					}
					xmlDocs.documentElement.insertBefore(node, xmlDocs.documentElement.firstChild);
					// move node to Trash (end)
				}
			}

			childs = nodes[indx].getElementsByTagName("folder");
			if ((childs != null) && (childs.length > 0)) {
				Brother2child(childs[0], "folder");
				node = childs[0].cloneNode(true);
				nodes[indx].parentNode.replaceChild(node,nodes[indx]);
			} else {
				nodes[indx].parentNode.removeChild(nodes[indx]);
			}
		}

		if (!act) {
			node = getSysTrash();
			if (node != null) {
				childs = xmlDocs.getElementsByTagName("folder");
				for (var i = 0; i < childs.length; i++) {
					node.appendChild(childs[i].cloneNode(true));
				}
			}
		}
		showManageFolder();
	}

	/**
	 * copy or cut node
	 * @param ActMode
	 *    false：cut
	 *    true ：copy
	 **/
	function cpcutNode(ActMode) {
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
			if ((typeof(idx) != "object") || (idx.length <= 0)) {
				throw (ActMode ? MSG_SEL_CP_NODE : MSG_SEL_CUT_NODE);
			}
				
			cnt = idx.length;

			nodes = xmlVars.getElementsByTagName('folder');
			if (nodes == null) throw MSG_SYS_ERROR;
		}
		catch (ex) {
			alert(ex);
			return false;
		}

		for (var i = 0; i < cnt; i++) {
			if (idx[i] == 0) continue;
			idex = isNB ? parseInt(idx[i]) : parseInt(idx[i]) - 1;
			node = nodes[idex].cloneNode(true);
			//node.removeAttribute("id");

			childs = nodes[idex].childNodes;
			for (var j = childs.length - 1; j > 0 ; j--) {
				if ((childs[j].nodeType == 1) && (childs[j].nodeName == "folder")) {
					node.removeChild(node.childNodes[j]);
				}
			}
			xmlClip.documentElement.appendChild(node);
			if (ActMode) selectNode(2);
		}
		if (!ActMode) delNode(true);
	}

	/**
	 * paste node
	 **/
	function pasteNode() {
		var node = null, nodes = null, childs = null;
		var idx = new Array();
		var cnt = 0, idex = 0;


		try {
			if ( (xmlClip == null) || (!xmlClip.documentElement.hasChildNodes()) )
				throw MSG_CLIP_EMPTY;
	
			idx = searchPoint();
			if ((typeof(idx) != "object") || (idx.length <= 0))
				throw MSG_SEL_PSE_NODE;

			if (idx[0] == 0) throw 'asdfas';
			idex = isNB ? parseInt(idx[0]) : parseInt(idx[0]) - 1;

			nodes = xmlVars.getElementsByTagName('folder');
			if (nodes == null) throw MSG_SYS_ERROR;
			node = nodes[idex];
		}
		catch (ex) {
			alert(ex);
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
		showManageFolder();
		rePoint(idx);
	}

	/**
	 * show Folder setting layer
	 **/
	function displaySetPage(idex) {
		var obj = null, node = null, nodes = null;
		var idx = new Array();
		var orgLang = "";

		idx = searchPoint();
		nodes = xmlVars.getElementsByTagName("folder");

		if ((idex < 0) && (typeof(idx) == "object") && (idx.length > 0)) {
			idex = parseInt(idx[0]);
		}

		try {
			if (nodes == null) throw MSG_SYS_ERROR;
			if (idex < 0) throw MSG_SEL_EDIT_NODE;
			if (idex == 0) throw MSG_CANT_EDIT;
		}
		catch (ex) {
			alert(ex);
			return false;
		}

		// select edit Node
		//selectRang(idex, idex);

		// get node name (Begin)
		editIdx = isNB ? idex : idex - 1;
		orgLang = lang;
		for (i = 0; i < langList.length; i++) {
			lang = langList[i];
			obj = document.getElementById("GPName_" + langList[i]);
			if (obj != null) {
				obj.value = old_getCaption(nodes[editIdx]);
			}
		}
		lang = orgLang;
		// get node name (End)

		// show setting Layer
		actionLayer("divSettings", true);
	}
///////////////////////////////////////////////////////////////////////////////
	var indx = 0;
	var col = "bg03";
	function parseManageFolder(node, indent) {
		var nodes = null, tmpNode = null, newNode = null, attr = null;
		var txt = "", txt1 = "", res = "";
		var re = /^sys_/;

		if ((typeof(node) != "object") || (node == null))
			return false;

		if (!node.hasChildNodes()) return false;
		nodes = node.childNodes;
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].nodeType != 1) continue;
			if (nodes[i].nodeName == "folder") {
				col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
				attr = nodes[i].getAttribute("id");
				
				indx++;
				txt = "";

				// indent
				tmpNode = nodes[i].parentNode;
				txt1 = "";
				while ((tmpNode != null) && (tmpNode.tagName != "manifest")) {
					if (chkLast(tmpNode, "folder")) {
						txt1 = '<span style="width: 16px;">&nbsp;&nbsp;</span>' + txt1;
					} else {
						txt1 = '<img src="/theme/' + theme + '/vertline.gif" width="16" height="18" border="0" align="absmiddle">' + txt1;
					}
					tmpNode = tmpNode.parentNode;
				}
				txt += txt1;
				txt += (chkLast(nodes[i], "folder")) ? '<img src="/theme/' + theme + '/lastnode.gif" width="16" height="18" border="0" align="absmiddle">' : '<img src="/theme/' + theme + '/node.gif" width="16" height="18" border="0" align="absmiddle">';

				txt += '<input type="checkbox">';
				if ((attr != null) && re.test(attr)) {
					txt += "" + indx + ".<a href=\"javascript:void(null);\" class=\"cssAnchor\" onclick=\"displaySetPage(" + indx + "); return false;\">" + htmlspecialchars(getCaption(nodes[i])) + "</a><br />";
				} else {
					txt += "" + indx + ".<a href=\"javascript:void(null);\" class=\"cssAnchor\" onclick=\"displaySetPage(" + indx + "); return false;\">" + htmlspecialchars(getCaption(nodes[i])) + "</a><br />";
				}
				res += '<tr class="' + col + '"><td noWrap="noWrap">' + txt + '</td></tr>';
				res += parseManageFolder(nodes[i], indent + 1);
			}
		}
		return res;
	}

	/**
	 * display massage center folder
	 **/
	function showManageMSGFolder() {
		var obj = document.getElementById("Folder");
		var nodes = null;
		var txt = "";

		if ((typeof(obj) != "object") || (obj == null)) return false;

		indx = 0;
		col = "bg03";
		txt  = '<table width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable">';
		txt += '<tr class="cssTrHelp"><td noWrap="noWrap">';
		txt += MSG_HELP;
		txt += '</td></tr>';
		txt += '<tr class="cssTrEvn"><td noWrap="noWrap">';
		txt += '<input type="checkbox" disabled>' + MSG_TITLE;
		txt += '</td></tr>';

		txt += parseManageFolder(xmlVars.documentElement, 0);
		txt += '</table>';

		obj.innerHTML = txt;
	}

	/**
	 * display Notebook folder
	 **/
	function showManageNBFolder() {
		var obj = document.getElementById("Folder");
		var node = null, nodes = null;
		var txt = "";

		if ((typeof(obj) != "object") || (obj == null)) return false;

		node = xmlVars.selectSingleNode('//folder');
		indx = 0;
		col = "bg03";
		txt  = '<table width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable">';
		txt += '<tr class="cssTrHead"><td noWrap="noWrap">';
		txt += MSG_HELP;
		txt += '</td></tr>';
		txt += '<tr class="cssTrEvn"><td noWrap="noWrap">';
		txt += '<input type="checkbox" disabled>' + getCaption(node);
		txt += '</td></tr>';

		txt += parseManageFolder(node, 0);
		txt += '</table>';

		obj.innerHTML = txt;
	}

	function showManageFolder() {
		if (isNB) {
			showManageNBFolder();
		} else {
			showManageMSGFolder();
		}
	}
///////////////////////////////////////////////////////////////////////////////

	function do_func(act, extra) {
		var obj = null, nodes = null, node = null;
		var txt = "";
		var res = 0;

		//alert(act);
		switch (act) {
			case 'list_folder'   :
			case 'manage_folder' :
			case 'man_nb_folder' :
				if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
				if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
				notSave = false;
				txt  = "<manifest>";
				txt += "<ticket>" + ticket + "</ticket>";
				txt += "<action>" + act + "</action>";
				txt += "</manifest>";

				res = xmlVars.loadXML(txt);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				xmlHttp.open("POST", "msg_function.php", false);
				xmlHttp.send(xmlVars);
				// alert(xmlHttp.responseText);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				ticket = getNodeValue(xmlVars.documentElement, "ticket");

				if (act == "list_folder")
					showListFolder();
				else if (act == "manage_folder")
					showManageFolder();
				else if (act == "man_nb_folder") {
					showManageNBFolder();
				}
				break;

			case "folder" : // open folder
				txt  = "<manifest>";
				txt += "<ticket>" + ticket + "</ticket>";
				txt += "<action>" + act + "</action>";
				txt += "<folder_id>" + extra + "</folder_id>";
				txt += "</manifest>";
				res = xmlVars.loadXML(txt);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				xmlHttp.open("POST", "msg_function.php", false);
				xmlHttp.send(xmlVars);
				// alert(xmlHttp.responseText);
				/*
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				ticket = getNodeValue(xmlVars.documentElement, "ticket");
				*/
				obj = getTarget();
				if (typeof(obj.remove_unload) == "function")
					obj.remove_unload();
				obj.location.replace(targetf);
				break;

			case "manage" : // Goto Message Center folder manage system
				notSave = false;
				obj = getTarget();
				if (obj != null) obj.location.replace("msg_manage_folder.php");
				break;

			case "list" : // Goto Message Center
				if (notSave && !confirm(MSG_EXIT))
					return;
				notSave = false;
				this.location.replace(targetf);
				break;

			case "select" : // select node
				actionLayer("divSettings", false);
				selectNode(parseInt(extra));
				break;

			case "selectRang" : // select range
				actionLayer("divSettings", false);
				selectRang(extra[0], extra[1]);
				break;

			case "move" : // move node
				notSave = true;
				actionLayer("divSettings", false);
				moveNode(parseInt(extra));
				break;

			case "add" : // add node
				notSave = true;
				actionLayer("divSettings", false);
				addNode(parseInt(extra));
				break;

			case "delete" : // delete node
				notSave = true;
				actionLayer("divSettings", false);
				delNode(false);
				break;

			case "copy_cut" : // copy or cut node
				notSave = true;
				actionLayer("divSettings", false);
				cpcutNode(parseInt(extra));
				break;

			case "paste" : // paste or cut node
				notSave = true;
				actionLayer("divSettings", false);
				pasteNode();
				break;

			case "set" : // show setting layer
				displaySetPage(-1);
				break;

			case "save" : // save
				if (!notSave) {
					alert(MSG_SAVE_FAIL);
					return false;
				}
				if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
				if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

				node = xmlVars.createElement("action");
				obj = xmlVars.createTextNode("save");
				node.appendChild(obj);
				xmlVars.documentElement.appendChild(node);

				xmlHttp.open("POST", "msg_function.php", false);
				xmlHttp.send(xmlVars);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				ticket = getNodeValue(xmlVars.documentElement, "ticket");
				res = getNodeValue(xmlVars.documentElement, "result");
				notSave = false;

				if (parseInt(res)) {
					alert(MSG_SAVE_SUCCESS);
				} else {
					alert(MSG_SAVE_FAIL);
				}

				if (isNB) {
					do_func("man_nb_folder");
				} else {
					do_func("manage_folder");
				}
				break;
		} // End switch (act)
	}

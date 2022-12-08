	/**
	 * 筆記本 js lib ( 改自訊息中心的共用函式 /message/lib.js )
	 *
	 * 建立日期：2004/07/5
	 * @author  Kuo Yang Tsao
	 * @copyright 2004 SUNNET
	 **/

	var xmlVars = null, xmlHttp = null, xmlClip = null;
	var isIE = false, isMZ = false;
	var langList = new Array("big5", "gb2312", "en", "euc-jp", "user-define");
	var idNum = 1;
	var editIdx = 0;
	var isEdit = false;

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

	/****************
	 處理引號
	 ****************/
	function escape_str(str) {
		str = str.replace(/\\/g,"\\u005C");
		str = str.replace(/"/g,'\\u0022');
		str = str.replace(/'/g,"\\u0027");
		return str;
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
			top.FrameExpand(1, true, '');
		} else {
			obj1.style.display = "block";
			obj2.style.display = "none";
			obj3.style.visibility = "hidden";
			top.FrameExpand(2, false, '20');
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
				icon.src = "/theme/" + theme + "/academic/plus.gif";
				icon.alt = MSG_EXPAND;
				icon.title = MSG_EXPAND;
			}
		} else {
			obj.style.display = "block";
			if (icon != null) {
				icon.src = "/theme/" + theme + "/academic/minus.gif";
				icon.alt = MSG_COLLECT;
				icon.title = MSG_COLLECT;
			}
		}
		if (isIE) event.cancelBubble = true;
		return false;
	}

	function chooseFolder(idx, name) {
		if(confirm(MSG_CHOOSE + MSG_FOLDER + ' [' + name + ']?')) {
			var win =  dialogArguments;
			if(idx=='root')	// 根目錄
				idx = 0;
			win.do_q_folder(name, idx);
			window.close();
		}
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
				txt += '<tr><td nowrap class="cssTbBlur">';
				txt += '<div MyAttr="' + idx + '" id="Div' + idx + '" onmouseover="mouseEvent(this, 1)" onmouseout="mouseEvent(this, 2)" onclick="mouseEvent(this, 3)">';

				// indent
				for (var j = (indent + 1); j > 0 ; j--)
					txt += '<span style="width=15px">&nbsp;&nbsp;</span>';

				childs = nodes[i].getElementsByTagName("folder");
				if ((childs != null) && (childs.length > 0))
					txt += '<img src="/theme/' + theme + '/academic/plus.gif" width="9" height="15" border="0" align="absmiddle" alt="' + MSG_EXPAND + '" title="' + MSG_EXPAND + '" id="Icon' + idx + '" onclick="gpExpand(\'' + idx + '\')">';
				else
					txt += '<img src="/theme/' + theme + '/academic/dot.gif" width="9" height="15" border="0" align="absmiddle">';
				//txt += '<input type="radio" name="actTarget" value="' + idx + '" onClick="alert(this.value);">';

				title = getNodeValue(nodes[i], 'title');
				name = htmlspecialchars(title); // getCaption(nodes[i]));

				txt += '<a href="javascript:void(null);" onClick="chooseFolder(\'' + idx + '\',\''+ escape_str(name) +'\');return false;">';
				txt += name; //htmlspecialchars(getCaption(nodes[i]));
				txt += '</a>';
				txt += '</div>';
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
		node = xmlVars.selectSingleNode("//folder[@id='root']");
		return node;
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
						txt1 = '<img src="/theme/' + theme + '/academic/vertline.gif" width="16" height="18" border="0" align="absmiddle">' + txt1;
					}
					tmpNode = tmpNode.parentNode;
				}
				txt += txt1;
				txt += (chkLast(nodes[i], "folder")) ? '<img src="/theme/' + theme + '/academic/lastnode.gif" width="16" height="18" border="0" align="absmiddle">' : '<img src="/theme/' + theme + '/academic/node.gif" width="16" height="18" border="0" align="absmiddle">';
				title = htmlspecialchars(getNodeValue(nodes[i], 'title'));
				txt += '<input type="checkbox">';
				if ((attr != null) && re.test(attr)) {
					txt += "" + indx + ".<a href=\"javascript:void(null);\" class=\"cssAnchor\" onclick=\"displaySetPage(" + indx + "); return false;\">" + title + "</a><br />";
				} else {
					txt += "" + indx + ".<a href=\"javascript:void(null);\" class=\"cssAnchor\" onclick=\"displaySetPage(" + indx + "); return false;\">" + title + "</a><br />";
				}
				res += '<tr class="' + col + '"><td noWrap="noWrap">' + txt + '</td></tr>';
				res += parseManageFolder(nodes[i], indent + 1);
			}
		}
		return res;
	}

///////////////////////////////////////////////////////////////////////////////

	function do_func(act, extra) {
		var obj = null, nodes = null, node = null;
		var txt = "";
		var res = 0;

		switch (act) {
			case 'list_folder'   :
				if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
				if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
				isEdit = false;
				txt  = "<manifest>";
				txt += "<action>" + act + "</action>";
				txt += "</manifest>";

				res = xmlVars.loadXML(txt);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				xmlHttp.open("POST", "/forum/q_folder.php", false);
				xmlHttp.send(xmlVars);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					alert("Here:" + MSG_SYS_ERROR);
					return false;
				}
				ticket = getNodeValue(xmlVars.documentElement, "ticket");

				if (act == "list_folder")
					showListFolder();
				break;
		} // End switch (act)
	}

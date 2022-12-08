	/**
	 * 訊息中心的共用函式
	 *
	 * 建立日期：2003/04/22
	 * @author  ShenTing Lin
	 * @version $Id: manage_folder.js,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright 2003 SUNNET
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
		if (!state) editIdx = 0;
		layerAction(objName, state);
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
		while((cur != null) && (cur.tagName == tag)) {
			newNode = cur.cloneNode(true);
			node.appendChild(newNode);
			node.parentNode.removeChild(cur);
			cur = node.nextSibling;
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
			if (parseInt(val) == 3) do_func("courses", idx);
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
				if (parseInt(val) == 3) do_func("courses", idx);
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
	 * 重新點選 checkbox 的位置
	 **/
	function rePoint(idx) {
		var obj = null, nodes = null, childs = null, attr = null, res = null;
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
		res = parseInt(idx[0]);
		if (isNaN(res)) {
			childs = xmlVars.getElementsByTagName('courses');
			for (var i = 0; i < childs.length; i++) {
				attr = childs[i].getAttribute("id");
				for (var j = 0; j < cnt; j++) {
					if (idx[j] == "") continue;
					if (attr == idx[j]) {
						nodes[i + 1].checked = true;
						idx[j] = "";
					}
				}
			}
		} else {
			for (var i = 0; i < cnt; i++) {
				if (idx[i] == 0) continue;
				nodes[idx[i]].checked = true;
			}
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
			else if (val == "courses") {
				attr = pNode.getAttribute("id");
				if ((attr != null) && (attr == "sys_trash")) return 1;
			}

			pNode = pNode.parentNode;
		}
		return 0;
	}

	function getSysTrash() {
		var nodes = null, attr = null;

		if (xmlVars == null) return null;
		nodes = xmlVars.documentElement.getElementsByTagName("courses");
		if ((nodes == null) || (nodes.length <= 0)) return null;
		for (var i = nodes.length - 1; i > 0; i--) {
			attr = nodes[i].getAttribute("id");
			if ((attr != null) && (attr == "sys_trash")) return nodes[i];
		}
		return null;
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
		var nodes = null, childs = null, pNode = null;
		var idx = new Array(), newIdx = new Array();
		var cnt = 0, idex = 0;

		try {
			idx = searchPoint();
			if ((typeof(idx) != "object") || (idx.length <= 0))
				throw MSG_SEL_MOVE_NODE;

			if (idx[0] == 0)
				throw MSG_CANT_MOVE;
			cnt = idx.length;

			nodes = xmlVars.getElementsByTagName('courses');
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
					idex = parseInt(idx[i]) - 1;
	
					prevNode = getPrevNode(nodes[idex], "courses");
					if (prevNode != null) {
						newIdx[newIdx.length] = nodes[idex].getAttribute("id");
						
						swapNode(nodes[idex], prevNode);

						childs = prevNode.getElementsByTagName("courses");
						idx[i] = idx[i] - 1 - childs.length;
					} else {
						alert(MSG_FOLDER + ' [' + getCaption(nodes[idex]) + '] ' + MSG_CANT_FORWARD);
					}
				}
				break;

			case 2 :    // move down
				for (var i = (cnt - 1); i >= 0; i--) {
					if (idx[i] == 0) continue;
					idex = parseInt(idx[i]) - 1;
	
					nextNode = getNextNode(nodes[idex], "courses");
					if (nextNode != null) {
						newIdx[newIdx.length] = nodes[idex].getAttribute("id");
						
						swapNode(nodes[idex], nextNode);

						childs = nextNode.getElementsByTagName("courses");
						idx[i] = idx[i] + 1 + childs.length;
					} else {
						alert(MSG_FOLDER + ' [' + getCaption(nodes[idex]) + '] ' + MSG_CANT_BACKWARD);
					}
				}
				break;

			case 3 :    // move left
				for (var i = (cnt - 1); i >= 0; i--) {
					if (idx[i] == 0) continue;
					idex = parseInt(idx[i]) - 1;

					if (nodes[idex].parentNode.tagName == 'courses'){
						newIdx[newIdx.length] = nodes[idex].getAttribute("id");
						
						Brother2child(nodes[idex]);
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
					if (idx[i] == 0) continue;
					idex = parseInt(idx[i]) - 1;
					
					prevNode = getPrevNode(nodes[idex], "courses");
					if (prevNode != null) {
						newIdx[newIdx.length] = nodes[idex].getAttribute("id");

						newNode = nodes[idex].cloneNode(true);
						nodes[idex].parentNode.removeChild(nodes[idex]);
						prevNode.appendChild(newNode);
						Child2Brother(newNode, "courses");
					} else {
						alert(MSG_FOLDER + ' [' + getCaption(nodes[idex]) + '] ' + MSG_CANT_INDENT);
					}
				}
				break;
			default:
		}

		showManageFolder();
		// rePoint(idx);
		rePoint(newIdx);
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

		node = xmlVars.createElement("courses");

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
		var cnt = 0;
		var idx = new Array();

		idx = searchPoint();
		nodes = xmlVars.getElementsByTagName('courses');
		cnt = idx.length;

		if ((cnt == 0) || (idx[0] == 0) || (nodes == null)) {
			// add on Root
			newNode = createNode('undefined');
			xmlVars.documentElement.appendChild(newNode);
		} else {
			node = nodes[parseInt(idx[0]) - 1];
			newNode = createNode('undefined');
			if (isChild) {
				node.appendChild(newNode);
				// add child node happen some error when repoint
			} else {
				node.parentNode.insertBefore(newNode, node);
			}
			for (var i = 0; i < cnt; i++) {
				if (isChild && (i == 0)) continue;
				idx[i]++;
			}
		}

		showManageFolder();
		rePoint(idx);
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

			nodes = xmlVars.getElementsByTagName("courses");
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
		isEdit = true;
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
		nodes = xmlVars.getElementsByTagName('courses');
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
			indx = parseInt(idx[i]) - 1;
			attr = nodes[indx].getAttribute("id");
			if ((attr != null) && re.test(attr)) {
				alert("[" + getCaption(nodes[indx]) + "] " + MSG_SYS_CANT_DEL);
				continue;
			}


			if (!act) {
				if (!confirm("[" + getCaption(nodes[indx]) + "] " + MSG_CONFIRM_DEL)) continue;
			}

			childs = nodes[indx].getElementsByTagName("courses");
			if ((childs != null) && (childs.length > 0)) {
				Brother2child(childs[0], "courses");
				node = childs[0].cloneNode(true);
				nodes[indx].parentNode.replaceChild(node,nodes[indx]);
			} else {
				nodes[indx].parentNode.removeChild(nodes[indx]);
			}
		}
		if (act) alert(MSG_ALREADY_CUT);

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

			nodes = xmlVars.getElementsByTagName('courses');
			if (nodes == null) throw MSG_SYS_ERROR;
		}
		catch (ex) {
			alert(ex);
			return false;
		}

		for (var i = 0; i < cnt; i++) {
			if (idx[i] == 0) continue;
			idex = parseInt(idx[i]) - 1;
			node = nodes[idex].cloneNode(true);
			//node.removeAttribute("id");

			childs = nodes[idex].childNodes;
			for (var j = childs.length - 1; j > 0 ; j--) {
				if ((childs[j].nodeType == 1) && (childs[j].nodeName == "courses")) {
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
			idex = parseInt(idx[0]) - 1;

			nodes = xmlVars.getElementsByTagName('courses');
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
		nodes = xmlVars.getElementsByTagName("courses");

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
		editIdx = idex - 1;
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
			if (nodes[i].nodeName == "courses") {
				col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
				attr = nodes[i].getAttribute("id");
				
				indx++;
				txt = "";

				// indent
				tmpNode = nodes[i].parentNode;
				txt1 = "";
				while ((tmpNode != null) && (tmpNode.tagName != "manifest")) {
					if (chkLast(tmpNode, "courses")) {
						txt1 = '<span style="width: 16px;">&nbsp;&nbsp;</span>' + txt1;
					} else {
						txt1 = '<img src="/theme/' + theme + '/academic/vertline.gif" width="16" height="18" border="0" align="absmiddle">' + txt1;
					}
					tmpNode = tmpNode.parentNode;
				}
				txt += txt1;
				txt += (chkLast(nodes[i], "courses")) ? '<img src="/theme/' + theme + '/academic/lastnode.gif" width="16" height="18" border="0" align="absmiddle">' : '<img src="/theme/' + theme + '/academic/node.gif" width="16" height="18" border="0" align="absmiddle">';

				txt += '<input type="checkbox" value="' + attr + '">';
				if ((attr != null) && re.test(attr)) {
					txt += "" + indx + ".<a href=\"javascript:void(null);\" class=\"cssAnchor\" onclick=\"displaySetPage(" + indx + "); return false;\">" + getCaption(nodes[i]) + "</a><br />";
				} else {
					txt += "" + indx + ".<a href=\"javascript:void(null);\" class=\"cssAnchor\" onclick=\"displaySetPage(" + indx + "); return false;\">" + getCaption(nodes[i]) + "</a><br />";
				}
				res += '<tr class="' + col + '"><td noWrap="noWrap">' + txt + '</td></tr>';
				res += parseManageFolder(nodes[i], indent + 1);
			}
		}
		return res;
	}

	/**
	 * display massage center folder
	 * @return
	 **/
	function showManageFolder() {
		var obj = document.getElementById("Folder");
		var nodes = null;
		var txt = "";

		if ((typeof(obj) != "object") || (obj == null)) return false;

		indx = 0;
		col = "cssTrEvn";
		txt  = '<table width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable">';
		txt += '<tr class="cssTrHead"><td noWrap="noWrap">';
		txt += MSG_HELP;
		txt += '</td></tr>';
		txt += '<tr class="cssTrEvn"><td noWrap="noWrap">';
		txt += '<input type="checkbox" disabled>' + MSG_TITLE;
		txt += '</td></tr>';

		txt += parseManageFolder(xmlVars.documentElement, 0);
		txt += '</table>';

		obj.innerHTML = txt;
	}

///////////////////////////////////////////////////////////////////////////////

	function do_func(act, extra) {
		var obj = null, nodes = null, node = null;
		var txt = "";
		var res = 0;

		//alert(act);
		switch (act) {
			case 'manage_folder' :
				if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
				if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
				isEdit = false;
				txt  = "<manifest>";
				txt += "<ticket>" + ticket + "</ticket>";
				txt += "<action>" + act + "</action>";
				txt += "</manifest>";

				res = xmlVars.loadXML(txt);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				xmlHttp.open("POST", "do_function.php", false);
				xmlHttp.send(xmlVars);
				//alert(xmlHttp.responseText);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				ticket = getNodeValue(xmlVars.documentElement, "ticket");
				showManageFolder();
				break;

			case "list" : // Goto Message Center
				if (isEdit && confirm(MSG_CONFIRM_SAVE)) {
					do_func("save", "");
				}
				isEdit = false;
				this.location.replace("index.php");
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
				isEdit = true;
				actionLayer("divSettings", false);
				moveNode(parseInt(extra));
				break;

			case "add" : // add node
				isEdit = true;
				actionLayer("divSettings", false);
				addNode(parseInt(extra));
				break;

			case "delete" : // delete node
				isEdit = true;
				actionLayer("divSettings", false);
				delNode(false);
				break;

			case "copy_cut" : // copy or cut node
				isEdit = true;
				actionLayer("divSettings", false);
				cpcutNode(parseInt(extra));
				break;

			case "paste" : // paste or cut node
				isEdit = true;
				actionLayer("divSettings", false);
				pasteNode();
				break;

			case "set" : // show setting layer
				displaySetPage(-1);
				break;

			case "save" : // save
				if (!isEdit) {
					alert(MSG_SAVE_FAIL);
					return false;
				}
				if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
				if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

				node = xmlVars.createElement("action");
				obj = xmlVars.createTextNode("save");
				node.appendChild(obj);
				xmlVars.documentElement.appendChild(node);

				xmlHttp.open("POST", "do_function.php", false);
				xmlHttp.send(xmlVars);
				//alert(xmlHttp.responseText);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				ticket = getNodeValue(xmlVars.documentElement, "ticket");
				res = getNodeValue(xmlVars.documentElement, "result");
				isEdit = false;

				if (parseInt(res)) {
					alert(MSG_SAVE_SUCCESS);
				} else {
					alert(MSG_SAVE_FAIL);
				}

				do_func("manage_folder");
				break;
		} // End switch (act)
	}

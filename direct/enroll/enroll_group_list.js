	/**
	 * 課程群組
	 *
	 * @since   2004/07/16
	 * @author  ShenTing Lin
	 * @version $Id: enroll_group_list.js,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	var isIE = false, isMZ = false;
	var BVER = "5.0";
	var bodyHeight = 0, bodyWidth = 0;
	var xmlVars = null, xmlHttp = null, xmlDocs = null;
	var lstData = new Object();

	/**
	 * 檢查瀏覽器 (Check Browser)
	 * @version 1.1
	 * @return
	 **/
	function chkBrowser() {
		var re = new RegExp("MSIE", "ig");
		if (re.test(navigator.userAgent)) {
			isIE = true;
			re = new RegExp("MSIE 5.0", "ig");
			if (re.test(navigator.userAgent)) BVER = "5.0";
			re = new RegExp("MSIE 5.5", "ig");
			if (re.test(navigator.userAgent)) BVER = "5.5";
			re = new RegExp("MSIE 6.0", "ig");
			if (re.test(navigator.userAgent)) BVER = "6.0";
		}

		re = new RegExp("Gecko", "ig");
		if (re.test(navigator.userAgent)) {
			isMZ = true;
		}
	}

	/**
	 * 轉換 Html 的特殊字
	 * @param string str : 要轉換的字串 (want trans string)
	 * @return string : 轉換後的字串 (transed string)
	 **/
	function htmlspecialchars(str) {
		var txt = "";
		if (str == "") return txt;
		txt = str.replace(/&/ig, "&amp;");
		txt = txt.replace(/</ig, "&lt;");
		txt = txt.replace(/>/ig, "&gt;");
		txt = txt.replace(/'/ig, "&#039;");
		txt = txt.replace(/"/ig, "&quot;");
		return txt;
	}

	/**
	 * 取得另外的視窗
	 * @return Object 另外的視窗 (other frame)
	 **/
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
// ////////////////////////////////////////////////////////////////////////////
	/**
	 * 取得節點的值 (get node value)
	 * @param object node : 要從哪個節點中取值 (target node)
	 * @param string tag  : 節點中的哪個 tag (child nodes tagname)
	 * @return string result : 取得的值。若無法取值，則回傳空字串 (get node value, if can not read return empty string)
	 **/
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

	var expandStatus = true;
	/**
	 * 顯示或隱藏 Frame (show or hidden frame)
	 **/
	function winExpand(val) {
		var obj1 = document.getElementById("IconExpand");
		var obj2 = document.getElementById("IconCollection");
		var obj3 = document.getElementById("ToolBar");

		expandStatus = val;
		if (obj1 != null) obj1.style.display = val ? "none" : "";
		if (obj2 != null) obj2.style.display = val ? "" : "none";
		if (obj3 != null) obj3.style.visibility = val ? "visible" : "hidden";
		if (val) {
			parent.FrameExpand(1, true, '');
		} else {
			parent.FrameExpand(2, false, '20');
		}
		return false;
	}
// ////////////////////////////////////////////////////////////////////////////
	var ERROR_CNT = 0;
	function getGroupList(gid) {
		var txt = "";
		var res = true;

		txt  = "<manifest>";
		txt += "<ticket>" + ticket + "</ticket>";
		txt += "<group_id>" + gid + "</group_id>";
		txt += "</manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) return false;

		xmlHttp.open("POST", "course_group_get.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		res = xmlDocs.loadXML(xmlHttp.responseText);
		if (!res) {
			ERROR_CNT++;
			if (ERROR_CNT > 5) {
				alert(MSG_SYS_ERROR);
				return false;
			} else {
				getGroupList(gid);
				ERROR_CNT = 0;
			}
		}
		ticket = getNodeValue(xmlDocs.documentElement, "ticket");
	}

	function expandGroup(gid, indent) {
		var obj = document.getElementById("Group" + gid);
		var icon = document.getElementById("Icon" + gid);
		var attr = null;

		if (obj == null) return false;
		if (obj.style.display == "") {
			obj.style.display = "none";
			if (icon != null) {
				icon.src = "/theme/" + theme + "/direct/plus.gif";
				icon.alt = MSG_EXPAND;
				icon.title = MSG_EXPAND;
			}
		} else {
			if (icon != null) {
				icon.src = "/theme/" + theme + "/direct/minus.gif";
				icon.alt = MSG_COLLECT;
				icon.title = MSG_COLLECT;
			}
			obj.style.display = "";
			attr = obj.getAttribute("MyAttr");
			if ((attr != null) && (attr == "false")) {
				obj.innerHTML = '<span class="cssTbBlur">' + MSG_LOADING + '</span>';
				txt = buildGroup(gid, parseInt(indent));
				if (txt == "") {
					obj.style.display = "none";
					if (icon != null) {
						icon.src = "/theme/" + theme + "/direct/dot.gif";
						icon.alt = MSG_EXPAND;
						icon.title = MSG_EXPAND;
					}
				} else {
					obj.setAttribute("MyAttr", "true");
				}
				obj.innerHTML = txt;
				ERROR_CNT = 0;
			}
		}
	}

	/**
	 * 建立子課程群組 (create sub group)
	 * @param string  gid   : 課程群組的編號 (group id)
	 * @param integer indent: 層級 (indent)
	 * @return string result: 呈現課程目錄的結果 (group tree html result)
	 **/
	function buildGroup(gid, indent) {
		var obj = null, nodes = null, childs = null;
		var cnt = 0, idx = 0;
		var txt = "";

		getGroupList(gid);
		if ((ERROR_CNT > 0) || (typeof(xmlDocs) != "object") || (xmlDocs == null)) return "";
		nodes = xmlDocs.getElementsByTagName("courses");
		cnt = nodes.length;
		for (var i = 0; i < cnt; i++) {
			if (nodes[i].nodeType != 1) continue;
			idx = nodes[i].getAttribute("id");

			txt += '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
			txt += '<tr><td nowrap>';
			// ident
			for (var j = (indent + 1); j > 0 ; j--)
				txt += '<pre style="width: 15px; display: inline;">&nbsp;&nbsp;</pre>';
			// icon
			childs = nodes[i].getAttribute("childs");
			if (parseInt(childs) > 0) {
				txt += '<img src="/theme/' + theme + '/direct/plus.gif" width="9" height="15" border="0" align="absmiddle" alt="' + MSG_EXPAND + '" title="' + MSG_EXPAND + '" id="Icon' + idx + '" onclick="expandGroup(\'' + idx + '\', ' + (indent + 1) + ')">';
			} else {
				txt += '<img src="/theme/' + theme + '/direct/dot.gif" width="9" height="15" border="0" align="absmiddle">';
			}
			txt += '<span class="cssTbBlur" Mimura="true" MyAttr="' + idx + '" id="Div' + idx + '">';
			txt += getNodeValue(nodes[i], "title");
			txt += '</span>';
			txt += '<div id="Group' + idx + '" MyAttr="false" style="display:none"></div>';
			txt += '</td></tr>';
			txt += '</table>';
		}
		if ((gid == '0') || (gid == 0)) {
			obj = document.getElementById("CGroup");
			if (obj != null) obj.innerHTML = txt;
		}
		return txt;
	}

	function restoreTree() {
		var obj = null, evnt = null;

		if ((typeof(plst) != "object") || (plst == null)) return false;
		for (var i = 0; i < plst.length; i++) {
			expandGroup(plst[i], i + 1);
		}

		obj = document.getElementById("Div" + ids);
		if (obj != null) {
			evnt = new Object();
			evnt['button'] = 0;
			evnt['target'] = obj;
			evnt['srcElement'] = obj;
			mouseEvent(evnt, "cssTbFocus");
			preIdx = ids;
		}
	}
// ////////////////////////////////////////////////////////////////////////////
	var preIdx = "";
	function mouseEvent(evnt, val) {
		var obj = null, node = null;
		var attr = null;
		var idx = "", tag = "";
		if ((isMZ) || (typeof(evnt) == "object")) {
			if (parseInt(evnt.button) > 0) return true;
			obj = evnt.target;
		} else {
			obj = event.srcElement;
		}
		if (obj == null) return false;
		while ((tag = obj.tagName.toLowerCase()) != "body") {
			attr = obj.getAttribute("Mimura");
			if ((tag == "span") && (attr != null) && (attr = "true")) {
				idx = obj.getAttribute("MyAttr");
				if (preIdx != idx) {
					obj.className = val;
				}
				return true;
			}
			obj = obj.parentNode;
			while (obj.nodeType != 1) {
				obj = obj.parentNode;
				if (obj == null) break;
			}
			if (obj == null) break;
		}
	}

	document.onmouseover = function (evnt) {
		mouseEvent(evnt, "cssTbFocus");
	};

	document.onmouseout = function (evnt) {
		mouseEvent(evnt, "cssTbBlur");
	};

	document.onclick = function (evnt) {
		var obj = null, node = null, attr = null;
		var idx = "", tag = "";
		if (isMZ) {
			if (parseInt(evnt.button) > 0) return true;
			obj = evnt.target;
			if (evnt.target.tagName.toLowerCase() == "img") return true;
		} else {
			if (event.srcElement.tagName.toLowerCase() == "img") return true;
			obj = event.srcElement;
		}
		if (obj == null) return false;
		while ((tag = obj.tagName.toLowerCase()) != "body") {
			attr = obj.getAttribute("Mimura");
			if ((attr != null) && (attr = "true")) {
				idx = obj.getAttribute("MyAttr");
				if (idx != preIdx) {
					obj = getTarget();
					if (obj != null) {
						node = obj.document.getElementById("actFm");
						if (node != null) {
							node.ticket.value = idx;
							node.page.value = 1;
							// node.submit();
						}
						obj.rmUnload('other');
					}

					node = document.getElementById("Div" + preIdx);
					if (node != null) node.className = "cssTbBlur";
					preIdx = idx;
				}
				return true;
			}
			obj = obj.parentNode;
			while (obj.nodeType != 1) {
				obj = obj.parentNode;
				if (obj == null) break;
			}
			if (obj == null) break;
		}
	};
// ////////////////////////////////////////////////////////////////////////////
	window.onresize = function () {
		var obj = document.getElementById("ToolBar");
		if (obj == null) return false;
		bodyHeight = (isIE) ? document.body.clientHeight : window.innerHeight;
		bodyHeight = Math.max(parseInt(bodyHeight) - 30, 0);
		bodyWidth  = (isIE) ? document.body.clientWidth : window.innerWidth;
		bodyWidth  = Math.max(parseInt(bodyWidth) - 12, 0);

		obj.style.height = parseInt(bodyHeight);
		if (parseInt(bodyWidth) <= 30) {
			bodyWidth = 20;
			winExpand(false);
		}
		obj.style.width = bodyWidth;
		obj.firstChild.style.width = bodyWidth;
	};

	window.onload = function () {
		var obj = null, fm = null;
		chkBrowser();
		parent.FrameExpand(1, true, '');
		document.body.scroll = "no"; // 關閉 IE 的 scrollbar

		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		buildGroup(0, 0);
		restoreTree();
		/*
		obj = getTarget();
		if (obj != null) {
			obj.rmUnload();
			fm = obj.document.getElementById("actFm");
			if (fm != null) {
				fm.ticket.value = ids;
				fm.page.value = 1;
				fm.submit();
			}
		}
		*/
	};

	window.onunload = function () {
		window.onresize = function () {};
		document.body.scroll = "no";
		parent.FrameExpand(0, false, '');
	};

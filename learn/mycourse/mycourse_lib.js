	/**
	 * 共用函數
	 *
	 * @since   2004/09/10
	 * @author  ShenTing Lin
	 * @version $Id: mycourse_lib.js,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	var isIE = false, isMZ = false;
	var BVER = "5.0";
	var isDrag = false;
	var dragID = "", curObjID = "";
	var defWidth = 750, defLSize = 200, defRSize = 525, curSize = 200;
	var DOLeft = 0, DOTop = 0;
	var orgFunc = null;
	var aryPst = null;
	var xmlDocs = null, xmlVars = null, xmlHttp = null;

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

	function do_func(act, extra) {
		var res = false;
		var txt = "";
		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		txt  = "<manifest>";
		txt += "<action>" + act + "</action>";
		txt += extra;
		txt += "</manifest>";
		res = xmlVars.loadXML(txt);
		xmlHttp.open("POST", "mycourse_func.php", false);
		xmlHttp.send(xmlVars);
		if (xmlHttp.responseText != "") {
			alert(xmlHttp.responseText);
		}
	}

	function closeMyTitle(objID) {
		var obj = document.getElementById(objID);
		if (obj == null) return false;
		do_func("close", "<curid>" + objID + "</curid>")
		obj.parentNode.removeChild(obj);
	}

	function resizeMyTitle() {
		var nodes = null;
		var isSmall = false;
		var txt = "";
		var wd = 0;
		txt += "var resEval = false;\n";
		// txt += "alert(typeof(mod_" + dragID + "_resize));\n";
		txt += "if (typeof(mod_" + dragID + "_resize) == \"function\") {\n";
		txt += "    resEval = true;\n";
		txt += "    mod_" + dragID + "_resize();\n";
		txt += "}\n";
		eval(txt);
		if (resEval == false) return false;

		isSmall = (parseInt(curSize) <= defLSize);
		// 標題的 resize
		obj = document.getElementById(dragID);
		if (obj != null) {
			wd = isSmall ? (parseInt(defLSize) - 10) : parseInt(defRSize) - 10;
			obj.style.width = wd + "px";
			obj.width = wd;
			nodes = obj.rows[0].getElementsByTagName("div");
			for (var j = 0; j < nodes.length; j++) {
				nodes[j].style.width = (parseInt(wd) - 40) + "px";
			}
		}
		// eval("mod_" + dragID + "_resize()");
	}

	function getDragObj() {
		var obj = null;
		// 取得拖曳的物件，假如不存在就建立一個 (get drag object, if not exist, create it)
		obj = document.getElementById("divBox");
		if (obj == null) {
			obj = document.createElement('div');
			obj.setAttribute("id", "divBox");
			obj.innerHTML = "&nbsp;";
			if (isIE) {
				obj.style.width    = "100px";
				obj.style.height   = "100px";
				obj.style.border   = "0px none #FF0000";
				obj.style.display  = "none";
				obj.style.position = "absolute";
				obj.style.zIndex   = "10";
			} else {
				obj.setAttribute("style", "width: 100px; height: 100px; border: 0px none #FF0000; display: none; position: absolute; z-index: 0;");
			}
			document.body.appendChild(obj);
		}
		return obj;
	}

	function getAbsolutePos(el) {
		var SL = 0, ST = 0;
		var is_div = /^(div|table)$/i.test(el.tagName);
		if (is_div && el.scrollLeft) SL = el.scrollLeft;
		if (is_div && el.scrollTop) ST = el.scrollTop;
		var r = {
			x : el.offsetLeft - SL,
			y : el.offsetTop - ST
		};
		if (el.offsetParent) {
			var tmp = this.getAbsolutePos(el.offsetParent);
			r.x += tmp.x;
			r.y += tmp.y;
		}
		return r;
	}

	document.onmousedown = function (evnt) {
		var obj = null, node = null, attr = null, pst = null;
		var OL = 2, OT = 2, OW = 0, OH = 10;

		if (isIE) evnt = event;
		node = (isIE) ? event.srcElement : evnt.target;
		if (node.tagName.toLowerCase() == "img") return false;
		attr = node.getAttribute("myAttr");
		while (node.nodeType == 1) {
			if (node.tagName.toLowerCase() == "body") {
				return false;
			}
			if (attr == "drag") break;
			node = node.parentNode;
			if (node == null) return false;
			if ((typeof(node.getAttribute) == "function") || (typeof(node.getAttribute) == "object")) {
				attr = node.getAttribute("myAttr");
			}
		}
		if ((node == null) || (node.parentNode == null)) return false;
		// 修正顯示上的誤差
		if (isIE) {
			OL = 0; OT = 0; OW = 0; OH = -11;
		} else {
			OL = 0; OT = 0; OW = -6; OH = -17;
		}
		node = node.parentNode.parentNode;
		pst  = getAbsolutePos(node);
		obj  = getDragObj();
		obj.style.width   = node.offsetWidth  + OW;
		obj.style.height  = node.offsetHeight + OH;
		obj.style.left    = pst.x + OL;
		obj.style.top     = pst.y + OT;
		// obj.style.border  = "3px solid #FF0000";

		var txt = '<table border="0" cellspacing="0" cellpadding="0" style="-moz-opacity: 0.7;filter:Alpha(opacity=70);" ';
		attr = node.getAttribute("width");
		if (attr)
			txt += 'width="' + attr + '">';
		else
			txt += 'width="190">';
		txt += node.innerHTML + '</table>';
		obj.innerHTML = txt;
		if (isIE)
			node.style.filter = "Alpha(opacity=20)";
		else
			node.setAttribute("style", "-moz-opacity: 0.2;");

		obj.style.display = "";
		DOLeft = parseInt(evnt.clientX) - (pst.x + OL);
		DOTop  = parseInt(evnt.clientY) - (pst.y + OT);
		dragID = node.id;
		isDrag = true;

		orgFunc = {
			"onselectstart" : document.onselectstart
		};
		document.onselectstart = function () {
			return false;
		};
		var nodes = document.getElementsByTagName("div");
		aryPst = new Array();
		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("myAttr");
			if ((attr == null) || (attr != "pst")) continue;
			pst = getAbsolutePos(nodes[i]);
			pst.x -= parseInt(document.body.scrollLeft);
			pst.y -= parseInt(document.body.scrollTop);
			aryPst[aryPst.length] = [nodes[i].id, pst.x, pst.y, nodes[i].offsetWidth + pst.x, nodes[i].offsetHeight + pst.y];
			// nodes = document.getElementsByTagName("div");
		}
		return false;
	};

	document.onmouseup = function (evnt) {
		if (!isDrag) return false;
		var obj = document.getElementById("divBox");
		if ((typeof(obj) == "object") && (obj != null)) {
			obj.style.display = "none";
		}
		isDrag = false;

		var obj1 = null, obj2 = null, node1 = null, node2 = null;
		obj1 = document.getElementById(dragID);
		if (obj1 != null) {
			if (isIE)
				obj1.style.filter = "";
			else
				obj1.setAttribute("style", "");
		} else {
			dragID = ""; curObjID = "";
			return false;
		}

		if ((dragID == "") || (curObjID == "")) return false;
		if (dragID == curObjID) {
			dragID   = ""; curObjID = "";
			return false;
		}

		obj2 = document.getElementById(curObjID);
		if (obj2 == null) {
			dragID = ""; curObjID = "";
			return false;
		}
		node1 = obj1.cloneNode(true);
		node2 = obj2.nextSibling;
		node2.parentNode.insertBefore(node1, node2);
		obj1.parentNode.removeChild(obj1); // 移除舊的資料 (remove old data)
		resizeMyTitle();
		do_func("post", "<curid>" + curObjID + "</curid><dragid>" + dragID + "</dragid>");

		dragID = ""; curObjID = "";
		document.onselectstart = orgFunc["onselectstart"];
		for (var i = 0; i < aryPst.length; i++) {
			node = document.getElementById(aryPst[i][0]);
			if (node == null) continue;
			node.style.visibility = "hidden";
		}
		return false;
	};

	document.onmousemove = function (evnt) {
		var obj = null, nodes = null;
		if (!isDrag) return false;
		if (isIE) evnt = event;
		obj = getDragObj();
		obj.style.top  = parseInt(evnt.clientY) - DOTop;
		obj.style.left = parseInt(evnt.clientX) - DOLeft;
		// obj.innerHTML = "";

		var CX = false, CY = false;
		var node = null;
		var isTg = false;
		for (var i = 0; i < aryPst.length; i++) {
			CX = ((parseInt(aryPst[i][1]) <= parseInt(evnt.clientX)) && (parseInt(evnt.clientX) <= parseInt(aryPst[i][3])));
			CY = ((parseInt(aryPst[i][2]) <= parseInt(evnt.clientY)) && (parseInt(evnt.clientY) <= parseInt(aryPst[i][4])));
			node = document.getElementById(aryPst[i][0]);
			// obj.innerHTML += aryPst[i] + "<br>";
			if (node == null) continue;
			if (CX && CY) {
				curObjID = node.getAttribute("modID");
				if (dragID != curObjID) {
					curSize = parseInt(node.offsetWidth);
					node.style.visibility = "visible";
					isTg = true;
				} else {
					node.style.visibility = "hidden";
				}
			} else {
				node.style.visibility = "hidden";
			}
		}
		if (isTg) {
			// obj.style.border = "0px solid #00FF00";
		} else {
			// obj.style.border = "0px solid #FF0000";
			curObjID = "";
			curSize = defLSize;
		}
		// return false;
	};

	chkBrowser();

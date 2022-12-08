	/**
	 * 拖曳 Layer 的函式
	 * 呼叫介面：dragLayer(objName, hL, hT, hW, hH)
	 *     objName：物件的名稱
	 *     hL, hT, hW, hH：界定可以拖曳的範圍，分別是 Left, Top, Width, Height
	 *         hL, hT：範圍的起點
	 *         hW, hH：範圍的大小
	 *         其中要注意的是，當 hW 與 hH 兩者其中一個值為 0 時，
	 *         則表示整個 Layer 皆可拖曳。
	 *
	 * @author: lst
	 * @version: $Id: dragLayer.js,v 1.1 2010/02/24 02:39:36 saly Exp $
	 **/

	var isIE = false, isMZ = false;
	var BVER = "5.0";
	var dragEvnt = null;
	var dragapproved = false;
	var dragObj = null;
	var dragOrgX, dragOrgY, mouseOrgX, mouseOrgY;
	var orgSelectStart = null;

	function chkBrowser() {
		var re = new RegExp("MSIE","ig");
		if (re.test(navigator.userAgent)) {
			isIE = true;
			re = new RegExp("MSIE 5.0","ig");
			if (re.test(navigator.userAgent)) BVER = "5.0";
			re = new RegExp("MSIE 5.5","ig");
			if (re.test(navigator.userAgent)) BVER = "5.5";
			re = new RegExp("MSIE 6.0","ig");
			if (re.test(navigator.userAgent)) BVER = "6.0";
		}

		re = new RegExp("Gecko","ig");
		if (re.test(navigator.userAgent)) {
			isMZ = true;
		}
	}

	function layerMove(evnt) {
		var tmpX, tmpY;
		if (isIE) evnt = event;
		tmpX = dragOrgX + evnt.clientX - mouseOrgX;
		tmpY = dragOrgY + evnt.clientY - mouseOrgY;

		hideShowCovered();

		if (isIE && (evnt.button == 1) && dragapproved) {
			dragObj.style.pixelLeft = tmpX;
			dragObj.style.pixelTop =  tmpY;
		}

		if (isMZ && (evnt.button == 0) && dragapproved) {
			dragObj.style.left = tmpX + "px";
			dragObj.style.top  = tmpY + "px";
		}
	}

	function myDrags(evnt) {
		var mouseX = 0, mouseY = 0;
		var hL, hT, hW, hH;
		if (dragObj == null) return false;
		if (!isIE && !isMZ) return false;

		hL = dragObj.getAttribute("WM_hL");
		hT = dragObj.getAttribute("WM_hT");
		hW = dragObj.getAttribute("WM_hW");
		hH = dragObj.getAttribute("WM_hH");

		if (isIE) evnt = event;
		mouseX = evnt.clientX + document.body.scrollLeft - ((isMZ) ? parseInt(evnt.currentTarget.style.left) : dragObj.style.pixelLeft);
		mouseY = evnt.clientY + document.body.scrollTop - ((isMZ) ? parseInt(evnt.currentTarget.style.top)  : dragObj.style.pixelTop);

		if ((hW != 0) && (hH != 0)) {
			if ( (mouseX < hL) || (mouseY < hT) ||
				(mouseX > (hL + hW) ) || (mouseY > (hT + hH) ) )
			{
				return false
			}
		}

		dragapproved = true;
		dragOrgX  = (isMZ) ? parseInt(dragObj.style.left) : dragObj.style.pixelLeft;
		dragOrgY  = (isMZ) ? parseInt(dragObj.style.top)  : dragObj.style.pixelTop;
		mouseOrgX = evnt.clientX;
		mouseOrgY = evnt.clientY;
		document.onmousemove = layerMove;
	}

	function dragLayer(objName, hL, hT, hW, hH) {
		if (!isIE && !isMZ) chkBrowser();
		if (!isIE && !isMZ) return false;
		dragObj = document.getElementById(objName);
		if (dragObj == null) return false;

		dragObj.setAttribute("WM_hL", hL);
		dragObj.setAttribute("WM_hT", hT);
		dragObj.setAttribute("WM_hW", hW);
		dragObj.setAttribute("WM_hH", hH);

		getHideObjList();
		if (isIE) myDrags();
		if (isMZ) dragObj.addEventListener("mousedown", myDrags, false);
	}

	/**
	 * 將指定的 Layer 移至可視的範圍
	 * @param string objName : 物件名稱
	 * @return none
	 **/
	function Mv2View(objName) {
		if (!isIE && !isMZ) chkBrowser();
		if (!isIE && !isMZ) return false;
		var obj = document.getElementById(objName);
		var sclTop = 0, winHeight = 0;
		var sclLeft = 0, winWidth = 0;
		var objTop = 0, objHeight = 0;
		var objLeft = 0, objWidth = 0;
		var evnt = isMZ ? dragEvnt : event;

		if (obj == null) return false;
		sclTop    = parseInt(document.body.scrollTop);   // 捲動軸 Top (scrollTop)
		sclLeft   = parseInt(document.body.scrollLeft);  // 捲動軸 Left (scrollLeft)
		objTop    = ((typeof(evnt) == "object") && (evnt != null)) ? parseInt(evnt.clientY) : 50;
		objLeft   = ((typeof(evnt) == "object") && (evnt != null)) ? parseInt(evnt.clientX) : 100;
		objHeight = parseInt(obj.offsetHeight);
		objWidth  = parseInt(obj.offsetWidth);
		winHeight = (isMZ) ? parseInt(window.innerHeight) : parseInt(document.body.offsetHeight);
		winWidth  = (isMZ) ? parseInt(window.innerWidth) : parseInt(document.body.offsetWidth);
		if ((objTop  + objHeight) > winHeight) objTop  = objTop  - objHeight;
		if ((objLeft + objWidth)  > winWidth)  objLeft = objLeft - objWidth - 4;
		obj.style.top  = Math.max(0, objTop  + sclTop) + "px";
		obj.style.left = Math.max(0, objLeft + sclLeft + 2) + "px";
	}

	/**
	 * show or hidden layer
	 *
	 * @param string objName : Object name
	 * @param boolean state :
	 *     true : show
	 *     false: hidden
	 **/
	function layerAction(objName, state) {
		var obj = document.getElementById(objName);
		dragObj = obj;
		if (state) {
			obj.style.display = "";
			obj.style.visibility = "visible";
			obj.hidden = false;
			Mv2View(objName);
		} else {
			obj.style.display = "none";
			obj.style.visibility = "hidden";
			obj.hidden = true;
		}
		/*
		orgSelectStart = document.onselectstart;
		document.onselectstart = function () {
			return false;
		};
		*/
		getHideObjList();
		hideShowCovered();
	}

	// 將 IE 的 select 物件隱藏 (Begin)
	function getAbsolutePos(el) {
		var SL = 0, ST = 0;
		var is_div = /^(div|table)$/i.test(el.tagName);
		if (is_div && el.scrollLeft) SL = el.scrollLeft;
		if (is_div && el.scrollTop) ST = el.scrollTop;
		var r = {
			x:el.offsetLeft - SL,
			y:el.offsetTop - ST
		};
		if (el.offsetParent) {
			var tmp = this.getAbsolutePos(el.offsetParent);
			r.x += tmp.x;
			r.y += tmp.y;
		}
		return r;
	}

	function getVisib(obj) {
		var value = obj.style.visibility;
		if (!value) {
			if (document.defaultView && typeof(document.defaultView.getComputedStyle) == "function") {
				if (!Calendar.is_khtml) value = document.defaultView.getComputedStyle(obj, "").getPropertyValue("visibility");
				else value = "";
			} else if (obj.currentStyle) {
				value = obj.currentStyle.visibility;
			} else value = '';
		}
		return value;
	}

	var hidObjList = new Array(0);
	function getHideObjList() {
		// if (dragObj == null) return false;
		if (isMZ) return false;

		var tags = new Array("applet", "iframe", "select");
		var p = null;
		hidObjList = new Array(0);
		for (var k = 0; k < tags.length; k++) {
			var exObj = dragObj.getElementsByTagName(tags[k]);
			var exLst = new Object();
			if ((exObj != null) && (exObj.length > 0)) {
				for (var j = 0; j < exObj.length; j++) {
					exLst[exObj[j].uniqueID] = true;
				}
			}
			var nodes = document.getElementsByTagName(tags[k]);
			if ((nodes != null) && (nodes.length > 0)) {
				for (var j = 0; j < nodes.length; j++) {
					if (typeof(exLst[nodes[j].uniqueID]) != "undefined") continue;
					p = getAbsolutePos(nodes[j]);
					hidObjList[hidObjList.length] = new Array(nodes[j], p.x, nodes[j].offsetWidth + p.x, p.y, nodes[j].offsetHeight + p.y);
				}
			}
		}
	}

	function hideShowCovered() {
		if (dragObj == null) return false;
		if (isMZ) return false;

		var p = getAbsolutePos(dragObj);
		var EX1 = p.x;
		var EX2 = dragObj.offsetWidth + EX1;
		var EY1 = p.y;
		var EY2 = dragObj.offsetHeight + EY1;

		var cc = null;
		var attr = null;
		for (var i = hidObjList.length; i > 0;) {
			cc = hidObjList[--i][0];
			attr = cc.getAttribute("exclude");
			if ((attr != null) || (attr == "true")) continue;
			var CX1 = parseInt(hidObjList[i][1]);
			var CX2 = parseInt(hidObjList[i][2]);
			var CY1 = parseInt(hidObjList[i][3]);
			var CY2 = parseInt(hidObjList[i][4]);
			if (dragObj.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) {
				if (!cc.__msh_save_visibility) {
					cc.__msh_save_visibility = getVisib(cc);
				}
				cc.style.visibility = cc.__msh_save_visibility;
			} else {
				if (!cc.__msh_save_visibility) {
					cc.__msh_save_visibility = getVisib(cc);
				}
				cc.style.visibility = "hidden";
			}
		}

		return false;
		var tags = new Array("applet", "iframe", "select");
		var p = getAbsolutePos(dragObj);
		var EX1 = p.x;
		var EX2 = dragObj.offsetWidth + EX1;
		var EY1 = p.y;
		var EY2 = dragObj.offsetHeight + EY1;
		for(var k = tags.length;k > 0;) {
			var ar = document.getElementsByTagName(tags[--k]);
			var cc = null;
			var attr = null;
			for(var i = ar.length; i > 0;) {
				cc = ar[--i];
				attr = cc.getAttribute("exclude");
				if ((attr != null) || (attr == "true")) continue;
				p = getAbsolutePos(cc);
				var CX1 = p.x;
				var CX2 = cc.offsetWidth + CX1;
				var CY1 = p.y;
				var CY2 = cc.offsetHeight + CY1;
				if (dragObj.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) {
					if (!cc.__msh_save_visibility) {
						cc.__msh_save_visibility = getVisib(cc);
					}
					cc.style.visibility = cc.__msh_save_visibility;
				} else {
					if (!cc.__msh_save_visibility) {
						cc.__msh_save_visibility = getVisib(cc);
					}
					cc.style.visibility = "hidden";
				}
			}
		}
	}
	// 將 IE 的 select 物件隱藏 (End)

	document.onmouseup = function (evnt) {
		dragEvnt = evnt;
		document.onmousemove = function () {};
		dragapproved = false;
		document.onselectstart = orgSelectStart;
	}

	var isDragsOK = true;

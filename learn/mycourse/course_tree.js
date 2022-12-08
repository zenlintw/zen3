 	/**
 	 * 教材目錄
 	 *
 	 * @since   2003/06/05
 	 * @author  ShenTing Lin
 	 * @version $Id: course_tree.js,v 1.1 2010/02/24 02:39:08 saly Exp $
 	 * @copyright 2003 SUNNET
 	 **/

	var isIE = false, isMZ = false;
	var bodyHeight = 0, bodyWidth = 0;
	var xmlVars = null, xmlHttp = null, xmlDocs = null;
	var objCkbox = new Object;
	var majorCourse = new Object;

 	function chkBrowser() {
		var re = new RegExp("MSIE","ig");
		if (re.test(navigator.userAgent)) {
 	        isIE = true;
 	    }

 	    re = new RegExp("Gecko", "ig");
 	    if (re.test(navigator.userAgent)) {
 	        isMZ = true;
 	    }
 	}

 	/**
 	 * 轉換 Html 的特殊字
 	 * @param string str : 要轉換的字串
 	 * @return string : 轉換後的字串
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
///////////////////////////////////////////////////////////////////////////////
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

	/**
	 * 取得節點的值
	 * @param object node : 要從哪個節點中取值
 	 * @param string tag  : 節點中的哪個 tag
 	 * @return string result : 取得的值。若無法取值，則回傳空字串
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
 	///////////////////////////////////////////////////////////////////////////////

 	var expandStatus = true;

 	/**
 	 * 顯示或隱藏 Frame
 	 **/
 	function winExpand(val) {
 	    var obj1 = document.getElementById("IconExpand");
 	    var obj2 = document.getElementById("IconCollection");
 	    var obj3 = document.getElementById("ToolBar");
 	    if ((obj1 == null) || (obj2 == null) || (obj3 == null))
 	        return false;

 	    expandStatus = val;
 	    if (val) {
 	        obj1.style.display = "none";
 	        obj2.style.display = "";
 	        obj3.style.visibility = "visible";
 	        if (obj2.offsetParent != null) obj2.offsetParent.style.padding = "6px 6px 0px 0px";
 	        parent.FrameExpand(1, true, '');
 	    } else {
 	        obj1.style.display = "";
 	        obj2.style.display = "none";
 	        obj3.style.visibility = "hidden";
 	        if (obj1.offsetParent != null) obj1.offsetParent.style.padding = "6px 6px 0px 0px";
 	        parent.FrameExpand(2, false, '58');
 	    }
 	    return false;
 	}

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
 	            icon.src = "/public/images/icon_expand_inc.png";
 	            icon.alt = MSG_EXPAND;
 	            icon.title = MSG_EXPAND;
 	        }
 	    } else {
 	        if (icon != null) {
 	            icon.src = "/public/images/icon_expand_dec.png";
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
 	var preIdx = '';

 	function mouseEvent(obj, val) {
 	    var idx = 0;
		var node = null;
		if ((typeof(obj) != "object") || (obj == null)) return false;
		idx = obj.getAttribute("MyAttr");
		if (idx == preIdx) {
			if (parseInt(val) == 3) do_func("folder", idx);
			return false;
		}
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
 	            if (parseInt(val) == 3) do_func("group", idx);
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
		if (gid == "root") return xmlDocs.documentElement;
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
		if ((typeof(node) != "object") || (node == null)) return "";
		if (!node.hasChildNodes()) return "";
		nodes = node.childNodes;
 	    cnt = nodes.length;
 	    for (var i = 0; i < cnt; i++) {
 	        if (nodes[i].nodeType != 1) continue;
 	        if (getCaption(nodes[i]) == '--=[unnamed]=--') continue;
 	        if (nodes[i].nodeName == "courses") {
 	            idx = nodes[i].getAttribute("id");

 	            txt += '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
 	            txt += '<tr><td nowrap>';
 	            // ident
 	            for (var j = (indent + 1); j > 0; j--)
 	                txt += '<pre style="width: 15px; display: inline;">&nbsp;&nbsp;</pre>';
 	            // icon
 	            childs = nodes[i].getElementsByTagName("courses");
 	            if ((childs != null) && (childs.length > 0))
 	                txt += '<img src="/public/images/icon_expand_inc.png" border="0" align="absmiddle" alt="' + MSG_EXPAND + '" title="' + MSG_EXPAND + '" id="Icon' + idx + '" onclick="gpExpand(\'' + idx + '\', ' + (indent + 1) + ')">';
 	            else
 	                txt += '<img src="/public/images/icon_expand_dot.png" border="0" align="absmiddle">';
 	            if (favorite) txt += '<input type="radio" name="actTarget[]" value="' + idx + '">';
 	            txt += '<span class="cssTbBlur" MyAttr="' + idx + '" id="Div' + idx + '" onmouseover="mouseEvent(this, 1)" onmouseout="mouseEvent(this, 2)" onclick="mouseEvent(this, 3)">';
 	            txt += htmlspecialchars(getCaption(nodes[i]));
 	            txt += '</span>';
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
 	        } // End for (var i = 0; i < cnt; i++)
 	    } // End if (isMZ)

 	    nodes = document.getElementsByTagName("input");
 	    if (nodes == null) return false;
 	    cnt = nodes.length;
 	    for (var i = 0; i < cnt; i++) {
 	        if (nodes[i].type == "radio") {
 	            nodes[i].onclick = eCancelBubble;
 	        }
 	    } // End for (var i = 0; i < cnt; i++)
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

 	    if ((typeof(obj) != "object") || (obj == null)) return false;
 	    txt = buildGP("root", 0);
 	    obj.innerHTML = txt;
 	    disableBubble();
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
 	    bodyHeight = Math.max(parseInt(bodyHeight) - 40, 0);
 	    bodyWidth = (isIE) ? document.body.clientWidth : window.innerWidth;
 	    bodyWidth = Math.max(parseInt(bodyWidth) - 12, 0);
 	    if (!navigator.userAgent.match(/(iPad)/i)) {
 	        obj.style.height = parseInt(bodyHeight);
 	    }
 	    if (parseInt(bodyWidth) <= 30) {
 	        bodyWidth = 20;
 	        winExpand(false);
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

 	    switch (act) {
 	        case "list_group":
 	            objCkbox = new Object;
 	            majorCourse = new Object;
 	            if (parseInt(extra) >= 5) {
 	                window.onresize = function() {};
 	                parent.FrameExpand(0, false, '');
 	                return true;
 	            } else {
 	                //resetWin();
 	                window.onresize = resetWin;
 	                winExpand(expandStatus);
 	            }
 	            txt = "<manifest>";
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
 	            // alert(xmlHttp.responseText);
 	            res = xmlDocs.loadXML(xmlHttp.responseText);
 	            if (!res) {
 	                alert(MSG_SYS_ERROR);
 	                return false;
				}
				ticket = getNodeValue(xmlDocs.documentElement, "ticket");
				preIdx = 0;
				txt = "";
				switch (parseInt(extra)) {
					case 1 : txt = MSG_ALL_MAJOR;    break;
					case 2 : txt = MSG_ALL_TEACH;    break;
					case 3 : txt = MSG_ALL_SCHOOL;   break;
					case 4 : txt = MSG_ALL_FAVORITE; break;
				}
				obj = document.getElementById("allCSTitle");
				if (obj != null) obj.setAttribute("title", txt);
				if (parseInt(extra) == 4) {
					txt = "<a href=\"javascript:void(null);\" onclick=\"do_func('manage', ''); event.cancelBubble=true;\" class=\"cssAnchor\" title=\"" + MSG_ALT_EDFOLDER + "\">" + MSG_EDIT_FOLDER + "</a>";
					favorite = true;
				} else {
					txt = "";
 	                favorite = false;
 	            }
 	            obj = document.getElementById("editDir");
 	            if (obj != null) obj.innerHTML = '<br /><blockquote style="display: inline">' + txt + '</blockquote>';
 	            obj = document.getElementById("sname");
 	            if (obj != null) {
 	                obj.innerHTML = favorite ? '<input type="radio" value="10000000" name="actTarget[]">' + MSG_FAVORITE : MSG_SCHOOL_NAME;
 	            }
 	            showListFolder(); // display course group
 	            return true;
 	            break;

 	        case "group":
 	            txt = "<manifest>";
 	            txt += "<ticket>" + ticket + "</ticket>";
 	            txt += "<action>" + act + "</action>";
 	            txt += "<group_id>" + extra + "</group_id>";
 	            txt += "</manifest>";

 	            res = xmlVars.loadXML(txt);
 	            if (!res) {
 	                alert(MSG_SYS_ERROR);
 	                return false;
 	            }
 	            xmlHttp.open("POST", "do_function.php", false);
 	            xmlHttp.send(xmlVars);
 	            // alert(xmlHttp.responseText);
 	            res = xmlVars.loadXML(xmlHttp.responseText);
 	            if (!res) {
 	                alert(MSG_SYS_ERROR);
 	                return false;
 	            }
 	            ticket = getNodeValue(xmlVars.documentElement, "ticket");
 	            // reload index.php
 	            obj = getTarget();
 	            if ((obj != null) && (typeof(obj.reloadSelf) == "function")) {
 	                obj.reloadSelf();
 	            }
 	            objCkbox = new Object;
 	            //majorCourse = new Object;
 	            break;

 	        case "favorite": // show or hidden favorite
 	            txt = "<manifest>";
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
 	            // alert(xmlHttp.responseText);
 	            res = xmlVars.loadXML(xmlHttp.responseText);
 	            if (!res) {
 	                alert(MSG_SYS_ERROR);
 	                return false;
 	            }
 	            ticket = getNodeValue(xmlVars.documentElement, "ticket");
 	            break;

 	        case "add_favorite": // add course to my favorite
 	            txt = "<manifest>";
 	            txt += "<ticket>" + ticket + "</ticket>";
 	            txt += "<action>" + act + "</action>";
 	            txt += "<course_id>" + parseInt(extra) + "</course_id>";
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
 	            res = getNodeValue(xmlVars.documentElement, "result");
 	            return parseInt(res);
 	            break;

 	        case "manage": // Goto Favorite manage system
 	            obj = getTarget();
 	            if (obj != null) obj.location.replace("manage_folder.php");
 	            break;

 	        case "detail": // get course detail info
 	            txt = "<manifest>";
 	            txt += "<ticket>" + ticket + "</ticket>";
 	            txt += "<action>" + act + "</action>";
 	            txt += "<course_id>" + parseInt(extra) + "</course_id>";
 	            txt += "</manifest>";

 	            res = xmlVars.loadXML(txt);
 	            if (!res) {
 	                alert(MSG_SYS_ERROR);
 	                return false;
 	            }
 	            xmlHttp.open("POST", "do_function.php", false);
 	            xmlHttp.send(xmlVars);
 	            // alert(xmlHttp.responseText);
 	            res = xmlVars.loadXML(xmlHttp.responseText);
 	            if (!res) {
 	                alert(MSG_SYS_ERROR);
 	                return false;
 	            }
 	            ticket = getNodeValue(xmlVars.documentElement, "ticket");
 	            csObj = new Object();
 	            nodes = xmlVars.getElementsByTagName("course");
 	            if ((nodes == null) || (nodes.length <= 0)) return null;
 	            node = nodes[0];
 	            nodes = node.childNodes;
 	            csObj['title'] = getCaption(node, 'title');
				for (var i = 0; i < nodes.length; i++) {
					if (nodes[i].nodeType != 1) continue;
					switch (nodes[i].nodeName) {
						case "title" : break;
						case "content_name" :
							csObj["content"] = getCaption(nodes[i], 'title');
							if(csObj["content"] == "--=[unnamed]=--" ||  csObj["content"] == "undefined") csObj["content"]="";
							break;
						case "status" :
							txt = parseInt(getNodeValue(node, "status"));
							csObj["status"] = cs_status[txt];
							break;
						case "content":
 	                        csObj["introduce"] = getNodeValue(node, nodes[i].nodeName);
 	                        break;
 	                    default:
 	                        csObj[nodes[i].nodeName] = getNodeValue(node, nodes[i].nodeName);
 	                }
 	            }
 	            //csObj['title'] = getCaption(nodes[0]);
 	            return csObj;
 	            break;

 	        case "major_add":
 	        case "major_del":
 	            nodes = new Array();
 	            for (var i = 0; i < extra.length; i++) {
 	                if (!isNaN(parseInt(extra[i])) && (parseInt(extra[i]) > 10000000))
 	                    nodes[nodes.length] = parseInt(extra[i]);
 	            }

 	            if (nodes.length == 0) {
 	                alert(MSG_SEL_COURSE);
 	                return false;
 	            }

 	            txt = "<manifest>";
 	            txt += "<ticket>" + ticket + "</ticket>";
 	            txt += "<action>" + act + "</action>";
 	            txt += "<course_id>" + nodes.toString() + "</course_id>";
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
 	            res = getNodeValue(xmlVars.documentElement, "result");
 	            txt = (act == "major_add") ? MSG_COURSE_ADD : MSG_COURSE_DEL;
 	            txt += (parseInt(res)) ? MSG_FAIL : MSG_SUCCESS;
 	            alert(txt);
 	            break;

 	        case "major_reset":
 	            objCkbox = new Object;

 	            txt = "<manifest>";
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
 	            //res = getNodeValue(xmlVars.documentElement, "result");
 	            break;

 	        case "elective": // 送出選課清單 (submit elective list)
 	            nodes = new Array();
 	            for (var i = 0; i < extra.length; i++) {
 	                if (!isNaN(parseInt(extra[i])) && (parseInt(extra[i]) > 10000000))
 	                    nodes[nodes.length] = parseInt(extra[i]);
 	            }

 	            if (nodes.length == 0) {
 	                alert(MSG_NO_ELECTIVE);
 	                return false;
 	            }

 	            txt = "<manifest>";
 	            txt += "<ticket>" + ticket + "</ticket>";
 	            txt += "<action>" + act + "</action>";
 	            txt += "<course_id>" + nodes.toString() + "</course_id>";
 	            txt += "</manifest>";
 	            res = xmlVars.loadXML(txt);
 	            if (!res) {
 	                alert(MSG_SYS_ERROR);
 	                return false;
 	            }
 	            xmlHttp.open("POST", "do_function.php", false);
 	            xmlHttp.send(xmlVars);
 	            // alert(xmlHttp.responseText);
 	            if (!xmlVars.loadXML(xmlHttp.responseText)) {
 	                alert(MSG_SYS_ERROR);
 	                // alert(xmlHttp.responseText);
 	                return false;
 	            }
 	            ticket = getNodeValue(xmlVars.documentElement, "ticket");
 	            res = getNodeValue(xmlVars.documentElement, "result");
 	            txt = (parseInt(res)) ? MSG_SEND_FAIL : MSG_SEND_SUCCESS;
 	            if ((parseInt(res))) alert(txt);
 	            break;
 	        case "drop_unelective": // 正式生退選未審核
 	        case "drop_elective": //退選課程
 	            txt = "<manifest>";
 	            txt += "<ticket>" + ticket + "</ticket>";
 	            txt += "<action>" + act + "</action>";
 	            txt += "<course_id>" + parseInt(extra) + "</course_id>";
 	            txt += "</manifest>";

 	            res = xmlVars.loadXML(txt);
 	            if (!res) {
 	                alert(MSG_SYS_ERROR);
 	                return false;
 	            }
 	            xmlHttp.open("POST", "do_function.php", false);
 	            xmlHttp.send(xmlVars);
 	            //alert(xmlHttp.responseText);
 	            if (!xmlVars.loadXML(xmlHttp.responseText)) {
 	                alert(MSG_SYS_ERROR);
 	                // alert(xmlHttp.responseText);
 	                return false;
 	            }
 	            ticket = getNodeValue(xmlVars.documentElement, "ticket");
 	            res = getNodeValue(xmlVars.documentElement, "result");
 	            txt = (parseInt(res)) ? MSG_DROP_SUCCESS : MSG_DROP_FAIL;
 	            alert(txt);
 	            obj = getTarget();
 	            obj.reloadSelf();
 	            break;
 	        case "append":
 	        case "move":
 	            res = searchPoint();
 	            if (res == "") {
 	                alert(MSG_TARGET);
 	                return false;
 	            }
 	            if (res == preIdx) {
 	                alert(MSG_SAME_SRC_TGT);
 	                return false;
 	            }
 	            // 注意：這邊沒有 break (Note: This no break)

 	        case "delete":
 	        case "up":
 	        case "down":
 	            if (!((act == "append") || (act == "move"))) res = "";
 	            txt = "<manifest>";
 	            txt += "<ticket>" + ticket + "</ticket>";
 	            txt += "<action>" + act + "</action>";
 	            txt += "<group_id>" + res + "</group_id>";
 	            txt += "<course_id>" + extra.toString() + "</course_id>";
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
 	            res = getNodeValue(xmlVars.documentElement, "result");
 	            res = parseInt(res);
 	            obj = getTarget();
 	            if (res == 0) {
 	                // reload index.php
 	                if ((obj != null) && (typeof(obj.reloadSelf) == "function")) {
 	                    obj.reloadSelf();
 	                }
				} else if (res < 0) {
					switch (act) {
						case "up"     : alert(MSG_NOT_MV_UP); break;
						case "down"   : alert(MSG_NOT_MV_DOWN); break;
						case "move"   :
						case "append" :
							if ((obj != null) && (typeof(obj.reloadSelf) == "function")) {
								obj.reloadSelf();
							}
							alert(MSG_SAME_COURSE);
 	                        break;
 	                    default:
 	                }
 	            }
 	            break;
 	    }
 	    return true;
 	}
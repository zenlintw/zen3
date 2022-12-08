	var xmlHttp = null, xmlDocs = null, xmlVars = null;

//////////////////////////////////////////////////////////////////////////////
	function getCourse(csid) {
		var txt = "";
		var res = false;

		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		txt  = "<manifest><course_id>" + csid + "</course_id></manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) return false;
		xmlHttp.open("POST", "course_get.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		return xmlVars.loadXML(xmlHttp.responseText);
	}

	function addCourse() {
		var nd = getTarget();
		var obj = document.getElementById("editFm");
		if (obj != null) {
			obj.csid.value   = "";
			obj.ticket.value = "";
			window.onunload = function () {};
			parent.FrameExpand(0, false, '');
			if ((typeof(nd) == "object") && (nd != null) && (typeof(nd.hid) != "undefined")) {
				nd.hid = true;
			}
			obj.submit();
		}
		return true;
	}

	function editCourse(val) {
		var nd = getTarget();
		var obj = document.getElementById("editFm");
		if (val.length <= 0) return false;
		if (obj != null) {
			obj.csid.value = val;
			window.onunload = function () {};
			parent.FrameExpand(0, false, '');
			if ((typeof(nd) == "object") && (nd != null) && (typeof(nd.hid) != "undefined")) {
				nd.hid = true;
			}
			obj.submit();
		}
		return true;
	}

	function delCourse() {
		var txt = "";
		var res = false;
		var ary = new Array();
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) ary[ary.length] = nodes[i].value;
		}
		if (nodes.length <= 0) {
			alert(MSG_DEL_DATA);
			return false;
		}
		if (!confirm(MSG_DELETE_COURSE)) return false;
		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		txt = "<manifest></manifest>";
		// <course_id>" +  + "</courses_id>
		res = xmlVars.loadXML(txt);
		if (!res) return false;
		txt = ary.toString();
		var cnode = xmlVars.createElement("course_id");
		var tnode = xmlVars.createTextNode(txt);
		cnode.appendChild(tnode);
		xmlVars.documentElement.appendChild(cnode);
		xmlHttp.open("POST", "course_delete.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		alert(MSG_DEL_CS_SUCCESS);
		var obj = document.getElementById("actFm");
		if (obj != null) {
			window.onunload = function () {};
			obj.submit();
		}
	}

	/**
	 * 顯示課程詳細資料
	 * show detail
	 * @param string val : 課程編號
	 **/
	function showDetail(val) {
		var obj = null;
		var nodes = null, node = null, attr = null;
		var idx = 0, cnt = 0;
		var csGroup = new Array();
		var txt = "";
		var res = false;

		// 介面顯示 (show interface) (Begin)
		obj = document.getElementById("DetailTable");
		if (obj == null) {
			alert(MSG_SYSTEM_ERROR);
			return false;
		}
		obj.style.display = "";
		obj = document.getElementById("ListTable");
		if (obj != null) obj.style.display = "none";
		// 介面顯示 (show interface) (End)

		node = xmlDocs.selectSingleNode('//course[@id="' + val + '"]');
		if (node == null) {
			res = getCourse(val);
			if (!res) {
				alert(MSG_SYSTEM_ERROR);
				return false;
			}
			node = xmlVars.documentElement.cloneNode(true);
			xmlDocs.documentElement.appendChild(node);
			node = xmlVars.documentElement;
		}

		// 課程名稱 (course name)
		obj = document.getElementById("csCaption");
		if (obj != null) obj.innerHTML = getCaption(node);
		// 開課老師 (techer)
		obj = document.getElementById("csTeacher");
		if (obj != null) obj.innerHTML = getNodeValue(node, "teacher_view");
		obj = document.getElementById("csRTeacher");
		if (obj != null) obj.innerHTML = getNodeValue(node, "teacher");
		obj = document.getElementById("csRInstructor");
		if (obj != null) obj.innerHTML = getNodeValue(node, "instructor");
		obj = document.getElementById("csRAssistant");
		if (obj != null) obj.innerHTML = getNodeValue(node, "assistant");
		// 教材使用 (content name)
		obj = document.getElementById("csContentName");
		if (obj != null) {
			nodes = node.getElementsByTagName("content_name");
			obj.innerHTML = ((nodes == null) || (nodes.length == 0)) ? "" : getCaption(nodes[0]);
		}
		// 報名起訖日期 (enroll date)
		obj = document.getElementById("csEnroll");
		if (obj != null) obj.innerHTML = getNodeValue(node, "enroll");
		// 上課起訖日期 (study date)
		obj = document.getElementById("csStudy");
		if (obj != null) obj.innerHTML = getNodeValue(node, "study");
		// 課程狀態 (course state)
		obj = document.getElementById("csStatus");
		if (obj != null) {
			idx = parseInt(getNodeValue(node, "status"));
			if (isNaN(idx)) idx = 0;
			obj.innerHTML = cs_status[idx];
		}
		// 修課審核 (course review)
		obj = document.getElementById("csReview");
		if (obj != null) obj.innerHTML = getNodeValue(node, "review");
		// 所屬課程群組 (course group)
		obj = document.getElementById("csGroup");
		if (obj != null) {
			cnt = getNodeValue(node, "group");
			obj.innerHTML = cnt;
		}
		// 教材或參考書 (content or referer)
		obj = document.getElementById("csTexts");
		if (obj != null) obj.innerHTML = getNodeValue(node, "texts");
		// 相關網站 (relation url)
		obj = document.getElementById("csURL");
		if (obj != null) obj.innerHTML = getNodeValue(node, "url");
		// 課程簡介 (course introduction)
		obj = document.getElementById("csContent");
		if (obj != null) obj.innerHTML = getNodeValue(node, "content");
		// 學分數 (credit)
		obj = document.getElementById("csCredit");
		if (obj != null) obj.innerHTML = getNodeValue(node, "credit");
		// 正式生人數 (student number)
		obj = document.getElementById("csNLimit");
		if (obj != null) {
			cnt = parseInt(getNodeValue(node, "n_limit"));
			if (isNaN(cnt)) cnt = 0;
			obj.innerHTML = ((cnt == 0) ? MSG_UNLIMIT : cnt);
		}
		// 旁聽生人數 (auditor number)
		obj = document.getElementById("csALimit");
		if (obj != null) {
			cnt = parseInt(getNodeValue(node, "a_limit"));
			if (isNaN(cnt)) cnt = 0;
			obj.innerHTML = ((cnt == 0) ? MSG_UNLIMIT : cnt);
		}
		// 教材空間上限 (Quota limit)
		obj = document.getElementById("csQuotaLimit");
		if (obj != null) {
			cnt = getNodeValue(node, "quota_limit");
			obj.innerHTML = cnt;
		}
		// 教材空間使用率 (course usage)
		obj = document.getElementById("csUsagePercent");
		if (obj != null) obj.innerHTML = getNodeValue(node, "quota_remain_percent");

		obj = document.getElementById("csUsage");
		if (obj != null) {
			cnt = getNodeValue(node, "quota_used");
			obj.innerHTML = cnt;
		}

		// 及格成績 (fair grade)
		obj = document.getElementById("fair_grade");
		if (obj != null) {
			idx = parseInt(idx);
			if (isNaN(idx) || (idx == 0)) idx = 1;
			cnt = parseInt(getNodeValue(node, "fair_grade"));
			if (isNaN(cnt)) cnt = 0;
			obj.innerHTML = cnt;
		}

		// 編輯 sysbar (modify sysbar)
		obj = document.getElementById("csSysbar");
		if (obj != null) {
			obj.innerHTML = '<a href="javascript:void(null)" class="cssAnchor" onclick="editSysbar(\'' + val + '\'); return false;">' + MSG_SYSBAR + '</a>';
		}
	}

	function closeDetail() {
		var obj = null;
		obj = document.getElementById("DetailTable");
		if (obj != null) obj.style.display = "none";
		obj = document.getElementById("ListTable");
		if (obj != null) obj.style.display = "";
	}

	/**
	 * 編輯課程選單
	 * @param string val : 課程編號
	 * @return void
	 **/
	var barWin = null;
	function editSysbar(val) {
		var obj = document.getElementById("sysbarFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		if ((barWin != null) && !barWin.closed) {
			barWin.focus();
		} else {
			barWin = window.open("about:blank", "esBar", "width=770,height=500,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1");
		}
		obj.csid.value = val;
		obj.submit();
	}

	function queryCourse(evnt) {
		var obj = null, tg = null;
		var txt = "";
		obj = document.getElementById("queryTxt");
		if (obj != null) txt = obj.value;
		obj = document.getElementById("actFm");
		if (obj != null) {
			obj.page.value = 1;
			obj.keyword.value = txt;
			window.onunload = function () {};
			tg = getTarget();
			if ((tg != null) && (typeof(tg.mouseEvent) == "function")) {
				tg.mouseEvent(evnt, "cssTbBlur");
			}
			obj.submit();
		}
	}

	function funcGroup(act) {
		var obj = null, cnode = null, tnode = null, nodes = null, attr = null;
		var ary = new Array(), sel = new Array();
		var txt = "", msg = "";
		var res = false;

		obj = document.getElementById("ListTable");
		if (obj == null) {
			alert(MSG_SYSTEM_ERROR);
			return false;
		}

		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (!nodes[i].checked)) continue;
			attr = nodes[i].getAttribute("exclude");
			if ((attr != null) && (attr == "true")) continue;
			sel[sel.length] = nodes[i].value;
		}
		if (sel.length <= 0) {
			switch (act) {
				case "remove" : msg = MSG_SEL_REMOVE; break;
				case "up"     :
				case "down"   : msg = MSG_SEL_MOVE; break
				default:
					msg = MSG_SEL_ACTION;
			}
			alert(msg);
			return false;
		}

		// 是否是全校課程列表 (is all courses list)
		if (gpidx == gpsch) {
			switch (act) {
				case "remove" : msg = MSG_NOT_DELTET; break;
				case "move"   : msg = MSG_NOT_MOVE_TO; break
				case "up"     :
				case "down"   : msg = MSG_MOT_MOVE; break
				default:
					msg = "";
			}
			if (msg != "") {
				alert(msg);
				return false;
			}
		}
		switch (act) {
			case "remove":
				if (!confirm(MSG_GP_REMOVE)) return false;
				break;

			case "move":
			case "append":
				obj = getTarget();
				if ((typeof(obj) == "object") && (obj != null)) {
					ary = obj.getChecke();
				}
				if (ary.length <= 0) {
					alert(MSG_SEL_GP_TARGET);
					return false;
				}
				break;
			default:
		}

		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		txt  = "<manifest>";
		txt += "<act>" + act + "</act>";
		txt += "<idx>" + gpidx + "</idx>";
		txt += "</manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) {
			alert(MSG_SYSTEM_ERROR);
			return false;
		}
		cnode = xmlVars.createElement("course");
		tnode = xmlVars.createTextNode(sel.toString());
		cnode.appendChild(tnode);
		xmlVars.documentElement.appendChild(cnode);
		cnode = xmlVars.createElement("group");
		tnode = xmlVars.createTextNode(ary.toString());
		cnode.appendChild(tnode);
		xmlVars.documentElement.appendChild(cnode);

		xmlHttp.open("POST", "course_group_func.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		if (xmlHttp.responseText == "") {
			switch (act) {
				case "append": alert(MSG_APPEND_SUCCESS); break;
				case "move"  : alert(MSG_MOVE_SUCCESS); break;
				case "remove": alert(MSG_DEL_SUCCESS); break;
				default:
			}
			if (act != "append") {
				obj = document.getElementById("actFm");
				if (obj != null) {
					window.onunload = function () {};
					obj.lsList.value = sel.toString();
					obj.submit();
				}
			}
		} else {
			switch (xmlHttp.responseText) {
				case "GorupID_Error": alert(MSG_GROUP_ID_ERROR); break;
				case "NOT_UP"       : alert(MSG_NOT_UP);         break;
				case "NOT_DOWN"     : alert(MSG_NOT_DOWN);       break;
				default:
			}
		}
	}
// ////////////////////////////////////////////////////////////////////////////
	function rmUnload(kind) {
		var obj = null;
		if (typeof(kind) == "string") {
			obj = document.getElementById("actFm");
			if (obj == null) return false;
			obj.page.value = (kind == 'page') ? pg : 1;
			if (typeof(sb) != "undefined") obj.sortby.value = sb;
			if (typeof(od) != "undefined") obj.order.value  = od;
			window.onunload = function () {};
			obj.submit();
		} else {
			window.onunload = function () {};
		}
	}

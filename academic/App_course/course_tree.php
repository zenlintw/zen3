<?php
	/**
	 * 課程群組列表
	 * 
	 * 取自/academic/course/course_tree.php
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');
	require_once(sysDocumentRoot . '/lang/course_tree.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');

	$title = $sysSession->school_name;
	$benc = sysEncode('10000000');
	$plst = 'var plst = new Array();';

	$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->school_id . $_COOKIE['idx']);
	$lang = str_replace('_', '-', strtolower($sysSession->lang));

	$js = <<< BOF
	var MSG_LOADING   = "{$MSG['msg_waiting'][$sysSession->lang]}";
	var MSG_EXPAND    = "{$MSG['cs_tree_expand'][$sysSession->lang]}";
	var MSG_COLLECT   = "{$MSG['cs_tree_collect'][$sysSession->lang]}";
	var MSG_SYS_ERROR = "{$MSG['msg_system_error'][$sysSession->lang]}";

	var xmlVars = null, xmlHttp = null, xmlDocs = null;
	var bodyHeight = 0, bodyWidth = 0;

	var hid     = false;
	var lstData = new Object();
	var ticket  = "{$ticket}";
	var theme   = "/theme/{$sysSession->theme}/{$sysSession->env}/";
	var lang    = "{$lang}";
	var ids     = "{$benc}";
	{$plst}

	// 關閉 Mozilla 的 scrollbar (Begin)
	var obj = window.scrollbars;
	if ((typeof(obj) == "object") && (obj.visible == true)) {
		obj.visible = false;
	}
	// 關閉 Mozilla 的 scrollbar (End)
	
	/**
	 * 取得另外的視窗
	 * @return Object 另外的視窗 (other frame)
	 **/
	function getTarget() {
		var obj = null;
		switch (this.name) {
			case "s_main"   : obj = parent.s_catalog; break;
			case "c_main"   : obj = parent.c_catalog; break;
			case "main"     : obj = parent.catalog;   break;
			case "s_catalog": obj = parent.s_main; break;
			case "c_catalog": obj = parent.c_main; break;
			case "catalog"  : obj = parent.main;   break;
			case "s_sysbar" : obj = parent.s_main; break;
			case "c_sysbar" : obj = parent.c_main; break;
			case "sysbar"   : obj = parent.main;   break;
		}
		return obj;
	}

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
			if (obj2.offsetParent != null) obj2.offsetParent.style.padding = "6px 5px 0px 0px";
			parent.FrameExpand(1, true, '');
		} else {
			obj1.style.display = "block";
			obj2.style.display = "none";
			obj3.style.visibility = "hidden";
			if (obj1.offsetParent != null) obj1.offsetParent.style.padding = "6px 0px 0px 0px";
			parent.FrameExpand(2, false, '20');
		}
		return false;
	}
// ////////////////////////////////////////////////////////////////////////////
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
			alert(MSG_SYS_ERROR);
			return false;
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
				icon.src = theme + "plus.gif";
				icon.alt = MSG_EXPAND;
				icon.title = MSG_EXPAND;
			}
		} else {
			if (icon != null) {
				icon.src = theme + "minus.gif";
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
						icon.src = theme + "/dot.gif";
						icon.alt = MSG_EXPAND;
						icon.title = MSG_EXPAND;
					}
				} else {
					obj.setAttribute("MyAttr", "true");
				}
				obj.innerHTML = txt;
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
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) return "";
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
				txt += '<img src="' + theme + 'plus.gif" width="9" height="15" border="0" align="absmiddle" alt="' + MSG_EXPAND + '" title="' + MSG_EXPAND + '" id="Icon' + idx + '" onclick="expandGroup(\'' + idx + '\', ' + (indent + 1) + ')">';
			} else {
				txt += '<img src="' + theme + 'dot.gif" width="9" height="15" border="0" align="absmiddle">';
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
							node.keyword.value = "";
							node.submit();
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
		if (hid) {
			hid = false;
			return false;
		}
		var obj = document.getElementById("ToolBar");
		if (obj == null) return false;
		bodyHeight = (isIE) ? document.body.clientHeight : window.innerHeight;
		bodyHeight = Math.max(parseInt(bodyHeight) - 30, 0);
		bodyWidth  = (isIE) ? document.body.clientWidth : window.innerWidth;
		bodyWidth  = Math.max(parseInt(bodyWidth) - 12, 0);

		obj.style.height = parseInt(bodyHeight);
		if (parseInt(bodyWidth) <= 30) {
			bodyWidth = 20;
			winFolderExpand(false);
		}
		obj.style.width = bodyWidth;
		obj.firstChild.style.width = bodyWidth;
	};

	window.onload = function () {
		document.body.scroll = "no";
		chkBrowser();
		// parent.FrameExpand(1, true, 0);
		winFolderExpand(true);

		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		buildGroup(0, 0);
		restoreTree();
	};

	window.onunload = function () {
		document.body.scroll = "no";
		parent.FrameExpand(0, false, 0);
	};
BOF;
	
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('class="cssTbBodyBg" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0"');
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="right"');
			showXHTML_tr_B('');
				showXHTML_td_B('class="cssTbBtn"');
					echo '<a href="javascript:;" onclick="return winFolderExpand(true)" id="IconExpand" style="display:none"><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_expand.gif" border="0" alt="' . $MSG['mage_expand'][$sysSession->lang] . '"></a>';
					echo '<a href="javascript:;" onclick="return winFolderExpand(false)" id="IconCollection" style="display:block"><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_collection.gif" border="0" alt="' . $MSG['mage_collect'][$sysSession->lang] . '"></a>';
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		echo '<div id="ToolBar" class="cssToolbar" style="width: 190px; height: 200px; overflow: auto;">';
		showXHTML_table_B('width="190" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
						showXHTML_tr_B('class="cssTrEvn"');
							// 版面問題，所以自己輸出
							echo '<td width="3" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/cl2.gif" width="3" height="3" border="0"></td>';
							echo '<td align="right" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/cl3.gif" width="3" height="3" border="0"></td>';
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('colspan="2" nowrap="nowrap"');
								echo '&nbsp;<img src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/icon_book.gif" width="22" height="12" border="0" align="absmiddle">';
									$csid = sysEncode(10000000);
									$txt = '<a href="javascript:;" onclick="return false;" Mimura="true" MyAttr="' . $csid . '" class="cssTbHead">' . $title . '</a>';
									echo $txt;
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" class="cssTbTable"');
						showXHTML_tr_B('style="cursor: default;" class="cssTbTr"');
							showXHTML_td_B('colspan="2" class="cssTbTd" nowrap id="CGroup"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		echo '</div>';
		echo '<div id="ToolBar" class="cssTbBugIE5">&nbsp;</div>';
	showXHTML_body_E('');

?>

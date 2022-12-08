<?php
	 /**************************************************************************************************
	*                                                                                                  *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                *
	*                                                                                                  *
	*		Programmer: Amm Lee                                                                        *
	*		Creation  : 2003/09/23                                                                     *
	*		work for  : 顯示班級管理的tree                                                             *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                         *
	*       $Id: class_group_tree.php,v 1.1 2010/02/24 02:38:14 saly Exp $                                                                                           *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/class_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2400100400';
	$sysSession->restore();
	
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){
	
	}
	
	$class_id = $_GET['a'] ? intVal($_GET['a']) : '';

	$js = <<< EOF
	var MSG_LOADING       = "{$MSG['msg_waiting'][$sysSession->lang]}";
	var MSG_EXPAND        = "{$MSG['expend'][$sysSession->lang]}";
	var MSG_COLLECT       = "{$MSG['collect'][$sysSession->lang]}";

	var class_id = "{$class_id}";
	var theme = "{$sysSession->theme}";
	var bodyHeight = 0, bodyWidth = 0;
	var obj = window.scrollbars;
	if ((typeof(obj) == "object") && (obj.visible == true)) {
		obj.visible = false;
	}

	/**
	 * 顯示或隱藏 Frame
	 **/
	function winExpand(val) {
		var obj1 = document.getElementById("IconExpand");
		var obj2 = document.getElementById("IconCollection");
		var obj3 = document.getElementById("ToolBar");
		if ((obj1 == null) || (obj2 == null) || (obj3 == null))
			return false;

		if (val) {
			// document.body.scroll = "no";
			obj1.style.display = "none";
			obj2.style.display = "block";
			obj3.style.visibility = "visible";
			parent.FrameExpand(1, true, '');
		} else {
			// document.body.scroll = "no";
			obj1.style.display = "block";
			obj2.style.display = "none";
			obj3.style.visibility = "hidden";
			parent.FrameExpand(2, false, '20');
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
				icon.src = "/theme/" + theme + "/academic/plus.gif";
				icon.alt = MSG_EXPAND;
				icon.title = MSG_EXPAND;
			}
		} else {
			if (icon != null) {
				icon.src = "/theme/" + theme + "/academic/minus.gif";
				icon.alt = MSG_COLLECT;
				icon.title = MSG_COLLECT;
			}
			obj.style.display = "block";
			attr = obj.getAttribute("MyAttr");
			if ((attr != null) && (attr == "false")) {
				obj.innerHTML = MSG_LOADING;
				obj.setAttribute("MyAttr", "true");
				txt = buildGP(val, parseInt(indent));

           //     alert('txt 97='+txt);

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

	var preIdx = 0;
	function mouseEvent(obj, val) {
		var idx = 0;
		var node = null;
		if (typeof(obj) != "object") return false;
		idx = parseInt(obj.getAttribute("MyAttr"));
		if (idx == preIdx) return false;
		switch (parseInt(val)) {
			case 1 : obj.className = "cssTrOdd"; break;   // Mouse Over
			case 2 : obj.className = "cssTrHead"; break;   // Mouse Out
			case 3 :    // Mouse Click
				node = document.getElementById("Div" + preIdx);
				if (node != null) {
					node.className = "cssTbBlur";
				}
				obj.className = "cssTbFocus";
				preIdx = idx;
				parent.main.loadCS(idx, true);
				break;
		}
	}
 // //////////////////////////////////////////////////////////////////////////
	var xmlVars = null;
	function getCaption(node) {
		if ((typeof(parent.main) == "object")
			&& (typeof(parent.main.getCaption) == "function")) {
			return parent.main.getCaption(node);
		}
		return "";
	}

	function getNode(gid) {
		var nodes = null, attr = null;
		if (gid == "root") return xmlVars.documentElement;
		nodes = xmlVars.getElementsByTagName("classes");
		if ((nodes == null) || (nodes.length <= 0)) return null;
		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("id");
			if ((attr != null) && (parseInt(attr) == parseInt(gid))) return nodes[i];
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

		node = getNode(gid);

		if ((typeof(node) != "object") || (node == null)) return "";
		if (!node.hasChildNodes()) return "";
		nodes = node.childNodes;
		cnt = nodes.length;
		for (var i = 0; i < cnt; i++) {
			if (nodes[i].nodeType != 1) continue;
			if (nodes[i].nodeName == "classes") {
				idx = nodes[i].getAttribute("id");

				txt += '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
				txt += '<tr><td nowrap class="cssTbBlur">';

				// ident
				for (var j = (indent + 1); j > 0 ; j--)
					txt += '<pre style="width: 15px; display: inline;">&nbsp;&nbsp;</pre>';
				// icon
				childs = nodes[i].getElementsByTagName("classes");

				if ((childs != null) && (childs.length > 0))
					txt += '<img src="/theme/' + theme + '/academic/plus.gif" width="9" height="15" border="0" align="absmiddle" alt="' + MSG_EXPAND + '" title="' + MSG_EXPAND + '" id="Icon' + idx + '" onclick="gpExpand(' + idx + ', ' + (indent + 1) + ')">';
				else
					txt += '<img src="/theme/' + theme + '/academic/dot.gif" width="9" height="15" border="0" align="absmiddle">';
				txt += '<input type="checkbox" value="' + idx + '">';
				txt += '<span MyAttr="' + idx + '" id="Div' + idx + '" onmouseover="mouseEvent(this, 1)" onmouseout="mouseEvent(this, 2)" onclick="mouseEvent(this, 3)">';
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
			}   // End for (var i = 0; i < cnt; i++)
		}   // End if (isMZ)

		nodes = document.getElementsByTagName("input");
		if (nodes == null) return false;
		cnt = nodes.length;
		for (var i = 0; i < cnt; i++) {
			if (nodes[i].type == "checkbox") {
				nodes[i].onclick = eCancelBubble;
			}
		}   // End for (var i = 0; i < cnt; i++)
		// 取消事件沸升 (End)
	}

	function showSearch() {
		var node = null;
		node = document.getElementById("Div" + preIdx);
		if (node != null) {
			node.className = "cssTbBlur";
			node.parentNode.className = "cssTbFocus";
		}
		preIdx = 0;
	}

	function showRoot(class_id) {

	    if (typeof(class_id) =="undefined"){
	        class_id = 1000000;
	    }

		var node = null;
		node = document.getElementById("Div" + preIdx);
		if (node != null) {
		    node.className="cssTbHead";
		}
		preIdx = class_id;

		parent.main.loadCS(class_id, true);
	}

	/**
	 * 顯示群組
	 **/
	function showGroup(node) {

		var obj = document.getElementById("CGroup");
		var nodes = null;
		var cnt = 0;
		var txt = "";

		xmlVars = node;
		txt = buildGP("root", 0);
		if (obj != null) {
			obj.innerHTML = txt;
		}
		disableBubble();

		if (class_id == '')
			showRoot();
		else
			parent.main.loadCS(class_id, true);
	}

	/**
	 * 搜尋勾選了哪些群組
	 **/
	function searchPoint() {
		var obj = null, nodes = null;
		var idx = new Array();
		var cnt = 0;

		obj = document.getElementById("CGroup");
		if (obj == null) return idx;
		nodes = obj.getElementsByTagName("input");
		cnt = nodes.length;
		for (var i = 0; i < cnt; i++) {
			if ((nodes[i].type == "checkbox") && nodes[i].checked)
				idx[idx.length] = nodes[i].value;
		}
		return idx;
	}

	window.onresize = function () {
		var obj = document.getElementById("ToolBar");
		if (obj == null) return false;
		bodyHeight = (isIE) ? document.body.clientHeight : window.innerHeight;
		bodyHeight = parseInt(bodyHeight) - 30;
		bodyWidth = (isIE) ? document.body.clientWidth : window.innerWidth;
		bodyWidth = parseInt(bodyWidth) - 10;

		obj.style.height = parseInt(bodyHeight);
		if (parseInt(bodyWidth) <= 30) {
			bodyWidth = 190;
			winExpand(false);
		}
		obj.style.width = bodyWidth;
		obj.firstChild.style.width = bodyWidth;
	};

	window.onload = function () {
		document.body.scroll = "no";
		chkBrowser();
		parent.FrameExpand(1, true, '');

		if ((typeof(parent.main.loadGP) == "object") || (typeof(parent.main.loadGP) == "function")) {
			parent.main.loadGP();
		} else {
			history.back();

		}
	};

	window.onunload = function () {
		window.onresize = function () {};
		document.body.scroll = "no";
		parent.FrameExpand(0, false, '');
	};
EOF;
	showXHTML_head_B($MSG['title_course_group'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('class="cssTbBodyBg" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0"');
	showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="right"');
		showXHTML_tr_B('');
			showXHTML_td_B('');
				echo '<a href="javascript:;" onclick="return winExpand(true)" id="IconExpand" style="display:none"><img src="/theme/' . $sysSession->theme . '/academic/icon_expand.gif" border="0" alt="' . $MSG['expend'][$sysSession->lang] . '"></a>';
				echo '<a href="javascript:;" onclick="return winExpand(false)" id="IconCollection" style="display:block"><img src="/theme/' . $sysSession->theme . '/academic/icon_collection.gif" border="0" alt="' . $MSG['collect'][$sysSession->lang] . '"></a>';
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
						echo '<td width="3" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/academic/cl2.gif" width="3" height="3" border="0"></td>';
						echo '<td align="right" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/academic/cl3.gif" width="3" height="3" border="0"></td>';
					showXHTML_tr_E('');

					showXHTML_tr_B('class="cssTrEvn" onclick="showRoot();"');
						showXHTML_td_B('colspan="2" nowrap="nowrap"');
							echo '&nbsp;<img src="/theme/' . $sysSession->theme . '/academic/icon_book.gif" width="22" height="12" border="0" align="absmiddle">&nbsp;';
							echo '<a href="javascript:;" class="cssTbHead">' . $sysSession->school_name . '</a>';
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
	showXHTML_body_E('');
?>

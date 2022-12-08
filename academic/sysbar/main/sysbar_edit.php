<?php
	/**
	 * 選單編輯
	 *
	 * @since   2004/03/30
	 * @author  ShenTing Lin
	 * @version $Id: sysbar_edit.php,v 1.1 2010/02/24 02:38:46 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_lib.php');
	require_once(sysDocumentRoot . '/lang/sysbar_config.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '1300100400';
	// $sysSession->restore();
	if (!aclVerifyPermission(1300300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!defined('SYSBAR_MENU') || !defined('SYSBAR_LEVEL')) {
		die('deny!');
	}
	$SYSBAR_MENU  = SYSBAR_MENU;
	$SYSBAR_LEVEL = SYSBAR_LEVEL;
	// 依各個環境不同顯示不同的字
	$ary = array();
	switch ($SYSBAR_LEVEL) {
		case 'root' :   // 系統設定值
		case 'administrator' :   // 學校預設值
			$ary[] = array($MSG['academic_title'][$sysSession->lang], 'tabs1', 'chgTabs(1)');
			$ary[] = array($MSG['teach_title'][$sysSession->lang]   , 'tabs2', 'chgTabs(2)');
			$ary[] = array($MSG['class_title'][$sysSession->lang]   , 'tabs3', 'chgTabs(3)');
			$ary[] = array($MSG['direct_title'][$sysSession->lang]  , 'tabs4', 'chgTabs(4)');
			// $ary[] = array($MSG['class_title'][$sysSession->lang]   , 'tabs3', 'chgTabs(5)');
			$ary[] = array($MSG['personal_title'][$sysSession->lang], 'tabs5', 'chgTabs(6)');
			$ary[] = array($MSG['maidan_title'][$sysSession->lang]  , 'tabs6', 'chgTabs(7)');
			$act   = $sysbarMenuNum[$SYSBAR_MENU] - 2;
			$sb_level = ($SYSBAR_LEVEL == 'root') ? 1 : 2;
			break;

		case 'manager' :   // 學校設定值
			$ary[] = array($MSG['teach_title'][$sysSession->lang]   , 'tabs2', 'chgTabs(2)');
			$ary[] = array($MSG['class_title'][$sysSession->lang]   , 'tabs3', 'chgTabs(3)');
			$ary[] = array($MSG['direct_title'][$sysSession->lang]  , 'tabs4', 'chgTabs(4)');
			// $ary[] = array($MSG['class_title'][$sysSession->lang]   , 'tabs3', 'chgTabs(5)');
			$ary[] = array($MSG['personal_title'][$sysSession->lang], 'tabs5', 'chgTabs(6)');
			$ary[] = array($MSG['maidan_title'][$sysSession->lang]  , 'tabs6', 'chgTabs(7)');
			$act   = $sysbarMenuNum[$SYSBAR_MENU] - 3;
			$sb_level = 3;
			break;

		case 'director' :   // 導師 -> 班級
			$ary[] = array($MSG['sysbar_setup'][$sysSession->lang]   , 'tabs3');
			$act   = 1;
			$sb_level = 4 . $sysSession->class_id;
			break;

		case 'teacher' :   // 教師 -> 教室
			$ary[] = array($MSG['sysbar_setup'][$sysSession->lang]   , 'tabs3');
			$act   = 1;
			$sb_level = 5 . $sysSession->course_id;
			break;

		case 'personal' :   // 個人
			$ary[] = array($MSG['sysbar_setup'][$sysSession->lang], 'tabs5');
			$act   = 1;
			$sb_level = 6;
			break;

		case 'manager_course' :   // 管理者 -> 課程或班級
			$ary[] = array($MSG['teach_title'][$sysSession->lang]   , 'tabs2', 'chgTabs(2)');
			$ary[] = array($MSG['class_title'][$sysSession->lang]   , 'tabs3', 'chgTabs(3)');
			$act   = $sysbarMenuNum[$SYSBAR_MENU] - 3;
			$sb_level = 7;
			break;

		default:
			$ary[] = array($MSG['sysbar_setup'][$sysSession->lang], 'tabs5');
			$act   = 1;
			$sb_level = 8;
	}

	// 顯示課程路徑
	$sysbarCourseName = '';

	$aryRole = $sysRoles;
	array_pop($aryRole); // 去掉最後一個 all
	$aryRoles = implode(', ', $aryRole);
// ////////////////////////////////////////////////////////////////////////////
	// 選單的種類
	$sysbarKind  = getSysbarKindList();

	// 身份
	$sysbarRoles = getSysbarRoleList();

	// $Theme = "/theme/{$sysSession->theme}/{$sysSession->env}";
	$ticket  = md5(sysTicketSeed . 'sysbar' . $_COOKIE['idx'] . $SYSBAR_LEVEL . $SYSBAR_MENU);
	$lang    = strtolower($sysSession->lang);
	$chgtabs = getChgTabJS();

	$js = <<< BOF
	// 訊息
	var MSG_HELP           = "{$MSG['help'][$sysSession->lang]}";
	var MSG_MODIFY_FUNC    = "{$MSG['modify_function'][$sysSession->lang]}";
	var MSG_MODIFY_FOLDER  = "{$MSG['modify_folder'][$sysSession->lang]}";
	var MSG_FUNC_ADD       = "{$MSG['function_add'][$sysSession->lang]}";
	var MSG_FUNC_EDIT      = "{$MSG['function_edit'][$sysSession->lang]}";
	var MSG_FUNC_DEL       = "{$MSG['function_del'][$sysSession->lang]}";
	var MSG_FUNC_MOVE_UP   = "{$MSG['function_mv_up'][$sysSession->lang]}";
	var MSG_FUNC_MOVE_DW   = "{$MSG['function_mv_down'][$sysSession->lang]}";
	var MSG_FUNC_SHOW_HIDE = "{$MSG['function_show_hide'][$sysSession->lang]}";
	var MSG_FUNC_MOVE_SEL  = "{$MSG['function_mv_sel'][$sysSession->lang]}";
	var MSG_FUNCTION       = "{$MSG['function'][$sysSession->lang]}";
	var MSG_FOLDER         = "{$MSG['folder'][$sysSession->lang]}";
	var MSG_NEW_FUNCTION   = "{$MSG['new_function'][$sysSession->lang]}";
	var MSG_SYSTEM_RESERVE = "{$MSG['system_reserve'][$sysSession->lang]}";
	var MSG_DEL_SURE1      = "{$MSG['sure_delete1'][$sysSession->lang]}";
	var MSG_DEL_SURE2      = "{$MSG['sure_delete2'][$sysSession->lang]}";
	var MSG_SEL_DIR_FIRST  = "{$MSG['select_folder_first'][$sysSession->lang]}";
	var MSG_SEL_FUNC_FIRST = "{$MSG['select_function_first'][$sysSession->lang]}";
	var MSG_ACL_CREATE     = "{$MSG['acl_create'][$sysSession->lang]}";
	var MSG_ID_DUPLICATE   = "{$MSG['id_duplicate'][$sysSession->lang]}";
	var MSG_SYS_ERROR      = "{$MSG['system_error'][$sysSession->lang]}";
	var MSG_SAVE_SUCCESS   = "{$MSG['save_success'][$sysSession->lang]}";
	var MSG_SAVE_FAIL      = "{$MSG['save_fail'][$sysSession->lang]}";
	var MSG_EXIT           = "{$MSG['save_confirm'][$sysSession->lang]}";
	var MSG_RELOAD_SYSTEM  = "{$MSG['reload_system_folder'][$sysSession->lang]}";
	var MSG_NOT_MV_UP      = "{$MSG['msg_not_move_up'][$sysSession->lang]}";
	var MSG_NOT_MV_DOWN    = "{$MSG['msg_not_move_down'][$sysSession->lang]}";
	var MSG_NOT_MV_LEFT    = "{$MSG['msg_not_move_left'][$sysSession->lang]}";
	var MSG_NOT_MV_RIGHT   = "{$MSG['msg_not_move_right'][$sysSession->lang]}";

// ////////////////////////////////////////////////////////////////////////////
	// 共用參數
	var ticket = "{$ticket}";
	var lang = '{$lang}';
	var theme  = "/theme/{$sysSession->theme}/{$sysSession->env}";
	var xmlDocs = null, xmlVars = null, xmlHttp = null;
	var notSave = false, isId = false, isSysDef = false;
	var isChgTabs = false;
	var maxColumn = 0;
	var aryEnv = new Array(1, 2, 4, 8, 16);
	var aryRole = new Array({$aryRoles});
	var cacheKind = new Object();
	cacheKind[1] = null; cacheKind[2]  = null;
	cacheKind[3] = null; cacheKind[4]  = null;
	cacheKind[5] = null; cacheKind[6]  = null;
	cacheKind[7] = null; cacheKind[8]  = null;
	cacheKind[9] = null; cacheKind[10] = null;
	var aryTitle = new Array();
	aryTitle[0] = new Array("big5"       , "big5");
	aryTitle[1] = new Array("gb2312"     , "gb2312");
	aryTitle[2] = new Array("en"         , "en");
	aryTitle[3] = new Array("euc-jp"     , "euc_jp");
	aryTitle[4] = new Array("user-define", "user_define");

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
	 * 取得使用者點選了哪些下層選單
	 */
	function getSubItem(n) {
		var obj = document.getElementById('sysbarTabs');
		var re = new RegExp("^I_" + n + "+_([0-9]+)$");
		var nodes = null, ret = null;
		var ary = new Array();

		if (obj == null) return ary;
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type == "checkbox") && nodes[i].checked) {
				ret = re.exec(nodes[i].name);
				if (ret != null){
					ary[ary.length] = RegExp.$1;
				}
			}
		}
		return ary;
	}

	/**
	 * 取得使用者點選了哪些主選單
	 */
	function getMainItem() {
		var obj = document.getElementById('sysbarTabs');
		var nodes = null;
		var ary = new Array();

		if (obj == null) return ary;
		nodes = obj.getElementsByTagName("input");
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if (nodes[i].type == "radio") {
				if (nodes[i].checked) return j;
				j++;
			}
		}
		return -1;
	}

	/**
	 * 取得指定節點
	 * @param integer main :
	 * @param integer sub  :
	 * @return
	 **/
	function getItem(main, sub) {
		var node = null, nodes = null;
		var path = "";
		var idx = 0;

		if (xmlDocs == null) return null;
		main = parseInt(main);
		sub  = parseInt(sub);
		if (sub < 0) {
			path = "//items/item";
			idx = main;
		} else {
			idx = main + 1;
			path = "//items/item[" + idx + "]/item";
			idx = sub;
		}
		xmlDocs.setProperty("SelectionLanguage", "XPath");
		nodes = xmlDocs.selectNodes(path);
		node = nodes[idx];
		return node;
	}

	function reSetCheckbox(main, val) {
		var txt = "I_" + main + "_";
		var str = "";
		var obj = null;

		for (var i = 0; i < val.length; i++) {
			str = txt + val[i];
			obj = document.getElementById(str);
			if (obj != null) obj.checked = true;
		}
	}

	function reSetRadio(val) {
		var obj = document.getElementById('sysbarTabs');
		var nodes = null;

		if ((obj == null) || (val < 0)) return false;
		nodes = obj.getElementsByTagName("input");
		for (var i = 0, j = 0, k = 0; i < nodes.length; i++) {
			if (nodes[i].type == "radio") {
				if (val == j) {
					nodes[i].checked = true;
					nodes[i].scrollIntoView(true);
					return false;
				}
				j++;
			}
		}
	}

	/**
	 * 同步功能種類
	 * @param val : 功能
	 * @param val : 功能
	 **/
	function syncValue(obj, val) {
		if ((typeof(obj) != "object") || (obj == null)) return false;
		for (var i = 0; i < obj.length; i++) {
			if (val == obj.options[i].value) {
				obj.selectedIndex = i;
				return true;
			}
		}
		return false;
	}

	/**
	 * 改變節點種類
	 */
	function chgKind(val) {
		var obj = document.getElementById("cntDetail");

		if (obj == null) return false;
		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();

		// 使用 cache
		if (cacheKind[val] != null) {
			obj.innerHTML = cacheKind[val];
			return true;
		}
		txt  = "<manifest>";
		txt += "<ticket>" + ticket + "</ticket>";
		txt += "<action>" + val + "</action>";
		txt += "</manifest>";

		res = xmlVars.loadXML(txt);
		if (!res) {
			alert(MSG_SYS_ERROR);
			return false;
		}

		xmlHttp.open("POST", "sysbar_kind.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		cacheKind[val] = xmlHttp.responseText;
		obj.innerHTML = xmlHttp.responseText;
	}

	/**
	 * 隱藏部分設定值
	 * @param boolean bol : 隱藏或顯示
	 *     true  : 隱藏
	 *     false : 顯示
	 * @return
	 **/
	function hiddenProperty(bol) {
		var obj = null;

		obj = document.getElementById("cntKind");
		if (obj != null) obj.style.display = (bol) ? "none" : "";

		obj = document.getElementById("cntCont");
		if (obj != null) obj.style.display = (bol) ? "none" : "";

		obj = document.getElementById("cntNWin");
		if (obj != null) obj.style.display = (bol) ? "none" : "";
	}

	/**
	 * 功能項的內容設定
	 * @param integer main : 主選單的編號
	 * @param integer sub  : 子選單的編號
	 * @return
	 **/
	var processNode = null;
	function itemProperties(main, sub) {
		var obj = document.getElementById("fmProperties");
		var node = null, nodes = null, attr = null, tag = null;
		var val = 0;
		var str = "";
		var res = false;

		if ((main < 0) && (processNode != null)) {
			node = processNode;
		} else {
			node = getItem(main, sub);
			if ((obj == null) || (node == null)) return false;
			if (!node.hasChildNodes()) return false;
			processNode = node;
		}

		attr = node.getAttribute("system");
		isSysDef = ((attr != null) && (attr == "true"));
		if (typeof(obj.sysdef) == "object") {
			obj.sysdef.checked = isSysDef;
		}

		nodes = node.childNodes;
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].nodeType != 1) continue;
			switch (nodes[i].nodeName) {
				case 'title' :
					if (obj.big5){
						obj.big5.value = getNodeValue(nodes[i], "big5");
						if (obj.big5.value == '')obj.big5.value='--=[unnamed]=--';
					}
					if (obj.gb2312){
						obj.gb2312.value      = getNodeValue(nodes[i], "gb2312");
						if (obj.gb2312.value == '')obj.gb2312.value='--=[unnamed]=--';
					}
					if (obj.en){
						obj.en.value          = getNodeValue(nodes[i], "en");
						if (obj.en.value == '')obj.en.value='--=[unnamed]=--';
					}
					if (obj.euc_jp){
						obj.euc_jp.value      = getNodeValue(nodes[i], "euc-jp");
						if (obj.euc_jp.value == '')obj.euc_jp.value='--=[unnamed]=--';
					}
					if (obj.user_define){
						obj.user_define.value = getNodeValue(nodes[i], "user-define");
						if (obj.user_define.value == '')obj.user_define.value='--=[unnamed]=--';
					}
					break;
				case 'href'  :
					attr = nodes[i].getAttribute("kind");
					res = syncKind(attr);
					if ((typeof(obj.detail) == "object") && (obj.detail != null)) {
						str = getNodeValue(node, "href");
						if (obj.detail.tagName.toLowerCase() == "select") {
							syncValue(obj.detail, str);
						} else if (obj.detail.tagName.toLowerCase() == "input") {
							obj.detail.value = str;
						}
					}
					attr = nodes[i].getAttribute("target");
					obj.newopen.checked = ((attr != null) && (attr == "_blank"));
					break;
				default:
			}
		}
		if (typeof(obj.fid) == "object") {
			attr = node.getAttribute("id");
			obj.fid.value = (attr == null) ? "" : attr;
		}

		if (typeof(obj.hidfunc) == "object") {
			attr = node.getAttribute("hidden");
			obj.hidfunc.checked = ((attr != null) && (attr == "true"));
		}

		// 進階設定 (Begin)
			// 環境
		attr = node.getAttribute("env");
		if (attr != null) val = parseInt(attr);
		for (var i = 0; i < aryEnv.length; i++) {
			tag = document.getElementById("env_" + aryEnv[i]);
			if (tag != null) tag.checked = ((val & aryEnv[i]) > 0);
		}

			// 身份
		val = 0;
		attr = node.getAttribute("role");
		if (attr != null) val = parseInt(attr);
		for (var i = 0; i < aryRole.length; i++) {
			tag = document.getElementById("role_" + aryRole[i]);
			if (tag != null) tag.checked = ((val & aryRole[i]) > 0);
		}

		extraSelFunc(null, "barEnvs");
		extraSelFunc(null, "barRoles");
		// 進階設定 (End)

		tag = document.getElementById("toolbox");
		if (tag != null) {
			tag.style.left = "100px";
			tag.style.top  = "50px";
		}

		// 切換背景色 (Begin)
		obj = document.getElementById("propertiesTabs");
		var cols = "cssTrEvn";
		if (obj != null) {
			for (var i = 0; i < obj.rows.length; i++) {
				if (obj.rows[i].style.display == "none") continue;
				obj.rows[i].className = cols;
				cols = (cols == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
			}
		}
		// 切換背景色 (End)
		layerAction("toolbox", true);
	}

	/**
	 * 儲存修改後的結果
	 **/
	function itemPropertyComplete() {
		var obj = document.getElementById("fmProperties");
		var node = null, nodes = null, childs = null, tnode = null;
		var val = "";
		var isUd = false;

		if ((processNode == null) || (obj == null)) return false;

		// 選單編號
		if (typeof(obj.fid) == "object") {
			processNode.setAttribute("id", obj.fid.value);
		}
		// 隱藏功能
		if (typeof(obj.hidfunc) == "object") {
			val = (obj.hidfunc.checked) ? "true" : "false";
			processNode.setAttribute("hidden", val);
		}
		// 系統預設值
		if (typeof(obj.sysdef) == "object") {
			val = (obj.sysdef.checked) ? "true" : "false";
			processNode.setAttribute("system", val);
		}
		// 環境
		val = 0;
		isUd = false;
		for (var i = 0; i < aryEnv.length; i++) {
			node = document.getElementById("env_" + aryEnv[i]);
			if (node != null) {
				isUd = true;
				if (node.checked) val += parseInt(node.value);
			}
		}
		if (isUd) processNode.setAttribute("env", val);
		// 身份
		val = 0;
		isUd = false;
		for (var i = 0; i < aryRole.length; i++) {
			node = document.getElementById("role_" + aryRole[i]);
			if (node != null) {
				isUd = true;
				if (node.checked) val += parseInt(node.value);
			}
		}
		if (isUd) processNode.setAttribute("role", val);
		// 標題
		nodes = processNode.childNodes;
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].nodeType != 1) continue;
			switch (nodes[i].nodeName) {
				case 'title' :
					for (var j = 0; j < aryTitle.length; j++) {
						val = "";
						childs = nodes[i].getElementsByTagName(aryTitle[j][0]);
						node = eval("obj." + aryTitle[j][1]);
						if (typeof(node) == "object") {
							val = node.value;
							if(val=='')val="--=[unnamed]=--";
							if (childs[0].firstChild != null) {
								childs[0].firstChild.nodeValue = val;
							} else {
								node = xmlDocs.createTextNode(val);
								childs[0].appendChild(node);
							}
						}
					}
					break;
				case 'href'  :
					if (typeof(obj.kind) == "object") {
						tnode = document.getElementById("cntKind");
						if ((tnode != null) && (tnode.style.display == "")) {
							nodes[i].setAttribute("kind", obj.kind.value);
						}
					}
					if (typeof(obj.newopen) == "object") {
						val = (obj.newopen.checked) ? "_blank" : "default";
						nodes[i].setAttribute("target", val);
					}
					val = "";
					if (typeof(obj.detail) == "object") {
						val = obj.detail.value;
						if (nodes[i].firstChild != null) {
							nodes[i].firstChild.nodeValue = val;
						} else {
							node = xmlDocs.createTextNode(val);
							nodes[i].appendChild(node);
						}
					}
					break;
				default:
			}
		}
		notSave = true;
		layerAction("toolbox", false);
		displayLayout();
	}

	/**
	 * 子功能項的處理(新增、刪除、隱藏)
	 */
	function itemProc(label, func){
		var ary = new Array();
		var nodes = null, node = null, attr = null, node1 = null, node2 = null;
		var val = "", txt = "";
		var isAdd = false;


		switch(func){
			case 1:	/* 加子項 */
				txt = "<manifest><ticket>" + ticket + "</ticket></manifest>";
				res = xmlVars.loadXML(txt);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}

				xmlHttp.open("POST", "sysbar_new.php", false);
				xmlHttp.send(xmlVars);
				// alert(xmlHttp.responseText);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					xmlVars = null;
					alert(MSG_SYS_ERROR);
					return false;
				}
				// ticket = getNodeValue(xmlVars.documentElement, "ticket");
				nodes = xmlVars.getElementsByTagName("item");
				if ((nodes == null) || (nodes.length <= 0)) return false;
				node = getItem(label, -1);
				processNode = nodes[0].cloneNode(true);
				node.appendChild(processNode);
				isAdd = true;
				break;
			case 2: /* 刪子項 */
				ary = getSubItem(label);
				if (ary.length <= 0) {
					alert(MSG_SEL_FUNC_FIRST);
					return false;
				}
				for (var i = ary.length - 1; i >= 0; i--) {
					node = getItem(label, ary[i]);
					if (node == null) continue;
					attr = node.getAttribute("system");
					if ((attr != null) && (attr == "true")) {
						alert(' [' + getCaption(node) + '] ' + MSG_SYSTEM_RESERVE);
						node.setAttribute("hidden", "true");
					} else {
						node.parentNode.removeChild(node);
					}
				}
				break;
			case 3: /* 隱藏子項 */
				ary = getSubItem(label);
				if (ary.length <= 0) {
					alert(MSG_SEL_FUNC_FIRST);
					return false;
				}
				for (var i = 0; i < ary.length; i++) {
					node = getItem(label, ary[i]);
					if (node != null) {
						attr = node.getAttribute("hidden");
						val = ((attr != null) && (attr == "true")) ? "false" : "true";
						node.setAttribute("hidden", val);
					}
				}
				break;
			case 4: /* 集合子項 */
				var itemcnt = 0;
				node1 = getItem(label, -1);
				for (var i = 0; i < maxColumn; i++) {
					if (i == label) continue;
					ary = getSubItem(i);
					itemcnt += ary.length;
					for (var j = 0; j < ary.length; j++) {
						node2 = getItem(i, ary[j]);
						node1.appendChild(node2.cloneNode(true));
					}
					for (var j = ary.length - 1; j >= 0; j--) {
						node2 = getItem(i, ary[j]);
						node2.parentNode.removeChild(node2);
					}
				}
				if (itemcnt == 0) {
					alert(MSG_SEL_FUNC_FIRST);
				}
				ary = new Array();
				break;
			case 5: /* 修改子項 */
				ary = getSubItem(label);
				if (ary.length <= 0) {
					alert(MSG_SEL_FUNC_FIRST);
					return false;
				}
				itemProperties(label, ary[0]);
				return false;
				break;
			case 6: /* 子項上移 */
				ary = getSubItem(label);
				if (ary.length <= 0) {
					alert(MSG_SEL_FUNC_FIRST);
					return false;
				}
				if (ary[0] == 0) {
					alert(MSG_NOT_MV_UP);
					return false;
				}
				for (var i = 0; i < ary.length; i++) {
					val = parseInt(ary[i]) - 1;
					node1 = getItem(label, ary[i]);
					node2 = getItem(label, val);
					swapNode(node1, node2);
					ary[i]--;
				}
				break;
			case 7: /* 子項下移 */
				ary = getSubItem(label);
				if (ary.length <= 0) {
					alert(MSG_SEL_FUNC_FIRST);
					return false;
				}
				val  = parseInt(ary[ary.length - 1]) + 1;
				node = getItem(label, val);
				if (node == null) {
					alert(MSG_NOT_MV_DOWN);
					return false;
				}
				for (var i = ary.length - 1; i >= 0; i--) {
					val = parseInt(ary[i]) + 1;
					node1 = getItem(label, ary[i]);
					node2 = getItem(label, val);
					swapNode(node1, node2);
					ary[i]++;
				}
				break;
		}
		notSave = true;
		displayLayout();
		reSetCheckbox(label, ary);
		if (isAdd) itemProperties(-1, 0);
	}

	function doFunc(val) {
		var node = null, nodes = null, attr = null, node1 = null, node2 = null;
		var txt = "";
		var idx = 0;

		switch (val) {
			case 1:  /* 新增 */
				txt = "<manifest><ticket>" + ticket + "</ticket></manifest>";
				res = xmlVars.loadXML(txt);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}

				xmlHttp.open("POST", "sysbar_new.php", false);
				xmlHttp.send(xmlVars);
				// alert(xmlHttp.responseText);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					xmlVars = null;
					alert(MSG_SYS_ERROR);
					return false;
				}
				// ticket = getNodeValue(xmlVars.documentElement, "ticket");
				nodes = xmlVars.getElementsByTagName("item");
				if ((nodes == null) || (nodes.length <= 0)) return false;
				node = getItem(0, -1);
				processNode = nodes[0].cloneNode(true);
				nodes = processNode.getElementsByTagName("href");
				if ((nodes != null) && (nodes.length > 0)) nodes[0].setAttribute("kind", "0");
				node.parentNode.appendChild(processNode);
				notSave = true;
				displayLayout();
				itemProperties(-1, -1);
				break;
			case 2:   /* 刪除 */
				idx = parseInt(getMainItem());
				if (idx < 0) {
					alert(MSG_SEL_DIR_FIRST);
					return false;
				}

				node = getItem(idx, -1);
				if (node == null) return false;
				attr = node.getAttribute("system");
				if ((attr != null) && (attr == "true")) {
					alert(' [' + getCaption(node) + '] ' + MSG_SYSTEM_RESERVE);
					node.setAttribute("hidden", "true");
					notSave = true;
				} else if (confirm(MSG_DEL_SURE1 + getCaption(node) + MSG_DEL_SURE2)) {
					node.parentNode.removeChild(node);
					notSave = true;
				} else {
					return false;
				}
				displayLayout();
				break;
			case 3:   /* 顯示或隱藏 */
				idx = parseInt(getMainItem());
				if (idx < 0) {
					alert(MSG_SEL_DIR_FIRST);
					return false;
				}

				node = getItem(idx, -1);
				if (node == null) return false;
				attr = node.getAttribute("hidden");
				txt = ((attr != null) && (attr == "true")) ? "false" : "true";
				node.setAttribute("hidden", txt);
				notSave = true;
				displayLayout();
				reSetRadio(idx);
				break;
			case 4:   /* 修改 */
				idx = parseInt(getMainItem());
				if (idx < 0) {
					alert(MSG_SEL_DIR_FIRST);
					return false;
				}
				itemProperties(idx, -1);
				break;
			case 5:   /* 儲存 */
				nodes = xmlDocs.getElementsByTagName("ticket");
				for(var i = (nodes.length - 1); i >= 0; i--) {
					node = nodes[i].parentNode;
					node.removeChild(nodes[i]);
				}

				node1 = xmlDocs.createElement("ticket");
				node2 = xmlDocs.createTextNode(ticket);
				node1.appendChild(node2);
				xmlDocs.documentElement.appendChild(node1);

                xajax_clean_temp(st_id);    // 清除暫存資料

				xmlHttp.open("POST", "sysbar_save.php", false);
				xmlHttp.send(xmlDocs);
				// alert(xmlHttp.responseText);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}
				// ticket = getNodeValue(xmlVars.documentElement, "ticket");
				res = getNodeValue(xmlVars.documentElement, "result");
				notSave = false;
				alert(res);
				break;
			case 6:   /* 左移 */
				idx = parseInt(getMainItem());
				if (idx < 0) {
					alert(MSG_SEL_DIR_FIRST);
					return false;
				}
				if (idx == 0) {
					alert(MSG_NOT_MV_LEFT);
					return false;
				}
				node1 = getItem(idx, -1);
				node2 = getItem(--idx, -1);
				swapNode(node1, node2);
				notSave = true;
				displayLayout();
				reSetRadio(idx);
				break;
			case 7:   /* 右移 */
				idx = parseInt(getMainItem());
				if (idx < 0) {
					alert(MSG_SEL_DIR_FIRST);
					return false;
				}
				node1 = getItem(idx, -1);
				node2 = getItem(++idx, -1);
				if (node2 == null) {
					alert(MSG_NOT_MV_RIGHT);
					return false;
				}
				swapNode(node1, node2);
				notSave = true;
				displayLayout();
				reSetRadio(idx);
				break;
			case 8:   /* 恢復系統預設值 */
				if (!confirm(MSG_RELOAD_SYSTEM)) return false;
				txt = "<manifest><ticket>" + ticket + "</ticket></manifest>";
				res = xmlVars.loadXML(txt);
				if (!res) {
					alert(MSG_SYS_ERROR);
					return false;
				}

				xmlHttp.open("POST", "sysbar_default.php", false);
				xmlHttp.send(xmlVars);
				// alert(xmlHttp.responseText);
				res = xmlVars.loadXML(xmlHttp.responseText);
				if (!res) {
					xmlVars = null;
					alert(MSG_SYS_ERROR);
					return false;
				}
				// ticket = getNodeValue(xmlVars.documentElement, "ticket");
				loadSysbar();
				displayLayout();
				break;
			default:
				break;
		}
	}
// ////////////////////////////////////////////////////////////////////////////
	function swapNode(node1, node2) {
		var pnode1 = null, pnode2 = null, tnode1 = null, tnode2 = null;

		if ((typeof(node1) != "object") || (node1 == null)
			|| (typeof(node2) != "object") || (node2 == null))
		{
			return false;
		}
		pnode1 = node1.parentNode;
		pnode2 = node2.parentNode;
		tnode1 = node1.cloneNode(true);
		tnode2 = node2.cloneNode(true);
		pnode1.replaceChild(tnode2, node1);
		pnode2.replaceChild(tnode1, node2);
	}
// ////////////////////////////////////////////////////////////////////////////
	/**
	 * 清除 UI
	 */
	function cleanTable() {
		var cnt = 0;
		var obj = document.getElementById("sysbarTabs");
		if (obj == null) return false;
		cnt = obj.rows.length - 1;

		for (var i = cnt; i > 1; i--) {
			obj.deleteRow(i);
		}
	}
	/**
	 * 將 XML 內容轉換為 UI 呈現出來
	 */
	function displayLayout() {
		var obj = document.getElementById("sysbarTabs");
		var root = null, node = null, nodes = null, item = null, items = null, child = null, childs = null;
		var attr = null;
		var leng = 0, mcnt = 0, scnt = 0, cnt = 0;
		var mcaption = "", scaption = "", sty = "", ids = "", displayHTML = "";
		var cols = "cssTrOdd";

		if ((obj == null) || (xmlDocs == null)) return false;
		cleanTable();   // 清除表格
		root = xmlDocs.selectSingleNode("//items");
		if ((root == null) || (!root.hasChildNodes())) return false;
		nodes = root.childNodes;
		for (var i = 0; i < nodes.length; i++) {
			node = nodes[i];
			if ((node.nodeType != 1) || (node.nodeName != "item")) continue;
			// 建立主選單的位置
			leng = obj.rows.length;
			for (var j = leng; j < 4; j++) {
				obj.insertRow(-1);
			}

			cnt = 0;
			items = node.childNodes;
			for (var j = 0; j < items.length; j++) {
				item = items[j];
				if ((item.nodeType != 1) || (item.nodeName != "item")) continue;
				// 顯示子選單
				// 先建立子選單的位置
				leng = cnt + 4;
				if (obj.rows.length <= leng) {
					obj.insertRow(-1);
					// 由左至右填空白
					for (var k = 0; k <= mcnt; k++) {
						obj.rows[leng].insertCell(-1);
						obj.rows[leng].cells[k].innerHTML = "&nbsp;";
					}
				}

				// 填寫資料
				if (obj.rows[leng].cells.length <= mcnt) obj.rows[leng].insertCell(-1);
				attr = item.getAttribute("hidden");
				sty  = ((attr == null) || (attr == "false")) ? "" : ' style="text-decoration: line-through;"';
				ids  = item.getAttribute("id");
				scaption = getCaption(item);
				if (isId) scaption += " (" + ids + ")";
				displayHTML = (
					'<input type="checkbox" name="I_' + mcnt + '_' + cnt + '" id="I_' + mcnt + '_' + cnt + '">' +
					'<a href="javascript:;" onclick="itemProperties(' + mcnt + ',' + cnt + '); return false;" title="' + MSG_MODIFY_FUNC + '" class="cssAnchor"' + sty + '>' + scaption + '</a>'
					);
				obj.rows[leng].cells[mcnt].noWrap = true;
				obj.rows[leng].cells[mcnt].innerHTML = displayHTML;
				cnt++;
			}
			if (scnt <= 0) scnt = 1;
			if (scnt < cnt) {
				scnt = cnt;
			} else {
				// 由上至下填空白
				for (var k = cnt; k < scnt; k++) {
					if (obj.rows.length <= k + 4) obj.insertRow(-1);
					obj.rows[k + 4].insertCell(-1);
					obj.rows[k + 4].cells[mcnt].innerHTML = "&nbsp;";
				}
			}

			// 主選單
			attr = node.getAttribute("hidden");
			sty  = ((attr == null) || (attr == "false")) ? "" : ' style="text-decoration: line-through;"';
			ids  = node.getAttribute("id");
			mcaption = getCaption(node);
			if (isId) mcaption += " (" + ids + ")";
			displayHTML = (
				'<input type="radio" name="mainItems" value="' + mcnt + '">' +
				'<a href="javascript:;" onclick="itemProperties(' + mcnt + ', -1); return false;" title="' + MSG_MODIFY_FOLDER + '" class="cssAnchor"' + sty + '>' + mcaption + '</a>'
			);
			leng = obj.rows[2].cells.length;
			obj.rows[2].insertCell(-1);
			obj.rows[2].cells[leng].noWrap = true;
			obj.rows[2].cells[leng].innerHTML = displayHTML;

			// 子選單的工具列
			leng = obj.rows[3].cells.length;
			obj.rows[3].insertCell(-1);
			displayHTML = (
				'<a href="javascript:;" onclick="itemProc(' + mcnt + ',1); return false;"><img src="' + theme + '/icon_insert.gif" border="0" align="absmiddle" width="16" height="16" alt="' + MSG_FUNC_ADD + '" title="' + MSG_FUNC_ADD + '"></a> ' +
				'<a href="javascript:;" onclick="itemProc(' + mcnt + ',5); return false;"><img src="' + theme + '/icon_edit.gif"   border="0" align="absmiddle" width="16" height="16" alt="' + MSG_FUNC_EDIT + '" title="' + MSG_FUNC_EDIT + '"></a> ' +
				'<a href="javascript:;" onclick="itemProc(' + mcnt + ',2); return false;"><img src="' + theme + '/icon_delete.gif" border="0" align="absmiddle" width="16" height="16" alt="' + MSG_FUNC_DEL + '" title="' + MSG_FUNC_DEL + '"></a> ' +
				'<a href="javascript:;" onclick="itemProc(' + mcnt + ',6); return false;"><img src="' + theme + '/icon_up.gif"     border="0" align="absmiddle" width="16" height="16" alt="' + MSG_FUNC_MOVE_UP + '" title="' + MSG_FUNC_MOVE_UP + '"></a> ' +
				'<a href="javascript:;" onclick="itemProc(' + mcnt + ',7); return false;"><img src="' + theme + '/icon_down.gif"   border="0" align="absmiddle" width="16" height="16" alt="' + MSG_FUNC_MOVE_DW + '" title="' + MSG_FUNC_MOVE_DW + '"></a> ' +
				'<a href="javascript:;" onclick="itemProc(' + mcnt + ',3); return false;"><img src="' + theme + '/icon_show.gif"   border="0" align="absmiddle" width="16" height="16" alt="' + MSG_FUNC_SHOW_HIDE + '" title="' + MSG_FUNC_SHOW_HIDE + '"></a> ' +
				'<a href="javascript:;" onclick="itemProc(' + mcnt + ',4); return false;"><img src="' + theme + '/icon_import.gif" border="0" align="absmiddle" width="16" height="16" alt="' + MSG_FUNC_MOVE_SEL + '" title="' + MSG_FUNC_MOVE_SEL + '"></a>'
			);
			obj.rows[3].cells[leng].noWrap = true;
			obj.rows[3].cells[leng].innerHTML = displayHTML;
			mcnt++; // 計算總共有幾個主選單
		}

		// 建立第一個項目 (目錄)
		obj.rows[2].insertCell(0);
		obj.rows[2].cells[0].rowSpan = 2;
		obj.rows[2].cells[0].noWrap = true;
		obj.rows[2].cells[0].innerHTML = MSG_FOLDER;
		obj.rows[2].cells[0].onclick = showID;


		// 建立第一個項目 (功能)
		if (obj.rows.length <= 4) obj.insertRow(-1);
		obj.rows[4].insertCell(0);
		obj.rows[4].cells[0].rowSpan = scnt;
		obj.rows[4].cells[0].noWrap = true;
		obj.rows[4].cells[0].innerHTML = MSG_FUNCTION;

		// 設定佈景
		obj.rows[2].className = "cssTrEvn";
		obj.rows[3].className = "cssTrOdd";
		obj.rows[4].cells[0].className = "cssTrOdd";
		for (var i = 4; i < obj.rows.length; i++) {
			cols = (cols == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
			obj.rows[i].className = cols;
		}

		maxColumn = mcnt;
		// 設定說明與課程路徑的 colspan
		mcnt++;
		obj.rows[0].cells[0].colSpan = mcnt;
		obj.rows[1].cells[0].colSpan = mcnt;
	}

	/**
	 * 載入選單
	 */
	function loadSysbar() {
		var txt = "";
		var res = false;

		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		txt  = "<manifest><ticket>" + ticket + "</ticket></manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) {
			alert(MSG_SYS_ERROR);
			return false;
		}

		xmlHttp.open("POST", "sysbar_load.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		res = xmlDocs.loadXML(xmlHttp.responseText);
		if (!res) {
			xmlDocs = null;
			// alert(xmlHttp.responseText);
			alert(MSG_SYS_ERROR);
			return false;
		}
		// ticket = getNodeValue(xmlVars.documentElement, "ticket");
	}
// ////////////////////////////////////////////////////////////////////////////
	function extraSelFunc(obj, objName) {
		var node = null, nodes = null;
		var sel = true;

		if ((obj != null) && (obj.value == 0)) {
			select_func(objName, obj.checked);
		} else {
			node = document.getElementById(objName);
			if (node == null) return false;
			nodes = node.getElementsByTagName("input");
			for (var i = 0; i < nodes.length; i++) {
				if (nodes[i].type == "checkbox") {
					if (nodes[i].value == 0) {
						node = nodes[i];
						continue;
					}
					if (!nodes[i].checked) sel = false;
				}
			}
			if ((node != null) && (node.type == "checkbox")) node.checked = sel;
		}
	}
// ////////////////////////////////////////////////////////////////////////////
	function showID() {
		isId = !isId;
		displayLayout();
	}

	function getReturnValue() {
		var obj = document.getElementById('detail');
		if (typeof(window.returnValue) == 'undefined') return false;
		if (obj == null) return false;
		obj.value = window.returnValue;
	}

	function chgTabs(val) {
		var turl = "";

		if (notSave)
			if (!confirm(MSG_EXIT)) return;
		notSave = false;

		isChgTabs = true;
		switch (parseInt(val)) {
			{$chgtabs}
			default:
				turl = "about:blank";
				isChgTabs = false;
		}
		window.location.replace(turl);
	}

	var myWin = null;
	function browseFile(myurl) {
		myWin = window.open(myurl, '', 'width=380,height=420,status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1');
	}


	var editor = new Object();
	editor.setHTML = function(x)
	{
		xmlDocs.loadXML(x);
		displayLayout(); // 轉換介面
	};
	var st_id = '{$sysSession->cur_func}{$sb_level}';

	window.onload = function () {
		var obj = null;
		var re = /\/sysbar_tools\.php$/;

		obj = getTarget();
		if (obj.location.href.match(re) == null) {
			if (obj != null) obj.location.replace("sysbar_tools.php");
		}
		loadSysbar();    // 載入 sysbar
		displayLayout(); // 轉換介面

		xajax_check_temp(st_id, 'FCK.editor');
		window.setInterval(function(){if (notSave) xajax_save_temp(st_id, xmlDocs.xml);}, 100000);
	};

	window.onunload = function () {
		var obj = null;
		if ((myWin != null) && !myWin.closed) {
			myWin.close();
		}
		// if (notSave && confirm(MSG_EXIT)) {
		// 	doFunc(5);
		// }
		// notSave = false;
		if (!isChgTabs) {
			obj = getTarget();
			if (obj != null) obj.location.replace("about:blank");
		}
	};

	window.onbeforeunload=function() {
		if (notSave) return MSG_EXIT;
	};

BOF;

	$js_normal = <<< BOF

	/**
	 * 同步功能種類
	 * @param integer val : 功能
	 **/
	function syncKind(val) {
		var res = false;
		var obj = document.getElementById("fmProperties");
		if (obj == null) return false;
		res = syncValue(obj.kind, val);
		if (res) {
			chgKind(val);
			// hiddenProperty(false);
		} else if (val <= 1) {
			// hiddenProperty(true);
		}
		if (isSysDef) res = !isSysDef;
		hiddenProperty(!res);
		return res;
	}
BOF;

	$js_advance = <<< BOF

	/**
	 * 同步功能種類
	 * @param integer val : 功能
	 **/
	function syncKind(val) {
		var res = false;
		var obj = document.getElementById("fmProperties");
		if (obj == null) return false;
		res = syncValue(obj.kind, val);
		if (res) {
			chgKind(val);
			// hiddenProperty(false);
		} else if (val <= 1) {
			// hiddenProperty(true);
		}
		hiddenProperty(!res);
		return res;
	}
BOF;

	if (in_array($SYSBAR_LEVEL, array('root', 'administrator'))) {
		$js .= $js_advance;
	} else {
		$js .= $js_normal;
	}

	showXHTML_head_B($MSG['sysbar_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('inline' , $js);
	$xajax_save_temp->printJavascript('/lib/xajax/');
	showXHTML_head_E();
	showXHTML_body_B();
		// 主功能畫面 (Begin)
		// 說明
		switch ($SYSBAR_MENU) {
			case 'academic' : $help = 'academic_help'; break;
			case 'teach'    : $help = 'teac_help';     break;
			case 'learn'    : $help = 'class_help';    break;
			case 'direct'   : $help = 'direct_help';   break;
			case 'personal' : $help = 'personal_help'; break;
			case 'school'   : $help = 'maidan_help';   break;
		}
		showXHTML_tabFrame_B($ary, $act); //, form_id, table_id, form_extra, isDragable);
			$extra = 'width="100%" border="0" cellspacing="1" cellpadding="3" id="sysbarTabs" class="cssTable"';
			showXHTML_table_B($extra);
				// 說明
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('', $MSG[$help][$sysSession->lang]);
				showXHTML_tr_E();
				// 目前所處的課程
				$extra = (empty($sysbarCourseName)) ? ' style="display: none;"' : '';
				showXHTML_tr_B('class="cssTrHelp"' . $extra);
					$str = $MSG['msg_course_name'][$sysSession->lang] . $sysbarCourseName;
					showXHTML_td('', $str);
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		// 主功能畫面 (End)

		// 內容設定 (Begin)
		// echo '<div id="toolbox" style="position: absolute; z-index:1; left: 90px; top: 10px; display: none;" onmousedown="dragLayer(\'toolbox\', 0, 0, 400, 30)" onclick="document.getElementById(\'acl_box\').style.display = \'none\';document.getElementById(\'kind\').style.visibility = \'visible\';">';
		$ary = array();
		$ary[] = array($MSG['function_attribute'][$sysSession->lang], 'propertiesTabs');
		// Add By Edi, 暫時先隱藏
		$ary[] = array($MSG['tabs_access_set'][$sysSession->lang], 'accessTabs');
		showXHTML_tabFrame_B($ary, 1, 'fmProperties', 'toolbox', 'style="display: inline;"', true);
			// 一般設定 (Begin)
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="propertiesTabs"');
				// 選單編號
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator'))) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('align="right"', $MSG['function_id'][$sysSession->lang]);
						showXHTML_td_B('');
							showXHTML_input('text', 'fid', '', '', 'style="width: 160px" class="cssInput"');
						showXHTML_td_E('');
					showXHTML_tr_E('');
				}
				// 語系 (Begin)
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$arr_names = array('Big5'		=>	'big5',
								   'GB2312'		=>	'gb2312',
								   'en'			=>	'en',
								   'EUC-JP'		=>	'euc_jp',
								   'user_define'=>	'user_define'
								   );
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['function_title'][$sysSession->lang]);
					showXHTML_td_B('');
						$multi_lang = new Multi_lang(false, '', $col); // 多語系輸入框
						$multi_lang->show(true, $arr_names);
					showXHTML_td_E();
				showXHTML_tr_E();

				// 功能種類
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="cntKind"');
					showXHTML_td('align="right"', $MSG['function_kind'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('select', 'kind', $sysbarKind, 1, 'exclude="true" style="width: 160px" id="kind" class="cssInput" onchange="chgKind(this.value);"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
				// 功能內容
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="cntCont"');
					showXHTML_td('align="right"', $MSG['function_content'][$sysSession->lang]);
					showXHTML_td('id="cntDetail"', '&nbsp;');
				showXHTML_tr_E('');
				// 新開視窗
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="cntNWin"');
					showXHTML_td('align="right"', $MSG['open_new_window'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('checkbox', 'newopen', '_blank', '', '');
					showXHTML_td_E('');
				showXHTML_tr_E('');
				// 隱藏功能
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right"', $MSG['func_hide_function'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('checkbox', 'hidfunc', '', '', '');
					showXHTML_td_E('');
				showXHTML_tr_E('');

				// 設定系統預設值
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator'))) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col . ' id="cntSys"');
						showXHTML_td('align="right"', $MSG['set_system'][$sysSession->lang]);
						showXHTML_td_B('');
							showXHTML_input('checkbox', 'sysdef', '', '', '');
						showXHTML_td_E('');
					showXHTML_tr_E('');
				}
				// 工具列
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="cntTools"');
					showXHTML_td_B('align="center" colspan="2"');
						showXHTML_input('button', '', $MSG['ok'][$sysSession->lang], '', 'class="button01" onclick="itemPropertyComplete();"');
						showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="button01" onclick="document.getElementById(\'toolbox\').style.display = \'none\';"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
			// 一般設定 (End)
			// 存取設定 (Begin)
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="accessTabs" style="display: none;"');
				// 環境
				/*
				if (in_array($SYSBAR_LEVEL, array('root', 'administrator'))) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('align="right" valign="top"', $MSG['bar_envs'][$sysSession->lang]);
						showXHTML_td_B('id="barEnvs"');
							foreach ($sysbarEnv as $key => $val) {
								showXHTML_input('checkbox', 'env_' . $key , $key, '', 'id="env_' . $key . '" onclick="extraSelFunc(this, \'barEnvs\');"');
								echo '<label for="env_' . $key . '">' . $val . '</label><br />';
							}
						showXHTML_td_E('');
					showXHTML_tr_E('');
				}
				*/
				// 身份
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['bar_roles'][$sysSession->lang]);
					showXHTML_td_B('id="barRoles"');
						foreach ($sysbarRoles as $key => $val) {
							showXHTML_input('checkbox', 'role_' . $key , $key, '', 'id="role_' . $key . '" onclick="extraSelFunc(this, \'barRoles\');"');
							echo '<label for="role_' . $key . '">' . $val . '</label><br />';
						}
					showXHTML_td_E('');
				showXHTML_tr_E('');
				// 工具列
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="2"');
						showXHTML_input('button', '', $MSG['ok'][$sysSession->lang], '', 'class="button01" onclick="itemPropertyComplete();"');
						showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="button01" onclick="document.getElementById(\'toolbox\').style.display = \'none\';"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
			// 存取設定 (End)
		showXHTML_tabFrame_E();
		// echo '</div>';
		// 內容設定 (End)
	echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
	showXHTML_body_E();
?>

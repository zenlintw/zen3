<?php
	/**
	 * 線上傳訊公用函式
	 *
	 * @since   2003/11/18
	 * @author  ShenTing Lin
	 * @version $Id: msg_lib.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/msg_online.php');

	$sendJS = <<< BOF
	var MSG_SELECT       = "{$MSG['msg_select'][$sysSession->lang]}";
	var MSG_FILL_CONTENT = "{$MSG['msg_fill_content'][$sysSession->lang]}";
	var MSG_SEND_ERROR   = "{$MSG['msg_send_error'][$sysSession->lang]}";
	var MSG_RES_FAIL     = "{$MSG['msg_res_fail'][$sysSession->lang]}";
	var MSG_RES_SUCCESS  = "{$MSG['msg_res_success'][$sysSession->lang]}";
	var MSG_RES_SUC_OFF  = "{$MSG['msg_res_success_offline'][$sysSession->lang]}";
	var MSG_RES_SUC_HID  = "{$MSG['msg_res_success_invisible'][$sysSession->lang]}";
	var MSG_RES_SUC_REV  = "{$MSG['msg_res_success_reciver'][$sysSession->lang]}";
	var MSG_NO_TALK      = "{$MSG['msg_res_no_talk'][$sysSession->lang]}";
	var MSG_BTN_SEND     = "{$MSG['btn_send'][$sysSession->lang]}";
	var MSG_BTN_CALL     = "{$MSG['btn_call'][$sysSession->lang]}";
	var MSG_BTN_REJECT   = "{$MSG['btn_reject'][$sysSession->lang]}";
	var MSG_BTN_ACCEPT   = "{$MSG['btn_accept'][$sysSession->lang]}";
	var MSG_SEND_MSG     = "{$MSG['tabs_send_msg'][$sysSession->lang]}";
	var MSG_SEND_CALL    = "{$MSG['tabs_send_call'][$sysSession->lang]}";
	var MSG_HELP_MSG     = "{$MSG['msg_help_send'][$sysSession->lang]}";
	var MSG_HELP_CALL    = "{$MSG['msg_help_call'][$sysSession->lang]}";
	var MSG_DEF_REJECT   = "{$MSG['msg_default_reject'][$sysSession->lang]}";
	var MSG_DEF_ACCEPT   = "{$MSG['msg_default_accept'][$sysSession->lang]}";

	var ticket = "";
	var ctype  = "text";
	var talkSM = "";
	var xmlHttp = null, xmlVars = null;
	var user   = new Object();    // 帳號跟姓名的對應表
	var resObj = new Object();    // 傳送後的結果列表
	var mywin  = null;
	resObj[0] = MSG_RES_FAIL;
	resObj[1] = MSG_RES_SUCCESS;
	resObj[2] = MSG_RES_SUC_OFF;
	resObj[3] = MSG_RES_SUC_HID;
	resObj[4] = MSG_RES_SUC_REV;
	resObj[5] = MSG_NO_TALK;

	xmlHttp = XmlHttp.create();
	xmlVars = XmlDocument.create();

	function trim(str) {
		var re = /[\s]*/ig;
		return str.replace(re, "");
	}

	function chgType() {
		var obj = document.getElementById("ctlst");
		ctype = (ctype == "text") ? "html" : "text";
		if (obj) obj.innerHTML = (ctype == "text") ? "" : ".";
	}

	function wysiwyg()
	{
		if (ctype == "text") return false;
		if ((mywin != null) && !mywin.closed)
			mywin.focus();
		else
		{
			window.open("/online/msg_wysiwyg.php", "mywin", "resizable=1,scrollbar=1,toolbar=0,width=500,height=350");
		}
	}

	function buildUserList() {
		var username = "";
		var obj = document.getElementById("tabs1");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		user = new Object();
		for (var i = 3; i < obj.rows.length - 3; i++) {
			username = trim(obj.rows[i].cells[1].innerHTML);
			user[username] = obj.rows[i].cells[2].innerHTML;
		}
	}

	function msgWriteMutil() {
		var obj = document.getElementById("tabs1");
		var nodes = null, attr = null;
		var lst = new Array();

		if ((typeof(obj) != "object") || (obj == null)) return false;
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type != "checkbox") continue;
			attr = nodes[i].getAttribute("exclude");
			if ((attr != null) && (attr == "true")) continue;
			if (nodes[i].checked) lst[lst.length] = nodes[i].value;
		}
		if (lst.length <= 0) {
			alert(MSG_SELECT);
			return false;
		}
		msgWrite(lst);
	}

	/**
	 * 回覆訊息
	 * @param
	 * @return
	 **/
	function msgReply(val, idx) {
		var obj = null;
		var str = "";
		var re  = new RegExp("<.+>","ig");

		obj = document.getElementById("tabsMsgList");
		if (obj != null) str = obj.rows[idx + 1].cells[0].innerHTML;
		str = str.replace(re, " ", str);
		obj = document.getElementById("viewto");
		if (obj != null) obj.innerHTML = str;
		obj = document.getElementById("msg_list");
		if (obj != null) obj.value = val;

		re  = new RegExp(" (.+)$","ig");
		re.exec(str);
		str = RegExp.$1;
		str = str.replace("(", "", str);
		str = str.replace(")", "", str);
		user[val] = str;

		msgLayer(true);
	}

	function msgWrite(val) {
		var obj = document.getElementById("tab_send");
		if (obj != null) obj.innerHTML = MSG_SEND_MSG;
		obj = document.getElementById("helpMsg");
		if (obj != null) obj.innerHTML = MSG_HELP_MSG;
		obj = document.getElementById("btnSend");
		if (obj != null) obj.value = MSG_BTN_SEND;
		talkSM = "";
		msgWriting(val);
	}

	function msgWriting(val) {
		var obj = null;
		var str = "", lst = "";

		switch (typeof(val)) {
			case "string":
				str = val + " (" + user[val] + ")";
				lst = val;
				break;

			case "object":
				if ((typeof(val.length) == "undefined") || (val.length <= 0)) return false;
				for (var i = 0; i < val.length; i++) {
					str += val[i] + " (" + user[val[i]] + ")<br />";
				}
				lst = val.toString();
				break;
			default:
		}

		obj = document.getElementById("viewto");
		if (obj != null) obj.innerHTML = str;
		obj = document.getElementById("msg_list");
		if (obj != null) obj.value = lst;
		msgLayer(true);
	}

	function callWriteMutil() {
		var obj = document.getElementById("tabs1");
		var nodes = null, attr = null;
		var lst = new Array();

		if ((typeof(obj) != "object") || (obj == null)) return false;
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type != "checkbox") continue;
			attr = nodes[i].getAttribute("exclude");
			if ((attr != null) && (attr == "true")) continue;
			if (nodes[i].checked) lst[lst.length] = nodes[i].value;
		}
		if (lst.length <= 0) {
			alert(MSG_SELECT);
			return false;
		}
		callWrite(lst);
	}

	function callWrite(val) {
		var obj = document.getElementById("tab_send");
		if (obj != null) obj.innerHTML = MSG_SEND_CALL;
		obj = document.getElementById("helpMsg");
		if (obj != null) obj.innerHTML = MSG_HELP_CALL;
		obj = document.getElementById("btnSend");
		if (obj != null) obj.value = MSG_BTN_CALL;
		talkSM = "talk";
		msgWriting(val);
	}

	function rejectWrite(val) {
		var obj = document.getElementById("tab_send");
		if (obj != null) obj.innerHTML = MSG_SEND_CALL;
		obj = document.getElementById("helpMsg");
		if (obj != null) obj.innerHTML = MSG_HELP_CALL;
		obj = document.getElementById("btnSend");
		if (obj != null) obj.value = MSG_BTN_SEND;
		talkSM = "reject";
		msgWriting(val);
	}

	function acceptWrite(val) {
		var obj = null;
		/*
		obj = document.getElementById("tab_send");
		if (obj != null) obj.innerHTML = MSG_SEND_CALL;
		obj = document.getElementById("helpMsg");
		if (obj != null) obj.innerHTML = MSG_HELP_CALL;
		obj = document.getElementById("btnSend");
		if (obj != null) obj.value = MSG_BTN_SEND;
		msgWriting(val);
		*/
		talkSM = "accept";
		obj = document.getElementById("msg_list");
		if (obj != null) obj.value = val;

		obj = document.getElementById("sendFm");
		if (obj != null) obj.msg_content.value = MSG_DEF_ACCEPT;
		msgSend(true);
		goChatroom();
	}

	function msgSend(val) {
		var txt = "";
		var obj = document.getElementById("sendFm");
		var node1 = null, node2 = null;

		try {
			if (trim(obj.msg_list.value) == "")    throw MSG_SEND_ERROR;
			if (trim(obj.msg_content.value) == "") throw MSG_FILL_CONTENT;
		} catch(ex) {
			alert(ex);
			return false;
		}

		txt  = "<manifest>";
		txt += "<ticket>" + ticket + "</ticket>";
		txt += "<user>" + obj.msg_list.value + "</user>";
		txt += "<talk>" + talkSM + "</talk>";
		if (typeof(rid) == "string") {
			txt += "<other>" + rid + "</other>";
		} else {
			txt += "<other></other>";
		}
		txt += "<ctype>" + ctype + "</ctype>";
		txt +="</manifest>";
		if (!xmlVars.loadXML(txt)) {
			xmlVars = null;
			return false;
		}
		/*
		node1 = xmlVars.createElement("user");
		node2 = xmlVars.createTextNode(obj.msg_list.value);
		node1.appendChild(node2);
		xmlVars.documentElement.appendChild(node1);
		*/
		node1 = xmlVars.createElement("content");
		node2 = xmlVars.createTextNode(obj.msg_content.value);
		node1.appendChild(node2);
		xmlVars.documentElement.appendChild(node1);

		xmlHttp.open("POST", "msg_send.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		if (val == 0) viewSendResult(xmlHttp.responseText);
	}

	function msgLayer(val) {
		var obj = null;

		obj = document.getElementById("divSendMsg");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj = document.getElementById("divSendMsg");
		if (obj != null) obj.style.display = (val) ? "" : "none";
		obj = document.getElementById("tabsMsgView");
		if (obj != null) obj.style.display = (val) ? "none" : "";
		obj = document.getElementById("divSendRes");
		if (obj != null) obj.style.display = "none";

		if (val) {
			obj = document.getElementById("sendFm");
			if (obj != null) {
				obj.msg_content.value = (talkSM == "reject") ? MSG_DEF_REJECT : "";
				obj.msg_content.focus();
			}
		}

		/*
		if (!val) {
			obj = document.getElementById("viewto");
			if (obj != null) obj.innerHTML = '';
			obj = document.getElementById("sendFm");
			if (obj != null) {
				obj.msg_list.value    = "";
				obj.msg_content.value = "";
			}
		}
		*/
	}

	function resLayer(val) {
		obj = document.getElementById("divSendMsg");
		if (obj != null) obj.style.display = "none";
		obj = document.getElementById("tabsMsgView");
		if (obj != null) obj.style.display = (val) ? "none" : "";
		obj = document.getElementById("divSendRes");
		if (obj != null) obj.style.display = (val) ? "" : "none";
	}

	function viewSendResult(val) {
		var obj = document.getElementById("tabs3");
		var ting = null;
		var cnt = 0;
		var col = "bg03 font01";

		if ((typeof(obj) != "object") || (obj == null)) return false;
		// 清除舊資料 (delete old data)
		for (var i = 2; i < obj.rows.length - 1; i++) {
			obj.deleteRow(2);
		}

		resLayer(true);
		ting = val.split("\t");
		for (var i = 0; i < ting.length; i = i + 2) {
			cnt = parseInt(obj.rows.length) - 1;
			obj.insertRow(cnt);
			obj.rows[cnt].insertCell(0);
			obj.rows[cnt].cells[0].innerHTML = resObj[ting[i + 1]];
			obj.rows[cnt].insertCell(0);
			obj.rows[cnt].cells[0].innerHTML = user[ting[i]];
			obj.rows[cnt].insertCell(0);
			obj.rows[cnt].cells[0].innerHTML = ting[i];
		}

		for (var i = 2; i < obj.rows.length; i++) {
			col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
			obj.rows[i].className = col;
		}
		resLayer(true);
	}

	/**
	 * 進討論室
	 * @param string val : 討論室編號
	 * @return
	 **/
	var chatWin = null;
	function goChatroom() {
		var res = "";
		var node = null;
		if ((xmlHttp == null) || (xmlVars == null)) {
			return false;
		}
		if ((chatWin != null) && !chatWin.closed) {
			chatWin.focus();
		} else {
			if (typeof(rid) == "undefined") return false;
			var rnd = Math.ceil(Math.random() * 100000);
			chatWin = window.open("about:blank", "win" + rnd, "width=770,height=500,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1");
			var obj = document.getElementById("chatroomFm");
			obj.rid.value = rid;
			obj.target = "win" + rnd;
			obj.submit();
		}
		closeWin();
	}

	function closeWin() {
		if ((mywin != null) && !mywin.closed)
			mywin.close();
		parent.close();
	}
BOF;

	function msgSendWin($sender='', $btns=NULL, $isTalk=false, $rname='') {
		global $_COOKIE, $_ENV, $_SERVER, $sysSession, $MSG, $sender_name;

		// 傳送訊息 (Begin)
		echo '<div id="divSendMsg" style="display:none">';
		$ary = array();
		$ary[] = array('<span id="tab_send">' . $MSG['tabs_send_msg'][$sysSession->lang] . '</span>', 'tabs2');
		showXHTML_tabFrame_B($ary, 1, 'sendFm');
			$cols = '2';
			showXHTML_table_B('width="250" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead" onclick="chgType()"');
					showXHTML_td('colspan="' . $cols . '" nowrap="nowrap" id="helpMsg"', $MSG['msg_help_send'][$sysSession->lang]);
				showXHTML_tr_E('');
				$col = 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('nowrap="nowrap"', $MSG['th_to'][$sysSession->lang] . '<span id="ctlst"></span>');
					showXHTML_td('nowrap="nowrap" id="viewto"', $sender . ' (' . $sender_name . ')');
				showXHTML_tr_E('');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('nowrap="nowrap" ondblclick="wysiwyg();"', $MSG['th_content'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('hidden'  , 'msg_list'   , $sender, '', 'id="msg_list"');
						showXHTML_input('textarea', 'msg_content', ''     , '', 'id="msg_content" rows="4" cols="40" class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E('');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="' . $cols . '" nowrap="nowrap"');
						showXHTML_input('button', '', $MSG['btn_send'][$sysSession->lang], '', 'id="btnSend" class="cssBtn" onclick="msgSend(0);"');
						if ($isTalk) {
							showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="closeWin()"');
						} else {
							showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="msgLayer(false)"');
						}
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
		// 傳送訊息 (End)

		// 傳送結果 (Begin)
		echo '<div id="divSendRes" style="display:none">';
		$ary = array();
		$ary[] = array($MSG['tabs_send_result'][$sysSession->lang], 'tabs3');
		showXHTML_tabFrame_B($ary, 1);
			$cols = '3';
			showXHTML_table_B('width="250" border="0" cellspacing="1" cellpadding="3" id="tabs3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="' . $cols . '" nowrap="nowrap"', $MSG['msg_send_result'][$sysSession->lang]);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('align="center" nowrap="nowrap"', $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('align="center" nowrap="nowrap"', $MSG['th_realname'][$sysSession->lang]);
					showXHTML_td('align="center" nowrap="nowrap"', $MSG['th_result'][$sysSession->lang]);
				showXHTML_tr_E('');
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B('align="center" colspan="' . $cols . '" nowrap="nowrap"');
						// showXHTML_input('button', '', $MSG['btn_send'][$sysSession->lang], '', 'class="cssBtn" onclick="msgSend(0);"');
						if (is_array($btns) && (count($btns) > 0))
						{
							foreach ($btns as $val) {
								showXHTML_input('button', '', $val[0],   '', 'class="cssBtn" onclick="' . $val[1] . '"');
							}
						}
						showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang]    , '', 'class="cssBtn" onclick="closeWin()"');
					showXHTML_td_E();
				showXHTML_tr_E('');
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
		// 傳送結果 (End)

		showXHTML_form_B('action="/online/msg_chat.php" method="post" target="_blank" enctype="multipart/form-data" style="display:none"', 'chatroomFm');
			showXHTML_input('hidden', 'rid', ''    , '', '');
			showXHTML_input('hidden', 'rnm', $rname, '', '');
		showXHTML_form_E('');
	}


?>

<?php
	/**
	 * online
	 *
	 * @since   2003/10/28
	 * @author  ShenTing Lin
	 * @version $Id: online.php,v 1.2 2010/02/25 06:23:27 small Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/index.php');

	$sysSession->cur_func = '2100100200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$interval = 30000;

	$js = <<< BOF
	var ticket = "";
	var xmlHttp = null, xmlDocs = null, xmlVars = null;
	var timer = "";
	var counter = 0, total = 1;


	/**
	 * 啟動 Session
	 **/
	function sessionStart() {
		touchSession();
		timer = window.setInterval("touchSession()", {$interval});
	}

	/**
	 * 停止 Session
	 **/
	function sessionStop() {
		if (timer != null) clearInterval(timer);
	}

	/**
	 * 顯示線上人數
	 * @param string  val : 系統時間
	 * @param integer v1  : 全校
	 * @param integer v2  : 線上
	 * @param integer v3  : 全班
	 **/
	function showOnline(val, v1, v2, v3) {
		if (typeof(parent.showOnline) == "function")
			parent.showOnline(val, v1, v2, v3);
	}

	/**
	 * 顯示有無線上傳訊 (alert online message)
	 **/
	var imDailog = null;
	function imAlert() {
		if ((imDailog != null) && !imDailog.closed) {
			imDailog.focus();
		} else {
			var rnd = Math.ceil(Math.random() * 100000);
			imDailog = window.open("/online/msg_view.php", "win" + rnd, "width=300,height=150,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1,scrollbar=1");
			// imDailog = showDialog("/online/message.php", false , "", true, "200px", "200px", "300px", "150px", "status=0, resizable=1, scrollbars=1");
		}
	}

	function doFunc(str) {
		var txt = "";
		var v1 = 0, v2 = 0, v3 = 0;
		var node = null;
		var xVars = XmlDocument.create();

		if (xVars.loadXML(str)) {
			if (xVars.documentElement.nodeName == "html") {
				parent.location.replace("/connect_lost.php");
				return false;
			}

			txt = getNodeValue(xVars, "time");
			v1  = getNodeValue(xVars, "school");
			v2  = v1;
			// v2  = getNodeValue(xVars, "school");
			v3  = getNodeValue(xVars, "course");
			total = v1;
			showOnline(txt, v1, v2, v3);

			// 判斷client時間與server時間如果超過15min則不允許繼續使用
			var dt          = getNodeValue(xVars, "datetime");
			var server_time = new Date(dt);
			var client_time = new Date();
			if (Math.abs(server_time.getTime() - client_time.getTime()) > 86400000)
			{
				sessionStop();
				alert("{$MSG['sysTime Error'][$sysSession->lang]}");
				parent.location.replace('/');
				return;
			}

			v1  = getNodeValue(xVars, "message");
			if (parseInt(v1) > 0) imAlert();
		}
	}

	function touchSession() {
		var txt = "";
		var val = null;
		var res = 0;
		var xDocs = null, xVars = null, xHttp = null;

		total = parseInt(total);
		if (isNaN(val) || (total == 0)) total = 1;
		res  = counter % total;
		if (res == 0) counter = 0;
		counter++;
		txt  = "<manifest>";
		txt += "<ticket>" + ticket + "</ticket>";
		txt += "<erase>" + res + "</erase>";
		txt += "</manifest>";

		xHttp = XmlHttp.create();
		xDocs = XmlDocument.create();
		xVars = XmlDocument.create();

		try {
			xVars.loadXML(txt);
			xHttp.open("POST", "/online/session.php", true);
			xHttp.onreadystatechange = function () {
				if (xHttp.readyState == 4) {
					doFunc(xHttp.responseText);
				}
			};
			xHttp.send(xVars);
		} catch (e) {
			// alert(e);
		}
	}

	function chgEnv(val) {
		var txt = "";
		txt  = "<manifest>";
		txt += "<ticket>" + ticket + "</ticket>";
		txt += "<env>" + val + "</env>";
		txt += "</manifest>";

		try {
			xmlVars.loadXML(txt);
			xmlHttp.open("POST", "chgenv.php", false);
			xmlHttp.send(xmlVars);
		} catch (e) {
		}
	}

	window.onload = function () {
		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

		// touchSession();
		// alert(typeof timer);
		sessionStart();
	};
BOF;
	showXHTML_head_B('');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
	showXHTML_body_E();
?>

	/**
	 * 設定快速鍵
	 *
	 * 建立日期：2003/05/16
	 * @author  ShenTing Lin
	 * @version $Id: hotkey.js,v 1.1 2010/02/24 02:40:17 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	var isIE = false, isMZ = false;

	function chkBrowser() {
		var re = new RegExp("MSIE","ig");
		if (re.test(navigator.userAgent)) {
			isIE = true;
		}

		re = new RegExp("Gecko","ig");
		if (re.test(navigator.userAgent)) {
			isMZ = true;
		}
	}

	function getMsgList() {
		var ary = new Array();
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length < 0)) return null;
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type == "checkbox") {
				ary[ary.length] = nodes[i].value;
			}
		}
		return ary;
	}

	document.onkeydown = function (evnt) {
		var ary = null, obj = null;
		var key_code = 0;
		var idx = 0;

		if (isIE) evnt = event;
		evnt.cancelBubble = true;
		key_code = evnt.keyCode;
		switch (key_code) {
			//case 36 :   // first page
			//	if (evnt.ctrlKey && (typeof(go_page) == "function")) go_page(-1);
			//	break;
			case 38 :   // pre page
				if (typeof(go_page) == "function") go_page(-2);
				break;
			case 40 :   // next page
				if (typeof(go_page) == "function") go_page(-3);
				break;
			//case 36 :   // last page
			//	if (evnt.ctrlKey && (typeof(go_page) == "function")) go_page(-4);
			//	break;
			case 37 :   // return message list
				obj = (isIE) ? evnt.srcElement : evnt.target;
				if ((obj.type == "text") || (obj.type == "textarea")
					|| (obj.type == "file") || (obj.type == "select-one"))
					return true;
				if (typeof(goList)) goList();
				break;
			case 45 :   // write new message
			case 78 :
				if (typeof(post) == "function") post();
				break;
			case 77 :   // move message
				if (typeof(mv) == "function") mv();
				break;
			case 82 : // reply message
				if (typeof(reply) == "function") reply();
				break;
			case 70 : // forward message
				if (typeof(fw) == "function") fw();
				break;
			case 46 :   // delete selected message
			case 68 :
				if (typeof(del) == "function") del();
				break;
			case 83 :   // send message
				if (evnt.altKey) {
					obj = document.getElementById("post1");
					if ((obj != null) && (typeof(chkData) == "function")) {
						if (chkData(obj)) obj.submit();
					}
				}
				break;
			case 65 :   // select all
				if (typeof(select_func) == "function") select_func('', 1);
				break;
			case 27 :   // clear all
				if (typeof(select_func) == "function") select_func('', 0);
				break;
			case 48 : case 49 : case 50 : case 51 : case 52 :
			case 53 : case 54 : case 55 : case 56 : case 57 :

			case 96 : case 97 : case 98 : case 99 : case 100:
			case 101: case 102: case 103: case 104: case 105:
				idx = (key_code > 95) ? parseInt(key_code - 97) : parseInt(key_code - 49);
				if (idx < 0) idx = 9;
				if (idx > 9) return true;
				ary = getMsgList();
				if (ary == null) return true;
				if (typeof(read) == "function") read(ary[idx]);
				break;
		}
		//alert(key_code);
		return true;
	}

	chkBrowser();

	/**
	 * 設定快速鍵
	 *
	 * @since   2003/10/28
	 * @author  ShenTing Lin
	 * @version $Id: hotkey.js,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
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

	document.onkeydown = function (evnt) {
		var ary = null, obj = null;
		var key_code = 0;
		var idx = 0;

		if (isIE) evnt = event;
		evnt.cancelBubble = true;
		key_code = evnt.keyCode;
		switch (key_code) {
			case 83  :   // send message
			case 115 :   // send message
				if (evnt.altKey) {
					obj = document.getElementById("divSendMsg");
					if ((obj != null) && (obj.style.display != "none")) {
						msgSend(0);
					}
				}
				break;
			case 82  :   // reply message
			case 114 :   // reply message
				if (evnt.altKey) {
					obj = document.getElementById("sendFm");
					if ((obj != null) && (obj.msg_list.value == "")) {
						return true;
					}
					obj = document.getElementById("divSendMsg");
					if ((obj != null) && (obj.style.display == "none")) {
						msgLayer(true);
					}
				}
				break;
		}
		//alert(key_code);
		return true;
	}

	chkBrowser();
